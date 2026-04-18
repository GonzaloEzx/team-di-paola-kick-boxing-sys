<?php

declare(strict_types=1);

// Copiar como config.local.php solo si se necesitan overrides locales.
// config.local.php no debe subirse al repositorio.
return [
    "environment" => "development",
    "url_base" => "http://localhost:8000",
    "database" => [
        "host" => "localhost",
        "name" => "team_di_paola_db",
        "user" => "root",
        "pass" => "",
    ],
];
