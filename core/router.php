<?php

declare(strict_types=1);

function dispatch_route(): void
{
    $route = current_route();

    switch ($route) {
        case '':
        case 'home':
            require_once APP_ROOT . '/modules/home/home_controller.php';
            home_index();
            return;

        case 'health':
            route_health();
            return;

        case 'api/health':
            route_api_health();
            return;

        case 'admin/dashboard':
            route_admin_dashboard();
            return;

        default:
            route_not_found($route);
            return;
    }
}

function route_health(): void
{
    header('Content-Type: text/plain; charset=utf-8');

    $dbStatus = app_db_status();

    echo "app: OK\n";
    echo 'db: ' . strtoupper($dbStatus) . "\n";
    echo 'env: ' . ENVIRONMENT . "\n";
}

function route_api_health(): void
{
    json_success([
        'app' => 'ok',
        'db' => app_db_status(),
        'environment' => ENVIRONMENT,
    ]);
}

function route_admin_dashboard(): void
{
    header('Content-Type: text/html; charset=utf-8');

    echo '<!doctype html>';
    echo '<html lang="es">';
    echo '<head>';
    echo '<meta charset="utf-8">';
    echo '<meta name="viewport" content="width=device-width, initial-scale=1">';
    echo '<title>Dashboard admin</title>';
    echo '</head>';
    echo '<body>';
    echo '<main>';
    echo '<h1>Dashboard admin en construcción</h1>';
    echo '</main>';
    echo '</body>';
    echo '</html>';
}

function route_not_found(string $route): void
{
    http_response_code(404);
    header('Content-Type: text/plain; charset=utf-8');

    echo 'Ruta no encontrada: ' . $route;
}

function app_db_status(): string
{
    try {
        getDB();
        return 'ok';
    } catch (Throwable $exception) {
        return 'error';
    }
}
