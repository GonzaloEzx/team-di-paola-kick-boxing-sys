<?php

declare(strict_types=1);

require_once APP_ROOT . '/modules/alumnos/alumnos_controller.php';
require_once APP_ROOT . '/modules/planes/planes_controller.php';

const MEMBRESIAS_ESTADOS_VIVAS = ['pendiente', 'activa', 'suspendida'];
const MEMBRESIAS_ESTADOS_FINALES = ['vencida', 'cancelada'];
const MEMBRESIAS_ESTADOS_VALIDOS = ['pendiente', 'activa', 'suspendida', 'vencida', 'cancelada'];

function membresias_new_form(): void
{
    $user = require_rol(['admin', 'recepcion']);

    $alumno_id = (int) query_param('alumno_id', 0);
    $alumno = $alumno_id > 0 ? alumnos_fetch_by_id($alumno_id) : null;
    if ($alumno === null) {
        http_response_code(404);
        header('Content-Type: text/plain; charset=utf-8');
        echo 'Alumno no encontrado.';
        return;
    }

    $actual = membresias_fetch_actual_viva($alumno_id);
    if ($actual !== null) {
        flash_push('alumnos', [
            'type' => 'error',
            'message' => 'Este alumno ya tiene una membresia viva. Cancelala antes de crear otra.',
        ]);
        redirect(base_url('?route=admin/alumnos/ver&id=' . $alumno_id));
        return;
    }

    render_view('membresias/membresias_form_view.php', [
        'user' => $user,
        'alumno' => $alumno,
        'planes' => planes_fetch_activos(),
        'payload' => [
            'plan_id' => '',
            'fecha_inicio' => date('Y-m-d'),
        ],
        'errors' => [],
        'csrf_token' => csrf_token(),
    ]);
}

function membresias_create_submit(): void
{
    $user = require_rol(['admin', 'recepcion']);
    require_csrf();

    $alumno_id = (int) query_param('alumno_id', 0);
    $alumno = $alumno_id > 0 ? alumnos_fetch_by_id($alumno_id) : null;
    if ($alumno === null) {
        http_response_code(404);
        header('Content-Type: text/plain; charset=utf-8');
        echo 'Alumno no encontrado.';
        return;
    }

    $plan_id = (int) post_param('plan_id', 0);
    $fecha_inicio = trim((string) post_param('fecha_inicio', ''));

    $result = membresias_create($alumno_id, $plan_id, $fecha_inicio);

    if (!$result['ok']) {
        render_view('membresias/membresias_form_view.php', [
            'user' => $user,
            'alumno' => $alumno,
            'planes' => planes_fetch_activos(),
            'payload' => ['plan_id' => $plan_id, 'fecha_inicio' => $fecha_inicio],
            'errors' => $result['errors'] ?? [$result['code'] => $result['message']],
            'csrf_token' => csrf_token(),
        ]);
        return;
    }

    flash_push('alumnos', ['type' => 'success', 'message' => 'Membresia creada. Primer periodo pendiente de pago.']);
    redirect(base_url('?route=admin/alumnos/ver&id=' . $alumno_id));
}

function membresias_cancel_submit(): void
{
    require_rol(['admin', 'recepcion']);
    require_csrf();

    $id = (int) query_param('id', 0);
    if ($id <= 0) {
        http_response_code(400);
        header('Content-Type: text/plain; charset=utf-8');
        echo 'ID invalido.';
        return;
    }

    $m = membresias_fetch_by_id($id);
    if ($m === null) {
        http_response_code(404);
        header('Content-Type: text/plain; charset=utf-8');
        echo 'Membresia no encontrada.';
        return;
    }

    $result = membresias_cancel($id);
    if (!$result['ok']) {
        flash_push('alumnos', ['type' => 'error', 'message' => (string) ($result['message'] ?? 'No se pudo cancelar.')]);
    } else {
        flash_push('alumnos', ['type' => 'success', 'message' => 'Membresia cancelada.']);
    }

    redirect(base_url('?route=admin/alumnos/ver&id=' . (int) $m['alumno_id']));
}

// --------------------------------------------------------------------
// Data layer
// --------------------------------------------------------------------

function membresias_create(int $alumno_id, int $plan_id, string $fecha_inicio): array
{
    $errors = [];

    $alumno = alumnos_fetch_by_id($alumno_id);
    if ($alumno === null) {
        return ['ok' => false, 'code' => 'ALUMNO_NO_ENCONTRADO', 'message' => 'Alumno no encontrado.'];
    }
    if ((string) $alumno['estado'] !== 'activo') {
        $errors['alumno'] = 'El alumno no esta activo.';
    }

    if ($plan_id <= 0) {
        $errors['plan_id'] = 'Seleccione un plan.';
    } else {
        $plan = planes_fetch_by_id($plan_id);
        if ($plan === null) {
            $errors['plan_id'] = 'Plan no encontrado.';
        } elseif ((int) $plan['activo'] !== 1) {
            $errors['plan_id'] = 'El plan no esta activo.';
        }
    }

    if ($fecha_inicio === '' || strtotime($fecha_inicio) === false) {
        $errors['fecha_inicio'] = 'Fecha de inicio invalida.';
    } else {
        $ts = strtotime($fecha_inicio);
        $limit_past = strtotime('-30 days');
        $limit_future = strtotime('+30 days');
        if ($ts < $limit_past || $ts > $limit_future) {
            $errors['fecha_inicio'] = 'La fecha debe estar dentro de 30 dias desde hoy (pasado o futuro).';
        }
    }

    if (!empty($errors)) {
        return ['ok' => false, 'code' => 'VALIDACION', 'errors' => $errors];
    }

    $db = getDB();

    try {
        $db->beginTransaction();

        // Lock alumno
        $stmt = $db->prepare('SELECT id FROM alumnos WHERE id = :id FOR UPDATE');
        $stmt->execute([':id' => $alumno_id]);
        if ($stmt->fetch() === false) {
            $db->rollBack();
            return ['ok' => false, 'code' => 'ALUMNO_NO_ENCONTRADO', 'message' => 'Alumno no encontrado.'];
        }

        // Check no hay membresia viva
        $check = $db->prepare(
            "SELECT id FROM membresias
             WHERE alumno_id = :a AND estado IN ('pendiente','activa','suspendida')
             LIMIT 1 FOR UPDATE"
        );
        $check->execute([':a' => $alumno_id]);
        if ($check->fetch() !== false) {
            $db->rollBack();
            return [
                'ok' => false,
                'code' => 'MEMBRESIA_EXISTENTE',
                'message' => 'El alumno ya tiene una membresia viva. Cancelala primero.',
            ];
        }

        $plan = planes_fetch_by_id($plan_id);
        $duracion = (int) $plan['duracion_dias'];

        $fecha_fin_ts = strtotime($fecha_inicio . ' +' . ($duracion - 1) . ' days');
        $fecha_fin = date('Y-m-d', $fecha_fin_ts);

        $clases_disp = $plan['tipo_acceso'] === 'cantidad_clases'
            ? (int) $plan['cantidad_clases']
            : null;

        $ins_m = $db->prepare(
            'INSERT INTO membresias
                (alumno_id, plan_id, fecha_inicio, fecha_fin, estado, clases_disponibles, created_at, updated_at)
             VALUES
                (:alumno_id, :plan_id, :fi, :ff, :estado, :cd, NOW(), NOW())'
        );
        $ins_m->execute([
            ':alumno_id' => $alumno_id,
            ':plan_id' => $plan_id,
            ':fi' => $fecha_inicio,
            ':ff' => $fecha_fin,
            ':estado' => 'pendiente',
            ':cd' => $clases_disp,
        ]);
        $membresia_id = (int) $db->lastInsertId();

        $ins_p = $db->prepare(
            'INSERT INTO periodos_liquidables
                (alumno_id, membresia_id, periodo_desde, periodo_hasta, fecha_vencimiento,
                 estado, monto, saldo, created_at, updated_at)
             VALUES
                (:alumno_id, :m, :pd, :ph, :fv, :estado, :monto, :saldo, NOW(), NOW())'
        );
        $ins_p->execute([
            ':alumno_id' => $alumno_id,
            ':m' => $membresia_id,
            ':pd' => $fecha_inicio,
            ':ph' => $fecha_fin,
            ':fv' => $fecha_inicio,
            ':estado' => 'pendiente',
            ':monto' => number_format((float) $plan['precio'], 2, '.', ''),
            ':saldo' => number_format((float) $plan['precio'], 2, '.', ''),
        ]);

        $db->commit();

        return ['ok' => true, 'membresia_id' => $membresia_id];
    } catch (Throwable $e) {
        if ($db->inTransaction()) {
            $db->rollBack();
        }
        throw $e;
    }
}

function membresias_cancel(int $id): array
{
    $db = getDB();

    try {
        $db->beginTransaction();

        $stmt = $db->prepare('SELECT * FROM membresias WHERE id = :id FOR UPDATE');
        $stmt->execute([':id' => $id]);
        $m = $stmt->fetch();
        if ($m === false) {
            $db->rollBack();
            return ['ok' => false, 'message' => 'Membresia no encontrada.'];
        }

        if (!in_array((string) $m['estado'], MEMBRESIAS_ESTADOS_VIVAS, true)) {
            $db->rollBack();
            return ['ok' => false, 'message' => 'La membresia no esta en un estado cancelable.'];
        }

        $upd = $db->prepare("UPDATE membresias SET estado = 'cancelada', updated_at = NOW() WHERE id = :id");
        $upd->execute([':id' => $id]);

        $upd_p = $db->prepare(
            "UPDATE periodos_liquidables
             SET estado = 'anulado', updated_at = NOW()
             WHERE membresia_id = :id AND estado = 'pendiente'"
        );
        $upd_p->execute([':id' => $id]);

        $db->commit();
        return ['ok' => true];
    } catch (Throwable $e) {
        if ($db->inTransaction()) {
            $db->rollBack();
        }
        throw $e;
    }
}

function membresias_fetch_by_id(int $id): ?array
{
    $db = getDB();
    $stmt = $db->prepare(
        'SELECT m.*, p.nombre AS plan_nombre, p.precio AS plan_precio,
                p.tipo_acceso AS plan_tipo_acceso, p.cantidad_clases AS plan_cantidad_clases
         FROM membresias m
         INNER JOIN planes p ON p.id = m.plan_id
         WHERE m.id = :id LIMIT 1'
    );
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch();
    return $row === false ? null : $row;
}

function membresias_fetch_by_alumno(int $alumno_id): array
{
    $db = getDB();
    $stmt = $db->prepare(
        'SELECT m.*, p.nombre AS plan_nombre, p.precio AS plan_precio,
                p.tipo_acceso AS plan_tipo_acceso, p.cantidad_clases AS plan_cantidad_clases
         FROM membresias m
         INNER JOIN planes p ON p.id = m.plan_id
         WHERE m.alumno_id = :a
         ORDER BY m.created_at DESC'
    );
    $stmt->execute([':a' => $alumno_id]);
    return $stmt->fetchAll();
}

function membresias_fetch_actual_viva(int $alumno_id): ?array
{
    $db = getDB();
    $stmt = $db->prepare(
        "SELECT m.*, p.nombre AS plan_nombre, p.precio AS plan_precio,
                p.tipo_acceso AS plan_tipo_acceso, p.cantidad_clases AS plan_cantidad_clases
         FROM membresias m
         INNER JOIN planes p ON p.id = m.plan_id
         WHERE m.alumno_id = :a AND m.estado IN ('pendiente','activa','suspendida')
         ORDER BY m.created_at DESC
         LIMIT 1"
    );
    $stmt->execute([':a' => $alumno_id]);
    $row = $stmt->fetch();
    return $row === false ? null : $row;
}

function membresias_fetch_periodos(int $membresia_id): array
{
    $db = getDB();
    $stmt = $db->prepare(
        'SELECT * FROM periodos_liquidables
         WHERE membresia_id = :m
         ORDER BY periodo_desde ASC'
    );
    $stmt->execute([':m' => $membresia_id]);
    return $stmt->fetchAll();
}
