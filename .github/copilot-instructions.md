# Copilot Instructions: Noosfera Portfolio Site

## Architecture Overview

**MVC Pattern with Twig Templating**
- **Entry Point:** `public/index.php` (Front Controller) - defines all routes
- **Routing:** `src/Router.php` - dispatches requests with **regex parameter support** for dynamic URLs
- **Controllers:** `src/Controllers/` - handle request logic, inherit from `BaseController`
- **Models:** `src/Models/` - database operations via PDO
- **Validators:** `src/Validators/FileValidator.php` - centralized validation logic
- **Views:** `templates/` - Twig templates with auto-escaping enabled
- **Database:** SQLite (`database/portfolio.db`) with auto-initialization on first run

**Request Flow:**
```
Browser → public/index.php → Router (regex matching) → Controller → Validator/Model → Twig → HTML
```
## Critical Workflows

### Dynamic Routing with Parameters
- **Simple parameter:** `/user/{id}` matches `/user/123`, passes `$id='123'` to action
- **Regex parameter:** `/post/{id:\d+}` matches only numeric IDs; `/slug/{name:[a-z0-9-]+}` matches slugs
- **Multiple parameters:** `/user/{userId}/post/{postId}` passes both to action method `action($userId, $postId)`
- **Priority:** Exact routes first, then dynamic routes in registration order
- **Example:** Admin edit route: `->add('/admin/works/{id:\d+}/edit', WorkController::class, 'edit')`

### File Validation Pattern
- **Centralized:** Use `FileValidator::validate($_FILES['field'] ?? [])` for all file uploads
- **Returns:** `['valid' => bool, 'error' => string, 'code' => string, 'details' => array]`
- **7-layer validation:** Field presence → PHP errors → tmp file → size → extension → MIME → valid image
- **Used in:** `Admin/WorkController`, `Dashboard/WorkController` (add/edit methods)
- **Constraints:** 5MB max, JPG/PNG/WebP/GIF only (in `src/Validators/FileValidator.php`)

### Local Development
```bash
# Option 1: Docker (recommended)
docker-compose up --build
# Access: http://localhost:8080
# Admin: admin@noosfera.ua / admin123 (env vars in docker-compose.yml)

# Option 2: Local PHP
composer install
php -S localhost:8000
```

### Database
- **Auto-initializes** on first connection via `Connection::initializeDatabase()`
- **Location:** `database/portfolio.db` (SQLite)
- **Schema:** users, works, admin, categories tables
- **Connection:** Singleton pattern in `src/Database/Connection.php`

### Debugging
- **Logs:** `logs/error.log` (only when `DEBUG_MODE=1`)
- **Config:** Environment variables in `docker-compose.yml`
- **Twig:** Debug extension enabled in `includes/twig.php` when DEBUG_MODE is on

### Pagination
- **Default:** 12 items per page (configurable via `PER_PAGE` constant in controllers)
- **Gallery:** `GalleryController` uses `getPaginated($page, $perPage, $category)`
- **Dashboard:** `DashboardController` uses `getByUserIdPaginated($user_id, $page, $perPage)`
- **URL pattern:** `?page=2` (preserves category filter: `?page=2&category=name`)
- **Helper methods in `Work` model:**
  - `getTotal($category)` - count total items
  - `getPaginated($page, $perPage, $category)` - get slice of items
  - `getTotalPages($perPage, $category)` - calculate page count
  - `getByUserIdPaginated($user_id, $page, $perPage)` - paginate user's items
  - `getUserWorkCount($user_id)`, `getUserWorkPages($user_id, $perPage)` - user metrics

## Project-Specific Conventions

### Autoloading & Namespaces
- **Namespace prefix:** `App\` maps to `src/` (PSR-4 in composer.json)
- **Files requiring no namespace:** `includes/config.php`, `includes/helpers.php`
- **Import pattern:** Always use `require_once` for includes, use `use` statements for namespaced classes

### Controllers
- **Must extend:** `BaseController` which provides:
  - `$this->render($template, $data)` - render Twig template
  - `$this->redirect($url)` - HTTP redirect
  - `$this->isLoggedIn()`, `$this->isAdmin()` - session checks
  - `$this->requireAuth()`, `$this->requireAdmin()` - enforce auth (redirects if fails)
  - `$this->getPost($key)`, `$this->getQuery($key)` - safe request param access
  - `$this->isPost()` - check if POST request

**Example:** `GalleryController` shows standard pattern - get data from Model, render with Twig

### Models
- **Database access only** - no business logic beyond DB operations
- **All queries use prepared statements** with bound parameters for SQL injection prevention
- **Use PDO::FETCH_ASSOC** for associative arrays
- **Exception handling:** Catch PDOException for duplicate keys, re-throw others

**Example:** `Work.php` methods: `getAll()`, `getByCategory($cat)`, `getById($id)`, `create($data)`, `update($id, $data)`, `delete($id)`

### Input Validation & Sanitization
- **Always use:** `sanitize_input($var)` - trims, strips slashes, escapes HTML
- **Email validation:** `validate_email($email)` returns boolean
- **Password validation:** `validate_password($pwd)` checks min 6 chars
- **File validation:** `FileValidator::validate($file)` - comprehensive file checking (7 layers)
  - Import: `use App\Validators\FileValidator;`
  - Call: `$validation = FileValidator::validate($_FILES['image'] ?? []);`
  - Check: `if (!$validation['valid']) { $error = $validation['error']; }`
  - Then: `$result = save_uploaded_file($_FILES['image']);` (only after FileValidator passes)
  - **See:** `.github/FILE_VALIDATION_GUIDE.md` for detailed patterns and examples
- **Save files:** `save_uploaded_file($file)` handles directory creation and returns path (use after FileValidator passes)

### Authentication
- **Session-based:** `$_SESSION['user_id']`, `$_SESSION['admin_logged']` (boolean)
- **Password hashing:** Use `password_hash($pwd, PASSWORD_DEFAULT)` to store, `password_verify($pwd, $hash)` to check
- **Admin redirect:** `$this->requireAdmin()` redirects non-admins to `/admin/login`
- **User redirect:** `$this->requireAuth()` redirects to `/auth`

### Twig Templates
- **Auto-escaping:** Enabled (safe for XSS) in `twig.php`
- **Global variables:** `site_name`, `site_lang`, `base` (all defined in `twig.php`)
- **Custom functions:** `is_admin()`, `is_current_page()`, `user_id()`, `user_name()`
- **Base layout:** `templates/base.html.twig` - extend this for common structure

### Logging & Error Handling
- **Log errors:** `log_error($message, $context = [])` in helpers.php
- **Logging location:** `logs/error.log` (auto-created, only if DEBUG_MODE=1)
- **Exception pattern:** Wrap DB operations in try-catch, log full message, show user-friendly error in render()

**Example from GalleryController:**
```php
try {
  // DB operations
} catch (\Exception $e) {
  log_error('Error in GalleryController::index', ['message' => $e->getMessage()]);
  $works = [];
}
```

## File Structure Reference

```
src/
  Controllers/
    BaseController.php       // Extend this
    {FeatureController}.php  // One per feature
    Admin/
      {FeatureController}.php
    Dashboard/
      {FeatureController}.php
  Models/
    {Entity}.php            // One per DB table
  Validators/
    FileValidator.php       // File upload validation (7 layers)
  Database/
    Connection.php          // Don't modify
  Router.php                // Modify only to add routes

includes/
  config.php                // App constants (don't auto-reload)
  helpers.php               // Helper functions
  db.php                    // DB initialization
  twig.php                  // Twig setup

templates/
  base.html.twig            // Base layout
  {feature}.html.twig       // Feature pages
  admin/                    // Admin templates
  dashboard/                // User dashboard templates

public/
  index.php                 // Front controller - modify to add routes
```

## Adding New Features

**Step 1:** Add route in `public/index.php`
```php
->add('/new-feature', NewFeatureController::class, 'index')
```

**Step 2:** Create `src/Controllers/NewFeatureController.php` extending `BaseController`

**Step 3:** Create Twig template `templates/new-feature.html.twig` extending `base.html.twig`

**Step 4:** If data needed, create/update model in `src/Models/`

**Step 5:** Use `sanitize_input()` for all user inputs, `log_error()` for issues

## Security Checklist

✅ All SQL queries use prepared statements
✅ All user input processed through `sanitize_input()` before rendering/DB
✅ Passwords stored with `password_hash()`, verified with `password_verify()`
✅ Admin routes use `$this->requireAdmin()` redirects
✅ File uploads validated with `FileValidator` (7-layer check: field → errors → size → extension → MIME → image type)
✅ Twig auto-escaping enabled prevents XSS
✅ Session-based auth with `$_SESSION['user_id']`
✅ Router regex parameters prevent type confusion (e.g., `{id:\d+}` only accepts digits)

## Common Tasks

| Task | How To |
|------|--------|
| Add new DB table | Edit `Connection::initializeDatabase()` in `src/Database/Connection.php` |
| Add simple route | `->add('/path', ControllerClass::class, 'action')` in `public/index.php` |
| Add dynamic route | `->add('/user/{id:\d+}', ControllerClass::class, 'show')` - parameters become method args |
| Validate file upload | `FileValidator::validate($_FILES['field'] ?? [])` before `save_uploaded_file()` |
| Add validation rule | Create function in `includes/helpers.php`, use in controller |
| Render page | `$this->render('template.html.twig', ['data' => $data])` |
| Redirect | `$this->redirect('/path')` |
| Check if admin | `if ($this->isAdmin()) { ... }` |
| Require login | `$this->requireAuth()` at start of action |
| Log for debugging | `log_error('message', ['key' => $value])` (if DEBUG_MODE=1) |

## Technology Stack

- **PHP:** 8.1+ with PDO
- **Database:** SQLite 3
- **Templating:** Twig 3.8
- **Frontend:** Bootstrap 5.3, custom CSS
- **Autoloader:** Composer PSR-4

---

**Last Updated:** 2026-02-07
**Recent Improvements:**
- ✨ Pagination implemented in Gallery and Dashboard (12 items per page, configurable)
- ✨ Helper methods: `getPaginated()`, `getByUserIdPaginated()`, `getUserWorkPages()`, `getTotal()`
- ✨ Pagination links preserve category filters and respect Bootstrap styling
- ✨ FileValidator centralized with 7-layer validation (field → errors → size → extension → MIME → image type → valid image)
- ✨ `save_uploaded_file()` now requires FileValidator pre-validation (documented in `.github/FILE_VALIDATION_GUIDE.md`)
- ✨ Router enhanced with dynamic parameters and regex support (`{id:\d+}`, `{slug:[a-z0-9-]+}`)
