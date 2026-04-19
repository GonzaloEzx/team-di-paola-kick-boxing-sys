<?php declare(strict_types=1);

$is_edit = ($mode ?? 'nuevo') === 'editar';
$title = $is_edit ? 'Editar alumno' : 'Nuevo alumno';
$action = $is_edit
    ? base_url('?route=admin/alumnos/editar&id=' . (int) ($alumno['id'] ?? 0))
    : base_url('?route=admin/alumnos/nuevo');

$error_for = function (string $field) use ($errors): string {
    return isset($errors[$field]) ? (string) $errors[$field] : '';
};

$field = function (string $name, string $default = '') use ($alumno) {
    return isset($alumno[$name]) ? (string) $alumno[$name] : $default;
};

layout_header([
    'title' => $title,
    'user' => $user,
]);
?>

<section class="tdp-page-header">
    <h1><?= h($title) ?></h1>
    <p>
        <?php if ($is_edit): ?>
            Editando a <strong><?= h($field('apellido')) ?>, <?= h($field('nombre')) ?></strong>.
        <?php else: ?>
            Datos minimos: nombre y apellido. El resto es opcional pero recomendable.
        <?php endif; ?>
    </p>
</section>

<?php if (!empty($errors)): ?>
    <div class="tdp-alert tdp-alert--error">
        Hay errores en el formulario. Revisa los campos marcados abajo.
    </div>
<?php endif; ?>

<form method="post" action="<?= h($action) ?>" class="tdp-form" autocomplete="on" novalidate>
    <input type="hidden" name="csrf_token" value="<?= h($csrf_token) ?>">

    <fieldset class="tdp-fieldset">
        <legend>Personal</legend>
        <div class="tdp-form__row">
            <div class="tdp-field">
                <label for="nombre">Nombre *</label>
                <input class="tdp-input" type="text" id="nombre" name="nombre" value="<?= h($field('nombre')) ?>" maxlength="100" required>
                <?php if ($error_for('nombre')): ?><small class="tdp-field__error"><?= h($error_for('nombre')) ?></small><?php endif; ?>
            </div>
            <div class="tdp-field">
                <label for="apellido">Apellido *</label>
                <input class="tdp-input" type="text" id="apellido" name="apellido" value="<?= h($field('apellido')) ?>" maxlength="100" required>
                <?php if ($error_for('apellido')): ?><small class="tdp-field__error"><?= h($error_for('apellido')) ?></small><?php endif; ?>
            </div>
        </div>
        <div class="tdp-form__row">
            <div class="tdp-field">
                <label for="dni">DNI</label>
                <input class="tdp-input" type="text" id="dni" name="dni" value="<?= h($field('dni')) ?>" maxlength="30">
                <?php if ($error_for('dni')): ?><small class="tdp-field__error"><?= h($error_for('dni')) ?></small><?php endif; ?>
            </div>
            <div class="tdp-field">
                <label for="fecha_nacimiento">Fecha de nacimiento</label>
                <input class="tdp-input" type="date" id="fecha_nacimiento" name="fecha_nacimiento" value="<?= h($field('fecha_nacimiento')) ?>">
                <?php if ($error_for('fecha_nacimiento')): ?><small class="tdp-field__error"><?= h($error_for('fecha_nacimiento')) ?></small><?php endif; ?>
            </div>
        </div>
    </fieldset>

    <fieldset class="tdp-fieldset">
        <legend>Contacto</legend>
        <div class="tdp-form__row">
            <div class="tdp-field">
                <label for="telefono">Telefono</label>
                <input class="tdp-input" type="text" id="telefono" name="telefono" value="<?= h($field('telefono')) ?>" maxlength="30">
                <?php if ($error_for('telefono')): ?><small class="tdp-field__error"><?= h($error_for('telefono')) ?></small><?php endif; ?>
            </div>
            <div class="tdp-field">
                <label for="email">Email</label>
                <input class="tdp-input" type="email" id="email" name="email" value="<?= h($field('email')) ?>" maxlength="150">
                <?php if ($error_for('email')): ?><small class="tdp-field__error"><?= h($error_for('email')) ?></small><?php endif; ?>
            </div>
        </div>
    </fieldset>

    <fieldset class="tdp-fieldset">
        <legend>Contacto de emergencia</legend>
        <div class="tdp-form__row">
            <div class="tdp-field">
                <label for="contacto_emergencia_nombre">Nombre</label>
                <input class="tdp-input" type="text" id="contacto_emergencia_nombre" name="contacto_emergencia_nombre" value="<?= h($field('contacto_emergencia_nombre')) ?>" maxlength="150">
                <?php if ($error_for('contacto_emergencia_nombre')): ?><small class="tdp-field__error"><?= h($error_for('contacto_emergencia_nombre')) ?></small><?php endif; ?>
            </div>
            <div class="tdp-field">
                <label for="contacto_emergencia_telefono">Telefono</label>
                <input class="tdp-input" type="text" id="contacto_emergencia_telefono" name="contacto_emergencia_telefono" value="<?= h($field('contacto_emergencia_telefono')) ?>" maxlength="30">
                <?php if ($error_for('contacto_emergencia_telefono')): ?><small class="tdp-field__error"><?= h($error_for('contacto_emergencia_telefono')) ?></small><?php endif; ?>
            </div>
        </div>
    </fieldset>

    <fieldset class="tdp-fieldset">
        <legend>Observaciones</legend>
        <div class="tdp-field">
            <label for="observaciones">Observaciones (medicas o administrativas)</label>
            <textarea class="tdp-input" id="observaciones" name="observaciones" rows="4"><?= h($field('observaciones')) ?></textarea>
        </div>
    </fieldset>

    <div class="tdp-form__actions">
        <a class="tdp-topbar__logout" href="<?= h($is_edit ? base_url('?route=admin/alumnos/ver&id=' . (int) ($alumno['id'] ?? 0)) : base_url('?route=admin/alumnos')) ?>">Cancelar</a>
        <button type="submit" class="tdp-btn"><?= $is_edit ? 'Guardar cambios' : 'Crear alumno' ?></button>
    </div>
</form>

<?php layout_footer(); ?>
