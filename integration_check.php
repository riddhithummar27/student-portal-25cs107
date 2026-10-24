<?php
declare(strict_types=1);
session_start();
require_once __DIR__ . '/db.php';

/*
 * Local integration diagnostic for Practical 15.
 * Restrict to an administrator. Remove or disable this file before public deployment.
 */
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    http_response_code(403);
    exit('403 Forbidden: Administrator access required.');
}

$checks = [];
$checks[] = ['PHP runtime', PHP_VERSION, true];

try {
    $pdo->query('SELECT 1');
    $checks[] = ['Database connection', 'Connected', true];
} catch (Throwable $e) {
    $checks[] = ['Database connection', 'Failed — check db.php and MySQL service', false];
}

$requiredTables = ['users', 'students', 'events', 'registrations', 'audit_logs'];
try {
    $stmt = $pdo->query('SHOW TABLES');
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    foreach ($requiredTables as $table) {
        $exists = in_array($table, $tables, true);
        $checks[] = ["Table: {$table}", $exists ? 'Found' : 'Missing', $exists];
    }
} catch (Throwable $e) {
    $checks[] = ['Database tables', 'Could not inspect tables', false];
}

$requiredFiles = ['db.php', 'login.php', 'register.php', 'logout.php', 'student_dashboard.php', 'admin_dashboard.php', 'admin_students.php', 'admin_events.php'];
foreach ($requiredFiles as $file) {
    $exists = is_file(__DIR__ . DIRECTORY_SEPARATOR . $file);
    $checks[] = ["File: {$file}", $exists ? 'Found' : 'Missing', $exists];
}

$posterColumn = false;
try {
    $stmt = $pdo->query("SHOW COLUMNS FROM events LIKE 'poster_path'");
    $posterColumn = (bool)$stmt->fetch(PDO::FETCH_ASSOC);
    $checks[] = ['Event poster column', $posterColumn ? 'Found' : 'Missing (only needed for poster uploads)', $posterColumn];
} catch (Throwable $e) {
    $checks[] = ['Event poster column', 'Could not inspect events table', false];
}

$allPassed = true;
foreach ($checks as $check) {
    if (!$check[2]) $allPassed = false;
}
function esc(string $v): string {
    return htmlspecialchars($v, ENT_QUOTES, 'UTF-8');
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>StudentHub Integration Check</title>
<style>
body{font:16px Arial,sans-serif;margin:24px;background:#f4f6fa;color:#20242a}
main{max-width:900px;margin:auto;background:#fff;padding:24px;border-radius:12px}
table{width:100%;border-collapse:collapse}th,td{text-align:left;padding:12px;border-bottom:1px solid #ddd}
th{background:#edf1f7}.pass{color:#087443;font-weight:bold}.fail{color:#b42318;font-weight:bold}
.notice{padding:12px;background:#fff4d6;border-radius:8px;margin:16px 0}
</style>
</head>
<body><main>
<h1>StudentHub Integration Check</h1>
<p>This diagnostic checks basic connectivity and required files. It does not replace the full test report.</p>
<div class="notice">For local development only. Remove or disable this page before public deployment.</div>
<table><thead><tr><th>Check</th><th>Result</th><th>Status</th></tr></thead><tbody>
<?php foreach ($checks as [$label, $result, $ok]): ?>
<tr><td><?=esc((string)$label)?></td><td><?=esc((string)$result)?></td><td class="<?=$ok?'pass':'fail'?>"><?=$ok?'PASS':'CHECK'?></td></tr>
<?php endforeach; ?>
</tbody></table>
<h2><?=$allPassed?'Basic checks passed':'Some checks need attention'?></h2>
<p><a href="admin_dashboard.php">Back to Admin Dashboard</a></p>
</main></body></html>
