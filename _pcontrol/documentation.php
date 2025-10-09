<?php
session_start();

// Requerir autenticación: redirige a index si no hay sesión válida
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Documentación - Panel de Control</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="css/configuracion.css">
    <style>
        /* Contenedor: casi ancho completo pero con límite razonable en pantalles grandes */
        .doc-container { max-width:96%; width:96%; margin:24px auto; padding:20px; font-family: inherit; }

        /* Mantener los paneles transparentes pero con separación y buen espaciado */
        .doc-index { background:transparent; border-radius:10px; padding:18px; box-shadow:none; }
        .doc-section { margin-top:26px; padding:18px; background:transparent; border-radius:10px; box-shadow:none; }

        /* Tipografía coherente con el resto del panel */
        .doc-index h2, .doc-section h3 { font-family: 'Libre Baskerville', serif; color: var(--color-dark); }
        .doc-index h2 { font-size: 1.35rem; margin-bottom:8px; }
        .doc-section h3 { font-size: 1.05rem; margin-bottom:10px; font-weight:700; }

        /* Texto y microtipografía */
        .doc-index p, .doc-section p { color: #333; font-size: 0.98rem; line-height:1.7; margin-bottom:12px; }
        .small-muted { color:#666; font-size:0.95rem; }

        /* Listas con espai i claredat */
        .doc-index ul, .doc-section ul { margin: 8px 0 14px 20px; padding:0; }
        .doc-index ul li, .doc-section ul li { margin-bottom:8px; color:#444; }

        /* Enllaços amb color d'accent i lleugera negrita per destacar a l'índex */
        .doc-index a { color: var(--color-accent); text-decoration:none; font-weight:600; }
        .doc-index a:hover { text-decoration:underline; }
        .doc-section a { color: var(--color-accent); }

        /* Codi i blocs de codi */
        code { background: #f5f5f5; padding: 2px 6px; border-radius:6px; font-family: Menlo, Monaco, monospace; font-size:0.95rem; }
        pre { background:#f5f5f5; padding:12px; border-radius:8px; overflow:auto; font-family: Menlo, Monaco, monospace; font-size:0.95rem; }

        /* Millor separació entre seccions sense ombres */
        .doc-section + .doc-section { margin-top: 20px; border-top: 1px solid rgba(0,0,0,0.03); padding-top:22px; }

        /* Responsivitat: ajustar padding en pantalles petites */
        @media (max-width: 900px) {
            .doc-container { width: calc(100% - 24px); padding:12px; }
            .doc-index h2 { font-size: 1.2rem; }
            .doc-section h3 { font-size: 1rem; }
        }
    </style>
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>

    <div class="main-content">
        <header class="top-bar">
            <div class="top-bar-left">
                <button class="menu-toggle" id="menuToggle"><i class="fas fa-bars"></i></button>
                <div class="top-bar-info">
                    <h1>Documentación interna</h1>
                    <p class="date-today">Índice de contenidos del manual de uso del panel de control</p>
                </div>
            </div>
        </header>

        <div class="doc-container">
            <div class="doc-index">
                <h2>Índice de contenidos</h2>
                <ul>
                    <li><a href="docs/intro.php">1. Introducción</a></li>
                </ul>
            </div>

        </div>
    </div>

    <script>
        // Maneja solo anclas locales (href que empiece por '#') para scroll suave.
        document.querySelectorAll('.doc-index a').forEach(a => {
            const href = a.getAttribute('href') || '';
            if (href.startsWith('#')) {
                a.addEventListener('click', function(e){
                    e.preventDefault();
                    const target = document.querySelector(href);
                    if (target) target.scrollIntoView({behavior:'smooth'});
                });
            }
            // Si no es una ancla local (p. ej. 'docs/intro.php'), permitimos la navegación normal.
        });
    </script>
</body>
</html>
