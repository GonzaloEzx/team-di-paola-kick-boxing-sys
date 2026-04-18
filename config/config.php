<?php

declare(strict_types=1);

$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$hostname = strtolower(parse_url('http://' . $host, PHP_URL_HOST) ?: $host);

$isLocal = in_array($hostname, ['localhost', '127.0.0.1', '::1'], true);

$config = [
    'environment' => $isLocal ? 'development' : 'production',
    'url_base' => $isLocal
        ? 'http://localhost:8000'
        : ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http') . '://' . $host),
];

$localConfigPath = __DIR__ . '/config.local.php';

if (is_file($localConfigPath)) {
    $localConfig = require $localConfigPath;

    if (is_array($localConfig)) {
        $config = array_replace($config, $localConfig);
    }
}

$GLOBALS['app_config'] = $config;

defined('ENVIRONMENT') || define('ENVIRONMENT', (string) $config['environment']);
defined('URL_BASE') || define('URL_BASE', rtrim((string) $config['url_base'], '/'));

return $config;
