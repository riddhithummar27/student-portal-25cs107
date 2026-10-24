-- =========================================================
-- StudentHub Database Dump
-- CHARUSAT FTE ITUE203 Practical 15 Submission
-- Generation Date: 2026-10-25 00:00:29
-- =========================================================

CREATE DATABASE IF NOT EXISTS `studenthub` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `studenthub`;

-- ---------------------------------------------------------
-- Table structure for `users`
-- ---------------------------------------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('student','admin') NOT NULL DEFAULT 'student',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for `users`
INSERT INTO `users` (`user_id`, `name`, `email`, `password`, `role`, `created_at`) VALUES
('1', 'riddhi thummar', '25cs107@charusat.edu.in', '$2y$10$TsZH8Ud1IIdwkuBAEvPoNOfiJfmZ7SZykIgS6be4eTqvHY.zCr59a', 'admin', '2026-10-25 00:00:54'),
('2', 'Admin Officer', 'admin@studenthub.com', '$2y$10$uQ/kZWyl0/2J92CdICTu5.I8V9/Da70mYg37/RbWfEveQJ.3pNYG6', 'admin', '2026-10-25 02:46:12'),
('3', 'Riddhi Thummar', 'student@studenthub.com', '$2y$10$/B6hGPTzXRtKQKQymKZDfeTG8JrbwPsFqHHtEeJBYwDE2CTl20cT6', 'student', '2026-10-25 02:46:12');

-- ---------------------------------------------------------
-- Table structure for `students`
-- ---------------------------------------------------------
DROP TABLE IF EXISTS `students`;
CREATE TABLE `students` (
  `student_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `mobile` varchar(15) DEFAULT NULL,
  `department` varchar(100) DEFAULT NULL,
  `semester` int(11) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  PRIMARY KEY (`student_id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for `students`
INSERT INTO `students` (`student_id`, `name`, `email`, `mobile`, `department`, `semester`, `status`) VALUES
('1', 'Riddhi Thummar', 'riddhi@example.com', '9876543210', 'Computer Engineering', '3', 'active'),
('2', 'Anushka Patel', 'anushka@example.com', '9876543211', 'Computer Engineering', '3', 'active'),
('3', 'Rahul Shah', 'rahul@example.com', '9876543212', 'Information Technology', '4', 'active'),
('4', 'Priya Mehta', 'priya@example.com', '9876543213', 'Computer Engineering', '2', 'active'),
('5', 'Riddhi Thummar', 'riddhi@studenthub.com', '9876543201', 'Information Technology', '3', 'active'),
('6', 'Aarav Patel', 'aarav.patel@studenthub.com', '9876543202', 'Computer Engineering', '3', 'active'),
('7', 'Diya Shah', 'diya.shah@studenthub.com', '9876543203', 'Information Technology', '5', 'active'),
('8', 'Kavya Joshi', 'kavya.joshi@studenthub.com', '9876543204', 'Computer Science', '3', 'active'),
('9', 'Rohan Mehta', 'rohan.mehta@studenthub.com', '9876543205', 'Electrical Engineering', '5', 'active'),
('10', 'Ananya Sharma', 'ananya.s@studenthub.com', '9876543206', 'Information Technology', '3', 'active'),
('11', 'Dev Parekh', 'dev.parekh@studenthub.com', '9876543207', 'Computer Engineering', '5', 'active'),
('12', 'Pooja Desai', 'pooja.desai@studenthub.com', '9876543208', 'Information Technology', '7', 'active'),
('13', 'Siddharth Verma', 'sid.verma@studenthub.com', '9876543209', 'Mechanical Engineering', '3', 'active'),
('14', 'Tanvi Trivedi', 'tanvi.t@studenthub.com', '9876543210', 'Computer Science', '5', 'active'),
('15', 'Aditya Rawal', 'aditya.r@studenthub.com', '9876543211', 'Civil Engineering', '3', 'active'),
('16', 'Khushi Pandya', 'khushi.p@studenthub.com', '9876543212', 'Information Technology', '3', 'active'),
('17', 'Harshil Soni', 'harshil.s@studenthub.com', '9876543213', 'Computer Engineering', '5', 'active'),
('18', 'Ishita Dave', 'ishita.d@studenthub.com', '9876543214', 'Information Technology', '7', 'active'),
('19', 'Meet Vora', 'meet.vora@studenthub.com', '9876543215', 'Computer Engineering', '3', 'active'),
('20', 'Bhavya Patel', 'bhavya.p@studenthub.com', '9876543216', 'Electrical Engineering', '5', 'active'),
('21', 'Nidhi Panchal', 'nidhi.p@studenthub.com', '9876543217', 'Information Technology', '3', 'active'),
('22', 'Yash Solanki', 'yash.s@studenthub.com', '9876543218', 'Mechanical Engineering', '5', 'active'),
('23', 'Priya Rathod', 'priya.r@studenthub.com', '9876543219', 'Computer Science', '3', 'active'),
('24', 'Manan Bhatt', 'manan.b@studenthub.com', '9876543220', 'Information Technology', '5', 'active'),
('25', 'Kruti Oza', 'kruti.oza@studenthub.com', '9876543221', 'Computer Engineering', '3', 'active');

-- ---------------------------------------------------------
-- Table structure for `events`
-- ---------------------------------------------------------
DROP TABLE IF EXISTS `events`;
CREATE TABLE `events` (
  `event_id` int(11) NOT NULL AUTO_INCREMENT,
  `event_name` varchar(150) NOT NULL,
  `event_date` date NOT NULL,
  `venue` varchar(150) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `poster_path` varchar(255) DEFAULT NULL,
  `category` varchar(50) DEFAULT 'General',
  PRIMARY KEY (`event_id`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for `events`
INSERT INTO `events` (`event_id`, `event_name`, `event_date`, `venue`, `description`, `poster_path`, `category`) VALUES
('1', 'Web Development Workshop', '2026-10-10', 'CSPIT Lab 1', 'Workshop on HTML, CSS and JavaScript', NULL, 'General'),
('2', 'Hackathon 2026', '2026-10-16', 'CSPIT Auditorium', 'Student project hackathon', NULL, 'General'),
('3', 'Git & GitHub Workshop', '2026-10-20', 'Seminar Hall', 'Introduction to Git and GitHub', NULL, 'General'),
('4', 'AI Seminar', '2026-10-25', 'CSPIT Auditorium', 'Seminar on Artificial Intelligence', NULL, 'General'),
('5', 'Technical Hackathon 2026', '2026-11-05', 'CSPIT Auditorium', '24-hour coding hackathon for students.', NULL, 'Technical'),
('6', 'Spandan Cultural Fest', '2026-11-10', 'University Ground', 'Annual music, dance, and drama festival.', NULL, 'Cultural'),
('7', 'Web Development Workshop', '2026-11-15', 'Computer Lab 301', 'Hands-on training in PHP, JS, and CSS.', NULL, 'Technical'),
('8', 'Campus Photography Contest', '2026-11-18', 'Main Campus Garden', 'Capture beautiful moments around CHARUSAT.', NULL, 'Cultural'),
('9', 'AI & Machine Learning Seminar', '2026-11-20', 'CSPIT Seminar Hall', 'Expert session on Generative AI and ML.', NULL, 'Technical'),
('10', 'Annual Sports Meet', '2026-11-22', 'University Sports Ground', 'Athletics, football, and cricket matches.', NULL, 'Sports'),
('11', 'CodeMaster Speed Coding', '2026-11-25', 'Computer Lab 204', 'Solve algorithmic puzzles in record time.', NULL, 'Technical'),
('12', 'Inter-College Dance Battle', '2026-11-28', 'University Auditorium', 'Solo and group dance performances.', NULL, 'Cultural'),
('13', 'Robotics Expo 2026', '2026-12-02', 'Innovation Center', 'Showcase of autonomous and line-follower robots.', NULL, 'Technical'),
('14', 'Box Cricket League', '2026-12-05', 'Sports Complex', 'Intra-department cricket tournament.', NULL, 'Sports'),
('15', 'UI/UX Design Bootcamp', '2026-12-08', 'Design Studio Lab', 'Learn Figma and responsive layout design.', NULL, 'Technical'),
('16', 'Sufi & Bollywood Music Night', '2026-12-10', 'Open Air Theatre', 'Live band performances by student bands.', NULL, 'Cultural'),
('17', 'Final Year Project Exhibition', '2026-12-12', 'FTE Exhibition Hall', 'Display of capstone engineering projects.', NULL, 'Technical'),
('18', 'Inter-Department Badminton Cup', '2026-12-15', 'Indoor Sports Complex', 'Men and women singles and doubles.', NULL, 'Sports'),
('19', 'Cyber Security Awareness Talk', '2026-12-18', 'Auditorium B', 'Guidance on web safety and ethical hacking.', NULL, 'Technical');

-- ---------------------------------------------------------
-- Table structure for `registrations`
-- ---------------------------------------------------------
DROP TABLE IF EXISTS `registrations`;
CREATE TABLE `registrations` (
  `registration_id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `registration_date` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`registration_id`),
  UNIQUE KEY `student_id` (`student_id`,`event_id`),
  KEY `event_id` (`event_id`),
  CONSTRAINT `registrations_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `registrations_ibfk_2` FOREIGN KEY (`event_id`) REFERENCES `events` (`event_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for `registrations`
INSERT INTO `registrations` (`registration_id`, `student_id`, `event_id`, `registration_date`) VALUES
('1', '1', '1', '2026-10-03 15:12:12'),
('2', '1', '2', '2026-10-03 15:12:12'),
('3', '2', '1', '2026-10-03 15:12:12'),
('4', '2', '3', '2026-10-03 15:12:12'),
('5', '3', '2', '2026-10-03 15:12:12'),
('6', '4', '4', '2026-10-03 15:12:12');

-- ---------------------------------------------------------
-- Table structure for `audit_logs`
-- ---------------------------------------------------------
DROP TABLE IF EXISTS `audit_logs`;
CREATE TABLE `audit_logs` (
  `log_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `details` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`log_id`),
  KEY `idx_audit_created_at` (`created_at`),
  KEY `idx_audit_user_id` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for `audit_logs`
INSERT INTO `audit_logs` (`log_id`, `user_id`, `action`, `details`, `ip_address`, `created_at`) VALUES
('1', '1', 'user_logout', 'User logged out', '::1', '2026-10-25 03:11:03');

