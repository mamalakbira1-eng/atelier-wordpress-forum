<?php
require dirname( __DIR__ ) . '/wp/wp-load.php';
global $wpdb;
$job = absint( $argv[1] ?? 0 );
$row = $wpdb->get_row( $wpdb->prepare( "SELECT status,last_error FROM {$wpdb->prefix}pfc_import_jobs WHERE id=%d", $job ), ARRAY_A );
$posts = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_key='_pfc_import_job' AND meta_value=%s", (string) $job ) );
$users = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->usermeta} WHERE meta_key='_pfc_import_job' AND meta_value=%s", (string) $job ) );
$rolled_back = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->prefix}pfc_import_items WHERE job_id=%d AND action_name='rolled_back'", $job ) );
$pass = 'rolled_back' === ( $row['status'] ?? '' ) && 0 === $posts && 0 === $users && $rolled_back >= 1;
$out = array( 'objective' => 'IMP-CL-05', 'target' => 'interruption déterministe après 2 objets puis rollback complet', 'observed' => array( 'job_after_rollback' => $row, 'objects_marked_by_job_remaining' => $posts + $users, 'rolled_back_audit_items' => $rolled_back ), 'exit_code' => $pass ? 0 : 1, 'status' => $pass ? 'PASS' : 'FAIL', 'blocked_reason' => null );
file_put_contents( dirname( __DIR__ ) . '/proofs-rc4-local/IMP-CL-05-import-interruption.json', wp_json_encode( $out, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE ) . PHP_EOL );
echo wp_json_encode( $out, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE ) . PHP_EOL;
exit( $out['exit_code'] );
