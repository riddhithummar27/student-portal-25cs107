<?php
// ==============================================================================
// Practical 10: Secure Login with Sessions, Role-Based Access & Remember-Me
// ==============================================================================
session_start();
require_once "db.php";

$message = "";
$messageType = "error";

// If already logged in, redirect based on role
if (isset($_SESSION["user_id"])) {
    if (($_SESSION["role"] ?? '') === "admin") {
        header("Location: admin_dashboard.php");
        exit;
    } else {
        header("Location: student_dashboard.php");
        exit;
    }
}

// Timeout or logout message feedback
if (isset($_GET["timeout"])) {
    $message = "Your session expired due to inactivity. Please log in again.";
    $messageType = "warning";
} elseif (isset($_GET["logout"])) {
    $message = "You have been logged out successfully.";
    $messageType = "success";
}

// Handle Form Submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim(filter_input(INPUT_POST, "email", FILTER_SANITIZE_EMAIL));
    $password = $_POST["password"] ?? "";
    $remember = isset($_POST["remember"]);

    if (empty($email) || empty($password)) {
        $message = "Please enter both email and password.";
        $messageType = "error";
    } else {
        // Query user record using prepared statement
        $stmt = $pdo->prepare("SELECT user_id, name, email, password, role FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        // Verify password hash
        if ($user && password_verify($password, $user["password"])) {
            // Prevent Session Fixation by regenerating ID
            session_regenerate_id(true);

            $_SESSION["user_id"] = $user["user_id"];
            $_SESSION["name"] = $user["name"];
            $_SESSION["email"] = $user["email"];
            $_SESSION["role"] = $user["role"];
            $_SESSION["last_activity"] = time();

            // Remember Me Token implementation
            if ($remember) {
                $token = bin2hex(random_bytes(32));
                $tokenHash = hash('sha256', $token);
                // Store token in database
                $updateToken = $pdo->prepare("UPDATE users SET remember_token = ? WHERE user_id = ?");
                $updateToken->execute([$tokenHash, $user["user_id"]]);
                // Set cookie for 7 days
                setcookie("studenthub_remember", $user["user_id"] . ":" . $token, time() + (86400 * 7), "/", "", false, true);
            }

            // Audit log action
            try {
                $audit = $pdo->prepare("INSERT INTO audit_logs (user_id, action, details, ip_address) VALUES (?, ?, ?, ?)");
                $audit->execute([$user["user_id"], "user_login", "User logged in as " . $user["role"], $_SERVER["REMOTE_ADDR"] ?? '127.0.0.1']);
            } catch (Exception $e) {}

            // Role-Based Redirection (RBAC)
            if ($user["role"] === "admin") {
                header("Location: admin_dashboard.php");
            } else {
                header("Location: student_dashboard.php");
            }
            exit;
        } else {
            $message = "Invalid email or password. Please try again.";
            $messageType = "error";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Login | StudentHub Portal</title>
  <link rel="stylesheet" href="style.css" />
  <style>
    .auth-box {
      max-width: 420px;
      margin: 40px auto;
      background: var(--card-bg, #ffffff);
      padding: 30px;
      border-radius: 12px;
      box-shadow: 0 4px 16px rgba(0,0,0,0.08);
    }
    .form-group {
      margin-bottom: 18px;
    }
    .form-group label {
      display: block;
      font-weight: 600;
      margin-bottom: 6px;
      font-size: 0.95rem;
    }
    .form-group input {
      width: 100%;
      padding: 10px 12px;
      border: 1px solid #d1d5db;
      border-radius: 6px;
      font-size: 0.95rem;
      box-sizing: border-box;
    }
    .form-group input:focus {
      outline: none;
      border-color: #3b82f6;
      box-shadow: 0 0 0 3px rgba(59,130,246,0.2);
    }
    .alert-box {
      padding: 12px 16px;
      border-radius: 8px;
      margin-bottom: 20px;
      font-size: 0.92rem;
    }
    .alert-error {
      background: #fee2e2;
      color: #b91c1c;
      border: 1px solid #f87171;
    }
    .alert-warning {
      background: #fef3c7;
      color: #b45309;
      border: 1px solid #fcd34d;
    }
    .alert-success {
      background: #dcfce7;
      color: #15803d;
      border: 1px solid #86efac;
    }
    .demo-creds {
      margin-top: 20px;
      padding: 12px;
      background: #f8fafc;
      border: 1px dashed #cbd5e1;
      border-radius: 8px;
      font-size: 0.85rem;
      color: #475569;
    }
  </style>
</head>
<body>
  <a href="#main" class="skip-link">Skip to main content</a>

  <header class="navbar">
    <div class="nav-container">
      <a href="index.php" class="logo">Student<span>Hub</span></a>
      <nav class="nav-links" id="navLinks" aria-label="Primary navigation">
        <a href="index.php">Home</a>
        <a href="dynamic.html">Events & Interactivity</a>
        <a href="login.php" class="active">Login</a>
        <a href="register.php">Register</a>
        <a href="contact.html">Contact</a>
      </nav>
      <div class="nav-actions">
        <button id="themeToggle" class="icon-btn" aria-label="Toggle dark mode">🌙</button>
        <button id="hamburger" class="hamburger" aria-label="Toggle menu" aria-expanded="false">
          <span></span><span></span><span></span>
        </button>
      </div>
    </div>
  </header>

  <main id="main" class="container" style="padding: 20px 16px;">
    <div class="auth-box">
      <h2 style="margin-top: 0; color: #1e3a8a; text-align: center;">Sign In to StudentHub</h2>
      <p style="text-align: center; color: #64748b; font-size: 0.9rem; margin-bottom: 20px;">
        Access your academic dashboard, events, and records.
      </p>

      <?php if (!empty($message)): ?>
        <div class="alert-box alert-<?= $messageType ?>" role="alert">
          <?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?>
        </div>
      <?php endif; ?>

      <form method="POST" action="login.php" novalidate>
        <div class="form-group">
          <label for="email">Email Address</label>
          <input type="email" id="email" name="email" required placeholder="your.name@studenthub.com"
                 value="<?= htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>" />
        </div>

        <div class="form-group">
          <label for="password">Password</label>
          <input type="password" id="password" name="password" required placeholder="Enter password" />
        </div>

        <div class="form-group" style="display: flex; justify-content: space-between; align-items: center;">
          <label style="margin-bottom: 0; font-weight: normal; font-size: 0.9rem; display: flex; align-items: center; gap: 6px;">
            <input type="checkbox" name="remember" value="1" style="width: auto;" /> Remember me
          </label>
          <a href="contact.html" style="font-size: 0.85rem; color: #2563eb;">Forgot password?</a>
        </div>

        <button type="submit" class="btn btn-primary" style="width: 100%; padding: 12px; font-size: 1rem; cursor: pointer;">
          Log In
        </button>
      </form>

      <div class="demo-creds">
        <strong>Default Test Credentials:</strong><br>
        • Admin: <code>admin@studenthub.com</code> / <code>admin123</code><br>
        • Student: <code>student@studenthub.com</code> / <code>student123</code>
      </div>

      <p style="text-align: center; margin-top: 20px; font-size: 0.9rem; color: #64748b;">
        New to StudentHub? <a href="register.php" style="color: #2563eb; font-weight: 600;">Register here</a>
      </p>
    </div>
  </main>

  <footer class="footer">
    <p>&copy; 2026 StudentHub Portal. Built for Web Development Frameworks Practical Submission.</p>
  </footer>

  <script src="script.js"></script>
</body>
</html>
