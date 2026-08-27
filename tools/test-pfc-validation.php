<?php
// Harnais local sans WordPress : vérifie uniquement le parseur/validateur CSV.
const ABSPATH = '/tmp/atelier-wp-stub/';
function trailingslashit(string $v): string { return rtrim($v, '/\\') . '/'; }
function sanitize_key($v): string { return strtolower(preg_replace('/[^a-z0-9_\-]/', '', (string) $v)); }
function absint($v): int { return abs((int) $v); }
function wp_unslash($v) { return $v; }
function is_email($v): bool { return (bool) filter_var($v, FILTER_VALIDATE_EMAIL); }
function wp_timezone(): DateTimeZone { return new DateTimeZone('UTC'); }
function apply_filters($tag, $value, ...$args) { return $value; }
function wp_json_encode($v): string { return json_encode($v, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); }
class StubWpdb {
    public string $prefix = 'wp_';
    public string $last_error = '';
    public function insert(...$args): bool { return true; }
}
$wpdb = new StubWpdb();
require_once __DIR__ . '/../premium-forum-core/includes/class-pfc-importer.php';
$method = new ReflectionMethod(PFC_Importer::class, 'validate_job');
$method->setAccessible(true);
$validDir = $argv[1] ?? '';
$invalidDir = $argv[2] ?? '';
if (!$validDir || !$invalidDir) { fwrite(STDERR, "Usage: php test-pfc-validation.php VALID_DIR INVALID_DIR\n"); exit(2); }
$valid = $method->invoke(null, 101, $validDir);
$invalid = $method->invoke(null, 102, $invalidDir);
$result = [
  'valid_pack' => [
    'files' => $valid['files'], 'rows' => $valid['rows'],
    'errors' => count($valid['errors']), 'warnings' => count($valid['warnings']),
    'counts' => $valid['counts'],
  ],
  'invalid_pack' => [
    'files' => $invalid['files'], 'rows' => $invalid['rows'],
    'errors' => count($invalid['errors']), 'first_errors' => array_slice($invalid['errors'], 0, 6),
  ],
  'assertions' => [
    'valid_pack_has_no_errors' => empty($valid['errors']),
    'invalid_pack_is_rejected' => !empty($invalid['errors']),
    'invalid_pack_detects_plain_password' => (bool) array_filter($invalid['errors'], fn($e) => str_contains($e['message'], 'mots de passe en clair')),
    'invalid_pack_detects_bad_date_or_votes' => (bool) array_filter($invalid['errors'], fn($e) => str_contains($e['message'], 'date ISO') || str_contains($e['message'], 'upvotes_count')),
    'invalid_pack_detects_missing_relationship' => (bool) array_filter($invalid['errors'], fn($e) => str_contains($e['message'], 'Référence legacy_')),
  ],
];
$result['pass'] = !in_array(false, $result['assertions'], true);
echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . PHP_EOL;
exit($result['pass'] ? 0 : 1);
?>
