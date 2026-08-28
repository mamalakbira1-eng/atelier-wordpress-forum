<?php
$root = dirname( __DIR__ ) . '/wp';
define( 'WP_USE_THEMES', false );
define( 'PFC_LOCAL_TESTING', true );
require $root . '/wp-load.php';
$admins = get_users( array( 'role' => 'administrator', 'number' => 1 ) );
$admin  = $admins[0] ?? null;
if ( ! $admin ) {
	$id = wp_insert_user( array( 'user_login' => 'rc4_local_admin', 'user_pass' => wp_generate_password( 32, true, true ), 'user_email' => 'rc4-local-admin@example.invalid', 'role' => 'administrator' ) );
	if ( is_wp_error( $id ) ) { fwrite( STDERR, "admin absent: " . $id->get_error_message() . "\n" ); exit( 1 ); }
	$admin = get_user_by( 'id', $id );
}
wp_set_current_user( $admin->ID );
$mode = $argv[1] ?? '';
$dir = '/home/ubuntu/atelier-cdc-20/proofs/import-interrupt';
$source = '/home/ubuntu/atelier-wordpress-forum-rc3/test-fixtures/valid';
if ( 'prepare' === $mode ) {
	wp_mkdir_p( $dir );
	foreach ( array( 'users', 'forums', 'topics', 'replies' ) as $type ) { copy( $source . '/' . $type . '.csv', $dir . '/' . $type . '.csv' ); }
	global $wpdb;
	$now = current_time( 'mysql', true );
	$wpdb->insert( $wpdb->prefix . 'pfc_import_jobs', array( 'status' => 'uploaded', 'created_by' => $admin->ID, 'created_at' => $now, 'updated_at' => $now, 'source_dir' => $dir ), array( '%s', '%d', '%s', '%s', '%s' ) );
	echo (int) $wpdb->insert_id . PHP_EOL; exit( 0 );
}
$job = absint( $argv[2] ?? 0 );
$action = 'dry' === $mode ? 'dry_run' : ( 'execute' === $mode ? 'execute_import' : 'rollback_import' );
if ( 'execute' === $mode ) { add_filter( 'pfc_local_test_interrupt_after', static function() { return 2; } ); }
$_POST = array( 'job_id' => $job, '_wpnonce' => wp_create_nonce( 'pfc_' . $action ) );
$_REQUEST = $_POST;
if ( 'dry' === $mode ) { PFC_Importer::dry_run(); }
if ( 'execute' === $mode ) { PFC_Importer::execute_import(); }
if ( 'rollback' === $mode ) { PFC_Importer::rollback_import(); }
fwrite( STDERR, "unknown action\n" ); exit( 2 );
