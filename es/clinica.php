<?php
// Inicialitzar sessió si no està iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Debug ABANS del processament
echo "<!-- DEBUG INDEX ABANS: GET lang: " . ($_GET['lang'] ?? 'no definit') . " -->";
echo "<!-- DEBUG INDEX ABANS: Session lang abans: " . ($_SESSION['language'] ?? 'no definit') . " -->";

// Forçar idioma català en aquesta pàgina
$_SESSION['language'] = 'es';
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
    <link rel="stylesheet" href="../css/clinica-v2.css">
            <!-- Estils exclusius a clinica.css -->
</head>
<body>
    <?php include '_includes/navigation.php'; ?>
    <!-- Secció HERO -->
    <script>
    // Sticky CTA: mostrar a mòbil al fer scroll cap amunt (o sempre visible si prefereixes)
    (function(){
        let lastScroll = window.scrollY;
        const sticky = document.querySelector('.cj-sticky-cta');
        if(!sticky) return;
        // Mostrar per defecte a mòbil
        const onScroll = () => {
            const current = window.scrollY;
            // si es fa scroll cap amunt, mostra; si cap avall, amaga lleument
            if(current < 120 || current < lastScroll){ sticky.style.transform = 'translateY(0)'; sticky.style.opacity = '1'; }
            else { sticky.style.transform = 'translateY(8px)'; sticky.style.opacity = '0.02'; }
            lastScroll = current;
        };
        window.addEventListener('scroll', onScroll);
        // Click handler: porta a la secció de contacte (si existeix) o obre telèfon
        sticky.addEventListener('click', function(e){
            const target = document.querySelector('#contacte, #contacto, a[href^="tel:"]');
            if(target){ e.preventDefault(); target.scrollIntoView({behavior:'smooth'}); }
        });
    })();
    </script>
    <section class="hero clinica-hero" id="clinica-hero">
        <div class="container hero-content">
            <h1>
                Transforma tu bienestar emocional
            </h1>
            <h2 class="hero-subtitle">Terapia y acompañamiento psicológico de alto impacto.</h2>
        </div>
    </section>    
    <main>
        <!-- Sección supera tus retos -->
        <section class="clinica-supera clinica-section">
            <h2 class="clinica-supera-title">Supera lo que te hace sufrir</h2>
            <p class="clinica-supera-intro">
                Hay momentos en que la vida pesa más de lo normal. Las preocupaciones se apilan, el malestar emocional te paraliza y sientes que has perdido las riendas de tu propia vida. No es un signo de debilidad, es una señal de que algo necesita atención.<br><br>
                En nuestra clínica, no te pondremos una etiqueta. Te ayudaremos a comprender lo que te pasa y te daremos las herramientas para superarlo. Deja de sobrevivir y comienza a vivir de nuevo.
            </p>
            <div class="clinica-slider">
                <div class="clinica-slider-track">
                    <div class="clinica-slide active">
                        <h3 class="clinica-repte-title">La ansiedad que te ahoga</h3>
                        <p class="clinica-repte-sentiment">Si te sientes... como si una alerta constante te siguiera a todas partes, con pensamientos negativos incontrolables, ataques de pánico, insomnio o inquietud.</p>
                        <p class="clinica-repte-solucio">Te podemos ayudar a... desconectar la alarma interna. Te enseñaremos técnicas prácticas para gestionar tus nervios, recuperar la calma y dejar que el miedo no dirija tus decisiones. Volverás a sentirte seguro.</p>
                    </div>
                    <div class="clinica-slide">
                        <h3 class="clinica-repte-title">El peso de la tristeza profunda (depresión)</h3>
                        <p class="clinica-repte-sentiment">Si te sientes... atrapado en un agujero sin energía, sin interés por las cosas que antes te gustaban, con un peso en el ánimo que no se va con el sueño.</p>
                        <p class="clinica-repte-solucio">Te podemos ayudar a... volver a encender tu chispa. Trabajaremos para sacarte de este vacío, recuperar tu motivación y encontrar un nuevo sentido de propósito y alegría en tu día a día.</p>
                    </div>
                    <div class="clinica-slide">
                        <h3 class="clinica-repte-title">Los pensamientos intrusivos que no callan (TOC)</h3>
                        <p class="clinica-repte-sentiment">Si te sientes... prisionero de rituales mentales o conductuales que crees que debes hacer para evitar que pase algo malo, pero que consumen tu tiempo y tu paz.</p>
                        <p class="clinica-repte-solucio">Te podemos ayudar a... romper el ciclo del TOC. Aprenderás a desafiar tus pensamientos obsesivos, a reducir las compulsiones y a recuperar el control de tu mente.</p>
                    </div>
                    <div class="clinica-slide">
                        <h3 class="clinica-repte-title">El dolor que te deja anclado</h3>
                        <p class="clinica-repte-sentiment">Si te sientes... desconectado después de una pérdida (de un ser querido, una relación, un trabajo...). Un dolor que no parece marchar y que te dificulta seguir adelante.</p>
                        <p class="clinica-repte-solucio">Te podemos ayudar a... honrar lo que perdiste sin que te detenga. Te acompañaremos en el proceso de duelo para poder elaborar el duelo, encontrar un nuevo significado y reinvertir en tu vida.</p>
                    </div>
                    <div class="clinica-slide">
                        <h3 class="clinica-repte-title">La falta de confianza en uno mismo (baja autoestima)</h3>
                        <p class="clinica-repte-sentiment">Si te sientes... tu peor crítico, siempre diciéndote que no es suficiente, que no lo harás bien, evitando retos por miedo a fracasar.</p>
                        <p class="clinica-repte-solucio">Te podemos ayudar a... silenciar tu crítico interno y convertirte en tu mayor aliado. Reconstruiremos tu autoimagen, descubriremos tus puntos fuertes y aprenderemos a tratarte con la misma amabilidad que tratarías a un buen amigo.</p>
                    </div>
                    <div class="clinica-slide">
                        <h3 class="clinica-repte-title">Las crisis que te hacen cuestionarlo todo (crisis vitales)</h3>
                        <p class="clinica-repte-sentiment">Si te sientes... perdido, sin rumbo, cuestionando tus decisiones, tu carrera o tus relaciones. Es como un terremoto que lo sacude todo.</p>
                        <p class="clinica-repte-solucio">Te podemos ayudar a... transformar la crisis en una oportunidad. Este no es el final, es un renacimiento. Te ayudaremos a encontrar un nuevo sentido, a redefinir tus metas y a salir de esta fase más fuerte y con más claridad que nunca.</p>
                    </div>
                </div>
            </div>
        </section>
        <!-- Fi clinica-supera -->
        <!-- frase motivadora -->
        <section class="quote-section">
            <div class="quote-content">
                <blockquote>
                    <p>No hay ningún premio por aguantar más que nadie. La verdadera fuerza radica en reconocer que necesitas ayuda y dar el paso para pedirla.</p>
                </blockquote>
            </div>
        </section>
        <!-- Fi frase motivadora -->

        <!-- Secció teràpia de parella i família -->
        <section class="clinica-parella pf-section v3" id="parella-familia" aria-labelledby="pf-title">
            <div class="container pf-inner">
                <!-- HERO (main emotional headline + CTA) -->
                <div class="pf-hero" role="region" aria-labelledby="pf-title">
                    <h2 id="pf-title" class="clinica-supera-title">Vuelve a conectar: reconstruye tu pareja o tu familia con herramientas reales</h2>
                    <p class="pf-hero-lead">Sea que quieran salvar lo que tienen o construir algo nuevo y más sólido, te ofrezco un espacio seguro y un método práctico para recuperar la conexión, el respeto y el amor.</p>
                </div>

                <!-- Tiles (4 summaries). Full texts kept as hidden details for SEO/accessibility -->
                <div class="pf-tiles" role="list">
                    <!-- Tile A: Acompanyament / Noves Parelles -->
                    <article class="pf-tile" role="listitem" aria-labelledby="pf-a-title">
                        <div class="pf-tile-head"><span class="pf-icon"><i class="fas fa-seedling" aria-hidden="true"></i></span><h3 id="pf-a-title">Acompañamiento para nuevas parejas</h3></div>
                        <p class="pf-tile-lead"><strong>Construye fundamentos sólidos desde el principio.</strong></p>
                        <button class="pf-toggle" aria-expanded="false" aria-controls="pf-a-detail">Leer más</button>
                        <div id="pf-a-detail" class="pf-detail" hidden>
                            <p><strong>Si os encontráis...</strong> comenzando una relación y queréis hacerlo bien desde el principio, o bien estáis reconstruyendo vuestra vida después de una separación y queréis evitar los errores del pasado.</p>
                            <p><strong>Os ayudaré a...</strong> aprender las herramientas de comunicación y gestión emocional que os permitirán crear una relación resiliente, honesta y profundamente satisfactoria desde el día cero.</p>
                        </div>
                    </article>

                    <!-- Tile B: Crisi de Parella (re-focus) -->
                    <article class="pf-tile" role="listitem" aria-labelledby="pf-b-title">
                        <div class="pf-tile-head"><span class="pf-icon"><i class="fas fa-heart-broken" aria-hidden="true"></i></span><h3 id="pf-b-title">Crisis de pareja</h3></div>
                        <p class="pf-tile-lead"><strong>Cuando la relación toca un punto crítico.</strong></p>
                        <button class="pf-toggle" aria-expanded="false" aria-controls="pf-b-detail">Leer más</button>
                        <div id="pf-b-detail" class="pf-detail" hidden>
                            <p><strong>Si os encontráis...</strong> en un momento donde las discusiones son frecuentes, la desconexión se intensifica o aparecen decisiones que dividen el futuro de la relación.</p>
                            <p><strong>Os ayudaré a...</strong> identificar los puntos de ruptura, restaurar la comunicación y hacer un plan claro (decidir si recuperar o transformar la relación) con herramientas terapéuticas prácticas y apoyo emocional.</p>
                        </div>
                    </article>

                    <!-- Tile C: Mediació Familiar -->
                    <article class="pf-tile" role="listitem" aria-labelledby="pf-c-title">
                        <div class="pf-tile-head"><span class="pf-icon"><i class="fas fa-handshake" aria-hidden="true"></i></span><h3 id="pf-c-title">Mediación familiar</h3></div>
                        <p class="pf-tile-lead"><strong>Soluciones justas sin juicio.</strong></p>
                        <button class="pf-toggle" aria-expanded="false" aria-controls="pf-c-detail">Leer más</button>
                        <div id="pf-c-detail" class="pf-detail" hidden>
                            <p><strong>Si sentís...</strong> que los conflictos familiares han creado una fractura y la comunicación está rota.</p>
                            <p><strong>Os ayudaré a...</strong> encontrar acuerdos justos y voluntarios en un marco de respeto y diálogo, protegiendo relaciones y ahorrando desgaste económico y emocional.</p>
                        </div>
                    </article>

                    <!-- Tile D: Suport en Crisi Familiars -->
                    <article class="pf-tile" role="listitem" aria-labelledby="pf-d-title">
                        <div class="pf-tile-head"><span class="pf-icon"><i class="fas fa-hands-helping" aria-hidden="true"></i></span><h3 id="pf-d-title">Soporte en crisis familiares</h3></div>
                        <p class="pf-tile-lead"><strong>Navegar dentro de la tormenta y salir más unidos.</strong></p>
                        <button class="pf-toggle" aria-expanded="false" aria-controls="pf-d-detail">Leer más</button>
                        <div id="pf-d-detail" class="pf-detail" hidden>
                            <p><strong>Si vuestra família...</strong> está sufriendo una crisis por comportamiento de un adolescente, una pérdida o un evento que sacude vuestros cimientos.</p>
                            <p><strong>Os ayudaré a...</strong> comprender, reforzar vínculos y convertir la crisis en una oportunidad para crecer.</p>
                        </div>
                    </article>
                </div>

                <!-- Full Method text kept visible below hero for SEO and clarity -->
                <section class="pf-method" aria-labelledby="pf-method-title">
                    <h3 id="pf-method-title">Mi método: Más allá de "hablar de los problemas"</h3>
                    <p>No me limitaré a escucharte. Mi enfoque es práctico y proactivo:</p>
                    <ul class="pf-method-list">
                        <li><strong>Análisis del ciclo del conflicto:</strong> Identificamos juntos el patrón que te mantiene atrapado.</li>
                        <li><strong>Herramientas de comunicación efectiva:</strong> Te enseño a expresar necesidades y a escuchar con empatía.</li>
                        <li><strong>Gestión emocional inteligente:</strong> Aprenderás a regular las emociones para evitar que controlen la relación.</li>
                        <li><strong>Plan de acción concreto:</strong> Trabajaremos con objetivos claros y pasos alcanzables para ver resultados reales.</li>
                    </ul>
                    <p class="pf-method-goal"><strong>Mi objetivo es uno:</strong> dotaros de las habilidades para ser el equipo que siempre habéis querido ser.</p>
                </section>
            </div>
        </section>
        <!-- Fi secció teràpia de parella i família -->
        <!-- Frase motivadora 2 per famílies/parelles -->
        <section class="quote-section">
            <div class="quote-content">
                <blockquote>
                    <p>La comunicación es la clave para una relación sana. No tengas miedo de compartir tus sentimientos y necesidades. </p>
                </blockquote>
            </div>
        </section>
        <!-- Fi frase motivadora -->

        <!-- Secció psicologia judicial i peritatge psicològic (redissenyada v2) -->
        <section class="clinica-judicial v2">
            <div class="cj-inner">
                <h2 id="cj-title" class="clinica-supera-title">Psicología judicial: La prueba pericial que puede decidir tu caso</h2>
                <div class="cj-intro">
                    <h3 class="cj-subtitle">Cuando el conflicto legal demanda un informe psicológico sólido</h3>
                    <p>En un proceso judicial, las emociones y los hechos a menudo se entrelazan. Un informe psicológico no es solo un documento; es la herramienta que traduce tu estado mental, tus capacidades y la realidad familiar en argumentos sólidos e incontestables para el juez.</p>
                    <p>No se trata solo de quién tiene la razón, sino de quién puede demostrarlo con rigor y credibilidad. Mi experiencia como perito psicóloga colegiada es tu mejor aliada para lograrlo.</p>
                </div>
                <div class="cj-cards">
                    <article class="cj-card" data-anim>
                        <div class="cj-card-head"><span class="cj-icon" aria-hidden="true"><i class="fas fa-users" aria-hidden="true"></i></span><h3>Informes para custodia y régimen de visitas</h3></div>
                        <div class="cj-card-body">
                            <p class="cj-summary"><strong>Si te encuentras en...</strong> un proceso de separación o divorcio conflictivo, donde se discute con quién deben vivir los hijos o cómo se deben hacer las visitas.</p>
                            <div class="cj-details" id="detail-1" hidden>
                                <p><strong>Mi informe demostrará</strong> la idoneidad parental de cada progenitor. Evaluaré el vínculo afectivo, las habilidades parentales y las necesidades de los menores para proponer el mejor régimen de convivencia para ellos, con su bienestar como prioridad absoluta. Protege a tus hijos con un informe que hable por ellos.</p>
                            </div>
                            <button class="cj-toggle" aria-expanded="false" aria-controls="detail-1">Leer más</button>
                        </div>
                    </article>
                    <article class="cj-card" data-anim>
                        <div class="cj-card-head"><span class="cj-icon" aria-hidden="true"><i class="fas fa-exclamation-triangle" aria-hidden="true"></i></span><h3>Evaluación en casos de violencia filio-parental (VFP)</h3></div>
                        <div class="cj-card-body">
                            <p class="cj-summary"><strong>Si te encuentras en...</strong> una situación donde los padres son víctimas de agresiones, amenazas o faltas de respeto por parte de un hijo, y sienten que han perdido el control y la autoridad.</p>
                            <div class="cj-details" id="detail-2" hidden>
                                <p><strong>Mi informe identifica y evalúa</strong> las causas psicológicas que alimentan la violencia. No solo documento los hechos, sino que trazo un plan de intervención para detener el ciclo y reconstruir la relación familiar desde una nueva base, siempre que sea posible.</p>
                            </div>
                            <button class="cj-toggle" aria-expanded="false" aria-controls="detail-2">Leer más</button>
                        </div>
                    </article>
                    <article class="cj-card" data-anim>
                        <div class="cj-card-head"><span class="cj-icon" aria-hidden="true"><i class="fas fa-heartbeat" aria-hidden="true"></i></span><h3>Evaluación del daño psicológico</h3></div>
                        <div class="cj-card-body">
                            <p class="cj-summary"><strong>Si te encuentras en...</strong> un proceso para reclamar una indemnización después de sufrir un accidente, mobbing laboral (acoso) o cualquier situación traumática que te haya provocado un sufrimiento psicológico verificable.</p>
                            <div class="cj-details" id="detail-3" hidden>
                                <p><strong>Mi informe cuantifica</strong> el impacto real de lo que has vivido. Conecto el hecho causante con tus síntomas (ansiedad, insomnio, estrés postraumático) para determinar el grado de incapacidad o daño y sustentar tu reclamación económica. Que te compensen también por tu dolor emocional.</p>
                            </div>
                            <button class="cj-toggle" aria-expanded="false" aria-controls="detail-3">Leer más</button>
                        </div>
                    </article>
                    <article class="cj-card" data-anim>
                        <div class="cj-card-head"><span class="cj-icon" aria-hidden="true"><i class="fas fa-brain" aria-hidden="true"></i></span><h3>Evaluación de la capacidad cognitiva y volitiva</h3></div>
                        <div class="cj-card-body">
                            <p class="cj-summary"><strong>Si te encuentras en...</strong> un proceso donde se cuestiona la capacidad de una persona para tomar decisiones (testar, administrar sus bienes, consentir...).</p>
                            <div class="cj-details" id="detail-4" hidden>
                                <p><strong>Mi informe determina</strong> el grado de discernimiento de la persona. Evalúo de forma rigurosa si existe algún trastorno que le impida entender las consecuencias de sus actos, ofreciendo al juez una fotografía clara de su capacidad mental.</p>
                            </div>
                            <button class="cj-toggle" aria-expanded="false" aria-controls="detail-4">Leer más</button>
                        </div>
                    </article>
                </div>
                <div class="cj-method-cta">
                    <h2 class="cj-method-title">Mi método: rigor científico y claridad legal</h2>
                    <ul class="cj-method-list">
                        <li>Entrevista en profundidad y evaluación psicológica con las pruebas estandarizadas más reconocidas.</li>
                        <li>Análisis de toda la documentación del caso (informes médicos, informes sociales, etc.).</li>
                        <li>Redacción clara, concisa y contundente, entendible para los operadores jurídicos.</li>
                        <li>Asistencia opcional a la Vista Oral para defender y explicar mis conclusiones con convicción y solvencia ante el tribunal.</li>
                    </ul>
                    <p class="cj-objectiu">Mi objetivo es un: que el juez comprenda la realidad psicológica del caso sin ninguna duda.</p>
                    <blockquote class="cj-quote">En un juicio, las palabras sin un fundamento sólido se las lleva el viento. Mi informe es el fundamento que tu causa necesita.</blockquote>
                </div>
            </div>
        </section>
        <!-- Fi secció psicologia judicial i peritatge psicològic (v2) -->
        <!-- CTA final -->
        <section class="clinica-cta-final">
            <div class="clinica-cta-content spectacular">
                <div class="clinica-cta-copy">
                    <h2 class="clinica-cta-title">¿Estás listo para transformar tu vida?</h2>
                    <p class="clinica-cta-text">No dejes que el malestar emocional te limite más tiempo. Contacta conmigo y definamos un plan práctico y sensible para tu familia o pareja.</p>
                </div>
                <div class="clinica-cta-actions">
                    <a href="/<?php echo getCurrentLanguage(); ?>/contacte.php" class="clinica-cta-button">Contacta ahora <span class="chev" aria-hidden="true">›</span></a>
                </div>
            </div>
        </section>
        <!-- Fi CTA final -->
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
        <script>
        // Reveal simple amb IntersectionObserver per a les cards de psicologia judicial v2
        document.addEventListener('DOMContentLoaded', function(){
            const items = document.querySelectorAll('[data-anim]');
            if('IntersectionObserver' in window){
                const io = new IntersectionObserver((entries)=>{
                    entries.forEach(e=>{
                        if(e.isIntersecting){ e.target.classList.add('in-view'); io.unobserve(e.target); }
                    });
                }, {threshold:0.12});
                items.forEach(i=>io.observe(i));
            } else { items.forEach(i=>i.classList.add('in-view')); }
        });
        </script>
            <script>
            // Toggle 'Veure més' per mostrar detalls i reduir densitat
            document.addEventListener('click', function(e){
                if(e.target && e.target.classList && e.target.classList.contains('cj-toggle')){
                    const btn = e.target;
                    const id = btn.getAttribute('aria-controls');
                    const details = document.getElementById(id);
                    if(!details) return;
                    const expanded = btn.getAttribute('aria-expanded') === 'true';
                    if(expanded){
                        details.hidden = true;
                        btn.setAttribute('aria-expanded','false');
                        btn.textContent = 'Veure més';
                    } else {
                        details.hidden = false;
                        btn.setAttribute('aria-expanded','true');
                        btn.textContent = 'Veure menys';
                    }
                }
                // Delegated toggles for Parella tiles
                if(e.target && e.target.classList && e.target.classList.contains('pf-toggle')){
                    const btn = e.target;
                    const id = btn.getAttribute('aria-controls');
                    const details = document.getElementById(id);
                    if(!details) return;
                    const expanded = btn.getAttribute('aria-expanded') === 'true';
                    if(expanded){
                        details.hidden = true;
                        btn.setAttribute('aria-expanded','false');
                        btn.textContent = 'Més info';
                    } else {
                        details.hidden = false;
                        btn.setAttribute('aria-expanded','true');
                        btn.textContent = 'Menys info';
                    }
                }
            });
            </script>
</body>
</html>
