<?php http_response_code(500); ?>
<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>500 - Error intern del servidor / Error interno del servidor / Internal Server Error</title>
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
        <h1>500 — Error intern del servidor / Error interno del servidor / Internal Server Error</h1>
        <p>
            S'ha produït un error inesperat en el servidor.<br>
            Se ha producido un error inesperado en el servidor.<br>
            An unexpected error occurred on the server.
        </p>

        <div class="lang">
            <h3>Català</h3>
            <p>Estem treballant per arreglar-ho. Torna a intentar-ho d'aquí uns minuts.</p>
            <h3>Español</h3>
            <p>Estamos trabajando para solucionarlo. Por favor, inténtelo de nuevo en unos minutos.</p>
            <h3>English</h3>
            <p>We're working to fix this. Please try again in a few minutes.</p>
        </div>

    <a class="home" href="index.php">Tornar a l'inici / Volver al inicio / Back to home</a>
    </div>
</body>
</html>
