<?php

declare(strict_types=1);

const ALUMNOS_ESTADOS = ['activo', 'inactivo', 'suspendido'];
const ALUMNOS_PAGE_SIZE = 20;

function alumnos_index(): void
{
    $user = require_rol(['admin', 'recepcion']);

    $q = trim((string) query_param('q', ''));
    $estado = (string) query_param('estado', '');
    $page = max(1, (int) query_param('page', 1));

    if ($estado !== '' && !in_array($estado, ALUMNOS_ESTADOS, true)) {
        $estado = '';
    }

    $result = alumnos_fetch_list([
        'q' => $q,
        'estado' => $estado,
        'page' => $page,
        'limit' => ALUMNOS_PAGE_SIZE,
    ]);

    render_view('alumnos/alumnos_list_view.php', [
        'user' => $user,
        'alumnos' => $result['rows'],
        'total' => $result['total'],
        'page' => $page,
        'limit' => ALUMNOS_PAGE_SIZE,
        'q' => $q,
        'estado' => $estado,
        'flash' => flash_pop('alumnos'),
    ]);
}

function alumnos_new_form(): void
{
    $user = require_rol(['admin', 'recepcion']);

    render_view('alumnos/alumnos_form_view.php', [
        'user' => $user,
        'mode' => 'nuevo',
        'alumno' => alumnos_blank_payload(),
        'errors' => [],
        'csrf_token' => csrf_token(),
    ]);
}

function alumnos_create_submit(): void
{
    $user = require_rol(['admin', 'recepcion']);
    require_csrf();

    $payload = alumnos_read_payload_from_post();
    $validation = alumnos_validate_payload($payload, null);

    if (!$validation['ok']) {
        render_view('alumnos/alumnos_form_view.php', [
            'user' => $user,
            'mode' => 'nuevo',
            'alumno' => $payload,
            'errors' => $validation['errors'],
            'csrf_token' => csrf_token(),
        ]);
        return;
    }

    $result = alumnos_insert($validation['clean']);

    if (!$result['ok']) {
        render_view('alumnos/alumnos_form_view.php', [
            'user' => $user,
            'mode' => 'nuevo',
            'alumno' => $payload,
            'errors' => [$result['field'] => $result['message']],
            'csrf_token' => csrf_token(),
        ]);
        return;
    }

    flash_push('alumnos', ['type' => 'success', 'message' => 'Alumno creado correctamente.']);
    redirect(base_url('?route=admin/alumnos/ver&id=' . (int) $result['id']));
}

function alumnos_edit_form(): void
{
    $user = require_rol(['admin', 'recepcion']);

    $id = (int) query_param('id', 0);
    $alumno = $id > 0 ? alumnos_fetch_by_id($id) : null;

    if ($alumno === null) {
        http_response_code(404);
        header('Content-Type: text/plain; charset=utf-8');
        echo 'Alumno no encontrado.';
        return;
    }

    render_view('alumnos/alumnos_form_view.php', [
        'user' => $user,
        'mode' => 'editar',
        'alumno' => $alumno,
        'errors' => [],
        'csrf_token' => csrf_token(),
    ]);
}

function alumnos_update_submit(): void
{
    $user = require_rol(['admin', 'recepcion']);
    require_csrf();

    $id = (int) query_param('id', 0);
    $existing = $id > 0 ? alumnos_fetch_by_id($id) : null;

    if ($existing === null) {
        http_response_code(404);
        header('Content-Type: text/plain; charset=utf-8');
        echo 'Alumno no encontrado.';
        return;
    }

    $payload = alumnos_read_payload_from_post();
    $payload['id'] = $id;

    $validation = alumnos_validate_payload($payload, $id);

    if (!$validation['ok']) {
        render_view('alumnos/alumnos_form_view.php', [
            'user' => $user,
            'mode' => 'editar',
            'alumno' => $payload,
            'errors' => $validation['errors'],
            'csrf_token' => csrf_token(),
        ]);
        return;
    }

    $result = alumnos_update($id, $validation['clean']);

    if (!$result['ok']) {
        render_view('alumnos/alumnos_form_view.php', [
            'user' => $user,
            'mode' => 'editar',
            'alumno' => $payload,
            'errors' => [$result['field'] => $result['message']],
            'csrf_token' => csrf_token(),
        ]);
        return;
    }

    flash_push('alumnos', ['type' => 'success', 'message' => 'Cambios guardados.']);
    redirect(base_url('?route=admin/alumnos/ver&id=' . $id));
}

function alumnos_show(): void
{
    $user = require_rol(['admin', 'recepcion']);

    $id = (int) query_param('id', 0);
    $alumno = $id > 0 ? alumnos_fetch_by_id($id) : null;

    if ($alumno === null) {
        http_response_code(404);
        header('Content-Type: text/plain; charset=utf-8');
        echo 'Alumno no encontrado.';
        return;
    }

    render_view('alumnos/alumnos_detail_view.php', [
        'user' => $user,
        'alumno' => $alumno,
        'csrf_token' => csrf_token(),
        'flash' => flash_pop('alumnos'),
    ]);
}

function alumnos_change_state_submit(): void
{
    require_rol(['admin', 'recepcion']);
    require_csrf();

    $id = (int) query_param('id', 0);
    $estado = (string) post_param('estado', '');

    if ($id <= 0 || !in_array($estado, ALUMNOS_ESTADOS, true)) {
        http_response_code(400);
        header('Content-Type: text/plain; charset=utf-8');
        echo 'Parametros invalidos.';
        return;
    }

    $ok = alumnos_update_state($id, $estado);

    if (!$ok) {
        http_response_code(404);
        header('Content-Type: text/plain; charset=utf-8');
        echo 'Alumno no encontrado.';
        return;
    }

    flash_push('alumnos', ['type' => 'success', 'message' => 'Estado actualizado a ' . $estado . '.']);
    redirect(base_url('?route=admin/alumnos/ver&id=' . $id));
}

// --------------------------------------------------------------------
// Data layer
// --------------------------------------------------------------------

function alumnos_blank_payload(): array
{
    return [
        'id' => null,
        'nombre' => '',
        'apellido' => '',
        'dni' => '',
        'telefono' => '',
        'email' => '',
        'fecha_nacimiento' => '',
        'contacto_emergencia_nombre' => '',
        'contacto_emergencia_telefono' => '',
        'observaciones' => '',
        'estado' => 'activo',
    ];
}

function alumnos_read_payload_from_post(): array
{
    return [
        'id' => null,
        'nombre' => trim((string) post_param('nombre', '')),
        'apellido' => trim((string) post_param('apellido', '')),
        'dni' => trim((string) post_param('dni', '')),
        'telefono' => trim((string) post_param('telefono', '')),
        'email' => trim((string) post_param('email', '')),
        'fecha_nacimiento' => trim((string) post_param('fecha_nacimiento', '')),
        'contacto_emergencia_nombre' => trim((string) post_param('contacto_emergencia_nombre', '')),
        'contacto_emergencia_telefono' => trim((string) post_param('contacto_emergencia_telefono', '')),
        'observaciones' => trim((string) post_param('observaciones', '')),
        'estado' => 'activo',
    ];
}

function alumnos_validate_payload(array $data, ?int $ignore_id): array
{
    $errors = [];

    $nombre = trim((string) ($data['nombre'] ?? ''));
    $apellido = trim((string) ($data['apellido'] ?? ''));
    $dni = trim((string) ($data['dni'] ?? ''));
    $telefono = trim((string) ($data['telefono'] ?? ''));
    $email = trim((string) ($data['email'] ?? ''));
    $fecha_nacimiento = trim((string) ($data['fecha_nacimiento'] ?? ''));
    $cen = trim((string) ($data['contacto_emergencia_nombre'] ?? ''));
    $cet = trim((string) ($data['contacto_emergencia_telefono'] ?? ''));
    $observaciones = (string) ($data['observaciones'] ?? '');

    if (mb_strlen($nombre) < 2) {
        $errors['nombre'] = 'Nombre requerido (minimo 2 caracteres).';
    } elseif (mb_strlen($nombre) > 100) {
        $errors['nombre'] = 'Nombre demasiado largo (max 100).';
    }

    if (mb_strlen($apellido) < 2) {
        $errors['apellido'] = 'Apellido requerido (minimo 2 caracteres).';
    } elseif (mb_strlen($apellido) > 100) {
        $errors['apellido'] = 'Apellido demasiado largo (max 100).';
    }

    if ($dni !== '' && mb_strlen($dni) > 30) {
        $errors['dni'] = 'DNI demasiado largo (max 30).';
    }

    if ($telefono !== '' && mb_strlen($telefono) > 30) {
        $errors['telefono'] = 'Telefono demasiado largo (max 30).';
    }

    if ($email !== '') {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Email invalido.';
        } elseif (mb_strlen($email) > 150) {
            $errors['email'] = 'Email demasiado largo (max 150).';
        }
    }

    if ($fecha_nacimiento !== '') {
        $ts = strtotime($fecha_nacimiento);
        if ($ts === false) {
            $errors['fecha_nacimiento'] = 'Fecha invalida.';
        } elseif ($ts > time()) {
            $errors['fecha_nacimiento'] = 'La fecha no puede ser futura.';
        }
    }

    if ($cen !== '' && mb_strlen($cen) > 150) {
        $errors['contacto_emergencia_nombre'] = 'Nombre demasiado largo (max 150).';
    }

    if ($cet !== '' && mb_strlen($cet) > 30) {
        $errors['contacto_emergencia_telefono'] = 'Telefono demasiado largo (max 30).';
    }

    if ($dni !== '' && alumnos_dni_exists($dni, $ignore_id)) {
        $errors['dni'] = 'Ya existe un alumno con ese DNI.';
    }

    if (!empty($errors)) {
        return ['ok' => false, 'errors' => $errors];
    }

    return [
        'ok' => true,
        'errors' => [],
        'clean' => [
            'nombre' => $nombre,
            'apellido' => $apellido,
            'dni' => $dni !== '' ? $dni : null,
            'telefono' => $telefono !== '' ? $telefono : null,
            'email' => $email !== '' ? $email : null,
            'fecha_nacimiento' => $fecha_nacimiento !== '' ? $fecha_nacimiento : null,
            'contacto_emergencia_nombre' => $cen !== '' ? $cen : null,
            'contacto_emergencia_telefono' => $cet !== '' ? $cet : null,
            'observaciones' => $observaciones !== '' ? $observaciones : null,
        ],
    ];
}

function alumnos_dni_exists(string $dni, ?int $ignore_id): bool
{
    $db = getDB();
    $sql = 'SELECT id FROM alumnos WHERE dni = :dni';
    $params = [':dni' => $dni];

    if ($ignore_id !== null) {
        $sql .= ' AND id <> :id';
        $params[':id'] = $ignore_id;
    }

    $sql .= ' LIMIT 1';

    $stmt = $db->prepare($sql);
    $stmt->execute($params);

    return $stmt->fetch() !== false;
}

function alumnos_fetch_list(array $filters): array
{
    $db = getDB();

    $q = isset($filters['q']) ? trim((string) $filters['q']) : '';
    $estado = isset($filters['estado']) ? (string) $filters['estado'] : '';
    $page = isset($filters['page']) ? max(1, (int) $filters['page']) : 1;
    $limit = isset($filters['limit']) ? max(1, min(100, (int) $filters['limit'])) : ALUMNOS_PAGE_SIZE;
    $offset = ($page - 1) * $limit;

    $where = [];
    $params = [];

    if ($q !== '') {
        $like = '%' . $q . '%';
        $where[] = '(nombre LIKE :q_nombre OR apellido LIKE :q_apellido OR dni LIKE :q_dni OR email LIKE :q_email OR telefono LIKE :q_telefono)';
        $params[':q_nombre'] = $like;
        $params[':q_apellido'] = $like;
        $params[':q_dni'] = $like;
        $params[':q_email'] = $like;
        $params[':q_telefono'] = $like;
    }

    if ($estado !== '' && in_array($estado, ALUMNOS_ESTADOS, true)) {
        $where[] = 'estado = :estado';
        $params[':estado'] = $estado;
    }

    $where_sql = empty($where) ? '' : 'WHERE ' . implode(' AND ', $where);

    $count_stmt = $db->prepare('SELECT COUNT(*) FROM alumnos ' . $where_sql);
    $count_stmt->execute($params);
    $total = (int) $count_stmt->fetchColumn();

    $list_sql = 'SELECT id, nombre, apellido, dni, telefono, email, estado, fecha_nacimiento, created_at
                 FROM alumnos ' . $where_sql . '
                 ORDER BY apellido ASC, nombre ASC
                 LIMIT ' . (int) $limit . ' OFFSET ' . (int) $offset;
    $stmt = $db->prepare($list_sql);
    $stmt->execute($params);

    return ['rows' => $stmt->fetchAll(), 'total' => $total];
}

function alumnos_fetch_by_id(int $id): ?array
{
    $db = getDB();
    $stmt = $db->prepare('SELECT * FROM alumnos WHERE id = :id LIMIT 1');
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch();

    if ($row === false) {
        return null;
    }

    return $row;
}

function alumnos_insert(array $data): array
{
    try {
        $db = getDB();
        $stmt = $db->prepare(
            'INSERT INTO alumnos
                (nombre, apellido, dni, telefono, email, fecha_nacimiento,
                 contacto_emergencia_nombre, contacto_emergencia_telefono,
                 observaciones, estado, created_at, updated_at)
             VALUES
                (:nombre, :apellido, :dni, :telefono, :email, :fecha_nacimiento,
                 :cen, :cet, :observaciones, :estado, NOW(), NOW())'
        );

        $stmt->execute([
            ':nombre' => $data['nombre'],
            ':apellido' => $data['apellido'],
            ':dni' => $data['dni'],
            ':telefono' => $data['telefono'],
            ':email' => $data['email'],
            ':fecha_nacimiento' => $data['fecha_nacimiento'],
            ':cen' => $data['contacto_emergencia_nombre'],
            ':cet' => $data['contacto_emergencia_telefono'],
            ':observaciones' => $data['observaciones'],
            ':estado' => 'activo',
        ]);

        return ['ok' => true, 'id' => (int) $db->lastInsertId()];
    } catch (PDOException $e) {
        if ((int) $e->getCode() === 23000) {
            return ['ok' => false, 'field' => 'dni', 'message' => 'Ya existe un alumno con ese DNI.'];
        }
        throw $e;
    }
}

function alumnos_update(int $id, array $data): array
{
    try {
        $db = getDB();
        $stmt = $db->prepare(
            'UPDATE alumnos SET
                nombre = :nombre,
                apellido = :apellido,
                dni = :dni,
                telefono = :telefono,
                email = :email,
                fecha_nacimiento = :fecha_nacimiento,
                contacto_emergencia_nombre = :cen,
                contacto_emergencia_telefono = :cet,
                observaciones = :observaciones,
                updated_at = NOW()
             WHERE id = :id'
        );

        $stmt->execute([
            ':nombre' => $data['nombre'],
            ':apellido' => $data['apellido'],
            ':dni' => $data['dni'],
            ':telefono' => $data['telefono'],
            ':email' => $data['email'],
            ':fecha_nacimiento' => $data['fecha_nacimiento'],
            ':cen' => $data['contacto_emergencia_nombre'],
            ':cet' => $data['contacto_emergencia_telefono'],
            ':observaciones' => $data['observaciones'],
            ':id' => $id,
        ]);

        return ['ok' => true];
    } catch (PDOException $e) {
        if ((int) $e->getCode() === 23000) {
            return ['ok' => false, 'field' => 'dni', 'message' => 'Ya existe un alumno con ese DNI.'];
        }
        throw $e;
    }
}

function alumnos_update_state(int $id, string $estado): bool
{
    if (!in_array($estado, ALUMNOS_ESTADOS, true)) {
        return false;
    }

    $db = getDB();
    $stmt = $db->prepare('UPDATE alumnos SET estado = :estado, updated_at = NOW() WHERE id = :id');
    $stmt->execute([':estado' => $estado, ':id' => $id]);

    return $stmt->rowCount() > 0;
}

// --------------------------------------------------------------------
// Flash (session-based one-shot messages)
// --------------------------------------------------------------------

function flash_push(string $key, array $payload): void
{
    auth_start_session();
    $_SESSION['_flash'][$key][] = $payload;
}

function flash_pop(string $key): array
{
    auth_start_session();
    if (!isset($_SESSION['_flash'][$key])) {
        return [];
    }
    $items = $_SESSION['_flash'][$key];
    unset($_SESSION['_flash'][$key]);
    return $items;
}
