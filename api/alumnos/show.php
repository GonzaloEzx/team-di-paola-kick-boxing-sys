<?php

declare(strict_types=1);

require_once APP_ROOT . '/modules/alumnos/alumnos_controller.php';

function api_alumnos_show(): void
{
    require_rol(['admin', 'recepcion']);

    $id = (int) query_param('id', 0);
    if ($id <= 0) {
        json_error('ID invalido', 'ID_INVALIDO', 400);
        return;
    }

    $alumno = alumnos_fetch_by_id($id);
    if ($alumno === null) {
        json_error('Alumno no encontrado', 'NO_ENCONTRADO', 404);
        return;
    }

    json_success([
        'id' => (int) $alumno['id'],
        'nombre' => (string) $alumno['nombre'],
        'apellido' => (string) $alumno['apellido'],
        'dni' => $alumno['dni'],
        'telefono' => $alumno['telefono'],
        'email' => $alumno['email'],
        'fecha_nacimiento' => $alumno['fecha_nacimiento'],
        'contacto_emergencia_nombre' => $alumno['contacto_emergencia_nombre'],
        'contacto_emergencia_telefono' => $alumno['contacto_emergencia_telefono'],
        'observaciones' => $alumno['observaciones'],
        'estado' => (string) $alumno['estado'],
        'created_at' => (string) $alumno['created_at'],
        'updated_at' => (string) $alumno['updated_at'],
    ]);
}
