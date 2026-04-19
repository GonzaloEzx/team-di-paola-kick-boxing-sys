<?php declare(strict_types=1); ?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ingresar &middot; Team Di Paola</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; }
        html, body { margin: 0; padding: 0; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            background: #0f0f12;
            color: #f3f3f3;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
        }
        .card {
            background: #17171c;
            border: 1px solid #26262e;
            border-radius: 14px;
            padding: 2rem 2rem 1.75rem;
            width: 100%;
            max-width: 380px;
            box-shadow: 0 10px 40px -20px rgba(0, 0, 0, 0.8);
        }
        .brand {
            font-size: 0.75rem;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: #d4a656;
            margin-bottom: 0.5rem;
            font-weight: 600;
        }
        h1 {
            margin: 0 0 0.25rem;
            font-size: 1.375rem;
            font-weight: 600;
            letter-spacing: -0.01em;
        }
        p.sub {
            margin: 0 0 1.5rem;
            font-size: 0.875rem;
            color: #8a8a92;
        }
        label {
            display: block;
            font-size: 0.8125rem;
            color: #c7c7cc;
            margin-bottom: 0.375rem;
        }
        input[type="email"], input[type="password"] {
            width: 100%;
            padding: 0.625rem 0.75rem;
            border-radius: 7px;
            border: 1px solid #2a2a32;
            background: #0f0f12;
            color: #f3f3f3;
            font-size: 0.9375rem;
            margin-bottom: 1rem;
            font-family: inherit;
            transition: border-color 120ms ease;
        }
        input:focus {
            outline: none;
            border-color: #d4a656;
        }
        button {
            width: 100%;
            padding: 0.6875rem;
            border-radius: 7px;
            border: 0;
            background: #d4a656;
            color: #0f0f12;
            font-size: 0.9375rem;
            font-weight: 600;
            cursor: pointer;
            font-family: inherit;
            transition: background 120ms ease;
        }
        button:hover { background: #e1b467; }
        .error {
            padding: 0.625rem 0.75rem;
            border-radius: 7px;
            background: rgba(200, 80, 80, 0.12);
            border: 1px solid rgba(200, 80, 80, 0.35);
            color: #f0b4b4;
            font-size: 0.8125rem;
            margin-bottom: 1rem;
        }
        footer {
            margin-top: 1.25rem;
            font-size: 0.75rem;
            color: #6a6a72;
            text-align: center;
        }
    </style>
</head>
<body>
    <main class="card">
        <div class="brand">Team Di Paola</div>
        <h1>Ingresar</h1>
        <p class="sub">Usa las credenciales del sistema para continuar.</p>

        <?php if (!empty($error)): ?>
            <div class="error" role="alert"><?= h($error) ?></div>
        <?php endif; ?>

        <form method="post" action="<?= h(base_url('?route=auth/login')) ?>" autocomplete="on">
            <input type="hidden" name="csrf_token" value="<?= h($csrf_token) ?>">

            <label for="email">Email</label>
            <input
                type="email"
                id="email"
                name="email"
                value="<?= h($email ?? '') ?>"
                required
                autofocus
                autocomplete="username">

            <label for="password">Contrasena</label>
            <input
                type="password"
                id="password"
                name="password"
                required
                autocomplete="current-password">

            <button type="submit">Ingresar</button>
        </form>
    </main>
</body>
</html>
