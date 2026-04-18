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
