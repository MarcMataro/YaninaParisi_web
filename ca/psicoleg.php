<?php 
// Inicialitzar sessió si no està iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Processar canvi d'idioma ABANS de forçar l'idioma
if (isset($_GET['lang'])) {
    $lang = $_GET['lang'];
    if (in_array($lang, array('ca', 'es'))) {
        $_SESSION['language'] = $lang;
        // Redirigir a la pàgina corresponent en l'altre idioma
        if ($lang === 'es') {
            $slug = $_GET['nom'] ?? '';
            header('Location: ../es/psicologo.php?nombre=' . urlencode($slug));
            exit;
        }
        // Si ja estem en català, redirigir sense canviar
        header('Location: psicoleg.php?nom=' . urlencode($_GET['nom'] ?? ''));
        exit;
    }
}

// Forçar idioma català en aquesta pàgina (després de processar canvi d'idioma)
$_SESSION['language'] = 'ca';

// Incluir sistema de traducció i funcions
include '../includes/lang.php';
include '../includes/functions.php';

// Carregar classes necessàries
require_once __DIR__ . '/../classes/connexio.php';
require_once __DIR__ . '/../classes/professionals.php';
require_once __DIR__ . '/../classes/professional_certificacions.php';
require_once __DIR__ . '/../classes/professional_photos.php';
require_once __DIR__ . '/../classes/professional_especialitat.php';
require_once __DIR__ . '/../classes/especialitats.php';

// Obtenir el slug de la URL
$slug = $_GET['nom'] ?? '';

if (empty($slug)) {
    header('Location: coneixnos.php');
    exit;
}

try {
    $profModel = Professionals::getInstance();
    $professional = findProfessionalBySlug($slug, $profModel);
    
    if (!$professional || !$professional['actiu'] || !$professional['visible_web']) {
        header('Location: coneixnos.php');
        exit;
    }
} catch (Exception $e) {
    error_log("Error al carregar professional: " . $e->getMessage());
    header('Location: coneixnos.php');
    exit;
}

// Meta tags dinàmics
$metaTitle = htmlspecialchars($professional['nom'] . ' ' . $professional['cognoms']) . ' - Yanina Parisi';
$metaDescription = !empty($professional['subtitol_ca']) ? htmlspecialchars($professional['subtitol_ca']) : 'Coneix ' . htmlspecialchars($professional['nom'] . ' ' . $professional['cognoms']) . ', professional de la clínica de Yanina Parisi.';
?>
<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $metaTitle; ?></title>
    <meta name="description" content="<?php echo $metaDescription; ?>">
    <meta name="keywords" content="<?php echo htmlspecialchars($professional['nom'] . ' ' . $professional['cognoms']); ?>, psicòleg, Girona, Yanina Parisi">
    
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="profile">
    <meta property="og:url" content="<?php echo 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>">
    <meta property="og:title" content="<?php echo $metaTitle; ?>">
    <meta property="og:description" content="<?php echo $metaDescription; ?>">
    <?php if (!empty($professional['foto'])): ?>
        <meta property="og:image" content="<?php echo 'http://' . $_SERVER['HTTP_HOST'] . '/' . $professional['foto']; ?>">
    <?php endif; ?>
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@7.0.0/css/all.min.css">
    <link rel="stylesheet" href="../css/estils.css">
    <link rel="stylesheet" href="../css/psicoleg.css">
    <link rel="icon" type="image/png" sizes="32x32" href="../img/Logo32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="../img/Logo16.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Libre+Baskerville:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet">
</head>
<body>
    <?php include '_includes/navigation.php'; ?>

    <!-- Hero Section amb foto del professional -->
    <section class="professional-hero" style="background-image: linear-gradient(135deg, rgba(98, 66, 119, 0.85), rgba(171, 120, 141, 0.75))<?php if (!empty($professional['foto'])): ?>, url('../<?php echo htmlspecialchars($professional['foto']); ?>')<?php endif; ?>;">
        <div class="hero-overlay"></div>
        <h1 class="professional-hero-name fade-in-up"><?php echo htmlspecialchars($professional['nom'] . ' ' . $professional['cognoms']); ?></h1>
    </section>

    <?php
        // Breadcrumbs: Home > Coneix-nos > Nom Professional
        if (function_exists('render_breadcrumbs')) {
            render_breadcrumbs([
                ['label' => 'Inici', 'url' => 'home.php'],
                ['label' => 'Coneix-nos', 'url' => 'coneixnos.php'],
                ['label' => $professional['nom'] . ' ' . $professional['cognoms']]
            ]);
        }
    ?>

    <!-- Professional Info Header -->
    <section class="professional-info-header">
        <div class="container">
            <div class="info-header-content">
                <div class="info-stats">
                    <?php if (!empty($professional['anys_experiencia'])): ?>
                        <div class="info-stat-item">
                            <i class="fas fa-award"></i>
                            <span><strong><?php echo htmlspecialchars($professional['anys_experiencia']); ?>+</strong> anys d'experiència</span>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($professional['num_collegiat'])): ?>
                        <div class="info-stat-item">
                            <i class="fas fa-certificate"></i>
                            <span>Col·legiat/da núm. <strong><?php echo htmlspecialchars($professional['num_collegiat']); ?></strong></span>
                        </div>
                    <?php endif; ?>
                </div>
                <?php if (!empty($professional['subtitol_ca'])): ?>
                    <p class="info-subtitle">
                        <?php echo htmlspecialchars($professional['subtitol_ca']); ?>
                    </p>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Contingut Principal -->
    <section class="professional-detail">
        <div class="container">
            <div class="professional-content">
                <?php if (!empty($professional['descripcio'])): ?>
                    <div class="description-with-photo">
                        <?php if (!empty($professional['foto'])): ?>
                            <div class="floating-photo">
                                <?php 
                                    $fotoPath = $professional['foto'];
                                    if (strpos($fotoPath, '../') !== 0 && strpos($fotoPath, 'img/') === 0) {
                                        $fotoPath = '../' . $fotoPath;
                                    } elseif (strpos($fotoPath, '../') !== 0 && strpos($fotoPath, 'img/') !== 0) {
                                        $fotoPath = '../img/' . $fotoPath;
                                    }
                                ?>
                                <img src="<?php echo htmlspecialchars($fotoPath); ?>" 
                                     alt="<?php echo htmlspecialchars($professional['nom'] . ' ' . $professional['cognoms']); ?>">
                            </div>
                        <?php endif; ?>
                        <div class="description-text">
                            <?php echo nl2br(htmlspecialchars($professional['descripcio'])); ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <?php
        // Obtenir certificacions del professional
        try {
            $certModel = ProfessionalCertificacions::getInstance();
            $certificacions = $certModel->obtenirPerProfessional($professional['id']);
        } catch (Exception $e) {
            error_log("Error al carregar certificacions: " . $e->getMessage());
            $certificacions = null;
        }

        // Obtenir fotos del professional
        try {
            $photosModel = ProfessionalPhotos::getInstance();
            $fotos = $photosModel->llistarPerProfessional($professional['id']);
            $primeraFoto = !empty($fotos) ? $fotos[0] : null;
            $restaDeFotos = !empty($fotos) ? array_slice($fotos, 1) : [];
        } catch (Exception $e) {
            error_log("Error al carregar fotos: " . $e->getMessage());
            $primeraFoto = null;
            $restaDeFotos = [];
        }

        // Obtenir especialitats del professional
        try {
            $relEspModel = ProfessionalEspecialitat::getInstance();
            $especialitats = $relEspModel->obtenirEspecialitatsProfessional($professional['id']);
        } catch (Exception $e) {
            error_log("Error al carregar especialitats: " . $e->getMessage());
            $especialitats = [];
        }
    ?>

    <?php if ($certificacions && !empty($certificacions['certificacions_ca'])): ?>
    <!-- Secció de Certificats i Màsters -->
    <section class="professional-certificates-section">
        <div class="certificates-container">
            <div class="certificates-header">
                <div class="section-icon">
                    <i class="fas fa-graduation-cap"></i>
                </div>
                <h2>Formació i Certificacions</h2>
            </div>
            <div class="certificates-content">
                <?php
                    // Convertir text en llista si conté salts de línia
                    $certificacionsText = htmlspecialchars($certificacions['certificacions_ca']);
                    $linies = explode("\n", $certificacionsText);
                    $linies = array_filter(array_map('trim', $linies)); // Eliminar línies buides
                    $totalLinies = count($linies);
                    
                    if ($totalLinies > 1) {
                        echo '<ul class="certificates-list" id="certificatesList">';
                        foreach ($linies as $index => $linia) {
                            if (!empty($linia)) {
                                // Afegir classe 'hidden' als elements després del 3r
                                $hiddenClass = $index >= 3 ? ' certificate-item-hidden' : '';
                                echo '<li class="certificate-item' . $hiddenClass . '">' . $linia . '</li>';
                            }
                        }
                        echo '</ul>';
                    } else {
                        echo '<p>' . $certificacionsText . '</p>';
                    }
                ?>
            </div>
            <?php if ($totalLinies > 3): ?>
                <button class="certificates-toggle-btn" id="certificatesToggle">
                    <span class="toggle-text">Veure més certificacions</span>
                    <i class="fas fa-chevron-down toggle-icon"></i>
                </button>
            <?php endif; ?>
        </div>
    </section>
    <?php endif; ?>

    <?php if ($primeraFoto): ?>
    <!-- Primera Foto amb Text -->
    <section class="professional-photo-section">
        <div class="container">
            <div class="photo-content-wrapper">
                <div class="photo-image">
                    <img src="../<?php echo htmlspecialchars($primeraFoto['image_path']); ?>" 
                         alt="<?php echo htmlspecialchars($primeraFoto['alt_ca']); ?>">
                </div>
                <div class="photo-text">
                    <h2><?php echo htmlspecialchars($primeraFoto['title_ca']); ?></h2>
                    <?php if (!empty($primeraFoto['description_ca'])): ?>
                        <p><?php echo nl2br(htmlspecialchars($primeraFoto['description_ca'])); ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <?php if (!empty($especialitats)): ?>
    <!-- Secció d'Especialitats -->
    <section class="professional-specialties-section">
        <div class="container">
            <div class="specialties-header">
                <h2><i class="fas fa-stethoscope"></i> Especialitats</h2>
            </div>
            <div class="specialties-grid">
                <?php foreach ($especialitats as $especialitat): ?>
                    <div class="specialty-item">
                        <h3><?php echo htmlspecialchars($especialitat['nom']); ?></h3>
                        <?php if (!empty($especialitat['descripcio'])): ?>
                            <p><?php echo htmlspecialchars($especialitat['descripcio']); ?></p>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <?php if (!empty($restaDeFotos)): ?>
    <?php foreach ($restaDeFotos as $index => $foto): ?>
    <!-- Secció de Foto amb Text -->
    <section class="professional-photo-section">
        <div class="container">
            <div class="photo-content-wrapper <?php echo ($index % 2 === 0) ? 'reverse' : ''; ?>">
                <div class="photo-image">
                    <img src="../<?php echo htmlspecialchars($foto['image_path']); ?>" 
                         alt="<?php echo htmlspecialchars($foto['alt_ca']); ?>">
                </div>
                <div class="photo-text">
                    <h2><?php echo htmlspecialchars($foto['title_ca']); ?></h2>
                    <?php if (!empty($foto['description_ca'])): ?>
                        <p><?php echo nl2br(htmlspecialchars($foto['description_ca'])); ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>
    <?php endforeach; ?>
    <?php endif; ?>

    <?php include '_includes/footer.php'; ?>
    
    <script src="../js/site-nav.js"></script>
    <script src="../js/language.js"></script>
    
    <script>
    // Animacions scroll-triggered
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -100px 0px'
    };
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
            }
        });
    }, observerOptions);
    
    document.querySelectorAll('.animate-on-scroll').forEach(el => {
        observer.observe(el);
    });
    
    // Smooth scroll per al hero indicator
    document.querySelector('.hero-scroll-indicator')?.addEventListener('click', () => {
        document.querySelector('.professional-detail').scrollIntoView({ 
            behavior: 'smooth' 
        });
    });
    
    // Funcionalitat d'expandir/contraure certificacions
    const toggleBtn = document.getElementById('certificatesToggle');
    if (toggleBtn) {
        toggleBtn.addEventListener('click', function() {
            const list = document.getElementById('certificatesList');
            const toggleText = this.querySelector('.toggle-text');
            const isExpanded = list.classList.contains('expanded');
            
            if (isExpanded) {
                list.classList.remove('expanded');
                this.classList.remove('expanded');
                toggleText.textContent = 'Veure més certificacions';
            } else {
                list.classList.add('expanded');
                this.classList.add('expanded');
                toggleText.textContent = 'Veure menys';
            }
        });
    }
    </script>
</body>
</html>
