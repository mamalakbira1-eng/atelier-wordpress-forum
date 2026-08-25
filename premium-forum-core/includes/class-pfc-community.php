<?php
/**
 * PFC Community — notifications internes, suivi des sujets et statistiques membres.
 * Les votes importés restent des compteurs agrégés ; aucune identité de votant n'est exposée.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class PFC_Community {
	public static function init(): void {
		add_action( 'init', array( __CLASS__, 'register_svip_role' ), 5 );
		add_action( 'init', array( __CLASS__, 'ensure_schema' ), 20 );
		add_action( 'show_user_profile', array( __CLASS__, 'profile_field' ) );
		add_action( 'edit_user_profile', array( __CLASS__, 'profile_field' ) );
		add_action( 'personal_options_update', array( __CLASS__, 'save_profile_field' ) );
		add_action( 'edit_user_profile_update', array( __CLASS__, 'save_profile_field' ) );
		add_action( 'bbp_new_reply', array( __CLASS__, 'on_new_reply' ), 10, 5 );
			add_action( 'wp_ajax_pfc_community_nonce', array( __CLASS__, 'ajax_community_nonce' ) );
			add_action( 'wp_ajax_pfc_toggle_follow', array( __CLASS__, 'ajax_toggle_follow' ) );
			add_action( 'wp_ajax_pfc_mark_notifications_read', array( __CLASS__, 'ajax_mark_notifications_read' ) );
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
		<table class="form-table" role="presentation"><tr><th><label for="pfc_rank">Rang affiché</label></th><td><input name="pfc_rank" id="pfc_rank" value="<?php echo esc_attr( $value ); ?>" class="regular-text" placeholder="Ex. Contributeur principal" /><p class="description">Ce rang apparaît sur les sujets, réponses et profils publics.</p></td></tr></table>
		<?php
	}

	public static function save_profile_field( int $user_id ): void {
		if ( ! current_user_can( 'edit_user', $user_id ) || ! isset( $_POST['pfc_rank'] ) ) {
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
		$charset = $wpdb->get_charset_collate();
		$notifications = self::table( 'notifications' );
		$follows = self::table( 'follows' );
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

	public static function received_upvotes( int $user_id ): int {
		$ids = get_posts( array(
			'post_type'      => array( 'topic', 'reply' ),
			'post_status'    => 'publish',
			'author'         => $user_id,
			'posts_per_page' => 100,
			'fields'         => 'ids',
			'no_found_rows'  => true,
		) );
		$total = 0;
		foreach ( $ids as $id ) {
			$total += absint( get_post_meta( $id, 'pfc_legacy_upvotes_count', true ) );
			$total += absint( get_post_meta( $id, 'pfc_native_upvotes_count', true ) );
		}
		return $total;
	}

	private static function notify( int $recipient_id, int $actor_id, int $object_id, int $topic_id, string $type, string $message, string $url ): void {
		if ( $recipient_id < 1 || $recipient_id === $actor_id ) {
			return;
		}
		global $wpdb;
		$wpdb->insert( self::table( 'notifications' ), array(
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
	}

	public static function on_new_reply( int $reply_id, int $topic_id = 0 ): void {
		if ( ! $reply_id || ! function_exists( 'bbp_get_reply_topic_id' ) ) {
			return;
		}
		$topic_id = $topic_id ?: (int) bbp_get_reply_topic_id( $reply_id );
		$actor_id = function_exists( 'bbp_get_reply_author_id' ) ? (int) bbp_get_reply_author_id( $reply_id ) : (int) get_post_field( 'post_author', $reply_id );
		$url = function_exists( 'bbp_get_reply_url' ) ? bbp_get_reply_url( $reply_id ) : get_permalink( $topic_id );
		$actor = get_userdata( $actor_id );
		$name = $actor ? $actor->display_name : 'Un membre';
		$topic_author = function_exists( 'bbp_get_topic_author_id' ) ? (int) bbp_get_topic_author_id( $topic_id ) : (int) get_post_field( 'post_author', $topic_id );
		self::notify( $topic_author, $actor_id, $reply_id, $topic_id, 'reply_to_topic', $name . ' a répondu à votre discussion.', $url );
		$parent_id = function_exists( 'bbp_get_reply_to' ) ? (int) bbp_get_reply_to( $reply_id ) : (int) get_post_field( 'post_parent', $reply_id );
		if ( $parent_id ) {
			$parent_author = function_exists( 'bbp_get_reply_author_id' ) ? (int) bbp_get_reply_author_id( $parent_id ) : (int) get_post_field( 'post_author', $parent_id );
			self::notify( $parent_author, $actor_id, $reply_id, $topic_id, 'reply_to_reply', $name . ' a répondu à votre réponse.', $url );
		}
		global $wpdb;
		$followers = $wpdb->get_col( $wpdb->prepare( 'SELECT user_id FROM ' . self::table( 'follows' ) . ' WHERE topic_id=%d', $topic_id ) );
		foreach ( $followers as $follower_id ) {
			self::notify( (int) $follower_id, $actor_id, $reply_id, $topic_id, 'followed_topic_reply', $name . ' a ajouté une réponse à un sujet que vous suivez.', $url );
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
		if ( ! $topic_id || get_post_type( $topic_id ) !== 'topic' ) {
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
