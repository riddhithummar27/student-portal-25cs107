# StudentHub — Practical 15: Final Integration, Testing, Documentation & Deployment

**Course:** ITUE-203 — Web Development Framework  
**Project:** StudentHub Portal  
**Local stack:** HTML, CSS, JavaScript, PHP, MySQL, Apache/XAMPP

## 1. Objective

Integrate the StudentHub modules into one deployable portal. Verify navigation, responsive UI, authentication, role-based access, database operations, security, AJAX/API behavior, and documentation. Prepare the project for GitHub and local XAMPP deployment.

## 2. Current project structure

Keep your existing files and folders. A typical structure may look like this:

```text
studenthub/
├── index.html                 # Existing frontend entry page, if present
├── login.php
├── register.php
├── logout.php
├── db.php
├── auth.php
├── student_dashboard.php
├── admin_dashboard.php
├── admin_students.php
├── admin_events.php
├── api_students.php
├── audit_logs.php
├── assets/                    # Existing CSS, JS, and images
├── uploads/
│   └── event_posters/
├── studenthub_additions.sql   # SQL file previously provided
├── studenthub_event_poster.sql
├── README.md
├── TEST_REPORT.md
├── SECURITY_ACCESSIBILITY_CHECKLIST.md
└── .gitignore
```

Your existing filenames may differ. Do not rename existing pages just to match this example; update links to match the files that actually exist.

## 3. Local installation with XAMPP

1. Copy the project folder to `C:\xampp\htdocs\studenthub`.
2. Open XAMPP Control Panel and start **Apache** and **MySQL**.
3. Open phpMyAdmin at `http://localhost/phpmyadmin/`.
4. Confirm the `studenthub` database exists and includes the required tables:
   - `users`
   - `students`
   - `events`
   - `registrations`
   - `audit_logs`
5. Confirm `events.poster_path` exists if you use event poster uploads. Run the provided `studenthub_event_poster.sql` only if the column does not already exist.
6. Confirm `db.php` uses your local database name and credentials. For a default XAMPP setup, the MySQL username is often `root` and the password is blank; use your actual local settings.
7. Open `http://localhost/studenthub/` or the correct existing entry page.

## 4. Account roles

New registrations should normally receive the `student` role. To use administrator-only pages during local testing, change the role of your own test account to `admin` in phpMyAdmin. Log out and log in again after changing the role so the session refreshes.

Administrator-only pages must reject non-admin users with HTTP 403 or redirect them to login. Never rely only on hiding an admin menu link in HTML.

## 5. Integration requirements

- Every navigation link points to a real project page.
- Student and admin navigation menus show the correct options.
- Login, registration, logout, and role redirects work end-to-end.
- Protected pages verify the active session and role on the server.
- Database operations use prepared statements.
- Passwords are stored with `password_hash()` and checked with `password_verify()`.
- State-changing forms validate input and use CSRF tokens where applicable.
- User-provided text is escaped before rendering HTML.
- Uploaded event posters are validated by MIME type and file size, and use generated filenames.
- AJAX/API endpoints return valid JSON and appropriate HTTP status codes.
- Empty search results and validation errors are displayed clearly.
- Layouts remain usable on mobile and desktop.
- Forms have visible labels and controls can be used with a keyboard.
- Do not commit database passwords, private keys, real student records, or session secrets to GitHub.

## 6. Run the integration health check

After copying `integration_check.php` from this package into your project folder, log in as an administrator and open:

`http://localhost/studenthub/integration_check.php`

It checks basic PHP/database connectivity, expected tables, and selected project files. It does not replace manual end-to-end testing. Remove this diagnostic page before public deployment, or restrict it to local administrators.

## 7. GitHub submission

From a terminal opened in the project folder:

```bash
git init
git add .
git status
git commit -m "Complete StudentHub Practical 15 integration"
git branch -M main
git remote add origin YOUR_GITHUB_REPOSITORY_URL
git push -u origin main
```

Replace `YOUR_GITHUB_REPOSITORY_URL` with your actual repository URL. If Git is already initialized or a remote already exists, do not run `git init` or `git remote add origin` again; inspect with `git status` and `git remote -v`.

Before pushing:
- Review `.gitignore`.
- Check `git status` for unwanted files.
- Remove real/private test data.
- Do not commit `.env`, credentials, database dumps containing real records, or uploaded private documents.

## 8. Apache Virtual Host (optional intermediate extension)

A sample configuration is provided in `apache-vhost-example.conf`. Paths and hostnames may need to be adjusted to your XAMPP installation. Add the matching hostname to the Windows hosts file only if you choose to use the virtual host. Restart Apache after configuration. The ordinary `http://localhost/studenthub/` setup is sufficient for basic local deployment.

## 9. Submission items

- GitHub repository link
- SQL schema/database dump with test data only
- README installation and usage guide
- Completed test report
- Security and accessibility checklist
- Screenshots of key modules and test results
- Short project demonstration

## 10. Important note

This package provides the Practical 15 integration/documentation support files. It does not replace your existing StudentHub frontend or all prior practical modules. Keep your existing code and integrate these files into it.
