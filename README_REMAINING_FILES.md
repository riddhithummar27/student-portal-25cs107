# StudentHub — Additional Files Only

This ZIP intentionally excludes files already created in earlier steps:
`db.php`, `test_connection.php`, `register.php`, `login.php`, `auth.php`,
`student_dashboard.php`, `admin_dashboard.php`, and `logout.php`.
It also does not include or replace your existing HTML/CSS/JS frontend.

## Copying
1. Back up your project first.
2. Copy the new PHP files into `C:\xampp\htdocs\studenthub\`.
3. Keep the existing `db.php` and `auth.php`; these files use them.
4. Run `studenthub_additions.sql` in phpMyAdmin > `studenthub` > SQL.
5. Check the table columns before running any ALTER statements. The SQL uses
   `CREATE TABLE IF NOT EXISTS` for the audit log and adds a poster column only
   if you choose to run that ALTER statement manually.

## Files
- `admin_students.php`: admin-only student CRUD/search/filter using the existing `students` table.
- `admin_events.php`: admin-only event CRUD using the existing `events` table; poster upload is not included yet.
- `api_students.php`: JSON API for listing/searching students (GET) and creating students (POST).
- `audit_logs.php`: admin-only audit log viewer.
- `studenthub_additions.sql`: creates the audit log table.

## Important
Your existing `students` table was observed with these columns:
`student_id, name, email, mobile, department, semester`.
Your existing `events` table was observed with these columns:
`event_id, event_name, event_date, venue, description`.
Your `registrations` table links students and events. The files below use those names.
The dashboards are still simple test pages; these files are separate admin tools and can be linked from your real UI later.
