<?php

declare(strict_types=1);

require_once APP_ROOT . '/modules/planes/planes_controller.php';

function api_planes_list(): void
{
    require_rol(['admin', 'recepcion']);

    $result = planes_fetch_list([
        'q' => (string) query_param('q', ''),
        'activo' => (string) query_param('activo', ''),
        'page' => (int) query_param('page', 1),
        'limit' => (int) query_param('limit', PLANES_PAGE_SIZE),
    ]);

    $rows = array_map(static function (array $p) {
        return [
            'id' => (int) $p['id'],
            'nombre' => (string) $p['nombre'],
            'descripcion' => $p['descripcion'],
            'precio' => (float) $p['precio'],
            'duracion_dias' => (int) $p['duracion_dias'],
            'tipo_acceso' => (string) $p['tipo_acceso'],
            'cantidad_clases' => $p['cantidad_clases'] !== null ? (int) $p['cantidad_clases'] : null,
            'activo' => (int) $p['activo'] === 1,
        ];
    }, $result['rows']);

    json_response([
        'success' => true,
        'data' => $rows,
        'meta' => [
            'page' => max(1, (int) query_param('page', 1)),
            'limit' => max(1, min(100, (int) query_param('limit', PLANES_PAGE_SIZE))),
            'total' => (int) $result['total'],
        ],
    ]);
}
