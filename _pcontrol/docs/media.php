<?php
session_start();

// Comprobar sesión y redirigir si no está autenticada
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: ../index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Gestor de Media — Guía de Usuario</title>
    <link rel="stylesheet" href="../css/dashboard.css">
    <link rel="stylesheet" href="../css/configuracion.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Libre+Baskerville:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet">
    <style>
        html, body { font-family: 'Libre Baskerville', serif; font-size:16px; }
        .docs-hero { padding: 26px 22px; }
        .docs-hero h1 { margin:0 0 8px 0; font-size:1.6rem; }
        .docs-hero p.lead { color:var(--color-dark); margin-bottom:6px; }
        .docs-grid { display:flex; gap:24px; align-items:flex-start; }
        .docs-index { flex:0 0 300px; max-width:300px; }
        .docs-index ul { list-style:none; padding-left:0; }
        .docs-index a { display:block; padding:8px 10px; border-radius:8px; color:var(--color-dark); text-decoration:none; }
        .docs-index a:hover { background: rgba(var(--color-light),0.18); color:var(--color-accent); }
        .doc-body { flex:1 1 auto; }
        .doc-body h2 { font-size:1.15rem; margin-top:18px; }
        .doc-body p, .doc-body li { color:#333; font-size:1rem; line-height:1.7; }
        code { background:#f5f5f5; padding:2px 6px; border-radius:6px; }
        pre.codeblock { background:#f7f7f7; padding:12px; border-radius:8px; overflow:auto; }
        @media (max-width:900px) { .docs-grid { flex-direction:column; } .docs-index { max-width:none; } }
    </style>
</head>
<body>
    <link rel="icon" type="image/png" sizes="32x32" href="../../img/Logo32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="../../img/Logo16.png">
    <?php include 'sidebar.php'; ?>

    <div class="main-content">
        <header class="top-bar">
            <div class="top-bar-left">
                <button class="menu-toggle" id="menuToggle"><i class="fas fa-bars"></i></button>
                <div class="top-bar-info">
                    <h1>Documentación interna</h1>
                    <p class="date-today">Guía del Gestor de Media</p>
                </div>
            </div>
        </header>

        <main class="dashboard-container">
            <section class="docs-hero card">
                <h1>Gestor de Media</h1>
                <p class="lead">Esta guía explica cómo subir, gestionar e insertar imágenes y vídeos en la web usando el Gestor de Media.</p>
                <p class="small-muted">Pensada para el personal que publica contenidos: sencilla, paso a paso y sin tecnicismos innecesarios.</p>
            </section>

            <div class="docs-grid" style="margin-top:18px">
                <nav class="docs-index card">
                    <h3 style="margin:8px 10px">Índice</h3>
                    <ul>
                        <li><a href="#resumen">Resumen</a></li>
                        <li><a href="#subir">Subir archivos</a></li>
                        <li><a href="#formatos">Formatos y límites</a></li>
                        <li><a href="#miniaturas">Miniaturas y previsualización</a></li>
                        <li><a href="#metadatos">Títulos y renombrado</a></li>
                        <li><a href="#buscar">Buscar, ordenar y seleccionar</a></li>
                        <li><a href="#insertar">Insertar en entradas/páginas</a></li>
                        <li><a href="#eliminar">Eliminar y acciones masivas</a></li>
                        <li><a href="#buenas">Buenas prácticas</a></li>
                        <li><a href="#errores">Errores comunes y soluciones</a></li>
                        <li><a href="#ubicacion">Dónde se guardan los archivos</a></li>
                        <li><a href="#soporte">Contacto y soporte</a></li>
                    </ul>
                </nav>

                <article class="doc-body card">
                    <section id="resumen">
                        <h2>Resumen</h2>
                        <p>El Gestor de Media centraliza las imágenes y vídeos que se usan en la web. Desde aquí puedes subir nuevos archivos, editar su título, obtener la URL para insertarlos en contenidos y eliminar lo que ya no necesites.</p>
                    </section>

                    <section id="subir">
                        <h2>Subir archivos</h2>
                        <ol>
                            <li>Accede a <strong>Gestor de Media</strong> desde el panel.</li>
                            <li>Usa la zona de arrastrar y soltar: arrastra las imágenes o vídeos desde tu equipo y suéltalos en el recuadro.</li>
                            <li>O pulsa <em>Seleccionar archivos</em> y elige uno o varios archivos desde el explorador de archivos.</li>
                            <li>Verás el progreso de subida y, al finalizar, los archivos aparecerán en la cuadrícula.</li>
                        </ol>
                        <p>Si la subida falla, revisa el apartado de <a href="#errores">Errores comunes</a> para soluciones rápidas.</p>
                    </section>

                    <section id="formatos">
                        <h2>Formatos y límites</h2>
                        <ul>
                            <li><strong>Imágenes permitidas:</strong> JPG, PNG, GIF, WEBP, AVIF, SVG. Límite recomendado por archivo: <strong>12 MB</strong>.</li>
                            <li><strong>Vídeos permitidos:</strong> MP4, WEBM, OGG, MOV, MKV. Límite recomendado por archivo: <strong>300 MB</strong>.</li>
                            <li>Si tu archivo excede el límite, comprímelo o reduzca su resolución antes de subirlo.</li>
                        </ul>
                        <p>El sistema intentará validar que lo subido sea realmente una imagen o vídeo y rechazará formatos no permitidos.</p>
                    </section>

                    <section id="miniaturas">
                        <h2>Miniaturas y previsualización</h2>
                        <p>Al subir imágenes, el sistema genera una miniatura automáticamente para mostrarla en la cuadrícula. Esto facilita la navegación y acelera la carga de la administración.</p>
                        <p>Si la miniatura no se genera (por ejemplo con ciertos formatos como AVIF o SVG), el gestor mostrará la imagen completa o un placeholder; revisa permisos de archivos si ves problemas constantes.</p>
                    </section>

                    <section id="metadatos">
                        <h2>Títulos, renombrado y metadatos</h2>
                        <p>Cada archivo tiene un título que puedes editar desde el gestor. El título se usa como texto alternativo y como descripción en la selección para insertar en contenidos.</p>
                        <ol>
                            <li>Haz clic en el archivo que quieras editar.</li>
                            <li>En la fila de edición puedes cambiar el <strong>título</strong> y, opcionalmente, renombrar el fichero (si renombrar está disponible, el gestor pedirá un nombre seguro).</li>
                            <li>Guarda los cambios. Si renombras, el sistema evitará sobrescribir archivos existentes y actualizará la miniatura si procede.</li>
                        </ol>
                        <p>Recomendación: usa títulos descriptivos y sin caracteres especiales (por ejemplo: <em>consulta-yanina-enero-2025.jpg</em>).</p>
                    </section>

                    <section id="buscar">
                        <h2>Buscar, ordenar y seleccionar</h2>
                        <p>Usa la caja de búsqueda para localizar archivos por nombre o título. También puedes ordenar por fecha, nombre o tamaño usando el selector de orden.</p>
                        <p>Para trabajar con varios archivos a la vez, usa los controles de selección (Seleccionar todo, o selecciona manualmente varias miniaturas) y después aplica la acción deseada (eliminar, descargar si está habilitado, etc.).</p>
                    </section>

                    <section id="insertar">
                        <h2>Insertar imágenes y vídeos en contenidos</h2>
                        <p>Hay dos formas habituales de insertar medios en entradas o páginas:</p>
                        <ol>
                            <li><strong>Copiar URL:</strong> abre la vista previa del archivo y pulsa <em>Copiar URL</em>. Pega esa URL en el editor o en el código HTML de la página.</li>
                            <li><strong>Seleccionador (Picker):</strong> si el editor soporta integración (ej. editor visual / TinyMCE), usa el selector para elegir una imagen: se abrirá una ventana con miniaturas y, al seleccionar, el editor recibirá automáticamente la URL y el texto alternativo.</li>
                        </ol>
                        <p>Al insertar imágenes, añade siempre un <strong>texto alternativo (alt)</strong> descriptivo para mejorar accesibilidad y SEO.</p>
                    </section>

                    <section id="eliminar">
                        <h2>Eliminar y acciones masivas</h2>
                        <p>Para eliminar archivos:</p>
                        <ol>
                            <li>Selecciona uno o varios archivos.</li>
                            <li>Pulsa <strong>Eliminar seleccionados</strong> y confirma la acción.</li>
                        </ol>
                        <p>Advertencia: eliminar borra el archivo del servidor. Si no estás seguro, descarga una copia antes de borrar o consulta al administrador.</p>
                    </section>

                    <section id="buenas">
                        <h2>Buenas prácticas</h2>
                        <ul>
                            <li>Optimiza las imágenes antes de subir: reduce resolución y aplica compresión para web para mejorar tiempos de carga.</li>
                            <li>Usa nombres y títulos descriptivos y coherentes para encontrar archivos fácilmente.</li>
                            <li>Añade texto alternativo (alt) a las imágenes para accesibilidad y SEO.</li>
                            <li>Evita subir archivos innecesarios: limpia periódicamente la biblioteca para ahorrar espacio.</li>
                            <li>Guarda copias locales de vídeos grandes; los vídeos consumen mucho espacio y backup/archivado es recomendable.</li>
                        </ul>
                    </section>

                    <section id="errores">
                        <h2>Errores comunes y soluciones</h2>
                        <ul>
                            <li><strong>Subida fallida / Archivo demasiado grande:</strong> comprime el archivo o reduce resolución. Si el problema persiste, consulta los límites de `upload_max_filesize` y `post_max_size` en el servidor.</li>
                            <li><strong>Miniatura no generada:</strong> algunos formatos (AVIF, SVG) pueden no generar miniatura; revisa permisos de carpetas `img/media/thumbs` y espacio en disco.</li>
                            <li><strong>Vídeo sin reproducir:</strong> algunos navegadores no soportan ciertos códecs; convierte el vídeo a MP4 (H.264) o WEBM para máxima compatibilidad.</li>
                            <li><strong>No puedo eliminar archivo:</strong> puede ser un problema de permisos en el servidor; contacta con el administrador.</li>
                            <li><strong>Errores de tipo 'Tipo no permitido':</strong> revisa que el archivo sea realmente una imagen o vídeo aceptado y no un archivo mal nombrado.</li>
                        </ul>
                    </section>

                    <section id="ubicacion">
                        <h2>Dónde se guardan los archivos</h2>
                        <p>Para información administrativa, los archivos se almacenan en la siguiente estructura dentro del servidor web:</p>
                        <ul>
                            <li><code>/img/media/</code> — imágenes subidas.</li>
                            <li><code>/img/media/thumbs/</code> — miniaturas generadas.</li>
                            <li><code>/img/videos/</code> — vídeos subidos.</li>
                            <li><code>_pcontrol/media_meta.json</code> — metadatos (títulos) asociados a los archivos.</li>
                        </ul>
                        <p>No es necesario que los usuarios editen estos archivos directamente; cualquier cambio debe hacerse desde el Gestor de Media para mantener la coherencia.</p>
                    </section>

                    <section id="soporte">
                        <h2>Contacto y soporte</h2>
                        <p>Si tienes problemas que no puedes resolver con esta guía:</p>
                        <ul>
                            <li>Anota el nombre del archivo afectado y la acción intentada (subir, eliminar, renombrar).</li>
                            <li>Envía estos datos al administrador técnico o soporte indicando la hora aproximada y el mensaje de error si aparece.</li>
                            <li>Para problemas de espacio o permisos, el administrador deberá revisar el servidor y los logs (archivo: <code>_pcontrol/gmedia_debug.log</code>).</li>
                        </ul>
                    </section>
                </article>
            </div>
        </main>
    </div>

    <script>
        // Toggle sidebar
        document.getElementById('menuToggle')?.addEventListener('click', function(){
            document.querySelector('.sidebar')?.classList.toggle('active');
        });
        // Smooth anchors
        document.querySelectorAll('.docs-index a').forEach(a => a.addEventListener('click', function(e){
            e.preventDefault(); const target = document.querySelector(this.getAttribute('href')); if(target) target.scrollIntoView({behavior:'smooth', block:'start'});
        }));
    </script>
</body>
</html>
