# Noosfera Portfolio - Повний гайд розробника

## 📋 Зміст
1. [Архітектура проекту](#архітектура-проекту)
2. [Структура файлів](#структура-файлів)
3. [Як додати нову сторінку](#як-додати-нову-сторінку)
4. [Як додати CRUD](#як-додати-crud)
5. [Робота з базою даних](#робота-з-базою-даних)
6. [Twig шаблони](#twig-шаблони)
7. [Типові сценарії](#типові-сценарії)

---

## 🏗️ Архітектура проекту

### MVC Pattern (Model-View-Controller)

```
┌─────────────┐     ┌──────────────┐     ┌─────────────┐
│   Browser   │────▶│    Router    │────▶│ Controller  │
│             │     │              │     │             │
└─────────────┘     └──────────────┘     └──────┬──────┘
       ▲                                        │
       │                                        ▼
       │            ┌──────────────┐     ┌─────────────┐
       └────────────│     View     │◀────│    Model    │
                    │   (Twig)     │     │   (PDO)     │
                    └──────────────┘     └─────────────┘
```

**Потік даних:**
1. Браузер → `public/index.php` (Front Controller)
2. Router → знаходить контролер за URL
3. Controller → отримує дані з Model
4. Controller → передає дані в View (Twig)
5. Twig → рендерить HTML
6. HTML → повертається браузеру

---

## 📁 Структура файлів

```
portfolio_site1/
├── public/                    # Публічна папка (DocumentRoot)
│   ├── index.php             # 🔑 Front Controller - точка входу
│   └── assets/               # Симлінк на ../assets
│
├── src/                       # 🧠 Логіка додатку
│   ├── Router.php            # Маршрутизатор
│   ├── Controllers/          # Контролери
│   │   ├── BaseController.php      # Базовий клас
│   │   ├── HomeController.php
│   │   ├── GalleryController.php
│   │   ├── AuthController.php
│   │   ├── ContactController.php
│   │   ├── DashboardController.php
│   │   └── Admin/                  # Адмін контролери
│   │       ├── CategoryController.php
│   │       ├── WorkController.php
│   │       └── UserController.php
│   │
│   ├── Models/               # Моделі (робота з БД)
│   │   ├── Category.php
│   │   ├── Work.php
│   │   └── User.php
│   │
│   └── Database/
│       └── Connection.php    # Singleton підключення до БД
│
├── templates/                # 🎨 Twig шаблони
│   ├── base.html.twig       # Базовий layout
│   ├── index.html.twig
│   ├── gallery.html.twig
│   ├── auth.html.twig
│   ├── dashboard.html.twig
│   └── admin/
│       ├── dashboard.html.twig
│       ├── categories/
│       ├── works/
│       └── users/
│
├── includes/                 # Допоміжні файли
│   ├── config.php           # Конфігурація
│   ├── db.php               # Ініціалізація БД
│   ├── twig.php             # Налаштування Twig
│   └── helpers.php          # Функції-помічники
│
├── assets/                   # Статичні ресурси
│   ├── css/
│   │   ├── bootstrap.min.css
│   │   └── style.css
│   ├── js/
│   │   └── script.js
│   └── img/
│       └── works/           # Завантажені зображення
│
├── database/                 # SQLite БД
│   └── portfolio.db
│
├── admin/                    # Legacy файли (частково)
│   ├── login.php
│   └── dashboard.php
│
├── docker-compose.yml
├── dockerfile
└── composer.json
```

---

## 🆕 Як додати нову сторінку

### Крок 1: Створити контролер

**Файл:** `src/Controllers/AboutController.php`

```php
<?php
namespace App\Controllers;

class AboutController extends BaseController
{
  public function index()
  {
    $this->render('about.html.twig', [
      'title' => 'Про нас',
      'description' => 'Опис компанії',
    ]);
  }
}
```

### Крок 2: Створити шаблон

**Файл:** `templates/about.html.twig`

```twig
{% extends "base.html.twig" %}

{% block title %}{{ title }} - {{ site_name }}{% endblock %}

{% block content %}
  <section class="auth-section">
    <div class="container">
      <h1>{{ title }}</h1>
      <p>{{ description }}</p>
    </div>
  </section>
  {% include 'footer.html.twig' %}
{% endblock %}
```

### Крок 3: Зареєструвати роут

**Файл:** `public/index.php`

```php
// 1. Додати use statement
use App\Controllers\AboutController;

// 2. Додати роут
$router
  ->add('/', HomeController::class, 'index')
  ->add('/about', AboutController::class, 'index')  // ← новий роут
  ->add('/gallery', GalleryController::class, 'index');
```

### Крок 4: Додати в навігацію (опціонально)

**Файл:** `templates/base.html.twig`

```twig
<ul class="nav-menu">
  <li><a href="{{ base }}">Головна</a></li>
  <li><a href="{{ base }}about">Про нас</a></li>  {# ← новий пункт #}
  <li><a href="{{ base }}gallery">Проекти</a></li>
</ul>
```

---

## 🔄 Як додати CRUD (Create, Read, Update, Delete)

### Приклад: Додавання CRUD для коментарів

#### 1. Створити таблицю в БД

**Файл:** `src/Database/Connection.php` → метод `initializeDatabase()`

```php
$db->exec('
  CREATE TABLE IF NOT EXISTS comments (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    work_id INTEGER NOT NULL,
    user_id INTEGER NOT NULL,
    text TEXT NOT NULL,
    created_at DATETIME NOT NULL,
    FOREIGN KEY (work_id) REFERENCES works(id),
    FOREIGN KEY (user_id) REFERENCES users(id)
  );
');
```

#### 2. Створити модель

**Файл:** `src/Models/Comment.php`

```php
<?php
namespace App\Models;

use PDO;

class Comment
{
  private PDO $db;

  public function __construct(PDO $db)
  {
    $this->db = $db;
  }

  // CREATE
  public function create($data)
  {
    $stmt = $this->db->prepare('
      INSERT INTO comments (work_id, user_id, text, created_at)
      VALUES (:work_id, :user_id, :text, :created_at)
    ');
    
    return $stmt->execute([
      ':work_id' => $data['work_id'],
      ':user_id' => $data['user_id'],
      ':text' => $data['text'],
      ':created_at' => date('Y-m-d H:i:s'),
    ]);
  }

  // READ - всі коментарі
  public function getAll()
  {
    $stmt = $this->db->query('SELECT * FROM comments ORDER BY created_at DESC');
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  // READ - за ID
  public function getById($id)
  {
    $stmt = $this->db->prepare('SELECT * FROM comments WHERE id = :id');
    $stmt->execute([':id' => $id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
  }

  // READ - за роботою
  public function getByWork($work_id)
  {
    $stmt = $this->db->prepare('SELECT * FROM comments WHERE work_id = :work_id ORDER BY created_at DESC');
    $stmt->execute([':work_id' => $work_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  // UPDATE
  public function update($id, $data)
  {
    $stmt = $this->db->prepare('UPDATE comments SET text = :text WHERE id = :id');
    return $stmt->execute([
      ':id' => $id,
      ':text' => $data['text'],
    ]);
  }

  // DELETE
  public function delete($id)
  {
    $stmt = $this->db->prepare('DELETE FROM comments WHERE id = :id');
    return $stmt->execute([':id' => $id]);
  }
}
```

#### 3. Створити контролер

**Файл:** `src/Controllers/Admin/CommentController.php`

```php
<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\Comment;

class CommentController extends BaseController
{
  public function __construct($db, $twig)
  {
    parent::__construct($db, $twig);
    $this->requireAdmin();  // Тільки для адміна
  }

  // Список
  public function index()
  {
    $commentModel = new Comment($this->db);
    $comments = $commentModel->getAll();

    $this->render('admin/comments/index.html.twig', [
      'comments' => $comments,
      'success' => $this->getQuery('success') ? 'Операція успішна' : '',
    ]);
  }

  // Додавання
  public function add()
  {
    $error = '';

    if ($this->isPost()) {
      require_once __DIR__ . '/../../../includes/helpers.php';
      
      $text = sanitize_input($this->getPost('text'));
      $work_id = (int) $this->getPost('work_id');

      if (!$text || !$work_id) {
        $error = 'Всі поля обов\'язкові';
      } else {
        $commentModel = new Comment($this->db);
        $commentModel->create([
          'work_id' => $work_id,
          'user_id' => $_SESSION['user_id'],
          'text' => $text,
        ]);
        $this->redirect('/admin/comments?success=1');
      }
    }

    $this->render('admin/comments/add.html.twig', [
      'error' => $error,
    ]);
  }

  // Редагування
  public function edit()
  {
    $id = (int) $this->getQuery('id');
    $commentModel = new Comment($this->db);
    $comment = $commentModel->getById($id);

    if (!$comment) {
      $this->redirect('/admin/comments?error=1');
    }

    $error = '';

    if ($this->isPost()) {
      require_once __DIR__ . '/../../../includes/helpers.php';
      $text = sanitize_input($this->getPost('text'));

      if (!$text) {
        $error = 'Текст обов\'язковий';
      } else {
        $commentModel->update($id, ['text' => $text]);
        $this->redirect('/admin/comments?success=1');
      }
    }

    $this->render('admin/comments/edit.html.twig', [
      'comment' => $comment,
      'error' => $error,
    ]);
  }

  // Видалення
  public function delete()
  {
    $id = (int) $this->getQuery('id');
    $commentModel = new Comment($this->db);
    $commentModel->delete($id);
    $this->redirect('/admin/comments?success=1');
  }
}
```

#### 4. Створити шаблони

**Файл:** `templates/admin/comments/index.html.twig`

```twig
{% extends "base.html.twig" %}

{% block title %}Коментарі - {{ site_name }}{% endblock %}

{% block content %}
  <section class="auth-section" style="padding-top: 150px; padding-bottom: 100px;">
    <div class="auth-container" style="max-width: 900px;">
      <h2 class="auth-title" style="color: #ff4500;">Коментарі</h2>

      {% if success %}
        <div class="alert alert-success">Операція успішна</div>
      {% endif %}

      <div class="mb-4">
        <a href="/admin/comments/add" class="btn btn-primary">
          <i class="bi bi-plus-circle"></i> Додати коментар
        </a>
      </div>

      <div class="table-responsive">
        <table class="table table-hover table-dark">
          <thead>
            <tr>
              <th style="color: #ff4500;">Текст</th>
              <th style="color: #ff4500;">Дата</th>
              <th style="color: #ff4500;">Дії</th>
            </tr>
          </thead>
          <tbody>
            {% for comment in comments %}
              <tr>
                <td>{{ comment.text|sanitize }}</td>
                <td>{{ comment.created_at }}</td>
                <td>
                  <a href="/admin/comments/edit?id={{ comment.id }}" class="btn btn-sm btn-warning">
                    <i class="bi bi-pencil"></i> Редагувати
                  </a>
                  <a href="/admin/comments/delete?id={{ comment.id }}" class="btn btn-sm btn-danger" onclick="return confirm('Ви впевнені?')">
                    <i class="bi bi-trash"></i> Видалити
                  </a>
                </td>
              </tr>
            {% endfor %}
          </tbody>
        </table>
      </div>
    </div>
  </section>
{% endblock %}
```

#### 5. Зареєструвати роути

**Файл:** `public/index.php`

```php
use App\Controllers\Admin\CommentController;

$router
  ->add('/admin/comments', CommentController::class, 'index')
  ->add('/admin/comments/add', CommentController::class, 'add')
  ->add('/admin/comments/edit', CommentController::class, 'edit')
  ->add('/admin/comments/delete', CommentController::class, 'delete');
```

---

## 💾 Робота з базою даних

### Підключення

```php
use App\Database\Connection;

$db = Connection::get();  // Singleton pattern
```

### Виконання запитів

#### SELECT

```php
// Простий запит
$stmt = $db->query('SELECT * FROM works');
$works = $stmt->fetchAll(PDO::FETCH_ASSOC);

// З параметрами
$stmt = $db->prepare('SELECT * FROM works WHERE category = :cat');
$stmt->execute([':cat' => $category]);
$works = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Один запис
$stmt = $db->prepare('SELECT * FROM works WHERE id = :id');
$stmt->execute([':id' => $id]);
$work = $stmt->fetch(PDO::FETCH_ASSOC);
```

#### INSERT

```php
$stmt = $db->prepare('
  INSERT INTO works (title, description, category, image, created_at)
  VALUES (:title, :desc, :cat, :img, :created)
');

$stmt->execute([
  ':title' => $title,
  ':desc' => $description,
  ':cat' => $category,
  ':img' => $imagePath,
  ':created' => date('Y-m-d H:i:s'),
]);

// Отримати ID вставленого запису
$lastId = $db->lastInsertId();
```

#### UPDATE

```php
$stmt = $db->prepare('
  UPDATE works 
  SET title = :title, category = :cat 
  WHERE id = :id
');

$stmt->execute([
  ':title' => $newTitle,
  ':cat' => $newCategory,
  ':id' => $id,
]);
```

#### DELETE

```php
$stmt = $db->prepare('DELETE FROM works WHERE id = :id');
$stmt->execute([':id' => $id]);
```

### Обробка помилок

```php
try {
  $stmt = $db->prepare('SELECT * FROM works WHERE id = :id');
  $stmt->execute([':id' => $id]);
  $work = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
  log_error('Database error', ['message' => $e->getMessage()]);
  // Показати користувачу дружнє повідомлення
  $error = 'Помилка бази даних';
}
```

---

## 🎨 Twig шаблони

### Базова структура

```twig
{% extends "base.html.twig" %}

{% block title %}Назва сторінки - {{ site_name }}{% endblock %}

{% block content %}
  {# Ваш контент тут #}
{% endblock %}
```

### Глобальні змінні

```twig
{{ site_name }}      {# Назва сайту #}
{{ site_lang }}      {# Мова (uk) #}
{{ base }}           {# Базовий URL (/) #}
```

### Функції

```twig
{% if is_admin() %}
  {# Контент для адміна #}
{% endif %}

{% if user_id() %}
  {# Контент для залогінених #}
{% endif %}

{% if is_current_page('gallery') %}
  class="active"
{% endif %}

{{ user_name() }}    {# Ім'я користувача #}
```

### Фільтри

```twig
{{ text|sanitize }}           {# Очищення HTML #}
{{ text|nl2br }}              {# Переноси рядків → <br> #}
{{ url|url_encode }}          {# Кодування URL #}
{{ value|default('Default') }} {# Значення за замовчуванням #}
```

### Умови

```twig
{% if works is empty %}
  <p>Немає робіт</p>
{% else %}
  {% for work in works %}
    <h3>{{ work.title }}</h3>
  {% endfor %}
{% endif %}
```

### Цикли

```twig
{% for work in works %}
  <div class="work">
    <h3>{{ work.title }}</h3>
    <p>{{ work.description }}</p>
  </div>
{% else %}
  <p>Список порожній</p>
{% endfor %}
```

### Include інших шаблонів

```twig
{% include 'footer.html.twig' %}
{% include 'components/alert.html.twig' with {'message': 'Успіх!'} %}
```

---

## 🔧 Типові сценарії

### 1. Додати нове поле в форму

#### У контролері:
```php
$new_field = sanitize_input($this->getPost('new_field'));
```

#### У моделі:
```php
$stmt = $db->prepare('
  INSERT INTO table (old_field, new_field) 
  VALUES (:old, :new)
');
$stmt->execute([
  ':old' => $data['old_field'],
  ':new' => $data['new_field'],  // ← нове поле
]);
```

#### У шаблоні:
```twig
<div class="mb-3">
  <label for="new_field">Нове поле</label>
  <input type="text" name="new_field" id="new_field" class="form-control">
</div>
```

### 2. Завантаження файлів

```php
if (!empty($_FILES['image']['tmp_name'])) {
  require_once __DIR__ . '/../../includes/helpers.php';
  
  $file_upload = save_uploaded_file($_FILES['image']);
  
  if ($file_upload['success']) {
    $imagePath = $file_upload['path'];
  } else {
    $error = $file_upload['error'];
  }
}
```

### 3. Захист сторінок

#### Тільки для адміна:
```php
public function __construct($db, $twig)
{
  parent::__construct($db, $twig);
  $this->requireAdmin();
}
```

#### Тільки для залогінених:
```php
if (!$this->isLoggedIn()) {
  $this->redirect('/auth');
}
```

### 4. Валідація даних

```php
require_once __DIR__ . '/../../includes/helpers.php';

// Email
if (!validate_email($email)) {
  $error = 'Невірний формат email';
}

// Пароль (мін 6 символів)
if (!validate_password($password)) {
  $error = 'Пароль має бути мінімум 6 символів';
}

// Файл
$validation = validate_file_upload($_FILES['image']);
if (!$validation['valid']) {
  $error = $validation['error'];
}
```

### 5. Відправка форм POST

#### У шаблоні:
```twig
<form method="POST" action="{{ base }}contact">
  <input type="text" name="name" required>
  <button type="submit" name="submit" value="1">Відправити</button>
</form>
```

#### У контролері:
```php
if ($this->isPost() && $this->getPost('submit')) {
  $name = sanitize_input($this->getPost('name'));
  // Обробка форми
}
```

### 6. Повідомлення успіху/помилки

#### У контролері:
```php
$this->redirect('/admin/works?success=1');
// або
$this->redirect('/admin/works?error=' . urlencode('Текст помилки'));
```

#### У шаблоні:
```twig
{% if success %}
  <div class="alert alert-success">{{ success }}</div>
{% endif %}

{% if error %}
  <div class="alert alert-danger">{{ error }}</div>
{% endif %}
```

---

## 🚀 Запуск проекту

### Docker (рекомендовано)

```bash
# Запуск
docker-compose up -d

# Перегляд логів
docker-compose logs -f

# Зупинка
docker-compose down

# Перебудова
docker-compose up --build
```

### Локально

```bash
composer install
php -S localhost:8000 -t public
```

---

## 🐛 Налагодження

### Логування помилок

```php
require_once __DIR__ . '/../../includes/helpers.php';

log_error('Помилка в контролері', [
  'user_id' => $_SESSION['user_id'] ?? null,
  'error' => $e->getMessage(),
]);
```

Логи зберігаються в: `contacts.log`

### Перевірка запитів

```php
// В контролері
var_dump($_POST);
var_dump($_FILES);
var_dump($_SESSION);
exit;
```

### Перевірка SQL

```php
echo $stmt->queryString;  // Показати SQL запит
var_dump($stmt->errorInfo());  // Помилки SQL
```

---

## 📦 Composer пакети

- `twig/twig` - шаблонізатор
- Додати нові: `composer require vendor/package`

---

## ✅ Чеклист при додаванні нової функції

- [ ] Створено модель (якщо потрібна робота з БД)
- [ ] Створено контролер
- [ ] Створено шаблон(и) Twig
- [ ] Зареєстровано роут(и) в `public/index.php`
- [ ] Додано use statement для контролера
- [ ] Додано валідацію даних
- [ ] Додано обробку помилок (try-catch)
- [ ] Перевірено безпеку (sanitize, require_admin)
- [ ] Протестовано функціонал
- [ ] Додано в навігацію (якщо потрібно)

---

## 🎯 Приклади реальних завдань

### Завдання 1: Додати поле "ціна" до робіт

1. **БД**: Додати колонку в `src/Database/Connection.php`
2. **Модель**: Оновити `Work::create()` та `Work::update()`
3. **Форми**: Додати `<input name="price">` в шаблони
4. **Контролер**: Додати `$price = $this->getPost('price')`
5. **Відображення**: Додати `{{ work.price }}` в шаблони

### Завдання 2: Додати можливість лайкати роботи

1. **БД**: Таблиця `likes (id, work_id, user_id, created_at)`
2. **Модель**: `Like.php` з методами `add()`, `remove()`, `count()`
3. **Контролер**: `LikeController` з методом `toggle()`
4. **JS**: AJAX запит на `/likes/toggle?work_id=X`
5. **UI**: Кнопка з іконкою серця + лічильник

---

**Автор:** Noosfera Team  
**Версія:** 2.0 (MVC + Twig)  
**Дата:** 2026
