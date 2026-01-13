<?php 
// Inicializar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Forzar idioma español en esta página
$_SESSION['language'] = 'es';

// Procesar cambio de idioma
if (isset($_GET['lang'])) {
    $lang = $_GET['lang'];
    if (in_array($lang, array('ca', 'es'))) {
        $_SESSION['language'] = $lang;
        header('Location: /' . $lang . '/home.php');
        exit;
    }
}

// Incluir sistema de traducción y funciones
include '../includes/lang.php';
include '../includes/functions.php';

// Cargar la clase de profesionales
require_once __DIR__ . '/../classes/connexio.php';
require_once __DIR__ . '/../classes/professionals.php';

try {
    $profModel = Professionals::getInstance();
    $professionals = $profModel->llistarVisiblesWeb();
} catch (Exception $e) {
    error_log("Error al cargar profesionales: " . $e->getMessage());
    $professionals = [];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Conócenos - Yanina Parisi</title>
    <meta name="description" content="Conoce el equipo de profesionales de la clínica de Yanina Parisi, psicóloga en Girona.">
    <meta name="keywords" content="equipo, profesionales, psicólogos, Girona, Yanina Parisi">
    
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?php echo 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>">
    <meta property="og:title" content="Conócenos - Yanina Parisi">
    <meta property="og:description" content="Conoce el equipo de profesionales de la clínica de Yanina Parisi.">
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
                <h1>Conócenos</h1>
                <p class="team-hero-subtitle">
                    Nuestro equipo de profesionales está aquí para acompañarte en tu camino hacia el bienestar emocional.
                </p>
            </div>
        </div>
    </section>

    <?php
        // Breadcrumbs: Home > Conócenos
        if (function_exists('render_breadcrumbs')) {
            render_breadcrumbs([
                ['label' => 'Inicio', 'url' => 'home.php'],
                ['label' => 'Conócenos']
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
                                    <?php $fotoUrl = resolve_media_url($prof['foto']); ?>
                                    <img src="<?php echo htmlspecialchars($fotoUrl); ?>" 
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
                                
                                <?php if (!empty($prof['subtitol_es'])): ?>
                                    <p class="professional-subtitle">
                                        <?php echo htmlspecialchars($prof['subtitol_es']); ?>
                                    </p>
                                <?php endif; ?>
                                
                                <?php if (!empty($prof['num_collegiat'])): ?>
                                    <p class="professional-college">
                                        <i class="fas fa-id-card"></i>
                                        Núm. Colegiado: <?php echo htmlspecialchars($prof['num_collegiat']); ?>
                                    </p>
                                <?php endif; ?>
                                
                                <?php if (!empty($prof['anys_experiencia'])): ?>
                                    <p class="professional-experience">
                                        <i class="fas fa-award"></i>
                                        <?php echo htmlspecialchars($prof['anys_experiencia']); ?> años de experiencia
                                    </p>
                                <?php endif; ?>
                                
                                <?php if (!empty($prof['descripcio_es'])): ?>
                                    <div class="professional-description">
                                        <?php echo nl2br(htmlspecialchars($prof['descripcio_es'])); ?>
                                    </div>
                                <?php endif; ?>
                                
                                <?php 
                                $slug = generateSlug($prof['nom'], $prof['cognoms']);
                                ?>
                                <a href="psicologo.php?nombre=<?php echo urlencode($slug); ?>" class="btn-view-profile">
                                    Ver perfil completo
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
                    <p>Actualmente no hay profesionales disponibles.</p>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <?php include '_includes/footer.php'; ?>
    
    <script src="../js/site-nav.js"></script>
    <script src="../js/language.js"></script>
</body>
</html>
