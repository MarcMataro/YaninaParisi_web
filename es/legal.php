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
    // SEO extraction pattern (same as other pages)
    $base_url = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'];
    require_once __DIR__ . '/../classes/seo_onpage.php';

    $pagina_seo = null;
    $seoTitle = null;
    $lang = getCurrentLanguage();

    try {
        $items = SEO_OnPage::llistarPaginesActives('legal');
        if (!empty($items) && isset($items[0]) && $items[0] instanceof SEO_OnPage) {
            $pagina_seo = $items[0];
            $seoTitle = $pagina_seo->getTitle($lang) ?: null;
        }
    } catch (Exception $e) { }

    if (!$seoTitle) {
        $tries = ['/legal.php','/legal','/es/legal.php','/es/legal','/aviso-legal'];
        foreach ($tries as $r) {
            try {
                $tmp = SEO_OnPage::carregarPerUrl($r, $lang);
                if ($tmp instanceof SEO_OnPage) { $pagina_seo = $tmp; $seoTitle = $pagina_seo->getTitle($lang) ?: null; break; }
            } catch (Exception $e) { }
        }
    }

    if (!$seoTitle) {
        $seoTitle = ($lang === 'es') ? 'Aviso legal - Yanina Parisi' : 'Avís legal - Yanina Parisi';
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
    if (!$canonical) $canonical = $base_url . (($lang === 'es') ? '/es/legal.php' : '/ca/legal.php');
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

    <!-- Twitter -->
    <?php
    $tw_title = isset($pagina_seo) && $pagina_seo instanceof SEO_OnPage ? $pagina_seo->getTwitterTitle($lang) : $seoTitle;
    $tw_description = isset($pagina_seo) && $pagina_seo instanceof SEO_OnPage ? $pagina_seo->getTwitterDescription($lang) : $seoDescription;
    $tw_image = null;
    if (isset($pagina_seo) && $pagina_seo instanceof SEO_OnPage) $tw_image = $pagina_seo->getTwitterImage();
    if (!$tw_image) $tw_image = '/img/Logo.png';
    if (!preg_match('#^https?://#i', $tw_image)) {
        $tw_image = (strpos($base_url, 'http') === 0 ? $base_url : 'https://' . $_SERVER['HTTP_HOST']) . '/' . ltrim($tw_image, '/');
    }
    ?>
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?php echo htmlspecialchars($tw_title); ?>">
    <meta name="twitter:description" content="<?php echo htmlspecialchars($tw_description); ?>">
    <meta name="twitter:image" content="<?php echo htmlspecialchars($tw_image); ?>">

    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "WebPage",
        "name": "<?php echo htmlspecialchars($seoTitle); ?>",
        "description": "<?php echo htmlspecialchars($seoDescription); ?>",
        "url": "<?php echo htmlspecialchars($canonical); ?>"
    }
    </script>

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
            <h1 class="hero-title"><?php echo htmlspecialchars(isset($pagina_seo) && $pagina_seo instanceof SEO_OnPage ? $pagina_seo->getH1($lang) : 'Aviso legal'); ?></h1>
        </div>
    </section>

    <main class="container">
        <?php
            // Breadcrumbs: Inicio > Aviso legal
            if (function_exists('render_breadcrumbs')) {
                render_breadcrumbs([
                    ['label' => t('nav_home'), 'url' => '/es/home.php'],
                    ['label' => 'Aviso legal']
                ]);
            }
        ?>

        <section class="content-section">
            <div class="policy-content">
                <h2>1. INFORMACIÓN GENERAL</h2>
                <p>De acuerdo con lo establecido en el artículo 10 de la Ley 34/2002, de 11 de julio, de Servicios de la Sociedad de la Información y del Comercio Electrónico (LSSI-CE), facilito la información general relativa al presente sitio web:</p>

                <ul class="contact-details">
                    <li><strong>Responsable:</strong> [Nombre y apellidos de la psicóloga]</li>
                    <li><strong>NIF/CIF:</strong> [Número de identificación]</li>
                    <li><strong>Domicilio profesional:</strong> [Dirección completa del consultorio, Girona]</li>
                    <li><strong>Colegiada:</strong> Colegio Oficial de Psicología de Cataluña, núm. [XXXXX]</li>
                    <li><strong>Correo electrónico:</strong> <a href="mailto:[direccion@email.com]">[direccion@email.com]</a></li>
                    <li><strong>Teléfono:</strong> [Número de teléfono]</li>
                </ul>

                <h2>2. OBJETO Y ÁMBITO DE APLICACIÓN</h2>
                <p>El presente Aviso Legal regula el acceso, la navegación y el uso de este sitio web (en adelante, el "Sitio Web"). El acceso y uso del Sitio Web implica la aceptación de estas condiciones y de la normativa aplicable.</p>

                <h2>3. ACCESO Y USO DEL SITIO WEB</h2>
                <h3>3.1 Naturaleza de la información</h3>
                <p>Los contenidos del Sitio Web tienen una finalidad meramente informativa y no sustituyen la relación terapéutica entre la persona paciente y la profesional. No constituyen diagnóstico ni tratamiento.</p>

                <h3>3.2 Responsabilidad en el uso</h3>
                <p>El usuario/a se compromete a hacer un uso lícito y correcto del Sitio Web; queda prohibido el uso con fines ilícitos o perjudiciales.</p>

                <h2>4. PROPIEDAD INTELECTUAL E INDUSTRIAL</h2>
                <p>Todos los derechos de propiedad intelectual e industrial del Sitio Web y de sus contenidos corresponden a la Profesional o a sus titulares. Queda prohibida la reproducción, distribución, comunicación pública y transformación sin autorización.</p>

                <h2>5. ENLACES</h2>
                <h3>5.1 Enlaces a otras webs</h3>
                <p>El Sitio Web puede contener enlaces a webs de terceros; la Profesional no se responsabiliza del contenido ni de las marcas o servicios de estos enlaces.</p>

                <h3>5.2 Enlaces desde otras webs</h3>
                <p>No se permite el establecimiento de enlaces hacia el Sitio Web sin la autorización previa y por escrito de la Profesional.</p>

                <h2>6. EXENCIÓN DE GARANTÍAS Y RESPONSABILIDAD</h2>
                <p>La Profesional no garantiza la continuidad, disponibilidad ni la veracidad absoluta del Sitio Web; tampoco se responsabiliza de los daños que puedan derivarse del uso del mismo cuando no sean imputables a la Profesional.</p>

                <h2>7. PROTECCIÓN DE DATOS PERSONALES</h2>
                <p>Los datos personales recogidos serán tratados de acuerdo con nuestra <a href="/es/privacy.php">Política de Privacidad</a>, en cumplimiento del RGPD y de la normativa vigente. Para más información, consulte la política de privacidad.</p>

                <h2>8. LEGISLACIÓN APLICABLE Y JURISDICCIÓN</h2>
                <p>La legislación aplicable será la legislación española y, para la resolución de controversias, las partes se someten a los juzgados y tribunales de Girona, con renuncia a cualquier otro fuero, salvo que la normativa disponga lo contrario.</p>

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
