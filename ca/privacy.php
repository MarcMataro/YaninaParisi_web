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
        $items = SEO_OnPage::llistarPaginesActives('privacy');
        if (!empty($items) && isset($items[0]) && $items[0] instanceof SEO_OnPage) {
            $pagina_seo = $items[0];
            $seoTitle = $pagina_seo->getTitle($lang) ?: null;
        }
    } catch (Exception $e) { }

    if (!$seoTitle) {
        $tries = ['/privacy.php','/privacy','/ca/privacy.php','/ca/privacy','/politica-privacitat','/politica-de-privacidad'];
        foreach ($tries as $r) {
            try {
                $tmp = SEO_OnPage::carregarPerUrl($r, $lang);
                if ($tmp instanceof SEO_OnPage) { $pagina_seo = $tmp; $seoTitle = $pagina_seo->getTitle($lang) ?: null; break; }
            } catch (Exception $e) { }
        }
    }

    if (!$seoTitle) {
        $seoTitle = ($lang === 'es') ? 'Política de privacidad - Yanina Parisi' : 'Política de privacitat - Yanina Parisi';
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
    if (!$canonical) $canonical = $base_url . (($lang === 'es') ? '/es/privacy.php' : '/ca/privacy.php');
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
        <img src="<?php echo htmlspecialchars(resolve_media_url('img/portada.jpg')); ?>" alt="Portada" class="hero-img">
        <div class="container hero-content">
            <h1 class="hero-title"><?php echo htmlspecialchars(isset($pagina_seo) && $pagina_seo instanceof SEO_OnPage ? $pagina_seo->getH1($lang) : ( $lang === 'es' ? 'Política de privacidad' : 'Política de privacitat' )); ?></h1>
        </div>
    </section>

    <main class="container">
        <?php
            // Breadcrumbs: Home > Privacy (per-page insertion)
            if (function_exists('render_breadcrumbs')) {
                // Use an explicit, human-friendly label here (don't rely on nav_privacy key)
                render_breadcrumbs([
                    ['label' => t('nav_home'), 'url' => '/ca/home.php'],
                    ['label' => 'Política de privacitat']
                ]);
            }
        ?>

        <section class="content-section">
            <div class="policy-content">
                <h2>1. Responsable del tractament</h2>
                <p>El Responsable del tractament de les teves dades personals és:</p>
                <ul>
                    <li><strong>Nom:</strong> [Nom i cognoms de la psicòloga]</li>
                    <li><strong>NIF/CIF:</strong> [Número d'identificació]</li>
                    <li><strong>Domicili professional:</strong> [Adreça completa del consultori, Girona]</li>
                    <li><strong>Activitat:</strong> Psicòloga col·legiada (Col·legi Oficial de Psicologia de Catalunya, núm. [XXXXX])</li>
                    <li><strong>Correu electrònic:</strong> [adreça@email.com]</li>
                    <li><strong>Telèfon:</strong> [Número de telèfon]</li>
                </ul>

                <h2>2. Finalitat del tractament de les teves dades</h2>
                <p>En aquesta pàgina web recollim i tractem la informació que ens facilites amb les següents finalitats principals:</p>
                <ul>
                    <li><strong>Formulari de contacte:</strong> per gestionar i respondre consultes, sol·licituds d'informació o peticions de cita prèvia.</li>
                    <li><strong>Subscripció al blog o newsletter:</strong> per enviar-te materials d'interès, novetats del blog o recursos, sempre que hagis donat el teu consentiment explícit.</li>
                    <li><strong>Prestació del servei psicològic:</strong> si contractes els meus serveis, les dades s'utilitzaran per a la gestió administrativa, la prestació de la teràpia i el manteniment de la història clínica.</li>
                </ul>

                <h2>3. Legitimació per al tractament</h2>
                <p>La base legal per al tractament de les teves dades inclou, segons el cas:</p>
                <ul>
                    <li><strong>Consentiment:</strong> per respondre consultes i enviar comunicacions comercials o newsletters.</li>
                    <li><strong>Execució d'un contracte o relació precontractual:</strong> per gestionar la sol·licitud de cita i la posterior prestació del servei psicològic.</li>
                    <li><strong>Interès vital:</strong> en situacions d'emergència que puguin posar en perill la teva integritat física o la d'altres.</li>
                    <li><strong>Compliment d'una obligació legal:</strong> com a professional sanitari, estic obligada a custodiar la història clínica durant el termini legal corresponent.</li>
                </ul>

                <h2>4. Conservació de les dades</h2>
                <p>Les dades de contacte i les consultes es conservaran mentre no sol·licitis la seva supressió. Les dades relacionades amb la prestació del servei (història clínica) es conservaran durant el termini legalment establert per a professionals sanitaris (actualment 5 anys des de l'última intervenció, segons la Llei 41/2002, d'autonomia del pacient). Les dades per a l'enviament de newsletters es conservaran mentre mantinguis el teu consentiment.</p>

                <h2>5. Destinataris de les dades</h2>
                <p>No cediré les teves dades a tercers, excepte en els casos següents:</p>
                <ul>
                    <li><strong>Obligació legal:</strong> quan existeixi una obligació legal (per exemple, en casos de risc vital greu o per requeriment judicial).</li>
                    <li><strong>Amb el teu consentiment explícit:</strong> per a gestions específiques que ho requereixin i que hagis autoritzat prèviament.</li>
                </ul>
                <p>Com a professional, estic subjecta al secret professional i a les obligacions de confidencialitat més estrictes.</p>

                <h2>6. Els teus drets</h2>
                <p>Tens dret a:</p>
                <ul>
                    <li>Accedir a les teves dades personals.</li>
                    <li>Sol·licitar la rectificació de les dades inexactes.</li>
                    <li>Sol·licitar la supressió de les teves dades quan ja no siguin necessàries per als fins per als quals es van recollir.</li>
                    <li>Oposar-te al tractament de les teves dades.</li>
                    <li>Sol·licitar la limitació del tractament de les teves dades.</li>
                    <li>Exercir la portabilitat de les dades.</li>
                    <li>Retirar el teu consentiment en qualsevol moment, sense que això afecti la licitud del tractament basat en el consentiment previ.</li>
                </ul>
                <p>Pots exercir aquests drets mitjançant un escrit adreçat a <strong>[adreça postal del consultori o adreça electrònica indicada a l'apartat 1]</strong>. A més, tens dret a presentar una reclamació davant l'Autoritat Catalana de Protecció de Dades (APDCAT) o l'Agència Espanyola de Protecció de Dades (AEPD) si consideres que el tractament no s'ajusta a la normativa.</p>

                <h2>7. Origen de les dades</h2>
                <p>Les dades personals que tractem provenen, preferentment, de la pròpia persona interessada o del seu representant legal.</p>

                <h2>8. Dades especialment protegides</h2>
                <p>En el marc de la prestació del servei psicològic, és possible que tractem dades de salut, que són categories especials de dades segons la normativa. Aquest tractament es realitza amb la màxima confidencialitat i està amparat per les bases legals esmentades a l'apartat 3 (compliment d'obligacions en l'àmbit sanitari i interès vital).</p>

                <h2>9. Actualització de la política</h2>
                <p>Aquesta política de privacitat pot ser actualitzada en qualsevol moment per adaptar-se a novetats legislatives o canvis en les activitats. La versió més actualitzada es trobarà sempre disponible al nostre lloc web.</p>
                <p><strong>Data de l'última actualització:</strong> [Data]</p>
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
    <script src="../js/language.js"></script>
    <script src="../js/site-nav.js"></script>
</body>
</html>