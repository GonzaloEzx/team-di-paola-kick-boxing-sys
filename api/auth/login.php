<?php

declare(strict_types=1);

function api_auth_login(): void
{
    if (request_method() !== 'POST') {
        json_error('Metodo no permitido', 'METODO_INVALIDO', 405);
        return;
    }

    $raw = file_get_contents('php://input');
    $data = is_string($raw) && $raw !== '' ? json_decode($raw, true) : null;

    if (!is_array($data)) {
        $data = $_POST;
    }

    $email = (string) ($data['email'] ?? '');
    $password = (string) ($data['password'] ?? '');

    $result = auth_attempt($email, $password);

    if (!$result['ok']) {
        $status = $result['code'] === 'USUARIO_INACTIVO' ? 403 : 401;
        $message = $result['code'] === 'USUARIO_INACTIVO'
            ? 'Usuario inactivo'
            : 'Credenciales invalidas';
        json_error($message, $result['code'], $status);
        return;
    }

    json_success([
        'usuario_id' => $result['usuario_id'],
        'nombre' => $result['nombre'],
        'apellido' => $result['apellido'],
        'roles' => $result['roles'],
        'csrf_token' => csrf_token(),
    ]);
}
