<?php http_response_code(405); ?>
<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>405 - Mètode no permès / Método no permitido / Method Not Allowed</title>
    <link rel="icon" href="/img/Logo32.png">
    <link rel="stylesheet" href="/css/estils.css">
    <style>
        body{font-family: 'Libre Baskerville', serif;display:flex;align-items:center;justify-content:center;min-height:100vh;background:#fff7f6;margin:0}
        .err{max-width:900px;background:#fff;padding:26px;border-radius:12px;box-shadow:0 8px 30px rgba(0,0,0,0.06)}
        h1{margin:0 0 8px;color:#333}
        .home{display:inline-block;margin-top:14px;padding:10px 14px;background:#aa9e6b;color:#fff;border-radius:8px;text-decoration:none}
    </style>
</head>
<body>
    <div class="err">
        <img src="img/Logo.png" alt="Logo" style="height:44px;margin-bottom:8px">
        <h1>405 — Mètode no permès / Método no permitido / Method Not Allowed</h1>
        <p>
            El mètode HTTP utilitzat per fer la sol·licitud no està permès per aquest recurs.<br>
            El método HTTP utilizado para realizar la solicitud no está permitido para este recurso.<br>
            The HTTP method used for the request is not allowed for this resource.
        </p>

        <div class="lang">
            <h3>Català</h3>
            <p>Prova una altra acció o torna a la pàgina anterior. Si creus que això és un error, contacta amb l'administrador.</p>

            <h3>Español</h3>
            <p>Intente otra acción o vuelva a la página anterior. Si cree que esto es un error, contacte con el administrador.</p>

            <h3>English</h3>
            <p>The HTTP method used is not allowed for this resource. Try a different action or go back. Contact the administrator if the problem persists.</p>
        </div>

    <a class="home" href="index.php">Tornar a l'inici / Volver al inicio / Back to home</a>
    </div>
</body>
</html>
