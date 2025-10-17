<?php
// Inicializar sesión si no está iniciada

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Forçar idioma espanyol en aquesta pàgina
$_SESSION['language'] = 'es';
// Procesar cambio de idioma primero
if (isset($_GET['lang'])) {
    $lang = $_GET['lang'];
    if (in_array($lang, array('ca', 'es'))) {
        $_SESSION['language'] = $lang;
        header('Location: /' . $lang . '/home.php');
        exit;
    }
}
// Incluir sistema de traducción
include '../includes/lang.php';
?>
<!DOCTYPE html>
<html lang="<?php echo getCurrentLanguage(); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo t('meta_title'); ?></title>
    <meta name="description" content="<?php echo t('meta_description'); ?>">
    <meta name="keywords" content="<?php echo t('meta_keywords'); ?>">
    <meta name="author" content="<?php echo t('meta_author'); ?>">
    <meta name="robots" content="index, follow">
    <meta name="theme-color" content="#aa9e6b">
    <link rel="canonical" href="https://<?php echo $_SERVER['HTTP_HOST']; ?>/es/home.php">
    <link rel="icon" type="image/png" sizes="32x32" href="../img/Logo32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="../img/Logo16.png">
    <link rel="apple-touch-icon" sizes="180x180" href="../img/apple-touch-icon.png">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?php echo 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>">
    <meta property="og:title" content="<?php echo t('meta_og_title'); ?>">
    <meta property="og:description" content="<?php echo t('meta_og_description'); ?>">
    <meta property="og:image" content="<?php echo 'http://' . $_SERVER['HTTP_HOST']; ?>/img/Logo.png">
    <meta property="og:site_name" content="<?php echo t('meta_og_site_name'); ?>">
    <meta property="og:locale" content="<?php echo getCurrentLanguage() === 'ca' ? 'ca_ES' : 'es_ES'; ?>">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?php echo t('meta_og_title'); ?>">
    <meta name="twitter:description" content="<?php echo t('meta_og_description'); ?>">
    <meta name="twitter:image" content="<?php echo 'http://' . $_SERVER['HTTP_HOST']; ?>/img/Logo.png">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@7.0.0/css/all.min.css">
    <link rel="stylesheet" href="../css/estils.css">
    <link rel="stylesheet" href="../css/brands.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Libre+Baskerville:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet">
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "Psychologist",
        "name": "Yanina Parisi",
        "description": "<?php echo t('meta_description'); ?>",
        "url": "<?php echo 'https://' . $_SERVER['HTTP_HOST']; ?>",
        "telephone": "+34-XXX-XXX-XXX",
        "email": "info@yaninaparisi.com",
        "image": "<?php echo 'http://' . $_SERVER['HTTP_HOST']; ?>/img/img_2282.jpeg",
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
    <section class="hero" id="inicio">
        <div class="container hero-content">
            <h1><span class="highlight">Yanina Parisi</span></h1>
            <h2 class="hero-subtitle">Psicóloga en Girona especializada en terapia individual, de pareja e infantil.</h2>
            <div class="hero-buttons">
                <a href="#contacto" class="btn btn-primary">
                    <i class="fas fa-calendar-check"></i>
                    Solicitar cita
                </a>
                <a href="#servicios" class="btn btn-secondary">
                    Ver servicios
                </a>
            </div>
        </div>
    </section>
    <section id="sobre-mi" class="about-section">
        <div class="container">
            <div class="about-content">
                <div class="about-image">
                    <img src="../img/img_2282.jpeg" alt="Yanina Parisi - Psicóloga en Girona" width="300" height="350" loading="lazy">
                </div>
                <div class="about-text">
                    <h2 class="about-title">Sobre mí</h2>
                    <p>Especialista en terapia individual, de pareja e infantil. Atención presencial y online en Girona.</p>
                    <div class="about-actions">
                        <a href="../contacta.php" class="btn btn-primary">Contactar</a>
                        <a href="../sobremi.php" class="btn btn-secondary">Saber más</a>
                    </div>
                </div>
            </div>
        </div>
    </section>
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