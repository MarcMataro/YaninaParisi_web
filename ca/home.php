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

// Incluir sistema de traducció
include '../includes/functions.php';

$lang = getCurrentLanguage();
?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- METAETIQUETES ESSENCIALS -->
        <?php
        $base_url = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'];
        ?>
    <?php
    // Carregar la classe SEO_OnPage i intentar obtenir el title configurat per a la pàgina "home"
    require_once __DIR__ . '/../classes/seo_onpage.php';

    
    $seoTitle = null;

    // 1) Comprovem si hi ha una pàgina activa de tipus 'home' al llistat
    $homePages = SEO_OnPage::llistarPaginesActives('home');
    if (!empty($homePages) && isset($homePages[0]) && $homePages[0] instanceof SEO_OnPage) {
        $pagina_seo = $homePages[0];
        $seoTitle = $pagina_seo->getTitle($lang) ?: null;
    }

    // 2) Si no hi ha resultat, intentem carregar per URL relativa (fallback)
    if (!$seoTitle) {
        $rel = ($lang === 'es') ? '/' : '/';
        $pagina_seo = SEO_OnPage::carregarPerUrl($rel, $lang);
        if ($pagina_seo) {
            $seoTitle = $pagina_seo->getTitle($lang) ?: null;
        }
    }

    // 3) Si encara no tenim títol, posem un fallback sensible
    if (!$seoTitle) {
        $seoTitle = ($lang === 'es') ? 'Yanina Parisi - Psicóloga' : 'Yanina Parisi - Psicòloga';
    }
    
    // Obtenir la meta description des de la configuració SEO (mateixa pàgina que hem trobat)
    $seoDescription = null;
    if (isset($pagina_seo) && $pagina_seo instanceof SEO_OnPage) {
        $seoDescription = $pagina_seo->getMetaDescription($lang) ?: null;
    }
    // Fallback a la traducció per defecte si no hi ha cap descripció SEO configurada
    if (!$seoDescription) {
        $seoDescription = t('meta_description');
    }
    ?>
    <title><?php echo htmlspecialchars($seoTitle); ?></title>
    <meta name="description" content="<?php echo htmlspecialchars($seoDescription); ?>">
    <meta name="keywords" content="<?php echo t('meta_keywords'); ?>">
    <meta name="author" content="Yanina Parisi">
    <meta name="robots" content="index, follow">
    <meta name="theme-color" content="#aa9e6b">
    
    <!-- Canonical URL -->
        <?php
        // Preferir la URL canònica configurada per la pàgina SEO
        $canonical = null;
        if (isset($pagina_seo) && $pagina_seo instanceof SEO_OnPage) {
            $canonical = $pagina_seo->getCanonicalUrl($lang);
        }
        if (!$canonical) {
            // Fallback per a home segons l'idioma
            $canonical = $base_url . (($lang === 'es') ? '/es/home.php' : '/ca/home.php');
        }
        ?>
        <link rel="canonical" href="<?php echo htmlspecialchars($canonical); ?>">
    
    <!-- Icons -->
    <link rel="icon" type="image/png" sizes="32x32" href="../img/Logo32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="../img/Logo16.png">
    <link rel="apple-touch-icon" sizes="180x180" href="../img/apple-touch-icon.png">
    
    <!-- Open Graph / Facebook -->
    <?php
    // Build OG tags ensuring absolute HTTPS URLs for images
    $og_title = isset($pagina_seo) && $pagina_seo instanceof SEO_OnPage ? $pagina_seo->getOgTitle($lang) : $seoTitle;
    $og_description = isset($pagina_seo) && $pagina_seo instanceof SEO_OnPage ? $pagina_seo->getOgDescription($lang) : $seoDescription;
    $og_image = null;
    if (isset($pagina_seo) && $pagina_seo instanceof SEO_OnPage) {
        $og_image = $pagina_seo->getOgImage();
    }
    if (!$og_image) {
        $og_image = '/img/Logo.png';
    }
    // make absolute https URL if needed
    if (!preg_match('#^https?://#i', $og_image)) {
        $og_image = (strpos($base_url, 'http') === 0 ? $base_url : 'https://' . $_SERVER['HTTP_HOST']) . '/' . ltrim($og_image, '/');
    }

    $og_url = htmlspecialchars($canonical ?: ($base_url . $_SERVER['REQUEST_URI']));
    ?>
    <meta property="og:type" content="<?php echo (isset($pagina_seo) ? ($pagina_seo->getTipoPagina() === 'articulo' ? 'article' : 'website') : 'website'); ?>">
    <meta property="og:url" content="<?php echo $og_url; ?>">
    <meta property="og:title" content="<?php echo htmlspecialchars($og_title); ?>">
    <meta property="og:description" content="<?php echo htmlspecialchars($og_description); ?>">
    <meta property="og:image" content="<?php echo htmlspecialchars($og_image); ?>">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:site_name" content="<?php echo htmlspecialchars(t('meta_og_site_name')); ?>">
    <meta property="og:locale" content="<?php echo getCurrentLanguage() === 'ca' ? 'ca_ES' : 'es_ES'; ?>">
    
    <!-- Twitter -->
    <?php
    // Build Twitter tags and ensure absolute HTTPS image URL
    $tw_title = isset($pagina_seo) && $pagina_seo instanceof SEO_OnPage ? $pagina_seo->getTwitterTitle($lang) : $seoTitle;
    $tw_description = isset($pagina_seo) && $pagina_seo instanceof SEO_OnPage ? $pagina_seo->getTwitterDescription($lang) : $seoDescription;
    $tw_image = null;
    if (isset($pagina_seo) && $pagina_seo instanceof SEO_OnPage) {
        $tw_image = $pagina_seo->getTwitterImage();
    }
    if (!$tw_image) {
        $tw_image = '/img/Logo.png';
    }
    if (!preg_match('#^https?://#i', $tw_image)) {
        $tw_image = (strpos($base_url, 'http') === 0 ? $base_url : 'https://' . $_SERVER['HTTP_HOST']) . '/' . ltrim($tw_image, '/');
    }
    ?>
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?php echo htmlspecialchars($tw_title); ?>">
    <meta name="twitter:description" content="<?php echo htmlspecialchars($tw_description); ?>">
    <meta name="twitter:image" content="<?php echo htmlspecialchars($tw_image); ?>">


    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@7.0.0/css/all.min.css">
    <link rel="stylesheet" href="../css/estils.css">
    <link rel="stylesheet" href="../css/brands.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Libre+Baskerville:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet">
    
    <!-- Schema Markup JSON-LD -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "Psychologist",
        "name": "Yanina Parisi",
    "description": "<?php echo htmlspecialchars($seoDescription); ?>",
        "url": "<?php echo 'https://' . $_SERVER['HTTP_HOST']; ?>",
        "telephone": "+34-XXX-XXX-XXX",
        "email": "info@yaninaparisi.com",
        "image": "<?php echo 'https://' . $_SERVER['HTTP_HOST']; ?>/img/img_2282.jpeg",
        "priceRange": "€€",
        "address": {
            "@type": "PostalAddress",
            "addressLocality": "Girona",
            "addressRegion": "Catalunya", 
            "addressCountry": "ES"
        },
        "geo": {
            "@type": "GeoCoordinates",
            "latitude": "41.9794",
            "longitude": "2.8214"
        },
        "openingHours": "Mo-Fr 09:00-19:00",
        "serviceArea": {
            "@type": "Country",
            "name": "España"
        },
        "medicalSpecialty": [
            "Psychology",
            "Couple Therapy", 
            "Individual Therapy",
            "Anxiety Treatment",
            "Depression Treatment"
        ],
        "areaServed": [
            "Girona",
            "Catalunya", 
            "España"
        ]
    }
    </script>
</head>
<body>
    <?php include '_includes/navigation.php'; ?>

    <!-- Secció Hero -->
    <section class="hero" id="inici">
        <?php /* hero image as an <img> element so path resolves correctly across subfolders */ ?>
        <img src="<?php echo htmlspecialchars(resolve_media_url('img/portada.jpg')); ?>" alt="Portada" class="hero-img">
        <div class="container hero-content">
            <h1 class="hero-title">
                <?php
                    //Aquí escriurem el títol HA que hi ha a les dades del SEO que hem extret abans
                    //La variable a la classe és $h1_ca per al català. Hem d'extreure-la de les dades del SEO.
                    echo htmlspecialchars(isset($pagina_seo) && $pagina_seo instanceof SEO_OnPage ? $pagina_seo->getH1($lang) : 'Yanina Parisi - Psicòloga');
                ?>
            </h1>
            <p class="hero-subtitle">Construeix la vida que desitges. El teu canvi comença aquí</p>

            <div class="hero-buttons">
                <a href="#contacte" class="btn btn-primary">
                    <i class="fas fa-calendar-check"></i>
                    Primera consulta de franc!
                </a>
            </div>
        </div>
    </section>

    <!-- Frase inspiradora -->
    <section class="quote-section">
        <div class="container">
            <div class="quote-content">
                <blockquote>
                    <p>"El primer pas cap al canvi és l'acceptació. El segon és l'acció. I jo t'acompanyaré en ambdós."</p>
                </blockquote>
            </div>
        </div>
    </section>

    <!-- Especialitats -->
    <section id="serveis" class="specialties-section">
        <div class="container">
            <div class="section-title">
                <h2>Especialitats i àrees d'intervenció</h2>
            </div>
            <div class="specialties-grid">
                <!-- Salut Mental Adults -->
                <div class="specialty-card">
                    <div class="specialty-icon">
                        <i class="fas fa-brain"></i>
                    </div>
                    <h3>Benestar en adults</h3>
                    <ul>
                        <li>Ansietat i atacs de pànic</li>
                        <li>Depressió i tristesa persistent</li>
                        <li>Transtorn obsessiu compulsiu (TOC)</li>
                        <li>Crisis vitals i canvis personals</li>
                        <li>Problemes d'autoestima</li>
                        <li>Gestió del dol</li>
                    </ul>
                </div>
                
                <!-- Teràpia de Parella i Família -->
                <div class="specialty-card">
                    <div class="specialty-icon">
                        <i class="fas fa-heart"></i>
                    </div>
                    <h3>Dinàmica de parella i de família</h3>
                    <ul>
                        <li>Mediació i resolució de conflictes</li>
                        <li>Millora de la comunicació</li>
                        <li>Enfortiment dels vincles familiars</li>
                        <li>Teràpia de parella</li>
                        <li>Acompanyament en separacions</li>
                    </ul>
                </div>

                <!-- Coaching personalitzat -->
                <div class="specialty-card">
                    <div class="specialty-icon">
                        <i class="fas fa-user-check"></i>
                    </div>
                    <h3>Coaching personalitzat</h3>
                    <ul>
                        <li>Hàbits i productivitat</li>
                        <li>Gestió de la auto-exigència</li>
                        <li>Creixement personal</li>
                        <li>Transisions de la vida</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- Serveis Especials -->
    <section class="special-services">
        <div class="container">
            <div class="section-title">
                <h2>Serveis especialitzats</h2>
            </div>
            <div class="services-special-grid">
                <div class="service-special-card">
                    <div class="service-special-header">
                        <i class="fas fa-heart-circle-check"></i>
                        <h3>Acompanyament en la cerca de parella</h3>
                    </div>
                    <p>Servei psicològic per a persones que busquen una relació significativa. Basat en <strong>criteris de compatibilitat psicològica</strong> per a vincles estables i de qualitat.</p>
                    <ul>
                        <li>Anàlisi psicològic del perfil emocional i relacional</li>
                        <li>Identificació de valors compatibles</li>
                        <li>Acompanyament en el procés de cerca</li>
                    </ul>
                </div>
                
                <div class="service-special-card">
                    <div class="service-special-header">
                        <i class="fas fa-scale-balanced"></i>
                        <h3>Psicologia pericial judicial</h3>
                    </div>
                    <p><strong>Psicòloga judicial</strong> amb formació específica en l\'àmbit jurídic. Elaboració d\'informes pericials psicològics per a processos legals.</p>
                    <ul>
                        <li>Informes per a casos de família</li>
                        <li>Assessoria en custòdies</li>
                        <li>Violència filioparental</li>
                        <li>Procediments legals diversos</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- Sobre mi -->
    <section id="sobre-mi" class="about-section">
        <div class="container">
            <div class="about-content">
                <div class="about-image">
                    <img src="../img/img_2282.jpeg" 
                         alt="Yanina Parisi - Psicòloga General Sanitària Col·legiada a Girona"
                         width="300" 
                         height="350"
                         loading="lazy">
                </div>
                
                <div class="about-text">
                    <h2 class="about-title">
                        Psicologia pràctica per al teu benestar
                    </h2>
                    
                    <div class="about-intro">
                        <p>Sóc Yanina Parisi, psicòloga general sanitària col·legiada amb més de cinc anys d'experiència. El meu objectiu és oferir-te un espai segur on puguis superar el malestar i construir la vida que vols, ja sigui recuperant el teu equilibri emocional o trobant una parella realment compatible.</p>
                    </div>
                    
                    <div class="about-services">
                        <h3>Com treballo?</h3>
                        
                        <div class="service-item">
                            <h4>Teràpia individual:</h4>
                            <p>Especialitzada en ansietat, depressió, TOC i crisis vitals. Junts, trobarem les eines perquè recuperis el control.</p>
                        </div>
                        
                        <div class="service-item">
                            <h4>Teràpia de parella:</h4>
                            <p>Gestió de conflictes i millora de la comunicació per enfortir el vostre vincle.</p>
                        </div>
                        
                        <div class="service-item">
                            <h4>Cerca de parella conscient:</h4>
                            <p>Un servei únic basat en criteris psicològics per a qui busca relacions estables i de qualitat, lluny del desgast de les apps convencionals.</p>
                        </div>
                    </div>
                    
                    <div class="about-location">
                        <p>Atenció personalitzada online a tota Espanya i presencial a Girona.</p>
                    </div>
                    
                    <div class="about-actions">
                        <a href="../contacta.php" class="btn btn-primary">
                            Reserva la teva primera consulta gratuïta!
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Segona cita inspiradora -->
    <section class="quote-section">
        <div class="container">
            <div class="quote-content">
                <blockquote>
                    <p>"Sempre tens l'última llibertat, la de triar la teva actitud. Triar demanar consell i acompanyament és el primer pas per transformar el teu destí."</p>
                </blockquote>
            </div>
        </div>
    </section>

    <!-- Testimonis -->
    <section id="testimonis">
        <div class="container">
            <div class="section-title">
                <h2>Testimonis</h2>
            </div>
            <div class="testimonials-grid">
                <div class="testimonial-card">
                    <p class="testimonial-text">"La Yanina m'ha ajudat a superar la meva ansietat com mai havia imaginat. Les seves tècniques i suport em van donar les eines necessàries per afrontar les meves pors."</p>
                    <div class="testimonial-author">
                        <div class="author-image"><i class="fas fa-user"></i></div>
                        <div>
                            <h4>Laura G.</h4>
                            <p>Pacient des de 2021</p>
                        </div>
                    </div>
                </div>
                <div class="testimonial-card">
                    <p class="testimonial-text">"Després de poc més de sis mesos de teràpia de parella, la nostra relació ha millorat dràsticament. Gràcies a la Yanina per ensenyar-nos a comunicar-nos millor."</p>
                    <div class="testimonial-author">
                        <div class="author-image"><i class="fas fa-users"></i></div>
                        <div>
                            <h4>Marc i Elena</h4>
                            <p>Pacients des de 2022</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Tarifes -->
    <section id="tarifes">
        <div class="container">
            <div class="section-title">
                <h2>Aquestes són les meves tarifes</h2>
            </div>
            <div class="pricing-grid">
                <div class="pricing-card">
                    <div class="pricing-header">
                        <i class="fas fa-gift pricing-icon"></i>
                        <h3>Primera sessió</h3>
                    </div>
                    <div class="pricing-features">
                        <div class="price">
                            <span class="original-price">60</span>
                            <span class="amount">0</span>
                            <span class="currency">€</span>
                            <span class="period">/sessió</span>
                        </div>
                        <ul>
                            <li><i class="fas fa-check"></i> Primera sessió de valoració de franc</li>
                            <li><i class="fas fa-check"></i> Explica'm el que t'angoixa</li>
                            <li><i class="fas fa-check"></i> Valorem junts els teus objectius</li>
                            <li><i class="fas fa-check"></i> Coneix el meu mètode</li>
                        </ul>
                    </div>
                    <a href="#contacte" class="btn pricing-btn">Vull començar ara!</a>
                </div>

                <div class="pricing-card">
                    <div class="pricing-header">
                        <i class="fas fa-heart pricing-icon"></i>
                        <h3>Sessió individual</h3>
                    </div>
                    <div class="pricing-features">
                        <div class="price">
                            <span class="amount">60</span>
                            <span class="currency">€</span>
                            <span class="period">/sessió</span>
                        </div>
                        <ul>
                            <li><i class="fas fa-check"></i> Trobada de 60 minuts</li>
                            <li><i class="fas fa-check"></i> Atenció totalment personalitzada</li>
                            <li><i class="fas fa-check"></i> Flexibilitat horària</li>
                            <li><i class="fas fa-check"></i> Seguiment continuat</li>
                        </ul>
                    </div>
                    <a href="#contacte" class="btn pricing-btn">Programa una sessió</a>
                </div>
                <div class="pricing-card">
                    <div class="pricing-header">
                        <i class="fas fa-calendar-week pricing-icon"></i>
                        <h3>Pack quinzenal</h3>
                    </div>
                    <div class="pricing-features">
                        <div class="price">
                            <span class="amount">100</span>
                            <span class="currency">€</span>
                            <span class="period">/sessió</span>
                        </div>
                        <ul>
                            <li><i class="fas fa-check"></i> Dues sessions al mes</li>
                            <li><i class="fas fa-check"></i> Seguiment regular</li>
                            <li><i class="fas fa-check"></i> Cada sessió et surt per 50€</li>
                            <li><i class="fas fa-check"></i> Acompanyament entre sessions</li>
                        </ul>
                    </div>
                    <a href="#contacte" class="btn pricing-btn">Programa una sessió</a>
                </div>

                <div class="pricing-card">
                    <div class="pricing-header">
                        <i class="fas fa-calendar-alt pricing-icon"></i>
                        <h3>Pack mensual</h3>
                    </div>
                    <div class="pricing-features">
                        <div class="price">
                            <span class="amount">180</span>
                            <span class="currency">€</span>
                            <span class="period">/sessió</span>
                        </div>
                        <ul>
                            <li><i class="fas fa-check"></i> Quatre sessions al mes</li>
                            <li><i class="fas fa-check"></i> Sessions per 45€. Estalvi d'un 25%</li>
                            <li><i class="fas fa-check"></i> Procés accelerat i constant</li>
                            <li><i class="fas fa-check"></i> Resultats optimitzats</li>
                        </ul>
                    </div>
                    <a href="#contacte" class="btn pricing-btn">Programa una sessió</a>
                </div>
            </div>
        </div>
    </section>

    <!-- Peu de pàgina -->
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
</body>
</html>