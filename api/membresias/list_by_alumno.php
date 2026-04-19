<?php

declare(strict_types=1);

require_once APP_ROOT . '/modules/membresias/membresias_controller.php';

function api_membresias_list_by_alumno(): void
{
    require_rol(['admin', 'recepcion']);

    $alumno_id = (int) query_param('alumno_id', 0);
    if ($alumno_id <= 0) {
        json_error('alumno_id invalido', 'ALUMNO_ID_INVALIDO', 400);
        return;
    }

    $alumno = alumnos_fetch_by_id($alumno_id);
    if ($alumno === null) {
        json_error('Alumno no encontrado', 'NO_ENCONTRADO', 404);
        return;
    }

    $membresias = membresias_fetch_by_alumno($alumno_id);
    $actual = membresias_fetch_actual_viva($alumno_id);

    json_success([
        'alumno_id' => $alumno_id,
        'actual' => $actual,
        'historial' => $membresias,
    ]);
}
