<?php
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: ../index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SEO Off-Page — Documentación del Panel</title>
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
                    <p class="date-today">Guía de la sección SEO Off-Page</p>
                </div>
            </div>
        </header>

        <main class="dashboard-container">
            <section class="docs-hero card">
                <h1>SEO Off-Page — Gestión de Backlinks y Directorios</h1>
                <p class="lead">Guía de uso de la interfaz Off-Page: cómo crear, editar y mantener backlinks y directorios desde el panel.</p>
                <p class="small-muted">Esta página documenta el fichero `_pcontrol/gseooffpage.php`. Incluye ejemplos, campos esperados y recomendaciones de mantenimiento.</p>
            </section>

            <div class="docs-grid" style="margin-top:18px">
                <nav class="docs-index card">
                    <h3 style="margin:8px 10px">Índice</h3>
                    <ul>
                        <li><a href="#overview">Resumen</a></li>
                        <li><a href="#backlinks">Backlinks</a></li>
                        <li><a href="#backlink-fields">Campos Backlink</a></li>
                        <li><a href="#directories">Directorios</a></li>
                        <li><a href="#directory-fields">Campos Directorio</a></li>
                        <li><a href="#examples">Ejemplos</a></li>
                        <li><a href="#best-practices">Buenas prácticas</a></li>
                        <li><a href="#errors">Errores</a></li>
                    </ul>
                </nav>

                <article class="doc-body card">
                    <article id="overview" class="doc-card">
                        <h2>Resumen</h2>
                        <p>La pestaña Off-Page se divide en <strong>Backlinks</strong> y <strong>Directorios</strong>. Permite registrar enlaces externos, su calidad aparente y el estado de inclusión en directorios para organizar campañas de linkbuilding.</p>
                    </article>

                    <article id="backlinks" class="doc-card">
                        <h2>Backlinks — Crear / Editar / Eliminar</h2>
                        <p>Acciones disponibles:</p>
                        <ul>
                            <li><strong>Crear backlink</strong>: formulario con campos descriptivos y métricas opcionales.</li>
                            <li><strong>Editar backlink</strong>: actualizar campos y marcar atributos (nofollow, sponsored, ugc).</li>
                            <li><strong>Eliminar</strong>: acción GET con <code>action=delete_backlink&amp;id_offpage=ID</code>.</li>
                        </ul>
                        <p>Tras crear o actualizar el sistema redirige a la lista con parámetros de éxito/ error en la URL.</p>
                    </article>

                    <article id="backlink-fields" class="doc-card">
                        <h2>Campos importantes (Backlinks)</h2>
                        <ul>
                            <li><strong>`url_origen`</strong>: URL donde aparece el enlace (origen).</li>
                            <li><strong>`url_destino`</strong>: URL de tu sitio que recibe el enlace.</li>
                            <li><strong>`anchor_text`</strong>: texto del enlace.</li>
                            <li><strong>`dominio_origen`</strong>: dominio base del origen (ej. ejemplo.com).</li>
                            <li><strong>`tipo_backlink`</strong>: (manual, editorial, adquirido, patrocinado...)</li>
                            <li><strong>Métricas opcionales</strong>: `da_origen`, `dr_origen`, `tf_origen`, `cf_origen`, `traffic_origen`.</li>
                            <li><strong>`idioma_origen`</strong>: idioma del sitio de origen.</li>
                            <li><strong>`posicion_enlace`</strong>: ubicación en la página (contenido, sidebar, footer).</li>
                            <li><strong>`contexto_backlink`</strong>: breve nota sobre contexto semántico.</li>
                            <li><strong>`relevancia_tematica`</strong> / <strong>`calidad_percibida`</strong>: valores para priorizar.</li>
                            <li><strong>`nofollow`, `sponsored`, `ugc`</strong>: checkboxes que indican atributos del rel.</li>
                            <li><strong>`fecha_descubrimiento`</strong>: fecha en que se detectó el enlace.</li>
                            <li><strong>`notas_internas`</strong>: observaciones privadas.</li>
                        </ul>
                    </article>

                    <article id="directories" class="doc-card">
                        <h2>Directorios — Gestión</h2>
                        <p>Los directorios permiten registrar sitios de directorio en los que enviar fichas. Acciones:</p>
                        <ul>
                            <li><strong>Crear directorio</strong>: registrar datos básicos y estado (pendiente, aprobado, rechazado).</li>
                            <li><strong>Editar directorio</strong>: actualizar métricas y fechas de envío/aprobación.</li>
                            <li><strong>Eliminar</strong>: acción GET con <code>action=delete_directorio&amp;id_directorio=ID</code>.</li>
                        </ul>
                    </article>

                    <article id="directory-fields" class="doc-card">
                        <h2>Campos importantes (Directorios)</h2>
                        <ul>
                            <li><strong>`nombre`</strong>: nombre del directorio.</li>
                            <li><strong>`url`</strong>: URL de la ficha/directorio.</li>
                            <li><strong>`categoria`</strong>: categoría del directorio (ej. psicologia).</li>
                            <li><strong>`da_directorio`</strong>: métrica DA estimada (opcional).</li>
                            <li><strong>`costo`</strong>: coste económico si aplica.</li>
                            <li><strong>`idioma`</strong>: idioma de la ficha.</li>
                            <li><strong>`nofollow`</strong>: indica si el directorio impone nofollow.</li>
                            <li><strong>`permite_anchor_personalizado`</strong>: si el directorio admite anchor personalizado.</li>
                            <li><strong>`estado`</strong>: `pendiente`, `enviado`, `aprobado`, etc.</li>
                            <li><strong>`fecha_envio` / `fecha_aprobacion`</strong>: seguimiento temporal.</li>
                            <li><strong>`notas`</strong>: observaciones internas.</li>
                        </ul>
                    </article>

                    <article id="examples" class="doc-card">
                        <h2>Ejemplos — Payloads POST</h2>
                        <p>Crear backlink (formulario):</p>
                        <pre>action=create_backlink
url_origen=https://origen.example/page
url_destino=https://tusitio.example/pagina-objetivo
anchor_text=Texto objetivo
dominio_origen=origen.example
tipo_backlink=editorial
nofollow=on (si marcado)
fecha_descubrimiento=2025-10-01
...</pre>

                        <p>Actualizar backlink:</p>
                        <pre>action=update_backlink
id_offpage=123
url_origen=...
anchor_text=Nuevo texto
nofollow= (checkbox)
...</pre>

                        <p>Crear directorio:</p>
                        <pre>action=create_directorio
nombre=Directorio Ejemplo
url=https://directorio.example/ficha
categoria=psicologia
costo=0
idioma=es
permite_anchor_personalizado=on
...</pre>
                    </article>

                    <article id="best-practices" class="doc-card">
                        <h2>Buenas prácticas y plantillas</h2>
                        <ul>
                            <li><strong>Priorizar calidad</strong>: enlaces editoriales en contenido contextual tienen más valor que enlaces en footers o directorios de baja calidad.</li>
                            <li><strong>Verificar métricas</strong>: comprueba DA/DR/tráfico antes de dar prioridad.</li>
                            <li><strong>Registro de campañas</strong>: usa `campana_seo` y `objetivo_seo` para agrupar acciones y medir ROI.</li>
                            <li><strong>Plantilla outreach</strong>: breve, personalizada, menciona un contenido concreto a enlazar y un beneficio mutuo.</li>
                            <li><strong>Disavow</strong>: sólo para enlaces con riesgo; documenta antes de subir el archivo a Search Console.</li>
                        </ul>
                        <p>Plantilla outreach corta:</p>
                        <pre>Hola [Nombre],
He visto tu artículo sobre [tema] y creo que nuestra guía sobre [tema relacionado] sería útil para tus lectores. ¿Te interesa que lo valoremos juntos para añadirlo como recurso?</pre>
                    </article>

                    <article id="errors" class="doc-card">
                        <h2>Errores comunes y cómo resolverlos</h2>
                        <ul>
                            <li><strong>Falta de campos obligatorios</strong>: el controlador lanza excepción si faltan IDs en update/delete. Asegúrate de enviar `id_offpage` o `id_directorio`.</li>
                            <li><strong>Formato de fecha</strong>: usar `YYYY-MM-DD` en `fecha_descubrimiento`, `fecha_envio`, `fecha_aprobacion`.</li>
                            <li><strong>Redirecciones</strong>: tras guardar se redirige a la lista; si no ves cambios, revisa parámetros `saved=1` o `error=1` en la URL y el `$_SESSION['seo_error']`.</li>
                        </ul>
                    </article>

                    <article class="doc-card">
                        <h2>Siguientes pasos sugeridos</h2>
                        <ul>
                            <li>Decidir si quieres campos adicionales (ej.: prioridad automática por DA/DR).</li>
                            <li>Exportar listados para auditorías (puede añadirse una exportación CSV).</li>
                            <li>Vincular con la sección Global para usar plantillas de campañas.</li>
                        </ul>
                    </article>
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
