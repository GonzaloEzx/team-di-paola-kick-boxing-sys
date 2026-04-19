<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "Este script solo corre por CLI.\n");
    exit(1);
}

require __DIR__ . '/../core/bootstrap.php';

$opts = getopt('', ['email:', 'password:', 'nombre::', 'apellido::', 'help']);

if (isset($opts['help'])) {
    echo "Uso: php bin/crear_admin.php --email=X --password=Y [--nombre=Admin] [--apellido=Inicial]\n";
    echo "\n";
    echo "Crea o reactiva un usuario con rol admin. Si el email existe, actualiza password y datos.\n";
    exit(0);
}

$email = isset($opts['email']) ? trim((string) $opts['email']) : '';
$password = isset($opts['password']) ? (string) $opts['password'] : '';
$nombre = isset($opts['nombre']) ? trim((string) $opts['nombre']) : 'Admin';
$apellido = isset($opts['apellido']) ? trim((string) $opts['apellido']) : 'Inicial';

if ($email === '' || $password === '') {
    fwrite(STDERR, "Faltan parametros obligatorios. Usa --help para ver el uso.\n");
    exit(1);
}

if (strlen($password) < 8) {
    fwrite(STDERR, "La password debe tener al menos 8 caracteres.\n");
    exit(1);
}

$email = mb_strtolower($email);

$db = getDB();
$db->beginTransaction();

try {
    $hash = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $db->prepare('SELECT id FROM usuarios WHERE email = :email LIMIT 1');
    $stmt->execute([':email' => $email]);
    $existing = $stmt->fetch();

    if ($existing) {
        $usuario_id = (int) $existing['id'];
        $update = $db->prepare(
            'UPDATE usuarios
             SET password_hash = :h, nombre = :n, apellido = :a, activo = 1
             WHERE id = :id'
        );
        $update->execute([
            ':h' => $hash,
            ':n' => $nombre,
            ':a' => $apellido,
            ':id' => $usuario_id,
        ]);
        echo "Usuario actualizado (id={$usuario_id}).\n";
    } else {
        $insert = $db->prepare(
            'INSERT INTO usuarios (email, password_hash, nombre, apellido, activo)
             VALUES (:e, :h, :n, :a, 1)'
        );
        $insert->execute([
            ':e' => $email,
            ':h' => $hash,
            ':n' => $nombre,
            ':a' => $apellido,
        ]);
        $usuario_id = (int) $db->lastInsertId();
        echo "Usuario creado (id={$usuario_id}).\n";
    }

    $stmt = $db->prepare('SELECT id FROM roles WHERE codigo = :c LIMIT 1');
    $stmt->execute([':c' => 'admin']);
    $rol = $stmt->fetch();

    if (!$rol) {
        throw new RuntimeException('No existe rol admin. Correr migracion 005 antes.');
    }

    $link = $db->prepare(
        'INSERT IGNORE INTO usuario_roles (usuario_id, rol_id) VALUES (:u, :r)'
    );
    $link->execute([':u' => $usuario_id, ':r' => (int) $rol['id']]);

    $db->commit();

    echo "Rol admin asignado. Listo.\n";
    exit(0);

} catch (Throwable $e) {
    $db->rollBack();
    fwrite(STDERR, "Error: " . $e->getMessage() . "\n");
    exit(1);
}
