# Приклад: Міграція контролера на динамічні параметри

## Поточний підхід (з getQuery)

```php
<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\Work;

class WorkController extends BaseController
{
  // ... інший код ...

  /**
   * Форма редагування роботи
   * 
   * ПОТОЧНО: ID передається через query параметр
   * Маршрут: /admin/works/edit?id=123
   */
  public function edit()
  {
    $error = '';
    $id = (int) $this->getQuery('id');  // ← Отримати з URL query
    
    if (!$id) {
      $this->redirect('/admin/dashboard?error=' . urlencode('Роботу не знайдено'));
      return;
    }

    // ... обробка редагування ...
  }

  /**
   * Видалення роботи
   * 
   * ПОТОЧНО: ID передається через query параметр
   * Маршрут: /admin/works/delete?id=123
   */
  public function delete()
  {
    $id = (int) $this->getQuery('id');  // ← Отримати з URL query
    
    if (!$id) {
      $this->redirect('/admin/dashboard');
      return;
    }

    // ... видалення ...
  }
}
```

### Використання в шаблонах

```html
<!-- Поточно -->
<a href="/admin/works/edit?id=123">Редагувати</a>
<a href="/admin/works/delete?id=456">Видалити</a>
```

---

## Новий підхід (з параметрами маршруту)

### Оновлена версія контролера

```php
<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\Work;

class WorkController extends BaseController
{
  // ... інший код ...

  /**
   * Форма редагування роботи
   * 
   * НОВ0: ID передається як параметр методу
   * Маршрут: /admin/works/{id}/edit
   * URL: /admin/works/123/edit
   */
  public function edit($id)  // ← ID як параметр!
  {
    $error = '';
    $id = (int) $id;  // ← Просто очистити тип
    
    if (!$id) {
      $this->redirect('/admin/dashboard?error=' . urlencode('Роботу не знайдено'));
      return;
    }

    // ... обробка редагування залишається незмінною ...
  }

  /**
   * Видалення роботи
   * 
   * НОВО: ID передається як параметр методу
   * Маршрут: /admin/works/{id}/delete
   * URL: /admin/works/123/delete
   */
  public function delete($id)  // ← ID як параметр!
  {
    $id = (int) $id;  // ← Просто очистити тип
    
    if (!$id) {
      $this->redirect('/admin/dashboard');
      return;
    }

    // ... видалення залишається незмінним ...
  }
}
```

### Оновлені маршрути в index.php

```php
// Поточно
$router
  ->add('/admin/works/edit', AdminWorkController::class, 'edit')
  ->add('/admin/works/delete', AdminWorkController::class, 'delete');

// Ново
$router
  ->add('/admin/works/{id:\d+}/edit', AdminWorkController::class, 'edit')
  ->add('/admin/works/{id:\d+}/delete', AdminWorkController::class, 'delete');
```

### Використання в шаблонах

```html
<!-- Поточно -->
<a href="/admin/works/edit?id=123">Редагувати</a>
<a href="/admin/works/delete?id=456">Видалити</a>

<!-- НОВО -->
<a href="/admin/works/123/edit">Редагувати</a>
<a href="/admin/works/456/delete">Видалити</a>
```

---

## Переваги нового підходу

| Аспект | Поточно | Ново |
|--------|---------|------|
| **URL** | `/admin/works/edit?id=123` | `/admin/works/123/edit` |
| **SEO** | ❌ Гірше | ✅ Краще |
| **Питання параметра** | ❌ Динамічно через getQuery() | ✅ Явно в сигнатурі методу |
| **IDE автодповідь** | ❌ Не знає про параметр | ✅ Знає всі параметри |
| **Документація** | ❌ В коментарях | ✅ В сигнатурі методу |
| **REST принципи** | ❌ Слабше | ✅ Краще (нормальні URL) |

---

## Крок за кроком: Мігрування одного контролера

### 1. Оновити маршрути в `/public/index.php`

```php
// Замінити:
$router->add('/admin/works/edit', AdminWorkController::class, 'edit');
$router->add('/admin/works/delete', AdminWorkController::class, 'delete');

// На:
$router
  ->add('/admin/works/{id:\d+}/edit', AdminWorkController::class, 'edit')
  ->add('/admin/works/{id:\d+}/delete', AdminWorkController::class, 'delete');
```

### 2. Оновити методи контролера

```php
// До:
public function edit() {
  $id = (int) $this->getQuery('id');
  // ...
}

// Після:
public function edit($id) {
  $id = (int) $id;
  // ...
}
```

### 3. Оновити шаблони (факультативно)

```html
<!-- До -->
<a href="/admin/works/edit?id={{ work.id }}">Редагувати</a>

<!-- Після -->
<a href="/admin/works/{{ work.id }}/edit">Редагувати</a>
```

---

## Багато параметрів

Якщо маршрут має кілька параметрів:

```php
// Маршрут
$router->add('/admin/users/{userId:\d+}/posts/{postId:\d+}', UserController::class, 'showPost');

// Контролер - параметри передаються в порядку появи в маршруті
public function showPost($userId, $postId)
{
  $userId = (int) $userId;
  $postId = (int) $postId;
  
  $user = User::getById($userId);
  $post = Post::getById($postId);
  
  if (!$user || !$post || $post['user_id'] != $userId) {
    http_response_code(404);
    return;
  }
  
  $this->render('user/post.html.twig', ['user' => $user, 'post' => $post]);
}
```

URL: `/admin/users/42/posts/99` → `showPost(42, 99)`

---

## Зворотна сумісність

✅ **Старі маршрути продовжують працювати!**

```php
// Обидва варіанти можуть співіснувати
$router->add('/admin/works/edit', AdminWorkController::class, 'edit');  // Старий
$router->add('/admin/works/{id:\d+}/edit', AdminWorkController::class, 'editNew');  // Новий
```

Точні маршрути (без параметрів) мають пріоритет, тому можна мігрувати поступово.
