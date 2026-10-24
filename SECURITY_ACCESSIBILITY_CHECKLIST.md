# StudentHub — Security & Accessibility Checklist

**Student Name:** Riddhi Thummar  
**Student ID:** 25CS107  
**Verification Date:** October 25, 2026  
**Status:** All Verification Items Passed

---

## Security

- [x] Passwords are stored using `password_hash()`; no plain-text passwords are stored.
- [x] Login uses `password_verify()`.
- [x] Session ID is regenerated after successful login (`session_regenerate_id(true)`).
- [x] Logout clears session data and expires the session cookie.
- [x] Protected pages validate login state on the server.
- [x] Admin pages validate the user's role on the server (`$_SESSION['role'] === 'admin'`).
- [x] Session inactivity timeout is enforced (15-minute guard).
- [x] Database queries use prepared statements with parameterized inputs.
- [x] Server-side validation is applied to every submitted form.
- [x] Output from users/database is escaped with `htmlspecialchars()` before HTML rendering.
- [x] State-changing forms use CSRF tokens.
- [x] File uploads validate MIME type and size on the server.
- [x] Upload filenames are generated randomly by the application (`bin2hex(random_bytes(16))`).
- [x] Uploaded files are restricted to non-executable image types (JPEG, PNG, WEBP).
- [x] Errors do not reveal database credentials, SQL, stack traces, or private paths.
- [x] No credentials, tokens, or real private student data are committed to GitHub.
- [x] Test/admin accounts use non-sensitive test data.
- [x] Diagnostic pages are restricted to admin users before deployment.

---

## Accessibility and Responsive Design

- [x] Each form control has a visible, associated `<label>`.
- [x] Images have meaningful alternative text, or empty alt text if decorative.
- [x] Interactive controls are reachable by keyboard (`<a href="#main" class="skip-link">`).
- [x] Keyboard focus is clearly visible with outline styling.
- [x] Text and background have sufficient contrast adhering to WCAG AA guidelines.
- [x] Headings follow a logical hierarchy (`<h1>` through `<h3>`).
- [x] Links and buttons have clear, descriptive names.
- [x] Error messages explain what needs correction in real time.
- [x] Status messages are not communicated by color alone (text alerts with icons).
- [x] Pages remain usable at narrow mobile widths (down to 320px).
- [x] Content does not require horizontal scrolling at common viewport widths.
- [x] Tables have clear headers (`<th>`) and responsive overflow containers.
- [x] JavaScript-dependent actions fail gracefully with fallback notices.

---

## Evidence Captured

- [x] Student dashboard screenshot (`student_dashboard.php`)
- [x] Admin dashboard screenshot (`admin_dashboard.php`)
- [x] Student CRUD screenshot (`admin_students.php`)
- [x] Event management screenshot (`admin_events.php`)
- [x] Audit log screenshot (`audit_logs.php`)
- [x] Login/registration validation screenshot (`login.php`, `register.php`)
- [x] Mobile responsive screenshot (`index.php` in mobile viewport)
- [x] Integration health check screenshot (`integration_check.php`)
- [x] GitHub repository screenshot
