<?php declare(strict_types=1);

$estado = (string) ($alumno['estado'] ?? 'activo');
$nombre_completo = trim(((string) ($alumno['apellido'] ?? '')) . ', ' . ((string) ($alumno['nombre'] ?? '')));

$field = function (?string $value) {
    if ($value === null || $value === '') {
        return '<span class="tdp-muted">&mdash;</span>';
    }
    return h($value);
};

$format_date = function (?string $date) {
    if ($date === null || $date === '' || $date === '0000-00-00') {
        return null;
    }
    $ts = strtotime($date);
    if ($ts === false) {
        return null;
    }
    return date('d/m/Y', $ts);
};

$fecha_nac = $format_date((string) ($alumno['fecha_nacimiento'] ?? ''));
$created_at = $format_date((string) ($alumno['created_at'] ?? ''));

$change_state_url = base_url('?route=admin/alumnos/estado&id=' . (int) $alumno['id']);

$estados_siguientes = array_values(array_filter(ALUMNOS_ESTADOS, static function ($s) use ($estado) {
    return $s !== $estado;
}));

layout_header([
    'title' => $nombre_completo !== ', ' ? $nombre_completo : 'Alumno',
    'user' => $user,
]);
?>

<nav class="tdp-breadcrumb">
    <a href="<?= h(base_url('?route=admin/alumnos')) ?>">Alumnos</a>
    <span>/</span>
    <span><?= h($nombre_completo) ?></span>
</nav>

<section class="tdp-page-header" style="display:flex; align-items:flex-start; justify-content:space-between; gap:1rem; flex-wrap:wrap;">
    <div>
        <h1><?= h($nombre_completo) ?></h1>
        <p>
            <span class="tdp-badge tdp-badge--<?= h($estado) ?>"><?= h($estado) ?></span>
            <?php if ($created_at !== null): ?>
                <span class="tdp-muted">&middot; alta <?= h($created_at) ?></span>
            <?php endif; ?>
        </p>
    </div>
    <div style="display:flex; gap:.5rem; flex-wrap:wrap;">
        <a class="tdp-btn" href="<?= h(base_url('?route=admin/alumnos/editar&id=' . (int) $alumno['id'])) ?>">Editar</a>
    </div>
</section>

<?php if (!empty($flash)): ?>
    <?php foreach ($flash as $f): ?>
        <div class="tdp-alert" style="background: rgba(111,207,151,0.12); border-left:3px solid var(--color-success); color:#b6e8c8;">
            <?= h($f['message'] ?? '') ?>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<div class="tdp-detail-grid">
    <section class="tdp-detail-card">
        <h2>Personal</h2>
        <dl class="tdp-dl">
            <dt>Nombre</dt><dd><?= $field((string) ($alumno['nombre'] ?? '')) ?></dd>
            <dt>Apellido</dt><dd><?= $field((string) ($alumno['apellido'] ?? '')) ?></dd>
            <dt>DNI</dt><dd><?= $field($alumno['dni'] ?? null) ?></dd>
            <dt>Fecha de nacimiento</dt><dd><?= $fecha_nac !== null ? h($fecha_nac) : '<span class="tdp-muted">&mdash;</span>' ?></dd>
        </dl>
    </section>

    <section class="tdp-detail-card">
        <h2>Contacto</h2>
        <dl class="tdp-dl">
            <dt>Telefono</dt><dd><?= $field($alumno['telefono'] ?? null) ?></dd>
            <dt>Email</dt><dd><?= $field($alumno['email'] ?? null) ?></dd>
        </dl>
    </section>

    <section class="tdp-detail-card">
        <h2>Emergencia</h2>
        <dl class="tdp-dl">
            <dt>Nombre</dt><dd><?= $field($alumno['contacto_emergencia_nombre'] ?? null) ?></dd>
            <dt>Telefono</dt><dd><?= $field($alumno['contacto_emergencia_telefono'] ?? null) ?></dd>
        </dl>
    </section>

    <section class="tdp-detail-card">
        <h2>Observaciones</h2>
        <p style="white-space:pre-wrap; margin:0;">
            <?= !empty($alumno['observaciones']) ? h((string) $alumno['observaciones']) : '<span class="tdp-muted">Sin observaciones.</span>' ?>
        </p>
    </section>

    <section class="tdp-detail-card tdp-detail-card--state">
        <h2>Cambiar estado</h2>
        <p class="tdp-muted" style="font-size:.85rem;">Estado actual: <strong><?= h($estado) ?></strong></p>
        <form method="post" action="<?= h($change_state_url) ?>" style="display:flex; gap:.5rem; flex-wrap:wrap;">
            <input type="hidden" name="csrf_token" value="<?= h($csrf_token) ?>">
            <?php foreach ($estados_siguientes as $st): ?>
                <button type="submit" name="estado" value="<?= h($st) ?>" class="tdp-topbar__logout" style="cursor:pointer;">
                    Marcar <?= h($st) ?>
                </button>
            <?php endforeach; ?>
        </form>
    </section>
</div>

<h2 class="tdp-detail-section-title">Historial operativo</h2>

<div class="tdp-detail-grid">
    <section class="tdp-detail-card tdp-detail-card--placeholder">
        <h2>Membresia</h2>
        <p class="tdp-muted">Modulo pendiente (Fase 8 - Planes y Membresias).</p>
    </section>
    <section class="tdp-detail-card tdp-detail-card--placeholder">
        <h2>Pagos recientes</h2>
        <p class="tdp-muted">Modulo pendiente (Fase 9 - Pagos).</p>
    </section>
    <section class="tdp-detail-card tdp-detail-card--placeholder">
        <h2>Asistencias recientes</h2>
        <p class="tdp-muted">Modulo pendiente (Fase 10 - Asistencias).</p>
    </section>
</div>

<?php layout_footer(); ?>
