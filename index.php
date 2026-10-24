<?php
// ==============================================================================
// StudentHub Portal — Main Landing / Home Page (Practical 1, 2, 3)
// ==============================================================================
session_start();
require_once "db.php";

$totalStudents = 0;
$totalEvents = 0;
try {
    $totalStudents = (int)$pdo->query("SELECT COUNT(*) FROM students")->fetchColumn();
    $totalEvents = (int)$pdo->query("SELECT COUNT(*) FROM events")->fetchColumn();
} catch (Exception $e) {}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>StudentHub | University Portal</title>
  <link rel="stylesheet" href="style.css" />
  <style>
    .hero {
      background: linear-gradient(135deg, #1e3a8a, #2563eb);
      color: white;
      padding: 60px 20px;
      text-align: center;
      border-radius: 16px;
      margin-bottom: 40px;
      box-shadow: 0 10px 25px rgba(37,99,235,0.2);
    }
    .hero h1 {
      font-size: 2.5rem;
      margin-bottom: 16px;
    }
    .hero p {
      font-size: 1.15rem;
      max-width: 700px;
      margin: 0 auto 28px auto;
      opacity: 0.92;
      line-height: 1.6;
    }
    .hero-buttons {
      display: flex;
      justify-content: center;
      gap: 16px;
      flex-wrap: wrap;
    }
    .hero-btn {
      padding: 12px 28px;
      border-radius: 8px;
      font-weight: bold;
      text-decoration: none;
      font-size: 1rem;
      transition: transform 0.2s, box-shadow 0.2s;
    }
    .hero-btn-primary {
      background: #ffffff;
      color: #1e3a8a;
    }
    .hero-btn-outline {
      background: transparent;
      color: white;
      border: 2px solid white;
    }
    .hero-btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }
    .feature-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
      gap: 24px;
      margin-bottom: 40px;
    }
    .feature-card {
      background: var(--card-bg, #ffffff);
      padding: 28px;
      border-radius: 12px;
      box-shadow: 0 4px 14px rgba(0,0,0,0.06);
      text-align: center;
    }
    .feature-card .icon {
      font-size: 2.4rem;
      margin-bottom: 14px;
    }
    .feature-card h3 {
      margin-top: 0;
      color: #1e293b;
    }
    .feature-card p {
      color: #64748b;
      font-size: 0.95rem;
      line-height: 1.5;
    }
  </style>
</head>
<body>
  <!-- Accessibility Skip-to-Content Link (Practical 2) -->
  <a href="#main" class="skip-link">Skip to main content</a>

  <header class="navbar">
    <div class="nav-container">
      <a href="index.php" class="logo">Student<span>Hub</span></a>
      <nav class="nav-links" id="navLinks" aria-label="Primary navigation">
        <a href="index.php" class="active">Home</a>
        <a href="about.html">About</a>
        <a href="dynamic.html">Events & Activities</a>
        <a href="faq.html">FAQ</a>
        <a href="feedback.html">Feedback</a>
        <a href="contact.html">Contact</a>
        <?php if (isset($_SESSION["user_id"])): ?>
          <?php if (($_SESSION["role"] ?? "") === "admin"): ?>
            <a href="admin_dashboard.php" style="color: #2563eb; font-weight: bold;">Admin Panel</a>
          <?php else: ?>
            <a href="student_dashboard.php" style="color: #2563eb; font-weight: bold;">My Dashboard</a>
          <?php endif; ?>
          <a href="logout.php" style="color: #dc2626;">Logout</a>
        <?php else: ?>
          <a href="login.php">Login</a>
          <a href="register.php" class="btn btn-primary" style="color:white; padding: 6px 14px;">Register</a>
        <?php endif; ?>
      </nav>
      <div class="nav-actions">
        <button id="themeToggle" class="icon-btn" aria-label="Toggle dark mode">🌙</button>
        <button id="hamburger" class="hamburger" aria-label="Toggle menu" aria-expanded="false">
          <span></span><span></span><span></span>
        </button>
      </div>
    </div>
  </header>

  <main id="main" class="container" style="max-width: 1100px; margin: 30px auto; padding: 0 16px;">
    <!-- Hero Section -->
    <section class="hero" aria-labelledby="heroHeading">
      <h1 id="heroHeading">Empowering Campus Life at CHARUSAT</h1>
      <p>
        StudentHub is the unified academic portal designed for students and faculty.
        Manage registrations, view course progress, stay updated with campus events, and access all college services in one place.
      </p>
      <div class="hero-buttons">
        <?php if (!isset($_SESSION["user_id"])): ?>
          <a href="login.php" class="hero-btn hero-btn-primary">Student / Staff Login</a>
          <a href="register.php" class="hero-btn hero-btn-outline">Create Account</a>
        <?php else: ?>
          <a href="<?= ($_SESSION["role"] ?? '') === 'admin' ? 'admin_dashboard.php' : 'student_dashboard.php' ?>" class="hero-btn hero-btn-primary">
            Go to Your Dashboard &rarr;
          </a>
        <?php endif; ?>
        <a href="dynamic.html" class="hero-btn hero-btn-outline">Explore Campus Events</a>
      </div>
    </section>

    <!-- Live Campus Counter -->
    <section class="stats-grid" aria-label="Campus metrics">
      <div class="stat-card">
        <div class="label">Registered Students</div>
        <div class="value"><?= $totalStudents ?>+</div>
      </div>
      <div class="stat-card">
        <div class="label">College Events</div>
        <div class="value"><?= $totalEvents ?>+</div>
      </div>
      <div class="stat-card">
        <div class="label">Active Departments</div>
        <div class="value">6</div>
      </div>
      <div class="stat-card">
        <div class="label">Campus Portal Version</div>
        <div class="value">2.0 (Sem 3)</div>
      </div>
    </section>

    <!-- Key Modules / Features -->
    <h2 class="section-title" style="text-align: center; margin: 36px 0 20px 0;">StudentHub Core Modules</h2>
    <section class="feature-grid" aria-label="Core Portal Modules">
      <article class="feature-card">
        <div class="icon">🎓</div>
        <h3>Student Management</h3>
        <p>Complete record management with real-time search, department filtering, and fast CRUD operations.</p>
        <a href="login.php" style="color: #2563eb; font-weight: 600; font-size: 0.9rem;">View Directory &rarr;</a>
      </article>

      <article class="feature-card">
        <div class="icon">🎪</div>
        <h3>Campus Events & Clubs</h3>
        <p>Stay up to date with hackathons, cultural festivals, sports leagues, and workshops across CHARUSAT.</p>
        <a href="dynamic.html" style="color: #2563eb; font-weight: 600; font-size: 0.9rem;">Browse Events &rarr;</a>
      </article>

      <article class="feature-card">
        <div class="icon">📊</div>
        <h3>Academic Records</h3>
        <p>Check semester attendance, examination results, timetable schedules, and downloadable grade reports.</p>
        <a href="dashboard.html" style="color: #2563eb; font-weight: 600; font-size: 0.9rem;">Open Academics &rarr;</a>
      </article>

      <article class="feature-card">
        <div class="icon">🔒</div>
        <h3>Security & RBAC</h3>
        <p>Built with password hashing, session guards, audit logging, and role-based permissions.</p>
        <a href="login.php" style="color: #2563eb; font-weight: 600; font-size: 0.9rem;">Security Info &rarr;</a>
      </article>
    </section>
  </main>

  <footer class="footer">
    <p>&copy; 2026 StudentHub Portal &bull; CHARUSAT FTE ITUE203 Web Development Frameworks</p>
    <p style="font-size: 0.85rem; margin-top: 6px;">
      <a href="about.html" style="color: inherit; margin: 0 8px;">About</a>
      <a href="faq.html" style="color: inherit; margin: 0 8px;">FAQ</a>
      <a href="feedback.html" style="color: inherit; margin: 0 8px;">Feedback</a>
      <a href="contact.html" style="color: inherit; margin: 0 8px;">Contact</a>
    </p>
  </footer>

  <script src="script.js"></script>
</body>
</html>
