<?php http_response_code(503); ?>
<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>503 - Service Unavailable / Servicio no disponible / Servicio no disponible</title>
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
        <h1>503 — Service Unavailable / Servicio no disponible / Servicio no disponible</h1>
        <p>
            El servidor no està disponible temporalment, generalment per manteniment o sobrecàrrega.<br>
            El servidor no está disponible temporalmente, generalmente por mantenimiento o sobrecarga.<br>
            The server is temporarily unavailable, usually due to maintenance or overload.
        </p>

        <div class="lang">
            <h3>Català</h3>
            <p>El servei no està disponible ara mateix. Si us plau, torni-ho a intentar més tard.</p>

            <h3>Español</h3>
            <p>El servicio no está disponible en este momento. Por favor, inténtelo de nuevo más tarde.</p>

            <h3>English</h3>
            <p>The server is currently unavailable (overloaded or down for maintenance). Please try again later.</p>
        </div>

    <a class="home" href="index.php">Tornar a l'inici / Volver al inicio / Back to home</a>
    </div>
</body>
</html>
