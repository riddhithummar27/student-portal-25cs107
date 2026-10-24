<?php
require_once "auth.php";

if ($_SESSION["role"] !== "admin") {
    http_response_code(403);
    exit("403 Forbidden: Admin access required.");
}
?>