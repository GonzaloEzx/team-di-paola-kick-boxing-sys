<?php

declare(strict_types=1);

function admin_dashboard_index(): void
{
    $user = require_rol(['admin', 'recepcion']);

    render_view('admin/admin_dashboard_view.php', [
        'user' => $user,
    ]);
}
