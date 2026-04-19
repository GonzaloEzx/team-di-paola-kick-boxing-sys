<?php declare(strict_types=1);

$total_pages = max(1, (int) ceil($total / $limit));
$from = $total === 0 ? 0 : (($page - 1) * $limit) + 1;
$to = min($total, $page * $limit);

$query_base = function (array $overrides) use ($q, $estado): string {
    $params = array_filter([
        'route' => 'admin/alumnos',
        'q' => $q,
        'estado' => $estado,
    ], static function ($v) { return $v !== '' && $v !== null; });
    foreach ($overrides as $k => $v) {
        if ($v === '' || $v === null) {
            unset($params[$k]);
        } else {
            $params[$k] = $v;
        }
    }
    return '?' . http_build_query($params);
};

layout_header([
    'title' => 'Alumnos',
    'user' => $user,
]);
?>

<section class="tdp-page-header" style="display:flex; align-items:flex-end; justify-content:space-between; gap:1rem; flex-wrap:wrap;">
    <div>
        <h1>Alumnos</h1>
        <p>Gestion de alumnos del equipo. Total: <strong style="color: var(--color-accent);"><?= (int) $total ?></strong>.</p>
    </div>
    <a class="tdp-btn" href="<?= h(base_url('?route=admin/alumnos/nuevo')) ?>">+ Nuevo alumno</a>
</section>

<?php if (!empty($flash)): ?>
    <?php foreach ($flash as $f): ?>
        <div class="tdp-alert" style="background: rgba(111,207,151,0.12); border-left:3px solid var(--color-success); color:#b6e8c8;">
            <?= h($f['message'] ?? '') ?>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<form method="get" action="<?= h(base_url()) ?>" class="tdp-filters">
    <input type="hidden" name="route" value="admin/alumnos">

    <div class="tdp-field" style="margin:0;">
        <label for="q">Buscar</label>
        <input class="tdp-input" type="text" id="q" name="q" value="<?= h($q) ?>" placeholder="Nombre, apellido, DNI, email o telefono">
    </div>

    <div class="tdp-field" style="margin:0; max-width:200px;">
        <label for="estado">Estado</label>
        <select class="tdp-input" id="estado" name="estado">
            <option value="">Todos</option>
            <?php foreach (ALUMNOS_ESTADOS as $st): ?>
                <option value="<?= h($st) ?>" <?= $estado === $st ? 'selected' : '' ?>><?= h(ucfirst($st)) ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="tdp-filters__actions">
        <button class="tdp-btn" type="submit">Filtrar</button>
        <?php if ($q !== '' || $estado !== ''): ?>
            <a class="tdp-topbar__logout" href="<?= h(base_url('?route=admin/alumnos')) ?>" style="text-transform:uppercase;">Limpiar</a>
        <?php endif; ?>
    </div>
</form>

<div class="tdp-table-wrap">
    <table class="tdp-table">
        <thead>
            <tr>
                <th>Alumno</th>
                <th>DNI</th>
                <th>Contacto</th>
                <th>Estado</th>
                <th>Membresia</th>
                <th style="text-align:right;">Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($alumnos)): ?>
                <tr>
                    <td colspan="6" class="tdp-table__empty">
                        <?php if ($q !== '' || $estado !== ''): ?>
                            No hay alumnos que coincidan con los filtros.
                        <?php else: ?>
                            Todavia no hay alumnos cargados. <a href="<?= h(base_url('?route=admin/alumnos/nuevo')) ?>">Cargar el primero</a>.
                        <?php endif; ?>
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($alumnos as $a): ?>
                    <tr>
                        <td>
                            <a class="tdp-table__name" href="<?= h(base_url('?route=admin/alumnos/ver&id=' . (int) $a['id'])) ?>">
                                <?= h((string) $a['apellido']) ?>, <?= h((string) $a['nombre']) ?>
                            </a>
                        </td>
                        <td><?= $a['dni'] !== null ? h((string) $a['dni']) : '<span class="tdp-muted">-</span>' ?></td>
                        <td>
                            <?php if (!empty($a['telefono'])): ?><div><?= h((string) $a['telefono']) ?></div><?php endif; ?>
                            <?php if (!empty($a['email'])): ?><div class="tdp-muted"><?= h((string) $a['email']) ?></div><?php endif; ?>
                            <?php if (empty($a['telefono']) && empty($a['email'])): ?><span class="tdp-muted">-</span><?php endif; ?>
                        </td>
                        <td><span class="tdp-badge tdp-badge--<?= h((string) $a['estado']) ?>"><?= h((string) $a['estado']) ?></span></td>
                        <td><span class="tdp-muted">pendiente modulo</span></td>
                        <td style="text-align:right; white-space:nowrap;">
                            <a class="tdp-link" href="<?= h(base_url('?route=admin/alumnos/ver&id=' . (int) $a['id'])) ?>">Ver</a>
                            <a class="tdp-link" href="<?= h(base_url('?route=admin/alumnos/editar&id=' . (int) $a['id'])) ?>">Editar</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php if ($total > 0): ?>
    <nav class="tdp-pager" aria-label="Paginacion">
        <span class="tdp-pager__info">Mostrando <?= (int) $from ?>-<?= (int) $to ?> de <?= (int) $total ?></span>
        <div class="tdp-pager__actions">
            <?php if ($page > 1): ?>
                <a class="tdp-topbar__logout" href="<?= h(base_url($query_base(['page' => $page - 1]))) ?>">&larr; Anterior</a>
            <?php else: ?>
                <span class="tdp-topbar__logout" style="opacity:.5; cursor:not-allowed;">&larr; Anterior</span>
            <?php endif; ?>

            <span class="tdp-pager__current">Pagina <?= (int) $page ?> de <?= (int) $total_pages ?></span>

            <?php if ($page < $total_pages): ?>
                <a class="tdp-topbar__logout" href="<?= h(base_url($query_base(['page' => $page + 1]))) ?>">Siguiente &rarr;</a>
            <?php else: ?>
                <span class="tdp-topbar__logout" style="opacity:.5; cursor:not-allowed;">Siguiente &rarr;</span>
            <?php endif; ?>
        </div>
    </nav>
<?php endif; ?>

<?php layout_footer(); ?>
