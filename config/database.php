<?php

declare(strict_types=1);

function getDB(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $environment = defined('ENVIRONMENT') ? ENVIRONMENT : 'production';
    $appConfig = $GLOBALS['app_config'] ?? [];
    $databaseConfig = isset($appConfig['database']) && is_array($appConfig['database'])
        ? $appConfig['database']
        : [];

    $defaults = $environment === 'development'
        ? [
            'host' => 'localhost',
            'name' => 'team_di_paola_db',
            'user' => 'root',
            'pass' => '',
        ]
        : [
            'host' => '',
            'name' => '',
            'user' => '',
            'pass' => '',
        ];

    $host = envValue('TDP_DB_HOST', (string) ($databaseConfig['host'] ?? $defaults['host']));
    $name = envValue('TDP_DB_NAME', (string) ($databaseConfig['name'] ?? $defaults['name']));
    $user = envValue('TDP_DB_USER', (string) ($databaseConfig['user'] ?? $defaults['user']));
    $pass = envValue('TDP_DB_PASS', (string) ($databaseConfig['pass'] ?? $defaults['pass']));

    if ($host === '' || $name === '' || $user === '') {
        throw new RuntimeException('Configuracion de base de datos incompleta.');
    }

    $dsn = sprintf('mysql:host=%s;dbname=%s;charset=utf8mb4', $host, $name);

    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);

    return $pdo;
}

function envValue(string $key, string $default): string
{
    $value = getenv($key);

    if ($value === false && isset($_ENV[$key])) {
        $value = $_ENV[$key];
    }

    return $value === false ? $default : (string) $value;
}
