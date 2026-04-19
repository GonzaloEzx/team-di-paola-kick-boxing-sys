<?php

declare(strict_types=1);

function dispatch_route(): void
{
    $route = current_route();
    $method = request_method();

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

        case 'auth/login':
            require_once APP_ROOT . '/modules/auth/auth_controller.php';
            if ($method === 'POST') {
                auth_login_submit();
            } else {
                auth_login_form();
            }
            return;

        case 'auth/logout':
            require_once APP_ROOT . '/modules/auth/auth_controller.php';
            auth_logout_handler();
            return;

        case 'api/auth/login':
            require_once APP_ROOT . '/api/auth/login.php';
            api_auth_login();
            return;

        case 'api/auth/logout':
            require_once APP_ROOT . '/api/auth/logout.php';
            api_auth_logout();
            return;

        case 'admin/dashboard':
            require_once APP_ROOT . '/modules/admin/admin_controller.php';
            admin_dashboard_index();
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
