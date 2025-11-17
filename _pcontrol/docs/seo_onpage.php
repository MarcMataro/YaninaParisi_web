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
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>SEO On-Page — Documentación del Panel</title>
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
    <?php include 'sidebar.php'; ?>

    <div class="main-content">
        <header class="top-bar">
            <div class="top-bar-left">
                <button class="menu-toggle" id="menuToggle"><i class="fas fa-bars"></i></button>
                <div class="top-bar-info">
                    <h1>Documentación interna</h1>
                    <p class="date-today">Guía de SEO On-Page</p>
                </div>
            </div>
        </header>

        <main class="dashboard-container">
            <section class="docs-hero card">
                <h1>SEO On-Page</h1>
                <p class="lead">Manual paso a paso para usar la sección <strong>SEO On Page</strong> (gestión de páginas, títulos, meta, Open Graph, imágenes y métricas).</p>
            </section>

            <div class="docs-grid" style="margin-top:18px">
                <nav class="docs-index card">
                    <h3 style="margin:8px 10px">Índice</h3>
                    <ul>
                        <li><a href="#guia">Guía rápida</a></li>
                        <li><a href="#que-hace">Qué hace la página</a></li>
                        <li><a href="#crear">Crear nueva página On-Page</a></li>
                        <li><a href="#editar">Editar una página existente</a></li>
                        <li><a href="#campos">Explicación de campos</a></li>
                        <li><a href="#acciones">Acciones (guardar / borrar)</a></li>
                        <li><a href="#modal">Uso del modal</a></li>
                        <li><a href="#mejoras">Buenas prácticas</a></li>
                        <li><a href="#errores">Errores comunes</a></li>
                        <li><a href="#archivos">Archivos relacionados</a></li>
                    </ul>
                </nav>

                <article class="doc-body card">
                    <section id="guia">
                        <h2>Guía rápida</h2>
                        <p>La sección <strong>SEO On Page</strong> permite crear, editar y eliminar fichas SEO para URLs internas del sitio. Cada ficha almacena títulos, meta descriptions, H1, contenido principal, slugs, keywords, directivas robots, canonical, datos Open Graph y Twitter, imágenes y parámetros para sitemap (priority, changefreq). También calcula métricas y un SEO score tras guardar.</p>
                    </section>

                    <section id="que-hace">
                        <h2>¿Qué hace exactamente `gseoonpage.php`?</h2>
                        <ol>
                            <li>Carga todas las páginas On-Page existentes y estadísticas globales (llamando a `SEO_OnPage::calcularEstadistiquesGlobals()`).</li>
                            <li>Muestra una tabla con las páginas y permite crear o editar mediante un modal.</li>
                            <li>Procesa formularios POST: <code>action=save_onpage</code> (editar), <code>action=create_onpage</code> (crear) y <code>action=delete_onpage</code> (eliminar).</li>
                            <li>Al guardar una página se actualizan métricas y se recalcula el SEO score del registro.</li>
                        </ol>
                    </section>

                    <section id="crear">
                        <h2>Crear una nueva página On-Page (paso a paso)</h2>
                        <ol>
                            <li>Pulsa el botón <strong>Nova pàgina</strong> en la parte superior. Se abrirá el formulario (modal).</li>
                            <li>Rellena las URLs relativas en catalán y castellano (por ejemplo: <code>/es/servicios/ansiedad</code>).</li>
                            <li>Introduce un título visible para tu referencia interna (<em>Títol visible</em>).</li>
                            <li>En la sección de contenido por idioma, rellena <strong>Meta Title</strong>, <strong>Meta Description</strong>, <strong>H1</strong> y el bloque de <strong>Contenido principal</strong> si quieres guardarlo para referencia (no reemplaza el contenido real del CMS a menos que esté integrado).</li>
                            <li>Configura SEO avanzado: <strong>meta robots</strong>, <strong>canonical</strong>, <strong>priority</strong> y <strong>changefreq</strong>.</li>
                            <li>Añade Open Graph / Twitter titles y descripciones, y las URLs de imagen si quieres controlar cómo se comparte la página.</li>
                            <li>Marca <strong>Activa</strong> si deseas que la página esté considerada por las rutinas de generación de sitemaps y revisiones.</li>
                            <li>Pulsa <strong>Desar</strong>. Si se crea correctamente verás la página recargada con <code>?saved=1&amp;created=ID</code>.</li>
                        </ol>
                    </section>

                    <section id="editar">
                        <h2>Editar una página existente</h2>
                        <ol>
                            <li>En la tabla localiza la fila de la página y pulsa <strong>Edita</strong>.</li>
                            <li>Se abrirá el modal con los campos cargados. Modifica los que necesites.</li>
                            <li>Al guardar el sistema ejecuta: <code>$pagina->actualitzarMultiplesCamps($data)</code>, <code>actualitzarMetriques()</code> y <code>calcularSeoScore()</code>.</li>
                            <li>Si la edición es correcta verás <code>?saved=1</code> en la URL y el mensaje de confirmación.</li>
                        </ol>
                    </section>

                    <section id="campos">
                        <h2>Explicación de campos (por secciones)</h2>
                        <h3>Información básica</h3>
                        <ul>
                            <li><strong>URL (CA / ES)</strong>: ruta relativa de la página por idioma. Útil para relacionar la ficha con la URL real.</li>
                            <li><strong>Títol visible</strong>: nombre interno para identificar la ficha.</li>
                            <li><strong>Tipus de pàgina</strong>: clasificación (home, servicios, blog, artículo, landing, etc.).</li>
                            <li><strong>Activa</strong>: si está activa se incluye en procesos y sitemaps; si no, se puede ignorar.</li>
                            <li><strong>Data publicació</strong>: fecha que se mostrará como referencia y se usa en ordenación.</li>
                        </ul>

                        <h3>Contenidos por idioma</h3>
                        <ul>
                            <li><strong>Meta Title</strong>: título SEO; objetivo: máximo ~60–70 caracteres.</li>
                            <li><strong>Meta Description</strong>: resumen; objetivo: 120–160 caracteres.</li>
                            <li><strong>H1</strong>: encabezado principal de la página.</li>
                            <li><strong>Contenido principal</strong>: se puede guardar texto de referencia para el autor/SEO.</li>
                            <li><strong>Slug</strong>: valor recomendado para URLs amigables; si se deja vacío puede generarse o tomarse de la URL real.</li>
                            <li><strong>Focus Keyword</strong>: palabra clave principal objetivo para esa página.</li>
                        </ul>

                        <h3>SEO avanzado</h3>
                        <ul>
                            <li><strong>Meta Robots</strong>: directiva por página (index, noindex, follow, nofollow).</li>
                            <li><strong>Canonical URL</strong>: URL canónica absoluta para evitar duplicados.</li>
                            <li><strong>Priority</strong> y <strong>Changefreq</strong>: valores que se usan en sitemap.xml.</li>
                            <li><strong>Keywords secundarias</strong>: lista separada por comas de keywords secundarias.</li>
                        </ul>

                        <h3>Open Graph & Twitter</h3>
                        <ul>
                            <li><strong>OG Title / OG Description</strong>: texto que se usará al compartir en redes sociales.</li>
                            <li><strong>OG Image / Twitter Image</strong>: URL de la imagen que se compartirá. Recomendado: 1200×630 para OG, 1200×675 para Twitter.</li>
                            <li><strong>Featured Image</strong> y <strong>Alt Image</strong>: imagen destacada y textos alternativos por idioma.</li>
                        </ul>
                    </section>

                    <section id="acciones">
                        <h2>Acciones (guardar / borrar)</h2>
                        <p>Resumen de internals:</p>
                        <ul>
                            <li><code>save_onpage</code>: acción para actualizar una página existente (requiere <code>id_pagina</code>).</li>
                            <li><code>create_onpage</code>: acción para crear una nueva página; si se crea se recalculan métricas y SEO score.</li>
                            <li><code>delete_onpage</code>: elimina la ficha permanentemente; la UI pide confirmación en un modal.</li>
                        </ul>
                        <pre class="codeblock"><code>// Ejemplo de payload al guardar
POST /_pcontrol/gseoonpage.php
action=save_onpage&id_pagina=123&title_es=Inicio+Servicio&meta_description_es=Texto...</code></pre>
                    </section>

                    <section id="modal">
                        <h2>Uso del modal (crear / editar)</h2>
                        <p>El modal (formulario emergente) contiene todos los campos listados arriba. Funciones JS importantes:</p>
                        <ul>
                            <li><code>openNewModal()</code>: inicializa valores por defecto y abre el formulario para una nueva ficha.</li>
                            <li><code>openEditModal(id, data)</code>: carga los datos (JSON) en los campos y abre el formulario para editar.</li>
                            <li><code>setDefaults()</code> y <code>populateFields(data)</code>: helpers para rellenar inputs.</li>
                            <li><code>closeModal()</code>: cierra el modal (y escucha la tecla Escape para cerrarlo).</li>
                        </ul>
                    </section>

                    <section id="mejoras">
                        <h2>Buenas prácticas y recomendaciones</h2>
                        <ul>
                            <li>Mantén <strong>titles</strong> y <strong>descriptions</strong> editados por página siempre que sea posible — no uses solo valores por defecto globales.</li>
                            <li>Usa slugs cortos y descriptivos; evita stop-words innecesarias.</li>
                            <li>Configura la imagen OG con la proporción adecuada y comprime para reducir latencia.</li>
                            <li>Rellena el <strong>focus keyword</strong> y las <strong>keywords secundarias</strong> para ayudar a la auditoría de contenidos.</li>
                            <li>Antes de eliminar, exporta o copia la URL y metadata por si necesitas restaurarla más tarde.</li>
                        </ul>
                    </section>

                    <section id="errores">
                        <h2>Errores comunes y soluciones</h2>
                        <ul>
                            <li><strong>No se guardan los cambios:</strong> comprueba que <code>action</code> viene como <code>save_onpage</code> o <code>create_onpage</code> y que el usuario tiene permisos.</li>
                            <li><strong>Error al crear página:</strong> revisa que los campos obligatorios (URLs, title) no estén vacíos y consulta <code>$_SESSION['seo_error']</code> si existe.</li>
                            <li><strong>JSON inválido al abrir edición:</strong> si el botón <em>Edita</em> no carga datos, abre la consola del navegador para ver errores y revisa que el payload en el atributo <code>data-json</code> esté correctamente escapado.</li>
                        </ul>
                    </section>

                    <section id="archivos">
                        <h2>Archivos relacionados</h2>
                        <ul>
                            <li><code>_pcontrol/gseoonpage.php</code> — controlador principal que procesa formularios y carga páginas.</li>
                            <li><code>classes/seo_onpage.php</code> — modelo con métodos <code>crear</code>, <code>actualitzarMultiplesCamps</code>, <code>eliminar</code>, <code>actualitzarMetriques</code>, <code>calcularSeoScore</code>.</li>
                            <li><code>css/onpage.css</code> — estilos de la interfaz On-Page.</li>
                            <li><code>js</code> — funciones JS embebidas en la página para modal y gestión de formularios.</li>
                        </ul>
                    </section>
                </article>
            </div>
        </main>
    </div>

    <script>
        document.getElementById('menuToggle')?.addEventListener('click', function(){
            document.querySelector('.sidebar')?.classList.toggle('active');
        });
        document.querySelectorAll('.docs-index a').forEach(a => a.addEventListener('click', function(e){
            e.preventDefault(); const target = document.querySelector(this.getAttribute('href')); if(target) target.scrollIntoView({behavior:'smooth', block:'start'});
        }));
    </script>
</body>
</html>
