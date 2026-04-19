<?php

declare(strict_types=1);

require_once APP_ROOT . '/modules/planes/planes_controller.php';

function api_planes_create(): void
{
    require_rol(['admin', 'recepcion']);
    require_csrf();

    if (request_method() !== 'POST') {
        json_error('Metodo no permitido', 'METODO_INVALIDO', 405);
        return;
    }

    $payload = api_planes_read_json_or_post();
    $payload = array_merge(planes_blank_payload(), $payload);

    $validation = planes_validate_payload($payload);
    if (!$validation['ok']) {
        http_response_code(422);
        json_response([
            'success' => false,
            'error' => 'Datos invalidos',
            'code' => 'VALIDACION',
            'errors' => $validation['errors'],
        ]);
        return;
    }

    $id = planes_insert($validation['clean']);
    json_success(['id' => $id]);
}

function api_planes_read_json_or_post(): array
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
