<?php declare(strict_types=1);

$is_edit = ($mode ?? 'nuevo') === 'editar';
$title = $is_edit ? 'Editar plan' : 'Nuevo plan';
$action = $is_edit
    ? base_url('?route=admin/planes/editar&id=' . (int) ($plan['id'] ?? 0))
    : base_url('?route=admin/planes/nuevo');

$error_for = function (string $field) use ($errors): string {
    return isset($errors[$field]) ? (string) $errors[$field] : '';
};
$field = function (string $name, string $default = '') use ($plan) {
    return isset($plan[$name]) ? (string) $plan[$name] : $default;
};

layout_header(['title' => $title, 'user' => $user]);
?>

<section class="tdp-page-header">
    <h1><?= h($title) ?></h1>
    <p>Define reglas comerciales de una membresia.</p>
</section>

<?php if (!empty($errors)): ?>
    <div class="tdp-alert tdp-alert--error">Revisa los campos marcados abajo.</div>
<?php endif; ?>

<form method="post" action="<?= h($action) ?>" class="tdp-form" novalidate>
    <input type="hidden" name="csrf_token" value="<?= h($csrf_token) ?>">

    <fieldset class="tdp-fieldset">
        <legend>Datos del plan</legend>
        <div class="tdp-field">
            <label for="nombre">Nombre *</label>
            <input class="tdp-input" type="text" id="nombre" name="nombre" value="<?= h($field('nombre')) ?>" maxlength="120" required>
            <?php if ($error_for('nombre')): ?><small class="tdp-field__error"><?= h($error_for('nombre')) ?></small><?php endif; ?>
        </div>
        <div class="tdp-field">
            <label for="descripcion">Descripcion</label>
            <textarea class="tdp-input" id="descripcion" name="descripcion" rows="3"><?= h($field('descripcion')) ?></textarea>
        </div>
    </fieldset>

    <fieldset class="tdp-fieldset">
        <legend>Condiciones</legend>
        <div class="tdp-form__row">
            <div class="tdp-field">
                <label for="precio">Precio *</label>
                <input class="tdp-input" type="number" id="precio" name="precio" value="<?= h($field('precio')) ?>" min="0" step="0.01" required>
                <?php if ($error_for('precio')): ?><small class="tdp-field__error"><?= h($error_for('precio')) ?></small><?php endif; ?>
            </div>
            <div class="tdp-field">
                <label for="duracion_dias">Duracion (dias) *</label>
                <input class="tdp-input" type="number" id="duracion_dias" name="duracion_dias" value="<?= h($field('duracion_dias')) ?>" min="1" max="3650" required>
                <?php if ($error_for('duracion_dias')): ?><small class="tdp-field__error"><?= h($error_for('duracion_dias')) ?></small><?php endif; ?>
            </div>
        </div>
        <div class="tdp-form__row">
            <div class="tdp-field">
                <label for="tipo_acceso">Tipo de acceso *</label>
                <select class="tdp-input" id="tipo_acceso" name="tipo_acceso" onchange="togglePlanClasses(this.value)">
                    <?php foreach (PLANES_TIPOS_ACCESO as $tipo): ?>
                        <option value="<?= h($tipo) ?>" <?= $field('tipo_acceso') === $tipo ? 'selected' : '' ?>>
                            <?= $tipo === 'libre' ? 'Libre (sin tope de clases)' : 'Por cantidad de clases' ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if ($error_for('tipo_acceso')): ?><small class="tdp-field__error"><?= h($error_for('tipo_acceso')) ?></small><?php endif; ?>
            </div>
            <div class="tdp-field" id="field_cantidad_clases" style="<?= $field('tipo_acceso') === 'cantidad_clases' ? '' : 'display:none;' ?>">
                <label for="cantidad_clases">Cantidad de clases *</label>
                <input class="tdp-input" type="number" id="cantidad_clases" name="cantidad_clases" value="<?= h($field('cantidad_clases')) ?>" min="1">
                <?php if ($error_for('cantidad_clases')): ?><small class="tdp-field__error"><?= h($error_for('cantidad_clases')) ?></small><?php endif; ?>
            </div>
        </div>
    </fieldset>

    <div class="tdp-form__actions">
        <a class="tdp-topbar__logout" href="<?= h(base_url('?route=admin/planes')) ?>">Cancelar</a>
        <button type="submit" class="tdp-btn"><?= $is_edit ? 'Guardar cambios' : 'Crear plan' ?></button>
    </div>
</form>

<script>
function togglePlanClasses(v) {
    var el = document.getElementById('field_cantidad_clases');
    if (!el) return;
    el.style.display = v === 'cantidad_clases' ? '' : 'none';
    if (v !== 'cantidad_clases') {
        var inp = document.getElementById('cantidad_clases');
        if (inp) inp.value = '';
    }
}
</script>

<?php layout_footer(); ?>
