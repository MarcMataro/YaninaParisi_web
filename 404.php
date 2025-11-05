<?php http_response_code(404); ?>
<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>404 - Pàgina no trobada / Página no encontrada / Page not found</title>
    <link rel="icon" href="/img/Logo32.png">
    <link rel="stylesheet" href="/css/estils.css">
    <style>
        body{font-family: 'Libre Baskerville', serif;display:flex;align-items:center;justify-content:center;min-height:100vh;background:#f8fafb;margin:0}
        .err{max-width:900px;background:#fff;padding:28px;border-radius:12px;box-shadow:0 8px 30px rgba(0,0,0,0.06);text-align:left}
        h1{margin:0 0 12px;font-size:2rem;color:#333}
        .lang{margin-top:14px;padding-top:12px;border-top:1px dashed #eee}
        .home{display:inline-block;margin-top:16px;padding:10px 14px;background:#aa9e6b;color:#fff;border-radius:8px;text-decoration:none}
    </style>
</head>
<body>
    <div class="err">
        <img src="img/Logo.png" alt="Logo" style="height:44px;margin-bottom:8px">
        <h1>404 — Pàgina no trobada / Página no encontrada / Page not found</h1>
        <p>
            La pàgina que busques no s'ha trobat al servidor.<br>
            La página que buscas no se ha encontrado en el servidor.<br>
            The page you are looking for was not found on the server.
        </p>

        <div class="lang">
            <h3>Català</h3>
            <p>Comprova que l'adreça sigui correcta o torna a la pàgina principal.</p>
            <h3>Español</h3>
            <p>Compruebe que la dirección sea correcta o vuelva a la página principal.</p>
            <h3>English</h3>
            <p>Check that the address is correct or return to the home page.</p>
        </div>

    <a class="home" href="index.php">Tornar a l'inici / Volver al inicio / Back to home</a>
    </div>
</body>
</html>
