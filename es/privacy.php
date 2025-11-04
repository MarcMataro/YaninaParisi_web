<?php
// Inicializar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Forzar idioma español en esta página
$_SESSION['language'] = 'es';
// Procesar cambio de idioma PRIMERO
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
        $tries = ['/privacy.php','/privacy','/es/privacy.php','/es/privacy','/politica-privacidad','/politica-de-privacidad'];
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
    <meta property="og:locale" content="es_ES">

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
        <?php /* imagen del hero como <img> para rutas consistentes */ ?>
        <img src="<?php echo htmlspecialchars(resolve_media_url('img/portada.jpg')); ?>" alt="Portada" class="hero-img">
        <div class="container hero-content">
            <h1 class="hero-title"><?php echo htmlspecialchars(isset($pagina_seo) && $pagina_seo instanceof SEO_OnPage ? $pagina_seo->getH1($lang) : 'Política de privacidad'); ?></h1>
        </div>
    </section>

    <main class="container">
        <?php
            // Breadcrumbs: Inicio > Política de privacidad
            if (function_exists('render_breadcrumbs')) {
                render_breadcrumbs([
                    ['label' => t('nav_home'), 'url' => '/es/home.php'],
                    ['label' => 'Política de privacidad']
                ]);
            }
        ?>

        <section class="content-section">
            <div class="policy-content">
                <h2>1. Responsable del tratamiento</h2>
                <p>El Responsable del tratamiento de sus datos personales es:</p>
                <ul>
                    <li><strong>Nombre:</strong> [Nombre y apellidos de la psicóloga]</li>
                    <li><strong>NIF/CIF:</strong> [Número de identificación]</li>
                    <li><strong>Domicilio profesional:</strong> [Dirección completa del consultorio, Girona]</li>
                    <li><strong>Actividad:</strong> Psicóloga colegiada (Colegio Oficial de Psicología de Cataluña, núm. [XXXXX])</li>
                    <li><strong>Correo electrónico:</strong> [direccion@email.com]</li>
                    <li><strong>Teléfono:</strong> [Número de teléfono]</li>
                </ul>

                <h2>2. Finalidad del tratamiento de sus datos</h2>
                <p>En este sitio web recogemos y tratamos la información que nos facilite con las siguientes finalidades principales:</p>
                <ul>
                    <li><strong>Formulario de contacto:</strong> para gestionar y responder consultas, solicitudes de información o peticiones de cita previa.</li>
                    <li><strong>Suscripción al blog o newsletter:</strong> para enviarle materiales de interés, novedades del blog o recursos, siempre que haya dado su consentimiento explícito.</li>
                    <li><strong>Prestación del servicio psicológico:</strong> si contrata mis servicios, los datos se utilizarán para la gestión administrativa, la prestación de la terapia y el mantenimiento de la historia clínica.</li>
                </ul>

                <h2>3. Legitimación para el tratamiento</h2>
                <p>La base legal para el tratamiento de sus datos incluye, según el caso:</p>
                <ul>
                    <li><strong>Consentimiento:</strong> para responder consultas y enviar comunicaciones comerciales o newsletters.</li>
                    <li><strong>Ejecución de un contrato o relación precontractual:</strong> para gestionar la solicitud de cita y la posterior prestación del servicio psicológico.</li>
                    <li><strong>Interés vital:</strong> en situaciones de emergencia que puedan poner en peligro su integridad física o la de terceros.</li>
                    <li><strong>Cumplimiento de una obligación legal:</strong> como profesional sanitario, estoy obligada a custodiar la historia clínica durante el plazo legal correspondiente.</li>
                </ul>

                <h2>4. Conservación de los datos</h2>
                <p>Los datos de contacto y las consultas se conservarán mientras no solicite su supresión. Los datos relacionados con la prestación del servicio (historia clínica) se conservarán durante el plazo legalmente establecido para profesionales sanitarios (actualmente 5 años desde la última intervención, según la Ley 41/2002, de autonomía del paciente). Los datos para el envío de newsletters se conservarán mientras mantenga su consentimiento.</p>

                <h2>5. Destinatarios de los datos</h2>
                <p>No cederé sus datos a terceros, salvo en los siguientes casos:</p>
                <ul>
                    <li><strong>Obligación legal:</strong> cuando exista una obligación legal (por ejemplo, en casos de riesgo vital grave o por requerimiento judicial).</li>
                    <li><strong>Con su consentimiento explícito:</strong> para gestiones específicas que lo requieran y que haya autorizado previamente.</li>
                </ul>
                <p>Como profesional, estoy sujeta al secreto profesional y a las obligaciones de confidencialidad más estrictas.</p>

                <h2>6. Sus derechos</h2>
                <p>Tiene derecho a:</p>
                <ul>
                    <li>Acceder a sus datos personales.</li>
                    <li>Solicitar la rectificación de los datos inexactos.</li>
                    <li>Solicitar la supresión de sus datos cuando ya no sean necesarios para los fines para los que se recogieron.</li>
                    <li>Oponerse al tratamiento de sus datos.</li>
                    <li>Solicitar la limitación del tratamiento de sus datos.</li>
                    <li>Ejercer la portabilidad de los datos.</li>
                    <li>Retirar su consentimiento en cualquier momento, sin que ello afecte a la licitud del tratamiento basado en el consentimiento previo.</li>
                </ul>
                <p>Puede ejercer estos derechos mediante un escrito dirigido a <strong>[dirección postal del consultorio o dirección electrónica indicada en el apartado 1]</strong>. Además, tiene derecho a presentar una reclamación ante la Autoridad Catalana de Protección de Datos (APDCAT) o la Agencia Española de Protección de Datos (AEPD) si considera que el tratamiento no se ajusta a la normativa.</p>

                <h2>7. Origen de los datos</h2>
                <p>Los datos personales que tratamos proceden, preferentemente, de la propia persona interesada o de su representante legal.</p>

                <h2>8. Datos especialmente protegidos</h2>
                <p>En el marco de la prestación del servicio psicológico, es posible que tratemos datos de salud, que son categorías especiales de datos según la normativa. Este tratamiento se realiza con la máxima confidencialidad y está amparado por las bases legales mencionadas en el apartado 3 (cumplimiento de obligaciones en el ámbito sanitario y interés vital).</p>

                <h2>9. Actualización de la política</h2>
                <p>Esta política de privacidad puede actualizarse en cualquier momento para adaptarse a novedades legislativas o cambios en las actividades. La versión más actualizada estará siempre disponible en nuestro sitio web.</p>
                <p><strong>Fecha de la última actualización:</strong> [Fecha]</p>
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
