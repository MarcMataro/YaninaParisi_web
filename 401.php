<?php http_response_code(401); ?>
<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>401 - No autoritzat / No autorizado / Unauthorized</title>
    <link rel="icon" href="/img/Logo32.png">
    <link rel="stylesheet" href="/css/estils.css">
    <style>
        body{font-family: 'Libre Baskerville', serif;display:flex;align-items:center;justify-content:center;min-height:100vh;background:#fafafa;margin:0}
        .err{max-width:900px;background:#fff;padding:26px;border-radius:12px;box-shadow:0 8px 30px rgba(0,0,0,0.06)}
        h1{margin:0 0 8px;color:#333}
        .lang{margin-top:12px}
        .home{display:inline-block;margin-top:14px;padding:10px 14px;background:#aa9e6b;color:#fff;border-radius:8px;text-decoration:none}
    </style>
</head>
<body>
    <div class="err">
        <img src="img/Logo.png" alt="Logo" style="height:44px;margin-bottom:8px">
        <h1>401 — No autoritzat / No autorizado / Unauthorized</h1>
        <p>
            Accés denegat. Cal identificar-se per accedir a aquest recurs.<br>
            Acceso denegado. Es necesario identificarse para acceder a este recurso.<br>
            Access denied. Authentication is required to access this resource.
        </p>

        <div class="lang">
            <h3>Català</h3>
            <p>Per accedir a aquesta pàgina cal iniciar sessió o tenir permisos adequats.</p>
            <h3>Español</h3>
            <p>Para acceder a esta página es necesario identificarse o tener permisos adecuados.</p>
            <h3>English</h3>
            <p>Access denied. You need to authenticate or have the proper permissions to view this resource.</p>
        </div>

    <a class="home" href="index.php">Tornar a l'inici / Volver al inicio / Back to home</a>
    </div>
</body>
</html>
