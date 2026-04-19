<?php declare(strict_types=1); ?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ingresar &middot; Team Di Paola</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Oswald:wght@400;500;600;700&display=swap">
    <link rel="stylesheet" href="<?= h(base_url('assets/css/tokens.css')) ?>">
    <link rel="stylesheet" href="<?= h(base_url('assets/css/base.css')) ?>">
    <style>
        body {
            min-height: 100vh;
            display: flex;
            align-items: stretch;
            overflow-x: hidden;
        }
        .login-split {
            display: flex;
            width: 100%;
            min-height: 100vh;
        }
        .login-hero {
            flex: 1 1 55%;
            background: linear-gradient(135deg, #1B0C0C 0%, #303841 60%, #3A4750 100%);
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 3rem;
            color: var(--color-text);
        }
        .login-hero::before {
            content: "";
            position: absolute;
            top: -10%;
            right: -15%;
            width: 60%;
            height: 140%;
            background: var(--color-accent);
            transform: rotate(18deg);
            opacity: 0.08;
            pointer-events: none;
        }
        .login-hero::after {
            content: "";
            position: absolute;
            bottom: -20%;
            left: -10%;
            width: 40%;
            height: 60%;
            background: var(--color-ink-deep);
            transform: rotate(-14deg);
            opacity: 0.5;
            pointer-events: none;
        }
        .login-hero__badge {
            position: relative;
            z-index: 2;
            width: 200px;
            height: 200px;
            border-radius: 50%;
            background: #000 center/cover no-repeat;
            border: 3px solid var(--color-accent);
            box-shadow: 0 0 0 6px rgba(246, 201, 14, 0.12), 0 20px 60px -20px rgba(0, 0, 0, 0.8);
            align-self: flex-start;
        }
        .login-hero__slogan {
            position: relative;
            z-index: 2;
        }
        .login-hero__slogan h1 {
            font-size: 3rem;
            line-height: 1;
            margin: 0 0 0.75rem;
            color: var(--color-text);
        }
        .login-hero__slogan h1 span {
            color: var(--color-accent);
            display: block;
        }
        .login-hero__tags {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
            margin-top: 1rem;
        }
        .login-hero__tag {
            font-family: var(--font-display);
            font-size: 0.8rem;
            letter-spacing: 0.15em;
            text-transform: uppercase;
            padding: 0.3rem 0.7rem;
            border: 1px solid var(--color-accent);
            color: var(--color-accent);
            border-radius: var(--radius-sm);
        }
        .login-form-wrap {
            flex: 1 1 45%;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            background: var(--color-ink);
        }
        .login-card {
            width: 100%;
            max-width: 380px;
        }
        .login-card__eyebrow {
            font-family: var(--font-display);
            font-size: 0.75rem;
            letter-spacing: 0.2em;
            text-transform: uppercase;
            color: var(--color-accent);
            margin-bottom: 0.5rem;
        }
        .login-card h2 {
            font-size: 2rem;
            margin: 0 0 0.5rem;
            color: var(--color-text);
        }
        .login-card p.sub {
            color: var(--color-text-dim);
            font-size: 0.9rem;
            margin: 0 0 2rem;
        }
        .login-card__divider {
            width: 40px;
            height: 3px;
            background: var(--color-accent);
            margin-bottom: 1.25rem;
        }
        .login-footer {
            margin-top: 2rem;
            font-size: 0.7rem;
            letter-spacing: 0.15em;
            text-transform: uppercase;
            color: var(--color-text-mute);
        }
        @media (max-width: 860px) {
            .login-split { flex-direction: column; }
            .login-hero {
                flex: 0 0 auto;
                padding: 2rem 1.5rem;
                min-height: 0;
            }
            .login-hero__badge {
                width: 120px;
                height: 120px;
                border-width: 2px;
            }
            .login-hero__slogan h1 { font-size: 2rem; }
            .login-form-wrap { padding: 2rem 1.5rem; }
        }
    </style>
</head>
<body>
    <div class="login-split">
        <aside class="login-hero" aria-hidden="true">
            <span class="login-hero__badge" style="background-image: url('<?= h(base_url('assets/img/logo-full.jpg')) ?>');"></span>
            <div class="login-hero__slogan">
                <h1>Disciplina.<br><span>Competencia.</span></h1>
                <div class="login-hero__tags">
                    <span class="login-hero__tag">Full Contact</span>
                    <span class="login-hero__tag">Kick Boxing</span>
                    <span class="login-hero__tag">K1</span>
                </div>
            </div>
        </aside>

        <main class="login-form-wrap">
            <div class="login-card">
                <div class="login-card__eyebrow">Team Di Paola</div>
                <h2>Ingresar</h2>
                <div class="login-card__divider"></div>
                <p class="sub">Usa tus credenciales del sistema para continuar.</p>

                <?php if (!empty($error)): ?>
                    <div class="tdp-alert tdp-alert--error" role="alert"><?= h($error) ?></div>
                <?php endif; ?>

                <form method="post" action="<?= h(base_url('?route=auth/login')) ?>" autocomplete="on">
                    <input type="hidden" name="csrf_token" value="<?= h($csrf_token) ?>">

                    <div class="tdp-field">
                        <label for="email">Email</label>
                        <input
                            class="tdp-input"
                            type="email"
                            id="email"
                            name="email"
                            value="<?= h($email ?? '') ?>"
                            required
                            autofocus
                            autocomplete="username">
                    </div>

                    <div class="tdp-field">
                        <label for="password">Contrasena</label>
                        <input
                            class="tdp-input"
                            type="password"
                            id="password"
                            name="password"
                            required
                            autocomplete="current-password">
                    </div>

                    <button class="tdp-btn tdp-btn--block" type="submit">Ingresar</button>
                </form>

                <div class="login-footer">Acceso restringido &middot; Personal autorizado</div>
            </div>
        </main>
    </div>
</body>
</html>
