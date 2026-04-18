<?php

declare(strict_types=1);

function json_success(array $data = []): void
{
    json_response([
        'success' => true,
        'data' => $data,
    ]);
}

function json_error(string $message, string $code = 'ERROR', int $statusCode = 400): void
{
    http_response_code($statusCode);

    json_response([
        'success' => false,
        'error' => $message,
        'code' => $code,
    ]);
}

function json_response(array $payload): void
{
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}
