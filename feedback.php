<?php
// ==============================================================================
// Practical 7: PHP Form Processing with Server-Side Validation & CSV/JSON Storage
// ==============================================================================
session_start();

$message = "";
$messageType = "";

$csvFile = __DIR__ . "/feedback.csv";
$jsonFile = __DIR__ . "/feedback.json";

// Handle POST Form Submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // 1. Sanitize Inputs
    $name = trim(filter_input(INPUT_POST, "name", FILTER_SANITIZE_SPECIAL_CHARS));
    $email = trim(filter_input(INPUT_POST, "email", FILTER_SANITIZE_EMAIL));
    $category = trim(filter_input(INPUT_POST, "category", FILTER_SANITIZE_SPECIAL_CHARS));
    $rating = filter_input(INPUT_POST, "rating", FILTER_VALIDATE_INT);
    $comments = trim(filter_input(INPUT_POST, "comments", FILTER_SANITIZE_SPECIAL_CHARS));

    // 2. Server-Side Validation
    if (empty($name) || strlen($name) < 2) {
        $message = "Please enter your valid name.";
        $messageType = "error";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Please enter a valid email address.";
        $messageType = "error";
    } elseif ($rating === false || $rating < 1 || $rating > 5) {
        $message = "Please select a star rating from 1 to 5.";
        $messageType = "error";
    } elseif (empty($comments) || strlen($comments) < 5) {
        $message = "Please provide detailed feedback (at least 5 characters).";
        $messageType = "error";
    } else {
        $timestamp = date("Y-m-d H:i:s");

        // 3. Write to CSV File
        $isNewCsv = !file_exists($csvFile);
        $handle = fopen($csvFile, "a");
        if ($handle) {
            if ($isNewCsv) {
                fputcsv($handle, ["Timestamp", "Name", "Email", "Category", "Rating", "Comments"]);
            }
            fputcsv($handle, [$timestamp, $name, $email, $category, $rating, $comments]);
            fclose($handle);
        }

        // 4. Write to JSON File
        $feedbacks = [];
        if (file_exists($jsonFile)) {
            $feedbacks = json_decode(file_get_contents($jsonFile), true) ?? [];
        }
        $feedbacks[] = [
            "timestamp" => $timestamp,
            "name" => $name,
            "email" => $email,
            "category" => $category,
            "rating" => $rating,
            "comments" => $comments
        ];
        file_put_contents($jsonFile, json_encode($feedbacks, JSON_PRETTY_PRINT));

        $message = "Thank you! Your feedback has been recorded safely in CSV and JSON.";
        $messageType = "success";
    }
}

// Read existing feedback records to display (Intermediate Extension of Practical 7)
$existingFeedback = [];
if (file_exists($jsonFile)) {
    $existingFeedback = array_reverse(json_decode(file_get_contents($jsonFile), true) ?? []);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Student Feedback | StudentHub</title>
  <link rel="stylesheet" href="style.css" />
  <style>
    .form-box {
      max-width: 650px;
      margin: 24px auto;
      background: var(--card-bg, #ffffff);
      padding: 28px;
      border-radius: 12px;
      box-shadow: 0 4px 14px rgba(0,0,0,0.06);
    }
    .form-group {
      margin-bottom: 16px;
    }
    .form-group label {
      display: block;
      font-weight: 600;
      margin-bottom: 6px;
      font-size: 0.95rem;
    }
    .form-group input, .form-group select, .form-group textarea {
      width: 100%;
      padding: 10px 12px;
      border: 1px solid #d1d5db;
      border-radius: 6px;
      font-size: 0.95rem;
      box-sizing: border-box;
      font-family: inherit;
    }
    .alert-box {
      padding: 12px 16px;
      border-radius: 8px;
      margin-bottom: 20px;
    }
    .alert-error { background: #fee2e2; color: #b91c1c; border: 1px solid #f87171; }
    .alert-success { background: #dcfce7; color: #15803d; border: 1px solid #86efac; }
    .review-card {
      background: #f8fafc;
      border: 1px solid #e2e8f0;
      border-radius: 8px;
      padding: 14px;
      margin-bottom: 12px;
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
        <a href="about.html">About</a>
        <a href="dynamic.html">Events & Activities</a>
        <a href="faq.html">FAQ</a>
        <a href="feedback.php" class="active">Feedback</a>
        <a href="contact.html">Contact</a>
        <a href="login.php">Login</a>
      </nav>
      <div class="nav-actions">
        <button id="themeToggle" class="icon-btn" aria-label="Toggle dark mode">🌙</button>
        <button id="hamburger" class="hamburger" aria-label="Toggle menu" aria-expanded="false">
          <span></span><span></span><span></span>
        </button>
      </div>
    </div>
  </header>

  <main id="main" class="container" style="max-width: 900px; margin: 30px auto; padding: 0 16px;">
    <!-- Breadcrumb -->
    <nav aria-label="Breadcrumb" style="font-size: 0.85rem; color: #64748b; margin-bottom: 18px;">
      <a href="index.php" style="color: #2563eb;">Home</a> &gt; <span>Feedback</span>
    </nav>

    <div class="page-header">
      <h1 style="color: #1e3a8a;">Student Portal Feedback</h1>
      <p style="color: #64748b;">Practical 7: Server-side validation and file storage in CSV and JSON formats.</p>
    </div>

    <div class="form-box">
      <?php if (!empty($message)): ?>
        <div class="alert-box alert-<?= $messageType ?>" role="alert">
          <?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?>
        </div>
      <?php endif; ?>

      <form method="POST" action="feedback.php">
        <div class="form-group">
          <label for="name">Your Name</label>
          <input type="text" id="name" name="name" required placeholder="e.g. Priyansh Shah" />
        </div>

        <div class="form-group">
          <label for="email">Student Email</label>
          <input type="email" id="email" name="email" required placeholder="e.g. 25cs107@charusat.edu.in" />
        </div>

        <div class="form-group">
          <label for="category">Category</label>
          <select id="category" name="category" required>
            <option value="Portal UI & Experience">Portal UI & User Experience</option>
            <option value="Academic Features">Academic & Attendance Features</option>
            <option value="Events & Clubs">Events & Extracurricular Activities</option>
            <option value="Bug Report">Technical Bug Report</option>
          </select>
        </div>

        <div class="form-group">
          <label for="rating">Satisfaction Rating (1 to 5 Stars)</label>
          <select id="rating" name="rating" required>
            <option value="5">⭐⭐⭐⭐⭐ 5 - Excellent</option>
            <option value="4">⭐⭐⭐⭐ 4 - Good</option>
            <option value="3">⭐⭐⭐ 3 - Average</option>
            <option value="2">⭐⭐ 2 - Poor</option>
            <option value="1">⭐ 1 - Very Bad</option>
          </select>
        </div>

        <div class="form-group">
          <label for="comments">Your Comments / Suggestions</label>
          <textarea id="comments" name="comments" rows="4" required placeholder="Tell us what you liked or how we can improve..."></textarea>
        </div>

        <button type="submit" class="btn btn-primary" style="width: 100%; padding: 12px; cursor: pointer;">
          Submit Feedback (Save to CSV/JSON)
        </button>
      </form>
    </div>

    <!-- Practical 7 Intermediate Extension: Display Stored Records -->
    <div style="background: var(--card-bg, #ffffff); padding: 24px; border-radius: 12px; margin-top: 30px; box-shadow: 0 4px 12px rgba(0,0,0,0.06);">
      <h3 style="margin-top: 0; color: #1e3a8a;">Recent Feedback Submissions (Stored in JSON)</h3>
      <?php if (!empty($existingFeedback)): ?>
        <?php foreach (array_slice($existingFeedback, 0, 5) as $fb): ?>
          <div class="review-card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
              <strong><?= htmlspecialchars($fb['name'] ?? 'Student', ENT_QUOTES, 'UTF-8') ?></strong>
              <span style="color: #f59e0b;"><?= str_repeat('⭐', (int)($fb['rating'] ?? 5)) ?></span>
            </div>
            <div style="font-size: 0.85rem; color: #64748b; margin-bottom: 6px;">
              <?= htmlspecialchars($fb['category'] ?? 'General', ENT_QUOTES, 'UTF-8') ?> &bull; <?= htmlspecialchars($fb['timestamp'] ?? '', ENT_QUOTES, 'UTF-8') ?>
            </div>
            <p style="margin: 0; font-size: 0.92rem; color: #334155;"><?= htmlspecialchars($fb['comments'] ?? '', ENT_QUOTES, 'UTF-8') ?></p>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <p style="color: #94a3b8;">No feedback submitted yet. Be the first to share your review!</p>
      <?php endif; ?>
    </div>
  </main>

  <footer class="footer">
    <p>&copy; 2026 StudentHub Portal &bull; CHARUSAT FTE</p>
  </footer>

  <script src="script.js"></script>
</body>
</html>
