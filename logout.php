<?php
// ==============================================================================
// Practical 10: Secure Logout with Session Destruction & Cookie Invalidation
// ==============================================================================
session_start();
require_once "db.php";

// Log the logout action if user was logged in
if (isset($_SESSION["user_id"])) {
    try {
        $audit = $pdo->prepare("INSERT INTO audit_logs (user_id, action, details, ip_address) VALUES (?, ?, ?, ?)");
        $audit->execute([$_SESSION["user_id"], "user_logout", "User logged out", $_SERVER["REMOTE_ADDR"] ?? '127.0.0.1']);
    } catch (Exception $e) {}
}

// Clear all session variables
$_SESSION = [];

// Invalidate session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        "",
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// Invalidate Remember-Me cookie if set
if (isset($_COOKIE["studenthub_remember"])) {
    setcookie("studenthub_remember", "", time() - 42000, "/");
}

// Destroy session data on server
session_destroy();

// Redirect to login page with logout confirmation message
header("Location: login.php?logout=1");
exit;
?>
