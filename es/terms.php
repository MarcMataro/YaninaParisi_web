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
        $items = SEO_OnPage::llistarPaginesActives('terms');
        if (!empty($items) && isset($items[0]) && $items[0] instanceof SEO_OnPage) {
            $pagina_seo = $items[0];
            $seoTitle = $pagina_seo->getTitle($lang) ?: null;
        }
    } catch (Exception $e) { }

    if (!$seoTitle) {
        $tries = ['/terms.php','/terms','/es/terms.php','/es/terms','/terminos-condiciones'];
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
            <h1 class="hero-title"><?php echo htmlspecialchars(isset($pagina_seo) && $pagina_seo instanceof SEO_OnPage ? $pagina_seo->getH1($lang) : 'Términos y condiciones'); ?></h1>
        </div>
    </section>

    <main class="container">
        <?php
            // Breadcrumbs: Inicio > Términos y condiciones
            if (function_exists('render_breadcrumbs')) {
                render_breadcrumbs([
                    ['label' => t('nav_home'), 'url' => '/es/home.php'],
                    ['label' => 'Términos y condiciones']
                ]);
            }
        ?>

        <section class="content-section">
            <div class="policy-content">
                <h2>1. INFORMACIÓN GENERAL</h2>
                <p>Estos términos y condiciones (en adelante, "los Términos") regulan la relación profesional entre la clienta/cliente (en adelante, "la Persona Cliente") y <strong>[Nombre y apellidos de la psicóloga]</strong> (en adelante, "la Profesional"), con NIF <strong>[Número]</strong>, colegiada en el Colegio Oficial de Psicología de Cataluña con el número <strong>[Número]</strong>, y con domicilio profesional en <strong>[Dirección completa, Girona]</strong>.</p>
                <p>La contratación de los servicios ofrecidos por la Profesional implica la aceptación plena y sin reservas de todos y cada uno de estos Términos.</p>

                <h2>2. NATURALEZA DEL SERVICIO</h2>
                <p>La Profesional ofrece servicios de psicología clínica y psicoterapia. Estos servicios tienen como objetivo la promoción del bienestar emocional, la evaluación, el diagnóstico y el tratamiento psicológico de diversas dificultades y trastornos mentales.</p>
                <p>Es importante entender que:</p>
                <ul>
                    <li>La psicoterapia no es una ciencia exacta y los resultados pueden variar en función de múltiples factores.</li>
                    <li>El proceso terapéutico requiere un compromiso activo y un trabajo por parte de la Persona Cliente entre las sesiones.</li>
                    <li>La Profesional se compromete a ofrecer sus servicios con la máxima calidad, rigor profesional y basándose en evidencias científicas y protocolos reconocidos.</li>
                </ul>

                <h2>3. PROCESO DE CONTRATACIÓN Y CITA PREVIA</h2>
                <p><strong>Solicitud de cita:</strong> La solicitud de cita se puede realizar mediante el formulario de contacto del sitio web, correo electrónico o teléfono. Esta solicitud no supone la confirmación de la cita.</p>
                <p><strong>Confirmación:</strong> La cita solo se considerará confirmada una vez recibida una confirmación explícita por parte de la Profesional (mediante correo electrónico, SMS o llamada telefónica).</p>
                <p><strong>Cuestionario inicial:</strong> Antes de la primera sesión, se podrá solicitar a la Persona Cliente que rellene un cuestionario inicial para una mejor preparación de la sesión.</p>

                <h2>4. POLÍTICA DE PAGOS</h2>
                <p><strong>Tarifas:</strong> Las tarifas vigentes para los diferentes tipos de servicios (sesión individual, sesión familiar, etc.) se comunicarán a la Persona Cliente de forma transparente antes de la confirmación de la primera cita. Las tarifas pueden ser revisadas anualmente.</p>
                <p><strong>Forma de pago:</strong> El pago se realizará mediante <em>[Indicar método: transferencia bancaria, efectivo, tarjeta, Bizum]</em> y deberá abonarse el mismo día en que se presta el servicio, salvo que se acuerde por escrito una modalidad diferente.</p>
                <p><strong>Facturación:</strong> Se entregará una factura o recibo de pago a todas aquellas personas que lo soliciten.</p>

                <h2>5. POLÍTICA DE CANCELACIÓN Y CAMBIOS DE CITA</h2>
                <p>La Profesional dedica un tiempo específico y exclusivo a cada clienta o cliente. Por este motivo:</p>
                <ul>
                    <li>Las cancelaciones o cambios de cita deben comunicarse con un mínimo de 24 horas de antelación.</li>
                    <li>Las cancelaciones o cambios notificados con menos de 24 horas de antelación, o la no asistencia a la sesión sin aviso previo (<em>"no-show"</em>), podrán suponer el cobro del 100% del importe de la sesión.</li>
                    <li>La Profesional se compromete a aplicar la misma política en caso de cancelación por su parte, intentando ofrecer una nueva fecha lo antes posible.</li>
                </ul>

                <h2>6. CONFIDENCIALIDAD</h2>
                <p>La Profesional está obligada por ley y por su código deontológico a mantener la más estricta confidencialidad sobre toda la información revelada durante el proceso terapéutico. Esta confidencialidad solo podrá verse quebrantada en las situaciones excepcionales previstas por la ley:</p>
                <ul>
                    <li>Cuando exista un riesgo grave e inminente para la vida de la Persona Cliente o de terceras personas.</li>
                    <li>En casos de sospecha fundada de maltrato o abuso a menores o personas en situación de dependencia.</li>
                    <li>Por requerimiento judicial legalmente previsto.</li>
                </ul>

                <h2>7. DURACIÓN Y FINALIZACIÓN DE LOS SERVICIOS</h2>
                <p>La duración del proceso terapéutico será variable y dependerá de los objetivos establecidos y de la evolución de la Persona Cliente. La Persona Cliente o la Profesional pueden dar por finalizado el servicio en cualquier momento.</p>
                <p>Se recomienda que la finalización se lleve a cabo de forma pactada, preferiblemente con una sesión de cierre, para cerrar adecuadamente el proceso.</p>

                <h2>8. LIMITACIÓN DE RESPONSABILIDAD</h2>
                <p>La Profesional no se hace responsable de las decisiones tomadas por la Persona Cliente basadas en la información o las discusiones mantenidas durante las sesiones de terapia. La responsabilidad última del propio bienestar y de las acciones tomadas recae en la Persona Cliente.</p>
                <p>En situaciones de emergencia o crisis grave (ideación suicida, psicosis, etc.), la Persona Cliente debe ponerse en contacto con los servicios de urgencias (teléfono 112), acudir al hospital más cercano o llamar al teléfono de prevención del suicidio 024, ya que la Profesional no puede ofrecer un servicio de atención 24 horas.</p>

                <h2>9. PROTECCIÓN DE DATOS PERSONALES</h2>
                <p>Los datos personales y, especialmente, los datos de salud serán tratados de acuerdo con la Política de Privacidad, disponible en la página web, y en cumplimiento del Reglamento General de Protección de Datos (RGPD) y la Ley Orgánica de Protección de Datos Personales y garantía de los derechos digitales (LOPDGDD).</p>

                <h2>10. PROPIEDAD INTELECTUAL</h2>
                <p>Todo el material (artículos, folletos, cuestionarios, etc.) entregado a la Persona Cliente por parte de la Profesional es para su uso exclusivo y personal. Está prohibida su reproducción, distribución o modificación sin el consentimiento explícito por escrito de la Profesional.</p>

                <h2>11. ACEPTACIÓN Y MODIFICACIÓN DE LOS TÉRMINOS</h2>
                <p>La contratación del servicio implica la aceptación plena de estos Términos y Condiciones. La Profesional se reserva el derecho de modificar estos Términos. Los cambios serán notificados a las Personas Clientes y entrarán en vigor con carácter general un mes después de su publicación en la página web.</p>

                <h2>12. FORO DE RESOLUCIÓN DE CONTROVERSIAS Y JURISDICCIÓN</h2>
                <p>Las partes se comprometen a intentar resolver cualquier controversia derivada de estos Términos mediante la negociación de buena fe. En caso de no llegar a un acuerdo, las partes se someterán a los juzgados y tribunales de la ciudad de Girona, con renuncia expresa a cualquier otro fuero, si procede.</p>

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
