<?php
// ==============================================================================
// Practical 10 & 14: Student Dashboard with Session RBAC & Dynamic Content
// ==============================================================================
require_once "auth.php";
require_once "db.php";

// Allow students (and admins if inspecting)
if (($_SESSION["role"] ?? "") !== "student" && ($_SESSION["role"] ?? "") !== "admin") {
    http_response_code(403);
    exit("403 Forbidden: Student access required.");
}

function h($v) {
    return htmlspecialchars((string)($v ?? ""), ENT_QUOTES, "UTF-8");
}

$userEmail = $_SESSION["email"] ?? "";
$userName = $_SESSION["name"] ?? "Student";

// Fetch student info from MySQL if present
$studentInfo = null;
try {
    $stmt = $pdo->prepare("SELECT * FROM students WHERE email = ? LIMIT 1");
    $stmt->execute([$userEmail]);
    $studentInfo = $stmt->fetch();

    // Fetch upcoming events from MySQL
    $upcomingEvents = $pdo->query("SELECT * FROM events ORDER BY event_date ASC LIMIT 4")->fetchAll();
} catch (PDOException $e) {
    $upcomingEvents = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Student Dashboard | StudentHub</title>
  <link rel="stylesheet" href="style.css" />
  <style>
    .welcome-card {
      background: linear-gradient(135deg, #1e3a8a, #3b82f6);
      color: white;
      padding: 24px;
      border-radius: 12px;
      margin-bottom: 24px;
      box-shadow: 0 4px 12px rgba(37,99,235,0.2);
    }
    .welcome-card h1 {
      margin: 0 0 8px 0;
      font-size: 1.8rem;
    }
    .welcome-card p {
      margin: 0;
      opacity: 0.9;
    }
    .grid-dashboard {
      display: grid;
      grid-template-columns: 2fr 1fr;
      gap: 20px;
    }
    @media (max-width: 768px) {
      .grid-dashboard { grid-template-columns: 1fr; }
    }
    .card {
      background: var(--card-bg, #ffffff);
      padding: 20px;
      border-radius: 12px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.06);
      margin-bottom: 20px;
    }
    .card h3 {
      margin-top: 0;
      color: #1e293b;
      border-bottom: 2px solid #f1f5f9;
      padding-bottom: 10px;
    }
    .event-item {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 12px 0;
      border-bottom: 1px solid #f1f5f9;
    }
    .event-item:last-child { border-bottom: none; }
    .event-badge {
      background: #eff6ff;
      color: #2563eb;
      font-size: 0.8rem;
      padding: 4px 8px;
      border-radius: 6px;
      font-weight: 600;
    }
  </style>
</head>
<body>
  <a href="#main" class="skip-link">Skip to main content</a>

  <header class="navbar">
    <div class="nav-container">
      <a href="student_dashboard.php" class="logo">Student<span>Hub</span></a>
      <nav class="nav-links" id="navLinks" aria-label="Primary navigation">
        <a href="student_dashboard.php" class="active">Dashboard</a>
        <a href="dynamic.html">Campus Events & Interactivity</a>
        <a href="attendance.html">Attendance</a>
        <a href="result.html">Result</a>
        <a href="timetable.html">Timetable</a>
        <a href="payment.html">Payment</a>
        <a href="course.html">Courses</a>
        <a href="notices.html">Notices</a>
        <a href="profile.html">Profile</a>
        <a href="contact.html">Contact</a>
        <a href="logout.php" style="color: #dc2626; font-weight: bold;">Logout</a>
      </nav>
      <div class="nav-actions">
        <button id="themeToggle" class="icon-btn" aria-label="Toggle dark mode">🌙</button>
        <button id="hamburger" class="hamburger" aria-label="Toggle menu" aria-expanded="false">
          <span></span><span></span><span></span>
        </button>
      </div>
    </div>
  </header>

  <main id="main" class="container" style="max-width: 1100px; margin: 24px auto; padding: 0 16px;">
    <!-- Welcome Banner with Logged-in Student Info -->
    <div class="welcome-card">
      <h1>Welcome back, <?= h($userName) ?>! 👋</h1>
      <p>
        <?= $studentInfo ? h($studentInfo['department']) . " | Semester " . h($studentInfo['semester']) : "Student Portal Member" ?>
        &bull; Email: <?= h($userEmail) ?>
      </p>
    </div>

    <!-- Quick Stats -->
    <section class="stats-grid" aria-label="Academic stats">
      <div class="stat-card">
        <div class="label">Attendance</div>
        <div class="value">87%</div>
      </div>
      <div class="stat-card">
        <div class="label">Last Result</div>
        <div class="value">SGPA 8.4</div>
      </div>
      <div class="stat-card">
        <div class="label">Pending Dues</div>
        <div class="value">₹0.00</div>
      </div>
      <div class="stat-card">
        <div class="label">Credits Earned</div>
        <div class="value">46 / 160</div>
      </div>
    </section>

    <div class="grid-dashboard">
      <div>
        <!-- Dynamic College Events from MySQL Database -->
        <div class="card">
          <h3>Upcoming Campus Events (Live from Database)</h3>
          <?php if (!empty($upcomingEvents)): ?>
            <?php foreach ($upcomingEvents as $ev): ?>
              <div class="event-item">
                <div>
                  <strong><?= h($ev["event_name"]) ?></strong><br>
                  <small style="color: #64748b;">📍 <?= h($ev["venue"]) ?> &bull; 📅 <?= h($ev["event_date"]) ?></small>
                </div>
                <div>
                  <span class="event-badge"><?= h($ev["category"] ?? "General") ?></span>
                </div>
              </div>
            <?php endforeach; ?>
          <?php else: ?>
            <p style="color: #94a3b8;">No upcoming events scheduled right now.</p>
          <?php endif; ?>
          <div style="margin-top: 14px; text-align: right;">
            <a href="dynamic.html" style="color: #2563eb; font-weight: 600; font-size: 0.9rem;">View interactive events slider & search &rarr;</a>
          </div>
        </div>

        <!-- Academic Shortcuts -->
        <h2 class="section-title">Academic Services</h2>
        <section class="card-grid" aria-label="Academic Links">
          <a class="link-card" href="attendance.html">
            <div class="icon">🗓️</div>
            <h3>Attendance</h3>
            <p>Subject-wise attendance breakdown.</p>
          </a>
          <a class="link-card" href="result.html">
            <div class="icon">📊</div>
            <h3>Results</h3>
            <p>Semester marks, SGPA and CGPA.</p>
          </a>
          <a class="link-card" href="timetable.html">
            <div class="icon">📅</div>
            <h3>Timetable</h3>
            <p>Weekly lecture and laboratory routine.</p>
          </a>
          <a class="link-card" href="payment.html">
            <div class="icon">💳</div>
            <h3>Fee Payment</h3>
            <p>College and exam fees payment portal.</p>
          </a>
        </section>
      </div>

      <div>
        <!-- Notice Board -->
        <div class="card">
          <h3>Notice Board</h3>
          <ul style="padding-left: 20px; color: #475569; font-size: 0.92rem; line-height: 1.6;">
            <li><strong>Mid-Sem Exam:</strong> Commencing from 15th Nov 2026.</li>
            <li><strong>Tech Fest 2026:</strong> Registration closes this Friday!</li>
            <li><strong>Library:</strong> Renew books before semester end.</li>
          </ul>
          <a href="notices.html" style="font-size: 0.88rem; color: #2563eb; font-weight: 600;">View all notices &rarr;</a>
        </div>

        <!-- Quick Profile Card -->
        <div class="card">
          <h3>My Account</h3>
          <p style="font-size: 0.9rem; color: #64748b; margin-bottom: 12px;">
            Role: <strong><?= h(strtoupper($_SESSION["role"] ?? "STUDENT")) ?></strong><br>
            Session Active: <strong>15 Min Inactivity Guard</strong>
          </p>
          <a href="profile.html" class="btn btn-primary btn-block" style="text-align: center;">View Full Profile</a>
        </div>
      </div>
    </div>
  </main>

  <footer class="footer">
    <p>&copy; 2026 StudentHub Portal &bull; CHARUSAT FTE</p>
  </footer>

  <script src="script.js"></script>
</body>
</html>
