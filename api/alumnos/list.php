<?php

declare(strict_types=1);

require_once APP_ROOT . '/modules/alumnos/alumnos_controller.php';

function api_alumnos_list(): void
{
    require_rol(['admin', 'recepcion']);

    $result = alumnos_fetch_list([
        'q' => (string) query_param('q', ''),
        'estado' => (string) query_param('estado', ''),
        'page' => (int) query_param('page', 1),
        'limit' => (int) query_param('limit', ALUMNOS_PAGE_SIZE),
    ]);

    $rows = array_map(static function (array $a) {
        return [
            'id' => (int) $a['id'],
            'nombre' => (string) $a['nombre'],
            'apellido' => (string) $a['apellido'],
            'dni' => $a['dni'] !== null ? (string) $a['dni'] : null,
            'telefono' => $a['telefono'] !== null ? (string) $a['telefono'] : null,
            'email' => $a['email'] !== null ? (string) $a['email'] : null,
            'estado' => (string) $a['estado'],
            'fecha_nacimiento' => $a['fecha_nacimiento'] !== null ? (string) $a['fecha_nacimiento'] : null,
        ];
    }, $result['rows']);

    $limit = max(1, min(100, (int) query_param('limit', ALUMNOS_PAGE_SIZE)));
    $page = max(1, (int) query_param('page', 1));

    json_response([
        'success' => true,
        'data' => $rows,
        'meta' => [
            'page' => $page,
            'limit' => $limit,
            'total' => (int) $result['total'],
        ],
    ]);
}
