<?php
// ==============================================================================
// Practical 11: Student Management CRUD with Search, Filter & CSV Export
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

function log_action(PDO $pdo, string $action, string $details = ""): void {
    try {
        $stmt = $pdo->prepare("INSERT INTO audit_logs (user_id, action, details, ip_address) VALUES (?, ?, ?, ?)");
        $stmt->execute([
            $_SESSION["user_id"] ?? null,
            $action,
            $details,
            $_SERVER["REMOTE_ADDR"] ?? '127.0.0.1'
        ]);
    } catch (Throwable $e) {}
}

// Practical 11 Intermediate Extension: Export Students to CSV
if (isset($_GET["export"]) && $_GET["export"] === "csv") {
    header("Content-Type: text/csv; charset=utf-8");
    header("Content-Disposition: attachment; filename=students_export_" . date("Ymd") . ".csv");
    $out = fopen("php://output", "w");
    fputcsv($out, ["Student ID", "Name", "Email", "Mobile", "Department", "Semester", "Status"]);

    $stmt = $pdo->query("SELECT student_id, name, email, mobile, department, semester, status FROM students ORDER BY student_id ASC");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        fputcsv($out, $row);
    }
    fclose($out);
    exit;
}

$message = "";
$messageType = "info";

// Handle Form Submissions (Create, Update, Delete)
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $action = $_POST["action"] ?? "";
    $id = filter_input(INPUT_POST, "student_id", FILTER_VALIDATE_INT);

    if ($action === "create") {
        $name = trim($_POST["name"] ?? "");
        $email = trim($_POST["email"] ?? "");
        $mobile = trim($_POST["mobile"] ?? "");
        $department = trim($_POST["department"] ?? "");
        $semester = filter_input(INPUT_POST, "semester", FILTER_VALIDATE_INT);

        if ($name === "" || !filter_var($email, FILTER_VALIDATE_EMAIL) ||
            ($semester !== null && $semester !== false && ($semester < 1 || $semester > 8))) {
            $message = "Please enter a valid student name, email, and semester (1–8).";
            $messageType = "error";
        } else {
            try {
                $stmt = $pdo->prepare("INSERT INTO students (name, email, mobile, department, semester) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$name, $email, $mobile, $department, $semester ?: 1]);
                $newId = $pdo->lastInsertId();
                log_action($pdo, "student_create", "Created student ID #$newId ($name)");
                $message = "Student added successfully!";
                $messageType = "success";
            } catch (PDOException $e) {
                $message = "Could not add student. Check for duplicate email.";
                $messageType = "error";
            }
        }
    } elseif ($action === "update" && $id) {
        $name = trim($_POST["name"] ?? "");
        $email = trim($_POST["email"] ?? "");
        $mobile = trim($_POST["mobile"] ?? "");
        $department = trim($_POST["department"] ?? "");
        $semester = filter_input(INPUT_POST, "semester", FILTER_VALIDATE_INT);

        if ($name === "" || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $message = "Please enter valid student details.";
            $messageType = "error";
        } else {
            try {
                $stmt = $pdo->prepare("UPDATE students SET name=?, email=?, mobile=?, department=?, semester=? WHERE student_id=?");
                $stmt->execute([$name, $email, $mobile, $department, $semester ?: 1, $id]);
                log_action($pdo, "student_update", "Updated student ID #$id ($name)");
                $message = "Student details updated successfully.";
                $messageType = "success";
            } catch (PDOException $e) {
                $message = "Could not update student. Check for duplicate email.";
                $messageType = "error";
            }
        }
    } elseif ($action === "delete" && $id) {
        try {
            // Delete associated registrations first if any
            $pdo->prepare("DELETE FROM registrations WHERE student_id = ?")->execute([$id]);
            $stmt = $pdo->prepare("DELETE FROM students WHERE student_id = ?");
            $stmt->execute([$id]);
            log_action($pdo, "student_delete", "Deleted student ID #$id");
            $message = "Student record deleted successfully.";
            $messageType = "success";
        } catch (PDOException $e) {
            $message = "Could not delete student: " . $e->getMessage();
            $messageType = "error";
        }
    }
}

// Search and Filter logic
$q = trim($_GET["q"] ?? "");
$deptFilter = trim($_GET["dept"] ?? "");

$sql = "SELECT student_id, name, email, mobile, department, semester, status FROM students WHERE 1=1";
$params = [];

if ($q !== "") {
    $sql .= " AND (name LIKE ? OR email LIKE ? OR mobile LIKE ?)";
    $term = "%" . $q . "%";
    $params[] = $term;
    $params[] = $term;
    $params[] = $term;
}
if ($deptFilter !== "") {
    $sql .= " AND department = ?";
    $params[] = $deptFilter;
}

$sql .= " ORDER BY student_id DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$students = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Student Management CRUD | StudentHub</title>
  <link rel="stylesheet" href="style.css" />
  <style>
    .admin-card {
      background: var(--card-bg, #ffffff);
      padding: 24px;
      border-radius: 12px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.06);
      margin-bottom: 24px;
    }
    .form-row {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
      gap: 12px;
      margin-bottom: 14px;
    }
    .form-row label {
      display: block;
      font-size: 0.85rem;
      font-weight: 600;
      color: #475569;
      margin-bottom: 4px;
    }
    .form-row input, .form-row select {
      width: 100%;
      padding: 8px 10px;
      border: 1px solid #cbd5e1;
      border-radius: 6px;
      font-size: 0.9rem;
      box-sizing: border-box;
    }
    .alert-msg {
      padding: 10px 14px;
      border-radius: 6px;
      margin-bottom: 16px;
      font-size: 0.92rem;
    }
    .alert-msg.success { background: #dcfce7; color: #166534; border: 1px solid #86efac; }
    .alert-msg.error { background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; }
    table { width: 100%; border-collapse: collapse; margin-top: 12px; }
    th, td { padding: 8px 10px; border: 1px solid #e2e8f0; text-align: left; font-size: 0.88rem; }
    th { background: #f8fafc; font-weight: 600; }
    .table-input {
      padding: 6px 8px;
      border: 1px solid #cbd5e1;
      border-radius: 4px;
      font-size: 0.85rem;
      width: 100%;
      box-sizing: border-box;
    }
    .btn-action {
      padding: 5px 10px;
      border-radius: 4px;
      border: none;
      cursor: pointer;
      font-size: 0.8rem;
      font-weight: bold;
    }
    .btn-save { background: #2563eb; color: white; }
    .btn-delete { background: #ef4444; color: white; }
  </style>
</head>
<body>
  <a href="#main" class="skip-link">Skip to main content</a>

  <header class="navbar">
    <div class="nav-container">
      <a href="admin_dashboard.php" class="logo">Student<span>Hub Admin</span></a>
      <nav class="nav-links" id="navLinks" aria-label="Primary navigation">
        <a href="admin_dashboard.php">Dashboard</a>
        <a href="admin_students.php" class="active">Students CRUD</a>
        <a href="admin_events.php">Events CRUD</a>
        <a href="ajax_crud.html">AJAX CRUD</a>
        <a href="audit_logs.php">Audit Logs</a>
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

  <main id="main" class="container" style="max-width: 1150px; margin: 24px auto; padding: 0 16px;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 10px;">
      <div>
        <h1 style="color: #1e3a8a; margin: 0;">Student Management CRUD</h1>
        <p style="color: #64748b; margin: 4px 0 0 0;">Add, view, edit, search, and delete students with MySQL statements.</p>
      </div>
      <div>
        <!-- Practical 11 Intermediate Extension: Export to CSV -->
        <a href="admin_students.php?export=csv" class="btn btn-sm" style="background: #10b981; color: white; text-decoration: none; padding: 8px 14px; border-radius: 6px; font-weight: 600;">
          📥 Export Students to CSV
        </a>
      </div>
    </div>

    <?php if (!empty($message)): ?>
      <div class="alert-msg <?= $messageType ?>" role="alert">
        <?= h($message) ?>
      </div>
    <?php endif; ?>

    <!-- Add New Student Form (Create Operation) -->
    <div class="admin-card">
      <h3 style="margin-top: 0; color: #1e293b;">➕ Add New Student Record</h3>
      <form method="POST" action="admin_students.php">
        <input type="hidden" name="action" value="create" />
        <div class="form-row">
          <div>
            <label for="newName">Full Name *</label>
            <input type="text" id="newName" name="name" required placeholder="Student Name" />
          </div>
          <div>
            <label for="newEmail">Email Address *</label>
            <input type="email" id="newEmail" name="email" required placeholder="student@charusat.edu.in" />
          </div>
          <div>
            <label for="newMobile">Mobile Number</label>
            <input type="tel" id="newMobile" name="mobile" placeholder="10 Digits" />
          </div>
          <div>
            <label for="newDept">Department</label>
            <select id="newDept" name="department">
              <option value="Information Technology">Information Technology</option>
              <option value="Computer Engineering">Computer Engineering</option>
              <option value="Computer Science">Computer Science</option>
              <option value="Electrical Engineering">Electrical Engineering</option>
              <option value="Mechanical Engineering">Mechanical Engineering</option>
              <option value="Civil Engineering">Civil Engineering</option>
            </select>
          </div>
          <div>
            <label for="newSem">Semester</label>
            <input type="number" id="newSem" name="semester" min="1" max="8" value="3" />
          </div>
        </div>
        <button type="submit" class="btn btn-primary" style="padding: 9px 18px; cursor: pointer;">
          Insert Student Record
        </button>
      </form>
    </div>

    <!-- Search and Filter Form (Practical 11 Requirement) -->
    <div class="admin-card">
      <h3 style="margin-top: 0; color: #1e293b;">🔍 Search & Department Filter</h3>
      <form method="GET" action="admin_students.php" style="display: flex; gap: 10px; flex-wrap: wrap;">
        <input type="text" name="q" value="<?= h($q) ?>" placeholder="Search by name, email, or mobile..." style="flex: 2; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 6px;" />
        <select name="dept" style="flex: 1; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 6px;">
          <option value="">-- All Departments --</option>
          <option value="Information Technology" <?= $deptFilter === 'Information Technology' ? 'selected' : '' ?>>Information Technology</option>
          <option value="Computer Engineering" <?= $deptFilter === 'Computer Engineering' ? 'selected' : '' ?>>Computer Engineering</option>
          <option value="Computer Science" <?= $deptFilter === 'Computer Science' ? 'selected' : '' ?>>Computer Science</option>
          <option value="Electrical Engineering" <?= $deptFilter === 'Electrical Engineering' ? 'selected' : '' ?>>Electrical Engineering</option>
          <option value="Mechanical Engineering" <?= $deptFilter === 'Mechanical Engineering' ? 'selected' : '' ?>>Mechanical Engineering</option>
          <option value="Civil Engineering" <?= $deptFilter === 'Civil Engineering' ? 'selected' : '' ?>>Civil Engineering</option>
        </select>
        <button type="submit" class="btn btn-primary" style="padding: 8px 16px;">Filter</button>
        <?php if ($q !== '' || $deptFilter !== ''): ?>
          <a href="admin_students.php" class="btn" style="background: #64748b; color: white; padding: 8px 14px; text-decoration: none; border-radius: 6px;">Reset</a>
        <?php endif; ?>
      </form>
    </div>

    <!-- Students CRUD Data Table -->
    <div class="admin-card">
      <div style="display: flex; justify-content: space-between; align-items: center;">
        <h3 style="margin: 0;">Enrolled Students List (<?= count($students) ?> Records)</h3>
      </div>
      <div style="overflow-x: auto;">
        <table>
          <thead>
            <tr>
              <th style="width: 50px;">ID</th>
              <th>Student Name</th>
              <th>Email</th>
              <th>Mobile</th>
              <th>Department</th>
              <th style="width: 60px;">Sem</th>
              <th style="width: 70px;">Save</th>
              <th style="width: 70px;">Delete</th>
            </tr>
          </thead>
          <tbody>
            <?php if (!empty($students)): ?>
              <?php foreach ($students as $s): ?>
                <tr>
                  <td>#<?= (int)$s["student_id"] ?></td>
                  <form method="POST" action="admin_students.php">
                    <input type="hidden" name="action" value="update" />
                    <input type="hidden" name="student_id" value="<?= (int)$s["student_id"] ?>" />
                    <td><input type="text" class="table-input" name="name" value="<?= h($s["name"]) ?>" required /></td>
                    <td><input type="email" class="table-input" name="email" value="<?= h($s["email"]) ?>" required /></td>
                    <td><input type="tel" class="table-input" name="mobile" value="<?= h($s["mobile"]) ?>" /></td>
                    <td>
                      <select class="table-input" name="department">
                        <option value="Information Technology" <?= $s['department'] === 'Information Technology' ? 'selected' : '' ?>>IT</option>
                        <option value="Computer Engineering" <?= $s['department'] === 'Computer Engineering' ? 'selected' : '' ?>>CE</option>
                        <option value="Computer Science" <?= $s['department'] === 'Computer Science' ? 'selected' : '' ?>>CSE</option>
                        <option value="Electrical Engineering" <?= $s['department'] === 'Electrical Engineering' ? 'selected' : '' ?>>EE</option>
                        <option value="Mechanical Engineering" <?= $s['department'] === 'Mechanical Engineering' ? 'selected' : '' ?>>ME</option>
                        <option value="Civil Engineering" <?= $s['department'] === 'Civil Engineering' ? 'selected' : '' ?>>CL</option>
                      </select>
                    </td>
                    <td><input type="number" class="table-input" name="semester" min="1" max="8" value="<?= h($s["semester"]) ?>" /></td>
                    <td><button type="submit" class="btn-action btn-save">Save</button></td>
                  </form>
                  <td>
                    <!-- Delete with confirmation (Practical 11 Requirement) -->
                    <form method="POST" action="admin_students.php" onsubmit="return confirm('Are you sure you want to delete student: <?= h($s['name']) ?>?');">
                      <input type="hidden" name="action" value="delete" />
                      <input type="hidden" name="student_id" value="<?= (int)$s["student_id"] ?>" />
                      <button type="submit" class="btn-action btn-delete">Delete</button>
                    </form>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr>
                <td colspan="8" style="text-align: center; padding: 20px; color: #94a3b8;">
                  No student records matched your search query.
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
