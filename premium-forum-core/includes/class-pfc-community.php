<?php
/**
 * PFC Community — notifications internes, suivi des sujets et statistiques membres.
 * Les votes importés restent des compteurs agrégés ; les votes natifs sont dédupliqués par membre et objet.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class PFC_Community {
	public static function init(): void {
		add_action( 'init', array( __CLASS__, 'register_svip_role' ), 5 );
		add_action( 'init', array( __CLASS__, 'ensure_schema' ), 20 );
		add_action( 'init', array( __CLASS__, 'migrate_community_data' ), 25 );
		add_action( 'show_user_profile', array( __CLASS__, 'profile_field' ) );
		add_action( 'edit_user_profile', array( __CLASS__, 'profile_field' ) );
		add_action( 'personal_options_update', array( __CLASS__, 'save_profile_field' ) );
		add_action( 'edit_user_profile_update', array( __CLASS__, 'save_profile_field' ) );
		add_action( 'deleted_user', array( __CLASS__, 'cleanup_deleted_user' ) );
		add_action( 'trashed_post', array( __CLASS__, 'cleanup_trashed_object' ) );
		add_action( 'bbp_new_reply', array( __CLASS__, 'on_new_reply' ), 10, 5 );
		add_action( 'wp_ajax_pfc_community_nonce', array( __CLASS__, 'ajax_community_nonce' ) );
		add_action( 'wp_ajax_pfc_toggle_follow', array( __CLASS__, 'ajax_toggle_follow' ) );
		add_action( 'wp_ajax_pfc_mark_notifications_read', array( __CLASS__, 'ajax_mark_notifications_read' ) );
		add_action( 'wp_ajax_pfc_toggle_vote', array( __CLASS__, 'ajax_toggle_vote' ) );
	}

	public static function register_svip_role(): void {
		if ( ! get_role( 'atelier_svip' ) ) {
			add_role( 'atelier_svip', 'Membre SVIP', array( 'read' => true, 'publish_topics' => true, 'publish_replies' => true ) );
		}
	}

	public static function profile_field( WP_User $user ): void {
		if ( ! current_user_can( 'edit_users' ) ) {
			return;
		}
		$value = get_user_meta( $user->ID, 'pfc_rank', true );
		?>
		<h2>Atelier — identité communautaire</h2>
		<?php wp_nonce_field( 'pfc_save_profile_rank_' . $user->ID, 'pfc_profile_rank_nonce' ); ?>
		<table class="form-table" role="presentation"><tr><th><label for="pfc_rank">Rang affiché</label></th><td><input name="pfc_rank" id="pfc_rank" value="<?php echo esc_attr( $value ); ?>" class="regular-text" placeholder="Ex. Contributeur principal" /><p class="description">Ce rang apparaît sur les sujets, réponses et profils publics.</p></td></tr></table>
		<?php
	}

	public static function save_profile_field( int $user_id ): void {
		if ( ! current_user_can( 'edit_user', $user_id ) || ! isset( $_POST['pfc_rank'], $_POST['pfc_profile_rank_nonce'] ) ) {
			return;
		}
		if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['pfc_profile_rank_nonce'] ) ), 'pfc_save_profile_rank_' . $user_id ) ) {
			return;
		}
		update_user_meta( $user_id, 'pfc_rank', sanitize_text_field( wp_unslash( $_POST['pfc_rank'] ) ) );
	}

	public static function table( string $name ): string {
		global $wpdb;
		return $wpdb->prefix . 'pfc_' . $name;
	}

	public static function ensure_schema(): void {
		global $wpdb;
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		$charset       = $wpdb->get_charset_collate();
		$notifications = self::table( 'notifications' );
		$follows       = self::table( 'follows' );
		$votes         = self::table( 'votes' );
		dbDelta( "CREATE TABLE {$notifications} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			recipient_id bigint(20) unsigned NOT NULL,
			actor_id bigint(20) unsigned NOT NULL DEFAULT 0,
			object_id bigint(20) unsigned NOT NULL,
			topic_id bigint(20) unsigned NOT NULL DEFAULT 0,
			type varchar(32) NOT NULL,
			message text NOT NULL,
			url text NOT NULL,
			is_read tinyint(1) unsigned NOT NULL DEFAULT 0,
			created_at datetime NOT NULL,
			PRIMARY KEY  (id),
			KEY recipient_read (recipient_id,is_read),
			KEY object_type (object_id,type)
		) {$charset};" );
		dbDelta( "CREATE TABLE {$follows} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			user_id bigint(20) unsigned NOT NULL,
			topic_id bigint(20) unsigned NOT NULL,
			created_at datetime NOT NULL,
			PRIMARY KEY  (id),
			UNIQUE KEY user_topic (user_id,topic_id),
			KEY topic_id (topic_id)
		) {$charset};" );
		dbDelta( "CREATE TABLE {$votes} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			user_id bigint(20) unsigned NOT NULL,
			object_id bigint(20) unsigned NOT NULL,
			created_at datetime NOT NULL,
			PRIMARY KEY  (id),
			UNIQUE KEY user_object (user_id,object_id),
			KEY object_id (object_id)
		) {$charset};" );
	}

	/** Migration unique : assainit les données PFC héritées des essais antérieurs à la version courante. */
	public static function migrate_community_data(): void {
		if ( get_option( 'pfc_community_data_version' ) === PFC_VERSION ) {
			return;
		}
		global $wpdb;
		$notifications = self::table( 'notifications' );
		$follows       = self::table( 'follows' );
		$votes         = self::table( 'votes' );
		$posts         = $wpdb->posts;
		$users         = $wpdb->users;
		$wpdb->query( "DELETE n FROM {$notifications} n LEFT JOIN {$users} recipient ON recipient.ID = n.recipient_id LEFT JOIN {$users} actor ON actor.ID = n.actor_id LEFT JOIN {$posts} object_post ON object_post.ID = n.object_id WHERE recipient.ID IS NULL OR actor.ID IS NULL OR object_post.ID IS NULL OR object_post.post_status <> 'publish'" );
		$wpdb->query( "DELETE f FROM {$follows} f LEFT JOIN {$users} u ON u.ID = f.user_id LEFT JOIN {$posts} p ON p.ID = f.topic_id WHERE u.ID IS NULL OR p.ID IS NULL OR p.post_type <> 'topic' OR p.post_status <> 'publish'" );
		$wpdb->query( "DELETE v FROM {$votes} v LEFT JOIN {$users} u ON u.ID = v.user_id LEFT JOIN {$posts} p ON p.ID = v.object_id WHERE u.ID IS NULL OR p.ID IS NULL OR p.post_status <> 'publish'" );
		update_option( 'pfc_community_data_version', PFC_VERSION, false );
	}

	public static function is_following( int $user_id, int $topic_id ): bool {
		global $wpdb;
		return (bool) $wpdb->get_var( $wpdb->prepare( 'SELECT id FROM ' . self::table( 'follows' ) . ' WHERE user_id=%d AND topic_id=%d LIMIT 1', $user_id, $topic_id ) );
	}

	public static function unread_count( int $user_id ): int {
		global $wpdb;
		return (int) $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(*) FROM ' . self::table( 'notifications' ) . ' WHERE recipient_id=%d AND is_read=0', $user_id ) );
	}

	public static function notifications( int $user_id, int $limit = 12 ): array {
		global $wpdb;
		return $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM ' . self::table( 'notifications' ) . ' WHERE recipient_id=%d ORDER BY created_at DESC, id DESC LIMIT %d', $user_id, max( 1, min( 50, $limit ) ) ) ) ?: array();
	}

	/** Supprime les données communautaires dont le destinataire ou l’acteur n’existe plus. */
	public static function cleanup_deleted_user( int $user_id ): void {
		if ( $user_id < 1 ) {
			return;
		}
		global $wpdb;
		$wpdb->query( $wpdb->prepare( 'DELETE FROM ' . self::table( 'notifications' ) . ' WHERE recipient_id=%d OR actor_id=%d', $user_id, $user_id ) );
		$wpdb->delete( self::table( 'follows' ), array( 'user_id' => $user_id ), array( '%d' ) );
		$wpdb->delete( self::table( 'votes' ), array( 'user_id' => $user_id ), array( '%d' ) );
	}

	/** Retire les compteurs et alertes liés à une contribution qui n’est plus publique. */
	public static function cleanup_trashed_object( int $post_id ): void {
		$post = get_post( $post_id );
		if ( ! $post || ! in_array( $post->post_type, array( 'topic', 'reply' ), true ) ) {
			return;
		}
		global $wpdb;
		$wpdb->delete( self::table( 'notifications' ), array( 'object_id' => $post_id ), array( '%d' ) );
		$wpdb->delete( self::table( 'votes' ), array( 'object_id' => $post_id ), array( '%d' ) );
		if ( 'topic' === $post->post_type ) {
			$wpdb->delete( self::table( 'follows' ), array( 'topic_id' => $post_id ), array( '%d' ) );
		}
	}

	public static function native_upvotes( int $post_id ): int {
		global $wpdb;
		return (int) $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(*) FROM ' . self::table( 'votes' ) . ' WHERE object_id=%d', $post_id ) );
	}

	public static function has_voted( int $user_id, int $post_id ): bool {
		global $wpdb;
		return (bool) $wpdb->get_var( $wpdb->prepare( 'SELECT id FROM ' . self::table( 'votes' ) . ' WHERE user_id=%d AND object_id=%d LIMIT 1', $user_id, $post_id ) );
	}

	public static function received_upvotes( int $user_id ): int {
		global $wpdb;
		$posts = $wpdb->posts;
		$meta  = $wpdb->postmeta;
		$meta_total = (int) $wpdb->get_var( $wpdb->prepare(
			"SELECT COALESCE(SUM(CAST(pm.meta_value AS UNSIGNED)), 0) FROM {$meta} pm INNER JOIN {$posts} p ON p.ID = pm.post_id WHERE p.post_author = %d AND p.post_status = 'publish' AND p.post_type IN ('topic', 'reply') AND pm.meta_key IN ('pfc_legacy_upvotes_count', 'pfc_native_upvotes_count')",
			$user_id
		) );
		$native_total = (int) $wpdb->get_var( $wpdb->prepare(
			'SELECT COUNT(*) FROM ' . self::table( 'votes' ) . " v INNER JOIN {$posts} p ON p.ID = v.object_id WHERE p.post_author = %d AND p.post_status = 'publish' AND p.post_type IN ('topic', 'reply')",
			$user_id
		) );
		return max( 0, $meta_total + $native_total );
	}

	private static function notify( int $recipient_id, int $actor_id, int $object_id, int $topic_id, string $type, string $message, string $url ): void {
		if ( $recipient_id < 1 || $recipient_id === $actor_id ) {
			return;
		}
		global $wpdb;
		$table = self::table( 'notifications' );
		if ( $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$table} WHERE recipient_id=%d AND object_id=%d LIMIT 1", $recipient_id, $object_id ) ) ) {
			return;
		}
		$wpdb->insert( $table, array(
			'recipient_id' => $recipient_id,
			'actor_id'     => $actor_id,
			'object_id'    => $object_id,
				'topic_id'     => $topic_id,
				'type'         => $type,
				'message'      => $message,
				'url'          => $url,
			'is_read'      => 0,
			'created_at'   => current_time( 'mysql', true ),
		), array( '%d', '%d', '%d', '%d', '%s', '%s', '%s', '%d', '%s' ) );
		self::send_email( $recipient_id, $type, $message, $url );
	}

		private static function send_email( int $recipient_id, string $type, string $message, string $url ): void {
			$recipient = get_userdata( $recipient_id );
			if ( ! $recipient || ! is_email( $recipient->user_email ) || '1' === (string) get_user_meta( $recipient_id, 'pfc_disable_email_notifications', true ) ) {
				return;
			}
			$subjects = array(
				'reply_to_topic'     => 'Atelier — nouvelle réponse à votre discussion',
				'reply_to_reply'     => 'Atelier — nouvelle réponse à votre contribution',
				'followed_topic_reply' => 'Atelier — nouvelle réponse dans une discussion suivie',
			);
			$subject = $subjects[ $type ] ?? 'Atelier — nouvelle notification';
			$body = '<p>' . esc_html( $message ) . '</p><p><a href="' . esc_url( $url ) . '">Lire la contribution dans Atelier</a></p><p style="color:#777;font-size:12px">Vous pouvez désactiver ces e-mails depuis votre espace membre.</p>';
			wp_mail( $recipient->user_email, $subject, $body, array( 'Content-Type: text/html; charset=UTF-8' ) );
		}

	public static function on_new_reply( int $reply_id, int $topic_id = 0 ): void {
		if ( ! $reply_id || ! function_exists( 'bbp_get_reply_topic_id' ) ) {
			return;
		}
					$reply = get_post( $reply_id );
			if ( ! $reply instanceof WP_Post || 'reply' !== $reply->post_type || 'publish' !== $reply->post_status ) {
				return;
			}
			$topic_id = $topic_id ?: (int) bbp_get_reply_topic_id( $reply_id );
			$actor_id = function_exists( 'bbp_get_reply_author_id' ) ? (int) bbp_get_reply_author_id( $reply_id ) : (int) get_post_field( 'post_author', $reply_id );

		$url = function_exists( 'bbp_get_reply_url' ) ? bbp_get_reply_url( $reply_id ) : get_permalink( $topic_id );
		$actor = get_userdata( $actor_id );
		$name = $actor ? $actor->display_name : 'Un membre';
		$topic_author = function_exists( 'bbp_get_topic_author_id' ) ? (int) bbp_get_topic_author_id( $topic_id ) : (int) get_post_field( 'post_author', $topic_id );
		$recipients = array();
		if ( $topic_author > 0 && $topic_author !== $actor_id ) {
			$recipients[ $topic_author ] = array( 'reply_to_topic', $name . ' a répondu à votre discussion.' );
		}
		$parent_id = function_exists( 'bbp_get_reply_to' ) ? (int) bbp_get_reply_to( $reply_id ) : (int) get_post_field( 'post_parent', $reply_id );
		if ( $parent_id ) {
			$parent_author = function_exists( 'bbp_get_reply_author_id' ) ? (int) bbp_get_reply_author_id( $parent_id ) : (int) get_post_field( 'post_author', $parent_id );
			if ( $parent_author > 0 && $parent_author !== $actor_id ) {
				$recipients[ $parent_author ] = array( 'reply_to_reply', $name . ' a répondu à votre réponse.' );
			}
		}
		global $wpdb;
		$followers = $wpdb->get_col( $wpdb->prepare( 'SELECT user_id FROM ' . self::table( 'follows' ) . ' WHERE topic_id=%d', $topic_id ) );
		foreach ( $followers as $follower_id ) {
			$follower_id = (int) $follower_id;
			if ( $follower_id > 0 && $follower_id !== $actor_id && ! isset( $recipients[ $follower_id ] ) ) {
				$recipients[ $follower_id ] = array( 'followed_topic_reply', $name . ' a ajouté une réponse à un sujet que vous suivez.' );
			}
		}
		foreach ( $recipients as $recipient_id => $notification ) {
			self::notify( (int) $recipient_id, $actor_id, $reply_id, $topic_id, $notification[0], $notification[1], $url );
		}
	}

	public static function ajax_community_nonce(): void {
		if ( ! is_user_logged_in() ) {
			wp_send_json_error( array( 'message' => 'Connexion requise.' ), 401 );
		}
		wp_send_json_success( array( 'nonce' => wp_create_nonce( 'pfc_community' ) ) );
	}

	public static function ajax_toggle_follow(): void {
		if ( ! is_user_logged_in() ) {
			wp_send_json_error( array( 'message' => 'Connexion requise.' ), 401 );
		}
					$topic_id = absint( $_POST['topic_id'] ?? 0 );
			if ( ! $topic_id || get_post_type( $topic_id ) !== 'topic' || 'publish' !== get_post_status( $topic_id ) ) {

			wp_send_json_error( array( 'message' => 'Discussion introuvable.' ), 404 );
		}
		check_ajax_referer( 'pfc_community', 'nonce' );
		global $wpdb;
		$user_id = get_current_user_id();
		$table = self::table( 'follows' );
		if ( self::is_following( $user_id, $topic_id ) ) {
			$wpdb->delete( $table, array( 'user_id' => $user_id, 'topic_id' => $topic_id ), array( '%d', '%d' ) );
			wp_send_json_success( array( 'following' => false, 'label' => 'Suivre' ) );
		}
		$wpdb->insert( $table, array( 'user_id' => $user_id, 'topic_id' => $topic_id, 'created_at' => current_time( 'mysql', true ) ), array( '%d', '%d', '%s' ) );
		wp_send_json_success( array( 'following' => true, 'label' => 'Suivi activé' ) );
	}

	public static function ajax_toggle_vote(): void {
		if ( ! is_user_logged_in() ) {
			wp_send_json_error( array( 'message' => 'Connexion requise.' ), 401 );
		}
		check_ajax_referer( 'pfc_community', 'nonce' );
		$post_id = absint( $_POST['object_id'] ?? 0 );
		$post = $post_id ? get_post( $post_id ) : null;
		if ( ! $post || ! in_array( $post->post_type, array( 'topic', 'reply' ), true ) || 'publish' !== $post->post_status ) {
			wp_send_json_error( array( 'message' => 'Contribution introuvable.' ), 404 );
		}
		$user_id = get_current_user_id();
		if ( (int) $post->post_author === $user_id ) {
			wp_send_json_error( array( 'message' => 'Vous ne pouvez pas voter pour votre propre contribution.' ), 403 );
		}
		global $wpdb;
		$table = self::table( 'votes' );
		if ( self::has_voted( $user_id, $post_id ) ) {
			$wpdb->delete( $table, array( 'user_id' => $user_id, 'object_id' => $post_id ), array( '%d', '%d' ) );
			$active = false;
		} else {
			$wpdb->insert( $table, array( 'user_id' => $user_id, 'object_id' => $post_id, 'created_at' => current_time( 'mysql', true ) ), array( '%d', '%d', '%s' ) );
			$active = true;
		}
		$count = absint( get_post_meta( $post_id, 'pfc_legacy_upvotes_count', true ) ) + absint( get_post_meta( $post_id, 'pfc_native_upvotes_count', true ) ) + self::native_upvotes( $post_id );
		wp_send_json_success( array( 'voted' => $active, 'count' => $count, 'label' => $active ? 'Vote utile ajouté' : 'Voter utile' ) );
	}

	public static function ajax_mark_notifications_read(): void {
		if ( ! is_user_logged_in() ) {
			wp_send_json_error( array( 'message' => 'Connexion requise.' ), 401 );
		}
		check_ajax_referer( 'pfc_community', 'nonce' );
		global $wpdb;
		$wpdb->update( self::table( 'notifications' ), array( 'is_read' => 1 ), array( 'recipient_id' => get_current_user_id(), 'is_read' => 0 ), array( '%d' ), array( '%d', '%d' ) );
		wp_send_json_success( array( 'unread' => 0 ) );
	}
}

PFC_Community::init();
