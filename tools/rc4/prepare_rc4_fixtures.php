<?php
$root = dirname( __DIR__ ) . '/wp';
define( 'WP_USE_THEMES', false );
require $root . '/wp-load.php';
function rc4_user( string $login, string $role ): WP_User {
	$user = get_user_by( 'login', $login );
	if ( ! $user ) {
		$id = wp_insert_user( array( 'user_login' => $login, 'user_pass' => 'RC4-local-only-password!', 'user_email' => $login . '@example.invalid', 'role' => $role, 'display_name' => $login ) );
		if ( is_wp_error( $id ) ) { throw new RuntimeException( $id->get_error_message() ); }
		$user = get_user_by( 'id', $id );
	} else { wp_set_password( 'RC4-local-only-password!', $user->ID ); }
	return $user;
}
$admin = rc4_user( 'cdc_admin', 'administrator' );
$member = rc4_user( 'cdc_member', 'subscriber' );
$moderator = rc4_user( 'cdc_moderator', 'subscriber' );
wp_set_current_user( $admin->ID );
if ( class_exists( 'PFC_Moderation' ) ) { PFC_Moderation::register_role(); } $moderator->set_role( 'atelier_moderator' );
$forums = get_posts( array( 'post_type' => function_exists( 'bbp_get_forum_post_type' ) ? bbp_get_forum_post_type() : 'forum', 'post_status' => 'publish', 'numberposts' => 1, 'fields' => 'ids' ) );
$forum = (int) ( $forums[0] ?? 0 );
if ( ! $forum && function_exists( 'bbp_insert_forum' ) ) { $forum = (int) bbp_insert_forum( array( 'post_title' => 'CDC Forum', 'post_content' => 'Forum synthétique RC4.', 'post_status' => bbp_get_public_status_id(), 'post_author' => $admin->ID ) ); }
if ( ! $forum ) { throw new RuntimeException( 'Forum bbPress indisponible.' ); }
global $wpdb;
$wpdb->query( "UPDATE {$wpdb->posts} SET post_name = CONCAT('cdc-pending-', ID) WHERE post_type = 'topic' AND post_status <> 'publish' AND post_name = 'cdc-topic'" );
$topic_ids = get_posts( array( 'post_type' => function_exists( 'bbp_get_topic_post_type' ) ? bbp_get_topic_post_type() : 'topic', 'name' => 'cdc-topic', 'post_status' => 'publish', 'numberposts' => 1, 'fields' => 'ids' ) );
if ( empty( $topic_ids ) && function_exists( 'bbp_insert_topic' ) ) { $topic_ids[] = (int) bbp_insert_topic( array( 'post_parent' => $forum, 'post_author' => $member->ID, 'post_title' => 'CDC topic', 'post_name' => 'cdc-topic', 'post_content' => 'Sujet français synthétique pour la validation SEO.', 'post_status' => bbp_get_public_status_id() ) ); }
if ( ! empty( $topic_ids ) ) { wp_update_post( array( 'ID' => (int) $topic_ids[0], 'post_status' => bbp_get_public_status_id(), 'post_name' => 'cdc-topic' ) ); }
if ( empty( $topic_ids ) ) { throw new RuntimeException( 'Topic bbPress indisponible.' ); }
$out = array( 'admin' => $admin->ID, 'member' => $member->ID, 'moderator' => $moderator->ID, 'forum' => $forum, 'topic_fr' => (int) $topic_ids[0], 'pages' => array() );
foreach ( array( 'discussions' => 'Discussions', 'espaces' => 'Espaces', 'methodes' => 'Méthodes', 'mon-espace' => 'Mon espace' ) as $slug => $title ) { $page = get_page_by_path( $slug, OBJECT, 'page' ); if ( ! $page ) { $id = wp_insert_post( array( 'post_title' => $title, 'post_name' => $slug, 'post_status' => 'publish', 'post_type' => 'page' ), true ); if ( is_wp_error( $id ) ) { throw new RuntimeException( $id->get_error_message() ); } $page = get_post( $id ); } $out['pages'][ $slug ] = (int) $page->ID; }
flush_rewrite_rules( false );
echo wp_json_encode( $out, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE ) . PHP_EOL;
