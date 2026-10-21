<?php

function loadEnvironment(): void
{
    static $loaded = false;
    if ($loaded) {
        return;
    }

    $autoload = __DIR__ . '/../vendor/autoload.php';
    if (!is_file($autoload)) {
        throw new RuntimeException('Chưa cài dependency. Hãy chạy composer install.');
    }
    require_once $autoload;
    Dotenv\Dotenv::createImmutable(dirname(__DIR__))->safeLoad();
    $loaded = true;
}

function isManager(): bool
{
    return ($_SESSION['user_role'] ?? '') === 'manager';
}

function requireManager(): void
{
    if (!isManager()) {
        header('Location: index.php?act=login');
        exit;
    }
}
