<?php
$base = 'http://127.0.0.1:8090';
foreach ( array( 'cdc_admin', 'cdc_member' ) as $login ) {
	$jar = sys_get_temp_dir() . '/rc4-login-' . $login . '.txt';
	$post = http_build_query( array( 'log' => $login, 'pwd' => 'RC4-local-only-password!', 'wp-submit' => 'Log In', 'redirect_to' => $base . '/mon-espace/', 'testcookie' => '1' ) );
	$ch = curl_init( $base . '/wp-login.php' ); curl_setopt_array( $ch, array( CURLOPT_POST => true, CURLOPT_POSTFIELDS => $post, CURLOPT_RETURNTRANSFER => true, CURLOPT_FOLLOWLOCATION => false, CURLOPT_COOKIEJAR => $jar, CURLOPT_HEADER => true ) ); $raw = (string) curl_exec( $ch ); $code = (int) curl_getinfo( $ch, CURLINFO_HTTP_CODE ); curl_close( $ch );
	echo json_encode( array( 'login' => $login, 'status' => $code, 'cookie_header' => false !== strpos( $raw, 'wordpress_logged_in_' ), 'jar_exists' => is_file( $jar ), 'raw_headers' => substr( $raw, 0, (int) strpos( $raw, "\r\n\r\n" ) ) ), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT ) . PHP_EOL;
	@unlink( $jar );
}
