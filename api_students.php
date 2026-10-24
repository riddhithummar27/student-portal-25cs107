<?php
require_once __DIR__ . "/auth.php";
require_once __DIR__ . "/db.php";

header("Content-Type: application/json; charset=utf-8");
header("Cache-Control: no-store");

if (($_SESSION["role"] ?? "") !== "admin") {
    http_response_code(403);
    echo json_encode(["error" => "Admin access required."]);
    exit;
}

function respond(int $status, array $payload): void {
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "GET") {
    $q = trim($_GET["q"] ?? "");
    $sql = "SELECT student_id, name, email, mobile, department, semester FROM students";
    $params = [];
    if ($q !== "") {
        $sql .= " WHERE name LIKE ? OR email LIKE ? OR department LIKE ?";
        $term = "%" . $q . "%";
        $params = [$term, $term, $term];
    }
    $sql .= " ORDER BY student_id DESC LIMIT 200";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    respond(200, ["data" => $stmt->fetchAll()]);
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $raw = file_get_contents("php://input");
    $data = json_decode($raw, true);
    if (!is_array($data)) {
        respond(400, ["error" => "Send a valid JSON object."]);
    }
    $name = trim((string)($data["name"] ?? ""));
    $email = trim((string)($data["email"] ?? ""));
    $mobile = trim((string)($data["mobile"] ?? ""));
    $department = trim((string)($data["department"] ?? ""));
    $semester = filter_var($data["semester"] ?? null, FILTER_VALIDATE_INT);

    if ($name === "" || strlen($name) > 100 || !filter_var($email, FILTER_VALIDATE_EMAIL) ||
        strlen($email) > 150 || ($semester !== null && $semester !== false && ($semester < 1 || $semester > 12))) {
        respond(422, ["error" => "Invalid name, email, or semester."]);
    }
    try {
        $stmt = $pdo->prepare("INSERT INTO students (name, email, mobile, department, semester) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$name, $email, $mobile, $department, $semester ?: null]);
        respond(201, ["message" => "Student created.", "student_id" => (int)$pdo->lastInsertId()]);
    } catch (PDOException $e) {
        error_log("StudentHub API create error: " . $e->getMessage());
        respond(409, ["error" => "Could not create student. Check duplicate email and database constraints."]);
    }
}

header("Allow: GET, POST");
respond(405, ["error" => "Method not allowed."]);
