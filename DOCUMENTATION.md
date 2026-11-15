# CoviCare – Smart COVID-19 Information & Awareness Portal
BE Computer Engineering Mini Project – Documentation & Viva Guide

---
## 1. XAMPP Setup (Windows)
1. Install XAMPP. Open **XAMPP Control Panel** → **Start Apache** and **Start MySQL**.
2. Copy the whole `CoviCare` folder to `C:\xampp\htdocs\` (final path: `C:\xampp\htdocs\CoviCare\index.php`).
3. Open `http://localhost/phpmyadmin` → **Import** tab → choose `database/covicare.sql` → **Go**. (This creates `covicare_db`, the 4 tables and sample statistics.)
4. Open `http://localhost/CoviCare/create_admin.php` **once**. It creates the admin account, then **delete `create_admin.php`**.
   - Admin login: `admin@covicare.com` / `Admin@123` at `http://localhost/CoviCare/admin/login.php`
5. Open `http://localhost/CoviCare/` – register a user and try everything.
6. Open `http://localhost/CoviCare/testing/regex_testing.php` for the testing page.

If the DB password for root is not empty, or the folder name is not `CoviCare`, edit `config/database.php` (`BASE_URL` and the PDO line).

Troubleshooting: *"Database connection failed"* → MySQL not started or SQL not imported. *Styles missing / links broken* → folder name differs from `CoviCare`. *Port 80 busy* → change the Apache port and browse to `http://localhost:8080/CoviCare/` (BASE_URL needs no change).

---
## 2. Architecture
**3-tier, plain PHP:** Browser (HTML/CSS/JS) → PHP pages on Apache (logic, validation, sessions) → MySQL (`covicare_db`) through PDO.

```
Browser ── HTML form + validation.js (client check)
   │ POST
PHP page ── csrf_ok() → validate_field() (server check) → prepared statement → MySQL
   │ HTML (escaped with e())
Browser
```
Shared code (used by all members):
| File | Purpose |
|---|---|
| `config/database.php` | PDO connection, session start, helpers: `e()`, CSRF, `require_user()`, `require_admin()`, `attempt_login()` |
| `testing/regex_patterns.php` | All regex rules in one place; PHP validates with them and `includes/footer.php` sends the **same patterns to JavaScript** so both sides always match |
| `includes/header.php`, `footer.php` | Navbar (changes by role), flash messages, scripts |
| `js/validation.js` | Reads `data-rule` on inputs and checks them with the shared patterns |

### Folder structure
```
CoviCare/  index.php login.php register.php logout.php create_admin.php
  user/   dashboard.php test_registration.php comments.php
  admin/  login.php dashboard.php users.php registrations.php comments.php
  testing/ regex_patterns.php regex_testing.php
  config/ database.php   includes/ header.php footer.php
  css/ style.css   js/ validation.js   database/ covicare.sql
```
(Simplification vs. suggested structure: added `includes/`, `admin/login.php`, `create_admin.php`.)

### Team division
| Member | Area |
|---|---|
| 1 | `index.php`, `register.php`, `login.php`, `logout.php`, `includes/*`, `css/style.css`, sessions |
| 2 | `user/*`, `covid_stats` display, test registration, comments |
| 3 | `admin/*`, `testing/*`, `js/validation.js`, SQL injection/XSS testing |

---
## 3. Database Design
| Table | Purpose |
|---|---|
| `users` | Everyone who can log in. `role` = `user` or `admin`. Password stored as bcrypt hash. Email is UNIQUE. |
| `test_registrations` | One row per test booking. `status`: Pending → Scheduled → Completed / Cancelled (changed by admin). |
| `comments` | Feedback text written by users. |
| `covid_stats` | Statistics rows shown on home/dashboard (**illustrative sample data**, labelled in the UI). |

**Relationships:** `users (1) ──< (many) test_registrations` and `users (1) ──< (many) comments`, both through the foreign key `user_id` with `ON DELETE CASCADE` (deleting a user removes their bookings and comments). `covid_stats` is standalone.

Full SQL: `database/covicare.sql`.

---
## 4. Security Implemented
| Threat | Protection | Where |
|---|---|---|
| Plain-text passwords | `password_hash()` / `password_verify()` | register.php, `attempt_login()` |
| SQL injection | PDO prepared statements (`?` placeholders, `EMULATE_PREPARES=false`) | every query with user input |
| XSS | `htmlspecialchars` via `e()` on all output | every page |
| CSRF | Random token in each form, checked by `csrf_ok()` | all POST forms |
| Unauthorised access | `require_user()`, `require_admin()` at top of protected pages | user/*, admin/* |
| Session fixation | `session_regenerate_id(true)` at login | `attempt_login()` |
| Privilege escalation | Role forced to `user` on registration; admin login only accepts `role='admin'` | register.php, admin/login.php |
| Bad input | Regex validation on client **and** server | all forms |
| Status tampering | Status checked against a whitelist | admin/registrations.php |

---
## 5. Validation Rules (same on client and server)
| Field | Rule |
|---|---|
| name | 2–50 chars, starts with a letter; letters, space, `.` `'` `-` |
| email | valid format, 6–100 chars |
| phone | 10 digits, first digit 6–9 |
| password | 8–20 chars, at least 1 upper, 1 lower, 1 digit |
| age | whole number 1–120, no leading zero |
| date | `YYYY-MM-DD` (server also checks real date, not past, within 1 year) |
| comment | 5–500 characters |

**Regex concepts to explain:** `^ $` anchors, `[...]` character class, `{m,n}` quantifier, `(?=.*[A-Z])` lookahead (checks a condition without consuming characters – used for password strength). PHP uses the `D` modifier so `$` does not accept a trailing newline, matching JavaScript behaviour.

---
## 6. Software Testing

### 6.1 Methodology
- **Black-box functional testing** (manual): enter input, compare with expected output.
- **Regex unit testing** (automated): `testing/regex_testing.php` runs 59 cases and reports total/passed/failed/percentage.
- **Boundary value analysis:** min, max, min−1, max+1 for name, phone, password, age, comment.
- **Security testing:** SQL injection and XSS strings.

### 6.2 Automated regex testing – how to read the page
59 cases: valid, invalid, boundary, empty and security inputs across 7 fields + output escaping. A case "passes" when the validator behaves as expected (accepts valid, rejects invalid). Expected outcome with the supplied code: **59 total, 59 passed, 0 failed, 100 %.** (We verified the 57 regex cases with an equivalent engine; run the page to record your own screenshot for the report.)
The same page has an **interactive tester**: choose a field, type any text, see PASS/FAIL.

### 6.3 Manual functional test cases (fill "Actual" during demo)
| ID | Module | Steps / Input | Expected | Actual | Status |
|---|---|---|---|---|---|
| T01 | Register | Valid name, email, phone, strong password | Success message, redirected to login | | |
| T02 | Register | All fields empty | Error under every field, nothing saved | | |
| T03 | Register | Email `student@` | "Enter a valid email address" | | |
| T04 | Register | Phone `12345` | Phone error | | |
| T05 | Register | Password `password1` | Password rule error | | |
| T06 | Register | Confirm password different | "Passwords do not match" | | |
| T07 | Register | Email already used | "This email is already registered" | | |
| T08 | Login | Correct credentials | Dashboard opens | | |
| T09 | Login | Wrong password | "Incorrect email or password" | | |
| T10 | Login | Empty fields | Required messages | | |
| T11 | Login | Email `' OR '1'='1` and any password | Rejected (invalid email) – no login | | |
| T12 | Logout | Click Logout then press browser Back and open dashboard | Redirected to login | | |
| T13 | Test booking | Valid details, future date | Success; row appears in "My Test Registrations" with Pending | | |
| T14 | Test booking | Age `0` / `121` | Age error | | |
| T15 | Test booking | Past date | "Test date cannot be in the past" | | |
| T16 | Comment | Valid 5+ char comment | Success; shown in "My Previous Comments" | | |
| T17 | Comment | `<script>alert(1)</script>` | Saved but shown as plain text, no popup | | |
| T18 | Comment | 4 characters | Length error | | |
| T19 | Admin access | Not logged in, open `/admin/dashboard.php` | Redirect to admin login | | |
| T20 | Admin access | Logged in as normal user, open `/admin/users.php` | Redirect to admin login | | |
| T21 | Admin login | Normal user credentials on admin login | "Incorrect admin credentials" | | |
| T22 | Admin | Change status in Tests page | "Status updated"; user sees new status | | |
| T23 | Admin | Delete a comment | Comment removed | | |
| T24 | CSRF | Remove the hidden `csrf_token` field using browser dev-tools, submit | "Invalid request" | | |
| T25 | Client vs server | Disable JavaScript, submit invalid data | Server still shows the same errors | | |

---
## 7. Module Explanations (for each member)

### Member 1 – Frontend, Registration, Login, Sessions
**What it does:** home page (info, symptoms, prevention, FAQ, stats cards), responsive navbar, register, login, logout.
**Files:** `index.php`, `register.php`, `login.php`, `logout.php`, `includes/header.php`, `includes/footer.php`, `css/style.css`, `config/database.php` (session + `attempt_login`).
**How it works:**
1. Register: form → JS checks patterns → on submit PHP checks CSRF → validates every field again → checks email not already used → `password_hash` → prepared INSERT → flash message → redirect to login.
2. Login: validate → `attempt_login()` fetches the row by email and role → `password_verify` → stores `user_id`, `name`, `role` in `$_SESSION` → `session_regenerate_id`.
3. Every protected page calls `require_user()` which checks `$_SESSION`.
4. Logout: clears `$_SESSION`, `session_destroy()`.
5. Responsive design: CSS grid + media query (menu collapses under 700 px).
**Tables:** `users` (write/read), `covid_stats` (read on home).
**Connects to others:** the session set at login is what Members 2 and 3 use for access control; header/footer/CSS used by all pages; `validation.js` and `regex_patterns.php` are Member 3's.
**Viva questions:**
- *What is a session?* Server-side storage that remembers a logged-in user across pages; the browser only holds a session ID cookie.
- *Why hash passwords?* If the DB leaks, attackers don't get real passwords. `password_hash` uses bcrypt with a random salt.
- *Hashing vs encryption?* Hashing is one-way; encryption can be reversed.
- *Why the same error for wrong email and wrong password?* Not revealing which one is wrong.
- *What is `session_regenerate_id`?* New session ID after login to prevent session fixation.
- *How is the page responsive?* Viewport meta tag, flexible grid, media query.
- *GET vs POST?* POST for forms with sensitive data; data not in URL.

### Member 2 – COVID info, Test Registration, Comments
**What it does:** statistics display, test booking form, user's own booking list, feedback form and list.
**Files:** `user/dashboard.php`, `user/test_registration.php`, `user/comments.php`.
**How it works:**
1. Statistics: `SELECT * FROM covid_stats`, looped into a table; totals computed by summing rows. Labelled as illustrative sample data.
2. Test booking: `require_user()` → validate (regex + `checkdate`, date not in the past) → INSERT with the `user_id` from the **session** (not from the form, so a user cannot book for someone else) → redirect (Post-Redirect-Get avoids duplicate insert on refresh).
3. "My records": `WHERE user_id = ?` using the session id, so users only see their own data.
4. Comments: validate 5–500 chars → INSERT → shown with `e()` so scripts cannot run.
**Tables:** `test_registrations`, `comments`, `covid_stats`, and `users` (via the foreign key).
**Connects:** depends on login session (Member 1); validation functions from Member 3; admin (Member 3) reads and updates the rows it creates.
**Viva questions:**
- *What is a prepared statement?* SQL with placeholders; data is sent separately so input can never change the query.
- *Why take `user_id` from the session?* Form values can be edited by the user; session cannot.
- *What is a foreign key?* A column linking to another table's primary key, keeping data consistent.
- *What is Post-Redirect-Get?* Redirect after POST so refreshing does not resubmit the form.
- *Is the statistics data real?* No – clearly labelled sample data stored in MySQL; admin could replace it with real figures.
- *What does `e()` do?* Converts `< > " '` to HTML entities so it displays as text.

### Member 3 – Admin, Regex Validation, Testing
**What it does:** separate admin login, user list, test registration management (status update), comment management (delete), regex rules, client validation, testing page.
**Files:** `admin/*.php`, `create_admin.php`, `testing/regex_patterns.php`, `testing/regex_testing.php`, `js/validation.js`.
**How it works:**
1. Admin login uses the same `attempt_login()` but requires `role = 'admin'`; admin pages call `require_admin()`.
2. Registrations page: JOIN of `test_registrations` and `users`; each row has a dropdown form; server checks CSRF and that the status is in the allowed list, then UPDATE.
3. Validation: `covicare_rules()` returns pattern + message per field. PHP `validate_field()` uses `preg_match`. `footer.php` prints the same array as JSON for `validation.js`, which uses `new RegExp`.
4. Testing page: loops over an array of test cases, calls the validator, compares with expected result, counts passed/failed, computes `passed / total × 100`.
**Tables:** all four (read), `test_registrations` (update), `comments` (delete).
**Connects:** reads data created by Member 2; uses session/role from Member 1; its validators are used by Members 1 and 2.
**Viva questions:**
- *Why validate on both client and server?* Client = fast feedback; server = real security, since JS can be disabled or bypassed.
- *How do you keep both consistent?* One pattern list used by both.
- *Explain your password regex.* Three lookaheads require lowercase, uppercase, digit; `.{8,20}` enforces length.
- *What is boundary testing?* Testing at the edges of allowed ranges (e.g. 7, 8, 20, 21 characters).
- *How does the app stop SQL injection?* Prepared statements plus regex rejecting quotes/semicolons in names and emails.
- *How does it stop XSS?* Output escaping with `htmlspecialchars`.
- *What is CSRF?* A malicious site making your browser submit a form to our site; the secret token blocks it.
- *How is admin protected?* Role check on every admin page, separate login, role cannot be chosen at registration.
- *Why delete `create_admin.php`?* So nobody else can create admin accounts.

---
## 8. Project Report Content

**Abstract:** CoviCare is a web portal built with HTML, CSS, JavaScript, PHP and MySQL that provides COVID-19 awareness information, statistics, online test registration and feedback for users, and a management dashboard for administrators. It emphasises secure development (hashed passwords, prepared statements, CSRF and XSS protection) and software testing through regex-based validation and an automated test page.

**Problem statement:** Reliable COVID-19 information, test booking and feedback are often scattered across multiple sources. A simple, secure and validated single portal is needed, along with a demonstration of software testing practice.

**Objectives:** (1) provide awareness content and statistics; (2) secure registration/login with sessions; (3) online test registration with status tracking; (4) feedback collection; (5) admin management; (6) consistent client/server regex validation; (7) test and report validation results.

**Existing system:** Static information websites, phone/manual booking, no feedback loop, weak or no input validation, no central record keeping.

**Proposed system:** Dynamic database-backed portal with role-based access, validated forms, trackable bookings, admin control and a built-in testing page.

**Advantages:** simple to run (XAMPP only); secure basics; easy to explain; consistent validation; testable; responsive.
**Limitations:** statistics are sample data (admin edit screen not included); no email/SMS notification; no password reset; no pagination; no real laboratory integration; no rate limiting on login.
**Conclusion:** The project delivers a working, secure mini-portal and demonstrates database connectivity, session handling, validation and testing in one coherent system.
**Future scope:** live statistics API; admin screen to edit statistics; email/SMS reminders; forgot-password; lab report upload; charts; pagination/search; login attempt limiting; multilingual support.

---
## 9. Extra General Viva Questions
- *Why PDO?* Supports prepared statements, clean error handling (exceptions), works with many databases.
- *What does `htmlspecialchars` convert?* `&`, `<`, `>`, `"`, `'`.
- *Difference between `include` and `require_once`?* `require_once` stops with an error if the file is missing and loads it only once.
- *What is `ENUM` in MySQL?* A column that accepts only a fixed list of values (used for role and status).
- *What happens if a user is deleted?* `ON DELETE CASCADE` removes their bookings and comments.
- *Is the project fully secure?* No system is; we cover the main OWASP basics. Adding HTTPS, login throttling and password reset would be next.
- *Why is `.` in the comment regex written as `[\s\S]`?* So that new lines in a multi-line comment are also counted.
- *Known regex limitation?* Email regex is practical, not perfect RFC; the date regex accepts 30 Feb, so PHP also runs `checkdate()`.
