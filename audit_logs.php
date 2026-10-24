<?php
// ==============================================================================
// Practical 14: Audit Logs Viewer with Traceability & Admin RBAC
// ==============================================================================
require_once __DIR__ . "/auth.php";
require_once __DIR__ . "/db.php";

if (($_SESSION["role"] ?? "") !== "admin") {
    http_response_code(403);
    exit("403 Forbidden: Administrator access required.");
}

function h($value) {
    return htmlspecialchars((string)($value ?? ""), ENT_QUOTES, "UTF-8");
}

try {
    $stmt = $pdo->query(
        "SELECT l.log_id, l.user_id, u.name AS user_name, u.email AS user_email, l.action, l.details, l.ip_address, l.created_at
         FROM audit_logs l 
         LEFT JOIN users u ON u.user_id = l.user_id
         ORDER BY l.log_id DESC LIMIT 150"
    );
    $logs = $stmt->fetchAll();
} catch (PDOException $e) {
    http_response_code(500);
    exit("Audit log table error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Security Audit Logs | StudentHub</title>
  <link rel="stylesheet" href="style.css" />
  <style>
    .admin-card {
      background: var(--card-bg, #ffffff);
      padding: 24px;
      border-radius: 12px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.06);
      margin-bottom: 24px;
    }
    table { width: 100%; border-collapse: collapse; margin-top: 12px; }
    th, td { padding: 10px 12px; border: 1px solid #e2e8f0; text-align: left; font-size: 0.88rem; }
    th { background: #f8fafc; font-weight: 600; color: #475569; }
    .badge-action {
      display: inline-block;
      padding: 3px 8px;
      border-radius: 4px;
      font-size: 0.78rem;
      font-weight: 600;
      background: #eff6ff;
      color: #1d4ed8;
    }
  </style>
</head>
<body>
  <a href="#main" class="skip-link">Skip to main content</a>

  <header class="navbar">
    <div class="nav-container">
      <a href="admin_dashboard.php" class="logo">Student<span>Hub Admin</span></a>
      <nav class="nav-links" id="navLinks" aria-label="Primary navigation">
        <a href="admin_dashboard.php">Dashboard</a>
        <a href="admin_students.php">Students CRUD</a>
        <a href="admin_events.php">Events CRUD</a>
        <a href="ajax_crud.html">AJAX CRUD</a>
        <a href="audit_logs.php" class="active">Audit Logs</a>
        <a href="integration_check.php">Integration Check</a>
        <a href="logout.php" style="color: #dc2626;">Logout</a>
      </nav>
      <div class="nav-actions">
        <button id="themeToggle" class="icon-btn" aria-label="Toggle dark mode">🌙</button>
        <button id="hamburger" class="hamburger" aria-label="Toggle menu" aria-expanded="false">
          <span></span><span></span><span></span>
        </button>
      </div>
    </div>
  </header>

  <main id="main" class="container" style="max-width: 1100px; margin: 24px auto; padding: 0 16px;">
    <div class="page-header" style="margin-bottom: 20px;">
      <h1 style="color: #1e3a8a;">System Security Audit Trail</h1>
      <p style="color: #64748b;">Practical 14: Traceability log capturing admin actions, student registrations, and login events.</p>
    </div>

    <div class="admin-card">
      <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
        <h3 style="margin: 0;">Captured Security Events (<?= count($logs) ?> Entries)</h3>
        <a href="admin_dashboard.php" style="color: #2563eb; font-size: 0.88rem; font-weight: 600;">&larr; Back to Dashboard</a>
      </div>
      <div style="overflow-x: auto;">
        <table>
          <thead>
            <tr>
              <th style="width: 60px;">Log ID</th>
              <th>Triggered By</th>
              <th>Action Name</th>
              <th>Activity Details</th>
              <th>Client IP</th>
              <th>Timestamp</th>
            </tr>
          </thead>
          <tbody>
            <?php if (!empty($logs)): ?>
              <?php foreach ($logs as $log): ?>
                <tr>
                  <td>#<?= (int)$log["log_id"] ?></td>
                  <td>
                    <strong><?= h($log["user_name"] ?? "Guest / Visitor") ?></strong>
                    <?php if (!empty($log["user_email"])): ?>
                      <br><small style="color: #64748b;"><?= h($log["user_email"]) ?></small>
                    <?php endif; ?>
                  </td>
                  <td><span class="badge-action"><?= h($log["action"]) ?></span></td>
                  <td><?= h($log["details"]) ?></td>
                  <td><code><?= h($log["ip_address"] ?? '127.0.0.1') ?></code></td>
                  <td><?= h($log["created_at"]) ?></td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr>
                <td colspan="6" style="text-align: center; padding: 24px; color: #94a3b8;">
                  No audit logs recorded yet.
                </td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </main>

  <footer class="footer">
    <p>&copy; 2026 StudentHub Admin System &bull; CHARUSAT FTE</p>
  </footer>

  <script src="script.js"></script>
</body>
</html>
