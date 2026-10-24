<?php
require_once "auth.php";

if ($_SESSION["role"] !== "student") {
    http_response_code(403);
    exit("403 Forbidden: Student access required.");
}
?>