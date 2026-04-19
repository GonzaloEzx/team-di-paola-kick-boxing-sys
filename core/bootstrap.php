<?php

declare(strict_types=1);

define('APP_ROOT', dirname(__DIR__));

require APP_ROOT . '/config/config.php';
require APP_ROOT . '/config/database.php';
require APP_ROOT . '/core/helpers.php';
require APP_ROOT . '/core/request.php';
require APP_ROOT . '/core/response.php';
require APP_ROOT . '/core/view.php';
require APP_ROOT . '/core/auth.php';
require APP_ROOT . '/core/permissions.php';
require APP_ROOT . '/core/csrf.php';
require APP_ROOT . '/core/router.php';

date_default_timezone_set('America/Argentina/Buenos_Aires');
