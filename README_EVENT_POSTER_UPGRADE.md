StudentHub — Event Poster Upgrade

Included:
- admin_events.php — upgraded event management with create/edit/delete, search, poster upload, file validation, CSRF protection, prepared statements, and audit logging.
- studenthub_event_poster.sql — adds the poster_path column to events.

Install:
1. Back up C:\\xampp\\htdocs\\studenthub.
2. Replace the earlier admin_events.php with this new admin_events.php.
3. In phpMyAdmin, select studenthub, open SQL, and run studenthub_event_poster.sql.
   IMPORTANT: Run the ALTER TABLE statement only once. If poster_path already exists, skip it.
4. The page attempts to create uploads/event_posters automatically. If uploads fail, create those folders inside studenthub and allow Apache to write to them.

Assumptions:
- db.php provides a PDO variable named $pdo.
- Login session contains user_id and role; admin role is exactly 'admin'.
- events has event_id, event_name, event_date, venue, description columns.
- audit_logs exists from studenthub_additions.sql.
