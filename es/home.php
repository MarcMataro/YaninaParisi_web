<?php
// Inicializar sesión si no está iniciada

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Forzar idioma español en esta página
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
include '../includes/functions.php';
?>
<!DOCTYPE html>
<html lang="<?php echo getCurrentLanguage(); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php
    $lang = getCurrentLanguage();
    $base_url = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'];
    require_once __DIR__ . '/../classes/seo_onpage.php';

    // Determine SEO page for 'home'
    $seoTitle = null;
    $homePages = SEO_OnPage::llistarPaginesActives('home');
    if (!empty($homePages) && isset($homePages[0]) && $homePages[0] instanceof SEO_OnPage) {
        $pagina_seo = $homePages[0];
        $seoTitle = $pagina_seo->getTitle($lang) ?: null;
    }
    if (!$seoTitle) {
        $pagina_seo = SEO_OnPage::carregarPerUrl($lang === 'es' ? '/' : '/', $lang);
        if ($pagina_seo) {
            $seoTitle = $pagina_seo->getTitle($lang) ?: null;
        }
    }
    if (!$seoTitle) {
        $seoTitle = ($lang === 'es') ? 'Yanina Parisi - Psicóloga' : 'Yanina Parisi - Psicòloga';
    }

    // Description
    $seoDescription = null;
    if (isset($pagina_seo) && $pagina_seo instanceof SEO_OnPage) {
        $seoDescription = $pagina_seo->getMetaDescription($lang) ?: null;
    }
    if (!$seoDescription) {
        $seoDescription = t('meta_description');
    }

    // Canonical
    $canonical = null;
    if (isset($pagina_seo) && $pagina_seo instanceof SEO_OnPage) {
        $canonical = $pagina_seo->getCanonicalUrl($lang);
    }
    if (!$canonical) {
        $canonical = $base_url . '/es/home.php';
    }
    ?>
    <title><?php echo htmlspecialchars($seoTitle); ?></title>
    <meta name="description" content="<?php echo htmlspecialchars($seoDescription); ?>">
    <meta name="keywords" content="<?php echo t('meta_keywords'); ?>">
    <meta name="author" content="<?php echo t('meta_author'); ?>">
    <meta name="robots" content="index, follow">
    <meta name="theme-color" content="#aa9e6b">
    <link rel="canonical" href="<?php echo htmlspecialchars($canonical); ?>">
    <link rel="icon" type="image/png" sizes="32x32" href="../img/Logo32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="../img/Logo16.png">
    <link rel="apple-touch-icon" sizes="180x180" href="../img/apple-touch-icon.png">

    <!-- Open Graph / Facebook -->
    <?php
    $og_title = isset($pagina_seo) && $pagina_seo instanceof SEO_OnPage ? $pagina_seo->getOgTitle($lang) : $seoTitle;
    $og_description = isset($pagina_seo) && $pagina_seo instanceof SEO_OnPage ? $pagina_seo->getOgDescription($lang) : $seoDescription;
    $og_image = isset($pagina_seo) && $pagina_seo instanceof SEO_OnPage ? $pagina_seo->getOgImage() : null;
    if (!$og_image) { $og_image = '/img/Logo.png'; }
    if (!preg_match('#^https?://#i', $og_image)) { $og_image = $base_url . '/' . ltrim($og_image, '/'); }
    $og_url = htmlspecialchars($canonical ?: ($base_url . $_SERVER['REQUEST_URI']));
    ?>
    <meta property="og:type" content="<?php echo (isset($pagina_seo) ? $pagina_seo->getTipoPagina() === 'articulo' ? 'article' : 'website' : 'website'); ?>">
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
    $tw_title = isset($pagina_seo) && $pagina_seo instanceof SEO_OnPage ? $pagina_seo->getTwitterTitle($lang) : $seoTitle;
    $tw_description = isset($pagina_seo) && $pagina_seo instanceof SEO_OnPage ? $pagina_seo->getTwitterDescription($lang) : $seoDescription;
    $tw_image = isset($pagina_seo) && $pagina_seo instanceof SEO_OnPage ? $pagina_seo->getTwitterImage() : null;
    if (!$tw_image) { $tw_image = '/img/Logo.png'; }
    if (!preg_match('#^https?://#i', $tw_image)) { $tw_image = $base_url . '/' . ltrim($tw_image, '/'); }
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
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "Psychologist",
        "name": "Yanina Parisi",
        "description": "<?php echo htmlspecialchars($seoDescription); ?>",
            "url": "<?php echo $base_url; ?>",
        "telephone": "+34-XXX-XXX-XXX",
        "email": "info@yaninaparisi.com",
    "image": "<?php echo $base_url; ?>/img/img_2282.jpeg",
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
    
    <!-- Sección Hero -->
    <section class="hero" id="inici">
        <?php /* hero image as an <img> element so path resolves correctly across subfolders */ ?>
        <img src="<?php echo htmlspecialchars(resolve_media_url('img/Portada.jpg')); ?>" alt="Portada" class="hero-img">
        <div class="container hero-content">
            <h1><?php echo htmlspecialchars(isset($pagina_seo) && $pagina_seo instanceof SEO_OnPage ? $pagina_seo->getH1($lang) : 'Yanina Parisi - Psicóloga'); ?></h1>
            <p class="hero-subtitle">Construye la vida que deseas. Tu cambio empieza aquí</p>

            <div class="hero-buttons">
                <a href="contacto.php" class="btn btn-primary">
                    <i class="fas fa-calendar-check"></i>
                    ¡Primera consulta gratuita!
                </a>
            </div>
        </div>
    </section>

    <!-- Frase inspiradora -->
    <section class="quote-section">
        <div class="container">
            <div class="quote-content">
                <blockquote>
                    <p>"El primer paso hacia el cambio es la aceptación. El segundo es la acción. Y yo te acompañaré en ambos."</p>
                </blockquote>
            </div>
        </div>
    </section>

    <!-- Especialidades -->
    <section id="serveis" class="specialties-section">
        <div class="container">
            <div class="section-title">
                <h2>Especialidades y áreas de intervención</h2>
            </div>
            <div class="specialties-grid">
                <!-- Salut Mental Adults -->
                <div class="specialty-card">
                    <div class="specialty-icon">
                        <i class="fas fa-brain"></i>
                    </div>
                    <h3>Bienestar en adultos</h3>
                    <ul>
                        <li>Ansiedad y ataques de pánico</li>
                        <li>Depresión y tristeza persistente</li>
                        <li>Trastorno obsesivo compulsivo (TOC)</li>
                        <li>Crisis vitales y cambios personales</li>
                        <li>Problemas de autoestima</li>
                        <li>Gestión del duelo</li>
                    </ul>
                </div>
                
                <!-- Teràpia de Parella i Família -->
                <div class="specialty-card">
                    <div class="specialty-icon">
                        <i class="fas fa-heart"></i>
                    </div>
                    <h3>Dinámica de pareja y familia</h3>
                    <ul>
                        <li>Mediación y resolución de conflictos</li>
                        <li>Mejora de la comunicación</li>
                        <li>Fortalecimiento de los vínculos familiares</li>
                        <li>Terapia de pareja</li>
                        <li>Acompañamiento en procesos de separación</li>
                    </ul>
                </div>

                <!-- Coaching personalitzat -->
                <div class="specialty-card">
                    <div class="specialty-icon">
                        <i class="fas fa-user-check"></i>
                    </div>
                    <h3>Coaching personalizado</h3>
                    <ul>
                        <li>Hábitos y productividad</li>
                        <li>Gestión de la autoexigencia</li>
                        <li>Crecimiento personal</li>
                        <li>Transiciones de la vida</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- Servicios Especiales -->
    <section id="special-services" class="special-services">
        <div class="container">
            <div class="section-title">
                <h2>Servicios especializados</h2>
            </div>
            <div class="services-special-grid">
                <div class="service-special-card">
                    <div class="service-special-header">
                        <i class="fas fa-heart-circle-check"></i>
                        <h3>Acompañamiento en la búsqueda de pareja</h3>
                    </div>
                    <p>Servicio psicológico para personas que buscan una relación significativa. Basado en <strong>criterios de compatibilidad psicológica</strong> para vínculos estables y de calidad.</p>
                    <ul>
                        <li>Análisis psicológico del perfil emocional y relacional</li>
                        <li>Identificación de valores compatibles</li>
                        <li>Acompañamiento en el proceso de búsqueda</li>
                    </ul>
                </div>
                
                <div class="service-special-card">
                    <div class="service-special-header">
                        <i class="fas fa-scale-balanced"></i>
                        <h3>Psicología pericial judicial</h3>
                    </div>
                        <p><strong>Psicóloga pericial</strong> con formación específica en el ámbito jurídico. Elaboración de informes periciales psicológicos para procesos legales.</p>
                    <ul>
                        <li>Informes para casos de familia</li>
                        <li>Asesoría en custodias</li>
                        <li>Violencia filioparental</li>
                        <li>Procedimientos legales diversos</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- Sobre mí -->
    <section id="sobre-mi" class="about-section">
        <div class="container">
            <div class="about-content">
                <div class="about-image">
                    <img src="../img/media/1766556992_yanina.jpg"
                         alt="Yanina Parisi - Psicòloga General Sanitària Col·legiada a Girona"
                         width="300" 
                         height="350"
                         loading="lazy">
                </div>
                
                <div class="about-text">
                    <h2 class="about-title">
                        Psicología práctica para tu bienestar
                    </h2>
                    
                    <div class="about-intro">
                        <p>Soy Yanina Parisi, psicóloga general sanitaria colegiada con más de cinco años de experiencia. Mi objectivo es ofrecerte un espacio seguro donde puedas superar el malestar y construir la vida que quieres, ya sea recuperando tu equilibrio emocional o encontrando una pareja realmente compatible.</p>
                    </div>
                    
                    <div class="about-services">
                        <h3>¿Cómo trabajo?</h3>
                        
                        <div class="service-item">
                            <h4>Terapia individual:</h4>
                            <p>Especializada en ansiedad, depresión, TOC y crisis vitales. Juntos encontraremos las herramientas para que recuperes el control.</p>
                        </div>
                        
                        <div class="service-item">
                            <h4>Terapia de pareja:</h4>
                            <p>Gestión de conflictos y mejora de la comunicación para fortalecer vuestro vínculo.</p>
                        </div>
                        
                        <div class="service-item">
                            <h4>Búsqueda de pareja consciente:</h4>
                            <p>Un servicio único basado en criterios psicológicos para quien busca relaciones estables y de calidad, lejos del desgaste de las apps convencionales.</p>
                        </div>
                    </div>
                    
                    <div class="about-location">
                        <p>Atención personalizada online a toda España y presencial en Girona.</p>
                    </div>
                    
                    <div class="about-actions">
                        <a href="contacta.php" class="btn btn-primary">
                            ¡Reserva tu primera consulta gratuita!
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Segunda cita inspiradora -->
    <section class="quote-section">
        <div class="container">
            <div class="quote-content">
                <blockquote>
                    <p>"Siempre tienes la última libertad: elegir tu actitud. Elegir pedir consejo y acompañamiento es el primer paso para transformar tu destino."</p>
                </blockquote>
            </div>
        </div>
    </section>

    <!-- Testimonios -->
    <section id="testimonios">
        <div class="container">
            <div class="section-title">
                <h2>Testimonios</h2>
                <p>Lo que dicen mis pacientes</p>
            </div>
            <div class="testimonials-grid">
                <div class="testimonial-card">
                    <p class="testimonial-text">"Yanina me ha ayudado a superar mi ansiedad como nunca imaginé. Sus técnicas y su apoyo me dieron las herramientas necesarias para afrontar mis miedos."</p>
                    <div class="testimonial-author">
                        <div class="author-image"><i class="fas fa-user"></i></div>
                        <div>
                            <h4>Laura G.</h4>
                            <p>Paciente desde 2021</p>
                        </div>
                    </div>
                </div>
                <div class="testimonial-card">
                    <p class="testimonial-text">"Tras algo más de seis meses de terapia de pareja, nuestra relación ha mejorado drásticamente. Gracias a Yanina por enseñarnos a comunicarnos mejor."</p>
                    <div class="testimonial-author">
                        <div class="author-image"><i class="fas fa-users"></i></div>
                        <div>
                            <h4>Marc y Elena</h4>
                            <p>Pacientes desde 2022</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Tarifas -->
    <section id="tarifes">
        <div class="container">
            <div class="section-title">
                <h2>Estas son mis tarifas</h2>
            </div>
            <div class="pricing-grid">
                <div class="pricing-card">
                    <div class="pricing-header">
                        <i class="fas fa-gift pricing-icon"></i>
                        <h3>Primera sesión</h3>
                    </div>
                    <div class="pricing-features">
                        <div class="price">
                            <span class="original-price">60</span>
                            <span class="amount">0</span>
                            <span class="currency">€</span>
                            <span class="period">/sesión</span>
                        </div>
                        <ul>
                            <li><i class="fas fa-check"></i> Primera sesión de valoración gratuita</li>
                            <li><i class="fas fa-check"></i> Cuéntame lo que te preocupa</li>
                            <li><i class="fas fa-check"></i> Valoramos juntos tus objetivos</li>
                            <li><i class="fas fa-check"></i> Conoce mi método</li>
                        </ul>
                    </div>
                    <a href="contacta.php" class="btn pricing-btn">¡Quiero empezar ahora!</a>
                </div>

                <div class="pricing-card">
                    <div class="pricing-header">
                        <i class="fas fa-heart pricing-icon"></i>
                        <h3>Sesión individual</h3>
                    </div>
                    <div class="pricing-features">
                        <div class="price">
                            <span class="amount">60</span>
                            <span class="currency">€</span>
                            <span class="period">/sesión</span>
                        </div>
                        <ul>
                            <li><i class="fas fa-check"></i> Sesión de 60 minutos</li>
                            <li><i class="fas fa-check"></i> Atención totalmente personalizada</li>
                            <li><i class="fas fa-check"></i> Flexibilidad horaria</li>
                            <li><i class="fas fa-check"></i> Seguimiento continuo</li>
                        </ul>
                    </div>
                    <a href="contacta.php" class="btn pricing-btn">Programa una sesión</a>
                </div>
                <div class="pricing-card">
                    <div class="pricing-header">
                        <i class="fas fa-calendar-week pricing-icon"></i>
                        <h3>Pack quincenal</h3>
                    </div>
                    <div class="pricing-features">
                        <div class="price">
                            <span class="amount">100</span>
                            <span class="currency">€</span>
                            <span class="period">/sesión</span>
                        </div>
                        <ul>
                            <li><i class="fas fa-check"></i> Dos sesiones al mes</li>
                            <li><i class="fas fa-check"></i> Seguimiento regular</li>
                            <li><i class="fas fa-check"></i> Cada sesión sale a 50€</li>
                            <li><i class="fas fa-check"></i> Acompañamiento entre sesiones</li>
                        </ul>
                    </div>
                    <a href="contacta.php" class="btn pricing-btn">Programa una sesión</a>
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
                            <li><i class="fas fa-check"></i> Cuatro sesiones al mes</li>
                            <li><i class="fas fa-check"></i> Sesiones por 45€. Ahorro del 25%</li>
                            <li><i class="fas fa-check"></i> Proceso acelerado y constante</li>
                            <li><i class="fas fa-check"></i> Resultados optimizados</li>
                        </ul>
                    </div>
                    <a href="contacta.php" class="btn pricing-btn">Programa una sesión</a>
                </div>
            </div>
        </div>
    </section>
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

        // Script para el efecto de scroll en la navegación
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
                    // Obtenir l'idioma del data attribute
                    const lang = this.getAttribute('data-lang');
                    console.log('Botón pulsado, idioma:', lang);
                    
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