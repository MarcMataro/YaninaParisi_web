<?php
// Inicialitzar sessió si no està iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Forçar idioma català en aquesta pàgina
$_SESSION['language'] = 'ca';
// Processar canvi d'idioma PRIMER
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
        $tries = ['/cookies.php','/cookies','/ca/cookies.php','/ca/cookies','/politica-cookies'];
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
    if (!$canonical) $canonical = $base_url . '/ca/cookies.php';
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
    <meta property="og:locale" content="ca_ES">

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
        <?php /* hero image as <img> so paths resolve consistently */ ?>
        <img src="<?php echo htmlspecialchars(resolve_media_url('img/portada.jpg')); ?>" alt="Portada" class="hero-img">
        <div class="container hero-content">
            <h1 class="hero-title"><?php echo htmlspecialchars(isset($pagina_seo) && $pagina_seo instanceof SEO_OnPage ? $pagina_seo->getH1($lang) : 'Política de cookies'); ?></h1>
        </div>
    </section>

    <main class="container">
        <?php
            if (function_exists('render_breadcrumbs')) {
                render_breadcrumbs([
                    ['label' => t('nav_home'), 'url' => 'home.php'],
                    ['label' => 'Política de cookies']
                ]);
            }
        ?>

            <div class="policy-content">
                <p class="lead">Una cookie és un fitxer de text d'una mida molt petita que s'emmagatzema al teu dispositiu quan visites un lloc web. Les utilitzem per fer funcionar el lloc, millorar l'experiència i obtenir estadístiques anonimitzades d'ús.</p>

                <h2>1. Tipus de galetes que utilitzem</h2>
                <ul>
                    <li><strong>Galetes tècniques (necessàries):</strong> imprescindibles pel funcionament del lloc (per exemple, gestionar la sessió).</li>
                    <li><strong>Galetes d'anàlisi/estadística:</strong> ens ajuden a comprendre l'ús del lloc i a millorar els continguts (p. ex. Google Analytics).</li>
                    <li><strong>Galetes de funcionalitat:</strong> recorden preferències (idioma, configuracions, etc.) per oferir una experiència més personalitzada.</li>
                    <li><strong>Galetes de tercers:</strong> provistes per serveis externs (p. ex. xarxes socials o plataformes d'analítica).</li>
                </ul>

                <h2>2. Llistat específic de galetes</h2>
                <div class="cookie-table-wrapper">
                    <table class="cookie-table" summary="Listado de cookies utilizadas en el sitio">
                        <thead>
                            <tr>
                                <th>Nom</th>
                                <th>Finalitat</th>
                                <th>Tipus</th>
                                <th>Propietat</th>
                                <th>Durada</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>cookies funcionals</td>
                                <td>Guarda les preferències de cookies de l'usuari/ària.</td>
                                <td>Tècnica</td>
                                <td>Pròpia</td>
                                <td>1 any</td>
                            </tr>
                            <tr>
                                <td>PHPSESSID</td>
                                <td>Permet mantenir la sessió de l'usuari/ària durant la seva visita.</td>
                                <td>Tècnica</td>
                                <td>Pròpia</td>
                                <td>Sessió</td>
                            </tr>
                            <tr>
                                <td>_ga</td>
                                <td>Distingix els usuaris per a les estadístiques de Google Analytics.</td>
                                <td>Anàlisi</td>
                                <td>Tercers (Google)</td>
                                <td>2 anys</td>
                            </tr>
                            <tr>
                                <td>_gid</td>
                                <td>Distingix els usuaris per a les estadístiques de Google Analytics.</td>
                                <td>Anàlisi</td>
                                <td>Tercers (Google)</td>
                                <td>24 hores</td>
                            </tr>
                            <tr>
                                <td>_gat_gtag_UA_*</td>
                                <td>Limita el percentatge de sol·licituds a Google Analytics.</td>
                                <td>Anàlisi</td>
                                <td>Tercers (Google)</td>
                                <td>1 minut</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <h2>3. Gestió i configuració de les galetes</h2>
                <p>En accedir per primera vegada al nostre lloc se us mostrarà un banner que us permetrà acceptar totes les galetes o configurar les vostres preferències, rebutjant les no necessàries.</p>
                <p>També pots gestionar, bloquejar o eliminar les galetes des de la configuració del teu navegador. A continuació trobaràs enllaços amb instruccions:</p>
                <ul>
                    <li><a href="https://support.google.com/chrome/answer/95647">Google Chrome</a></li>
                    <li><a href="https://support.mozilla.org/ca/kb/enable-and-disable-cookies-website-preferences">Mozilla Firefox</a></li>
                    <li><a href="https://support.apple.com/ca-es/guide/safari/sfri11471/mac">Safari</a></li>
                    <li><a href="https://support.microsoft.com/ca-es/windows/eliminar-i-administrar-cookies-168dab11-0753-043d-7c16-ede5947fc64d">Internet Explorer / Microsoft Edge</a></li>
                </ul>

                <h2>4. Conseqüències de bloquejar galetes</h2>
                <p>Si bloqueges o elimines galetes, algunes funcionalitats del lloc poden no estar disponibles o no funcionar correctament (per exemple, record de preferències, inici de sessió, estadístiques).</p>

                <h2>5. Actualitzacions i canvis en la política</h2>
                <p>La política de galetes pot ser modificada per adaptar-se a canvis legislatius o tècnics. La versió més actualitzada sempre estarà disponible en aquesta pàgina. Quan hi hagi canvis significatius, ho notificarem amb un avís al lloc web.</p>

                <p class="last-updated"><strong>Data de l'última actualització:</strong> [Data]</p>
            </div>
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
