# Copilot Instructions for Noosfera Portfolio

## Project Overview
**Noosfera** is a Ukrainian art portfolio site showcasing mythology-themed artwork. It's a PHP+SQLite web application with public gallery viewing and admin dashboard for managing artwork.

## Architecture

### Core Components
- **Frontend**: HTML templates + Bootstrap 5, AOS animations, custom CSS
- **Backend**: PHP 8.1 with SQLite PDO (auto-created on first run)
- **Database**: Three main tables - `users` (registered members), `works` (gallery items), `admin` (admin credentials)
- **Deployment**: Docker with Apache 2.4 + PHP 8.1 (runs on port 8080)

### Data Flow
1. **Public Pages**: `index.php` → `includes/header.php` + artwork includes → rendered HTML
2. **User Auth**: `auth.php` (login/register) → PDO inserts into `users` table → `$_SESSION['user_id']` set
3. **Admin Workflow**: `admin/login.php` → checks `admin` table → `$_SESSION['admin_logged']` → redirects to `dashboard.php`
4. **Gallery Management**: Dashboard lists works → add/edit/delete routes upload images to `assets/img/works/`

## File Structure Reference
- **Public pages**: Root directory (`index.php`, `auth.php`, `gallery.php`, `contact.php`, `dashboard.php`)
- **Admin section**: `admin/` (isolated login at `admin/login.php`, add/edit/delete operations in `admin/`)
- **Includes**: `includes/` (reusable components - `db.php`, `header.php`, `footer.php`, `config.php`, `helpers.php`)
- **Assets**: `assets/css/style.css`, `assets/js/script.js`, `assets/img/works/` (user uploads)

## Key Development Patterns

### Configuration & Constants
- Located in `includes/config.php` - centralized app configuration
- Use constants for DB paths, upload settings, error messages: `DB_FILE`, `UPLOADS_DIR`, `MAX_FILE_SIZE`, `ERROR_MESSAGES[]`
- Always reference constants instead of hardcoded strings

### Database (SQLite via PDO)
- Located in `includes/db.php` - auto-initializes on first request
- **Always use parameterized queries** with `:param` syntax or `?` placeholders
- Example pattern:
  ```php
  $stmt = $db->prepare('SELECT * FROM works WHERE id = :id');
  $stmt->execute([':id' => $id]);
  $row = $stmt->fetch(PDO::FETCH_ASSOC);
  ```
- Never concatenate user input into SQL strings
- Wrap database operations in try-catch blocks with `log_error()`

### Input Validation & Sanitization
- Use `sanitize_input()` helper for form data: `sanitize_input($_POST['field'] ?? '')`
- Use `validate_email()` for email validation
- Use `validate_password()` for password strength (min 6 chars)
- Use `validate_file_upload()` and `save_uploaded_file()` for file uploads
- File uploads checked for MIME type, size, and extension before saving
- All output to HTML must use `htmlspecialchars()` to prevent XSS

### Session & Auth
- `header.php` always calls `session_start()` first and requires config/helpers
- User session keys: `$_SESSION['user_id']`, `$_SESSION['user_name']`, `$_SESSION['admin_logged']` (boolean)
- Admin pages use `require_admin()` helper to check auth and redirect
- User pages use `require_user()` helper
- Check admin: `if (is_admin()) { ... }`
- Passwords hashed with `password_hash($pwd, PASSWORD_DEFAULT)` and verified via `password_verify()`

### Form Handling
- POST requests check: `$_SERVER['REQUEST_METHOD'] === 'POST'`
- Always validate required fields and email format before DB operations
- Catch `PDOException` with code 23000 for duplicate email
- Display errors with Bootstrap alerts: `<div class="alert alert-danger" role="alert">`
- File uploads stored in `assets/img/works/` with timestamp prefix: `time() . '_' . filename`
- Delete old image files when updating works

### Helper Functions
- `sanitize_input()` - trim, htmlspecialchars, stripslashes
- `validate_email()` - email format validation
- `validate_password()` - password strength check
- `validate_file_upload()` - check MIME, size, extension
- `save_uploaded_file()` - upload with validation
- `is_admin()` - check if current user is admin
- `require_admin()` - require admin, redirect if not
- `require_user()` - require logged-in user
- `log_error()` - debug logging (only if `DEBUG_MODE` is true)

### HTML Structure
- Use Bootstrap 5 classes for layouts and components
- Include AOS data attribute for animations: `<div ... data-aos="fade-up">`
- Use Bootstrap Icons (`bi bi-*` classes) for UI icons
- All `<input>` elements should have `id` attributes and proper `<label>` associations
- Always include `htmlspecialchars()` when outputting user data

### Admin Pages Pattern
- All admin pages start with: `require_once __DIR__ . '/../includes/header.php'; require_admin();`
- Check with: `if (!is_admin()) { header('Location: login.php'); exit; }`
- Use Bootstrap tables with hover effects for data display
- Provide edit/delete buttons with confirmation dialogs
- Show success/error messages with Bootstrap alerts
- Redirect to dashboard after successful operations with `?success=1`

### Error Handling
- Wrap database operations in try-catch blocks
- Use `log_error()` to log exceptions (if `DEBUG_MODE` is enabled)
- Display user-friendly error messages from `ERROR_MESSAGES` constant
- Never expose PHP errors or stack traces to users in production
- Log failed login attempts for security

## Docker & Local Development
- Build & run: `docker-compose up --build` (creates container `portfolio-php`)
- PHP errors displayed in browser (set via environment: `PHP_DISPLAY_ERRORS=1`)
- Database persists in `./database/portfolio.db` (mounted volume)
- Code changes auto-reflect in container (`.:/var/www/html` volume mount)
- Default admin credentials: `admin` / `admin123`

## Common Tasks

### Adding a New Work (Artwork)
1. Use admin form at `admin/add_work.php`
2. Validates: title (required), category (required), image (required)
3. Calls `save_uploaded_file()` helper for validation and upload
4. Inserts into `works` table with `created_at` timestamp
5. Redirects to dashboard with success message

### User Registration Flow
1. Form in `auth.php` with validation
2. Validate email format, password strength (min 6 chars), password match
3. Hash password with `password_hash($pwd, PASSWORD_DEFAULT)`
4. Insert into `users` table with `created_at`
5. Catch `PDOException` code 23000 for duplicate email
6. Show success message, redirect to login

### Session Management
- Login sets `$_SESSION['user_id']`, `$_SESSION['user_name']`, `$_SESSION['admin_logged']`
- Logout via `logout.php` - destroys session and redirects with `?logged_out=1`
- Protected pages check session at start, not end of file
- Redirect non-authenticated users to `auth.php` or `admin/login.php`

## Language Context
- Site primarily Ukrainian (lang="uk")
- All UI text, labels, error messages in Ukrainian
- Use `SITE_NAME` and `SITE_LANG` constants from config
- Error messages centralized in `ERROR_MESSAGES` array in config.php

## Testing Checklist
- Forms validate all required fields with helpful error messages
- Images upload with proper MIME type and size validation
- Old images deleted when updating works
- Session auth persists across page refreshes
- PDO queries use parameterized syntax (no string concatenation)
- Admin pages redirect non-authenticated users to login
- Output always uses `htmlspecialchars()` for user content
- All database operations wrapped in try-catch with logging
- Form resubmission preserves user input where appropriate
