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
        $items = SEO_OnPage::llistarPaginesActives('terms');
        if (!empty($items) && isset($items[0]) && $items[0] instanceof SEO_OnPage) {
            $pagina_seo = $items[0];
            $seoTitle = $pagina_seo->getTitle($lang) ?: null;
        }
    } catch (Exception $e) { }

    if (!$seoTitle) {
        $tries = ['/terms.php','/terms','/ca/terms.php','/ca/terms','/terms-i-condicions'];
        foreach ($tries as $r) {
            try {
                $tmp = SEO_OnPage::carregarPerUrl($r, $lang);
                if ($tmp instanceof SEO_OnPage) { $pagina_seo = $tmp; $seoTitle = $pagina_seo->getTitle($lang) ?: null; break; }
            } catch (Exception $e) { }
        }
    }

    if (!$seoTitle) {
        $seoTitle = ($lang === 'es') ? 'Términos y condiciones - Yanina Parisi' : 'Termes i condicions - Yanina Parisi';
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
    if (!$canonical) $canonical = $base_url . (($lang === 'es') ? '/es/terms.php' : '/ca/terms.php');
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
            <h1 class="hero-title"><?php echo htmlspecialchars(isset($pagina_seo) && $pagina_seo instanceof SEO_OnPage ? $pagina_seo->getH1($lang) : 'Termes i condicions'); ?></h1>
        </div>
    </section>

    <main class="container">
        <?php
            // Breadcrumbs: Home > Termes i condicions
            if (function_exists('render_breadcrumbs')) {
                render_breadcrumbs([
                    ['label' => t('nav_home'), 'url' => 'home.php'],
                    ['label' => 'Termes i condicions']
                ]);
            }
        ?>

        <section class="content-section">
            <div class="policy-content">
                <h2>1. INFORMACIÓ GENERAL</h2>
                <p>Aquests termes i condicions (en endavant, "els Termes") regulen la relació professional entre la clienta/client (en endavant, "el/la Client/a") i <strong>[Nom i cognoms de la psicòloga]</strong> (en endavant, "la Professional"), amb NIF <strong>[Número]</strong>, col·legiada al Col·legi Oficial de Psicologia de Catalunya amb el número <strong>[Número]</strong>, i amb domicili professional a <strong>[Adreça completa, Girona]</strong>.</p>
                <p>La contractació dels serveis oferts per la Professional implica l'acceptació plena i sense reserves de tots i cadascun d'aquests Termes.</p>

                <h2>2. NATURESA DEL SERVEI</h2>
                <p>La Professional ofereix serveis de psicologia clínica i psicoteràpia. Aquests serveis tenen com a objectiu la promoció del benestar emocional, l'avaluació, el diagnòstic i el tractament psicològic de diverses dificultats i trastorns mentals.</p>
                <p>És important entendre que:</p>
                <ul>
                    <li>La psicoteràpia no és una ciència exacta i els resultats poden variar en funció de múltiples factors.</li>
                    <li>El procés terapèutic requereix un compromís actiu i un treball per part del/de la Client/a entre les sessions.</li>
                    <li>La Professional es compromet a oferir els seus serveis amb la màxima qualitat, rigor professional i basant-se en evidències científiques i protocols reconeguts.</li>
                </ul>

                <h2>3. PROCÉS DE CONTRACTACIÓ I CITA PRÈVIA</h2>
                <p><strong>Sol·licitud de cita:</strong> La sol·licitud de cita es pot realitzar mitjançant el formulari de contacte de la web, correu electrònic o telèfon. Aquesta sol·licitud no suposa la confirmació de la cita.</p>
                <p><strong>Confirmació:</strong> La cita només es considerarà confirmada un cop rebuda una confirmació explícita per part de la Professional (mitjançant correu electrònic, SMS o trucada telefònica).</p>
                <p><strong>Questionari inicial:</strong> Abans de la primera sessió, es podrà sol·licitar al/la Client/a que ompli un qüestionari inicial per a una millor preparació de la sessió.</p>

                <h2>4. POLÍTICA DE PAGAMENTS</h2>
                <p><strong>Tarifes:</strong> Les tarifes vigents per als diferents tipus de serveis (sessió individual, sessió familiar, etc.) es comunicaran al/la Client/a de forma transparent abans de la confirmació de la primera cita. Les tarifes poden ser revisades anualment.</p>
                <p><strong>Forma de pagament:</strong> El pagament es realitzarà mitjançant <em>[Indicar mètode: transferència bancària, efectiu, targeta, Bizum]</em> i haurà d'abonar-se en el mateix dia en què es presta el servei, tret que s'acordi una modalitat diferent per escrit.</p>
                <p><strong>Facturació:</strong> Es lliurarà una factura o rebut de pagament a totes aquelles persones que ho sol·licitin.</p>

                <h2>5. POLÍTICA D'ANUL·LACIÓ I CANVIS DE CITA</h2>
                <p>La Professional dedica un temps específic i exclusiu a cada client. Per aquest motiu:</p>
                <ul>
                    <li>Les anul·lacions o canvis de cita s'han de comunicar amb un mínim de 24 hores d'antelació.</li>
                    <li>Les anul·lacions o canvis notificats amb menys de 24 hores d'antelació, o la no comparèixea a la sessió sense avís previ (<em>"no-show"</em>), podran suposar el cobrament del 100% de l'import de la sessió.</li>
                    <li>La Professional es compromet a aplicar la mateixa política en cas d'anul·lació per part seva, intentant oferir una nova data el més aviat possible.</li>
                </ul>

                <h2>6. CONFIDENCIALITAT</h2>
                <p>La Professional està obligada per llei i pel seu codi deontològic a mantenir la més estricta confidencialitat sobre tota la informació revelada durant el procés terapèutic. Aquesta confidencialitat només podrà veure's trencada en les situacions excepcionals previstes per la llei:</p>
                <ul>
                    <li>Quan existeixi un risc greu i imminent per a la vida del/de la Client/a o per a terceres persones.</li>
                    <li>En casos de sospita fundada de maltractament o abús a menors o persones en situació de dependència.</li>
                    <li>Per requeriment judicial legalment previst.</li>
                </ul>

                <h2>7. DURADA I FINALITZACIÓ DELS SERVEIS</h2>
                <p>La durada del procés terapèutic serà variable i dependrà dels objectius establerts i de l'evolució del/de la Client/a. El/la Client/a o la Professional poden donar per finalitzat el servei en qualsevol moment.</p>
                <p>Es recomana que la finalització es dugui a terme d'una manera pactada, preferiblement amb una sessió de cloenda, per tal de tancar adequadament el procés.</p>

                <h2>8. LIMITACIÓ DE RESPONSABILITAT</h2>
                <p>La Professional no es fa responsable de les decisions preses pel/de la Client/a basades en la informació o les discussions mantingudes durant les sessions de teràpia. La responsabilitat última del propi benestar i de les accions preses roman en el/la Client/a.</p>
                <p>En situacions d'emergència o crisi greu (ideació suïcida, psicosi, etc.), el/la Client/a s'ha de posar en contacte amb els serveis d'urgències (telèfon 112), anar a l'hospital més proper o trucar al telèfon de suïcidi 024, ja que la Professional no pot oferir un servei d'atenció 24 hores.</p>

                <h2>9. PROTECCIÓ DE DADES PERSONALS</h2>
                <p>Les dades personals i especialment les dades de salut seran tractades d'acord amb la Política de Privacitat, disponible a la pàgina web, i en compliment del Reglament General de Protecció de Dades (RGPD) i la Llei Orgànica de Protecció de Dades Personales i garantia dels drets digitals (LOPDGDD).</p>

                <h2>10. PROPIETAT INTEL·LECTUAL</h2>
                <p>Tot el material (articles, fulletons, qüestionaris, etc.) lliurat al/la Client/a per part de la Professional és per al seu ús exclusiu i personal. Està prohibit la seva reproducció, distribució o modificació sense el consentiment explícit per escrit de la Professional.</p>

                <h2>11. ACCEPTACIÓ I MODIFICACIÓ DELS TERMES</h2>
                <p>La contractació del servei implica l'acceptació plena d'aquests Termes i Condicions. La Professional es reserva el dret de modificar aquests Termes. Els canvis seran notificats als/les Clients i entraran en vigor amb caràcter general un mes després de la seva publicació a la pàgina web.</p>

                <h2>12. FORUM DE SOLUCIÓ DE CONTROVÈRSIES I JURISDICCIÓ</h2>
                <p>Les parts es comprometen a intentar resoldre qualsevol controvèrsia derivada d'aquests Termes mitjançant la negociació de bona fe. En cas de no arribar a un acord, les parts es sotmetran als jutjats i tribunals de la ciutat de Girona, amb renúncia expressa a qualsevol altre fur, si escau.</p>

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
