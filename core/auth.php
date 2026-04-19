<?php

declare(strict_types=1);

function auth_start_session(): void
{
    if (PHP_SAPI === 'cli') {
        return;
    }

    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    $is_production = defined('ENVIRONMENT') && ENVIRONMENT === 'production';

    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => '',
        'secure' => $is_production,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);

    session_name('TDPSESSID');
    session_start();
}

function auth_attempt(string $email, string $password): array
{
    $email = trim(mb_strtolower($email));

    if ($email === '' || $password === '') {
        return ['ok' => false, 'code' => 'LOGIN_INVALIDO'];
    }

    $db = getDB();
    $stmt = $db->prepare(
        'SELECT id, email, password_hash, nombre, apellido, activo
         FROM usuarios
         WHERE email = :email
         LIMIT 1'
    );
    $stmt->execute([':email' => $email]);
    $user = $stmt->fetch();

    if (!$user || $user['password_hash'] === null) {
        return ['ok' => false, 'code' => 'LOGIN_INVALIDO'];
    }

    if (!password_verify($password, (string) $user['password_hash'])) {
        return ['ok' => false, 'code' => 'LOGIN_INVALIDO'];
    }

    if ((int) $user['activo'] !== 1) {
        return ['ok' => false, 'code' => 'USUARIO_INACTIVO'];
    }

    $roles = auth_roles_for_user((int) $user['id']);

    auth_login_success(
        (int) $user['id'],
        (string) $user['nombre'],
        (string) $user['apellido'],
        $roles
    );

    auth_touch_last_access((int) $user['id']);

    return [
        'ok' => true,
        'usuario_id' => (int) $user['id'],
        'nombre' => (string) $user['nombre'],
        'apellido' => (string) $user['apellido'],
        'roles' => $roles,
    ];
}

function auth_login_success(int $usuario_id, string $nombre, string $apellido, array $roles): void
{
    auth_start_session();
    session_regenerate_id(true);

    $_SESSION['usuario_id'] = $usuario_id;
    $_SESSION['nombre'] = $nombre;
    $_SESSION['apellido'] = $apellido;
    $_SESSION['roles'] = $roles;
    $_SESSION['login_at'] = time();
}

function auth_logout(): void
{
    auth_start_session();

    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params['path'],
            $params['domain'],
            $params['secure'],
            $params['httponly']
        );
    }

    session_destroy();
}

function auth_current_user(): ?array
{
    auth_start_session();

    if (!isset($_SESSION['usuario_id'])) {
        return null;
    }

    return [
        'id' => (int) $_SESSION['usuario_id'],
        'nombre' => (string) ($_SESSION['nombre'] ?? ''),
        'apellido' => (string) ($_SESSION['apellido'] ?? ''),
        'roles' => (array) ($_SESSION['roles'] ?? []),
    ];
}

function auth_roles_for_user(int $usuario_id): array
{
    $db = getDB();
    $stmt = $db->prepare(
        'SELECT r.codigo
         FROM usuario_roles ur
         INNER JOIN roles r ON r.id = ur.rol_id
         WHERE ur.usuario_id = :uid AND r.activo = 1'
    );
    $stmt->execute([':uid' => $usuario_id]);

    $codigos = [];
    while ($row = $stmt->fetch()) {
        $codigos[] = (string) $row['codigo'];
    }

    return $codigos;
}

function auth_touch_last_access(int $usuario_id): void
{
    $db = getDB();
    $stmt = $db->prepare('UPDATE usuarios SET ultimo_acceso_at = NOW() WHERE id = :uid');
    $stmt->execute([':uid' => $usuario_id]);
}
