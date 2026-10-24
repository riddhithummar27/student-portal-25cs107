<?php
// ==============================================================================
// Practical 5, 7, 8, 9: Secure Student Registration with Validation & File Storage
// ==============================================================================
session_start();
require_once "db.php";

$message = "";
$messageType = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // 1. Collect and sanitize input fields
    $name = trim(filter_input(INPUT_POST, "name", FILTER_SANITIZE_SPECIAL_CHARS));
    $email = trim(filter_input(INPUT_POST, "email", FILTER_SANITIZE_EMAIL));
    $mobile = trim(filter_input(INPUT_POST, "mobile", FILTER_SANITIZE_SPECIAL_CHARS));
    $course = trim(filter_input(INPUT_POST, "course", FILTER_SANITIZE_SPECIAL_CHARS));
    $semester = filter_input(INPUT_POST, "semester", FILTER_VALIDATE_INT);
    $password = $_POST["password"] ?? "";
    $confirm_password = $_POST["confirm_password"] ?? "";
    $terms = isset($_POST["terms"]);

    // 2. Server-side validation (Practical 5 & 7)
    if (empty($name) || strlen($name) < 3) {
        $message = "Please enter your full name (minimum 3 characters).";
        $messageType = "error";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Please enter a valid email address.";
        $messageType = "error";
    } elseif (!preg_match("/^[0-9]{10}$/", $mobile)) {
        $message = "Please enter a valid 10-digit mobile number.";
        $messageType = "error";
    } elseif (empty($course)) {
        $message = "Please select your course / department.";
        $messageType = "error";
    } elseif ($semester === false || $semester < 1 || $semester > 8) {
        $message = "Please select a valid semester (1 to 8).";
        $messageType = "error";
    } elseif (strlen($password) < 8) {
        $message = "Password must be at least 8 characters long.";
        $messageType = "error";
    } elseif ($password !== $confirm_password) {
        $message = "Passwords do not match.";
        $messageType = "error";
    } elseif (!$terms) {
        $message = "You must agree to the terms and conditions.";
        $messageType = "error";
    } else {
        // 3. Check for duplicate email in database (Practical 9)
        $checkStmt = $pdo->prepare("SELECT user_id FROM users WHERE email = ?");
        $checkStmt->execute([$email]);

        if ($checkStmt->fetch()) {
            $message = "This email address is already registered. Please login instead.";
            $messageType = "error";
        } else {
            // 4. Secure password hashing using bcrypt (Practical 9)
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            try {
                // Insert into users table
                $insertUser = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'student')");
                $insertUser->execute([$name, $email, $hashedPassword]);
                $newUserId = $pdo->lastInsertId();

                // Insert into students table
                $insertStudent = $pdo->prepare("INSERT INTO students (name, email, mobile, department, semester) VALUES (?, ?, ?, ?, ?)");
                $insertStudent->execute([$name, $email, $mobile, $course, $semester]);

                // 5. Store record in CSV file (Practical 7 Requirement)
                $csvFile = __DIR__ . "/registrations.csv";
                $isNewCsv = !file_exists($csvFile);
                $csvHandle = fopen($csvFile, "a");
                if ($csvHandle) {
                    if ($isNewCsv) {
                        fputcsv($csvHandle, ["Timestamp", "Name", "Email", "Mobile", "Course", "Semester"]);
                    }
                    fputcsv($csvHandle, [date("Y-m-d H:i:s"), $name, $email, $mobile, $course, $semester]);
                    fclose($csvHandle);
                }

                // 6. Also append to JSON file (Practical 7 Requirement)
                $jsonFile = __DIR__ . "/registrations.json";
                $jsonData = [];
                if (file_exists($jsonFile)) {
                    $jsonContent = file_get_contents($jsonFile);
                    $jsonData = json_decode($jsonContent, true) ?? [];
                }
                $jsonData[] = [
                    "timestamp" => date("Y-m-d H:i:s"),
                    "name" => $name,
                    "email" => $email,
                    "mobile" => $mobile,
                    "course" => $course,
                    "semester" => $semester
                ];
                file_put_contents($jsonFile, json_encode($jsonData, JSON_PRETTY_PRINT));

                // 7. Audit log (Practical 14 Requirement)
                try {
                    $auditStmt = $pdo->prepare("INSERT INTO audit_logs (user_id, action, details, ip_address) VALUES (?, ?, ?, ?)");
                    $auditStmt->execute([$newUserId, 'user_register', "Registered student: $email", $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1']);
                } catch (Exception $e) {}

                $message = "Registration successful! You can now log in with your credentials.";
                $messageType = "success";
            } catch (PDOException $e) {
                $message = "Registration failed due to database error: " . $e->getMessage();
                $messageType = "error";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Student Registration | StudentHub</title>
  <link rel="stylesheet" href="style.css" />
  <style>
    .form-container {
      max-width: 600px;
      margin: 30px auto;
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
    .form-group input, .form-group select {
      width: 100%;
      padding: 10px 12px;
      border: 1px solid #d1d5db;
      border-radius: 6px;
      font-size: 0.95rem;
      box-sizing: border-box;
    }
    .form-group input:focus, .form-group select:focus {
      outline: none;
      border-color: #3b82f6;
      box-shadow: 0 0 0 3px rgba(59,130,246,0.2);
    }
    .password-meter {
      height: 6px;
      background: #e5e7eb;
      border-radius: 3px;
      margin-top: 6px;
      overflow: hidden;
    }
    .password-meter-bar {
      height: 100%;
      width: 0%;
      transition: width 0.3s ease, background-color 0.3s ease;
    }
    .meter-text {
      font-size: 0.8rem;
      color: #6b7280;
      margin-top: 4px;
    }
    .alert-box {
      padding: 12px 16px;
      border-radius: 8px;
      margin-bottom: 20px;
      font-size: 0.95rem;
    }
    .alert-error {
      background: #fee2e2;
      color: #b91c1c;
      border: 1px solid #f87171;
    }
    .alert-success {
      background: #dcfce7;
      color: #15803d;
      border: 1px solid #86efac;
    }
    .field-error {
      color: #dc2626;
      font-size: 0.82rem;
      margin-top: 4px;
      display: none;
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
        <a href="login.php">Login</a>
        <a href="register.php" class="btn btn-primary active" style="color:white; padding: 6px 14px;">Sign Up</a>
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
    <div class="form-container">
      <h2 style="margin-top: 0; color: #1e3a8a;">Student Registration (Sign Up)</h2>
      <p style="color: #64748b; font-size: 0.9rem; margin-bottom: 24px;">
        Fill out this form to sign up and create your StudentHub portal account.
      </p>

      <?php if (!empty($message)): ?>
        <div class="alert-box <?= $messageType === 'success' ? 'alert-success' : 'alert-error' ?>" role="alert">
          <?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?>
          <?php if ($messageType === 'success'): ?>
            <div style="margin-top: 8px;">
              <a href="login.php" style="font-weight: bold; color: #15803d; text-decoration: underline;">Click here to Login</a>
            </div>
          <?php endif; ?>
        </div>
      <?php endif; ?>

      <!-- Practical 5 & 9: Registration Form with Frontend JS Validation & RegEx -->
      <form id="regForm" method="POST" action="register.php" novalidate>
        <div class="form-group">
          <label for="name">Full Name *</label>
          <input type="text" id="name" name="name" placeholder="e.g. John Doe" required
                 value="<?= htmlspecialchars($_POST['name'] ?? '', ENT_QUOTES, 'UTF-8') ?>" />
          <span class="field-error" id="nameError">Full name must be at least 3 letters.</span>
        </div>

        <div class="form-group">
          <label for="email">Email Address *</label>
          <input type="email" id="email" name="email" placeholder="e.g. student@charusat.edu.in" required
                 value="<?= htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>" />
          <span class="field-error" id="emailError">Please enter a valid email address.</span>
        </div>

        <div class="form-group">
          <label for="mobile">Mobile Number (10 Digits) *</label>
          <input type="tel" id="mobile" name="mobile" placeholder="e.g. 9876543210" maxlength="10" required
                 value="<?= htmlspecialchars($_POST['mobile'] ?? '', ENT_QUOTES, 'UTF-8') ?>" />
          <span class="field-error" id="mobileError">Mobile number must be exactly 10 digits.</span>
        </div>

        <div class="form-group">
          <label for="course">Department / Course *</label>
          <select id="course" name="course" required>
            <option value="">-- Select Course --</option>
            <option value="Information Technology">Information Technology</option>
            <option value="Computer Engineering">Computer Engineering</option>
            <option value="Computer Science">Computer Science</option>
            <option value="Electrical Engineering">Electrical Engineering</option>
            <option value="Mechanical Engineering">Mechanical Engineering</option>
            <option value="Civil Engineering">Civil Engineering</option>
          </select>
          <span class="field-error" id="courseError">Please select your department.</span>
        </div>

        <div class="form-group">
          <label for="semester">Current Semester *</label>
          <select id="semester" name="semester" required>
            <option value="">-- Select Semester --</option>
            <?php for ($i = 1; $i <= 8; $i++): ?>
              <option value="<?= $i ?>">Semester <?= $i ?></option>
            <?php endfor; ?>
          </select>
          <span class="field-error" id="semesterError">Please select your semester.</span>
        </div>

        <div class="form-group">
          <label for="password">Password (Minimum 8 Characters) *</label>
          <input type="password" id="password" name="password" minlength="8" required />
          <div class="password-meter">
            <div class="password-meter-bar" id="meterBar"></div>
          </div>
          <div class="meter-text" id="meterText">Strength: Too short</div>
          <span class="field-error" id="passwordError">Password must be at least 8 characters.</span>
        </div>

        <div class="form-group">
          <label for="confirm_password">Confirm Password *</label>
          <input type="password" id="confirm_password" name="confirm_password" required />
          <span class="field-error" id="confirmError">Passwords do not match.</span>
        </div>

        <div class="form-group" style="display: flex; align-items: center; gap: 8px;">
          <input type="checkbox" id="terms" name="terms" required style="width: auto;" />
          <label for="terms" style="margin-bottom: 0; font-weight: normal; font-size: 0.9rem;">
            I agree to the <a href="terms.html" target="_blank">StudentHub portal terms and code of conduct</a>.
          </label>
        </div>
        <span class="field-error" id="termsError" style="margin-bottom: 12px;">You must accept the terms.</span>

        <button type="submit" class="btn btn-primary" style="width: 100%; padding: 12px; font-size: 1rem; cursor: pointer;">
          Create Student Account
        </button>
      </form>

      <div style="margin-top: 24px; padding: 14px; background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 8px; text-align: center;">
        <span style="color: #1e40af; font-weight: 500;">Already registered with an account?</span><br>
        <a href="login.php" style="color: #2563eb; font-weight: bold; font-size: 1.05rem; text-decoration: underline; display: inline-block; margin-top: 4px;">
          👉 Click here to Log In
        </a>
      </div>
    </div>
  </main>

  <footer class="footer">
    <p>&copy; 2026 StudentHub Portal. Built for Web Development Frameworks Practical Submission.</p>
  </footer>

  <script src="script.js"></script>
  <!-- Practical 5: Frontend JavaScript Regular Expression Validation -->
  <script>
    const regForm = document.getElementById('regForm');
    const nameInput = document.getElementById('name');
    const emailInput = document.getElementById('email');
    const mobileInput = document.getElementById('mobile');
    const courseInput = document.getElementById('course');
    const semesterInput = document.getElementById('semester');
    const passInput = document.getElementById('password');
    const confirmInput = document.getElementById('confirm_password');
    const termsInput = document.getElementById('terms');

    const meterBar = document.getElementById('meterBar');
    const meterText = document.getElementById('meterText');

    // Real-time password strength meter
    passInput.addEventListener('input', function() {
      const val = passInput.value;
      let score = 0;
      if (val.length >= 8) score++;
      if (/[A-Z]/.test(val)) score++;
      if (/[0-9]/.test(val)) score++;
      if (/[^A-Za-z0-9]/.test(val)) score++;

      if (val.length === 0) {
        meterBar.style.width = '0%';
        meterText.textContent = 'Strength: Too short';
      } else if (score <= 1) {
        meterBar.style.width = '25%';
        meterBar.style.backgroundColor = '#ef4444';
        meterText.textContent = 'Strength: Weak';
      } else if (score === 2) {
        meterBar.style.width = '50%';
        meterBar.style.backgroundColor = '#f59e0b';
        meterText.textContent = 'Strength: Medium';
      } else if (score === 3) {
        meterBar.style.width = '75%';
        meterBar.style.backgroundColor = '#3b82f6';
        meterText.textContent = 'Strength: Good';
      } else {
        meterBar.style.width = '100%';
        meterBar.style.backgroundColor = '#10b981';
        meterText.textContent = 'Strength: Strong';
      }
    });

    function showErr(id, show) {
      document.getElementById(id).style.display = show ? 'block' : 'none';
    }

    regForm.addEventListener('submit', function(e) {
      let isValid = true;

      // Name validation (min 3 chars, letters and spaces)
      if (nameInput.value.trim().length < 3) {
        showErr('nameError', true);
        isValid = false;
      } else {
        showErr('nameError', false);
      }

      // Email regex validation
      const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      if (!emailRegex.test(emailInput.value.trim())) {
        showErr('emailError', true);
        isValid = false;
      } else {
        showErr('emailError', false);
      }

      // Mobile regex validation (10 digits)
      const mobileRegex = /^[0-9]{10}$/;
      if (!mobileRegex.test(mobileInput.value.trim())) {
        showErr('mobileError', true);
        isValid = false;
      } else {
        showErr('mobileError', false);
      }

      // Course select validation
      if (!courseInput.value) {
        showErr('courseError', true);
        isValid = false;
      } else {
        showErr('courseError', false);
      }

      // Semester select validation
      if (!semesterInput.value) {
        showErr('semesterError', true);
        isValid = false;
      } else {
        showErr('semesterError', false);
      }

      // Password length validation
      if (passInput.value.length < 8) {
        showErr('passwordError', true);
        isValid = false;
      } else {
        showErr('passwordError', false);
      }

      // Confirm password match
      if (passInput.value !== confirmInput.value || confirmInput.value === '') {
        showErr('confirmError', true);
        isValid = false;
      } else {
        showErr('confirmError', false);
      }

      // Terms acceptance
      if (!termsInput.checked) {
        showErr('termsError', true);
        isValid = false;
      } else {
        showErr('termsError', false);
      }

      if (!isValid) {
        e.preventDefault();
      }
    });
  </script>
</body>
</html>
