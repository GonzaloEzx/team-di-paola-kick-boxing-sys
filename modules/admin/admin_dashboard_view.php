<?php declare(strict_types=1);

$modulos = [
    ['icon' => 'AL', 'titulo' => 'Alumnos', 'desc' => 'Alta, edicion y fichas de alumnos.', 'estado' => 'Disponible', 'href' => base_url('?route=admin/alumnos')],
    ['icon' => 'PL', 'titulo' => 'Planes', 'desc' => 'Catalogo de planes y precios.', 'estado' => 'Disponible', 'href' => base_url('?route=admin/planes')],
    ['icon' => 'MB', 'titulo' => 'Membresias', 'desc' => 'Alta desde la ficha de cada alumno.', 'estado' => 'Desde ficha alumno', 'href' => base_url('?route=admin/alumnos')],
    ['icon' => 'PG', 'titulo' => 'Pagos', 'desc' => 'Cobro de cuotas y comprobantes.', 'estado' => 'En construccion', 'href' => null],
    ['icon' => 'AS', 'titulo' => 'Asistencias', 'desc' => 'Check-in de clases grupales y libre.', 'estado' => 'En construccion', 'href' => null],
    ['icon' => 'CL', 'titulo' => 'Clases', 'desc' => 'Grilla, horarios y actividades.', 'estado' => 'En construccion', 'href' => null],
    ['icon' => 'VT', 'titulo' => 'Ventas', 'desc' => 'Ventas de productos y servicios.', 'estado' => 'En construccion', 'href' => null],
    ['icon' => 'PR', 'titulo' => 'Productos', 'desc' => 'Stock e inventario.', 'estado' => 'En construccion', 'href' => null],
    ['icon' => 'CJ', 'titulo' => 'Caja', 'desc' => 'Movimientos y cierre de caja.', 'estado' => 'En construccion', 'href' => null],
];

$nombre = isset($user['nombre']) ? (string) $user['nombre'] : '';
$roles = isset($user['roles']) && is_array($user['roles']) ? $user['roles'] : [];
$rol_principal = !empty($roles) ? strtoupper((string) $roles[0]) : '';

layout_header([
    'title' => 'Panel',
    'user' => $user,
]);
?>

<section class="tdp-page-header">
    <h1>Panel de control</h1>
    <p>
        Hola <?= h($nombre) ?>.
        <?php if ($rol_principal !== ''): ?>
            Sesion iniciada como <strong style="color: var(--color-accent);"><?= h($rol_principal) ?></strong>.
        <?php endif; ?>
    </p>
</section>

<section class="tdp-grid" aria-label="Modulos del sistema">
    <?php foreach ($modulos as $mod): ?>
        <?php $href = $mod['href'] ?? null; ?>
        <?php if ($href !== null): ?>
            <a class="tdp-card" href="<?= h($href) ?>" style="text-decoration:none; color:inherit; display:block;">
                <div class="tdp-card__icon"><?= h($mod['icon']) ?></div>
                <h3 class="tdp-card__title"><?= h($mod['titulo']) ?></h3>
                <p class="tdp-card__desc"><?= h($mod['desc']) ?></p>
                <span class="tdp-card__status tdp-card__status--ready"><?= h($mod['estado']) ?></span>
            </a>
        <?php else: ?>
            <article class="tdp-card tdp-card--disabled" aria-disabled="true">
                <div class="tdp-card__icon"><?= h($mod['icon']) ?></div>
                <h3 class="tdp-card__title"><?= h($mod['titulo']) ?></h3>
                <p class="tdp-card__desc"><?= h($mod['desc']) ?></p>
                <span class="tdp-card__status"><?= h($mod['estado']) ?></span>
            </article>
        <?php endif; ?>
    <?php endforeach; ?>
</section>

<?php layout_footer(); ?>
