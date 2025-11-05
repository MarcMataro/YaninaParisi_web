<!-- Pàgina que mostrarà el servei de recerca de parella -->
<?php
    // Inicialitzar sessió si no està iniciada
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Debug ABANS del processament
    echo "<!-- DEBUG INDEX ABANS: GET lang: " . ($_GET['lang'] ?? 'no definit') . " -->";
    echo "<!-- DEBUG INDEX ABANS: Session lang abans: " . ($_SESSION['language'] ?? 'no definit') . " -->";

    // Forçar idioma català en aquesta pàgina
    $_SESSION['language'] = 'ca';
    // Processar canvi d'idioma PRIMER
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
    <title><?php echo t('nav_blog'); ?> | Yanina Parisi</title>
    <meta name="description" content="Consulta les últimes entrades del blog de Yanina Parisi.">
    <link rel="stylesheet" href="../css/estils.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@7.0.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Libre+Baskerville:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" sizes="32x32" href="../img/Logo32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="../img/Logo16.png">
</head>
<body>
    <?php include '_includes/navigation.php'; ?>
    <!-- Secció Hero - Love Match (reutilitza estil Sobre Mi) -->
    <section class="hero love-hero" id="love-match">
        <div class="container hero-content">
            <h1 class="hero-title">El camí cap a una connexió veritable</h1>
            <p class="hero-subtitle"></p>
        </div>
    </section>
    <main>
        <?php
            // Breadcrumbs: Home > Dos ànimes (CAT)
            if (function_exists('render_breadcrumbs')) {
                render_breadcrumbs([
                    ['label' => t('nav_home'), 'url' => 'home.php'],
                    ['label' => t('nav_couple_search')]
                ]);
            }
        ?>
        <!-- Intro del servei: Love Match -->
        <section class="service-intro">
            <div class="container">
                <h2>Has arribat a un punt on les app de cites et generen més fatiga que il·lusió?</h2>
                <p>Despertar-se cada dia amb la sensació de soledat, malgastar energies en converses que no condueixen a enlloc, i sentir la pressió del rellotge biològic o social... és extenuant. Però, i si et digués que hi ha una manera més intel·ligent, humana i eficaç de trobar una parella estable?</p>

                <p>Aquest servei no és una app més. És un procés d'acompanyament personalitzat dissenyat per a persones com tu, que busquen alguna cosa real en un món de perfils superficials. Aquí, no ets un altre perfil més en una llista infinita; ets una persona única amb valors, somnis i la capacitat de construir una relació significativa.</p>

                <p><strong>Deixa de buscar i comença a connectar.</strong></p>

                <p><em>Aquest mètode està pensat per a qui:</em></p>
                <ul>
                    <li>Ja ha provat les app de cites i n'ha sortit decebut.</li>
                    <li>Té clar el que vol i no està disposat a conformar-se amb menys.</li>
                    <li>Valora la seva estabilitat emocional i no vol malgastar el seu temps.</li>
                    <li>Creu que l'amor pot ser una decisió conscient, no només una qüestió d'atzar.</li>
                </ul>

                <p>Si et sents identificat, segueix llegint. Has trobat el que portaves temps buscant.</p>

                <p class="service-cta"><a class="btn btn-primary" href="contacta.php">Contacta per a una consulta</a></p>
            </div>
        </section>
        <!-- Secció de presentació del servei de recerca de parella -->
        <section>

        </section>
        <!-- Fi de la secció de presentació del servei de recerca de parella -->
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
</body>
</html>