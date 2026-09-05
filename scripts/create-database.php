<?php

/**
 * Creates the MariaDB/MySQL database named in .env if it doesn't already
 * exist. Run via `make db-create` or directly with `php scripts/create-database.php`.
 * Plain PHP with no Laravel bootstrap so it works before `composer install`
 * has even finished wiring up the framework.
 */
$envPath = __DIR__.'/../.env';

if (! file_exists($envPath)) {
    fwrite(STDERR, ".env not found. Run `make env` (or copy .env.example to .env) first.\n");
    exit(1);
}

$env = [];
foreach (file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
    if (str_starts_with(trim($line), '#') || ! str_contains($line, '=')) {
        continue;
    }

    [$key, $value] = explode('=', $line, 2);
    $env[trim($key)] = trim($value, " \t\n\r\0\x0B\"'");
}

$host = $env['DB_HOST'] ?? '127.0.0.1';
$port = $env['DB_PORT'] ?? '3306';
$database = $env['DB_DATABASE'] ?? 'project_status_app';
$username = $env['DB_USERNAME'] ?? 'root';
$password = $env['DB_PASSWORD'] ?? '';

try {
    $pdo = new PDO("mysql:host={$host};port={$port}", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$database}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

    echo "Database `{$database}` is ready on {$host}:{$port}.\n";
} catch (PDOException $e) {
    fwrite(STDERR, "Could not create database: {$e->getMessage()}\n");
    exit(1);
}
