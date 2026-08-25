<?php
/**
 * Plugin Name: Premium Forum Core
 * Description: SEO LLM-first, profils, compteurs historiques et migration CSV sécurisée pour bbPress.
 * Version: 0.4.2
 * Requires at least: 6.4
 * Requires PHP: 8.1
 * Requires Plugins: bbpress
 * Text Domain: premium-forum-core
 */

defined( 'ABSPATH' ) || exit;

define( 'PFC_VERSION', '0.4.2' );
define( 'PFC_FILE', __FILE__ );
define( 'PFC_DIR', plugin_dir_path( __FILE__ ) );
define( 'PFC_URL', plugin_dir_url( __FILE__ ) );

require_once PFC_DIR . 'includes/class-pfc-importer.php';
require_once PFC_DIR . 'includes/class-pfc-admin.php';
require_once PFC_DIR . 'includes/class-pfc-seo.php';
require_once PFC_DIR . 'includes/class-pfc-community.php';
require_once PFC_DIR . 'includes/class-pfc-registration.php';
require_once PFC_DIR . 'includes/class-pfc-moderation.php';

register_activation_hook( __FILE__, array( 'PFC_Importer', 'activate' ) );

add_action( 'plugins_loaded', static function() {
	if ( ! function_exists( 'bbp_get_forum_post_type' ) ) {
		add_action( 'admin_notices', static function() {
			echo '<div class="notice notice-warning"><p><strong>Premium Forum Core</strong> nécessite bbPress.</p></div>';
		} );
		return;
	}

	PFC_Importer::ensure_schema();
	PFC_Importer::init();
	PFC_Admin::init();
			PFC_SEO::init();
			PFC_Registration::init();
			PFC_Moderation::init();

} );
