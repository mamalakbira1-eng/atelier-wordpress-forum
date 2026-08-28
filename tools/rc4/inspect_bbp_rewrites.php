<?php
require dirname( __DIR__ ) . '/wp/wp-load.php';
$rules = (array) get_option( 'rewrite_rules' );
foreach ( array_keys( $rules ) as $rule ) { if ( false !== strpos( $rule, 'topic' ) || false !== strpos( $rule, 'forum' ) ) { echo $rule . PHP_EOL; } }
