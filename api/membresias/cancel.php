<?php

declare(strict_types=1);

require_once APP_ROOT . '/modules/membresias/membresias_controller.php';

function api_membresias_cancel(): void
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

    $m = membresias_fetch_by_id($id);
    if ($m === null) {
        json_error('Membresia no encontrada', 'NO_ENCONTRADO', 404);
        return;
    }

    $result = membresias_cancel($id);
    if (!$result['ok']) {
        json_error((string) ($result['message'] ?? 'No se pudo cancelar.'), 'CANCELACION_FALLIDA', 409);
        return;
    }

    json_success(['id' => $id, 'estado' => 'cancelada']);
}
