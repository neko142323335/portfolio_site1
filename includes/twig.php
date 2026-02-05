<?php
/**
 * Twig Template Engine Configuration
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Twig\Extension\DebugExtension;

// Set up Twig loader
$loader = new FilesystemLoader(__DIR__ . '/../templates');

// Create Twig environment
$twig = new Environment($loader, [
    'cache' => false, // Disable cache for development (enable in production)
    'debug' => DEBUG_MODE,
    'autoescape' => 'html',
    'strict_variables' => DEBUG_MODE,
]);

// Add debug extension if enabled
if (DEBUG_MODE) {
    $twig->addExtension(new DebugExtension());
}

// Add custom filters
$twig->addFilter(new \Twig\TwigFilter('sanitize', function($text) {
    return sanitize_input($text);
}));

// Переконуємось що сесія запущена
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Add global variables
$twig->addGlobal('site_name', SITE_NAME);
$twig->addGlobal('site_lang', SITE_LANG);
$twig->addGlobal('base', '/'); // MVC використовує чисті URL від кореня

// Add custom functions для динамічного доступу до сесії
$twig->addFunction(new \Twig\TwigFunction('is_admin', function() {
    return is_admin();
}));

$twig->addFunction(new \Twig\TwigFunction('is_current_page', function($page) {
    return is_current_page($page);
}));

$twig->addFunction(new \Twig\TwigFunction('user_id', function() {
    return $_SESSION['user_id'] ?? null;
}));

$twig->addFunction(new \Twig\TwigFunction('user_name', function() {
    return $_SESSION['user_name'] ?? null;
}));

return $twig;
?>
