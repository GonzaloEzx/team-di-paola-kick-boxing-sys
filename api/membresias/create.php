<?php

declare(strict_types=1);

require_once APP_ROOT . '/modules/membresias/membresias_controller.php';

function api_membresias_create(): void
{
    require_rol(['admin', 'recepcion']);
    require_csrf();

    if (request_method() !== 'POST') {
        json_error('Metodo no permitido', 'METODO_INVALIDO', 405);
        return;
    }

    $payload = api_membresias_read_json_or_post();

    $alumno_id = (int) ($payload['alumno_id'] ?? 0);
    $plan_id = (int) ($payload['plan_id'] ?? 0);
    $fecha_inicio = trim((string) ($payload['fecha_inicio'] ?? ''));

    $result = membresias_create($alumno_id, $plan_id, $fecha_inicio);

    if (!$result['ok']) {
        $code = (string) ($result['code'] ?? 'ERROR');
        $status = $code === 'MEMBRESIA_EXISTENTE' ? 409 : ($code === 'VALIDACION' ? 422 : 400);
        http_response_code($status);
        json_response([
            'success' => false,
            'error' => $result['message'] ?? 'No se pudo crear la membresia.',
            'code' => $code,
            'errors' => $result['errors'] ?? null,
        ]);
        return;
    }

    json_success(['id' => (int) $result['membresia_id']]);
}

function api_membresias_read_json_or_post(): array
{
    $raw = file_get_contents('php://input');
    if (is_string($raw) && $raw !== '') {
        $decoded = json_decode($raw, true);
        if (is_array($decoded)) {
            $out = [];
            foreach ($decoded as $k => $v) {
                $out[(string) $k] = is_scalar($v) ? trim((string) $v) : '';
            }
            return $out;
        }
    }
    $out = [];
    foreach ($_POST as $k => $v) {
        $out[(string) $k] = is_scalar($v) ? trim((string) $v) : '';
    }
    return $out;
}
