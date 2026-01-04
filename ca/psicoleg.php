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
        <div class="container">
            <div class="professional-hero-content">
                <div class="hero-badge fade-in-up">
                    <i class="fas fa-user-md"></i>
                    <span>Professional Col·legiat</span>
                </div>
                <h1 class="fade-in-up delay-1"><?php echo htmlspecialchars($professional['nom'] . ' ' . $professional['cognoms']); ?></h1>
                <?php if (!empty($professional['subtitol_ca'])): ?>
                    <p class="professional-hero-subtitle fade-in-up delay-2">
                        <?php echo htmlspecialchars($professional['subtitol_ca']); ?>
                    </p>
                <?php endif; ?>
                <div class="hero-stats fade-in-up delay-3">
                    <?php if (!empty($professional['anys_experiencia'])): ?>
                        <div class="stat-item">
                            <div class="stat-number"><?php echo htmlspecialchars($professional['anys_experiencia']); ?>+</div>
                            <div class="stat-label">Anys d'experiència</div>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($professional['num_collegiat'])): ?>
                        <div class="stat-item">
                            <div class="stat-icon"><i class="fas fa-certificate"></i></div>
                            <div class="stat-label">Col·legiat/da</div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="hero-scroll-indicator">
            <i class="fas fa-chevron-down"></i>
        </div>
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

    <!-- Contingut Principal -->
    <section class="professional-detail">
        <div class="container">
            <div class="professional-grid">
                <!-- Columna esquerra: Informació bàsica -->
                <aside class="professional-sidebar">
                    <?php if (!empty($professional['foto'])): ?>
                        <div class="professional-photo">
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
                    
                    <div class="professional-info-box">
                        <?php if (!empty($professional['num_collegiat'])): ?>
                            <div class="info-item">
                                <i class="fas fa-id-card"></i>
                                <div>
                                    <strong>Núm. Col·legiat</strong>
                                    <span><?php echo htmlspecialchars($professional['num_collegiat']); ?></span>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($professional['anys_experiencia'])): ?>
                            <div class="info-item">
                                <i class="fas fa-award"></i>
                                <div>
                                    <strong>Experiència</strong>
                                    <span><?php echo htmlspecialchars($professional['anys_experiencia']); ?> anys</span>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <a href="contacta.php" class="btn-primary-large">
                        <i class="fas fa-calendar-check"></i>
                        Reserva una cita
                    </a>
                </aside>
                
                <!-- Columna dreta: Descripció i contingut -->
                <article class="professional-main-content">
                    <?php if (!empty($professional['descripcio'])): ?>
                        <div class="content-section animate-on-scroll">
                            <div class="section-header">
                                <div class="section-icon">
                                    <i class="fas fa-user-circle"></i>
                                </div>
                                <h2>Sobre mi</h2>
                            </div>
                            <div class="content-text">
                                <?php echo nl2br(htmlspecialchars($professional['descripcio'])); ?>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Secció d'especialitats -->
                    <div class="content-section animate-on-scroll">
                        <div class="section-header">
                            <div class="section-icon">
                                <i class="fas fa-brain"></i>
                            </div>
                            <h2>Àrees d'especialització</h2>
                        </div>
                        <div class="specialties-grid">
                            <div class="specialty-card">
                                <div class="specialty-icon">
                                    <i class="fas fa-heart"></i>
                                </div>
                                <h3>Teràpia individual</h3>
                                <p>Acompanyament personalitzat per superar reptes emocionals i personals.</p>
                            </div>
                            <div class="specialty-card">
                                <div class="specialty-icon">
                                    <i class="fas fa-users"></i>
                                </div>
                                <h3>Teràpia de parella</h3>
                                <p>Millora de la comunicació i resolució de conflictes en les relacions.</p>
                            </div>
                            <div class="specialty-card">
                                <div class="specialty-icon">
                                    <i class="fas fa-child"></i>
                                </div>
                                <h3>Psicologia infantil</h3>
                                <p>Atenció especialitzada per al desenvolupament emocional dels més petits.</p>
                            </div>
                            <div class="specialty-card">
                                <div class="specialty-icon">
                                    <i class="fas fa-mind-share"></i>
                                </div>
                                <h3>Gestió emocional</h3>
                                <p>Tècniques per gestionar ansietat, estrès i altres estats emocionals.</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Secció de metodologia -->
                    <div class="content-section animate-on-scroll">
                        <div class="section-header">
                            <div class="section-icon">
                                <i class="fas fa-compass"></i>
                            </div>
                            <h2>La meva metodologia</h2>
                        </div>
                        <div class="methodology-content">
                            <div class="methodology-item">
                                <div class="methodology-number">01</div>
                                <div class="methodology-text">
                                    <h3>Escolta activa</h3>
                                    <p>Començo entenent la teva història i les teves necessitats úniques.</p>
                                </div>
                            </div>
                            <div class="methodology-item">
                                <div class="methodology-number">02</div>
                                <div class="methodology-text">
                                    <h3>Diagnòstic personalitzat</h3>
                                    <p>Desenvolupo un pla d'acció adaptat als teus objectius específics.</p>
                                </div>
                            </div>
                            <div class="methodology-item">
                                <div class="methodology-number">03</div>
                                <div class="methodology-text">
                                    <h3>Acompanyament continu</h3>
                                    <p>Et guio en el procés amb eines pràctiques i suport constant.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Call to action destacat -->
                    <div class="content-cta animate-on-scroll">
                        <div class="cta-content">
                            <h3>Comença el teu camí cap al benestar</h3>
                            <p>Estic aquí per acompanyar-te en aquest viatge de creixement personal.</p>
                            <a href="contacta.php" class="cta-button">
                                <i class="fas fa-calendar-check"></i>
                                Reserva la teva primera sessió
                            </a>
                        </div>
                    </div>
                </article>
            </div>
            
            <div class="back-to-team">
                <a href="coneixnos.php" class="btn-secondary">
                    <i class="fas fa-arrow-left"></i>
                    Tornar a l'equip
                </a>
            </div>
        </div>
    </section>

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
    </script>
</body>
</html>
