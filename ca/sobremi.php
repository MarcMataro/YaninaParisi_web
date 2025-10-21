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
</head>
<body>
    <?php include '_includes/navigation.php'; ?>
    <!-- Secció Hero - Sobre Mi -->
    <section class="hero sobremi-hero" id="sobre-mi">
        <div class="container hero-content">
            <h1 class="about-title">Psicología amb propòsit per a la teva transformació personal</h1>
        </div>
    </section>
    <main>
        <section class="about-main spectacular">
            <div class="container">
                <p class="about-intro">Hola, soc <strong>Yanina Parisi</strong>. La meva història no és només la d’una psicòloga; és la d’algú que va néixer i créixer entenent el llenguatge de l’ànima humana. En el si d’una família de psicòlegs i psicoanalistes a l’Argentina, vaig créixer entre converses que desxifraven la complexitat de la ment. Aquesta herència no va ser només una professió, sinó una vocació: la de mirar el món amb sensibilitat i la ferma convicció que tothom mereix una vida plena.</p>

                <p>Avui, amb més d’una dècada d’experiència, he consolidat aquesta vocació en una pràctica professional integral. Soc <strong>Psicòloga General Sanitària Col·legiada</strong>, <strong>Perita Psicòloga Judicial</strong>, <strong>Mediadora Familiar</strong> i <strong>Coach Certificada</strong>. Però, més enllà dels títols, la meva missió és una: ser la teva guia experta en el camí cap al benestar i el canvi que cerques.</p>

                <h2 class="about-section-title">Et sents identificat amb alguna d’aquestes situacions?</h2>
                <ul class="about-list">
                    <li><strong>Quan l’amor fa mal:</strong> Si estàs travessant una crisi de parella, sents que la connexió s’esvaeix o que el conflicte és constant, t’ofereixo un espai per reconstruir o, si cal, tancar cicles amb comprensió i pau.</li>
                    <li><strong>Quan l’amor falla (o no arriba):</strong> Si estàs cansat de la solitud o de relacions que no t’omplen, i vols construir una parella estable i significativa, vaig més enllà de la teràpia tradicional. T’ofereixo un servei exclusiu de cerca de parella basat en criteris psicològics i de compatibilitat.</li>
                </ul>
                <p>Oblida’t de les apps de cites que generen desgast i desil·lusió. El meu mètode està pensat per a qui busca vincles autèntics i de qualitat, connectant amb persones afins des de la intel·ligència emocional i no des d’un algoritme superficial.</p>

                <h2 class="about-section-title">El teu benestar és multidimensional: Serveis especialitzats</h2>
                <ul class="about-list">
                    <li><strong>Teràpia psicològica online</strong> (per a tot Catalunya i Espanya) i <strong>presencial</strong> (a Girona): Un espai segur i confidencial on treballem junts per superar l’ansietat, la depressió, TOC, dols, problemes d’autoestima o crisis vitals. Integro les eines més eficaces de diferents enfocaments (psicoanàlisi, cognitiu-conductual, humanista i ACT) per oferir-te un pla 100% personalitzat.</li>
                    <li><strong>Peritatge psicològic judicial:</strong> Si estàs immers en un procés legal relacionat amb família, custòdies o violència filioparental, elaboro informes pericials rigorosos que aporten claredat psicològica crucial per al teu cas.</li>
                    <li><strong>Mediació familiar:</strong> Per quan el conflicte familiar sembla insuperable. T’ajudo a trobar solucions en un marc de respecte i diàleg, evitant desgast emocional i econòmic.</li>
                </ul>

                <h2 class="about-section-title">El meu compromís amb tu: No només escoltar-te, sinó comprendre’t i actuar</h2>
                <ul class="about-list">
                    <li>Creo un espai segur on et sentiràs veritablement escoltat i comprès, sense judicis.</li>
                    <li>M’involucro amb autenticitat i proximitat, perquè la confiança és la base de tota transformació.</li>
                    <li>No em conformo amb alleujar el malestar. El meu objectiu és que aquesta crisi es converteixi en la teva gran oportunitat. Treballarem per obrir noves perspectives, generar canvis reals i dotar-te d’eines pràctiques que et permetin reconstruir una vida més conscient, lliure i plena.</li>
                </ul>

                <h2 class="about-section-title">Portant la psicologia més enllà de la consulta</h2>
                <p>La meva passió per ajudar traspassa les parets del meu despatx. Escric articles en mitjans especialitzats i creo contingut a les xarxes socials amb un estil directe i proper, perquè crec que el coneixement psicològic ha d’estar a l’abast de tothom. És la meva manera d’oferir-te recursos útils, fins i tot abans que decideixis començar el teu procés.</p>

                <div class="about-cta">
                    <h3>Estàs preparat per fer el primer pas cap al teu canvi?</h3>
                    <p>La teva valentia t’ha portat fins aquí. Ara, la decisió d’avançar està a les teves mans.</p>
                    <a href="contacta.php" class="btn btn-primary">Reserva la teva primera sessió</a>
                    <a href="contacta.php" class="btn btn-secondary">Contacta’m per a més informació</a>
                    <p class="about-note">Teràpia online disponible per a tot Catalunya i Espanya. Consulta presencial a Girona ciutat.</p>
                </div>
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
</body>
</html>