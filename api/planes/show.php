<?php

declare(strict_types=1);

require_once APP_ROOT . '/modules/planes/planes_controller.php';

function api_planes_show(): void
{
    require_rol(['admin', 'recepcion']);

    $id = (int) query_param('id', 0);
    if ($id <= 0) {
        json_error('ID invalido', 'ID_INVALIDO', 400);
        return;
    }

    $p = planes_fetch_by_id($id);
    if ($p === null) {
        json_error('Plan no encontrado', 'NO_ENCONTRADO', 404);
        return;
    }

    json_success([
        'id' => (int) $p['id'],
        'nombre' => (string) $p['nombre'],
        'descripcion' => $p['descripcion'],
        'precio' => (float) $p['precio'],
        'duracion_dias' => (int) $p['duracion_dias'],
        'tipo_acceso' => (string) $p['tipo_acceso'],
        'cantidad_clases' => $p['cantidad_clases'] !== null ? (int) $p['cantidad_clases'] : null,
        'activo' => (int) $p['activo'] === 1,
        'created_at' => (string) $p['created_at'],
        'updated_at' => (string) $p['updated_at'],
    ]);
}
