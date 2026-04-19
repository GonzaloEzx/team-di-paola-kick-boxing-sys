<?php

declare(strict_types=1);

function csrf_token(): string
{
    auth_start_session();

    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return (string) $_SESSION['csrf_token'];
}

function csrf_verify(?string $token): bool
{
    auth_start_session();

    $session_token = (string) ($_SESSION['csrf_token'] ?? '');
    $provided = (string) ($token ?? '');

    if ($session_token === '' || $provided === '') {
        return false;
    }

    return hash_equals($session_token, $provided);
}

function require_csrf(): void
{
    $header = (string) ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
    $post = (string) ($_POST['csrf_token'] ?? '');
    $provided = $header !== '' ? $header : $post;

    if (!csrf_verify($provided)) {
        if (request_wants_json()) {
            json_error('Token CSRF invalido', 'CSRF_INVALIDO', 403);
            exit;
        }

        http_response_code(403);
        header('Content-Type: text/plain; charset=utf-8');
        echo 'CSRF invalido.';
        exit;
    }
}
