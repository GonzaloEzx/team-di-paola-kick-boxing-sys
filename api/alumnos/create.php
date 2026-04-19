<?php

declare(strict_types=1);

require_once APP_ROOT . '/modules/alumnos/alumnos_controller.php';

function api_alumnos_create(): void
{
    require_rol(['admin', 'recepcion']);
    require_csrf();

    if (request_method() !== 'POST') {
        json_error('Metodo no permitido', 'METODO_INVALIDO', 405);
        return;
    }

    $payload = api_alumnos_read_json_or_post();
    $payload = array_merge(alumnos_blank_payload(), $payload);

    $validation = alumnos_validate_payload($payload, null);
    if (!$validation['ok']) {
        $first = array_key_first($validation['errors']);
        $code = $first === 'dni' ? 'DNI_DUPLICADO' : 'VALIDACION';
        $status = $first === 'dni' ? 409 : 422;
        http_response_code($status);
        json_response([
            'success' => false,
            'error' => 'Datos invalidos',
            'code' => $code,
            'errors' => $validation['errors'],
        ]);
        return;
    }

    $result = alumnos_insert($validation['clean']);
    if (!$result['ok']) {
        json_error($result['message'] ?? 'No se pudo crear', 'DNI_DUPLICADO', 409);
        return;
    }

    json_success(['id' => (int) $result['id']]);
}

function api_alumnos_read_json_or_post(): array
{
    $raw = file_get_contents('php://input');
    if (is_string($raw) && $raw !== '') {
        $decoded = json_decode($raw, true);
        if (is_array($decoded)) {
            $out = [];
            foreach ($decoded as $k => $v) {
                $out[(string) $k] = is_scalar($v) ? trim((string) $v) : '';
            }
            return $out;
        }
    }

    $out = [];
    foreach ($_POST as $k => $v) {
        $out[(string) $k] = is_scalar($v) ? trim((string) $v) : '';
    }
    return $out;
}
