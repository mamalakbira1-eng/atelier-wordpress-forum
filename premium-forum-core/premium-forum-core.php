<?php
/**
 * Plugin Name: Premium Forum Core
 * Description: SEO LLM-first, profils, compteurs historiques et migration CSV sécurisée pour bbPress.
 * Version: 0.4.19
 * Requires at least: 6.4
 * Requires PHP: 8.1
 * Requires Plugins: bbpress
 * Text Domain: premium-forum-core
 */

defined( 'ABSPATH' ) || exit;

define( 'PFC_VERSION', '0.4.19' );
define( 'PFC_FILE', __FILE__ );
define( 'PFC_DIR', plugin_dir_path( __FILE__ ) );
define( 'PFC_URL', plugin_dir_url( __FILE__ ) );

require_once PFC_DIR . 'includes/class-pfc-importer.php';
require_once PFC_DIR . 'includes/class-pfc-admin.php';
require_once PFC_DIR . 'includes/class-pfc-seo.php';
require_once PFC_DIR . 'includes/class-pfc-community.php';
require_once PFC_DIR . 'includes/class-pfc-registration.php';
require_once PFC_DIR . 'includes/class-pfc-moderation.php';

/**
 * Staging hardening: Atelier does not use the legacy XML-RPC API or
 * application passwords. Disable both attack surfaces at WordPress level.
 */
add_filter( 'xmlrpc_enabled', '__return_false' );
add_filter( 'wp_is_application_passwords_available', '__return_false' );
add_filter( 'wp_is_application_passwords_available_for_user', '__return_false' );
add_filter( 'rest_endpoints', static function( array $endpoints ): array {
	if ( ! current_user_can( 'list_users' ) ) {
		unset( $endpoints['/wp/v2/users'], $endpoints['/wp/v2/users/(?P<id>[\\d]+)'] );
	}
	return $endpoints;
} );
add_action( 'init', static function() {
	if ( defined( 'XMLRPC_REQUEST' ) && XMLRPC_REQUEST ) {
		status_header( 403 );
		header( 'Content-Type: text/plain; charset=utf-8' );
		exit( 'XML-RPC is disabled.' );
	}
}, 0 );

/**
 * Défense navigateur à faible risque pour les pages publiques et l’authentification.
 * Une CSP n’est volontairement pas imposée ici : WordPress, bbPress et LiteSpeed
 * utilisent encore des scripts/styles dynamiques qui exigent une migration dédiée.
 */
function pfc_send_browser_security_headers() {
	header( 'X-Content-Type-Options: nosniff', true );
	header( 'X-Frame-Options: SAMEORIGIN', true );
	header( 'Referrer-Policy: strict-origin-when-cross-origin', true );
	header( 'Permissions-Policy: camera=(), geolocation=(), microphone=(), payment=(), usb=()', true );
}

add_action( 'send_headers', static function() {
	if ( wp_doing_ajax() || is_admin() ) {
		return;
	}
	pfc_send_browser_security_headers();
} );

// wp-login.php does not run the public send_headers hook.
add_action( 'login_init', 'pfc_send_browser_security_headers', 0 );

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
