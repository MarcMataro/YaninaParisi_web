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
    <title><?php echo t('nav_services'); ?> | Yanina Parisi</title>
    <meta name="description" content="<?php echo t('meta_description'); ?>">
    <link rel="stylesheet" href="../css/estils.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@7.0.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Libre+Baskerville:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/clinica.css">
            <link rel="stylesheet" href="css/parallax-clinica.css">
            <!-- Estils exclusius a clinica.css -->
</head>
<body>
    <?php include '_includes/navigation.php'; ?>
    <!-- Secció HERO -->
    <section class="hero clinica-hero" id="clinica-hero">
        <div class="container hero-content">
            <h1>
                Transforma el teu benestar emocional
            </h1>
            <h2 class="hero-subtitle">Teràpia i acompanyament psicològic d'alt impacte.</h2>
        </div>
    </section>    
    <main>
        <!-- Secció supera els teus reptes -->
        <section class="clinica-supera clinica-section">
            <h2 class="clinica-supera-title">Supera el que et fa patir</h2>
            <p class="clinica-supera-intro">
                Hi ha moments en què la vida pesa més del normal. Les preocupacions s'apilen, el malestar emocional et paralitza i sents que has perdut les regnes de la teva pròpia vida. No és un signe de feblesa, és un senyal que alguna cosa necessita atenció.<br><br>
                A la nostra clínica, no et posarem una etiqueta. T’ajudarem a comprendre el que et passa i et donarem les eines per superar-ho. Deixa de sobreviure i comença a viure de nou.
            </p>
            <div class="clinica-slider">
                <div class="clinica-slider-track">
                    <div class="clinica-slide active">
                        <h3 class="clinica-repte-title">L'Ansietat que t'Agafa l'Aire</h3>
                        <p class="clinica-repte-sentiment">Si et sents... Com si una alerta constant et seguís a tot arreu, amb pensaments negatius incontrolables, atacs de pànic, insomni o inquietud.</p>
                        <p class="clinica-repte-solucio">Et podem ajudar a... Desconnectar l'alarma interna. T'ensenyarem tècniques pràctiques per gestionar els teus nervis, recuperar la calma i deixar que la por no dirigeixi les teves decisions. Tornaràs a sentir-te segur.</p>
                    </div>
                    <div class="clinica-slide">
                        <h3 class="clinica-repte-title">El Pes de la Tristesa Profunda (Depressió)</h3>
                        <p class="clinica-repte-sentiment">Si et sents... Atrapat en un forat sense energia, sense interès per les coses que abans t'agradaven, amb un pes a l'ànim que no es va amb el son.</p>
                        <p class="clinica-repte-solucio">Et podem ajudar a... Tornar a encendre la teva espurna. Treballarem per treure't d'aquest buit, recuperar la teva motivació i trobar un nou sentit de propòsit i alegria en el teu dia a dia.</p>
                    </div>
                    <div class="clinica-slide">
                        <h3 class="clinica-repte-title">Els Pensaments Intrusius que no es Callen (TOC)</h3>
                        <p class="clinica-repte-sentiment">Si et sents... Presoner de rituals mentals o conductuals que creus que has de fer per evitar que passi alguna cosa dolenta, però que consumeixen el teu temps i la teva pau.</p>
                        <p class="clinica-repte-solucio">Et podem ajudar a... Trencar el cicle del TOC. Aprendràs a desafiar els teus pensaments obsessius, a reduir les compulsions i a recuperar el control de la teva ment.</p>
                    </div>
                    <div class="clinica-slide">
                        <h3 class="clinica-repte-title">El Duel que et Deixa Ancordat</h3>
                        <p class="clinica-repte-sentiment">Si et sents... Desconnectat després d'una pèrdua (d'un ésser estimat, una relació, una feina...). Un dolor que no sembla marxar i que et dificulta seguir endavant.</p>
                        <p class="clinica-repte-solucio">Et podem ajudar a... Honrar el que vas perdre sense que et detingui. T’acompanyarem en el procés de dolor per poder elaborar el dol, trobar un nou significat i reinvertir en la teva vida.</p>
                    </div>
                    <div class="clinica-slide">
                        <h3 class="clinica-repte-title">La Manca de Confiança en un Mateix (Baixa Autoestima)</h3>
                        <p class="clinica-repte-sentiment">Si et sents... El teu pitjor crític, sempre dient-te que no n'hi ha prou, que no ho faràs bé, evitant reptes per por a fracassar.</p>
                        <p class="clinica-repte-solucio">Et podem ajudar a... Silenciar el teu crític intern i convertir-te en el teu major aliat. Reconstruirem la teva autoimatge, descobrirem els teus punts forts i aprendrem a tractar-te amb la mateixa amabilitat que tractaries a un bon amic.</p>
                    </div>
                    <div class="clinica-slide">
                        <h3 class="clinica-repte-title">Les Crisis que et Fan Qüestionar-ho Tot (Crisis Vitale)</h3>
                        <p class="clinica-repte-sentiment">Si et sents... Perdut, sense rumb, qüestionant les teves decisions, la teva carrera o les teves relacions. És com un terratrèmol que ho sacseja tot.</p>
                        <p class="clinica-repte-solucio">Et podem ajudar a... Transformar la crisi en una oportunitat. Aquest no és el final, és un renaixement. T’ajudarem a trobar un nou sentit, a redefinir les teves metes i a sortir d'aquesta fase més fort i amb més claredat que mai.</p>
                    </div>
                </div>
            </div>
            
        </section>
        <!-- Fi clinica-supera -->
        <!-- frase motivadora -->
        <section class="quote-section">
            <div class="quote-content">
                <blockquote>
                    <p>No hi ha cap premi per aguantar més que ningú. La veritable força rau en reconèixer que necessites una mà i donar el pas per demanar-la.</p>
                </blockquote>
            </div>
        </section>
        <!-- Fi frase motivadora -->

        <!-- Secció teràpia de parella i família -->
        <section>

        </section>
        <!-- Fi secció teràpia de parella i família -->
        <!-- Frase motivadora 2 per famílies/parelles -->
        <section class="quote-section">
            <div class="quote-content">
                <blockquote>
                    <p>La comunicació és la clau per a una relació sana. No tinguis por de compartir els teus sentiments i necessitats. </p>
                </blockquote>
            </div>
        </section>
        <!-- Fi frase motivadora -->

        <!-- Secció psicologia judicial i peritatge psicològic -->
        <section class="clinica-judicial clinica-section">
            <div class="container">
                <h2 class="clinica-supera-title">Psicologia judicial: La prova pericial que pot decidir el teu cas</h2>
                <div class="clinica-judicial-intro">
                    <h3 class="clinica-judicial-subtitle">Quan el conflicte legal demana un informe psicològic sòlid</h3>
                    <p>En un procés judicial, les emocions i els fets sovint s'emboliquen. Un informe psicològic no és només un document; és l'eina que traduïx el teu estat mental, les teves capacitats i la realitat familiar en arguments sòlids i incontestables per al jutge.</p>
                    <p>No es tracta només de qui té la raó, sinó de qui pot demostrar-ho amb rigor i credibilitat. La nostra experiència com a Peritos Psicólogos Col·legiats és el teu millor aliat per a aconseguir-ho.</p>
                </div>
                <div class="clinica-judicial-serveis">
                    <ul class="clinica-judicial-serveis-list">
                        <li>
                            <h3>Informes per a Custòdia i Règim de Visites</h3>
                            <p><strong>Si et trobes en...</strong> Un procés de separació o divorci conflictiu, on es discuteix amb qui han de viure els fills o com s'han de fer les visites.</p>
                            <p><strong>El nostre informe demostra...</strong> L'idoneitat parental de cada progenitor. Avaluem el vincle afectiu, les habilitats parentals i les necessitats dels menors per proposar el millor règim de convivència per a ells, amb el seu benestar com a prioritat absoluta. Protegeix els teus fills amb un informe que parli per ells.</p>
                        </li>
                        <li>
                            <h3>Avaluació en Casos de Violència Filio-parental (VFP)</h3>
                            <p><strong>Si et trobes en...</strong> Una situació on els pares sou víctimes d'agressions, amenaces o mancances de respecte per part d'un fill, i sentiu que heu perdut el control i l'autoritat.</p>
                            <p><strong>El nostre informe identifica i avalua...</strong> Les causes psicològiques que alimenten la violència. No només documentem els fets, sinó que tracem un pla d'intervenció per a aturar el cicle i reconstruir la relació familiar des de una nova base, sempre que sigui possible.</p>
                        </li>
                        <li>
                            <h3>Avaluació del Dany Psicològic</h3>
                            <p><strong>Si et trobes en...</strong> Un procés per a reclamar una indemnització després de patir un accident, mobbing laboral (assetjament) o qualsevol situació traumàtica que t'hagi provocat un patiment psicològic verificable.</p>
                            <p><strong>El nostre informe quantifica...</strong> L'impacte real del que has viscut. Connectem el fet causant amb els teus símptomes (ansietat, insomni, estrès postraumàtic) per a determinar el grau d'incapacitat o dany i sustentar la teva reclamació econòmica. Que et compensin també pel teu dolor emocional.</p>
                        </li>
                        <li>
                            <h3>Avaluació de la Capacitat Cognitiva i Volitiva</h3>
                            <p><strong>Si et trobes en...</strong> Un procés on es qüestiona la capacitat d'una persona per a prendre decisions (testar, administrar els seus béns, consentir...).</p>
                            <p><strong>El nostre informe determina...</strong> El grau de discerniment de la persona. Avaluem de forma rigorosa si existeix algun trastorn que li impedeixi entendre les consequències dels seus actes, oferint al jutge una fotografia clara de la seva capacitat mental.</p>
                        </li>
                    </ul>
                </div>
                <div class="clinica-judicial-metode">
                    <h2 class="clinica-judicial-metode-title">El Nostre Mètode: Rigor Científic, Claritat Legal</h2>
                    <ul>
                        <li>Entrevista en profunditat i avaluació psicològica amb les proves estandarditzades més reconegudes.</li>
                        <li>Anàlisi de tota la documentació del cas (atgets mèdics, informes socials, etc.).</li>
                        <li>Redacció clara, concisa i contundent, entenedora per als operadors jurídics.</li>
                        <li>Assistència opcional a la Vista Oral per a defensar i explicar les nostres conclusions amb convicció i solvència davant el tribunal.</li>
                    </ul>
                    <p class="clinica-judicial-objetiu">El nostre objectiu és un: que el jutge comprengui la realitat psicològica del cas sense cap mena de dubte.</p>
                </div>
                <div class="clinica-judicial-cta">
                    <blockquote class="clinica-judicial-quote">En un judici, les paraules sense un fonament sòlid es les duu el vent. El nostre informe és el fonament que la teva causa necessita.</blockquote>
                    <div class="clinica-judicial-assessorament">
                        <span class="clinica-judicial-icon" style="font-size:2rem;">⚖️</span> Assessorament Previ Sense Compromís<br>
                        <span class="clinica-judicial-desc">Entenem la delicadesa de la teva situació. Abans de res, et brindem una avaluació inicial per analitzar el teu cas i explicar-te com podem ajudar-te específicament.</span>
                    </div>
                    <a href="#" class="clinica-supera-btn clinica-judicial-btn">Defensa la Teva Posició amb un Informe Psicològic Sòlid</a>
                    <div class="clinica-judicial-btns">
                        <a href="#" class="btn btn-secondary">Sol·licita una Avaluació del Teu Cas</a>
                        <a href="#" class="btn btn-secondary">Parlem Sense Compromís</a>
                    </div>
                </div>
            </div>
        </section>
        <!-- Fi secció psicologia judicial i peritatge psicològic -->
    </main>
<script>
// Slider logic per a clinica-reptes
document.addEventListener('DOMContentLoaded', function() {
    const slides = document.querySelectorAll('.clinica-slide');
    let current = 0;
    let prev = 0;
    function updateSuperaVisibility() {
        slides.forEach((slide, i) => {
            if (i === current) {
                slide.style.display = 'flex';
                slide.classList.add('active');
            } else {
                slide.style.display = 'none';
                slide.classList.remove('active', 'slide-in-right', 'slide-in-left', 'slide-out-left', 'slide-out-right');
            }
        });
    }
    function animateSlide(next, direction) {
        slides.forEach((slide, i) => {
            slide.classList.remove('active', 'slide-in-right', 'slide-in-left', 'slide-out-left', 'slide-out-right');
            slide.style.display = 'none';
        });
        slides[prev].classList.add(direction === 'right' ? 'slide-out-left' : 'slide-out-right');
        slides[next].classList.add(direction === 'right' ? 'slide-in-right' : 'slide-in-left', 'active');
        slides[next].style.display = 'flex';
        setTimeout(() => {
            slides[prev].classList.remove('slide-out-left', 'slide-out-right');
            slides[prev].style.display = 'none';
        }, 600);
    }
    function nextSlide() {
        prev = current;
        current = (current + 1) % slides.length;
        animateSlide(current, 'right');
    }
    function showFirstSupera() {
        current = 0;
        updateSuperaVisibility();
    }
    showFirstSupera();
    setInterval(nextSlide, 6000);
});
</script>
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
