<?php
require_once __DIR__ . "/auth.php";
require_once __DIR__ . "/db.php";

if (($_SESSION["role"] ?? "") !== "admin") {
    http_response_code(403);
    exit("403 Forbidden: Admin access required.");
}
function h($value) { return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8"); }

try {
    $stmt = $pdo->query(
        "SELECT l.log_id, l.user_id, u.name AS user_name, l.action, l.details, l.ip_address, l.created_at
         FROM audit_logs l LEFT JOIN users u ON u.user_id = l.user_id
         ORDER BY l.created_at DESC LIMIT 200"
    );
    $logs = $stmt->fetchAll();
} catch (PDOException $e) {
    http_response_code(500);
    exit("Audit log table is missing. Run studenthub_additions.sql first.");
}
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>StudentHub — Audit Logs</title><style>
body{font-family:Arial,sans-serif;max-width:1100px;margin:24px auto;padding:0 16px}table{width:100%;border-collapse:collapse}
th,td{border:1px solid #ccc;padding:8px;text-align:left} .scroll{overflow-x:auto}
</style></head><body>
<h1>Audit Logs</h1><p><a href="admin_dashboard.php">Admin dashboard</a> | <a href="admin_students.php">Students</a> | <a href="admin_events.php">Events</a></p>
<div class="scroll"><table><thead><tr><th>ID</th><th>User</th><th>Action</th><th>Details</th><th>IP</th><th>Date/time</th></tr></thead><tbody>
<?php foreach ($logs as $log): ?><tr>
<td><?= (int)$log["log_id"] ?></td><td><?= h($log["user_name"] ?? ("User #" . $log["user_id"])) ?></td>
<td><?= h($log["action"]) ?></td><td><?= h($log["details"]) ?></td><td><?= h($log["ip_address"]) ?></td><td><?= h($log["created_at"]) ?></td>
</tr><?php endforeach; ?>
<?php if (!$logs): ?><tr><td colspan="6">No audit entries yet. Student/event create/update/delete actions will add entries.</td></tr><?php endif; ?>
</tbody></table></div></body></html>
