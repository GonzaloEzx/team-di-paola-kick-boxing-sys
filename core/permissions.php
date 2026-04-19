<?php

declare(strict_types=1);

function require_login(): array
{
    $user = auth_current_user();

    if ($user === null) {
        if (request_wants_json()) {
            json_error('Requiere autenticacion', 'NO_AUTH', 401);
            exit;
        }

        redirect(base_url('?route=auth/login'));
    }

    return $user;
}

function require_rol($codigos): array
{
    $user = require_login();

    $codigos = is_array($codigos) ? $codigos : [$codigos];

    foreach ($codigos as $codigo) {
        if (user_has_rol((string) $codigo, $user)) {
            return $user;
        }
    }

    if (request_wants_json()) {
        json_error('Permisos insuficientes', 'SIN_PERMISO', 403);
        exit;
    }

    http_response_code(403);
    header('Content-Type: text/plain; charset=utf-8');
    echo 'Permisos insuficientes.';
    exit;
}

function user_has_rol(string $codigo, ?array $user = null): bool
{
    $user = $user ?? auth_current_user();

    if ($user === null) {
        return false;
    }

    return in_array($codigo, $user['roles'], true);
}

function request_wants_json(): bool
{
    $route = current_route();

    if (str_starts_with($route, 'api/')) {
        return true;
    }

    $accept = (string) ($_SERVER['HTTP_ACCEPT'] ?? '');
    return str_contains($accept, 'application/json');
}
