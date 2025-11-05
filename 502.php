<?php http_response_code(502); ?>
<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>502 - Bad Gateway / Puerta de enlace incorrecta / Bad Gateway</title>
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
        <h1>502 — Bad Gateway / Puerta de enlace incorrecta / Bad Gateway</h1>
        <p>
            El servidor ha rebut una resposta no vàlida d'un servidor amfitrió upstream.<br>
            El servidor ha recibido una respuesta no válida de un servidor upstream.<br>
            The server received an invalid response from an upstream server.
        </p>

        <div class="lang">
            <h3>Català</h3>
            <p>Hi ha hagut un problema comunicant amb un servei extern. Torna-ho a provar d'aquí uns instants.</p>

            <h3>Español</h3>
            <p>Hubo un problema comunicando con un servicio externo. Por favor, inténtelo de nuevo en unos instantes.</p>

            <h3>English</h3>
            <p>There was a problem communicating with an upstream service. Please try again shortly.</p>
        </div>

    <a class="home" href="index.php">Tornar a l'inici / Volver al inicio / Back to home</a>
    </div>
</body>
</html>
