<?php

declare(strict_types=1);

const PLANES_TIPOS_ACCESO = ['libre', 'cantidad_clases'];
const PLANES_PAGE_SIZE = 25;

function planes_index(): void
{
    $user = require_rol(['admin', 'recepcion']);

    $q = trim((string) query_param('q', ''));
    $activo = (string) query_param('activo', '');
    $page = max(1, (int) query_param('page', 1));

    $result = planes_fetch_list([
        'q' => $q,
        'activo' => $activo,
        'page' => $page,
        'limit' => PLANES_PAGE_SIZE,
    ]);

    render_view('planes/planes_list_view.php', [
        'user' => $user,
        'planes' => $result['rows'],
        'total' => $result['total'],
        'page' => $page,
        'limit' => PLANES_PAGE_SIZE,
        'q' => $q,
        'activo' => $activo,
        'flash' => flash_pop('planes'),
    ]);
}

function planes_new_form(): void
{
    $user = require_rol(['admin', 'recepcion']);

    render_view('planes/planes_form_view.php', [
        'user' => $user,
        'mode' => 'nuevo',
        'plan' => planes_blank_payload(),
        'errors' => [],
        'csrf_token' => csrf_token(),
    ]);
}

function planes_create_submit(): void
{
    $user = require_rol(['admin', 'recepcion']);
    require_csrf();

    $payload = planes_read_payload_from_post();
    $validation = planes_validate_payload($payload);

    if (!$validation['ok']) {
        render_view('planes/planes_form_view.php', [
            'user' => $user,
            'mode' => 'nuevo',
            'plan' => $payload,
            'errors' => $validation['errors'],
            'csrf_token' => csrf_token(),
        ]);
        return;
    }

    $id = planes_insert($validation['clean']);
    flash_push('planes', ['type' => 'success', 'message' => 'Plan creado correctamente.']);
    redirect(base_url('?route=admin/planes'));
}

function planes_edit_form(): void
{
    $user = require_rol(['admin', 'recepcion']);

    $id = (int) query_param('id', 0);
    $plan = $id > 0 ? planes_fetch_by_id($id) : null;

    if ($plan === null) {
        http_response_code(404);
        header('Content-Type: text/plain; charset=utf-8');
        echo 'Plan no encontrado.';
        return;
    }

    render_view('planes/planes_form_view.php', [
        'user' => $user,
        'mode' => 'editar',
        'plan' => $plan,
        'errors' => [],
        'csrf_token' => csrf_token(),
    ]);
}

function planes_update_submit(): void
{
    $user = require_rol(['admin', 'recepcion']);
    require_csrf();

    $id = (int) query_param('id', 0);
    $existing = $id > 0 ? planes_fetch_by_id($id) : null;
    if ($existing === null) {
        http_response_code(404);
        header('Content-Type: text/plain; charset=utf-8');
        echo 'Plan no encontrado.';
        return;
    }

    $payload = planes_read_payload_from_post();
    $payload['id'] = $id;

    $validation = planes_validate_payload($payload);
    if (!$validation['ok']) {
        render_view('planes/planes_form_view.php', [
            'user' => $user,
            'mode' => 'editar',
            'plan' => $payload,
            'errors' => $validation['errors'],
            'csrf_token' => csrf_token(),
        ]);
        return;
    }

    planes_update($id, $validation['clean']);
    flash_push('planes', ['type' => 'success', 'message' => 'Plan actualizado.']);
    redirect(base_url('?route=admin/planes'));
}

function planes_toggle_submit(): void
{
    require_rol(['admin', 'recepcion']);
    require_csrf();

    $id = (int) query_param('id', 0);
    $plan = $id > 0 ? planes_fetch_by_id($id) : null;
    if ($plan === null) {
        http_response_code(404);
        header('Content-Type: text/plain; charset=utf-8');
        echo 'Plan no encontrado.';
        return;
    }

    $nuevo = ((int) $plan['activo']) === 1 ? 0 : 1;
    planes_set_activo($id, $nuevo);

    flash_push('planes', [
        'type' => 'success',
        'message' => $nuevo === 1 ? 'Plan activado.' : 'Plan desactivado.',
    ]);
    redirect(base_url('?route=admin/planes'));
}

// --------------------------------------------------------------------
// Data layer
// --------------------------------------------------------------------

function planes_blank_payload(): array
{
    return [
        'id' => null,
        'nombre' => '',
        'descripcion' => '',
        'precio' => '',
        'duracion_dias' => '',
        'tipo_acceso' => 'libre',
        'cantidad_clases' => '',
        'activo' => 1,
    ];
}

function planes_read_payload_from_post(): array
{
    return [
        'id' => null,
        'nombre' => trim((string) post_param('nombre', '')),
        'descripcion' => trim((string) post_param('descripcion', '')),
        'precio' => trim((string) post_param('precio', '')),
        'duracion_dias' => trim((string) post_param('duracion_dias', '')),
        'tipo_acceso' => trim((string) post_param('tipo_acceso', 'libre')),
        'cantidad_clases' => trim((string) post_param('cantidad_clases', '')),
        'activo' => 1,
    ];
}

function planes_validate_payload(array $data): array
{
    $errors = [];

    $nombre = trim((string) ($data['nombre'] ?? ''));
    $descripcion = (string) ($data['descripcion'] ?? '');
    $precio_str = (string) ($data['precio'] ?? '');
    $duracion_str = (string) ($data['duracion_dias'] ?? '');
    $tipo = (string) ($data['tipo_acceso'] ?? '');
    $clases_str = (string) ($data['cantidad_clases'] ?? '');

    if (mb_strlen($nombre) < 2) {
        $errors['nombre'] = 'Nombre requerido (min 2).';
    } elseif (mb_strlen($nombre) > 120) {
        $errors['nombre'] = 'Nombre demasiado largo (max 120).';
    }

    if ($precio_str === '' || !is_numeric($precio_str) || (float) $precio_str < 0) {
        $errors['precio'] = 'Precio requerido (>= 0).';
    }

    if ($duracion_str === '' || !ctype_digit($duracion_str)) {
        $errors['duracion_dias'] = 'Duracion requerida (dias, entero).';
    } else {
        $dur = (int) $duracion_str;
        if ($dur < 1 || $dur > 3650) {
            $errors['duracion_dias'] = 'Duracion fuera de rango (1 a 3650).';
        }
    }

    if (!in_array($tipo, PLANES_TIPOS_ACCESO, true)) {
        $errors['tipo_acceso'] = 'Tipo invalido.';
    }

    $cantidad_clases = null;
    if ($tipo === 'cantidad_clases') {
        if ($clases_str === '' || !ctype_digit($clases_str)) {
            $errors['cantidad_clases'] = 'Cantidad de clases requerida (entero > 0).';
        } else {
            $cc = (int) $clases_str;
            if ($cc < 1) {
                $errors['cantidad_clases'] = 'Cantidad debe ser mayor a cero.';
            } else {
                $cantidad_clases = $cc;
            }
        }
    }

    if (!empty($errors)) {
        return ['ok' => false, 'errors' => $errors];
    }

    return [
        'ok' => true,
        'errors' => [],
        'clean' => [
            'nombre' => $nombre,
            'descripcion' => $descripcion !== '' ? $descripcion : null,
            'precio' => number_format((float) $precio_str, 2, '.', ''),
            'duracion_dias' => (int) $duracion_str,
            'tipo_acceso' => $tipo,
            'cantidad_clases' => $cantidad_clases,
        ],
    ];
}

function planes_fetch_list(array $filters): array
{
    $db = getDB();

    $q = isset($filters['q']) ? trim((string) $filters['q']) : '';
    $activo = isset($filters['activo']) ? (string) $filters['activo'] : '';
    $page = isset($filters['page']) ? max(1, (int) $filters['page']) : 1;
    $limit = isset($filters['limit']) ? max(1, min(100, (int) $filters['limit'])) : PLANES_PAGE_SIZE;
    $offset = ($page - 1) * $limit;

    $where = [];
    $params = [];

    if ($q !== '') {
        $where[] = '(nombre LIKE :q_nombre OR descripcion LIKE :q_desc)';
        $params[':q_nombre'] = '%' . $q . '%';
        $params[':q_desc'] = '%' . $q . '%';
    }

    if ($activo === '1' || $activo === '0') {
        $where[] = 'activo = :activo';
        $params[':activo'] = (int) $activo;
    }

    $where_sql = empty($where) ? '' : 'WHERE ' . implode(' AND ', $where);

    $count_stmt = $db->prepare('SELECT COUNT(*) FROM planes ' . $where_sql);
    $count_stmt->execute($params);
    $total = (int) $count_stmt->fetchColumn();

    $sql = 'SELECT * FROM planes ' . $where_sql . '
            ORDER BY activo DESC, nombre ASC
            LIMIT ' . (int) $limit . ' OFFSET ' . (int) $offset;
    $stmt = $db->prepare($sql);
    $stmt->execute($params);

    return ['rows' => $stmt->fetchAll(), 'total' => $total];
}

function planes_fetch_by_id(int $id): ?array
{
    $db = getDB();
    $stmt = $db->prepare('SELECT * FROM planes WHERE id = :id LIMIT 1');
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch();

    return $row === false ? null : $row;
}

function planes_fetch_activos(): array
{
    $db = getDB();
    $stmt = $db->query('SELECT * FROM planes WHERE activo = 1 ORDER BY nombre ASC');
    return $stmt->fetchAll();
}

function planes_insert(array $data): int
{
    $db = getDB();
    $stmt = $db->prepare(
        'INSERT INTO planes
            (nombre, descripcion, precio, duracion_dias, tipo_acceso, cantidad_clases, activo, created_at, updated_at)
         VALUES
            (:nombre, :descripcion, :precio, :duracion_dias, :tipo_acceso, :cantidad_clases, 1, NOW(), NOW())'
    );
    $stmt->execute([
        ':nombre' => $data['nombre'],
        ':descripcion' => $data['descripcion'],
        ':precio' => $data['precio'],
        ':duracion_dias' => $data['duracion_dias'],
        ':tipo_acceso' => $data['tipo_acceso'],
        ':cantidad_clases' => $data['cantidad_clases'],
    ]);

    return (int) $db->lastInsertId();
}

function planes_update(int $id, array $data): void
{
    $db = getDB();
    $stmt = $db->prepare(
        'UPDATE planes SET
            nombre = :nombre,
            descripcion = :descripcion,
            precio = :precio,
            duracion_dias = :duracion_dias,
            tipo_acceso = :tipo_acceso,
            cantidad_clases = :cantidad_clases,
            updated_at = NOW()
         WHERE id = :id'
    );
    $stmt->execute([
        ':nombre' => $data['nombre'],
        ':descripcion' => $data['descripcion'],
        ':precio' => $data['precio'],
        ':duracion_dias' => $data['duracion_dias'],
        ':tipo_acceso' => $data['tipo_acceso'],
        ':cantidad_clases' => $data['cantidad_clases'],
        ':id' => $id,
    ]);
}

function planes_set_activo(int $id, int $activo): void
{
    $db = getDB();
    $stmt = $db->prepare('UPDATE planes SET activo = :a, updated_at = NOW() WHERE id = :id');
    $stmt->execute([':a' => $activo, ':id' => $id]);
}
