<?php
require dirname( __DIR__ ) . '/wp/wp-load.php';
global $wpdb;
$rows = $wpdb->get_results( "SELECT ID,post_type,post_status,post_name,post_parent,post_title FROM {$wpdb->posts} WHERE post_name LIKE 'cdc-topic%' ORDER BY ID", ARRAY_A );
echo wp_json_encode( $rows, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE ) . PHP_EOL;
