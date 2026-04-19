<?php

declare(strict_types=1);

function api_auth_logout(): void
{
    if (request_method() !== 'POST') {
        json_error('Metodo no permitido', 'METODO_INVALIDO', 405);
        return;
    }

    if (auth_current_user() === null) {
        json_error('Requiere autenticacion', 'NO_AUTH', 401);
        return;
    }

    require_csrf();

    auth_logout();

    json_success(['logged_out' => true]);
}
