```php
<?php
session_start();
require_once "db.php";

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST["name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";

    if ($name === "" || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 8) {
        $message = "Enter a valid name, email, and password of at least 8 characters.";
    } else {
        $check = $pdo->prepare("SELECT user_id FROM users WHERE email = ?");
        $check->execute([$email]);

        if ($check->fetch()) {
            $message = "This email is already registered.";
        } else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $pdo->prepare(
                "INSERT INTO users (name, email, password, role)
                 VALUES (?, ?, ?, 'student')"
            );

            $stmt->execute([$name, $email, $hashedPassword]);

            $message = "Registration successful! You can now log in.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StudentHub Registration</title>
</head>
<body>
    <h2>StudentHub Registration</h2>

    <p><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></p>

    <form method="POST" action="register.php">
        <label for="name">Full Name</label>
        <input type="text" id="name" name="name" required>

        <br><br>

        <label for="email">Email</label>
        <input type="email" id="email" name="email" required>

        <br><br>

        <label for="password">Password (minimum 8 characters)</label>
        <input type="password" id="password" name="password"
               minlength="8" required>

        <br><br>

        <button type="submit">Register</button>
    </form>
</body>
</html>
```
