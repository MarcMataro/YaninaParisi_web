<?php 
// Inicialitzar sessió si no està iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Forçar idioma català en aquesta pàgina
$_SESSION['language'] = 'ca';

// Processar canvi d'idioma
if (isset($_GET['lang'])) {
    $lang = $_GET['lang'];
    if (in_array($lang, array('ca', 'es'))) {
        $_SESSION['language'] = $lang;
        header('Location: /' . $lang . '/home.php');
        exit;
    }
}

// Incluir sistema de traducció i funcions
include '../includes/lang.php';
include '../includes/functions.php';

// Carregar la classe de professionals
require_once __DIR__ . '/../classes/connexio.php';
require_once __DIR__ . '/../classes/professionals.php';

try {
    $profModel = Professionals::getInstance();
    $professionals = $profModel->llistarVisiblesWeb();
} catch (Exception $e) {
    error_log("Error al carregar professionals: " . $e->getMessage());
    $professionals = [];
}
?>
<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Coneix-nos - Yanina Parisi</title>
    <meta name="description" content="Coneix l'equip de professionals de la clínica de Yanina Parisi, psicòloga a Girona.">
    <meta name="keywords" content="equip, professionals, psicòlegs, Girona, Yanina Parisi">
    
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?php echo 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>">
    <meta property="og:title" content="Coneix-nos - Yanina Parisi">
    <meta property="og:description" content="Coneix l'equip de professionals de la clínica de Yanina Parisi.">
    <meta property="og:image" content="<?php echo 'http://' . $_SERVER['HTTP_HOST']; ?>/img/Logo.png">
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@7.0.0/css/all.min.css">
    <link rel="stylesheet" href="../css/estils.css">
    <link rel="stylesheet" href="../css/coneixnos.css">
    <link rel="icon" type="image/png" sizes="32x32" href="../img/Logo32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="../img/Logo16.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Libre+Baskerville:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet">
</head>
<body>
    <?php include '_includes/navigation.php'; ?>

    <!-- Hero Section -->
    <section class="team-hero">
        <div class="container">
            <div class="team-hero-content">
                <h1>Coneix-nos</h1>
                <p class="team-hero-subtitle">
                    El nostre equip de professionals està aquí per acompanyar-te en el teu camí cap al benestar emocional.
                </p>
            </div>
        </div>
    </section>

    <?php
        // Breadcrumbs: Home > Coneix-nos
        if (function_exists('render_breadcrumbs')) {
            render_breadcrumbs([
                ['label' => 'Inici', 'url' => 'home.php'],
                ['label' => 'Coneix-nos']
            ]);
        }
    ?>

    <!-- Professionals Section -->
    <section class="team-main">
        <div class="container">
            <?php if (!empty($professionals)): ?>
                <div class="professionals-grid">
                    <?php foreach ($professionals as $prof): ?>
                        <article class="professional-card">
                            <div class="professional-image">
                                <?php if (!empty($prof['foto'])): ?>
                                    <?php 
                                        // Normalitzar ruta: si ja comença amb ../ no afegir-ne més
                                        $fotoPath = $prof['foto'];
                                        if (strpos($fotoPath, '../') !== 0 && strpos($fotoPath, 'img/') === 0) {
                                            $fotoPath = '../' . $fotoPath;
                                        } elseif (strpos($fotoPath, '../') !== 0 && strpos($fotoPath, 'img/') !== 0) {
                                            $fotoPath = '../img/' . $fotoPath;
                                        }
                                    ?>
                                    <img src="<?php echo htmlspecialchars($fotoPath); ?>" 
                                         alt="<?php echo htmlspecialchars($prof['nom'] . ' ' . $prof['cognoms']); ?>">
                                <?php else: ?>
                                    <div class="professional-placeholder">
                                        <i class="fas fa-user"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="professional-content">
                                <h2 class="professional-name">
                                    <?php echo htmlspecialchars($prof['nom'] . ' ' . $prof['cognoms']); ?>
                                </h2>
                                
                                <?php if (!empty($prof['subtitol_ca'])): ?>
                                    <p class="professional-subtitle">
                                        <?php echo htmlspecialchars($prof['subtitol_ca']); ?>
                                    </p>
                                <?php endif; ?>
                                
                                <?php if (!empty($prof['num_collegiat'])): ?>
                                    <p class="professional-college">
                                        <i class="fas fa-id-card"></i>
                                        Núm. Col·legiat: <?php echo htmlspecialchars($prof['num_collegiat']); ?>
                                    </p>
                                <?php endif; ?>
                                
                                <?php if (!empty($prof['anys_experiencia'])): ?>
                                    <p class="professional-experience">
                                        <i class="fas fa-award"></i>
                                        <?php echo htmlspecialchars($prof['anys_experiencia']); ?> anys d'experiència
                                    </p>
                                <?php endif; ?>
                                
                                <?php if (!empty($prof['descripcio'])): ?>
                                    <div class="professional-description">
                                        <?php echo nl2br(htmlspecialchars($prof['descripcio'])); ?>
                                    </div>
                                <?php endif; ?>
                                
                                <?php 
                                $slug = generateSlug($prof['nom'], $prof['cognoms']);
                                ?>
                                <a href="psicoleg.php?nom=<?php echo urlencode($slug); ?>" class="btn-view-profile">
                                    Veure perfil complet
                                    <i class="fas fa-arrow-right"></i>
                                </a>
                                
                                <div class="professional-contact">
                                    <?php if (!empty($prof['email'])): ?>
                                        <a href="mailto:<?php echo htmlspecialchars($prof['email']); ?>" 
                                           class="contact-link">
                                            <i class="fas fa-envelope"></i>
                                            <?php echo htmlspecialchars($prof['email']); ?>
                                        </a>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($prof['telefon'])): ?>
                                        <a href="tel:<?php echo htmlspecialchars($prof['telefon']); ?>" 
                                           class="contact-link">
                                            <i class="fas fa-phone"></i>
                                            <?php echo htmlspecialchars($prof['telefon']); ?>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="no-professionals">
                    <i class="fas fa-info-circle"></i>
                    <p>Actualment no hi ha professionals disponibles.</p>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <?php include '_includes/footer.php'; ?>
    
    <script src="../js/site-nav.js"></script>
    <script src="../js/language.js"></script>
</body>
</html>
