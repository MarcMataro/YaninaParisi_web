<?php
// Inicializar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Forzar idioma español en esta página
$_SESSION['language'] = 'es';
// Procesar cambio de idioma PRIMERO
if (isset($_GET['lang'])) {
    $lang = $_GET['lang'];
    if (in_array($lang, array('ca', 'es'))) {
        $_SESSION['language'] = $lang;
        header('Location: /' . $lang . '/home.php');
        exit;
    }
}

include '../includes/functions.php';
?>
<!DOCTYPE html>
<html lang="<?php echo getCurrentLanguage(); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php
    $base_url = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'];
    require_once __DIR__ . '/../classes/seo_onpage.php';

    $pagina_seo = null;
    $seoTitle = null;
    $lang = getCurrentLanguage();

    try {
        $items = SEO_OnPage::llistarPaginesActives('cookies');
        if (!empty($items) && isset($items[0]) && $items[0] instanceof SEO_OnPage) {
            $pagina_seo = $items[0];
            $seoTitle = $pagina_seo->getTitle($lang) ?: null;
        }
    } catch (Exception $e) { }

    if (!$seoTitle) {
        $tries = ['/cookies.php','/cookies','/es/cookies.php','/es/cookies','/politica-cookies'];
        foreach ($tries as $r) {
            try {
                $tmp = SEO_OnPage::carregarPerUrl($r, $lang);
                if ($tmp instanceof SEO_OnPage) { $pagina_seo = $tmp; $seoTitle = $pagina_seo->getTitle($lang) ?: null; break; }
            } catch (Exception $e) { }
        }
    }

    if (!$seoTitle) {
        $seoTitle = 'Política de cookies - Yanina Parisi';
    }

    $seoDescription = null;
    if (isset($pagina_seo) && $pagina_seo instanceof SEO_OnPage) {
        $seoDescription = $pagina_seo->getMetaDescription($lang) ?: null;
    }
    if (!$seoDescription) $seoDescription = t('meta_description');

    $canonical = null;
    if (isset($pagina_seo) && $pagina_seo instanceof SEO_OnPage) {
        $canonical = $pagina_seo->getCanonicalUrl($lang);
    }
    if (!$canonical) $canonical = $base_url . '/es/cookies.php';
    ?>
    <title><?php echo htmlspecialchars($seoTitle); ?></title>
    <meta name="description" content="<?php echo htmlspecialchars($seoDescription); ?>">
    <meta name="keywords" content="<?php echo t('meta_keywords'); ?>">
    <meta name="author" content="Yanina Parisi">
    <meta name="robots" content="index, follow">
    <meta name="theme-color" content="#aa9e6b">
    <link rel="canonical" href="<?php echo htmlspecialchars($canonical); ?>">

    <!-- Open Graph -->
    <?php
    $og_title = isset($pagina_seo) && $pagina_seo instanceof SEO_OnPage ? $pagina_seo->getOgTitle($lang) : $seoTitle;
    $og_description = isset($pagina_seo) && $pagina_seo instanceof SEO_OnPage ? $pagina_seo->getOgDescription($lang) : $seoDescription;
    $og_image = null;
    if (isset($pagina_seo) && $pagina_seo instanceof SEO_OnPage) {
        $og_image = $pagina_seo->getOgImage();
    }
    if (!$og_image) { $og_image = '/img/Logo.png'; }
    if (!preg_match('#^https?://#i', $og_image)) {
        $og_image = (strpos($base_url, 'http') === 0 ? $base_url : 'https://' . $_SERVER['HTTP_HOST']) . '/' . ltrim($og_image, '/');
    }
    $og_url = htmlspecialchars($canonical ?: ($base_url . $_SERVER['REQUEST_URI']));
    ?>
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?php echo $og_url; ?>">
    <meta property="og:title" content="<?php echo htmlspecialchars($og_title); ?>">
    <meta property="og:description" content="<?php echo htmlspecialchars($og_description); ?>">
    <meta property="og:image" content="<?php echo htmlspecialchars($og_image); ?>">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:site_name" content="<?php echo htmlspecialchars(t('meta_og_site_name')); ?>">
    <meta property="og:locale" content="es_ES">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@7.0.0/css/all.min.css">
    <link rel="stylesheet" href="../css/estils.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Libre+Baskerville:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" sizes="32x32" href="../img/Logo32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="../img/Logo16.png">
</head>
<body>
    <?php include '_includes/navigation.php'; ?>

    <section class="hero privacy-hero">
        <?php /* imagen del hero como <img> para rutas consistentes */ ?>
        <img src="<?php echo htmlspecialchars(resolve_media_url('img/portada.jpg')); ?>" alt="Portada" class="hero-img">
        <div class="container hero-content">
            <h1 class="hero-title"><?php echo htmlspecialchars(isset($pagina_seo) && $pagina_seo instanceof SEO_OnPage ? $pagina_seo->getH1($lang) : 'Política de cookies'); ?></h1>
        </div>
    </section>

    <main class="container">
        <?php
            if (function_exists('render_breadcrumbs')) {
                render_breadcrumbs([
                    ['label' => t('nav_home'), 'url' => '/es/home.php'],
                    ['label' => 'Política de cookies']
                ]);
            }
        ?>

        <section class="content-section">
            <div class="policy-content">
                <p class="lead">Una cookie es un pequeño archivo de texto que se almacena en tu dispositivo cuando visitas un sitio web. Las utilizamos para que el sitio funcione correctamente, mejorar la experiencia y obtener estadísticas anonimizadas de uso.</p>

                <h2>1. Tipos de cookies que utilizamos</h2>
                <ul>
                    <li><strong>Cookies técnicas (necesarias):</strong> imprescindibles para el funcionamiento del sitio (por ejemplo, gestionar la sesión).</li>
                    <li><strong>Cookies de análisis/estadística:</strong> nos ayudan a comprender el uso del sitio y a mejorar los contenidos (p. ej. Google Analytics).</li>
                    <li><strong>Cookies de funcionalidad:</strong> recuerdan preferencias (idioma, configuraciones, etc.) para ofrecer una experiencia más personalizada.</li>
                    <li><strong>Cookies de terceros:</strong> proporcionadas por servicios externos (p. ej. redes sociales o plataformas de analítica).</li>
                </ul>

                <h2>2. Listado específico de cookies</h2>
                <div class="cookie-table-wrapper">
                    <table class="cookie-table" summary="Listado de cookies utilizadas en el sitio">
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Finalidad</th>
                                <th>Tipo</th>
                                <th>Propiedad</th>
                                <th>Duración</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>cookies funcionales</td>
                                <td>Guarda las preferencias de cookies del usuario/a.</td>
                                <td>Técnica</td>
                                <td>Propia</td>
                                <td>1 año</td>
                            </tr>
                            <tr>
                                <td>PHPSESSID</td>
                                <td>Permite mantener la sesión del usuario/a durante su visita.</td>
                                <td>Técnica</td>
                                <td>Propia</td>
                                <td>Sesión</td>
                            </tr>
                            <tr>
                                <td>_ga</td>
                                <td>Distingue a los usuarios para las estadísticas de Google Analytics.</td>
                                <td>Análisis</td>
                                <td>Terceros (Google)</td>
                                <td>2 años</td>
                            </tr>
                            <tr>
                                <td>_gid</td>
                                <td>Distingue a los usuarios para las estadísticas de Google Analytics.</td>
                                <td>Análisis</td>
                                <td>Terceros (Google)</td>
                                <td>24 horas</td>
                            </tr>
                            <tr>
                                <td>_gat_gtag_UA_*</td>
                                <td>Limita el porcentaje de solicitudes a Google Analytics.</td>
                                <td>Análisis</td>
                                <td>Terceros (Google)</td>
                                <td>1 minuto</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <h2>3. Gestión y configuración de las cookies</h2>
                <p>Al acceder por primera vez a nuestro sitio se mostrará un banner que te permitirá aceptar todas las cookies o configurar tus preferencias, rechazando las no necesarias.</p>
                <p>También puedes gestionar, bloquear o eliminar las cookies desde la configuración de tu navegador. A continuación encontrarás enlaces con instrucciones:</p>
                <ul>
                    <li><a href="https://support.google.com/chrome/answer/95647">Google Chrome</a></li>
                    <li><a href="https://support.mozilla.org/firefox">Mozilla Firefox</a></li>
                    <li><a href="https://support.apple.com/safari">Safari</a></li>
                    <li><a href="https://support.microsoft.com/edge">Microsoft Edge</a></li>
                </ul>

                <h2>4. Consecuencias de bloquear cookies</h2>
                <p>Si bloqueas o eliminas cookies, algunas funcionalidades del sitio pueden no estar disponibles o no funcionar correctamente (por ejemplo, el recuerdo de preferencias o inicio de sesión).</p>

                <h2>5. Cambios en la política</h2>
                <p>Esta política de cookies puede actualizarse para adaptarse a cambios legislativos o técnicos. La versión más reciente estará siempre disponible en esta página. Cuando haya cambios significativos, lo notificaremos mediante un aviso en el sitio web.</p>

                <p class="last-updated"><strong>Fecha de la última actualización:</strong> [Fecha]</p>
            </div>
        </section>
    </main>

    <?php include '_includes/footer.php'; ?>
    <script>
        // Script para la navegación suave
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                document.querySelector(this.getAttribute('href')).scrollIntoView({
                    behavior: 'smooth'
                });
            });
        });

        // Script para el efecto scroll de la navegación
        window.addEventListener('scroll', function() {
            const header = document.querySelector('header');
            if (window.scrollY > 50) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }
        });

        // Script para el selector de idioma
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.lang-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const lang = this.getAttribute('data-lang');
                    // Eliminar clase active de todos los botones
                    document.querySelectorAll('.lang-btn').forEach(b => b.classList.remove('active'));
                    document.querySelectorAll(`.lang-btn[data-lang="${lang}"]`).forEach(b => b.classList.add('active'));

                    // Cerrar menú móvil si está abierto
                    const mobileMenuToggle = document.querySelector('.mobile-menu-toggle');
                    const navMenu = document.querySelector('.nav-menu ul');
                    if (mobileMenuToggle && navMenu) {
                        mobileMenuToggle.classList.remove('active');
                        navMenu.classList.remove('show');
                    }

                    // Cambiar idioma
                    changeLanguage(lang);
                });
            });

            // Funcionalidad del menú hamburguesa
            const mobileMenuToggle = document.querySelector('.mobile-menu-toggle');
            const navMenu = document.querySelector('.nav-menu ul');

            if (mobileMenuToggle && navMenu) {
                mobileMenuToggle.addEventListener('click', function() {
                    this.classList.toggle('active');
                    navMenu.classList.toggle('show');
                });

                // Cerrar menú cuando se clica un enlace
                document.querySelectorAll('.nav-menu ul li a').forEach(link => {
                    link.addEventListener('click', function() {
                        mobileMenuToggle.classList.remove('active');
                        navMenu.classList.remove('show');
                    });
                });

                // Cerrar menú cuando se clica fuera
                document.addEventListener('click', function(e) {
                    if (!mobileMenuToggle.contains(e.target) && !navMenu.contains(e.target)) {
                        mobileMenuToggle.classList.remove('active');
                        navMenu.classList.remove('show');
                    }
                });
            }
        });
    </script>
    <script src="../js/language.js"></script>
    <script src="../js/site-nav.js"></script>
</body>
</html>
