<?php

declare(strict_types=1);

function home_index(): void
{
    render_view('home/home_view.php', [
        'systemName' => 'Sistema Team Di Paola',
        'environment' => ENVIRONMENT,
        'dbStatus' => app_db_status(),
    ]);
}
