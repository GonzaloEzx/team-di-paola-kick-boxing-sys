<?php

declare(strict_types=1);

function render_view(string $viewPath, array $params = []): void
{
    $fullPath = APP_ROOT . '/modules/' . ltrim($viewPath, '/');

    if (!is_file($fullPath)) {
        throw new RuntimeException('Vista no encontrada: ' . $viewPath);
    }

    extract($params, EXTR_SKIP);
    require $fullPath;
}
