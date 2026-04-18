<?php

declare(strict_types=1);

function request_method(): string
{
    return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
}

function query_param(string $key, $default = null)
{
    return $_GET[$key] ?? $default;
}

function post_param(string $key, $default = null)
{
    return $_POST[$key] ?? $default;
}

function current_route(): string
{
    $route = query_param('route', '');

    if (!is_string($route)) {
        return '';
    }

    return trim($route, " \t\n\r\0\x0B/");
}
