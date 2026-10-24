<?php
// ==============================================================================
// Practical 13: PHP JSON API for AJAX/Fetch-Based CRUD without Page Reload
// ==============================================================================
require_once __DIR__ . "/auth.php";
require_once __DIR__ . "/db.php";

header("Content-Type: application/json; charset=utf-8");
header("Cache-Control: no-store");

// Admin or authorized user check
if (($_SESSION["role"] ?? "") !== "admin") {
    http_response_code(403);
    echo json_encode(["status" => "error", "error" => "Administrator authorization required."]);
    exit;
}

function respond(int $status, array $payload): void {
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
    exit;
}

$method = $_SERVER["REQUEST_METHOD"];

// Check method override if needed
if ($method === "POST" && isset($_POST["_method"])) {
    $method = strtoupper($_POST["_method"]);
}

// -------------------------------------------------------------
// 1. GET: Fetch / Search students list
// -------------------------------------------------------------
if ($method === "GET") {
    $q = trim($_GET["q"] ?? "");
    $sql = "SELECT student_id, name, email, mobile, department, semester FROM students";
    $params = [];
    if ($q !== "") {
        $sql .= " WHERE name LIKE ? OR email LIKE ? OR department LIKE ?";
        $term = "%" . $q . "%";
        $params = [$term, $term, $term];
    }
    $sql .= " ORDER BY student_id DESC LIMIT 100";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    respond(200, [
        "status" => "success",
        "count" => $stmt->rowCount(),
        "data" => $stmt->fetchAll(PDO::FETCH_ASSOC)
    ]);
}

// -------------------------------------------------------------
// 2. POST: Create new student record
// -------------------------------------------------------------
if ($method === "POST") {
    $raw = file_get_contents("php://input");
    $data = json_decode($raw, true);
    if (!is_array($data)) {
        // Fallback to standard $_POST
        $data = $_POST;
    }

    $name = trim((string)($data["name"] ?? ""));
    $email = trim((string)($data["email"] ?? ""));
    $mobile = trim((string)($data["mobile"] ?? ""));
    $department = trim((string)($data["department"] ?? ""));
    $semester = filter_var($data["semester"] ?? null, FILTER_VALIDATE_INT);

    if (empty($name) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        respond(422, ["status" => "error", "error" => "Please provide a valid name and email address."]);
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO students (name, email, mobile, department, semester) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$name, $email, $mobile, $department, $semester ?: 1]);
        $newId = (int)$pdo->lastInsertId();

        // Audit log
        try {
            $pdo->prepare("INSERT INTO audit_logs (user_id, action, details, ip_address) VALUES (?, ?, ?, ?)")
                ->execute([$_SESSION["user_id"] ?? null, "api_student_create", "API created student #$newId", $_SERVER["REMOTE_ADDR"] ?? '127.0.0.1']);
        } catch (Exception $e) {}

        respond(201, [
            "status" => "success",
            "message" => "Student created successfully via Fetch API.",
            "student_id" => $newId
        ]);
    } catch (PDOException $e) {
        respond(409, ["status" => "error", "error" => "Email already exists or database constraint failed."]);
    }
}

// -------------------------------------------------------------
// 3. PUT: Update existing student record
// -------------------------------------------------------------
if ($method === "PUT") {
    $raw = file_get_contents("php://input");
    $data = json_decode($raw, true);

    $id = filter_var($data["student_id"] ?? null, FILTER_VALIDATE_INT);
    $name = trim((string)($data["name"] ?? ""));
    $email = trim((string)($data["email"] ?? ""));
    $mobile = trim((string)($data["mobile"] ?? ""));
    $department = trim((string)($data["department"] ?? ""));
    $semester = filter_var($data["semester"] ?? null, FILTER_VALIDATE_INT);

    if (!$id || empty($name) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        respond(422, ["status" => "error", "error" => "Valid Student ID, name, and email are required."]);
    }

    try {
        $stmt = $pdo->prepare("UPDATE students SET name=?, email=?, mobile=?, department=?, semester=? WHERE student_id=?");
        $stmt->execute([$name, $email, $mobile, $department, $semester ?: 1, $id]);

        respond(200, [
            "status" => "success",
            "message" => "Student #$id updated successfully."
        ]);
    } catch (PDOException $e) {
        respond(409, ["status" => "error", "error" => "Update failed: " . $e->getMessage()]);
    }
}

// -------------------------------------------------------------
// 4. DELETE: Delete student record
// -------------------------------------------------------------
if ($method === "DELETE") {
    $id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);
    if (!$id) {
        $raw = file_get_contents("php://input");
        $data = json_decode($raw, true);
        $id = filter_var($data["student_id"] ?? null, FILTER_VALIDATE_INT);
    }

    if (!$id) {
        respond(422, ["status" => "error", "error" => "Valid Student ID required for deletion."]);
    }

    try {
        $pdo->prepare("DELETE FROM registrations WHERE student_id = ?")->execute([$id]);
        $stmt = $pdo->prepare("DELETE FROM students WHERE student_id = ?");
        $stmt->execute([$id]);

        respond(200, [
            "status" => "success",
            "message" => "Student #$id deleted successfully via API."
        ]);
    } catch (PDOException $e) {
        respond(500, ["status" => "error", "error" => "Deletion error: " . $e->getMessage()]);
    }
}

header("Allow: GET, POST, PUT, DELETE");
respond(405, ["status" => "error", "error" => "HTTP Method Not Allowed."]);
