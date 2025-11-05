<?php http_response_code(400); ?>
<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>400 - Petició incorrecta / Petición incorrecta / Bad Request</title>
    <link rel="icon" href="/img/Logo32.png">
    <link rel="stylesheet" href="/css/estils.css">
    <style>
        body{font-family: 'Libre Baskerville', serif;display:flex;align-items:center;justify-content:center;min-height:100vh;background:#f7f7f7;margin:0}
        .err{max-width:900px;background:#fff;padding:28px;border-radius:12px;box-shadow:0 8px 30px rgba(0,0,0,0.06);text-align:left}
        h1{margin:0 0 12px;font-size:2rem;color:#333}
        .lang{margin-top:14px;padding-top:12px;border-top:1px dashed #eee}
        .lang h3{margin:6px 0;color:#666;font-size:0.95rem}
        p{color:#444;line-height:1.5}
        .home{display:inline-block;margin-top:16px;padding:10px 14px;background:#aa9e6b;color:#fff;border-radius:8px;text-decoration:none}
    </style>
</head>
<body>
    <div class="err">
        <img src="img/Logo.png" alt="Logo" style="height:44px;margin-bottom:8px">
        <h1>400 — Petició incorrecta / Petición incorrecta / Bad Request</h1>
        <p>
            La sol·licitud enviada al servidor no és vàlida o està mal formada.<br>
            La solicitud enviada al servidor no es válida o está mal formada.<br>
            The request sent to the server is invalid or malformed.
        </p>

        <div class="lang">
            <h3>Català</h3>
            <p>Ho sentim, la teva petició no s'ha pogut processar perquè és incorrecta. Revisa la URL o torna a intentar-ho.</p>

            <h3>Español</h3>
            <p>Lo sentimos, su solicitud no pudo procesarse porque es incorrecta. Revise la URL o inténtelo de nuevo.</p>

            <h3>English</h3>
            <p>Sorry, your request could not be processed because it was malformed. Please check the URL or try again.</p>
        </div>

        <a class="home" href="index.php">Tornar a l'inici</a>
    </div>
</body>
</html>
