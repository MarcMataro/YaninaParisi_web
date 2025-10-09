<?php 
// Inicialitzar sessió si no està iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Debug ABANS del processament
echo "<!-- DEBUG INDEX ABANS: GET lang: " . ($_GET['lang'] ?? 'no definit') . " -->";
echo "<!-- DEBUG INDEX ABANS: Session lang abans: " . ($_SESSION['language'] ?? 'no definit') . " -->";

// Processar canvi d'idioma PRIMER
if (isset($_GET['lang'])) {
    echo "<!-- DEBUG INDEX: Processant canvi d'idioma a " . $_GET['lang'] . " -->";
    if (in_array($_GET['lang'], array('ca', 'es'))) {
        $_SESSION['language'] = $_GET['lang'];
        echo "<!-- DEBUG INDEX: Idioma canviat a sessió: " . $_SESSION['language'] . " -->";
    }
    // Redirigir per evitar reenviar formulari
    $redirect_url = strtok($_SERVER["REQUEST_URI"], '?');
    echo "<!-- DEBUG INDEX: Redirigint a: " . $redirect_url . " -->";
    header('Location: ' . $redirect_url);
    exit;
}

// Incluir sistema de traducció
include 'includes/lang.php';

// Debug DESPRÉS
echo "<!-- DEBUG INDEX DESPRÉS: Session lang després: " . ($_SESSION['language'] ?? 'no definit') . " -->";
echo "<!-- DEBUG INDEX DESPRÉS: getCurrentLanguage(): " . getCurrentLanguage() . " -->";
?>
<!DOCTYPE html>
<html lang="<?php echo getCurrentLanguage(); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- METAETIQUETES ESSENCIALS -->
    <title><?php echo t('meta_title'); ?></title>
    <meta name="description" content="<?php echo t('meta_description'); ?>">
    <meta name="keywords" content="<?php echo t('meta_keywords'); ?>">
    <meta name="author" content="<?php echo t('meta_author'); ?>">
    
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?php echo 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>">
    <meta property="og:title" content="<?php echo t('meta_og_title'); ?>">
    <meta property="og:description" content="<?php echo t('meta_og_description'); ?>">
    <meta property="og:image" content="<?php echo 'http://' . $_SERVER['HTTP_HOST']; ?>/img/Logo.png">
    <meta property="og:site_name" content="<?php echo t('meta_og_site_name'); ?>">
    <meta property="og:locale" content="<?php echo getCurrentLanguage() === 'ca' ? 'ca_ES' : 'es_ES'; ?>">
    
    <!-- Twitter -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?php echo t('meta_og_title'); ?>">
    <meta name="twitter:description" content="<?php echo t('meta_og_description'); ?>">
    <meta name="twitter:image" content="<?php echo 'http://' . $_SERVER['HTTP_HOST']; ?>/img/Logo.png">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/estils.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Libre+Baskerville:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet">
</head>
<body>
    <?php include 'includes/navigation.php'; ?>

    <!-- Secció Hero -->
    <section class="hero" id="inici">
        <div class="container hero-content">
            <h1><?php echo t('hero_title'); ?> <span class="highlight"><?php echo t('hero_name'); ?></span></h1>
            <h2 class="hero-subtitle"><?php echo t('hero_subtitle'); ?></h2>
            
            <div class="hero-badge">
                <i class="fas fa-shield-alt"></i>
                <span><?php echo t('hero_badge'); ?></span>
            </div>

            <div class="hero-buttons">
                <a href="#contacte" class="btn btn-primary">
                    <i class="fas fa-calendar-check"></i>
                    <?php echo t('hero_btn_primary'); ?>
                </a>
                <a href="#serveis" class="btn btn-secondary">
                    <?php echo t('hero_btn_secondary'); ?>
                </a>
            </div>
        </div>
    </section>

    <!-- Frase inspiradora -->
    <section class="quote-section">
        <div class="container">
            <div class="quote-content">
                <blockquote>
                    <p>"<?php echo t('quote_text'); ?>"</p>
                </blockquote>
                <cite>— <?php echo t('quote_author'); ?></cite>
            </div>
        </div>
    </section>

    <!-- Especialitats -->
    <section id="serveis" class="specialties-section">
        <div class="container">
            <div class="section-title">
                <h2><?php echo t('services_title'); ?></h2>
                <p><?php echo t('services_subtitle'); ?></p>
            </div>
            <div class="specialties-grid">
                <!-- Salut Mental Adults -->
                <div class="specialty-card">
                    <div class="specialty-icon">
                        <i class="fas fa-brain"></i>
                    </div>
                    <h3><?php echo t('specialty1_title'); ?></h3>
                    <ul>
                        <li><?php echo t('specialty1_item1'); ?></li>
                        <li><?php echo t('specialty1_item2'); ?></li>
                        <li><?php echo t('specialty1_item3'); ?></li>
                        <li><?php echo t('specialty1_item4'); ?></li>
                        <li><?php echo t('specialty1_item5'); ?></li>
                        <li><?php echo t('specialty1_item6'); ?></li>
                    </ul>
                </div>
                
                <!-- Teràpia de Parella i Família -->
                <div class="specialty-card">
                    <div class="specialty-icon">
                        <i class="fas fa-heart"></i>
                    </div>
                    <h3><?php echo t('specialty2_title'); ?></h3>
                    <ul>
                        <li><?php echo t('specialty2_item1'); ?></li>
                        <li><?php echo t('specialty2_item2'); ?></li>
                        <li><?php echo t('specialty2_item3'); ?></li>
                        <li><?php echo t('specialty2_item4'); ?></li>
                        <li><?php echo t('specialty2_item5'); ?></li>
                    </ul>
                </div>
                
                <!-- Psicologia Judicial -->
                <div class="specialty-card">
                    <div class="specialty-icon">
                        <i class="fas fa-balance-scale"></i>
                    </div>
                    <h3><?php echo t('specialty3_title'); ?></h3>
                    <ul>
                        <li><?php echo t('specialty3_item1'); ?></li>
                        <li><?php echo t('specialty3_item2'); ?></li>
                        <li><?php echo t('specialty3_item3'); ?></li>
                        <li><?php echo t('specialty3_item4'); ?></li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- Serveis Especials -->
    <section class="special-services">
        <div class="container">
            <div class="section-title">
                <h2><?php echo t('special_services_title'); ?></h2>
                <p><?php echo t('special_services_subtitle'); ?></p>
            </div>
            
            <div class="services-special-grid">
                <div class="service-special-card">
                    <div class="service-special-header">
                        <i class="fas fa-heart-circle-check"></i>
                        <h3><?php echo t('special_service1_title'); ?></h3>
                    </div>
                    <p><?php echo t('special_service1_desc'); ?></p>
                    <ul>
                        <li><?php echo t('special_service1_item1'); ?></li>
                        <li><?php echo t('special_service1_item2'); ?></li>
                        <li><?php echo t('special_service1_item3'); ?></li>
                    </ul>
                </div>
                
                <div class="service-special-card">
                    <div class="service-special-header">
                        <i class="fas fa-scale-balanced"></i>
                        <h3><?php echo t('special_service2_title'); ?></h3>
                    </div>
                    <p><?php echo t('special_service2_desc'); ?></p>
                    <ul>
                        <li><?php echo t('special_service2_item1'); ?></li>
                        <li><?php echo t('special_service2_item2'); ?></li>
                        <li><?php echo t('special_service2_item3'); ?></li>
                        <li><?php echo t('special_service2_item4'); ?></li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- Sobre mi -->
    <section id="sobre-mi" class="about-section">
        <div class="container">
            <div class="about-content">
                <span class="about-label"><?php echo getCurrentLanguage() === 'ca' ? 'Psicòloga Col·legiada' : 'Psicóloga Colegiada'; ?></span>
                <h2><?php echo t('about_name'); ?></h2>
                <div class="about-description">
                    <p><?php echo t('about_desc1'); ?></p>
                    <p><?php echo t('about_desc2'); ?></p>
                </div>
                <div class="about-location">
                    <?php echo getCurrentLanguage() === 'ca' ? 'Girona · Sessions presencials i online' : 'Girona · Sesiones presenciales y online'; ?>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonis -->
    <section id="testimonis">
        <div class="container">
            <div class="section-title">
                <h2>Testimonis</h2>
                <p>El que diuen els meus pacients</p>
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
                <h2><?php echo t('pricing_title'); ?></h2>
                <p><?php echo t('pricing_subtitle'); ?></p>
            </div>
            <div class="pricing-grid">
                <div class="pricing-card">
                    <div class="pricing-header">
                        <i class="fas fa-gift pricing-icon"></i>
                        <h3><?php echo t('pricing_first_title'); ?></h3>
                    </div>
                    <div class="pricing-features">
                        <div class="price">
                            <span class="original-price">60</span>
                            <span class="amount">0</span>
                            <span class="currency">€</span>
                            <span class="period"><?php echo t('pricing_session'); ?></span>
                        </div>
                        <p class="price-note"><?php echo t('pricing_first_note'); ?></p>
                        <ul>
                            <li><i class="fas fa-check"></i> <?php echo t('pricing_first_feature2'); ?></li>
                            <li><i class="fas fa-check"></i> <?php echo t('pricing_first_feature3'); ?></li>
                            <li><i class="fas fa-check"></i> <?php echo t('pricing_first_feature4'); ?></li>
                        </ul>
                    </div>
                    <a href="#contacte" class="btn pricing-btn"><?php echo t('pricing_btn'); ?></a>
                </div>

                <div class="pricing-card">
                    <div class="pricing-header">
                        <i class="fas fa-heart pricing-icon"></i>
                        <h3><?php echo t('pricing_individual_title'); ?></h3>
                    </div>
                    <div class="pricing-features">
                        <div class="price">
                            <span class="amount">60</span>
                            <span class="currency">€</span>
                            <span class="period"><?php echo t('pricing_session'); ?></span>
                        </div>
                        <ul>
                            <li><i class="fas fa-check"></i> <?php echo t('pricing_individual_feature1'); ?></li>
                            <li><i class="fas fa-check"></i> <?php echo t('pricing_individual_feature2'); ?></li>
                            <li><i class="fas fa-check"></i> <?php echo t('pricing_individual_feature3'); ?></li>
                            <li><i class="fas fa-check"></i> <?php echo t('pricing_individual_feature4'); ?></li>
                        </ul>
                    </div>
                    <a href="#contacte" class="btn pricing-btn"><?php echo t('pricing_btn'); ?></a>
                </div>
                <div class="pricing-card">
                    <div class="pricing-header">
                        <i class="fas fa-calendar-week pricing-icon"></i>
                        <h3><?php echo t('pricing_biweekly_title'); ?></h3>
                    </div>
                    <div class="pricing-features">
                        <div class="price">
                            <span class="amount">100</span>
                            <span class="currency">€</span>
                            <span class="period"><?php echo t('pricing_month'); ?></span>
                        </div>
                        <ul>
                            <li><i class="fas fa-check"></i> <?php echo t('pricing_biweekly_feature1'); ?></li>
                            <li><i class="fas fa-check"></i> <?php echo t('pricing_biweekly_feature2'); ?></li>
                            <li><i class="fas fa-check"></i> <?php echo t('pricing_biweekly_feature3'); ?></li>
                            <li><i class="fas fa-check"></i> <?php echo t('pricing_biweekly_feature4'); ?></li>
                        </ul>
                    </div>
                    <a href="#contacte" class="btn pricing-btn"><?php echo t('pricing_btn'); ?></a>
                </div>

                <div class="pricing-card">
                    <div class="pricing-header">
                        <i class="fas fa-calendar-alt pricing-icon"></i>
                        <h3><?php echo t('pricing_monthly_title'); ?></h3>
                    </div>
                    <div class="pricing-features">
                        <div class="price">
                            <span class="amount">180</span>
                            <span class="currency">€</span>
                            <span class="period"><?php echo t('pricing_month'); ?></span>
                        </div>
                        <ul>
                            <li><i class="fas fa-check"></i> <?php echo t('pricing_monthly_feature1'); ?></li>
                            <li><i class="fas fa-check"></i> <?php echo t('pricing_monthly_feature2'); ?></li>
                            <li><i class="fas fa-check"></i> <?php echo t('pricing_monthly_feature3'); ?></li>
                            <li><i class="fas fa-check"></i> <?php echo t('pricing_monthly_feature4'); ?></li>
                        </ul>
                    </div>
                    <a href="#contacte" class="btn pricing-btn"><?php echo t('pricing_btn'); ?></a>
                </div>
            </div>
        </div>
    </section>

    <!-- Contacte -->
    <section id="contacte">
        <div class="container">
            <div class="section-title">
                <h2>Contacte</h2>
                <p>No dubtis a posar-te en contacte amb mi</p>
            </div>
            <div class="contact-grid">
                <div class="contact-info">
                    <div class="contact-item">
                        <i class="fas fa-map-marker-alt contact-icon"></i>
                        <div>
                            <h4>Adreça</h4>
                            <p>Carrer de la Pau, 23, Girona</p>
                        </div>
                    </div>
                    <div class="contact-item">
                        <i class="fas fa-phone contact-icon"></i>
                        <div>
                            <h4>Telèfon</h4>
                            <p>+34 972 123 45 67</p>
                        </div>
                    </div>
                    <div class="contact-item">
                        <i class="fas fa-envelope contact-icon"></i>
                        <div>
                            <h4>Email</h4>
                            <p>yanina@psicologiayanina.com</p>
                        </div>
                    </div>
                    <div class="contact-item">
                        <i class="fas fa-clock contact-icon"></i>
                        <div>
                            <h4>Horari</h4>
                            <p>Dilluns a Divendres: 9:00 - 20:00</p>
                            <p>Dissabte: 10:00 - 14:00</p>
                        </div>
                    </div>
                </div>
                <form>
                    <div class="form-group">
                        <label for="name">Nom</label>
                        <input type="text" id="name" placeholder="El teu nom">
                    </div>
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" placeholder="El teu email">
                    </div>
                    <div class="form-group">
                        <label for="phone">Telèfon</label>
                        <input type="tel" id="phone" placeholder="El teu telèfon">
                    </div>
                    <div class="form-group">
                        <label for="message">Missatge</label>
                        <textarea id="message" placeholder="Com et puc ajudar?"></textarea>
                    </div>
                    <button type="submit" class="btn">Enviar missatge</button>
                </form>
            </div>
        </div>
    </section>

    <!-- Peu de pàgina -->
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
    </script>
</body>
</html>