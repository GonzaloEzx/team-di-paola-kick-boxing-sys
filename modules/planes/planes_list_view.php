<?php declare(strict_types=1);

$total_pages = max(1, (int) ceil($total / $limit));
$from = $total === 0 ? 0 : (($page - 1) * $limit) + 1;
$to = min($total, $page * $limit);

$query_base = function (array $overrides) use ($q, $activo): string {
    $params = array_filter([
        'route' => 'admin/planes',
        'q' => $q,
        'activo' => $activo,
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

$money = function ($v): string {
    return '$ ' . number_format((float) $v, 2, ',', '.');
};

layout_header(['title' => 'Planes', 'user' => $user]);
?>

<section class="tdp-page-header" style="display:flex; align-items:flex-end; justify-content:space-between; gap:1rem; flex-wrap:wrap;">
    <div>
        <h1>Planes</h1>
        <p>Catalogo comercial de membresias. Total: <strong style="color: var(--color-accent);"><?= (int) $total ?></strong>.</p>
    </div>
    <a class="tdp-btn" href="<?= h(base_url('?route=admin/planes/nuevo')) ?>">+ Nuevo plan</a>
</section>

<?php if (!empty($flash)): ?>
    <?php foreach ($flash as $f): ?>
        <div class="tdp-alert" style="background: rgba(111,207,151,0.12); border-left:3px solid var(--color-success); color:#b6e8c8;">
            <?= h($f['message'] ?? '') ?>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<form method="get" action="<?= h(base_url()) ?>" class="tdp-filters">
    <input type="hidden" name="route" value="admin/planes">
    <div class="tdp-field" style="margin:0;">
        <label for="q">Buscar</label>
        <input class="tdp-input" type="text" id="q" name="q" value="<?= h($q) ?>" placeholder="Nombre o descripcion">
    </div>
    <div class="tdp-field" style="margin:0; max-width:200px;">
        <label for="activo">Estado</label>
        <select class="tdp-input" id="activo" name="activo">
            <option value="">Todos</option>
            <option value="1" <?= $activo === '1' ? 'selected' : '' ?>>Activos</option>
            <option value="0" <?= $activo === '0' ? 'selected' : '' ?>>Inactivos</option>
        </select>
    </div>
    <div class="tdp-filters__actions">
        <button class="tdp-btn" type="submit">Filtrar</button>
        <?php if ($q !== '' || $activo !== ''): ?>
            <a class="tdp-topbar__logout" href="<?= h(base_url('?route=admin/planes')) ?>">Limpiar</a>
        <?php endif; ?>
    </div>
</form>

<div class="tdp-table-wrap">
    <table class="tdp-table">
        <thead>
            <tr>
                <th>Plan</th>
                <th>Precio</th>
                <th>Duracion</th>
                <th>Tipo</th>
                <th>Estado</th>
                <th style="text-align:right;">Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($planes)): ?>
                <tr><td colspan="6" class="tdp-table__empty">No hay planes que coincidan.</td></tr>
            <?php else: ?>
                <?php foreach ($planes as $p): ?>
                    <tr>
                        <td>
                            <div class="tdp-table__name"><?= h((string) $p['nombre']) ?></div>
                            <?php if (!empty($p['descripcion'])): ?>
                                <div class="tdp-muted" style="font-size:.8rem;"><?= h((string) $p['descripcion']) ?></div>
                            <?php endif; ?>
                        </td>
                        <td><?= $money($p['precio']) ?></td>
                        <td><?= (int) $p['duracion_dias'] ?> dias</td>
                        <td>
                            <?php if ($p['tipo_acceso'] === 'libre'): ?>
                                Libre
                            <?php else: ?>
                                <?= (int) $p['cantidad_clases'] ?> clases
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ((int) $p['activo'] === 1): ?>
                                <span class="tdp-badge tdp-badge--activo">activo</span>
                            <?php else: ?>
                                <span class="tdp-badge tdp-badge--inactivo">inactivo</span>
                            <?php endif; ?>
                        </td>
                        <td style="text-align:right; white-space:nowrap;">
                            <a class="tdp-link" href="<?= h(base_url('?route=admin/planes/editar&id=' . (int) $p['id'])) ?>">Editar</a>
                            <form method="post" action="<?= h(base_url('?route=admin/planes/toggle&id=' . (int) $p['id'])) ?>" style="display:inline;">
                                <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
                                <button class="tdp-link" type="submit" style="background:none; border:0; cursor:pointer; padding:0; font-family:inherit;">
                                    <?= (int) $p['activo'] === 1 ? 'Desactivar' : 'Activar' ?>
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php if ($total > 0): ?>
    <nav class="tdp-pager">
        <span class="tdp-pager__info">Mostrando <?= (int) $from ?>-<?= (int) $to ?> de <?= (int) $total ?></span>
        <div class="tdp-pager__actions">
            <?php if ($page > 1): ?>
                <a class="tdp-topbar__logout" href="<?= h(base_url($query_base(['page' => $page - 1]))) ?>">&larr; Anterior</a>
            <?php else: ?>
                <span class="tdp-topbar__logout" style="opacity:.5;">&larr; Anterior</span>
            <?php endif; ?>
            <span class="tdp-pager__current">Pagina <?= (int) $page ?> de <?= (int) $total_pages ?></span>
            <?php if ($page < $total_pages): ?>
                <a class="tdp-topbar__logout" href="<?= h(base_url($query_base(['page' => $page + 1]))) ?>">Siguiente &rarr;</a>
            <?php else: ?>
                <span class="tdp-topbar__logout" style="opacity:.5;">Siguiente &rarr;</span>
            <?php endif; ?>
        </div>
    </nav>
<?php endif; ?>

<?php layout_footer(); ?>
