<?php 
// Inicialitzar sessió si no està iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Processar canvi d'idioma
if (isset($_GET['lang'])) {
    if (in_array($_GET['lang'], array('ca', 'es'))) {
        $_SESSION['language'] = $_GET['lang'];
    }
    // Redirigir per evitar reenviar formulari
    $redirect_url = strtok($_SERVER["REQUEST_URI"], '?');
    header('Location: ' . $redirect_url);
    exit;
}

// Incluir sistema de traducció
include 'includes/lang.php';
?>
<!DOCTYPE html>
<html lang="<?php echo getCurrentLanguage(); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- METAETIQUETES ESSENCIALS -->
    <title>Sobre mi - <?php echo getCurrentLanguage() === 'ca' ? 'Yanina Parisi - Psicòloga General Sanitària' : 'Yanina Parisi - Psicóloga General Sanitaria'; ?></title>
    <meta name="description" content="<?php echo getCurrentLanguage() === 'ca' ? 'Coneix la trajectòria professional de Yanina Parisi, Psicòloga General Sanitària, Perita Judicial i Mediadora Familiar amb més d\'una dècada d\'experiència.' : 'Conoce la trayectoria profesional de Yanina Parisi, Psicóloga General Sanitaria, Perito Judicial y Mediadora Familiar con más de una década de experiencia.'; ?>">
    <meta name="keywords" content="<?php echo getCurrentLanguage() === 'ca' ? 'Yanina Parisi, psicòloga, trajectòria professional, experiència, Girona, teràpia online' : 'Yanina Parisi, psicóloga, trayectoria profesional, experiencia, Girona, terapia online'; ?>">
    <meta name="author" content="Yanina Parisi">
    
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?php echo 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>">
    <meta property="og:title" content="Sobre mi - Yanina Parisi">
    <meta property="og:description" content="<?php echo getCurrentLanguage() === 'ca' ? 'Coneix la trajectòria professional de Yanina Parisi, Psicòloga General Sanitària especialitzada en teràpia de parella i individual.' : 'Conoce la trayectoria profesional de Yanina Parisi, Psicóloga General Sanitaria especializada en terapia de pareja e individual.'; ?>">
    <meta property="og:image" content="<?php echo 'http://' . $_SERVER['HTTP_HOST']; ?>/img/Logo.png">
    <meta property="og:site_name" content="Yanina Parisi - Psicòloga">
    <meta property="og:locale" content="<?php echo getCurrentLanguage() === 'ca' ? 'ca_ES' : 'es_ES'; ?>">
    
    <!-- Twitter -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="Sobre mi - Yanina Parisi">
    <meta name="twitter:description" content="<?php echo getCurrentLanguage() === 'ca' ? 'Coneix la trajectòria professional de Yanina Parisi, Psicòloga General Sanitària especialitzada en teràpia de parella i individual.' : 'Conoce la trayectoria profesional de Yanina Parisi, Psicóloga General Sanitaria especializada en terapia de pareja e individual.'; ?>">
    <meta name="twitter:image" content="<?php echo 'http://' . $_SERVER['HTTP_HOST']; ?>/img/Logo.png">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@7.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/estils.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Libre+Baskerville:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet">
</head>
<body>
    <?php include 'includes/navigation.php'; ?>

    <!-- Secció Hero - Sobre Mi -->
    <section class="hero about-hero" id="sobre-mi">
        <div class="container hero-content">
            <h1><?php echo t('about_page_title'); ?> <span class="highlight"><?php echo t('about_page_title_highlight'); ?></span></h1>
            <h2 class="hero-subtitle"><?php echo t('about_page_subtitle'); ?></h2>
            
            <div class="hero-badge">
                <i class="fas fa-user-graduate"></i>
                <span><?php echo t('about_page_badge'); ?></span>
            </div>
        </div>
    </section>

    <!-- Introducción Personal -->
    <section class="about-intro">
        <div class="container">
            <div class="about-content">
                <div class="about-text">
                    <h2><?php echo t('about_vocation_title'); ?></h2>
                    <p><?php echo t('about_vocation_text'); ?></p>
                </div>
                <div class="about-image">
                    <img src="img/yanina-about.jpg" alt="Yanina Parisi" placeholder="Foto profesional de Yanina Parisi">
                </div>
            </div>
        </div>
    </section>

    <!-- Trayectoria Profesional -->
    <section class="professional-journey">
        <div class="container">
            <h2><?php echo t('about_journey_title'); ?></h2>
            
            <div class="credentials">
                <div class="credential-item">
                    <div class="credential-icon">
                        <i class="fas fa-user-md"></i>
                    </div>
                    <div class="credential-content">
                        <h3><?php echo t('about_credential1_title'); ?></h3>
                        <p><?php echo t('about_credential1_desc'); ?></p>
                    </div>
                </div>
                
                <div class="credential-item">
                    <div class="credential-icon">
                        <i class="fas fa-balance-scale"></i>
                    </div>
                    <div class="credential-content">
                        <h3><?php echo t('about_credential2_title'); ?></h3>
                        <p><?php echo t('about_credential2_desc'); ?></p>
                    </div>
                </div>
                
                <div class="credential-item">
                    <div class="credential-icon">
                        <i class="fas fa-handshake"></i>
                    </div>
                    <div class="credential-content">
                        <h3><?php echo t('about_credential3_title'); ?></h3>
                        <p><?php echo t('about_credential3_desc'); ?></p>
                    </div>
                </div>
                
                <div class="credential-item">
                    <div class="credential-icon">
                        <i class="fas fa-lightbulb"></i>
                    </div>
                    <div class="credential-content">
                        <h3><?php echo t('about_credential4_title'); ?></h3>
                        <p><?php echo t('about_credential4_desc'); ?></p>
                    </div>
                </div>
            </div>

            <div class="main-description">
                <p><?php echo t('about_journey_desc'); ?></p>
            </div>
        </div>
    </section>

    <!-- Pilares Fundamentales -->
    <section class="core-pillars">
        <div class="container">
            <h2><?php echo t('about_pillars_title'); ?></h2>
            
            <div class="pillars-grid">
                <div class="pillar-card">
                    <div class="pillar-icon">
                        <i class="fas fa-heart"></i>
                    </div>
                    <h3><?php echo t('about_pillar1_title'); ?></h3>
                    <p><?php echo t('about_pillar1_desc'); ?></p>
                </div>
                
                <div class="pillar-card">
                    <div class="pillar-icon">
                        <i class="fas fa-user"></i>
                    </div>
                    <h3><?php echo t('about_pillar2_title'); ?></h3>
                    <p><?php echo t('about_pillar2_desc'); ?></p>
                </div>
            </div>
        </div>
    </section>

    <!-- Servicio Único -->
    <section class="unique-service">
        <div class="container">
            <div class="service-content">
                <div class="service-text">
                    <h2><?php echo t('about_unique_title'); ?></h2>
                    <p><?php echo t('about_unique_desc'); ?></p>
                </div>
                <div class="service-icon">
                    <i class="fas fa-search-heart"></i>
                </div>
            </div>
        </div>
    </section>

    <!-- Ámbitos de Especialización -->
    <section class="specialization-areas">
        <div class="container">
            <h2><?php echo t('about_specialization_title'); ?></h2>
            
            <div class="specialization-content">
                <p><?php echo t('about_specialization_intro'); ?></p>
                
                <div class="specialization-grid">
                    <div class="spec-item">
                        <h4><?php echo t('about_spec1_title'); ?></h4>
                        <p><?php echo t('about_spec1_desc'); ?></p>
                    </div>
                    
                    <div class="spec-item">
                        <h4><?php echo t('about_spec2_title'); ?></h4>
                        <p><?php echo t('about_spec2_desc'); ?></p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Enfoque Terapéutico -->
    <section class="therapeutic-approach">
        <div class="container">
            <h2><?php echo t('about_approach_title'); ?></h2>
            
            <div class="approach-content">
                <div class="approach-text">
                    <p><?php echo t('about_approach_desc'); ?></p>
                </div>
                
                <div class="therapy-types">
                    <div class="therapy-type">
                        <span><?php echo t('about_therapy1'); ?></span>
                    </div>
                    <div class="therapy-type">
                        <span><?php echo t('about_therapy2'); ?></span>
                    </div>
                    <div class="therapy-type">
                        <span><?php echo t('about_therapy3'); ?></span>
                    </div>
                    <div class="therapy-type">
                        <span><?php echo t('about_therapy4'); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- El Corazón de mi Práctica -->
    <section class="practice-heart">
        <div class="container">
            <h2><?php echo t('about_heart_title'); ?></h2>
            
            <div class="heart-content">
                <div class="heart-text">
                    <p><?php echo t('about_heart_desc1'); ?></p>
                    
                    <div class="specialties">
                        <h3><?php echo t('about_specialties_title'); ?></h3>
                        <ul>
                            <li><?php echo t('about_specialty1'); ?></li>
                            <li><?php echo t('about_specialty2'); ?></li>
                            <li><?php echo t('about_specialty3'); ?></li>
                            <li><?php echo t('about_specialty4'); ?></li>
                            <li><?php echo t('about_specialty5'); ?></li>
                            <li><?php echo t('about_specialty6'); ?></li>
                        </ul>
                    </div>
                    
                    <p><?php echo t('about_heart_desc2'); ?></p>
                </div>
            </div>
        </div>
    </section>

    <!-- Más Allá de la Consulta -->
    <section class="beyond-consultation">
        <div class="container">
            <h2><?php echo t('about_beyond_title'); ?></h2>
            
            <div class="beyond-content">
                <p><?php echo t('about_beyond_desc'); ?></p>
                
                <div class="outreach-activities">
                    <div class="activity">
                        <i class="fas fa-newspaper"></i>
                        <span><?php echo t('about_activity1'); ?></span>
                    </div>
                    <div class="activity">
                        <i class="fas fa-share-alt"></i>
                        <span><?php echo t('about_activity2'); ?></span>
                    </div>
                    <div class="activity">
                        <i class="fas fa-users"></i>
                        <span><?php echo t('about_activity3'); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Call to Action -->
    <section class="cta-section">
        <div class="container">
            <div class="cta-content">
                <h2><?php echo t('about_cta_title'); ?></h2>
                <p><?php echo t('about_cta_desc'); ?></p>
                
                <div class="cta-buttons">
                    <a href="contacta.php" class="btn btn-primary">
                        <i class="fas fa-calendar-check"></i>
                        <?php echo t('about_cta_btn1'); ?>
                    </a>
                    <a href="index.php#serveis" class="btn btn-secondary">
                        <?php echo t('about_cta_btn2'); ?>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>

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
        function changeLanguage(lang) {
            console.log('Canviant idioma a:', lang);
            window.location.href = window.location.pathname + '?lang=' + lang;
        }
        
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

        // Animacions al scroll
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, observerOptions);

        // Observar elements amb animació
        document.querySelectorAll('.credential-item, .pillar-card, .spec-item, .therapy-type, .activity').forEach(el => {
            el.style.opacity = '0';
            el.style.transform = 'translateY(20px)';
            el.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
            observer.observe(el);
        });

        // Efecte parallax lleuger per al hero
        window.addEventListener('scroll', () => {
            const scrolled = window.pageYOffset;
            const rate = scrolled * -0.5;
            const hero = document.querySelector('.about-hero');
            if (hero) {
                hero.style.transform = `translateY(${rate}px)`;
            }
        });
    </script>
</body>
</html>
