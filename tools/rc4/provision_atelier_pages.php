<?php
/**
 * Provisioning local/initial d’Atelier. Le script est idempotent et ne supprime
 * ni ne remplace jamais une page déjà présente.
 */
define( 'WP_USE_THEMES', false );
require dirname( __DIR__, 3 ) . '/atelier-cdc-20/wp/wp-load.php';
$pages = array(
	'discussions' => 'Discussions',
	'espaces'     => 'Espaces',
	'methodes'    => 'Méthodes',
	'mon-espace'  => 'Mon espace',
);
$result = array();
foreach ( $pages as $slug => $title ) {
	$page = get_page_by_path( $slug, OBJECT, 'page' );
	if ( $page ) {
		$result[ $slug ] = array( 'action' => 'existing', 'id' => (int) $page->ID );
		continue;
	}
	$id = wp_insert_post( array( 'post_title' => $title, 'post_name' => $slug, 'post_status' => 'publish', 'post_type' => 'page' ), true );
	$result[ $slug ] = is_wp_error( $id ) ? array( 'action' => 'error', 'error' => $id->get_error_message() ) : array( 'action' => 'created', 'id' => (int) $id );
}
$pass = true;
foreach ( $pages as $slug => $title ) { $pass = $pass && (bool) get_page_by_path( $slug, OBJECT, 'page' ); }
$out = array( 'objective' => 'SEC-CL-05', 'target' => 'route mon-espace résolue et provisioning idempotent de 4 pages éditoriales', 'observed' => $result, 'exit_code' => $pass ? 0 : 1, 'status' => $pass ? 'PASS' : 'FAIL', 'blocked_reason' => null );
echo wp_json_encode( $out, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE ) . PHP_EOL;
exit( $out['exit_code'] );
