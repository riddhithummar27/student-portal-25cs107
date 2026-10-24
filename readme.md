# StudentHub — University Student Portal
**Course:** Web Development Frameworks (`ITUE203`)  
**Institution:** Faculty of Technology and Engineering (FTE), Charotar University of Science and Technology (CHARUSAT)  
**Semester:** 3rd Semester, 2026-27 (Odd)  
**Project:** Comprehensive Integration of Practicals 1 to 15  

---

## 📌 Project Overview
**StudentHub** is a responsive, accessible, database-backed web portal developed for university academic and campus management. It integrates client-side web technologies (HTML5 semantic tags, CSS3 Grid/Flexbox, JavaScript DOM and Fetch API) with server-side processing (PHP 8.2 sessions, MySQL relational database, password hashing, file uploads, and JSON REST API).

---

## 🚀 Practical Concepts Implemented (PR 1 to PR 15)

| Practical No. | Practical Title & Core Concepts | Implementation in StudentHub |
| :--- | :--- | :--- |
| **PR 1** | Requirement Analysis, Sitemap, Wireframe & Git | Folder architecture, `README.md`, Sitemap, and Git version control. |
| **PR 2** | Semantic HTML5 & Accessibility | `header`, `nav`, `main`, `section`, `article`, `footer`, skip-link, alt text, ARIA. |
| **PR 3** | Responsive UI (CSS Grid & Flexbox) | Mobile-first CSS layout, media queries, flexbox navigation, and grid cards in `style.css`. |
| **PR 4** | JavaScript DOM & Interactivity | Accordion FAQ, modal dialogs, light/dark mode with `localStorage`, hamburger menu in `script.js`. |
| **PR 5** | Form Validation with RegEx | Real-time regex validation, password strength meter, and error handling in `register.php`. |
| **PR 6** | Fetch API & External JSON Data | Dynamic rendering, search, category filter, sort, and pagination from `events.json`. |
| **PR 7** | PHP Server-Side Form Processing & File Storage | Sanitization, input validation, and appending records to `feedback.csv` and `feedback.json`. |
| **PR 8** | MySQL Schema, ER Model & DB Connectivity | Normalized relational tables (`users`, `students`, `events`, `audit_logs`) and `db.php`. |
| **PR 9** | Secure User Registration | Server-side validation, duplicate email checking, and `password_hash()` in `register.php`. |
| **PR 10** | Secure Login/Logout & RBAC | Session security, `password_verify()`, 15-min timeout guard, and role-based redirect in `login.php`. |
| **PR 11** | Student Management CRUD & Search | Full CRUD operations, live search, department filtering, and CSV export in `admin_students.php`. |
| **PR 12** | Event Management CRUD & Poster Upload | Admin event CRUD with file type/size validation and poster image handling in `admin_events.php`. |
| **PR 13** | PHP JSON API & Asynchronous Fetch CRUD | RESTful endpoints in `api_students.php` consumed via async `fetch()` in `ajax_crud.html`. |
| **PR 14** | Admin Dashboard & Audit Logs | Real-time SQL aggregation stats, dynamic RBAC menus, and security audit log tracking in `admin_dashboard.php` & `audit_logs.php`. |
| **PR 15** | Final Integration, Testing & Deployment | End-to-end integration, health check diagnostics, and local XAMPP deployment. |

---

## 🛠️ Technology Stack
- **Frontend:** Semantic HTML5, CSS3 (CSS Grid, Flexbox, Custom Properties), JavaScript (ES6+, Fetch API, Canvas CAPTCHA)
- **Backend:** PHP 8.2 (Prepared statements, Session management, Filter sanitizers, File system I/O)
- **Database:** MySQL 8.0+ / MariaDB via XAMPP
- **Data Formats:** JSON, CSV, Multipart Form-Data
- **Version Control:** Git & GitHub

---

## 📂 Project Structure & Sitemap
```
studenthub/
├── index.php                 # Home landing page with live counter & features
├── about.html                # About FTE CHARUSAT & portal capabilities
├── faq.html                  # Collapsible accordion FAQ
├── feedback.php              # PHP form processor writing to CSV and JSON
├── contact.html              # University contact details and message form
├── login.php                 # Secure login with sessions, RBAC, and remember-me
├── register.php              # Secure student registration with password hashing
├── logout.php                # Session clearing and cookie invalidation
├── student_dashboard.php     # Student portal with upcoming events from MySQL
├── admin_dashboard.php       # Admin dashboard with SQL aggregation metrics
├── admin_students.php        # Student management CRUD with search, filter & CSV export
├── admin_events.php          # Event management CRUD with poster image upload
├── ajax_crud.html            # Asynchronous AJAX CRUD single-page application
├── api_students.php          # PHP JSON REST API endpoint (GET, POST, PUT, DELETE)
├── audit_logs.php            # Security audit trail viewer
├── integration_check.php     # Diagnostic health and integration checklist
├── setup_database.php        # Automated database schema creator and seed script
├── db.php                    # Centralized MySQL connection (PDO & MySQLi)
├── style.css                 # Responsive stylesheet with light/dark theme variables
├── script.js                 # Shared frontend interactivity and theme switcher
├── dynamic.html              # Dynamic events catalogue with search and pagination
├── events.json               # External JSON events dataset (15+ records)
├── students.json             # External JSON students dataset (15+ records)
├── faqs.json                 # External JSON FAQ dataset (15+ records)
└── uploads/                  # Upload directory for event posters
```

---

## ⚙️ How to Setup & Run Locally in XAMPP

1. **Start XAMPP:**
   - Launch the **XAMPP Control Panel**.
   - Start both **Apache** and **MySQL** modules.

2. **Place Files:**
   - Ensure the project folder is placed in:  
     `C:\xampp\htdocs\studenthub` or `D:\xampp\htdocs\studenthub`

3. **Initialize Database:**
   - Open your browser or terminal and run the setup script:
     ```
     php setup_database.php
     ```
     *Alternatively, visit `http://localhost/studenthub/setup_database.php` in your browser.*
   - This automatically creates the `studenthub` database, all 5 required tables, seeds 20+ student records, 15+ event records, and creates default accounts.

4. **Access the Application:**
   - Open your web browser and navigate to:  
     **`http://localhost/studenthub/`**

---

## 🔑 Default Test Credentials

| Role | Email Address | Password | Access Area |
| :--- | :--- | :--- | :--- |
| **Administrator** | `admin@studenthub.com` | `admin123` | Full Admin Dashboard, CRUD, Audit Logs |
| **Student** | `student@studenthub.com` | `student123` | Student Dashboard, Events, Academics |

---

## 🧪 Verification & Testing Highlights
- **Integration Check:** Visit `http://localhost/studenthub/integration_check.php` (logged in as Admin) to verify all tables and files.
- **SQL Injection Prevention:** All SQL queries use prepared statements with parameterized inputs.
- **XSS Prevention:** All user outputs pass through `htmlspecialchars(..., ENT_QUOTES, 'UTF-8')`.
- **Session Timeout:** Automatic logout after 15 minutes of inactivity (`auth.php`).
- **File Upload Security:** File type validation (JPEG/PNG/WEBP) and 2MB file size limits in `admin_events.php`.
