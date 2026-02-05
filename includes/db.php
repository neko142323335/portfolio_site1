<?php
/**
 * Database connection bootstrap (MVC)
 */

require_once __DIR__ . '/../vendor/autoload.php';

use App\Database\Connection;

$db = Connection::get();
