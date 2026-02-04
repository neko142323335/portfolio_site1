# Noosfera Portfolio Site - Setup Instructions

## Installation

### Prerequisites
- PHP 8.1 or higher (for local development)
- Docker & Docker Compose (recommended)

### Setup Steps

**Option 1: Docker (Recommended)**

```bash
docker-compose up --build
```

The Docker image will automatically:
- Install Composer
- Run `composer install` to download PHP dependencies
- Configure PHP extensions (PDO, SQLite)
- Set up Apache with mod_rewrite
- Initialize the SQLite database

**Note:** The Docker image uses a named volume for `vendor/` directory. This prevents the local file mount from overwriting Composer dependencies installed during the image build.

Access at: http://localhost:8080

### Default Credentials

- **Admin Panel:** http://localhost:8080/admin/login.php
- **Email:** `admin@noosfera.ua`
- **Password:** `admin123`

**Option 2: Local Development**

```bash
composer install
php -S localhost:8000
```

Access at: http://localhost:8000

## Docker Configuration

The `docker-compose.yml` automatically:
- Maps port 8080 (host) → 80 (container)
- Mounts local files to `/var/www/html` for live code changes
- Creates persistent database volume at `./database/portfolio.db`
- Sets environment variables for admin credentials and debug mode

To rebuild after code changes:
```bash
docker-compose up --build
```

To stop the container:
```bash
docker-compose down
```

## Project Structure

```
portfolio_site1/
├── admin/                 # Admin panel pages
├── assets/
│   ├── css/
│   ├── js/
│   └── img/works/        # Uploaded artwork storage
├── includes/             # Shared PHP files
│   ├── config.php        # Configuration & constants
│   ├── db.php            # Database initialization
│   ├── header.php        # (Legacy) HTML header
│   ├── footer.php        # (Legacy) HTML footer
│   ├── helpers.php       # Helper functions
│   └── twig.php          # Twig template engine config
├── templates/            # Twig HTML templates
│   ├── base.html.twig    # Base layout
│   ├── index.html.twig   # Home page
│   └── footer.html.twig  # Footer component
├── *.php                 # Page controllers
├── docker-compose.yml    # Docker configuration
└── composer.json         # PHP dependencies
```

## Technology Stack

- **Backend:** PHP 8.1 with PDO & SQLite
- **Template Engine:** Twig 3.8
- **Frontend:** Bootstrap 5.3 + Custom CSS
- **Database:** SQLite (auto-initialized)
- **Animation:** AOS (Animate On Scroll)
- **Icons:** Bootstrap Icons

## Features

✅ User registration & authentication (email-based)
✅ Admin panel for managing artwork
✅ Gallery with category filtering
✅ Dynamic data from SQLite database
✅ Responsive design
✅ Smooth animations
✅ Back-to-top button

## Environment Variables

Set in `docker-compose.yml`:
- `ADMIN_EMAIL` - Default admin email
- `ADMIN_PASSWORD` - Default admin password
- `APP_ENV` - Application environment (dev/prod)
- `DEBUG_MODE` - Enable debug logging

## Notes

The application uses Twig 3.8 for templating. All main pages and admin panels have been converted to Twig templates:

- `auth.php` - User login/registration
- `contact.php` - Contact form
- `dashboard.php` - User profile
- `admin/login.php` - Admin authentication  
- `admin/dashboard.php` - Admin panel
- `admin/add_work.php` - Add artwork
- `admin/edit_work.php` - Edit artwork

Twig templates are located in `templates/` directory and automatically loaded during Docker build or manual setup with `composer install`.
