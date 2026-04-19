<?php

declare(strict_types=1);

function layout_header(array $opts = []): void
{
    $title = isset($opts['title']) ? (string) $opts['title'] : 'Team Di Paola';
    $show_topbar = !isset($opts['topbar']) || $opts['topbar'] !== false;
    $body_class = isset($opts['body_class']) ? (string) $opts['body_class'] : '';
    $user = isset($opts['user']) && is_array($opts['user']) ? $opts['user'] : null;

    $css_tokens_path = APP_ROOT . '/assets/css/tokens.css';
    $css_base_path = APP_ROOT . '/assets/css/base.css';
    $ver_tokens = is_file($css_tokens_path) ? (string) filemtime($css_tokens_path) : '0';
    $ver_base = is_file($css_base_path) ? (string) filemtime($css_base_path) : '0';
    $css_tokens = base_url('assets/css/tokens.css') . '?v=' . $ver_tokens;
    $css_base = base_url('assets/css/base.css') . '?v=' . $ver_base;
    $logo_dpc = base_url('assets/img/logo-dpc.jpg');
    $dashboard_url = base_url('?route=admin/dashboard');
    $logout_url = base_url('?route=auth/logout');

    header('Content-Type: text/html; charset=utf-8');

    echo '<!doctype html>';
    echo '<html lang="es">';
    echo '<head>';
    echo '<meta charset="utf-8">';
    echo '<meta name="viewport" content="width=device-width, initial-scale=1">';
    echo '<title>' . h($title) . ' &middot; Team Di Paola</title>';
    echo '<link rel="preconnect" href="https://fonts.googleapis.com">';
    echo '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>';
    echo '<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Oswald:wght@400;500;600;700&display=swap">';
    echo '<link rel="stylesheet" href="' . h($css_tokens) . '">';
    echo '<link rel="stylesheet" href="' . h($css_base) . '">';
    echo '</head>';
    echo '<body' . ($body_class !== '' ? ' class="' . h($body_class) . '"' : '') . '>';

    if ($show_topbar) {
        echo '<header class="tdp-topbar" role="banner">';
        echo '<a class="tdp-topbar__brand" href="' . h($dashboard_url) . '">';
        echo '<span class="tdp-topbar__mark" style="background-image: url(' . h($logo_dpc) . ');" aria-hidden="true"></span>';
        echo '<span>';
        echo '<span class="tdp-topbar__title">Team Di Paola</span><br>';
        echo '<span class="tdp-topbar__subtitle">Full Contact &middot; Kick Boxing &middot; K1</span>';
        echo '</span>';
        echo '</a>';
        echo '<div class="tdp-topbar__spacer"></div>';

        if ($user !== null) {
            $nombre = isset($user['nombre']) ? (string) $user['nombre'] : '';
            $apellido = isset($user['apellido']) ? (string) $user['apellido'] : '';
            $display = trim($nombre . ' ' . $apellido);

            echo '<div class="tdp-topbar__user">';
            if ($display !== '') {
                echo '<span class="tdp-topbar__user-name">' . h($display) . '</span>';
            }
            echo '<a class="tdp-topbar__logout" href="' . h($logout_url) . '">Salir</a>';
            echo '</div>';
        }
        echo '</header>';
    }

    echo '<main class="tdp-main">';
}

function layout_footer(): void
{
    echo '</main>';
    echo '<footer class="tdp-footer">';
    echo 'Team Di Paola &middot; Disciplina &middot; Competencia &middot; Respeto';
    echo '</footer>';
    echo '</body>';
    echo '</html>';
}
