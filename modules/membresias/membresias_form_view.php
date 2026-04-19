<?php declare(strict_types=1);

$nombre_completo = trim(((string) $alumno['apellido']) . ', ' . ((string) $alumno['nombre']));
$error_for = function (string $field) use ($errors): string {
    return isset($errors[$field]) ? (string) $errors[$field] : '';
};

layout_header(['title' => 'Nueva membresia', 'user' => $user]);
?>

<nav class="tdp-breadcrumb">
    <a href="<?= h(base_url('?route=admin/alumnos')) ?>">Alumnos</a>
    <span>/</span>
    <a href="<?= h(base_url('?route=admin/alumnos/ver&id=' . (int) $alumno['id'])) ?>"><?= h($nombre_completo) ?></a>
    <span>/</span>
    <span>Nueva membresia</span>
</nav>

<section class="tdp-page-header">
    <h1>Nueva membresia</h1>
    <p>Asignando plan a <strong><?= h($nombre_completo) ?></strong>. Se generara un primer periodo pendiente de pago.</p>
</section>

<?php if (isset($errors['MEMBRESIA_EXISTENTE'])): ?>
    <div class="tdp-alert tdp-alert--error"><?= h($errors['MEMBRESIA_EXISTENTE']) ?></div>
<?php elseif (!empty($errors)): ?>
    <div class="tdp-alert tdp-alert--error">Revisa los campos marcados abajo.</div>
<?php endif; ?>

<?php if (empty($planes)): ?>
    <div class="tdp-alert tdp-alert--error">
        No hay planes activos. <a href="<?= h(base_url('?route=admin/planes/nuevo')) ?>" style="color:var(--color-accent);">Crea uno primero</a>.
    </div>
<?php endif; ?>

<form method="post" action="<?= h(base_url('?route=admin/membresias/nueva&alumno_id=' . (int) $alumno['id'])) ?>" class="tdp-form" novalidate>
    <input type="hidden" name="csrf_token" value="<?= h($csrf_token) ?>">

    <fieldset class="tdp-fieldset">
        <legend>Datos</legend>
        <div class="tdp-form__row">
            <div class="tdp-field">
                <label for="plan_id">Plan *</label>
                <select class="tdp-input" id="plan_id" name="plan_id" required <?= empty($planes) ? 'disabled' : '' ?>>
                    <option value="">Seleccionar...</option>
                    <?php foreach ($planes as $p): ?>
                        <option value="<?= (int) $p['id'] ?>" <?= ((string) $payload['plan_id'] === (string) $p['id']) ? 'selected' : '' ?>>
                            <?= h((string) $p['nombre']) ?> &mdash; $ <?= number_format((float) $p['precio'], 2, ',', '.') ?> / <?= (int) $p['duracion_dias'] ?> dias
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if ($error_for('plan_id')): ?><small class="tdp-field__error"><?= h($error_for('plan_id')) ?></small><?php endif; ?>
            </div>
            <div class="tdp-field">
                <label for="fecha_inicio">Fecha de inicio *</label>
                <input class="tdp-input" type="date" id="fecha_inicio" name="fecha_inicio" value="<?= h((string) $payload['fecha_inicio']) ?>" required>
                <?php if ($error_for('fecha_inicio')): ?><small class="tdp-field__error"><?= h($error_for('fecha_inicio')) ?></small><?php endif; ?>
            </div>
        </div>
        <?php if ($error_for('alumno')): ?><small class="tdp-field__error"><?= h($error_for('alumno')) ?></small><?php endif; ?>
    </fieldset>

    <div class="tdp-form__actions">
        <a class="tdp-topbar__logout" href="<?= h(base_url('?route=admin/alumnos/ver&id=' . (int) $alumno['id'])) ?>">Cancelar</a>
        <button type="submit" class="tdp-btn" <?= empty($planes) ? 'disabled' : '' ?>>Crear membresia</button>
    </div>
</form>

<?php layout_footer(); ?>
