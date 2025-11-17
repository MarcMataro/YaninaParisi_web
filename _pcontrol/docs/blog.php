<?php
/**
 * Documentación de usuario: Gestión del Blog
 * Ruta: _pcontrol/docs/blog.php
 * Idioma: Castellano
 */
?>
<!DOCTYPE html>
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
    <title>Blog — Documentación del Panel</title>
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
                    <p class="date-today">Guía de la sección Blog</p>
                </div>
            </div>
        </header>

        <main class="dashboard-container">
            <section class="docs-hero card">
                <h1>Blog</h1>
                <p class="lead">Guía práctica para gestionar las entradas, categorías y etiquetas desde el panel de control (archivo: <code>_pcontrol/gblog.php</code>).</p>
                <p class="small-muted">Incluye los pasos habituales para crear, editar, publicar y programar contenido, así como buenas prácticas de SEO y uso del gestor de medios.</p>
            </section>

            <div class="docs-grid" style="margin-top:18px">
                <nav class="docs-index card">
                    <h3 style="margin:8px 10px">Índice</h3>
                    <ul>
                        <li><a href="#resumen">Resumen rápido</a></li>
                        <li><a href="#entradas">Entradas (crear/editar)</a></li>
                        <li><a href="#publicar">Publicar / Programar</a></li>
                        <li><a href="#categorias">Categorías</a></li>
                        <li><a href="#etiquetas">Etiquetas</a></li>
                        <li><a href="#multimedia">Multimedia y portada</a></li>
                        <li><a href="#seo">SEO</a></li>
                        <li><a href="#errores">Errores comunes</a></li>
                        <li><a href="#archivos">Archivos relacionados</a></li>
                    </ul>
                </nav>

                <article class="doc-body card">
                    <section id="resumen">
                        <h2>Resumen rápido</h2>
                        <p>El módulo de Blog permite administrar artículos con soporte multidioma, gestionar categorías y etiquetas, y usar el gestor de medios integrado para imágenes. El archivo principal que controla estas funciones es <code>_pcontrol/gblog.php</code>, que expone acciones vía AJAX para las operaciones CRUD y devuelve listados para la interfaz.</p>
                    </section>

                    <section id="entradas">
                        <h2>Entradas: crear y editar</h2>
                        <ol>
                            <li>Pulsa <strong>Nueva Entrada</strong> para abrir el formulario dividido por idioma (Català / Español).</li>
                            <li>Rellena <strong>Título</strong> y <strong>Contenido</strong> en los idiomas deseados. El campo de resumen es recomendable para listados y social cards.</li>
                            <li>Utiliza el editor (TinyMCE) para dar formato. Para insertar imágenes, abre el gestor de medios desde el editor.</li>
                            <li>Selecciona <strong>Categorías</strong> y <strong>Etiquetas</strong> según proceda.</li>
                            <li>Rellena los campos de SEO si son relevantes y guarda la entrada.</li>
                        </ol>
                    </section>

                    <section id="publicar">
                        <h2>Publicar, programar y gestionar estado</h2>
                        <p>En <strong>Configuración General</strong> del formulario elige el <em>Estado</em> de la entrada. Para programar una publicación, selecciona una fecha futura en <em>Fecha de Publicación</em>. Cambiar el estado desde la lista también es posible mediante las acciones rápidas.</p>
                    </section>

                    <section id="categorias">
                        <h2>Categorías</h2>
                        <p>Desde la pestaña <strong>Categorías</strong> puedes crear, editar, activar/desactivar y eliminar categorías. Al crear una categoría indica nombres por idioma, descripción opcional, orden y si está activa.</p>
                    </section>

                    <section id="etiquetas">
                        <h2>Etiquetas</h2>
                        <p>Las etiquetas se gestionan desde su propia pestaña. Su uso es más libre que las categorías y permiten búsquedas y sugerencias al escribirlas en el formulario de entrada.</p>
                    </section>

                    <section id="multimedia">
                        <h2>Multimedia y imagen de portada</h2>
                        <p>La imagen de portada se selecciona pulsando <em>Seleccionar imagen</em> y eligiendo un recurso desde el gestor de medios (<code>_pcontrol/gmedia.php</code>). El editor también permite insertar imágenes mediante el mismo selector.</p>
                    </section>

                    <section id="seo">
                        <h2>SEO: cómo optimizar correctamente las entradas</h2>
                        <p>Optimizar una entrada para buscadores no es solo rellenar dos campos; es un proceso con pasos claros que influyen en cómo los motores de búsqueda entienden y posicionan tu contenido. A continuación tienes una guía práctica, ejemplos y una checklist para aplicar en cada entrada.</p>

                        <h3>1. Conceptos rápidos</h3>
                        <ul>
                            <li><strong>Palabra clave objetivo:</strong> la frase principal por la que quieres que la entrada sea encontrada (p.ej. "terapia online ansiedad").</li>
                            <li><strong>Intención de búsqueda:</strong> entiende si el usuario busca información, comparar opciones, contratar un servicio o resolver una duda.</li>
                            <li><strong>Relevancia:</strong> el contenido debe responder claramente la intención; calidad y profundidad pesan más que longitud vacía.</li>
                        </ul>

                        <h3>2. Investigación de palabras clave (antes de escribir)</h3>
                        <ol>
                            <li>Piensa en 3–5 términos relacionados con el tema. Usa herramientas sencillas (Google Autocomplete, Google Trends, u otras) para ver variantes y volumen.</li>
                            <li>Elige una <em>keyword principal</em> (una frase) y 2–3 <em>keywords secundarias</em> (sinónimos o variantes).</li>
                            <li>Anota preguntas frecuentes relacionadas; las responderás en subsecciones (ayuda a aparecer en fragments y en "People also ask").</li>
                        </ol>

                        <h3>3. Dónde colocar las palabras clave (prioridad)</h3>
                        <ol>
                            <li><strong>Título de la página (meta title)</strong> — incluir la keyword principal al inicio si es posible; longitud recomendada: 50–60 caracteres.</li>
                            <li><strong>URL / slug</strong> — usar la keyword principal en una forma limpia: por ejemplo <code>/blog/terapia-online-ansiedad</code>. Evita palabras innecesarias.</li>
                            <li><strong>H1 del artículo</strong> — normalmente es el mismo que el título visible; debe contener la keyword de forma natural.</li>
                            <li><strong>Primeros 100–150 caracteres (primer párrafo)</strong> — menciona la keyword y explica la intención de la entrada.</li>
                            <li><strong>Subtítulos (H2/H3)</strong> — utiliza keywords secundarias en algunos H2/H3 relacionados con las secciones.</li>
                            <li><strong>Meta descripción</strong> — redacta una frase atractiva que incluya la keyword y una llamada a la acción; 140–160 caracteres.</li>
                            <li><strong>Texto alternativo de las imágenes (alt)</strong> — describe la imagen e incluye la keyword si aplica, sin forzar.</li>
                            <li><strong>Nombre del archivo de la imagen</strong> — usa guiones y palabras descriptivas: <code>terapia-online-ansiedad.jpg</code>.</li>
                        </ol>

                        <h3>4. Cómo redactar el contenido</h3>
                        <ul>
                            <li>Comienza con un resumen claro que responda la intención (las primeras líneas son muy importantes).</li>
                            <li>Divide el artículo con subtítulos (H2/H3) para facilitar lectura y scannability.</li>
                            <li>Añade listas, tablas o pasos si ayudan a la comprensión; Google valora contenido utilizable.</li>
                            <li>Incluye al menos 300–600 palabras en posts básicos; para temas competitivos, 1.000+ palabras bien estructuradas suelen posicionar mejor.</li>
                            <li>Evita relleno: cada párrafo debe aportar valor. No repitas la keyword innecesariamente (evita keyword stuffing).</li>
                        </ul>

                        <h3>5. Enlaces internos y externos</h3>
                        <ul>
                            <li><strong>Enlaces internos:</strong> enlaza desde la nueva entrada a 2–3 contenidos relacionados del propio sitio (p. ej. páginas de servicios, otras entradas). Esto distribuye autoridad y mejora la navegación.</li>
                            <li><strong>Enlaces externos:</strong> enlaza a fuentes fiables cuando cedas información técnica o estadísticas; eso aporta confianza.</li>
                        </ul>

                        <h3>6. Imágenes y velocidad</h3>
                        <ul>
                            <li>Optimiza las imágenes (webp/jpg comprimido) antes de subir; arma miniaturas apropiadas.</li>
                            <li>Rellena <em>alt</em> con descripciones útiles y la keyword si aplica.</li>
                            <li>Comprueba que la imagen no exceda 100–200 KB cuando sea posible y que las dimensiones sean las adecuadas para portada y miniatura.</li>
                        </ul>

                        <h3>7. Meta datos sociales (opengraph/twitter)</h3>
                        <ul>
                            <li>Cuando el CMS lo permita, define <code>og:title</code>, <code>og:description</code> y una imagen (1200×630 px aproximadamente) para que al compartir en redes la vista sea óptima.</li>
                            <li>Si no hay campos específicos, el sistema suele usar el meta title, meta description y la imagen de portada como fallback.</li>
                        </ul>

                        <h3>8. Publicación y seguimiento</h3>
                        <ol>
                            <li>Antes de publicar, revisa: título, slug, meta descripción, H1, primer párrafo y alt de la imagen.</li>
                            <li>Después de publicar, añade la URL al sitemap (si se actualiza automáticamente, comprueba que aparece) y, si procede, pídela a Google mediante Search Console (inspeccionar URL & solicitar indexación).</li>
                            <li>Revisa rendimiento en 2–4 semanas: impresiones, clics y posición en Search Console. Ajusta si la entrada no obtiene tráfico (cambia título/meta, añade más contenido, optimiza snippets).</li>
                        </ol>

                        <h3>9. Checklist rápido (copiar y pegar)</h3>
                        <ul>
                            <li>[ ] Keyword objetivo elegida y anotada.</li>
                            <li>[ ] Meta title (50–60 chars) con keyword.</li>
                            <li>[ ] Slug amigable con keyword.</li>
                            <li>[ ] H1 contiene la keyword.</li>
                            <li>[ ] Primer párrafo menciona la keyword y responde la intención.</li>
                            <li>[ ] 1–3 subtítulos (H2/H3) con variantes/keywords secundarias.</li>
                            <li>[ ] Meta description (140–160 chars) con keyword y CTA.</li>
                            <li>[ ] Imágenes optimizadas + alt descriptivo.</li>
                            <li>[ ] Enlaces internos a contenidos relacionados.</li>
                            <li>[ ] Comprobado en móvil: legibilidad y velocidad básica.</li>
                        </ul>

                        <h3>10. Ejemplo práctico</h3>
                        <p>Imagina una entrada sobre "terapia online para la ansiedad":</p>
                        <ul>
                            <li><strong>Keyword principal:</strong> terapia online ansiedad</li>
                            <li><strong>Meta title:</strong> Terapia online ansiedad — Tratamiento efectivo en [Ciudad]</li>
                            <li><strong>Slug:</strong> /blog/terapia-online-ansiedad</li>
                            <li><strong>H1:</strong> Terapia online para la ansiedad: cómo funciona y qué esperar</li>
                            <li><strong>Primer párrafo:</strong> incluir la keyword y un resumen de la solución que ofreces.</li>
                            <li><strong>Meta description:</strong> Psicóloga especializada en ansiedad. Terapia online personalizada. Reserva tu primera sesión. (120–150 chars)</li>
                        </ul>

                        <p class="small-muted">Aplica esta guía a cada entrada y guarda la checklist en tu flujo de publicación. Con el tiempo, monitoriza qué tipos de títulos y descripciones generan más clics y ajusta la estrategia.</p>
                    </section>

                    <section id="errores">
                        <h2>Errores comunes y soluciones</h2>
                        <ul>
                            <li><strong>TinyMCE no carga:</strong> revisar la carga del CDN y permisos de red en el navegador.</li>
                            <li><strong>Lista vacía:</strong> limpiar filtros o comprobar si la tabla <code>blog_entrades</code> existe en la BD.</li>
                            <li><strong>No se pueden crear categorías/etiquetas:</strong> comprobar permisos de escritura en la base de datos y revisar mensajes de error devueltos por AJAX.</li>
                        </ul>
                    </section>

                    <section id="archivos">
                        <h2>Archivos relacionados</h2>
                        <ul>
                            <li><code>_pcontrol/gblog.php</code> — controlador principal (acciones AJAX y renderizado).</li>
                            <li><code>js/gblog-entrades-simple.js</code>, <code>js/gblog-categories.js</code>, <code>js/gblog-etiquetes.js</code> — scripts de cliente que gestionan la UI y llamadas AJAX.</li>
                            <li><code>css/gblog.css</code> — estilos del módulo.</li>
                            <li><code>_pcontrol/gmedia.php</code> — gestor de medios (selector para imágenes).</li>
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
