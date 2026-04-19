<?php

declare(strict_types=1);

require_once APP_ROOT . '/modules/alumnos/alumnos_controller.php';
require_once APP_ROOT . '/api/alumnos/create.php';

function api_alumnos_change_state(): void
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

    $payload = api_alumnos_read_json_or_post();
    $estado = isset($payload['estado']) ? (string) $payload['estado'] : '';

    if (!in_array($estado, ALUMNOS_ESTADOS, true)) {
        json_error('Estado invalido', 'ESTADO_INVALIDO', 422);
        return;
    }

    if (!alumnos_update_state($id, $estado)) {
        json_error('Alumno no encontrado', 'NO_ENCONTRADO', 404);
        return;
    }

    json_success(['id' => $id, 'estado' => $estado]);
}
