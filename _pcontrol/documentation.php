<?php
session_start();

// Requerir autenticación: redirige a index si no hay sesión válida
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: index.php');
    exit;
}

require_once 'includes/role_check.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Documentación - Panel de Control</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@7.0.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Libre+Baskerville:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet">
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
    <link rel="icon" type="image/png" sizes="32x32" href="../img/Logo32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="../img/Logo16.png">
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
                <p class="small-muted">Guía rápida pensada para el uso del panel: organizada por tareas diarias y apartados administrativos.</p>

                <section class="doc-section">
                    <h3>Empezar</h3>
                    <ul>
                        <li><a href="docs/intro.php">Introducción</a> — qué es el panel y primeros pasos</li>
                        <li><a href="docs/arquitectura.php">Arquitectura web</a> — diagrama visual de la estructura del sitio</li>
                    </ul>
                </section>

                <section class="doc-section">
                    <h3>Tareas diarias</h3>
                    <ul>
                        <li><a href="docs/pacients.php">Pacientes</a> — añadir/editar pacientes, historial y notas</li>
                        <li><a href="docs/sessions.php">Citas & Sesiones</a> — crear y gestionar citas, marcar asistencia</li>
                        <li><a href="docs/facturacio.php">Facturación</a> — facturas, estados de pago y generación de PDF</li>
                    </ul>
                </section>

                <section class="doc-section">
                    <h3>Contenidos y Media</h3>
                    <ul>
                        <li><a href="docs/media.php">Media</a> — subir imágenes, vídeos y gestionar la biblioteca</li>
                        <li><a href="docs/blog.php">Blog y Entradas</a> — crear entradas, categorías y etiquetas</li>
                        <li><a href="docs/faqs.php">FAQs</a> — preguntas frecuentes y respuestas</li>
                        <li><a href="docs/ressenyes.php">Reseñas</a> — gestionar reseñas y testimonios</li>
                    </ul>
                </section>

                <section class="doc-section">
                    <h3>SEO y Web pública</h3>
                    <ul>
                        <li><a href="docs/seo_general.php">SEO General</a> — metadatos generales e indexación</li>
                        <li><a href="docs/seo_onpage.php">On-page</a> — títulos, descripciones y contenido por página</li>
                        <li><a href="docs/seo_offpage.php">Off-page</a> — enlaces y directorios externos</li>
                    </ul>
                </section>

                <!-- <section class="doc-section">
                    <h3>Configuración y Preferencias</h3>
                    <ul>
                        <li><a href="configuracion.php">Configuración</a> — datos de la consulta, contacto y preferencias</li>
                        <li><a href="gtarifas.php">Tarifas</a> — gestionar tarifas y servicios</li>
                        <li><a href="logout.php">Seguridad</a> — cerrar sesión y buenas prácticas</li>
                    </ul>
                </section> -->
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
