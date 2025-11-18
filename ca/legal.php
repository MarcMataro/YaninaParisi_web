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
        $tries = ['/legal.php','/legal','/ca/legal.php','/ca/legal','/avis-legal'];
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
        <meta property="og:locale" content="<?php echo $lang === 'ca' ? 'ca_ES' : 'es_ES'; ?>">

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
            <?php /* hero image as <img> so paths resolve consistently */ ?>
            <img src="<?php echo htmlspecialchars(resolve_media_url('../img/Portada.jpg')); ?>" alt="Portada" class="hero-img">
            <div class="container hero-content">
                <h1 class="hero-title"><?php echo htmlspecialchars(isset($pagina_seo) && $pagina_seo instanceof SEO_OnPage ? $pagina_seo->getH1($lang) : 'Avís legal'); ?></h1>
            </div>
        </section>

        <main class="container">
            <?php
                // Breadcrumbs: Home > Avís legal
                if (function_exists('render_breadcrumbs')) {
                    render_breadcrumbs([
                        ['label' => t('nav_home'), 'url' => 'home.php'],
                        ['label' => 'Avís legal']
                    ]);
                }
            ?>

            <section class="content-section">
                <div class="policy-content">
                    <h2>1. INFORMACIÓ GENERAL</h2>
                    <p>D'acord amb el que estableix l'article 10 de la Llei 34/2002, d'11 de juliol, de Serveis de la Societat de la Informació i del Comerç Electrònic (LSSI-CE), facilito la informació de caràcter general relativa al present lloc web:</p>

                    <ul class="contact-details">
                        <li><strong>Responsable:</strong> [Nom i cognoms de la psicòloga]</li>
                        <li><strong>NIF/CIF:</strong> [Número d'identificació]</li>
                        <li><strong>Domicili professional:</strong> [Adreça completa del consultori, Girona]</li>
                        <li><strong>Col·legiada:</strong> Col·legi Oficial de Psicologia de Catalunya, núm. [XXXXX]</li>
                        <li><strong>Correu electrònic:</strong> <a href="mailto:[adreça@email.com]">[adreça@email.com]</a></li>
                        <li><strong>Telèfon:</strong> [Número de telèfon]</li>
                    </ul>

                    <h2>2. OBJECTE I ÀMBIT D'APLICACIÓ</h2>
                    <p>El present Avís Legal regula l'accés, la navegació i l'ús d'aquest lloc web (en endavant, el "Lloc Web"). L'accés i l'ús del Lloc Web suposa l'acceptació d'aquestes condicions i de la normativa aplicable.</p>

                    <h2>3. ACCÉS I UTILITZACIÓ DEL LLOC WEB</h2>
                    <h3>3.1 Caràcter de la informació</h3>
                    <p>Els continguts del Lloc Web tenen finalitat merament informativa i no substitueixen la relació terapèutica entre el/la pacient i la professional. No constitueixen diagnòstic ni tractament.</p>

                    <h3>3.2 Responsabilitat de l'ús</h3>
                    <p>L'usuari/ària es compromet a fer un ús lícit i correcte del Lloc Web; queda prohibit l'ús amb finalitats il·lícites o nocius.</p>

                    <h2>4. PROPIETAT INTEL·LECTUAL I INDUSTRIAL</h2>
                    <p>Tots els drets de propietat intel·lectual i industrial del Lloc Web i dels seus continguts corresponen a la Professional o als seus titulars. Queda prohibida la reproducció, distribució, comunicació pública i transformació sense autorització.</p>

                    <h2>5. ENLLAÇOS</h2>
                    <h3>5.1 Enllaços a altres webs</h3>
                    <p>El Lloc Web pot contenir enllaços a webs de tercers; la Professional no es responsabilitza del contingut ni de les marques o serveis d'aquests enllaços.</p>

                    <h3>5.2 Enllaços des d'altres webs</h3>
                    <p>No es permet l'establiment d'enllaços cap al Lloc Web sense l'autorització prèvia i per escrit de la Professional.</p>

                    <h2>6. EXEMPCIÓ DE GARANTIES I RESPONSABILITAT</h2>
                    <p>La Professional no garanteix la continuïtat, disponibilitat o veracitat absoluta del Lloc Web; tampoc es responsabilitza dels danys que puguin derivar-se de l'ús del mateix quan no siguin imputables a la Professional.</p>

                    <h2>7. PROTECCIÓ DE DADES DE CARÀCTER PERSONAL</h2>
                    <p>Les dades personals recollides seran tractades d'acord amb la nostra <a href="/ca/privacy.php">Política de Privacitat</a>, en compliment del RGPD i la normativa vigent. Per a més informació, consulteu la política de privacitat.</p>

                    <h2>8. LEGISLACIÓ APLICABLE I JURISDICCIÓ</h2>
                    <p>La legislació aplicable serà la llei espanyola i, per a la resolució de disputes, les parts es sotmeten als jutjats i tribunals de Girona, amb renúncia a qualsevol altre fur, tret que la normativa disposi el contrari.</p>

                    <p class="last-updated"><strong>Data de l'última actualització:</strong> [Data]</p>
                </div>
            </section>
        </main>

        <?php include '_includes/footer.php'; ?>
        <script>
            // Script per a la navegació suau
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function (e) {
                    e.preventDefault();
                    document.querySelector(this.getAttribute('href')).scrollIntoView({
                        behavior: 'smooth'
                    });
                });
            });

            // Script per l'efecte scroll de la navegació
            window.addEventListener('scroll', function() {
                const header = document.querySelector('header');
                if (window.scrollY > 50) {
                    header.classList.add('scrolled');
                } else {
                    header.classList.remove('scrolled');
                }
            });

            // Script per al selector d'idioma
            document.addEventListener('DOMContentLoaded', function() {
                document.querySelectorAll('.lang-btn').forEach(btn => {
                    btn.addEventListener('click', function() {
                        // Obtenir l'idioma del data attribute
                        const lang = this.getAttribute('data-lang');
                        console.log('Botó clickat, idioma:', lang);
                    
                        // Eliminar classe active de tots els botons (tant desktop com mòbil)
                        document.querySelectorAll('.lang-btn').forEach(b => b.classList.remove('active'));
                        // Afegir classe active a tots els botons del mateix idioma
                        document.querySelectorAll(`.lang-btn[data-lang="${lang}"]`).forEach(b => b.classList.add('active'));
                    
                        // Tancar menú mòbil si està obert
                        const mobileMenuToggle = document.querySelector('.mobile-menu-toggle');
                        const navMenu = document.querySelector('.nav-menu ul');
                        if (mobileMenuToggle && navMenu) {
                            mobileMenuToggle.classList.remove('active');
                            navMenu.classList.remove('show');
                        }
                    
                        // Canviar idioma
                        changeLanguage(lang);
                    });
                });

                // Funcionalitat del menú hamburguesa
                const mobileMenuToggle = document.querySelector('.mobile-menu-toggle');
                const navMenu = document.querySelector('.nav-menu ul');

                if (mobileMenuToggle && navMenu) {
                    mobileMenuToggle.addEventListener('click', function() {
                        this.classList.toggle('active');
                        navMenu.classList.toggle('show');
                    });

                    // Tancar menú quan es clica un enllaç
                    document.querySelectorAll('.nav-menu ul li a').forEach(link => {
                        link.addEventListener('click', function() {
                            mobileMenuToggle.classList.remove('active');
                            navMenu.classList.remove('show');
                        });
                    });

                    // Tancar menú quan es clica fora
                    document.addEventListener('click', function(e) {
                        if (!mobileMenuToggle.contains(e.target) && !navMenu.contains(e.target)) {
                            mobileMenuToggle.classList.remove('active');
                            navMenu.classList.remove('show');
                        }
                    });
                }
            });
        </script>
        <script src="../js/site-nav.js"></script>
        <script src="../js/language.js"></script>
    </body>
    </html>
