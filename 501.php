<?php http_response_code(501); ?>
<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>501 - No implementat / No implementado / Not Implemented</title>
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
        <h1>501 — No implementat / No implementado / Not Implemented</h1>
        <p>
            El servidor no suporta la funcionalitat sol·licitada per completar la petició.<br>
            El servidor no soporta la funcionalidad solicitada para completar la petición.<br>
            The server does not support the functionality required to fulfill the request.
        </p>

        <div class="lang">
            <h3>Català</h3>
            <p>Alguna funcionalitat requerida no està disponible en aquest servidor. Torna a intentar-ho més tard o contacta amb l'equip tècnic.</p>

            <h3>Español</h3>
            <p>Alguna funcionalidad requerida no está disponible en este servidor. Inténtelo de nuevo más tarde o contacte con el equipo técnico.</p>

            <h3>English</h3>
            <p>The server does not support the functionality required to fulfill the request. Please try again later or contact support.</p>
        </div>

    <a class="home" href="index.php">Tornar a l'inici / Volver al inicio / Back to home</a>
    </div>
</body>
</html>
