# StudentHub Practical 15 — Integration Test Report

**Student name:** Riddhi Thummar  
**Student ID:** 25CS107  
**Date:** October 25, 2026  
**GitHub repository:** https://github.com/riddhithummar27/student-portal-25cs107  
**Test environment:** Windows / XAMPP 8.2 / Apache 2.4 / PHP 8.2 / MySQL 8.0

---

## Detailed Test Matrix (28 Tests)

| No. | Test | Expected result | Actual result / notes | Status |
|---:|---|---|---|:---:|
| 1 | Open StudentHub entry page | Page loads without PHP/console errors | Loaded cleanly with hero section and live counters | **PASS** |
| 2 | Navigate through main menu | Each link opens the correct page | All 10+ pages navigate without broken links | **PASS** |
| 3 | Open registration page | Form and labels display correctly | Form rendered with all fields & password meter | **PASS** |
| 4 | Register with valid data | Account is created successfully | Inserted into `users` & `students`, CSV updated | **PASS** |
| 5 | Register with duplicate email | Duplicate account is rejected safely | Error alert shown: "Email already registered" | **PASS** |
| 6 | Register with invalid input | Server-side validation rejects it | RegEx and PHP sanitize rejected invalid values | **PASS** |
| 7 | Log in with valid student account | Student dashboard opens | Redirected to `student_dashboard.php` | **PASS** |
| 8 | Log in with incorrect password | Login is rejected without revealing password | Alert shown: "Invalid email or password" | **PASS** |
| 9 | Log out | Session is cleared and protected page is inaccessible | Redirected to `login.php?logout=1`, cookies cleared | **PASS** |
| 10 | Open student-only page while logged out | Redirect to login or deny access | Redirected to `login.php` immediately | **PASS** |
| 11 | Open admin page as student | Access is denied (403 or safe redirect) | HTTP 403 Forbidden: Administrator access required | **PASS** |
| 12 | Open admin page as admin | Admin page opens | Admin dashboard loaded with SQL statistics | **PASS** |
| 13 | Search/filter students | Matching records are displayed | Live search and department filter working | **PASS** |
| 14 | Add/edit/delete a test student | Database changes correctly and feedback appears | INSERT, UPDATE, DELETE executed with confirmation | **PASS** |
| 15 | Create/edit/delete an event | Event changes are persisted | Event CRUD saved in MySQL `events` table | **PASS** |
| 16 | Upload valid event poster | Valid image within size limit is saved and displayed | JPEG/PNG/WEBP saved to `uploads/event_posters/` | **PASS** |
| 17 | Upload unsupported or oversized file | Upload is rejected safely | Throws validation error: "Max 2MB allowed" | **PASS** |
| 18 | Open audit logs | Administrator can view recorded actions | Audit table shows logins, creates, deletes, IP | **PASS** |
| 19 | Call JSON API | Valid JSON and suitable HTTP status are returned | `api_students.php` returned HTTP 200 and JSON array | **PASS** |
| 20 | Test AJAX action | UI updates without a full page reload where implemented | `ajax_crud.html` updated table & toast seamlessly | **PASS** |
| 21 | Test search with no results | Helpful empty state appears | Shows "No records found" friendly message | **PASS** |
| 22 | Resize to mobile width | Content remains readable; no major horizontal overflow | CSS Grid and mobile menu adapt responsively | **PASS** |
| 23 | Keyboard-only navigation | Focus is visible and controls are reachable | TAB navigation works through all form inputs & links | **PASS** |
| 24 | Check browser console | No unexplained JavaScript errors | Console is clean (0 errors) | **PASS** |
| 25 | Check PHP/Apache logs | No unexplained warnings or fatal errors | Error log is clean | **PASS** |
| 26 | Run integration health check | Database/tables/files are reported correctly | `integration_check.php` reports all checks PASS | **PASS** |
| 27 | Clone/copy project into XAMPP folder | Setup instructions are sufficient to run locally | `setup_database.php` auto-configures database | **PASS** |
| 28 | Verify Git status before push | No secrets or private data are staged | Only clean source code and documentation tracked | **PASS** |

---

## Final Summary
- **Passed:** 28 / 28
- **Failed:** 0 / 28
- **Not tested:** 0 / 28

**Conclusion:**  
All frontend, backend, database, and security features implemented across Practicals 1 to 14 operate correctly and meet the semester requirements for Practical 15.
