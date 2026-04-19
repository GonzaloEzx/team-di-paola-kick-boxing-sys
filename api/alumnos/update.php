<?php

declare(strict_types=1);

require_once APP_ROOT . '/modules/alumnos/alumnos_controller.php';
require_once APP_ROOT . '/api/alumnos/create.php';

function api_alumnos_update(): void
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

    $existing = alumnos_fetch_by_id($id);
    if ($existing === null) {
        json_error('Alumno no encontrado', 'NO_ENCONTRADO', 404);
        return;
    }

    $payload = api_alumnos_read_json_or_post();
    $payload = array_merge(alumnos_blank_payload(), $payload);

    $validation = alumnos_validate_payload($payload, $id);
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

    $result = alumnos_update($id, $validation['clean']);
    if (!$result['ok']) {
        json_error($result['message'] ?? 'No se pudo actualizar', 'DNI_DUPLICADO', 409);
        return;
    }

    json_success(['id' => $id]);
}
