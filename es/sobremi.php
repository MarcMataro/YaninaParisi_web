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
// Incluir sistema de traducció
include '../includes/functions.php';
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
    <link rel="stylesheet" href="../css/estils.css">
    <link rel="stylesheet" href="../css/sobremi.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Libre+Baskerville:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" sizes="32x32" href="../img/Logo32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="../img/Logo16.png">
</head>
<body>
    <?php include '_includes/navigation.php'; ?>

    <!-- Secció Hero - Sobre Mi -->
    <section class="hero sobremi-hero" id="sobre-mi">
        <div class="container hero-content">
            <h1 class="about-title">Psicología con propósito para tu transformación personal</h1>
        </div>
    </section>

    <main>
        <?php
            // Breadcrumbs: Home > Sobre mi (ES)
            if (function_exists('render_breadcrumbs')) {
                render_breadcrumbs([
                    ['label' => t('nav_home'), 'url' => 'home.php'],
                    ['label' => t('nav_about')]
                ]);
            }
        ?>
        <section class="about-main spectacular">
            <div class="container">
                
                <p class="about-intro">Hola, soy <strong>Yanina Parisi</strong>. Mi historia no es solo la de una psicóloga; es la de alguien que nació y se crió entendiendo el lenguaje del alma humana. En el seno de una familia de psicólogos y psicoanalistas en Argentina, crecí entre conversaciones que descifraban la complejidad de la mente. Esa herencia no fue solo una profesión, sino una vocación: la de mirar el mundo con sensibilidad y la firme convicción de que todos merecemos una vida plena.</p>

                <p>Hoy, con más de una década de experiencia, he consolidado esa vocación en una práctica profesional integral. Soy <strong>Psicóloga General Sanitaria Colegiada</strong>, <strong>Perito Psicóloga Judicial</strong>, <strong>Mediadora Familiar</strong> y <strong>Coach Certificada</strong>. Pero, más allá de los títulos, mi misión es una: ser tu guía experta en el camino hacia el bienestar y el cambio que buscas.</p>

                <h2 class="about-section-title">¿Te Sientes Identificado con Alguna de Estas Situaciones?</h2>
                <ul class="about-list">
                    <li><strong>Cuando el Amor Duele:</strong> Si estás atravesando una crisis de pareja, sintiendo que la conexión se desvanece o que el conflicto es constante, te ofrezco un espacio para reconstruir o, si es necesario, cerrar ciclos con entendimiento y paz.</li>
                    <li><strong>Cuando el Amor Falla (o no llega):</strong> Si estás cansado de la soledad o de relaciones que no te llenan, y deseas construir una pareja estable y significativa, voy más allá de la terapia tradicional. Te ofrezco un servicio exclusivo de búsqueda de pareja basado en criterios psicológicos y de compatibilidad.</li>
                </ul>
                <p>Olvídate de las apps de citas que generan desgaste y desilusión. Mi método está diseñado para quienes buscan vínculos auténticos y de calidad, conectando con personas afines desde la inteligencia emocional y no desde un algoritmo superficial.</p>

                <h2 class="about-section-title">Tu Bienestar es Multidimensional: Mis Servicios Especializados</h2>
                <ul class="about-list">
                    <li><strong>Terapia Psicológica Online</strong> (para toda España) y <strong>Presencial</strong> (en Girona): Un espacio seguro y confidencial donde trabajamos juntos para superar la ansiedad, depresión, TOC, duelos, problemas de autoestima o crisis vitales. Integro las herramientas más eficaces de diferentes enfoques (psicoanálisis, cognitivo-conductual, humanista y ACT) para ofrecerte un plan 100% personalizado.</li>
                    <li><strong>Peritaje Psicológico Judicial:</strong> Si estás inmerso en un proceso legal relacionado con familia, custodias o violencia filio-parental, elaboro informes periciales rigurosos que aportan claridad psicológica crucial para tu caso.</li>
                    <li><strong>Mediación Familiar:</strong> Para cuando el conflicto familiar parece insuperable. Te ayudo a encontrar soluciones en un marco de respeto y diálogo, evitando desgastes emocionales y económicos mayores.</li>
                </ul>

                <h2 class="about-section-title">Mi Compromiso Contigo: No Solo Escucharte, Sino Comprenderte y Actuar</h2>
                <ul class="about-list">
                    <li>Creo un espacio seguro donde te sentirás verdaderamente escuchado y comprendido, sin juicios.</li>
                    <li>Me involucro con autenticidad y cercanía, porque la confianza es la base de toda transformación.</li>
                    <li>No me conformo con aliviar el malestar. Mi objetivo es que esta crisis se convierta en tu mayor oportunidad. Trabajaremos para abrir nuevas perspectivas, generar cambios reales y dotarte de herramientas prácticas que te permitan reconstruir una vida más consciente, libre y plena.</li>
                </ul>

                <h2 class="about-section-title">Llevando la Psicología Más Allá de la Consulta</h2>
                <p>Mi pasión por ayudar traspasa las paredes de mi consultorio. Escribo artículos en medios especializados y creo contenido en redes sociales con un estilo directo y cercano, porque creo que el conocimiento psicológico debe estar al alcance de todos. Es mi manera de tenderte una mano y ofrecerte recursos útiles, incluso antes de que decidas comenzar tu proceso.</p>

                <div class="about-cta">
                    <h3>¿Estás Listo para Dar el Primer Paso Hacia tu Cambio?</h3>
                    <p>Tu valentía te ha traído hasta aquí. Ahora, la decisión de avanzar está en tus manos.</p>
                    <a href="contacta.php" class="btn btn-primary">Reserva tu Sesión Inicial</a>
                    <a href="contacta.php" class="btn btn-secondary">Contáctame para Más Información</a>
                    <p class="about-note">Terapia online disponible para toda España. Consulta presencial en Girona ciudad.</p>
                </div>
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
    