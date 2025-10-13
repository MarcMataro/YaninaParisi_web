<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (isset($_GET['lang'])) {
    if (in_array($_GET['lang'], array('ca', 'es'))) {
        $_SESSION['language'] = $_GET['lang'];
    }
    $redirect_url = strtok($_SERVER["REQUEST_URI"], '?');
    header('Location: ' . $redirect_url);
    exit;
}
include 'includes/lang.php';
?>
<!DOCTYPE html>
<html lang="<?php echo getCurrentLanguage(); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo t('nav_services'); ?> | Yanina Parisi</title>
    <meta name="description" content="<?php echo t('meta_description'); ?>">
    <link rel="stylesheet" href="css/estils.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Libre+Baskerville:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/clinica.css">
            <link rel="stylesheet" href="css/parallax-clinica.css">
            <style>
            /* Hero sticky mobile-first */
                .clinica-hero {
                    position: sticky;
                    top: 0;
                    z-index: 10;
                    background: #fff;
                    box-shadow: 0 2px 16px rgba(168,153,104,0.07);
                    border-bottom: 1px solid #e7e2d2;
                    transition: box-shadow 0.3s;
                }
                main {
                    position: relative;
                    z-index: 20;
                }
            @media (max-width: 600px) {
                .clinica-hero { padding: 18px 0 12px 0; }
            }
            @media (min-width: 601px) {
                .clinica-hero { padding: 32px 0 24px 0; }
            }
            </style>
        <style>
        /* Mobile-first parallax i missatges */
        .parallax-section {
            min-height: 220px;
            padding: 0;
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(120deg, #f7f5ee 60%, #e7e2d2 100%);
        }
        .parallax-bg {
            position: absolute;
            top: 0; left: 0; width: 100%; height: 100%;
            background-image: url('../img/Portada.jpg');
            background-size: cover;
            background-position: center;
            opacity: 0.15;
            z-index: 1;
            will-change: transform;
            transition: opacity 0.5s;
        }
        .parallax-message {
            position: relative;
            z-index: 2;
            font-family: 'Libre Baskerville', serif;
            font-size: 1.25em;
            color: #a89968;
            text-align: center;
            max-width: 95vw;
            margin: 0 auto;
            padding: 24px 12px;
            background: rgba(255,255,255,0.92);
            border-radius: 14px;
            box-shadow: 0 4px 16px rgba(168,153,104,0.07);
            animation: fadeInUp 1.2s;
        }
        @media (min-width: 600px) {
            .parallax-message { font-size: 1.7em; padding: 32px 24px; border-radius: 18px; }
            .parallax-section { min-height: 320px; }
        }
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(40px); }
            to { opacity: 1; transform: translateY(0); }
        }
        </style>
</head>
<body>
    <?php include 'includes/navigation.php'; ?>
    <!-- HERO potent -->
    <section class="hero clinica-hero" id="clinica-hero">
        <div class="container hero-content">
            <span class="clinica-hero-badge"><?php echo t('hero_badge'); ?></span>
            <h1 class="clinica-hero-title">
                <?php echo t('nav_services'); ?> <span class="highlight">Yanina Parisi</span>
            </h1>
            <h2 class="clinica-hero-subtitle"><?php echo t('hero_subtitle'); ?></h2>
            <div class="hero-buttons" style="margin:18px 0 0 0;display:flex;flex-wrap:wrap;gap:16px;justify-content:center;">
                <a href="contacta.php" class="clinica-cta-btn">
                    <i class="fas fa-calendar-check"></i> <?php echo t('hero_btn_primary'); ?>
                </a>
                <a href="#serveis" class="clinica-cta-btn" style="background:#fff;color:#a89968;border:2px solid #a89968;">
                    <?php echo t('hero_btn_secondary'); ?>
                </a>
            </div>
            <p style="max-width:600px;margin:24px auto 0 auto;color:#555;font-size:1.1em;">
                <?php echo t('hero_description'); ?>
            </p>
        </div>
    </section>
    <main>
    <!-- Cita -->
     <!-- Frase inspiradora -->
    <section class="quote-section">
        <div class="container">
            <div class="quote-content">
                <blockquote>
                    <p class="clinica-quote-text">"<?php echo t('hero_quote'); ?>"</p>
                </blockquote>
                <p class="clinica-quote-author">- Yanina Parisi</p>
            </div>
        </div>
    </section>

    <!-- Parallax scroll amb quotes inspiradores -->
    <section class="parallax-section quote-section" id="parallax-1">
        <div class="parallax-bg"></div>
        <div class="container">
            <div class="quote-content">
                <blockquote>
                    <p>"Sents que s'ha apoderat de tu una tristesa que no vol marxar?"</p>
                </blockquote>
                <cite>— Psicologia empàtica</cite>
            </div>
        </div>
    </section>
    <section class="parallax-section quote-section" id="parallax-2">
        <div class="parallax-bg"></div>
        <div class="container">
            <div class="quote-content">
                <blockquote>
                    <p>"No estàs sol. La teva emoció té sentit i mereix ser escoltada."</p>
                </blockquote>
                <cite>— Acompanyament real</cite>
            </div>
        </div>
    </section>
    <section class="parallax-section quote-section" id="parallax-3">
        <div class="parallax-bg"></div>
        <div class="container">
            <div class="quote-content">
                <blockquote>
                    <p>"A vegades, demanar ajuda és el gest més valent que pots fer per tu mateix."</p>
                </blockquote>
                <cite>— Valor i canvi</cite>
            </div>
        </div>
    </section>
    <section class="parallax-section quote-section" id="parallax-4">
        <div class="parallax-bg"></div>
        <div class="container">
            <div class="quote-content">
                <blockquote>
                    <p>"La Yanina és aquí per acompanyar-te, escoltar-te i ajudar-te a recuperar la llum."</p>
                </blockquote>
                <cite>— Compromís amb tu</cite>
            </div>
        </div>
    </section>
    <section class="parallax-section quote-section" id="parallax-5">
        <div class="parallax-bg"></div>
        <div class="container">
            <div class="quote-content">
                <blockquote>
                    <p>"Potser fa temps que busques respostes. Aquí pots trobar-les, amb respecte i empatia."</p>
                </blockquote>
                <cite>— Respostes reals</cite>
            </div>
        </div>
    </section>
    <section class="parallax-section quote-section" id="parallax-6">
        <div class="parallax-bg"></div>
        <div class="container">
            <div class="quote-content">
                <blockquote>
                    <p>"La teva història importa. Mereixes sentir-te millor i començar de nou."</p>
                </blockquote>
                <cite>— Nou camí</cite>
            </div>
        </div>
    </section>
    <section class="parallax-section quote-section" id="parallax-7">
        <div class="parallax-bg"></div>
        <div class="container">
            <div class="quote-content">
                <blockquote>
                    <p>"Contacta amb mi i descobreix com la psicologia pot transformar la teva vida."</p>
                </blockquote>
                <cite>— Yanina Parisi</cite>
            </div>
        </div>
    </section>
    <script>
    // Parallax efecte scroll
    window.addEventListener('scroll', function() {
        document.querySelectorAll('.parallax-bg').forEach(function(bg, i) {
            const section = bg.parentElement;
            const rect = section.getBoundingClientRect();
            const offset = window.scrollY + rect.top;
            const speed = 0.18 + i * 0.05;
            bg.style.transform = `translateY(${(window.scrollY - offset) * speed}px)`;
        });
    });
    </script>
    <script>
    // Efecte scroll per al header
    document.addEventListener('scroll', function() {
        const header = document.querySelector('header');
        if (window.scrollY > 50) {
            header.classList.add('scrolled');
        } else {
            header.classList.remove('scrolled');
        }
    });
    // Script per al selector d'idioma
    function changeLanguage(lang) {
        window.location.href = window.location.pathname + '?lang=' + lang;
    }
    document.addEventListener('DOMContentLoaded', function() {
        // Navegació mòbil
        const mobileMenuToggle = document.querySelector('.mobile-menu-toggle');
        const navMenu = document.querySelector('.nav-menu ul');
        if (mobileMenuToggle && navMenu) {
            mobileMenuToggle.addEventListener('click', function() {
                this.classList.toggle('active');
                navMenu.classList.toggle('show');
            });
            document.querySelectorAll('.nav-menu ul li a').forEach(link => {
                link.addEventListener('click', function() {
                    mobileMenuToggle.classList.remove('active');
                    navMenu.classList.remove('show');
                });
            });
            document.addEventListener('click', function(e) {
                if (!mobileMenuToggle.contains(e.target) && !navMenu.contains(e.target)) {
                    mobileMenuToggle.classList.remove('active');
                    navMenu.classList.remove('show');
                }
            });
        }
        // Selector d'idioma
        document.querySelectorAll('.lang-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const lang = this.getAttribute('data-lang');
                document.querySelectorAll('.lang-btn').forEach(b => b.classList.remove('active'));
                document.querySelectorAll(`.lang-btn[data-lang="${lang}"]`).forEach(b => b.classList.add('active'));
                changeLanguage(lang);
            });
        });
    });
    </script>

        <!-- Seccions inspiradores per a cada servei -->
        <section class="clinica-section clinica-inspire">
            <h2 class="clinica-inspire-title"><?php echo t('specialty1_title'); ?></h2>
            <p class="clinica-inspire-desc"><?php echo t('service_individual_inspire'); ?></p>
            <a href="#contacte" class="clinica-cta"><?php echo t('service_individual_cta'); ?></a>
        </section>
        <div class="section-separator"></div>
        <section class="clinica-section clinica-inspire">
            <h2 class="clinica-inspire-title"><?php echo t('specialty2_title'); ?></h2>
            <p class="clinica-inspire-desc"><?php echo t('service_couple_inspire'); ?></p>
            <a href="#contacte" class="clinica-cta"><?php echo t('service_couple_cta'); ?></a>
        </section>
        <div class="section-separator"></div>
        <section class="clinica-section clinica-inspire">
            <h2 class="clinica-inspire-title"><?php echo t('specialty3_title'); ?></h2>
            <p class="clinica-inspire-desc"><?php echo t('service_assessment_inspire'); ?></p>
            <a href="#contacte" class="clinica-cta"><?php echo t('service_assessment_cta'); ?></a>
        </section>
        </section>
        <section class="clinica-section">
            <h2 style="color:#a89968;font-size:1.3em;margin-bottom:14px;text-align:center;">
                <?php echo t('special_services_title'); ?>
            </h2>
            <p style="text-align:center;color:#444;margin-bottom:18px;">
                <?php echo t('special_services_subtitle'); ?>
            </p>
            <div class="clinica-features">
                <div class="clinica-feature">
                    <div class="clinica-feature-title"><?php echo t('special_service1_title'); ?></div>
                    <p style="margin-bottom:8px;color:#555;"><?php echo t('special_service1_desc'); ?></p>
                    <ul class="clinica-feature-list">
                        <li><?php echo t('special_service1_item1'); ?></li>
                        <li><?php echo t('special_service1_item2'); ?></li>
                        <li><?php echo t('special_service1_item3'); ?></li>
                    </ul>
                </div>
                <div class="clinica-feature">
                    <div class="clinica-feature-title"><?php echo t('special_service2_title'); ?></div>
                    <p style="margin-bottom:8px;color:#555;"><?php echo t('special_service2_desc'); ?></p>
                    <ul class="clinica-feature-list">
                        <li><?php echo t('special_service2_item1'); ?></li>
                        <li><?php echo t('special_service2_item2'); ?></li>
                        <li><?php echo t('special_service2_item3'); ?></li>
                        <li><?php echo t('special_service2_item4'); ?></li>
                    </ul>
                </div>
            </div>
        </section>
        <section class="clinica-section">
            <h2 style="color:#a89968;font-size:1.3em;margin-bottom:14px;text-align:center;">
                <?php echo t('pricing_title'); ?>
            </h2>
            <p style="text-align:center;color:#444;margin-bottom:18px;">
                <?php echo t('pricing_subtitle'); ?>
            </p>
            <div class="clinica-features">
                <div class="clinica-feature">
                    <div class="clinica-feature-title"><?php echo t('pricing_first_title'); ?></div>
                    <ul class="clinica-feature-list">
                        <li><strong><?php echo t('pricing_free'); ?></strong> - <?php echo t('pricing_first_note'); ?></li>
                        <li><?php echo t('pricing_first_feature1'); ?></li>
                        <li><?php echo t('pricing_first_feature2'); ?></li>
                        <li><?php echo t('pricing_first_feature3'); ?></li>
                        <li><?php echo t('pricing_first_feature4'); ?></li>
                    </ul>
                </div>
                <div class="clinica-feature">
                    <div class="clinica-feature-title"><?php echo t('pricing_individual_title'); ?></div>
                    <ul class="clinica-feature-list">
                        <li><?php echo t('pricing_individual_feature1'); ?></li>
                        <li><?php echo t('pricing_individual_feature2'); ?></li>
                        <li><?php echo t('pricing_individual_feature3'); ?></li>
                        <li><?php echo t('pricing_individual_feature4'); ?></li>
                    </ul>
                </div>
                <div class="clinica-feature">
                    <div class="clinica-feature-title"><?php echo t('pricing_couple_title'); ?></div>
                    <ul class="clinica-feature-list">
                        <li><?php echo t('pricing_couple_feature1'); ?></li>
                        <li><?php echo t('pricing_couple_feature2'); ?></li>
                        <li><?php echo t('pricing_couple_feature3'); ?></li>
                        <li><?php echo t('pricing_couple_feature4'); ?></li>
                    </ul>
                </div>
                <div class="clinica-feature">
                    <div class="clinica-feature-title"><?php echo t('pricing_biweekly_title'); ?></div>
                    <ul class="clinica-feature-list">
                        <li><?php echo t('pricing_biweekly_feature1'); ?></li>
                        <li><?php echo t('pricing_biweekly_feature2'); ?></li>
                        <li><?php echo t('pricing_biweekly_feature3'); ?></li>
                        <li><?php echo t('pricing_biweekly_feature4'); ?></li>
                    </ul>
                </div>
                <div class="clinica-feature">
                    <div class="clinica-feature-title"><?php echo t('pricing_monthly_title'); ?></div>
                    <ul class="clinica-feature-list">
                        <li><?php echo t('pricing_monthly_feature1'); ?></li>
                        <li><?php echo t('pricing_monthly_feature2'); ?></li>
                        <li><?php echo t('pricing_monthly_feature3'); ?></li>
                        <li><?php echo t('pricing_monthly_feature4'); ?></li>
                    </ul>
                </div>
            </div>
            <div style="text-align:center;margin-top:24px;">
                <a href="contacta.php" class="clinica-cta-btn"><?php echo t('pricing_btn'); ?></a>
            </div>
        </section>
    </main>
    <?php include 'includes/footer.php'; ?>
</body>
</html>
