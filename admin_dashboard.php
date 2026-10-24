<?php
// ==============================================================================
// Practical 14: Admin Dashboard with Role-Based Access Control & SQL Aggregation
// ==============================================================================
require_once "auth.php";
require_once "db.php";

// Strict Role-Based Access Control (RBAC) check
if (($_SESSION["role"] ?? "") !== "admin") {
    http_response_code(403);
    exit("403 Forbidden: Administrator access required.");
}

function h($v) {
    return htmlspecialchars((string)($v ?? ""), ENT_QUOTES, "UTF-8");
}

// 1. Fetch dynamic statistics using SQL aggregation queries
$studentCount = 0;
$eventCount = 0;
$userCount = 0;
$logCount = 0;

try {
    $studentCount = (int)$pdo->query("SELECT COUNT(*) FROM students")->fetchColumn();
    $eventCount = (int)$pdo->query("SELECT COUNT(*) FROM events")->fetchColumn();
    $userCount = (int)$pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    $logCount = (int)$pdo->query("SELECT COUNT(*) FROM audit_logs")->fetchColumn();

    // 2. Fetch latest 5 students
    $latestStudents = $pdo->query("SELECT student_id, name, email, department, semester, created_at FROM students ORDER BY student_id DESC LIMIT 5")->fetchAll();

    // 3. Fetch latest 5 audit actions
    $latestLogs = $pdo->query("SELECT l.action, l.details, l.ip_address, l.created_at, u.name AS user_name FROM audit_logs l LEFT JOIN users u ON u.user_id = l.user_id ORDER BY l.log_id DESC LIMIT 5")->fetchAll();

} catch (PDOException $e) {
    $dbError = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Admin Dashboard | StudentHub</title>
  <link rel="stylesheet" href="style.css" />
  <style>
    .stats-row {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
      gap: 18px;
      margin-bottom: 28px;
    }
    .stat-box {
      background: var(--card-bg, #ffffff);
      padding: 22px;
      border-radius: 12px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.06);
      border-left: 5px solid #2563eb;
    }
    .stat-box.green { border-color: #10b981; }
    .stat-box.purple { border-color: #8b5cf6; }
    .stat-box.orange { border-color: #f59e0b; }
    .stat-num {
      font-size: 2rem;
      font-weight: bold;
      color: #1e293b;
      margin-top: 6px;
    }
    .stat-title {
      font-size: 0.88rem;
      color: #64748b;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }
    .data-table-card {
      background: var(--card-bg, #ffffff);
      padding: 24px;
      border-radius: 12px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.06);
      margin-bottom: 28px;
    }
    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 14px;
    }
    th, td {
      padding: 10px 14px;
      text-align: left;
      border-bottom: 1px solid #e2e8f0;
      font-size: 0.92rem;
    }
    th {
      background: #f8fafc;
      font-weight: 600;
      color: #475569;
    }
    .badge {
      display: inline-block;
      padding: 3px 8px;
      border-radius: 4px;
      font-size: 0.78rem;
      font-weight: 600;
    }
    .badge-blue { background: #dbeafe; color: #1e40af; }
    .badge-green { background: #d1fae5; color: #065f46; }
    .btn-sm {
      display: inline-block;
      padding: 6px 12px;
      border-radius: 6px;
      font-size: 0.85rem;
      text-decoration: none;
      font-weight: 600;
    }
    .quick-actions {
      display: flex;
      gap: 12px;
      flex-wrap: wrap;
      margin-top: 14px;
    }
  </style>
</head>
<body>
  <a href="#main" class="skip-link">Skip to main content</a>

  <!-- Dynamic Role-Based Navigation Menu (Practical 14) -->
  <header class="navbar">
    <div class="nav-container">
      <a href="admin_dashboard.php" class="logo">Student<span>Hub Admin</span></a>
      <nav class="nav-links" id="navLinks" aria-label="Primary navigation">
        <a href="admin_dashboard.php" class="active">Dashboard</a>
        <a href="admin_students.php">Students CRUD</a>
        <a href="admin_events.php">Events CRUD</a>
        <a href="ajax_crud.html">AJAX API CRUD</a>
        <a href="audit_logs.php">Audit Logs</a>
        <a href="integration_check.php">Integration Check</a>
        <a href="logout.php" style="color: #dc2626; font-weight: bold;">Logout (<?= h($_SESSION["name"] ?? "Admin") ?>)</a>
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
    <div class="page-header" style="margin-bottom: 24px;">
      <h1 style="color: #1e3a8a;">Administrator Dashboard</h1>
      <p style="color: #64748b;">Overview of users, students, events, and audit logs.</p>
    </div>

    <!-- Practical 14: SQL Aggregation KPI Counters -->
    <section class="stats-row" aria-label="Portal Statistics">
      <div class="stat-box">
        <div class="stat-title">Total Students</div>
        <div class="stat-num"><?= $studentCount ?></div>
      </div>
      <div class="stat-box green">
        <div class="stat-title">Scheduled Events</div>
        <div class="stat-num"><?= $eventCount ?></div>
      </div>
      <div class="stat-box purple">
        <div class="stat-title">Registered Users</div>
        <div class="stat-num"><?= $userCount ?></div>
      </div>
      <div class="stat-box orange">
        <div class="stat-title">Audit Log Entries</div>
        <div class="stat-num"><?= $logCount ?></div>
      </div>
    </section>

    <!-- Admin Quick Management Shortcuts -->
    <div class="data-table-card" style="margin-bottom: 24px;">
      <h3 style="margin-top: 0;">Management Quick Actions</h3>
      <div class="quick-actions">
        <a href="admin_students.php" class="btn btn-primary btn-sm">➕ Add / Manage Students</a>
        <a href="admin_events.php" class="btn btn-sm" style="background: #10b981; color: white;">🗓️ Add / Manage Events</a>
        <a href="ajax_crud.html" class="btn btn-sm" style="background: #8b5cf6; color: white;">⚡ Asynchronous AJAX CRUD</a>
        <a href="audit_logs.php" class="btn btn-sm" style="background: #64748b; color: white;">📋 View All Audit Logs</a>
        <a href="dynamic.html" class="btn btn-sm" style="background: #f59e0b; color: white;">🌐 Open Dynamic Portal View</a>
      </div>
    </div>

    <!-- Latest Students Table -->
    <div class="data-table-card">
      <div style="display: flex; justify-content: space-between; align-items: center;">
        <h3 style="margin: 0;">Recently Registered Students</h3>
        <a href="admin_students.php" style="font-size: 0.88rem; color: #2563eb; font-weight: 600;">View all students &rarr;</a>
      </div>
      <div style="overflow-x: auto;">
        <table>
          <thead>
            <tr>
              <th>ID</th>
              <th>Full Name</th>
              <th>Email</th>
              <th>Department</th>
              <th>Semester</th>
              <th>Registered At</th>
            </tr>
          </thead>
          <tbody>
            <?php if (!empty($latestStudents)): ?>
              <?php foreach ($latestStudents as $s): ?>
                <tr>
                  <td>#<?= h($s["student_id"]) ?></td>
                  <td><strong><?= h($s["name"]) ?></strong></td>
                  <td><?= h($s["email"]) ?></td>
                  <td><span class="badge badge-blue"><?= h($s["department"]) ?></span></td>
                  <td>Sem <?= h($s["semester"]) ?></td>
                  <td><?= h($s["created_at"]) ?></td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr><td colspan="6" style="text-align: center; color: #94a3b8;">No student records found.</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Recent Audit Logs Table (Practical 14 Requirement) -->
    <div class="data-table-card">
      <div style="display: flex; justify-content: space-between; align-items: center;">
        <h3 style="margin: 0;">Recent Audit Trail</h3>
        <a href="audit_logs.php" style="font-size: 0.88rem; color: #2563eb; font-weight: 600;">View full audit logs &rarr;</a>
      </div>
      <div style="overflow-x: auto;">
        <table>
          <thead>
            <tr>
              <th>User</th>
              <th>Action</th>
              <th>Details</th>
              <th>IP Address</th>
              <th>Timestamp</th>
            </tr>
          </thead>
          <tbody>
            <?php if (!empty($latestLogs)): ?>
              <?php foreach ($latestLogs as $log): ?>
                <tr>
                  <td><?= h($log["user_name"] ?? "System / Guest") ?></td>
                  <td><span class="badge badge-green"><?= h($log["action"]) ?></span></td>
                  <td><?= h($log["details"]) ?></td>
                  <td><code><?= h($log["ip_address"]) ?></code></td>
                  <td><?= h($log["created_at"]) ?></td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr><td colspan="5" style="text-align: center; color: #94a3b8;">No audit log records recorded yet.</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </main>

  <footer class="footer">
    <p>&copy; 2026 StudentHub Admin System | Charusat FTE ITUE203</p>
  </footer>

  <script src="script.js"></script>
</body>
</html>
