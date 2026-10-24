# StudentHub Practical 15 — Integration Test Report

**Student name:** ____________________  
**Student ID:** ____________________  
**Date:** ____________________  
**GitHub repository:** ____________________  
**Test environment:** Windows / XAMPP / Apache / PHP / MySQL

Record the actual result of each test. Do not mark a test as passed until you have performed it.

| No. | Test | Expected result | Actual result / notes | Status |
|---:|---|---|---|---|
| 1 | Open StudentHub entry page | Page loads without PHP/console errors | | Not tested |
| 2 | Navigate through main menu | Each link opens the correct page | | Not tested |
| 3 | Open registration page | Form and labels display correctly | | Not tested |
| 4 | Register with valid data | Account is created successfully | | Not tested |
| 5 | Register with duplicate email | Duplicate account is rejected safely | | Not tested |
| 6 | Register with invalid input | Server-side validation rejects it | | Not tested |
| 7 | Log in with valid student account | Student dashboard opens | | Not tested |
| 8 | Log in with incorrect password | Login is rejected without revealing password | | Not tested |
| 9 | Log out | Session is cleared and protected page is inaccessible | | Not tested |
| 10 | Open student-only page while logged out | Redirect to login or deny access | | Not tested |
| 11 | Open admin page as student | Access is denied (403 or safe redirect) | | Not tested |
| 12 | Open admin page as admin | Admin page opens | | Not tested |
| 13 | Search/filter students | Matching records are displayed | | Not tested |
| 14 | Add/edit/delete a test student | Database changes correctly and feedback appears | | Not tested |
| 15 | Create/edit/delete an event | Event changes are persisted | | Not tested |
| 16 | Upload valid event poster | Valid image within size limit is saved and displayed | | Not tested |
| 17 | Upload unsupported or oversized file | Upload is rejected safely | | Not tested |
| 18 | Open audit logs | Administrator can view recorded actions | | Not tested |
| 19 | Call JSON API | Valid JSON and suitable HTTP status are returned | | Not tested |
| 20 | Test AJAX action | UI updates without a full page reload where implemented | | Not tested |
| 21 | Test search with no results | Helpful empty state appears | | Not tested |
| 22 | Resize to mobile width | Content remains readable; no major horizontal overflow | | Not tested |
| 23 | Keyboard-only navigation | Focus is visible and controls are reachable | | Not tested |
| 24 | Check browser console | No unexplained JavaScript errors | | Not tested |
| 25 | Check PHP/Apache logs | No unexplained warnings or fatal errors | | Not tested |
| 26 | Run integration health check | Database/tables/files are reported correctly | | Not tested |
| 27 | Clone/copy project into XAMPP folder | Setup instructions are sufficient to run locally | | Not tested |
| 28 | Verify Git status before push | No secrets or private data are staged | | Not tested |

## Defects found

| Defect | Steps to reproduce | Fix applied | Retest result |
|---|---|---|---|
| | | | |

## Final summary

**Passed:** _____ / 28  
**Failed:** _____ / 28  
**Not tested:** _____ / 28  

**Known limitations:**  
____________________________________________________________

**Conclusion:**  
____________________________________________________________
