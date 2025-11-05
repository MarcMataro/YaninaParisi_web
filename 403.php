<?php http_response_code(403); ?>
<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>403 - Accés prohibit / Acceso prohibido / Forbidden</title>
    <link rel="icon" href="/img/Logo32.png">
    <link rel="stylesheet" href="/css/estils.css">
    <style>
        body{font-family: 'Libre Baskerville', serif;display:flex;align-items:center;justify-content:center;min-height:100vh;background:#fff;margin:0}
        .err{max-width:900px;background:#fff;padding:26px;border-radius:12px;box-shadow:0 8px 30px rgba(0,0,0,0.06)}
        h1{margin:0 0 8px;color:#333}
        .home{display:inline-block;margin-top:14px;padding:10px 14px;background:#aa9e6b;color:#fff;border-radius:8px;text-decoration:none}
    </style>
</head>
<body>
    <div class="err">
        <img src="img/Logo.png" alt="Logo" style="height:44px;margin-bottom:8px">
        <h1>403 — Accés prohibit / Acceso prohibido / Forbidden</h1>
        <p>
            No tens permisos per accedir a aquest recurs.<br>
            No tienes permisos para acceder a este recurso.<br>
            You do not have permission to access this resource.
        </p>

        <div class="lang">
            <h3>Català</h3>
            <p>Si creus que és un error, posa't en contacte amb l'administrador.</p>
            <h3>Español</h3>
            <p>Si cree que esto es un error, póngase en contacto con el administrador.</p>
            <h3>English</h3>
            <p>If you believe this is an error, contact the site administrator.</p>
        </div>

    <a class="home" href="index.php">Tornar a l'inici / Volver al inicio / Back to home</a>
    </div>
</body>
</html>
