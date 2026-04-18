<?php

declare(strict_types=1);
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= h($systemName ?? 'Sistema Team Di Paola') ?></title>
</head>
<body>
    <main>
        <h1><?= h($systemName ?? 'Sistema Team Di Paola') ?> activo</h1>
        <p>ENV: <?= h($environment ?? 'unknown') ?></p>
        <p>DB: <?= h(strtoupper($dbStatus ?? 'unknown')) ?></p>
    </main>
</body>
</html>
