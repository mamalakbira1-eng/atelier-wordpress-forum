<?php
/**
 * PFC Moderation — file commune des sujets et réponses en attente.
 * Les modérateurs et administrateurs peuvent valider ; seul l’administrateur
 * conserve les réglages globaux et la gestion des comptes.
 */
defined( 'ABSPATH' ) || exit;

final class PFC_Moderation {
	public static function init(): void {
		add_action( 'init', array( __CLASS__, 'register_role' ), 6 );
		add_filter( 'wp_insert_post_data', array( __CLASS__, 'hold_new_contributions' ), 20, 2 );
		add_action( 'admin_menu', array( __CLASS__, 'menu' ) );
		add_action( 'admin_post_pfc_moderate', array( __CLASS__, 'handle_action' ) );
	}

	public static function register_role(): void {
		$caps = array(
			'read' => true,
			'pfc_moderate' => true,
			'edit_others_topics' => true,
			'edit_others_replies' => true,
			'publish_topics' => true,
			'publish_replies' => true,
			'delete_others_topics' => true,
			'delete_others_replies' => true,
		);
		$role = get_role( 'atelier_moderator' );
		if ( ! $role ) {
			add_role( 'atelier_moderator', 'Modérateur Atelier', $caps );
		} else {
			foreach ( $caps as $cap => $grant ) { $role->add_cap( $cap, $grant ); }
		}
		$admin = get_role( 'administrator' );
		if ( $admin ) { $admin->add_cap( 'pfc_moderate' ); }
	}

	public static function can_moderate(): bool {
		return current_user_can( 'manage_options' ) || current_user_can( 'pfc_moderate' );
	}

	public static function hold_new_contributions( array $data, array $postarr ): array {
		if ( ! in_array( $data['post_type'] ?? '', array( 'topic', 'reply' ), true ) || self::can_moderate() || wp_doing_cron() ) {
			return $data;
		}
		if ( in_array( $data['post_status'] ?? '', array( 'publish', 'pending' ), true ) ) {
			$data['post_status'] = 'pending';
		}
		return $data;
	}

	public static function menu(): void {
		add_menu_page( 'Modération Atelier', 'À valider', 'pfc_moderate', 'pfc-moderation', array( __CLASS__, 'page' ), 'dashicons-visibility', 59 );
	}

	public static function page(): void {
		if ( ! self::can_moderate() ) { wp_die( 'Accès refusé.' ); }
		$items = get_posts( array(
			'post_type' => array( 'topic', 'reply' ),
			'post_status' => 'pending',
			'posts_per_page' => 100,
			'orderby' => 'date',
			'order' => 'ASC',
		) );
		$notice = sanitize_text_field( wp_unslash( $_GET['pfc_moderation_notice'] ?? '' ) );
		echo '<div class="wrap pfc-admin"><h1>À valider</h1><p class="description">Une seule file pour relire les sujets et les réponses avant leur publication. Les administrateurs voient tout ; les modérateurs disposent uniquement des actions éditoriales.</p>';
		if ( $notice ) { echo '<div class="notice notice-success"><p>' . esc_html( $notice ) . '</p></div>'; }
		if ( ! $items ) { echo '<section class="pfc-card"><h2>La file est claire.</h2><p>Aucune contribution n’attend actuellement une validation.</p></section></div>'; return; }
		echo '<section class="pfc-card"><table class="widefat striped"><thead><tr><th>Type</th><th>Contribution</th><th>Auteur</th><th>Date</th><th>Actions</th></tr></thead><tbody>';
		foreach ( $items as $item ) {
			$author = get_userdata( $item->post_author );
			$type = 'topic' === $item->post_type ? 'Sujet' : 'Réponse';
				echo '<tr><td>' . esc_html( $type ) . '</td><td><strong>' . esc_html( wp_trim_words( $item->post_title ?: wp_strip_all_tags( $item->post_content ), 18 ) ) . '</strong><p>' . esc_html( wp_trim_words( $item->post_content, 28 ) ) . '</p></td><td>' . esc_html( $author ? $author->display_name : 'Inconnu' ) . '</td><td>' . esc_html( get_the_date( 'd/m/Y H:i', $item ) ) . '</td><td>' . self::action_form( $item->ID, 'approve', 'Publier' ) . self::action_form( $item->ID, 'reject', 'Refuser' ) . self::action_form( $item->ID, 'trash', 'Supprimer' ) . '</td></tr>';
		}
		echo '</tbody></table></section></div>';
	}

	private static function action_form( int $post_id, string $action, string $label ): string {
		$class = 'button button-small' . ( 'trash' === $action ? ' button-link-delete' : '' );
		return '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" style="display:inline-block;margin:0 6px 6px 0;">'
			. '<input type="hidden" name="action" value="pfc_moderate">'
			. '<input type="hidden" name="post_id" value="' . esc_attr( (string) $post_id ) . '">'
			. '<input type="hidden" name="decision" value="' . esc_attr( $action ) . '">'
			. wp_nonce_field( 'pfc_moderate_' . $post_id, '_wpnonce', false, false )
			. '<button type="submit" class="' . esc_attr( $class ) . '">' . esc_html( $label ) . '</button></form>';
	}

	public static function handle_action(): void {
		if ( ! self::can_moderate() ) { wp_die( 'Accès refusé.' ); }
		if ( 'POST' !== strtoupper( $_SERVER['REQUEST_METHOD'] ?? '' ) ) { wp_die( 'Méthode de requête invalide.' ); }
		$post_id = absint( $_POST['post_id'] ?? 0 );
		$decision = sanitize_key( wp_unslash( $_POST['decision'] ?? '' ) );
		check_admin_referer( 'pfc_moderate_' . $post_id );
		$post = get_post( $post_id );
		if ( ! $post || ! in_array( $post->post_type, array( 'topic', 'reply' ), true ) ) { wp_die( 'Contribution introuvable.' ); }
		if ( 'pending' !== $post->post_status || ! current_user_can( 'edit_post', $post_id ) ) { wp_die( 'Cette contribution ne peut plus être modérée.' ); }
		$status = array( 'approve' => 'publish', 'reject' => 'draft', 'trash' => 'trash' )[ $decision ] ?? '';
		if ( ! $status ) { wp_die( 'Action inconnue.' ); }
		wp_update_post( array( 'ID' => $post_id, 'post_status' => $status ) );
		wp_safe_redirect( add_query_arg( 'pfc_moderation_notice', rawurlencode( 'Action appliquée.' ), admin_url( 'admin.php?page=pfc-moderation' ) ) );
		exit;
	}
}

PFC_Moderation::init();
