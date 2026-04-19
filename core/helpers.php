<?php

declare(strict_types=1);

function is_local_environment(): bool
{
    return defined('ENVIRONMENT') && ENVIRONMENT === 'development';
}

function base_url(string $path = ''): string
{
    $base = defined('URL_BASE') ? URL_BASE : '';
    $path = ltrim($path, '/');

    return $path === '' ? $base : $base . '/' . $path;
}

function h($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function redirect(string $url, int $statusCode = 302): void
{
    http_response_code($statusCode);
    header('Location: ' . $url);
    exit;
}

function flash_push(string $key, array $payload): void
{
    if (function_exists('auth_start_session')) {
        auth_start_session();
    } elseif (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $_SESSION['_flash'][$key][] = $payload;
}

function flash_pop(string $key): array
{
    if (function_exists('auth_start_session')) {
        auth_start_session();
    } elseif (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (!isset($_SESSION['_flash'][$key])) {
        return [];
    }
    $items = $_SESSION['_flash'][$key];
    unset($_SESSION['_flash'][$key]);
    return $items;
}
