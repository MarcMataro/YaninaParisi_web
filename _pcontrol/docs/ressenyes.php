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
    <title>Ressenyes — Documentación del Panel</title>
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
                    <p class="date-today">Guía de la sección Reseñas</p>
                </div>
            </div>
        </header>

        <main class="dashboard-container">
            <section class="docs-hero card">
                <h1>Ressenyes (Reseñas)</h1>
                <p class="lead">Guía práctica para moderar y gestionar las reseñas publicadas por pacientes o usuarios.</p>
                <p class="small-muted">Explica cómo usar la interfaz, filtrar reseñas, aprobar/rechazar, marcar como verificada, ver el contenido y buenas prácticas de moderación.</p>
            </section>

            <div class="docs-grid" style="margin-top:18px">
                <nav class="docs-index card">
                    <h3 style="margin:8px 10px">Índice</h3>
                    <ul>
                        <li><a href="#guia">Guía rápida</a></li>
                        <li><a href="#flujo">Flujo de moderación</a></li>
                        <li><a href="#acciones">Acciones disponibles</a></li>
                        <li><a href="#filtros">Filtros y búsqueda</a></li>
                        <li><a href="#modal">Ver reseña (modal)</a></li>
                        <li><a href="#buenas">Buenas prácticas</a></li>
                        <li><a href="#errores">Errores comunes</a></li>
                        <li><a href="#archivos">Archivos relacionados</a></li>
                    </ul>
                </nav>

                <article class="doc-body card">
                    <section id="guia">
                        <h2>Guía rápida para el usuario</h2>
                        <p>Esta guía explica, de forma directa, cómo usar la sección <strong>Ressenyes</strong> del panel de control para moderar opiniones recibidas.</p>

                        <h3>1) Acceder a Ressenyes</h3>
                        <p>En el menú del panel abre <strong>Ressenyes</strong>. Verás una tabla con las reseñas y una barra superior para filtrar y buscar.</p>

                        <h3>2) Filtrar por estado</h3>
                        <p>Usa el desplegable de <strong>Filtro</strong> para ver solo <em>Pendientes</em>, <em>Aprobadas</em>, <em>Rechazadas</em> o <em>Todas</em>. Por defecto se muestran las pendientes.</p>

                        <h3>3) Buscar reseñas</h3>
                        <p>La caja de búsqueda permite introducir texto libre (fragmento de la reseña) o nombre del paciente. Es útil para localizar reseñas concretas rápidamente.</p>

                        <h3>4) Ver el contenido completo</h3>
                        <p>Pulsa el botón con el icono de ojo para abrir un modal que muestra el título, autor y el texto completo de la reseña.</p>

                        <h3>5) Aprobar o rechazar</h3>
                        <p>Para aprobar una reseña pulsa el botón con el icono de check; para rechazarla usa el icono de cruz. Tras la acción verás un mensaje de confirmación en la parte superior.</p>

                        <h3>6) Marcar como verificada</h3>
                        <p>Si quieres indicar que la reseña proviene de un usuario verificado, usa el botón de verificación (user-check). Es un toggle: pulsa para marcar o desmarcar.</p>

                        <h3>7) Eliminar una reseña</h3>
                        <p>Para eliminar una reseña pulsa el icono de papelera. El sistema pide confirmación antes de borrar definitivamente.</p>
                    </section>

                    <section id="flujo">
                        <h2>Flujo de moderación recomendado</h2>
                        <ol>
                            <li>Revisa las reseñas pendientes diariamente o según volumen.</li>
                            <li>Si la reseña cumple con las normas (no es spam, no contiene datos personales sensibles ni lenguaje ofensivo), apruébala.</li>
                            <li>Si dudas de la autenticidad (p. ej. sin nombre ni datos), marca como <em>revisado</em> y, si procede, verifica con el paciente antes de publicar.</li>
                            <li>Rechaza o elimina reseñas que incumplan políticas (insultos, divulgación de datos personales de terceros, publicidad o spam).</li>
                            <li>Utiliza la marca <em>Verificada</em> para destacar opiniones que proceden de pacientes identificados por el sistema.</li>
                        </ol>
                    </section>

                    <section id="acciones">
                        <h2>Acciones disponibles en la tabla</h2>
                        <ul>
                            <li><strong>Ver</strong>: abre modal con el texto completo y autor.</li>
                            <li><strong>Aprovar</strong>: cambia el estado a <em>aprovat</em> (aprobada).</li>
                            <li><strong>Rebutjar</strong>: cambia el estado a <em>rebutjat</em> (rechazada).</li>
                            <li><strong>Verificada</strong>: alterna la marca de verificación (toggle).</li>
                            <li><strong>Eliminar</strong>: borra la reseña tras confirmación.</li>
                        </ul>

                        <h3>Notas sobre el comportamiento</h3>
                        <p>Cada acción se ejecuta mediante un formulario POST con un campo <code>accio</code> (p. ej. <code>aprovar</code>, <code>rebutjar</code>, <code>verificada</code>, <code>esborrar</code>) y el identificador <code>id_ressenya</code>. Tras la operación se muestra un mensaje de resultado y la lista se recarga.</p>
                    </section>

                    <section id="filtros">
                        <h2>Filtros y búsqueda</h2>
                        <p>Los controles principales son:</p>
                        <ul>
                            <li><strong>Filtro de estado</strong>: muestra pendientes, aprobadas, rechazadas o todas.</li>
                            <li><strong>Campo de búsqueda</strong>: busca por texto dentro de la reseña o por nombre del paciente.</li>
                        </ul>

                        <p>Consejo: combina filtros y búsqueda para acotar por ejemplo reseñas pendientes que contengan una palabra concreta.</p>
                    </section>

                    <section id="modal">
                        <h2>Modal de visualización</h2>
                        <p>El modal muestra:</p>
                        <ul>
                            <li><strong>Título</strong> de la reseña (si existe).</li>
                            <li><strong>Autor</strong> (nombre del paciente o identificador).</li>
                            <li><strong>Texto</strong> de la reseña con preservación de saltos de línea.</li>
                        </ul>

                        <p>El modal se abre mediante JavaScript (función <code>veureRessenya()</code>) y se cierra con el botón <em>Cerrar</em> o al pulsar fuera si la interfaz está configurada para ello.</p>
                    </section>

                    <section id="buenas">
                        <h2>Buenas prácticas de moderación</h2>
                        <ul>
                            <li>Revisa el contexto: si la reseña menciona citas o incidencias, intenta contrastar con el historial antes de publicar.</li>
                            <li>Evita eliminar reseñas legítimas por discrepancias; usa la respuesta pública cuando corresponda (si existe la funcionalidad).</li>
                            <li>Marca como verificada solo cuando haya constancia objetiva (token usado por el paciente, registro en la cita, etc.).</li>
                            <li>Registra en notas internas cualquier acción relevante (por ejemplo, contacto con el paciente para verificar una reseña sospechosa).</li>
                        </ul>
                    </section>

                    <section id="errores">
                        <h2>Errores comunes y cómo resolverlos</h2>
                        <ul>
                            <li><strong>No aparecen reseñas:</strong> comprueba filtros y la búsqueda; limpia ambos y vuelve a cargar.</li>
                            <li><strong>Error al aprobar/rechazar:</strong> puede ser un problema de permisos o de conexión. Reintenta y si persiste pulsa al equipo técnico indicando el ID de la reseña.</li>
                            <li><strong>Botón de verificación no cambia:</strong> revisa que el formulario POST incluye el campo <code>valor</code> con <code>1</code> o <code>0</code>.</li>
                            <li><strong>No se puede eliminar:</strong> comprueba confirmación y privilegios de usuario; una vez eliminada la reseña no es recuperable.</li>
                        </ul>
                    </section>

                    <section id="archivos">
                        <h2>Archivos relacionados</h2>
                        <ul>
                            <li><code>_pcontrol/gressenyes.php</code> — controlador principal que procesa filtros, lista y acciones POST.</li>
                            <li><code>classes/ressenyes.php</code> — modelo con métodos <code>list()</code>, <code>setEstat()</code>, <code>setVerificada()</code>, <code>delete()</code>.</li>
                            <li><code>css/dashboard.css</code> y <code>css/gpacients.css</code> — estilos usados por la interfaz.</li>
                            <li><code>js/dashboard.js</code> — utilidades UI comunes (toggle sidebar, modales suaves).</li>
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
        // Anclas suaves
        document.querySelectorAll('.docs-index a').forEach(a => a.addEventListener('click', function(e){
            e.preventDefault(); const target = document.querySelector(this.getAttribute('href')); if(target) target.scrollIntoView({behavior:'smooth', block:'start'});
        }));
    </script>
</body>
</html>
