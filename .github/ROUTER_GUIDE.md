# Посібник по Router з підтримкою динамічних параметрів

## Огляд

Оновлений `Router.php` тепер підтримує:
- ✅ Точні маршрути: `/about`
- ✅ Динамічні параметри: `/user/{id}`, `/work/{id}/edit`
- ✅ Regex параметри: `/post/{id:\d+}`, `/slug/{name:[a-z0-9-]+}`
- ✅ Кілька параметрів: `/user/{userId}/post/{postId}`

## Синтаксис маршрутів

### 1. Точні маршрути (без параметрів)
```php
$router->add('/', HomeController::class, 'index');
$router->add('/about', PageController::class, 'about');
$router->add('/contact', ContactController::class, 'index');
```

### 2. Маршрути з простими параметрами
```php
// /user/123 → id=123
$router->add('/user/{id}', UserController::class, 'show');

// /work/5/edit → id=5
$router->add('/work/{id}/edit', WorkController::class, 'edit');

// /category/tech/posts → category=tech
$router->add('/category/{slug}/posts', PostController::class, 'byCategory');
```

### 3. Маршрути з regex обмеженнями
```php
// Лише цифри
$router->add('/post/{id:\d+}', PostController::class, 'show');

// Слаги (букви, цифри, дефіси)
$router->add('/blog/{slug:[a-z0-9-]+}', BlogController::class, 'show');

// UUID формат
$router->add('/item/{uuid:[a-f0-9-]{36}}', ItemController::class, 'show');

// Будь-які символи крім слешу
$router->add('/page/{name:[^/]+}', PageController::class, 'show');
```

### 4. Мультипараметричні маршрути
```php
// /user/5/post/123
$router->add('/user/{userId}/post/{postId}', UserPostController::class, 'show');

// /categories/tech/posts/my-post
$router->add('/categories/{cat}/posts/{slug:[a-z0-9-]+}', PublicController::class, 'post');
```

## Оновлення контролерів

### До (без параметрів)
```php
class WorkController extends BaseController
{
    public function edit()
    {
        $id = $this->getQuery('id'); // Вручну з URL  
        // ... обробка
    }
}
```

### Після (з параметрами)
```php
class WorkController extends BaseController
{
    // Параметри передаються як аргументи метода
    public function edit($id)
    {
        // $id вже отримана з URL
        // ... обробка
    }
}
```

### З кількома параметрами
```php
class UserPostController extends BaseController
{
    public function show($userId, $postId)
    {
        // $userId та $postId вже отримані
        $post = Work::getById($postId);
        if ($post && $post['user_id'] == $userId) {
            $this->render('post.html.twig', ['post' => $post]);
        } else {
            http_response_code(404);
        }
    }
}
```

## Приклад міграції існуючих маршрутів

### Поточні маршрути (без параметрів)
```php
$router
  ->add('/admin/works/add', AdminWorkController::class, 'add')
  ->add('/admin/works/edit', AdminWorkController::class, 'edit')
  ->add('/admin/works/delete', AdminWorkController::class, 'delete')
  ->add('/admin/categories/edit', CategoryController::class, 'edit');
```

### Оптимізовані маршрути (з параметрами)
```php
$router
  ->add('/admin/works/add', AdminWorkController::class, 'add')
  ->add('/admin/works/{id}/edit', AdminWorkController::class, 'edit')
  ->add('/admin/works/{id}/delete', AdminWorkController::class, 'delete')
  ->add('/admin/categories/{id}/edit', CategoryController::class, 'edit');
```

### Оновлений контролер
```php
class AdminWorkController extends BaseController
{
    // Залишилось як раніше
    public function add()
    {
        // ... форма добавлення
    }

    // Тепер приймає id як параметр
    public function edit($id)
    {
        $work = Work::getById($id);
        if (!$work) {
            http_response_code(404);
            return;
        }
        // ... форма редагування
    }

    // Тепер приймає id як параметр
    public function delete($id)
    {
        Work::delete($id);
        $this->redirect('/admin/works');
    }
}
```

## Порядок пріоритету маршрутів

1. **Точні маршрути** мають найвищий пріоритет
   ```php
   $router->add('/admin', SpecialController::class, 'special'); // ← Буде переважати
   $router->add('/admin/{page}', AdminController::class, 'index');
   ```

2. **Динамічні маршрути** обробляються в порядку додавання
   ```php
   $router->add('/blog/{slug}', BlogController::class, 'show');
   $router->add('/blog/{id:\d+}', BlogController::class, 'showById');
   ```

## Внутрішня робота

### Конверсія до regex

| Маршрут | Regex параметр | Приклад URL | Параметри |
|---------|---|---|---|
| `/user/{id}` | `(?P<id>\d+)` | `/user/123` | `['id' => '123']` |
| `/post/{id:\d+}` | `(?P<id>\d+)` | `/post/456` | `['id' => '456']` |
| `/tag/{name:[a-z0-9-]+}` | `(?P<name>[a-z0-9-]+)` | `/tag/my-tag` | `['name' => 'my-tag']` |
| `/item/{uuid:[^/]+}` | `(?P<uuid>[^/]+)` | `/item/abc123` | `['uuid' => 'abc123']` |

### За замовчуванням

Якщо regex не вказаний: `{id}` → `(?P<id>\d+)` (тільки цифри)

## Тестування

```bash
# Точні маршрути
curl http://localhost/gallery          # ✓ GalleryController::index()

# Динамічні параметри
curl http://localhost/work/123/edit    # ✓ WorkController::edit(123)
curl http://localhost/user/42          # ✓ UserController::show(42)

# Regex обмеження
curl http://localhost/post/abc         # ✗ 404 (не відповідає \d+)
curl http://localhost/post/123         # ✓ PostController::show(123)

# Неправильні маршрути
curl http://localhost/unknown          # ✗ 404 - маршрут не знайдено
```

## Поточний стан документації

- **Файл:** `src/Router.php`
- **Версія:** З підтримкою regex параметрів
- **Сумісність:** Зворотна сумісна з існуючими точними маршрутами
