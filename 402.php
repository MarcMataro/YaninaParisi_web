<?php http_response_code(402); ?>
<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>402 - Pagament necessari / Pago requerido / Payment Required</title>
    <link rel="icon" href="/img/Logo32.png">
    <link rel="stylesheet" href="/css/estils.css">
    <style>
        body{font-family: 'Libre Baskerville', serif;display:flex;align-items:center;justify-content:center;min-height:100vh;background:#fffef8;margin:0}
        .err{max-width:900px;background:#fff;padding:26px;border-radius:12px;box-shadow:0 8px 30px rgba(0,0,0,0.06)}
        h1{margin:0 0 8px;color:#333}
        .home{display:inline-block;margin-top:14px;padding:10px 14px;background:#aa9e6b;color:#fff;border-radius:8px;text-decoration:none}
    </style>
</head>
<body>
    <div class="err">
        <img src="img/Logo.png" alt="Logo" style="height:44px;margin-bottom:8px">
        <h1>402 — Pagament necessari / Pago requerido / Payment Required</h1>
        <p>
            Aquest recurs requereix un pagament o subscripció per accedir.<br>
            Este recurso requiere un pago o suscripción para acceder.<br>
            This resource requires payment or a subscription to access.
        </p>

        <div class="lang">
            <h3>Català</h3>
            <p>Contacta amb l'administrador o revisa els plans disponibles per obtenir accés.</p>
            <h3>Español</h3>
            <p>Póngase en contacto con el administrador o revise los planes disponibles para obtener acceso.</p>
            <h3>English</h3>
            <p>Contact the administrator or check available plans to obtain access.</p>
        </div>

    <a class="home" href="index.php">Tornar a l'inici / Volver al inicio / Back to home</a>
    </div>
</body>
</html>
