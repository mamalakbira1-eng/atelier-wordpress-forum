<?php
require dirname( __DIR__ ) . '/wp/wp-load.php';
$deleted = array( 'users' => array(), 'pages' => array() );
$user = get_user_by( 'login', 'rc4_local_admin' );
if ( $user ) { require_once ABSPATH . 'wp-admin/includes/user.php'; wp_delete_user( $user->ID ); $deleted['users'][] = (int) $user->ID; }
foreach ( array( 'discussions', 'espaces', 'methodes', 'mon-espace' ) as $slug ) {
	$page = get_page_by_path( $slug, OBJECT, 'page' );
	if ( $page && in_array( (int) $page->ID, array( 200, 201, 202, 203 ), true ) ) { wp_delete_post( $page->ID, true ); $deleted['pages'][] = (int) $page->ID; }
}
$remaining_user = get_user_by( 'login', 'rc4_local_admin' );
$remaining_pages = 0;
foreach ( array( 'discussions', 'espaces', 'methodes', 'mon-espace' ) as $slug ) { $page = get_page_by_path( $slug, OBJECT, 'page' ); if ( $page && in_array( (int) $page->ID, array( 200, 201, 202, 203 ), true ) ) { $remaining_pages++; } }
$out = array( 'target' => 'RC4 fixtures only', 'deleted' => $deleted, 'remaining_rc4_admin' => $remaining_user ? 1 : 0, 'remaining_rc4_pages' => $remaining_pages, 'exit_code' => ( ! $remaining_user && 0 === $remaining_pages ) ? 0 : 4 );
echo wp_json_encode( $out, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE ) . PHP_EOL;
exit( $out['exit_code'] );
