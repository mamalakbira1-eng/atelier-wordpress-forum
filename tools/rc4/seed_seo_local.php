<?php
$root = dirname( __DIR__ ) . '/wp';
if ( ! defined( 'ABSPATH' ) ) { define( 'WP_USE_THEMES', false ); require $root . '/wp-load.php'; }
$member = get_user_by( 'login', 'cdc_member' );
$admin = get_user_by( 'login', 'cdc_admin' );
if ( $admin ) { wp_set_current_user( $admin->ID ); }
$forum = (int) ( get_posts( array( 'post_type' => 'forum', 'post_status' => 'publish', 'numberposts' => 1, 'fields' => 'ids' ) )[0] ?? 0 );
if ( ! $member || ! $forum || ! function_exists( 'bbp_insert_topic' ) ) { fwrite( STDERR, "prerequisite missing\n" ); exit( 1 ); }
function rc4_topic_by_title( string $title ) { $ids = get_posts( array( 'post_type' => 'topic', 'title' => $title, 'post_status' => array( 'publish', 'pending' ), 'numberposts' => 1, 'fields' => 'ids' ) ); return (int) ( $ids[0] ?? 0 ); }
$published = rc4_topic_by_title( 'مصادر عربية للتحقق من المعرفة' );
if ( ! $published ) { $published = bbp_insert_topic( array( 'post_parent' => $forum, 'post_author' => $member->ID, 'post_title' => 'مصادر عربية للتحقق من المعرفة', 'post_content' => 'هذا موضوع عربي اصطناعي لاختبار اللغة والاتجاه والبيانات المنظمة.', 'post_status' => bbp_get_public_status_id() ) ); }
$pending = rc4_topic_by_title( 'Sujet pending invisible pour test' );
if ( ! $pending ) { $pending = bbp_insert_topic( array( 'post_parent' => $forum, 'post_author' => $member->ID, 'post_title' => 'Sujet pending invisible pour test', 'post_content' => 'Contenu non public de test.', 'post_status' => bbp_get_pending_status_id() ) ); }
if ( is_wp_error( $published ) || is_wp_error( $pending ) ) { fwrite( STDERR, "topic creation failed\n" ); exit( 1 ); }
wp_update_post( array( 'ID' => (int) $published, 'post_status' => bbp_get_public_status_id(), 'post_name' => 'sources-arabes-connaissance' ) );
wp_update_post( array( 'ID' => (int) $pending, 'post_status' => bbp_get_pending_status_id(), 'post_name' => 'sujet-pending-invisible-pour-test' ) );
echo wp_json_encode( array( 'published' => (int) $published, 'pending' => (int) $pending, 'published_url' => bbp_get_topic_permalink( $published ), 'pending_url' => get_permalink( $pending ) ) ) . PHP_EOL;
