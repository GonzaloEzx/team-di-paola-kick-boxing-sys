<?php

declare(strict_types=1);

require_once APP_ROOT . '/modules/planes/planes_controller.php';

function api_planes_toggle(): void
{
    require_rol(['admin', 'recepcion']);
    require_csrf();

    if (request_method() !== 'POST') {
        json_error('Metodo no permitido', 'METODO_INVALIDO', 405);
        return;
    }

    $id = (int) query_param('id', 0);
    $plan = $id > 0 ? planes_fetch_by_id($id) : null;
    if ($plan === null) {
        json_error('Plan no encontrado', 'NO_ENCONTRADO', 404);
        return;
    }

    $nuevo = ((int) $plan['activo']) === 1 ? 0 : 1;
    planes_set_activo($id, $nuevo);

    json_success(['id' => $id, 'activo' => $nuevo === 1]);
}
