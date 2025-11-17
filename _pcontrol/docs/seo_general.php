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
    <title>SEO Global — Documentación del Panel</title>
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
        .note { background:#fffbe6; border-left:4px solid #ffd24d; padding:10px; border-radius:6px; }
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
                    <p class="date-today">Guía de la sección SEO Global</p>
                </div>
            </div>
        </header>

        <main class="dashboard-container">
            <section class="docs-hero card">
                <h1>SEO Global</h1>
                <p class="lead">Explicación clara y paso a paso de todo lo que hace `gseogeneral.php` y cómo usar la interfaz para configurar el SEO del sitio.</p>
                <p class="small-muted">Incluye instrucciones sobre títulos y descripciones, plantillas, Open Graph, Twitter Cards, verificaciones, analytics y sitemap.</p>
            </section>

            <div class="docs-grid" style="margin-top:18px">
                <nav class="docs-index card">
                    <h3 style="margin:8px 10px">Índice</h3>
                    <ul>
                        <li><a href="#guia">Guía rápida</a></li>
                        <li><a href="#que-hace">Qué hace la página</a></li>
                        <li><a href="#guardar">Guardar configuración (acción)</a></li>
                        <li><a href="#meta-tags">1. Meta Tags globales</a></li>
                        <li><a href="#plantillas">2. Plantillas por defecto</a></li>
                        <li><a href="#social">3. Perfiles sociales</a></li>
                        <li><a href="#og">4. Open Graph</a></li>
                        <li><a href="#twitter">5. Twitter Card</a></li>
                        <li><a href="#tecnico">6. SEO técnico</a></li>
                        <li><a href="#internacional">7. SEO internacional (hreflang)</a></li>
                        <li><a href="#sitemap">8. Sitemap (priority & changefreq)</a></li>
                        <li><a href="#practicas">Buenas prácticas</a></li>
                        <li><a href="#errores">Errores comunes</a></li>
                        <li><a href="#archivos">Archivos relacionados</a></li>
                    </ul>
                </nav>

                <article class="doc-body card">
                    <section id="guia">
                        <h2>Guía rápida</h2>
                        <p>La página <code>gseogeneral.php</code> permite configurar todos los valores globales de SEO del sitio: títulos y descripciones por idioma, plantillas dinámicas, datos para compartir en redes (Open Graph y Twitter), verificaciones para buscadores, códigos de analítica y parámetros por defecto para el sitemap. Los cambios se guardan con el botón <strong>Guardar Configuración Global</strong>.</p>
                    </section>

                    <section id="que-hace">
                        <h2>¿Qué hace exactamente esta página?</h2>
                        <ol>
                            <li>Carga la configuración global existente mediante <code>SEO_Global::carregarConfiguracio()</code>.</li>
                            <li>Muestra un formulario dividido en secciones (título, descripción, plantillas, redes sociales, Open Graph, Twitter, SEO técnico, hreflang, sitemap).</li>
                            <li>Cuando envías el formulario se procesa un <code>POST</code> con <code>action=save_global</code>, se recoge los campos y se llama a <code>actualitzarMultiplesCamps($data)</code> del modelo para guardar los cambios en la base de datos o en la fuente de configuración.</li>
                            <li>Tras guardar redirige a la misma página con <code>?saved=1</code> y muestra un mensaje de éxito; si hay error se redirige con <code>?error=1</code> y muestra el mensaje.</li>
                        </ol>
                    </section>

                    <section id="guardar">
                        <h2>Guardar configuración (acción)</h2>
                        <p>Al pulsar <strong>Guardar Configuración Global</strong> el formulario envía:</p>
                        <pre class="codeblock"><code>POST /_pcontrol/gseogeneral.php
action=save_global
site_title_es=...
site_title_ca=...
...otros campos...</code></pre>

                        <p>El backend construye un array <code>$data</code> con los campos que vienen en <code>$_POST</code> y llama a <code>$seo_global->actualitzarMultiplesCamps($data)</code>. Si todo va bien se setea <code>$_SESSION['seo_saved']</code> y se redirige a <code>gseogeneral.php?saved=1</code>.</p>
                    </section>

                    <section id="meta-tags">
                        <h2>1. Meta Tags globales</h2>
                        <p>Campos visibles: <code>site_title_ca</code>, <code>site_title_es</code>, <code>site_description_ca</code>, <code>site_description_es</code>.</p>
                        <p>Qué hacer (paso a paso):</p>
                        <ol>
                            <li>Rellena el título del sitio por idioma. Manténlo por debajo de 60–70 caracteres para evitar recorte en resultados de búsqueda.</li>
                            <li>Rellena la descripción (meta description) por idioma. Máximo recomendado: 150–160 caracteres. Resume la propuesta de valor y contiene la palabra clave principal.</li>
                            <li>Usa placeholders claros en el formulario; el sistema aplicará estos valores cuando no haya meta específicas por página.</li>
                        </ol>
                        <p class="note"><strong>Nota:</strong> Estos valores actúan como fallback global. Las páginas específicas (entradas, servicios) pueden sobreescribir títulos y descripciones cuando se generan dinámicamente.</p>
                    </section>

                    <section id="plantillas">
                        <h2>2. Plantillas por defecto</h2>
                        <p>Campos: <code>default_title_template_ca</code>, <code>default_title_template_es</code>, <code>default_meta_template_ca</code>, <code>default_meta_template_es</code>.</p>
                        <p>Explicación y uso:</p>
                        <ul>
                            <li>Las plantillas usan variables como <code>{page}</code>, <code>{service}</code>, <code>{location}</code>. Por ejemplo: <code>{page} | Psicóloga Yanina Parisi</code>.</li>
                            <li>Cuando se renderiza una página, el sistema sustituye las variables y genera el título y la meta description si no están definidos manualmente para esa página.</li>
                        </ul>
                        <p>Recomendación: prueba la plantilla con el nombre de una página y mira el resultado para asegurarte de que no supera los límites recomendados de longitud.</p>
                    </section>

                    <section id="social">
                        <h2>3. Perfiles de redes sociales</h2>
                        <p>Campos: <code>facebook_url</code>, <code>instagram_url</code>, <code>linkedin_url</code>, <code>twitter_url</code>, <code>google_business_url</code>.</p>
                        <p>Qué hacen y cómo rellenarlos:</p>
                        <ol>
                            <li>Pega la URL completa de cada perfil. Sirven para generar datos estructurados (Schema) y meta tags sociales.</li>
                            <li>Comprueba que las URLs son públicas y accesibles (sin autenticación).</li>
                        </ol>
                    </section>

                    <section id="og">
                        <h2>4. Open Graph global</h2>
                        <p>Campos: <code>og_site_name</code>, <code>default_og_image</code> (URL).</p>
                        <p>Instrucciones:</p>
                        <ol>
                            <li>Especifica un <em>site_name</em> que se mostrará en las comparticiones.</li>
                            <li>Proporciona una <strong>imagen por defecto</strong> con tamaño recomendado 1200×630 px (proporción 1.91:1). Esta imagen se usará cuando una página no tenga una imagen específica.</li>
                            <li>Usa imágenes con buena calidad y alt descriptivo cuando las subas en contenidos individuales.</li>
                        </ol>
                    </section>

                    <section id="twitter">
                        <h2>5. Twitter Card</h2>
                        <p>Campos: <code>twitter_site</code>, <code>twitter_creator</code>, <code>default_twitter_image</code>.</p>
                        <p>Consejos:</p>
                        <ul>
                            <li>Introduce el nombre de usuario del sitio y el del creador sin la arroba o con ella (ambos funcionan, la plantilla del meta lo normaliza).</li>
                            <li>Imagen recomendada: 1200×675 px (proporción 16:9 o 1.78:1).</li>
                        </ul>
                    </section>

                    <section id="tecnico">
                        <h2>6. SEO técnico</h2>
                        <p>Campos: <code>default_meta_robots</code>, <code>google_site_verification</code>, <code>bing_verification</code>, <code>google_analytics_id</code>, <code>google_tag_manager_id</code>.</p>
                        <p>Qué significa cada uno y cómo usarlo:</p>
                        <ul>
                            <li><strong>Meta Robots:</strong> valor por defecto (p. ej. <code>index, follow</code>) que se aplica cuando una página no define explicitamente robots.</li>
                            <li><strong>Google/Bing verification:</strong> copia aquí el valor que te proporciona Search Console / Bing Webmaster para verificar el sitio mediante meta tag.</li>
                            <li><strong>Google Analytics / Tag Manager:</strong> pega el ID correspondiente (G-XXXX, UA-..., o GTM-XXXX). Tag Manager permite gestionar scripts sin tocar el código.</li>
                        </ul>
                        <p class="note"><strong>Importante:</strong> Ten cuidado al cambiar analytics/tag manager en sitios en producción: puede afectar la recopilación de datos si lo sustituyes sin planificar.</p>
                    </section>

                    <section id="internacional">
                        <h2>7. SEO internacional (hreflang)</h2>
                        <p>Campo: <code>hreflang_default</code> (ej. <code>ca</code>, <code>es</code>, <code>en</code>).</p>
                        <p>Instrucciones sencillas:</p>
                        <ol>
                            <li>Selecciona el idioma principal del sitio. El sistema usará ese valor para construir las etiquetas <code>hreflang</code> por defecto cuando se generen páginas multilingües.</li>
                            <li>Si el sitio tiene versiones por idioma, comprueba que las URLs y los rel-alternate hreflang estén correctamente configurados a nivel de plantilla o CMS.</li>
                        </ol>
                    </section>

                    <section id="sitemap">
                        <h2>8. Sitemap: prioridad y frecuencia</h2>
                        <p>Campos: <code>default_priority</code> y <code>default_changefreq</code>.</p>
                        <p>Qué representan y cómo elegirlos:</p>
                        <ul>
                            <li><strong>Prioridad</strong> (0.2–1.0): sugiere a los crawlers la importancia relativa de la página respecto al resto del sitio. Usa <code>1.0</code> en la home y <code>0.6</code> para contenido principal.</li>
                            <li><strong>Changefreq</strong>: indica la frecuencia esperada de cambios. Selecciona <code>daily</code> para blogs frecuentes, <code>monthly</code> para páginas estáticas.</li>
                        </ul>
                        <p>Estos valores ayudan a generar un <code>sitemap.xml</code> más coherente, pero los motores pueden ignorarlos.</p>
                    </section>

                    <section id="practicas">
                        <h2>Buenas prácticas y recomendaciones</h2>
                        <ul>
                            <li>Antes de guardar, revisa longitudes de título (<=70) y description (<=160).</li>
                            <li>Usa plantillas con variables claras y evita duplicar keywords.</li>
                            <li>Configura correctamente las imágenes OG y Twitter con URLs absolutas y tamaños recomendados.</li>
                            <li>Verifica tu sitio en Google Search Console y Bing Webmaster y pega los códigos aquí para comprobar propiedad.</li>
                            <li>Si usas Google Tag Manager, coordina con el equipo de analítica antes de cambiar IDs.</li>
                        </ul>
                    </section>

                    <section id="errores">
                        <h2>Errores comunes y soluciones</h2>
                        <ul>
                            <li><strong>Los cambios no se guardan:</strong> comprueba que tienes permisos y que el formulario envía <code>action=save_global</code>. Revisa logs o el mensaje de error que se muestra en pantalla.</li>
                            <li><strong>Valores de verificación no funcionan:</strong> asegúrate de usar el formato exacto que te da Search Console o Bing (copia-pega completo).</li>
                            <li><strong>Imágenes Open Graph no aparecen al compartir:</strong> limpia la caché de la plataforma social (p. ej. Debugger de Facebook) y usa URLs accesibles públicamente.</li>
                            <li><strong>Analytics no recoge datos:</strong> comprueba el ID y que el snippet se está insertando correctamente en las páginas (o que Tag Manager está publicado).</li>
                        </ul>
                    </section>

                    <section id="archivos">
                        <h2>Archivos relacionados</h2>
                        <ul>
                            <li><code>_pcontrol/gseogeneral.php</code> — página que procesa y muestra el formulario (esta documentación describe su uso).</li>
                            <li><code>classes/seo_global.php</code> — modelo que implementa <code>carregarConfiguracio()</code>, <code>actualitzarMultiplesCamps()</code>, y getters para cada campo.</li>
                            <li><code>css/gseo.css</code> — estilos específicos para la interfaz SEO.</li>
                            <li><code>docs/</code> — otras páginas de documentación para referencia (por ejemplo, <code>docs/blog.php</code> para SEO de entradas individuales).</li>
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
        // Navegación suave por anclas
        document.querySelectorAll('.docs-index a').forEach(a => a.addEventListener('click', function(e){
            e.preventDefault(); const target = document.querySelector(this.getAttribute('href')); if(target) target.scrollIntoView({behavior:'smooth', block:'start'});
        }));
    </script>
</body>
</html>