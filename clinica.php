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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@7.0.0/css/all.min.css">
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
</head>
<body>
    <?php include 'includes/navigation.php'; ?>
    <!-- Secció HERO -->
    <section class="hero clinica-hero" id="clinica-hero">
        <div class="container hero-content">
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

    
    </main>
    <?php include 'includes/footer.php'; ?>
</body>
</html>
