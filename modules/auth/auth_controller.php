<?php

declare(strict_types=1);

function auth_login_form(): void
{
    auth_start_session();

    if (auth_current_user() !== null) {
        redirect(base_url('?route=admin/dashboard'));
    }

    render_view('auth/login_view.php', [
        'csrf_token' => csrf_token(),
        'error' => '',
        'email' => '',
    ]);
}

function auth_login_submit(): void
{
    auth_start_session();

    $email = trim((string) post_param('email', ''));
    $password = (string) post_param('password', '');
    $csrf = (string) post_param('csrf_token', '');

    if (!csrf_verify($csrf)) {
        render_view('auth/login_view.php', [
            'csrf_token' => csrf_token(),
            'error' => 'Token CSRF invalido. Recarga la pagina.',
            'email' => $email,
        ]);
        return;
    }

    $result = auth_attempt($email, $password);

    if (!$result['ok']) {
        $error = $result['code'] === 'USUARIO_INACTIVO'
            ? 'El usuario esta inactivo. Consulta con un administrador.'
            : 'Email o contrasena incorrectos.';

        render_view('auth/login_view.php', [
            'csrf_token' => csrf_token(),
            'error' => $error,
            'email' => $email,
        ]);
        return;
    }

    redirect(base_url('?route=admin/dashboard'));
}

function auth_logout_handler(): void
{
    auth_logout();
    redirect(base_url('?route=auth/login'));
}
