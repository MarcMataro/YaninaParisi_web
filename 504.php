<?php http_response_code(504); ?>
<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>504 - Gateway Timeout / Tiempo de espera de la puerta de enlace / Gateway Timeout</title>
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
        <h1>504 — Gateway Timeout / Tiempo de espera de la puerta de enlace / Gateway Timeout</h1>
        <p>
            El servidor no ha rebut una resposta a temps d'un servidor amfitrió upstream.<br>
            El servidor no ha recibido una respuesta a tiempo de un servidor upstream.<br>
            The server did not receive a timely response from an upstream server.
        </p>

        <div class="lang">
            <h3>Català</h3>
            <p>Hi ha hagut un retard en la comunicació amb un servei extern. Torna-ho a provar d'aquí uns minuts.</p>

            <h3>Español</h3>
            <p>Se ha producido un retraso en la comunicación con un servicio externo. Por favor, inténtelo de nuevo en unos minutos.</p>

            <h3>English</h3>
            <p>An upstream service failed to respond in time. Please try again in a few minutes.</p>
        </div>

    <a class="home" href="index.php">Tornar a l'inici / Volver al inicio / Back to home</a>
    </div>
</body>
</html>
