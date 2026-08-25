<?php
defined( 'ABSPATH' ) || exit;

/**
 * Import par pack CSV : staging, validation, mapping, dry run, exécution et rollback.
 * Aucun mot de passe en clair n'est accepté. Les comptes sans hash compatible reçoivent un reset.
 */
final class PFC_Importer {
	private const JOBS_TABLE  = 'pfc_import_jobs';
	private const ITEMS_TABLE = 'pfc_import_items';

	public static function init(): void {
		add_action( 'admin_post_pfc_upload_pack', array( __CLASS__, 'upload_pack' ) );
		add_action( 'admin_post_pfc_dry_run', array( __CLASS__, 'dry_run' ) );
		add_action( 'admin_post_pfc_execute_import', array( __CLASS__, 'execute_import' ) );
		add_action( 'admin_post_pfc_rollback_import', array( __CLASS__, 'rollback_import' ) );
		add_action( 'admin_post_pfc_download_template', array( __CLASS__, 'download_template' ) );
	}

	public static function activate(): void {
		global $wpdb;
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		$charset = $wpdb->get_charset_collate();
		$jobs    = $wpdb->prefix . self::JOBS_TABLE;
		$items   = $wpdb->prefix . self::ITEMS_TABLE;
		dbDelta( "CREATE TABLE {$jobs} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			status varchar(30) NOT NULL DEFAULT 'uploaded',
			created_by bigint(20) unsigned NOT NULL,
			created_at datetime NOT NULL,
			updated_at datetime NOT NULL,
			source_dir text NOT NULL,
			summary longtext NULL,
			last_error longtext NULL,
			PRIMARY KEY (id), KEY status (status)
		) {$charset};" );
		dbDelta( "CREATE TABLE {$items} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			job_id bigint(20) unsigned NOT NULL,
			object_type varchar(30) NOT NULL,
			legacy_id varchar(191) NULL,
			object_id bigint(20) unsigned NULL,
			action_name varchar(30) NOT NULL,
			source_row int(11) NULL,
			details longtext NULL,
			PRIMARY KEY (id), KEY job_id (job_id), KEY legacy_id (legacy_id)
		) {$charset};" );

		// dbDelta peut échouer silencieusement selon la configuration MySQL du mutualisé.
		// Une seconde tentative explicite rend l’absence de table observable et bloquante.
		if ( $items !== $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $items ) ) ) {
			$created = $wpdb->query( "CREATE TABLE IF NOT EXISTS {$items} (
				id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				job_id bigint(20) unsigned NOT NULL,
				object_type varchar(30) NOT NULL,
				legacy_id varchar(191) NULL,
				object_id bigint(20) unsigned NULL,
				action_name varchar(30) NOT NULL,
				source_row int(11) NULL,
				details longtext NULL,
				PRIMARY KEY (id), KEY job_id (job_id), KEY legacy_id (legacy_id)
			) {$charset}" );
			if ( false === $created ) {
				update_option( 'pfc_schema_last_error', $wpdb->last_error ?: 'Création explicite de pfc_import_items impossible.', false );
			}
		}

		if ( $jobs === $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $jobs ) ) && $items === $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $items ) ) ) {
			update_option( 'pfc_schema_version', PFC_VERSION, false );
			delete_option( 'pfc_schema_last_error' );
		}
	}

	/**
	 * Les mises à jour ZIP WordPress ne déclenchent pas le hook d’activation.
	 * Vérifie donc les deux tables à chaque chargement du plugin et répare
	 * uniquement un schéma absent ou incomplet, sans toucher aux données métier.
	 */
	public static function ensure_schema(): void {
		global $wpdb;
		$jobs  = $wpdb->prefix . self::JOBS_TABLE;
		$items = $wpdb->prefix . self::ITEMS_TABLE;
		$jobs_exists  = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $jobs ) );
		$items_exists = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $items ) );
		if ( $jobs !== $jobs_exists || $items !== $items_exists || PFC_VERSION !== get_option( 'pfc_schema_version' ) ) {
			self::activate();
		}
	}

	/** Données de diagnostic consultables dans wp-admin sans accès à la base. */
	public static function schema_diagnostics( int $job_id = 0 ): array {
		global $wpdb;
		$jobs  = $wpdb->prefix . self::JOBS_TABLE;
		$items = $wpdb->prefix . self::ITEMS_TABLE;
		$tables = array(
			'jobs'  => $jobs === $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $jobs ) ),
			'items' => $items === $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $items ) ),
		);
		$actions = array();
		if ( $tables['items'] && $job_id ) {
			$actions = $wpdb->get_results( $wpdb->prepare( "SELECT action_name, COUNT(*) AS total FROM {$items} WHERE job_id = %d GROUP BY action_name ORDER BY action_name", $job_id ), ARRAY_A );
		}
		return array(
			'tables'     => $tables,
			'jobs_table' => $jobs,
			'items_table'=> $items,
			'actions'    => $actions,
			'last_error' => get_option( 'pfc_schema_last_error', '' ) ?: $wpdb->last_error,
		);
	}

	public static function schemas(): array {
		return array(
			'users'   => array( 'required' => array( 'legacy_user_id', 'username', 'email', 'first_name', 'last_name', 'rank' ) ),
			'forums'  => array( 'required' => array( 'legacy_forum_id', 'title' ) ),
			'topics'  => array( 'required' => array( 'legacy_topic_id', 'legacy_forum_id', 'legacy_author_id', 'title', 'content', 'created_at', 'upvotes_count' ) ),
			'replies' => array( 'required' => array( 'legacy_reply_id', 'legacy_topic_id', 'legacy_author_id', 'content', 'created_at', 'upvotes_count' ) ),
		);
	}

	public static function template_headers( string $type ): array {
		return array(
			'users'   => array( 'legacy_user_id', 'username', 'email', 'first_name', 'last_name', 'display_name', 'registered_at', 'bio', 'rank', 'role', 'status', 'password_hash' ),
			'forums'  => array( 'legacy_forum_id', 'title', 'description', 'parent_legacy_forum_id', 'status', 'sort_order' ),
			'topics'  => array( 'legacy_topic_id', 'legacy_forum_id', 'legacy_author_id', 'title', 'content', 'created_at', 'updated_at', 'status', 'slug', 'upvotes_count', 'replies_count', 'is_sticky', 'is_resolved' ),
			'replies' => array( 'legacy_reply_id', 'legacy_topic_id', 'legacy_author_id', 'content', 'created_at', 'updated_at', 'status', 'upvotes_count', 'legacy_parent_reply_id', 'sort_order' ),
		)[ $type ] ?? array();
	}

	public static function upload_pack(): void {
		self::guard( 'pfc_upload_pack' );
		if ( empty( $_FILES['pfc_files'] ) ) {
			self::redirect( 0, 'error', 'Aucun fichier CSV reçu.' );
		}
		$job_id = self::create_job();
		$dir    = self::job_dir( $job_id );
		wp_mkdir_p( $dir );

		$files = $_FILES['pfc_files'];
		for ( $i = 0; $i < count( $files['name'] ); $i++ ) {
			$name = sanitize_file_name( $files['name'][ $i ] );
			$extension = strtolower( pathinfo( $name, PATHINFO_EXTENSION ) );
			if ( $files['error'][ $i ] !== UPLOAD_ERR_OK || ! in_array( $extension, array( 'csv', 'zip' ), true ) ) {
				self::add_item( $job_id, 'file', $name, 0, 'error', 0, array( 'message' => 'Fichier CSV ou ZIP invalide.' ) );
				continue;
			}
			if ( 'zip' === $extension ) {
				self::extract_zip_pack( $job_id, $files['tmp_name'][ $i ], $dir );
				continue;
			}
			if ( ! in_array( strtolower( $name ), array( 'users.csv', 'forums.csv', 'topics.csv', 'replies.csv' ), true ) ) {
				self::add_item( $job_id, 'file', $name, 0, 'error', 0, array( 'message' => 'Nom de fichier CSV non pris en charge.' ) );
				continue;
			}
			$target = trailingslashit( $dir ) . $name;
			if ( ! move_uploaded_file( $files['tmp_name'][ $i ], $target ) ) {
				self::add_item( $job_id, 'file', $name, 0, 'error', 0, array( 'message' => 'Copie du fichier impossible.' ) );
			}
		}
		self::set_job( $job_id, 'uploaded' );
		self::redirect( $job_id, 'updated', 'Pack CSV téléversé. Lancez la validation.' );
	}

	/** Extrait uniquement les quatre CSV attendus, sans chemins ni fichiers binaires. */
	private static function extract_zip_pack( int $job_id, string $temporary_path, string $dir ): void {
		if ( ! class_exists( 'ZipArchive' ) ) {
			self::add_item( $job_id, 'file', 'pack.zip', 0, 'error', 0, array( 'message' => 'ZipArchive est indisponible sur ce serveur.' ) );
			return;
		}
		$archive = new ZipArchive();
		if ( true !== $archive->open( $temporary_path ) ) {
			self::add_item( $job_id, 'file', 'pack.zip', 0, 'error', 0, array( 'message' => 'Ouverture du pack ZIP impossible.' ) );
			return;
		}
		$allowed = array_flip( array( 'users.csv', 'forums.csv', 'topics.csv', 'replies.csv' ) );
		for ( $index = 0; $index < $archive->numFiles; $index++ ) {
			$entry = $archive->statIndex( $index );
			$name  = $entry['name'] ?? '';
			$base  = strtolower( basename( $name ) );
			if ( $name !== $base || ! isset( $allowed[ $base ] ) ) {
				continue;
			}
			if ( (int) ( $entry['size'] ?? 0 ) > 5 * MB_IN_BYTES ) {
				self::add_item( $job_id, 'file', $base, 0, 'error', 0, array( 'message' => 'Fichier CSV trop volumineux (maximum 5 Mo).' ) );
				continue;
			}
			$stream = $archive->getStream( $name );
			if ( ! $stream ) {
				self::add_item( $job_id, 'file', $base, 0, 'error', 0, array( 'message' => 'Lecture du CSV dans le ZIP impossible.' ) );
				continue;
			}
			$target = fopen( trailingslashit( $dir ) . $base, 'wb' );
			if ( ! $target ) {
				fclose( $stream );
				self::add_item( $job_id, 'file', $base, 0, 'error', 0, array( 'message' => 'Écriture du CSV impossible.' ) );
				continue;
			}
			stream_copy_to_stream( $stream, $target );
			fclose( $stream );
			fclose( $target );
		}
		$archive->close();
	}

	public static function dry_run(): void {
		self::guard( 'pfc_dry_run' );
		$job_id = absint( $_POST['job_id'] ?? 0 );
		$job    = self::job( $job_id );
		if ( ! $job ) {
			self::redirect( 0, 'error', 'Import introuvable.' );
		}
		self::clear_items( $job_id );
		$summary = self::validate_job( $job_id, $job->source_dir );
		self::set_job( $job_id, empty( $summary['errors'] ) ? 'validated' : 'needs_fix', $summary );
		self::redirect( $job_id, empty( $summary['errors'] ) ? 'updated' : 'warning', empty( $summary['errors'] ) ? 'Dry run valide : aucune donnée n’a été écrite.' : 'Dry run terminé : des corrections sont requises.' );
	}

	public static function execute_import(): void {
		self::guard( 'pfc_execute_import' );
		$job_id = absint( $_POST['job_id'] ?? 0 );
		$job    = self::job( $job_id );
		if ( ! $job || 'validated' !== $job->status ) {
			self::redirect( $job_id, 'error', 'Un dry run valide est obligatoire avant l’import.' );
		}

		try {
			self::set_job( $job_id, 'running' );
			$rows    = self::read_pack( $job->source_dir );
			$mapping = array( 'users' => array(), 'forums' => array(), 'topics' => array() );
			foreach ( $rows['users'] as $number => $row ) {
				$user_id = self::import_user( $job_id, $row, $number );
				$mapping['users'][ (string) $row['legacy_user_id'] ] = $user_id;
			}
			foreach ( $rows['forums'] as $number => $row ) {
				$forum_id = self::import_forum( $job_id, $row, $number, $mapping['forums'] );
				$mapping['forums'][ (string) $row['legacy_forum_id'] ] = $forum_id;
			}
			foreach ( $rows['topics'] as $number => $row ) {
				$topic_id = self::import_topic( $job_id, $row, $number, $mapping['users'], $mapping['forums'] );
				$mapping['topics'][ (string) $row['legacy_topic_id'] ] = $topic_id;
			}
			foreach ( $rows['replies'] as $number => $row ) {
				self::import_reply( $job_id, $row, $number, $mapping['users'], $mapping['topics'] );
			}
				self::recount( $mapping['forums'], $mapping['topics'], $mapping['users'] );
			self::set_job( $job_id, 'completed', array_merge( (array) json_decode( $job->summary, true ), array( 'completed_at' => current_time( 'mysql', true ) ) ) );
			self::redirect( $job_id, 'updated', 'Import terminé. Les objets sont journalisés et peuvent être annulés.' );
		} catch ( Throwable $exception ) {
			self::set_job( $job_id, 'failed', array(), $exception->getMessage() );
			self::redirect( $job_id, 'error', 'Import interrompu : ' . $exception->getMessage() );
		}
	}

	public static function rollback_import(): void {
		self::guard( 'pfc_rollback_import' );
		$job_id = absint( $_POST['job_id'] ?? 0 );
		$items  = self::items( $job_id, array( 'created' ) );
		if ( empty( $items ) ) {
			self::recover_created_items_from_meta( $job_id );
			$items = self::items( $job_id, array( 'created' ) );
		}
		if ( empty( $items ) ) {
			self::redirect( $job_id, 'error', 'Aucun objet créé à annuler.' );
		}
		require_once ABSPATH . 'wp-admin/includes/user.php';
		$priority = array( 'reply' => 1, 'topic' => 2, 'forum' => 3, 'user' => 4 );
		usort( $items, static fn( $a, $b ) => ( $priority[ $a->object_type ] ?? 9 ) <=> ( $priority[ $b->object_type ] ?? 9 ) );
		foreach ( $items as $item ) {
			if ( 'user' === $item->object_type ) {
				if ( get_user_meta( $item->object_id, '_pfc_import_job', true ) === (string) $job_id ) {
					wp_delete_user( $item->object_id );
				}
			} else {
				wp_delete_post( $item->object_id, true );
			}
				self::add_item( $job_id, $item->object_type, $item->legacy_id, $item->object_id, 'rolled_back', $item->source_row, array() );
		}
		self::set_job( $job_id, 'rolled_back' );
		self::redirect( $job_id, 'updated', 'Rollback terminé. Seuls les objets créés par ce job ont été supprimés.' );
	}

	/**
	 * Répare le journal d’un ancien job incomplet avant rollback.
	 * Les métadonnées _pfc_import_job sont posées lors de la création et constituent
	 * la source de vérité de secours ; aucun objet non marqué par ce job n’est touché.
	 */
	private static function recover_created_items_from_meta( int $job_id ): void {
		global $wpdb;
		$post_types   = array_values( array_filter( array(
			function_exists( 'bbp_get_reply_post_type' ) ? bbp_get_reply_post_type() : 'reply',
			function_exists( 'bbp_get_topic_post_type' ) ? bbp_get_topic_post_type() : 'topic',
			function_exists( 'bbp_get_forum_post_type' ) ? bbp_get_forum_post_type() : 'forum',
		) ) );
		$placeholders = implode( ', ', array_fill( 0, count( $post_types ), '%s' ) );
		$sql          = "SELECT p.ID FROM {$wpdb->posts} p INNER JOIN {$wpdb->postmeta} pm ON pm.post_id = p.ID WHERE pm.meta_key = %s AND pm.meta_value = %s AND p.post_type IN ({$placeholders})";
		$post_ids     = $wpdb->get_col( $wpdb->prepare( $sql, array_merge( array( '_pfc_import_job', (string) $job_id ), $post_types ) ) );
		foreach ( $post_ids as $post_id ) {
			self::add_item( $job_id, get_post_type( $post_id ), (string) get_post_meta( $post_id, '_pfc_legacy_id', true ), (int) $post_id, 'created', 0, array( 'recovered_from_meta' => true ) );
		}
		$user_ids = $wpdb->get_col( $wpdb->prepare( "SELECT user_id FROM {$wpdb->usermeta} WHERE meta_key = %s AND meta_value = %s", '_pfc_import_job', (string) $job_id ) );
		foreach ( $user_ids as $user_id ) {
			self::add_item( $job_id, 'user', (string) get_user_meta( $user_id, '_pfc_legacy_user_id', true ), (int) $user_id, 'created', 0, array( 'recovered_from_meta' => true ) );
		}
	}

	public static function download_template(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'Accès refusé.' );
		}
		check_admin_referer( 'pfc_template' );
		$type = sanitize_key( $_GET['type'] ?? '' );
		$head = self::template_headers( $type );
		if ( empty( $head ) ) {
			wp_die( 'Modèle inconnu.' );
		}
		nocache_headers();
		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename="' . $type . '-template.csv"' );
		$out = fopen( 'php://output', 'w' );
		fputcsv( $out, $head );
		fclose( $out );
		exit;
	}

	private static function validate_job( int $job_id, string $dir ): array {
		$summary = array( 'files' => 0, 'rows' => 0, 'errors' => array(), 'warnings' => array(), 'counts' => array() );
		$pack    = self::read_pack( $dir, true );
		foreach ( self::schemas() as $type => $schema ) {
			$file = trailingslashit( $dir ) . $type . '.csv';
			if ( ! file_exists( $file ) ) {
				$summary['errors'][] = array( 'file' => $type . '.csv', 'row' => 0, 'message' => 'Fichier requis absent.' );
				continue;
			}
			$summary['files']++;
			foreach ( $pack[ $type ] as $line => $row ) {
				$summary['rows']++;
				$issues = self::validate_row( $type, $row, $schema['required'] );
				foreach ( $issues as $issue ) {
					$summary['errors'][] = array( 'file' => $type . '.csv', 'row' => $line, 'message' => $issue );
					self::add_item( $job_id, $type, self::legacy_key( $type, $row ), 0, 'error', $line, array( 'message' => $issue ) );
				}
				$summary['counts'][ $type ] = ( $summary['counts'][ $type ] ?? 0 ) + 1;
			}
		}
		return $summary;
	}

	private static function validate_row( string $type, array $row, array $required ): array {
		$issues = array();
		foreach ( $required as $field ) {
			if ( ! isset( $row[ $field ] ) || '' === trim( (string) $row[ $field ] ) ) {
				$issues[] = sprintf( 'Champ requis vide : %s.', $field );
			}
		}
		if ( isset( $row['password'], $row['plain_password'] ) || array_key_exists( 'password', $row ) || array_key_exists( 'plain_password', $row ) ) {
			$issues[] = 'Les mots de passe en clair sont interdits.';
		}
		if ( 'users' === $type && ! is_email( $row['email'] ?? '' ) ) {
			$issues[] = 'Adresse email invalide.';
		}
		if ( in_array( $type, array( 'topics', 'replies' ), true ) ) {
			if ( ! self::normalise_date( $row['created_at'] ?? '' ) ) {
				$issues[] = 'created_at doit être une date ISO 8601 valide.';
			}
			if ( isset( $row['updated_at'] ) && '' !== $row['updated_at'] && ! self::normalise_date( $row['updated_at'] ) ) {
				$issues[] = 'updated_at doit être une date ISO 8601 valide.';
			}
			if ( ! ctype_digit( (string) ( $row['upvotes_count'] ?? '' ) ) ) {
				$issues[] = 'upvotes_count doit être un entier positif ou nul.';
			}
		}
		return $issues;
	}

	private static function read_pack( string $dir, bool $allow_errors = false ): array {
		$result = array();
		foreach ( array_keys( self::schemas() ) as $type ) {
			$file = trailingslashit( $dir ) . $type . '.csv';
			$result[ $type ] = file_exists( $file ) ? self::read_csv( $file, $allow_errors, $type ) : array();
		}
		return $result;
	}

	private static function read_csv( string $file, bool $allow_errors, string $type ): array {
		$handle = fopen( $file, 'rb' );
		if ( ! $handle ) {
			throw new RuntimeException( 'Lecture CSV impossible.' );
		}
		$headers = fgetcsv( $handle );
		$headers = array_map( static fn( $v ) => self::map_header( sanitize_key( trim( (string) $v ) ), $type ), $headers ?: array() );
		$rows    = array();
		$line    = 1;
		while ( false !== ( $values = fgetcsv( $handle ) ) ) {
			$line++;
			if ( array( null ) === $values || array() === $values ) { continue; }
			$row = array_combine( $headers, array_pad( array_slice( $values, 0, count( $headers ) ), count( $headers ), '' ) );
			if ( false === $row ) { if ( $allow_errors ) { continue; } throw new RuntimeException( "Colonnes incohérentes à la ligne {$line}." ); }
			$row['__line'] = $line;
			$rows[ $line ] = array_map( static fn( $v ) => is_string( $v ) ? trim( wp_unslash( $v ) ) : $v, $row );
		}
		fclose( $handle );
		return $rows;
	}

	/** Accepte les colonnes usuelles de plateformes historiques sans cacher le mapping effectif. */
	private static function map_header( string $header, string $type ): string {
		$aliases = array(
			'user_id' => 'legacy_user_id', 'member_id' => 'legacy_user_id', 'pseudo' => 'username', 'login' => 'username',
			'mail' => 'email', 'email_address' => 'email', 'forum_id' => 'legacy_forum_id',
			'topic_id' => 'legacy_topic_id', 'thread_id' => 'legacy_topic_id', 'reply_id' => 'legacy_reply_id',
			'author_id' => 'legacy_author_id', 'user_id_author' => 'legacy_author_id', 'body' => 'content',
			'date' => 'created_at', 'published_at' => 'created_at', 'votes' => 'upvotes_count', 'upvotes' => 'upvotes_count', 'legacy_upvotes_count' => 'upvotes_count',
		);
		if ( in_array( $type, array( 'topics', 'replies' ), true ) && 'user_id' === $header ) {
			return 'legacy_author_id';
		}
		$mapping = apply_filters( 'pfc_csv_header_mapping', $aliases, $type );
		return $mapping[ $header ] ?? $header;
	}

	private static function import_user( int $job_id, array $row, int $line ): int {
		$existing = get_user_by( 'email', $row['email'] );
		if ( $existing ) {
			self::add_item( $job_id, 'user', $row['legacy_user_id'], $existing->ID, 'matched', $line, array() );
			return (int) $existing->ID;
		}
		$user_id = wp_insert_user( array( 'user_login' => sanitize_user( $row['username'], true ), 'user_email' => sanitize_email( $row['email'] ), 'user_pass' => wp_generate_password( 32, true, true ), 'display_name' => sanitize_text_field( $row['display_name'] ?: $row['username'] ), 'first_name' => sanitize_text_field( $row['first_name'] ), 'last_name' => sanitize_text_field( $row['last_name'] ), 'role' => 'subscriber', 'user_registered' => self::normalise_date( $row['registered_at'] ?? '' ) ?: current_time( 'mysql', true ) ) );
		if ( is_wp_error( $user_id ) ) { throw new RuntimeException( $user_id->get_error_message() ); }
		update_user_meta( $user_id, '_pfc_import_job', (string) $job_id );
		update_user_meta( $user_id, '_pfc_legacy_user_id', sanitize_text_field( $row['legacy_user_id'] ) );
		update_user_meta( $user_id, 'pfc_rank', sanitize_text_field( $row['rank'] ) );
		update_user_meta( $user_id, 'description', wp_kses_post( $row['bio'] ?? '' ) );
		update_user_meta( $user_id, 'pfc_password_reset_required', 1 );
			self::record_created( $job_id, 'user', $row['legacy_user_id'], $user_id, $line );
		return (int) $user_id;
	}

	private static function import_forum( int $job_id, array $row, int $line, array $forums ): int {
		$parent = $forums[ (string) ( $row['parent_legacy_forum_id'] ?? '' ) ] ?? 0;
		$forum  = bbp_insert_forum( array( 'post_title' => sanitize_text_field( $row['title'] ), 'post_content' => wp_kses_post( $row['description'] ?? '' ), 'post_parent' => $parent, 'post_status' => 'private' === ( $row['status'] ?? '' ) ? bbp_get_private_status_id() : bbp_get_public_status_id() ) );
		if ( is_wp_error( $forum ) || ! $forum ) { throw new RuntimeException( 'Création du forum impossible.' ); }
		update_post_meta( $forum, '_pfc_import_job', $job_id );
		self::record_created( $job_id, 'forum', $row['legacy_forum_id'], $forum, $line );
		return (int) $forum;
	}

	private static function import_topic( int $job_id, array $row, int $line, array $users, array $forums ): int {
		$author = $users[ (string) $row['legacy_author_id'] ] ?? 0;
		$forum  = $forums[ (string) $row['legacy_forum_id'] ] ?? 0;
		if ( ! $author || ! $forum ) { throw new RuntimeException( 'Mapping auteur ou forum absent pour un sujet.' ); }
		$date = self::normalise_date( $row['created_at'] );
		$topic = bbp_insert_topic( array( 'post_parent' => $forum, 'post_author' => $author, 'post_title' => sanitize_text_field( $row['title'] ), 'post_content' => wp_kses_post( $row['content'] ), 'post_name' => sanitize_title( $row['slug'] ?: $row['title'] ), 'post_status' => bbp_get_public_status_id(), 'post_date' => $date, 'post_date_gmt' => get_gmt_from_date( $date ) ) );
		if ( is_wp_error( $topic ) || ! $topic ) { throw new RuntimeException( 'Création du sujet impossible.' ); }
		self::import_meta( $topic, $job_id, $row );
		self::record_created( $job_id, 'topic', $row['legacy_topic_id'], $topic, $line );
		return (int) $topic;
	}

	private static function import_reply( int $job_id, array $row, int $line, array $users, array $topics ): int {
		$author = $users[ (string) $row['legacy_author_id'] ] ?? 0;
		$topic  = $topics[ (string) $row['legacy_topic_id'] ] ?? 0;
		if ( ! $author || ! $topic ) { throw new RuntimeException( 'Mapping auteur ou sujet absent pour une réponse.' ); }
		$date  = self::normalise_date( $row['created_at'] );
		$reply = bbp_insert_reply( array( 'post_parent' => $topic, 'post_author' => $author, 'post_content' => wp_kses_post( $row['content'] ), 'post_status' => bbp_get_public_status_id(), 'post_date' => $date, 'post_date_gmt' => get_gmt_from_date( $date ) ) );
		if ( is_wp_error( $reply ) || ! $reply ) { throw new RuntimeException( 'Création de la réponse impossible.' ); }
		self::import_meta( $reply, $job_id, $row );
		self::record_created( $job_id, 'reply', $row['legacy_reply_id'], $reply, $line );
		return (int) $reply;
	}

	private static function import_meta( int $post_id, int $job_id, array $row ): void {
		update_post_meta( $post_id, '_pfc_import_job', $job_id );
		update_post_meta( $post_id, '_pfc_legacy_id', self::legacy_key( '', $row ) );
		update_post_meta( $post_id, 'pfc_legacy_upvotes_count', absint( $row['upvotes_count'] ?? 0 ) );
		update_post_meta( $post_id, 'pfc_native_upvotes_count', 0 );
		if ( ! empty( $row['updated_at'] ) ) { update_post_meta( $post_id, 'pfc_historical_updated_at', self::normalise_date( $row['updated_at'] ) ); }
	}

	/** Synchronise les caches bbPress après l’import sans modifier les sources historiques. */
	private static function recount( array $forums, array $topics, array $users ): void {
		foreach ( array_unique( array_map( 'absint', $topics ) ) as $topic_id ) { if ( function_exists( 'bbp_update_topic_reply_count' ) ) bbp_update_topic_reply_count( $topic_id ); }
		foreach ( array_unique( array_map( 'absint', $forums ) ) as $forum_id ) { if ( function_exists( 'bbp_update_forum_topic_count' ) ) bbp_update_forum_topic_count( $forum_id ); if ( function_exists( 'bbp_update_forum_reply_count' ) ) bbp_update_forum_reply_count( $forum_id ); }
		foreach ( array_unique( array_map( 'absint', $users ) ) as $user_id ) { if ( function_exists( 'bbp_update_user_topic_count' ) && function_exists( 'bbp_get_user_topic_count_raw' ) ) bbp_update_user_topic_count( $user_id, bbp_get_user_topic_count_raw( $user_id ) ); if ( function_exists( 'bbp_update_user_reply_count' ) && function_exists( 'bbp_get_user_reply_count_raw' ) ) bbp_update_user_reply_count( $user_id, bbp_get_user_reply_count_raw( $user_id ) ); }
	}

	private static function normalise_date( string $value ): string {
		if ( '' === trim( $value ) ) { return ''; }
		try { return ( new DateTimeImmutable( $value, wp_timezone() ) )->format( 'Y-m-d H:i:s' ); } catch ( Exception $e ) { return ''; }
	}

	private static function legacy_key( string $type, array $row ): string {
		foreach ( array( 'legacy_user_id', 'legacy_forum_id', 'legacy_topic_id', 'legacy_reply_id' ) as $field ) { if ( isset( $row[ $field ] ) ) { return (string) $row[ $field ]; } }
		return $type;
	}

	private static function guard( string $action ): void {
		if ( ! current_user_can( 'manage_options' ) ) { wp_die( 'Accès refusé.' ); }
		check_admin_referer( $action );
	}
	private static function create_job(): int {
		global $wpdb; $now = current_time( 'mysql', true ); $wpdb->insert( $wpdb->prefix . self::JOBS_TABLE, array( 'status' => 'uploaded', 'created_by' => get_current_user_id(), 'created_at' => $now, 'updated_at' => $now, 'source_dir' => '' ) ); $id = (int) $wpdb->insert_id; $wpdb->update( $wpdb->prefix . self::JOBS_TABLE, array( 'source_dir' => self::job_dir( $id ) ), array( 'id' => $id ) ); return $id;
	}
	private static function job_dir( int $job_id ): string { $uploads = wp_upload_dir(); return trailingslashit( $uploads['basedir'] ) . 'premium-forum-imports/job-' . $job_id; }
	public static function get_job( int $id ) { global $wpdb; return $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . $wpdb->prefix . self::JOBS_TABLE . ' WHERE id = %d', $id ) ); }
	private static function job( int $id ) { return self::get_job( $id ); }
	private static function set_job( int $id, string $status, array $summary = array(), string $error = '' ): void { global $wpdb; $data = array( 'status' => $status, 'updated_at' => current_time( 'mysql', true ) ); if ( $summary ) { $data['summary'] = wp_json_encode( $summary ); } if ( $error ) { $data['last_error'] = $error; } $wpdb->update( $wpdb->prefix . self::JOBS_TABLE, $data, array( 'id' => $id ) ); }
	private static function add_item( int $job_id, string $type, string $legacy, int $object, string $action, int $line, array $details ): bool { global $wpdb; return false !== $wpdb->insert( $wpdb->prefix . self::ITEMS_TABLE, array( 'job_id' => $job_id, 'object_type' => $type, 'legacy_id' => $legacy, 'object_id' => $object, 'action_name' => $action, 'source_row' => $line, 'details' => wp_json_encode( $details ) ) ); }
	private static function record_created( int $job_id, string $type, string $legacy, int $object, int $line ): void { global $wpdb; if ( ! self::add_item( $job_id, $type, $legacy, $object, 'created', $line, array() ) ) { throw new RuntimeException( 'Journal de migration impossible pour ' . $type . ' #' . $object . ( $wpdb->last_error ? ' : ' . $wpdb->last_error : '' ) ); } }
	private static function clear_items( int $job_id ): void { global $wpdb; $wpdb->delete( $wpdb->prefix . self::ITEMS_TABLE, array( 'job_id' => $job_id ) ); }
	public static function get_items( int $job_id, array $actions = array() ): array { global $wpdb; if ( empty( $actions ) ) return $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM ' . $wpdb->prefix . self::ITEMS_TABLE . ' WHERE job_id = %d ORDER BY id DESC', $job_id ) ); $placeholders = implode( ',', array_fill( 0, count( $actions ), '%s' ) ); return $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM ' . $wpdb->prefix . self::ITEMS_TABLE . " WHERE job_id = %d AND action_name IN ({$placeholders}) ORDER BY id DESC", array_merge( array( $job_id ), $actions ) ) ); }
	private static function items( int $job_id, array $actions ): array { return self::get_items( $job_id, $actions ); }
	private static function redirect( int $job, string $kind, string $message ): void { wp_safe_redirect( add_query_arg( array( 'page' => 'pfc-import', 'job' => $job, 'pfc_notice' => rawurlencode( $message ), 'pfc_kind' => $kind ), admin_url( 'admin.php' ) ) ); exit; }
}
