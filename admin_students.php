<?php
require_once __DIR__ . "/auth.php";
require_once __DIR__ . "/db.php";

if (($_SESSION["role"] ?? "") !== "admin") {
    http_response_code(403);
    exit("403 Forbidden: Admin access required.");
}

function h($value) { return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8"); }

function log_action(PDO $pdo, string $action, string $details = ""): void {
    try {
        $stmt = $pdo->prepare("INSERT INTO audit_logs (user_id, action, details, ip_address) VALUES (?, ?, ?, ?)");
        $stmt->execute([
            $_SESSION["user_id"] ?? null,
            $action,
            $details,
            $_SERVER["REMOTE_ADDR"] ?? null
        ]);
    } catch (Throwable $e) {
        error_log("StudentHub audit log error: " . $e->getMessage());
    }
}

$message = "";
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $action = $_POST["action"] ?? "";
    $id = filter_input(INPUT_POST, "student_id", FILTER_VALIDATE_INT);

    if ($action === "create") {
        $name = trim($_POST["name"] ?? "");
        $email = trim($_POST["email"] ?? "");
        $mobile = trim($_POST["mobile"] ?? "");
        $department = trim($_POST["department"] ?? "");
        $semester = filter_input(INPUT_POST, "semester", FILTER_VALIDATE_INT);

        if ($name === "" || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 150 ||
            ($semester !== null && $semester !== false && ($semester < 1 || $semester > 12))) {
            $message = "Enter a valid name, email, and semester (1–12).";
        } else {
            try {
                $stmt = $pdo->prepare("INSERT INTO students (name, email, mobile, department, semester) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$name, $email, $mobile, $department, $semester ?: null]);
                log_action($pdo, "student_create", "Created student ID " . $pdo->lastInsertId());
                $message = "Student added successfully.";
            } catch (PDOException $e) {
                error_log("StudentHub student create error: " . $e->getMessage());
                $message = "Could not add student. Check for a duplicate email or database constraint.";
            }
        }
    } elseif ($action === "update" && $id) {
        $name = trim($_POST["name"] ?? "");
        $email = trim($_POST["email"] ?? "");
        $mobile = trim($_POST["mobile"] ?? "");
        $department = trim($_POST["department"] ?? "");
        $semester = filter_input(INPUT_POST, "semester", FILTER_VALIDATE_INT);

        if ($name === "" || !filter_var($email, FILTER_VALIDATE_EMAIL) ||
            ($semester !== null && $semester !== false && ($semester < 1 || $semester > 12))) {
            $message = "Enter valid student details.";
        } else {
            try {
                $stmt = $pdo->prepare("UPDATE students SET name=?, email=?, mobile=?, department=?, semester=? WHERE student_id=?");
                $stmt->execute([$name, $email, $mobile, $department, $semester ?: null, $id]);
                log_action($pdo, "student_update", "Updated student ID " . $id);
                $message = "Student updated successfully.";
            } catch (PDOException $e) {
                error_log("StudentHub student update error: " . $e->getMessage());
                $message = "Could not update student. Check for a duplicate email or database constraint.";
            }
        }
    } elseif ($action === "delete" && $id) {
        try {
            $stmt = $pdo->prepare("DELETE FROM students WHERE student_id = ?");
            $stmt->execute([$id]);
            log_action($pdo, "student_delete", "Deleted student ID " . $id);
            $message = $stmt->rowCount() ? "Student deleted." : "Student not found.";
        } catch (PDOException $e) {
            error_log("StudentHub student delete error: " . $e->getMessage());
            $message = "Could not delete this student. Existing event registrations may reference this record.";
        }
    }
}

$q = trim($_GET["q"] ?? "");
$sql = "SELECT student_id, name, email, mobile, department, semester FROM students";
$params = [];
if ($q !== "") {
    $sql .= " WHERE name LIKE ? OR email LIKE ? OR department LIKE ?";
    $term = "%" . $q . "%";
    $params = [$term, $term, $term];
}
$sql .= " ORDER BY student_id DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$students = $stmt->fetchAll();
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>StudentHub — Manage Students</title>
<style>
body{font-family:Arial,sans-serif;max-width:1100px;margin:24px auto;padding:0 16px;color:#222}
form{margin:12px 0}.grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:8px}
input{padding:8px;max-width:100%;box-sizing:border-box}button{padding:8px 12px;cursor:pointer}
table{width:100%;border-collapse:collapse;margin-top:18px}th,td{border:1px solid #ccc;padding:8px;text-align:left}
.scroll{overflow-x:auto}.message{padding:10px;background:#f1f1f1}
</style>
</head>
<body>
<h1>Manage Students</h1>
<p><a href="admin_dashboard.php">Back to admin dashboard</a> | <a href="audit_logs.php">View audit logs</a> | <a href="logout.php">Logout</a></p>
<?php if ($message !== ""): ?><p class="message" role="status"><?= h($message) ?></p><?php endif; ?>

<h2>Add student</h2>
<form method="post">
<input type="hidden" name="action" value="create">
<div class="grid">
<label>Name<br><input name="name" maxlength="100" required></label>
<label>Email<br><input name="email" type="email" maxlength="150" required></label>
<label>Mobile<br><input name="mobile" maxlength="30"></label>
<label>Department<br><input name="department" maxlength="100"></label>
<label>Semester (1–12)<br><input name="semester" type="number" min="1" max="12"></label>
</div><button type="submit">Add student</button>
</form>

<h2>Search students</h2>
<form method="get"><input name="q" value="<?= h($q) ?>" placeholder="Name, email, or department"><button>Search</button> <a href="admin_students.php">Clear</a></form>

<div class="scroll"><table>
<thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Mobile</th><th>Department</th><th>Semester</th><th>Update</th><th>Delete</th></tr></thead>
<tbody>
<?php foreach ($students as $s): ?>
<tr>
<td><?= (int)$s["student_id"] ?></td>
<td colspan="1">
<form method="post">
<input type="hidden" name="action" value="update"><input type="hidden" name="student_id" value="<?= (int)$s["student_id"] ?>">
<input name="name" value="<?= h($s["name"]) ?>" maxlength="100" required>
</td>
<td><input name="email" type="email" value="<?= h($s["email"]) ?>" maxlength="150" required></td>
<td><input name="mobile" value="<?= h($s["mobile"]) ?>" maxlength="30"></td>
<td><input name="department" value="<?= h($s["department"]) ?>" maxlength="100"></td>
<td><input name="semester" type="number" min="1" max="12" value="<?= h($s["semester"]) ?>"></td>
<td><button type="submit">Save</button></form></td>
<td><form method="post" onsubmit="return confirm('Delete this student? Existing registrations may prevent deletion.');">
<input type="hidden" name="action" value="delete"><input type="hidden" name="student_id" value="<?= (int)$s["student_id"] ?>">
<button type="submit">Delete</button></form></td>
</tr>
<?php endforeach; ?>
<?php if (!$students): ?><tr><td colspan="8">No students found.</td></tr><?php endif; ?>
</tbody></table></div>
</body></html>
