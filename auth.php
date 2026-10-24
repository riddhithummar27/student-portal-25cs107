
<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$timeout = 900; // 15 minutes

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

if (
    isset($_SESSION["last_activity"]) &&
    time() - $_SESSION["last_activity"] > $timeout
) {
    $_SESSION = [];
    session_destroy();

    header("Location: login.php?timeout=1");
    exit;
}

$_SESSION["last_activity"] = time();
?>

