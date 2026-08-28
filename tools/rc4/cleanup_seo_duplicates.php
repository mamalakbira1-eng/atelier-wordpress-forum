<?php
require dirname( __DIR__ ) . '/wp/wp-load.php';
global $wpdb;
$targets = array(
	'CDC topic' => array( 'slug' => 'cdc-topic', 'status' => 'publish' ),
	'مصادر عربية للتحقق من المعرفة' => array( 'slug' => 'sources-arabes-connaissance', 'status' => 'publish' ),
	'Sujet pending invisible pour test' => array( 'slug' => 'sujet-pending-invisible-pour-test', 'status' => 'pending' ),
);
$deleted = array();
foreach ( $targets as $title => $desired ) {
	$ids = $wpdb->get_col( $wpdb->prepare( "SELECT ID FROM {$wpdb->posts} WHERE post_type='topic' AND post_title=%s AND post_status IN ('publish','pending','draft','trash') ORDER BY (post_status=%s) DESC, (post_name=%s) DESC, ID ASC", $title, $desired['status'], $desired['slug'] ) );
	$keep = (int) ( $ids[0] ?? 0 );
	foreach ( array_slice( $ids, 1 ) as $id ) { wp_delete_post( (int) $id, true ); $deleted[] = (int) $id; }
	if ( $keep ) { wp_update_post( array( 'ID' => $keep, 'post_status' => $desired['status'], 'post_name' => $desired['slug'] ) ); }
}
flush_rewrite_rules( false );
echo wp_json_encode( array( 'deleted' => $deleted, 'exit_code' => 0 ), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE ) . PHP_EOL;
