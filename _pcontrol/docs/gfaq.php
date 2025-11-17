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
    <title>gfaq.php — Documentación técnica</title>
    <link rel="stylesheet" href="../css/dashboard.css">
    <link rel="stylesheet" href="../css/configuracion.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Libre+Baskerville:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet">
    <style>
        html, body { font-family: 'Libre Baskerville', serif; font-size:16px; }
        .docs-hero { padding: 26px 22px; }
        .docs-grid { display:flex; gap:24px; align-items:flex-start; }
        .docs-index { flex:0 0 300px; max-width:300px; }
        .docs-index ul { list-style:none; padding-left:0; }
        .doc-body { flex:1 1 auto; }
        pre.codeblock { background:#f7f7f7; padding:12px; border-radius:8px; overflow:auto; }
        .note { background:#fffbe6; border-left:4px solid #ffd24d; padding:10px; margin:12px 0; }
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
                    <p class="date-today">Manual técnico: `gfaq.php`</p>
                </div>
            </div>
        </header>

        <main class="dashboard-container">
            <section class="docs-hero card">
                <h1>`gfaq.php` — Gestión de FAQs</h1>
                <p class="lead">Manual que explica en detalle cómo funciona `gfaq.php`: flujo de control, acciones POST/GET, consultas SQL, campos de formulario y la integración con el modelo `classes/faqs.php`.</p>
            </section>

            <div class="docs-grid" style="margin-top:18px">
                <nav class="docs-index card">
                    <h3 style="margin:8px 10px">Índice</h3>
                    <ul>
                        <li><a href="#resumen">Resumen funcional</a></li>
                        <li><a href="#estructura">Estructura y flujo</a></li>
                        <li><a href="#acciones">Acciones POST</a></li>
                        <li><a href="#gets">Parámetros GET y vistas</a></li>
                        <li><a href="#sql">Consultas y búsqueda</a></li>
                        <li><a href="#campos">Campos y modelo</a></li>
                        <li><a href="#ui">Interfaz y modales</a></li>
                        <li><a href="#errores">Errores comunes</a></li>
                        <li><a href="#archivos">Archivos relacionados</a></li>
                    </ul>
                </nav>

                <article class="doc-body card">
                    <section id="resumen">
                        <h2>Resumen funcional</h2>
                        <p>`gfaq.php` es el controlador de la sección de FAQs del panel. Proporciona las funciones para listar, crear, editar, eliminar y marcar FAQs como activas/destacadas. Opera siguiendo el patrón clásico: comprobar sesión, instanciar modelo, procesar POST (acciones), obtener datos y renderizar la vista.</p>
                    </section>

                    <section id="estructura">
                        <h2>Estructura y flujo</h2>
                        <ol>
                            <li>Inicio de sesión y verificación de autenticación (session_start() y comprobación de `$_SESSION['logged_in']`).</li>
                            <li>Carga de dependencias: `classes/connexio.php` y `classes/faqs.php`.</li>
                            <li>Instanciación del modelo `Faq` con la conexión PDO.</li>
                            <li>Procesamiento de `$_POST` cuando se envía un formulario (acciones CRUD y toggles).</li>
                            <li>Procesamiento de `$_GET` para vistas y filtros (lista, editar, búsqueda).</li>
                            <li>Consulta de FAQs y carga de datos para la vista; renderizado HTML y JavaScript de interacción (modales, confirmaciones, etc.).</li>
                        </ol>

                        <h3>Fragmento inicial (resumido)</h3>
                        <pre class="codeblock"><code><?php echo htmlspecialchars("session_start();\nif (!isset(\\\$_SESSION['logged_in']) || \\\$_SESSION['logged_in'] !== true) header('Location: index.php');\nrequire_once __DIR__ . '/../classes/connexio.php';\nrequire_once __DIR__ . '/../classes/faqs.php';\n// crear modelo y proceder..."); ?></code></pre>
                    </section>

                    <section id="acciones">
                        <h2>Acciones POST</h2>
                        <p>Las acciones se distinguen mediante `$_POST['accion']`:</p>
                        <ul>
                            <li><strong>crear</strong>: recoge campos del formulario y llama a `Faq->crear()`; después redirige (PRG) con mensaje.</li>
                            <li><strong>actualizar</strong>: rellena propiedades del modelo con `$_POST` (incluyendo ID) y llama a `Faq->actualitzar()`; redirección PRG.</li>
                            <li><strong>eliminar</strong>: recibe `id_faq` y llama a `Faq->eliminar()`; redirección PRG.</li>
                            <li><strong>toggle_activa</strong>: invierte visibilidad mediante `Faq->toggleActiva($id)`.</li>
                            <li><strong>toggle_destacada</strong>: alterna la marca de destacada mediante `Faq->toggleDestacada($id)`.</li>
                        </ul>

                        <p class="note">Todas las acciones POST realizan un Redirect-After-Post (PRG) hacia `gfaq.php` con parámetros `msg` y `type` para mostrar alertas seguras y evitar reenvíos accidentales.</p>
                    </section>

                    <section id="gets">
                        <h2>Parámetros GET y vistas</h2>
                        <ul>
                            <li><code>vista</code>: determina la vista; valores principales: <code>lista</code> (por defecto) y <code>editar</code>.</li>
                            <li><code>id</code>: id de FAQ para editar (`vista=editar&id=123`).</li>
                            <li><code>categoria</code>, <code>activa</code>, <code>q</code>: filtros para la lista (categoría, estado, búsqueda libre).</li>
                        </ul>

                        <p>Si `vista === 'editar'` y `id` está presente, el controlador carga la FAQ con <code>Faq->obtenirPerId($id)</code> y muestra el formulario de edición con los campos precargados.</p>
                    </section>

                    <section id="sql">
                        <h2>Consultas y búsqueda</h2>
                        <p>La búsqueda por texto se implementa en el propio archivo con una consulta preparada:</p>
                        <pre class="codeblock"><code><?php echo htmlspecialchars("SELECT * FROM faqs WHERE pregunta_es LIKE :q OR resposta_es LIKE :q ORDER BY categoria, ordre, id_faq"); ?></code></pre>

                        <p>Para listados sin búsqueda el controlador delega en el modelo: <code>$faqModel->llistar($opts)</code>, donde <code>$opts</code> puede contener filtros como <code>categoria</code> o <code>activa</code>.</p>
                    </section>

                    <section id="campos">
                        <h2>Campos del formulario y modelo</h2>
                        <p>Campos usados al crear/editar una FAQ:</p>
                        <ul>
                            <li><code>pregunta_es</code>, <code>pregunta_ca</code> — pregunta en español y catalán.</li>
                            <li><code>resposta_es</code>, <code>resposta_ca</code> — respuesta en ambos idiomas (textarea, pueden incluir HTML simple).</li>
                            <li><code>categoria</code> — categoría lógica (ej. general, terapia, tarifes...)</li>
                            <li><code>ordre</code> — orden de aparición en listados.</li>
                            <li><code>activa</code> — booleano para visibilidad pública.</li>
                            <li><code>destacada</code> — booleano para marcar preguntas destacadas.</li>
                            <li>SEO: <code>meta_title_es/ca</code>, <code>meta_description_es/ca</code>, y <code>slug_es/ca</code>.</li>
                        </ul>

                        <p class="note">Los slugs pueden generarse automáticamente en el modelo si no se proporcionan; revisar `classes/faqs.php` para la lógica concreta.</p>
                    </section>

                    <section id="ui">
                        <h2>Interfaz, modales y comportamiento JS</h2>
                        <p>La vista incluye:</p>
                        <ul>
                            <li>Listado en tabla con acciones por fila (Editar, Eliminar, Mostrar/Ocultar, Destacar/Quitar).</li>
                            <li>Formulario de creación en modal (`#modalCrear`) con validación HTML básica (atributo <code>required</code>).</li>
                            <li>Formulario de edición en una tarjeta (`.card.edit-card`) cuando se solicita `vista=editar`.</li>
                            <li>Scripts para abrir/cerrar modal, gestionar foco y accesibilidad (escape, trap focus) implementados inline.</li>
                        </ul>

                        <p>Las acciones rápidas (Mostrar/Ocultar, Destacar) envían formularios POST reducidos que ejecutan la acción y redirigen. La eliminación incluye confirm dialog HTML/JS para evitar borrados accidentales.</p>
                    </section>

                    <section id="errores">
                        <h2>Errores comunes y soluciones</h2>
                        <ul>
                            <li><strong>Error al crear/actualizar:</strong> comprobar campos obligatorios y permisos de la BD. Revisar mensajes pasados por GET (`msg`/`type`).</li>
                            <li><strong>La búsqueda no devuelve resultados:</strong> la búsqueda utiliza LIKE sobre <code>pregunta_es</code> y <code>resposta_es</code>; para buscar en catalán u otros campos, extender la consulta o usar el método del modelo.</li>
                            <li><strong>Problemas con slugs:</strong> si se producen duplicados, revisar la lógica de generación de slugs en el modelo y restricciones de la tabla.</li>
                            <li><strong>Errores 500 o pantalla blanca:</strong> activar logs de PHP y revisar excepciones PDO; mostrar mensajes de error solo en entornos de desarrollo.</li>
                        </ul>
                    </section>

                    <section id="archivos">
                        <h2>Archivos relacionados</h2>
                        <ul>
                            <li><code>_pcontrol/gfaq.php</code> — controlador (este archivo).</li>
                            <li><code>classes/faqs.php</code> — modelo que implementa <code>crear</code>, <code>actualitzar</code>, <code>eliminar</code>, <code>llistar</code>, <code>obtenirPerId</code>, <code>toggleActiva</code>, <code>toggleDestacada</code>.</li>
                            <li><code>css/configuracion.css</code> y <code>css/dashboard.css</code> — estilos usados por la vista.</li>
                            <li><code>includes/sidebar.php</code> — navegación lateral incluida en la plantilla.</li>
                        </ul>
                    </section>

                    <section id="ejemplos">
                        <h2>Ejemplos rápidos</h2>
                        <p>Ejemplo de petición POST para crear (simulado):</p>
                        <pre class="codeblock"><code><?php echo htmlspecialchars("POST /_pcontrol/gfaq.php HTTP/1.1\nContent-Type: application/x-www-form-urlencoded\n\naccion=crear&pregunta_es=¿Qué+es+una+sesión?&resposta_es=Respuesta+detallada+...&categoria=general&activa=1"); ?></code></pre>

                        <p>Ejemplo de consulta para búsqueda (resumen):</p>
                        <pre class="codeblock"><code><?php echo htmlspecialchars("SELECT * FROM faqs WHERE pregunta_es LIKE '%ansiedad%' OR resposta_es LIKE '%ansiedad%' ORDER BY categoria, ordre, id_faq"); ?></code></pre>
                    </section>

                    <section id="consejos">
                        <h2>Consejos y buenas prácticas</h2>
                        <ul>
                            <li>Valida y sanitiza entradas en el modelo (no confiar únicamente en la validación del formulario).</li>
                            <li>Usa transacciones si extiendes operaciones que afecten a varias tablas al mismo tiempo.</li>
                            <li>Lleva registro (logs) de acciones críticas como eliminación de FAQs para auditoría.</li>
                            <li>Evita mostrar mensajes de error técnicos en producción; usa mensajes genéricos y guarda detalles en logs.</li>
                        </ul>
                    </section>
                </article>
            </div>
        </main>
    </div>

    <script>
        document.getElementById('menuToggle')?.addEventListener('click', function(){ document.querySelector('.sidebar')?.classList.toggle('active'); });
        // Anclas suaves
        document.querySelectorAll('.docs-index a').forEach(a => a.addEventListener('click', function(e){ e.preventDefault(); const target = document.querySelector(this.getAttribute('href')); if(target) target.scrollIntoView({behavior:'smooth', block:'start'}); }));
    </script>
</body>
</html>
