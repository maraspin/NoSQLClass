<?php

ini_set('display_errors', 1);

$dsn = 'pgsql:host=localhost;dbname=ecommerce';
$username = 'postgres';
$password = 'zf2';

function __autoload_elastica ($class) {
    $path = str_replace('\\', '/', $class);
    if (file_exists(__DIR__ . '/Elastica/lib/' . $path . '.php')) {
        require_once(__DIR__ . '/Elastica/lib/' . $path . '.php');
    }
}
spl_autoload_register('__autoload_elastica');
