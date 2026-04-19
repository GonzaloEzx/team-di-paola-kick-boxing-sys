<?php

declare(strict_types=1);

require_once APP_ROOT . '/modules/planes/planes_controller.php';
require_once APP_ROOT . '/api/planes/create.php';

function api_planes_update(): void
{
    require_rol(['admin', 'recepcion']);
    require_csrf();

    if (request_method() !== 'POST') {
        json_error('Metodo no permitido', 'METODO_INVALIDO', 405);
        return;
    }

    $id = (int) query_param('id', 0);
    if ($id <= 0) {
        json_error('ID invalido', 'ID_INVALIDO', 400);
        return;
    }

    $existing = planes_fetch_by_id($id);
    if ($existing === null) {
        json_error('Plan no encontrado', 'NO_ENCONTRADO', 404);
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

    planes_update($id, $validation['clean']);
    json_success(['id' => $id]);
}
