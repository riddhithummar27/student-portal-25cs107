<?php
// Setup script for StudentHub MySQL Database in XAMPP
$host = "localhost";
$username = "root";
$password = "";

try {
    $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 1. Create database if not exists
    $pdo->exec("CREATE DATABASE IF NOT EXISTS studenthub CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE studenthub");

    echo "Using database studenthub\n";

    // 2. Create users table
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        user_id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(150) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        role ENUM('admin', 'student') NOT NULL DEFAULT 'student',
        remember_token VARCHAR(255) NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB;");

    // 3. Create students table (20+ records needed for Practical 11)
    $pdo->exec("CREATE TABLE IF NOT EXISTS students (
        student_id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(150) NOT NULL UNIQUE,
        mobile VARCHAR(20) NOT NULL,
        department VARCHAR(50) NOT NULL,
        semester INT NOT NULL,
        status ENUM('active', 'inactive') DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB;");

    // Ensure status column exists if table existed previously
    try {
        $pdo->exec("ALTER TABLE students ADD COLUMN status ENUM('active', 'inactive') DEFAULT 'active'");
    } catch (Exception $e) {}

    // 4. Create events table
    $pdo->exec("CREATE TABLE IF NOT EXISTS events (
        event_id INT AUTO_INCREMENT PRIMARY KEY,
        event_name VARCHAR(150) NOT NULL,
        event_date DATE NOT NULL,
        venue VARCHAR(150) NOT NULL,
        description TEXT,
        category VARCHAR(50) DEFAULT 'General',
        poster_path VARCHAR(255) NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB;");

    // Ensure category and poster_path columns exist
    try {
        $pdo->exec("ALTER TABLE events ADD COLUMN poster_path VARCHAR(255) NULL");
    } catch (Exception $e) {}
    try {
        $pdo->exec("ALTER TABLE events ADD COLUMN category VARCHAR(50) DEFAULT 'General'");
    } catch (Exception $e) {}

    // 5. Create registrations table
    $pdo->exec("CREATE TABLE IF NOT EXISTS registrations (
        registration_id INT AUTO_INCREMENT PRIMARY KEY,
        student_id INT NOT NULL,
        event_id INT NOT NULL,
        registration_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX (student_id),
        INDEX (event_id)
    ) ENGINE=InnoDB;");

    // 6. Create audit_logs table
    $pdo->exec("CREATE TABLE IF NOT EXISTS audit_logs (
        log_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        user_id INT NULL,
        action VARCHAR(100) NOT NULL,
        details TEXT NULL,
        ip_address VARCHAR(45) NULL,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_audit_created_at (created_at),
        INDEX idx_audit_user_id (user_id)
    ) ENGINE=InnoDB;");

    // Seed default Admin & Student users
    $stmt = $pdo->prepare("SELECT user_id FROM users WHERE email = ?");
    $stmt->execute(['admin@studenthub.com']);
    if (!$stmt->fetch()) {
        $adminPass = password_hash("admin123", PASSWORD_DEFAULT);
        $insert = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'admin')");
        $insert->execute(['Admin Officer', 'admin@studenthub.com', $adminPass]);
        echo "Default admin created: admin@studenthub.com / admin123\n";
    }

    $stmt->execute(['student@studenthub.com']);
    if (!$stmt->fetch()) {
        $studentPass = password_hash("student123", PASSWORD_DEFAULT);
        $insert = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'student')");
        $insert->execute(['Riddhi Thummar', 'student@studenthub.com', $studentPass]);
        echo "Default student created: student@studenthub.com / student123\n";
    }

    // Seed students table with at least 20 records if count < 20
    $count = $pdo->query("SELECT COUNT(*) FROM students")->fetchColumn();
    if ($count < 20) {
        $sampleStudents = [
            ['Riddhi Thummar', 'riddhi@studenthub.com', '9876543201', 'Information Technology', 3],
            ['Aarav Patel', 'aarav.patel@studenthub.com', '9876543202', 'Computer Engineering', 3],
            ['Diya Shah', 'diya.shah@studenthub.com', '9876543203', 'Information Technology', 5],
            ['Kavya Joshi', 'kavya.joshi@studenthub.com', '9876543204', 'Computer Science', 3],
            ['Rohan Mehta', 'rohan.mehta@studenthub.com', '9876543205', 'Electrical Engineering', 5],
            ['Ananya Sharma', 'ananya.s@studenthub.com', '9876543206', 'Information Technology', 3],
            ['Dev Parekh', 'dev.parekh@studenthub.com', '9876543207', 'Computer Engineering', 5],
            ['Pooja Desai', 'pooja.desai@studenthub.com', '9876543208', 'Information Technology', 7],
            ['Siddharth Verma', 'sid.verma@studenthub.com', '9876543209', 'Mechanical Engineering', 3],
            ['Tanvi Trivedi', 'tanvi.t@studenthub.com', '9876543210', 'Computer Science', 5],
            ['Aditya Rawal', 'aditya.r@studenthub.com', '9876543211', 'Civil Engineering', 3],
            ['Khushi Pandya', 'khushi.p@studenthub.com', '9876543212', 'Information Technology', 3],
            ['Harshil Soni', 'harshil.s@studenthub.com', '9876543213', 'Computer Engineering', 5],
            ['Ishita Dave', 'ishita.d@studenthub.com', '9876543214', 'Information Technology', 7],
            ['Meet Vora', 'meet.vora@studenthub.com', '9876543215', 'Computer Engineering', 3],
            ['Bhavya Patel', 'bhavya.p@studenthub.com', '9876543216', 'Electrical Engineering', 5],
            ['Nidhi Panchal', 'nidhi.p@studenthub.com', '9876543217', 'Information Technology', 3],
            ['Yash Solanki', 'yash.s@studenthub.com', '9876543218', 'Mechanical Engineering', 5],
            ['Priya Rathod', 'priya.r@studenthub.com', '9876543219', 'Computer Science', 3],
            ['Manan Bhatt', 'manan.b@studenthub.com', '9876543220', 'Information Technology', 5],
            ['Kruti Oza', 'kruti.oza@studenthub.com', '9876543221', 'Computer Engineering', 3]
        ];

        $insertStmt = $pdo->prepare("INSERT IGNORE INTO students (name, email, mobile, department, semester) VALUES (?, ?, ?, ?, ?)");
        foreach ($sampleStudents as $s) {
            $insertStmt->execute($s);
        }
        echo "Seeded " . count($sampleStudents) . " students.\n";
    }

    // Seed events table with at least 15 events if count < 15
    $eventCount = $pdo->query("SELECT COUNT(*) FROM events")->fetchColumn();
    if ($eventCount < 15) {
        $sampleEvents = [
            ['Technical Hackathon 2026', '2026-11-05', 'CSPIT Auditorium', '24-hour coding hackathon for students.', 'Technical'],
            ['Spandan Cultural Fest', '2026-11-10', 'University Ground', 'Annual music, dance, and drama festival.', 'Cultural'],
            ['Web Development Workshop', '2026-11-15', 'Computer Lab 301', 'Hands-on training in PHP, JS, and CSS.', 'Technical'],
            ['Campus Photography Contest', '2026-11-18', 'Main Campus Garden', 'Capture beautiful moments around CHARUSAT.', 'Cultural'],
            ['AI & Machine Learning Seminar', '2026-11-20', 'CSPIT Seminar Hall', 'Expert session on Generative AI and ML.', 'Technical'],
            ['Annual Sports Meet', '2026-11-22', 'University Sports Ground', 'Athletics, football, and cricket matches.', 'Sports'],
            ['CodeMaster Speed Coding', '2026-11-25', 'Computer Lab 204', 'Solve algorithmic puzzles in record time.', 'Technical'],
            ['Inter-College Dance Battle', '2026-11-28', 'University Auditorium', 'Solo and group dance performances.', 'Cultural'],
            ['Robotics Expo 2026', '2026-12-02', 'Innovation Center', 'Showcase of autonomous and line-follower robots.', 'Technical'],
            ['Box Cricket League', '2026-12-05', 'Sports Complex', 'Intra-department cricket tournament.', 'Sports'],
            ['UI/UX Design Bootcamp', '2026-12-08', 'Design Studio Lab', 'Learn Figma and responsive layout design.', 'Technical'],
            ['Sufi & Bollywood Music Night', '2026-12-10', 'Open Air Theatre', 'Live band performances by student bands.', 'Cultural'],
            ['Final Year Project Exhibition', '2026-12-12', 'FTE Exhibition Hall', 'Display of capstone engineering projects.', 'Technical'],
            ['Inter-Department Badminton Cup', '2026-12-15', 'Indoor Sports Complex', 'Men and women singles and doubles.', 'Sports'],
            ['Cyber Security Awareness Talk', '2026-12-18', 'Auditorium B', 'Guidance on web safety and ethical hacking.', 'Technical']
        ];

        $insEvent = $pdo->prepare("INSERT INTO events (event_name, event_date, venue, description, category) VALUES (?, ?, ?, ?, ?)");
        foreach ($sampleEvents as $e) {
            $insEvent->execute($e);
        }
        echo "Seeded " . count($sampleEvents) . " events.\n";
    }

    echo "Database setup completed successfully!\n";

} catch (PDOException $e) {
    echo "Database Error: " . $e->getMessage() . "\n";
}
