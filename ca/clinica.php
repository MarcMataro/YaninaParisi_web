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
    <?php
    // Preparar SEO OnPage similar a la pàgina home
    $base_url = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'];
    require_once __DIR__ . '/../classes/seo_onpage.php';

    $pagina_seo = null;
    $seoTitle = null;

    // 1) intentar carregar per tipus 'clinica' (si existeix una pàgina configurada)
    try {
        $items = SEO_OnPage::llistarPaginesActives('clinica');
        if (!empty($items) && isset($items[0]) && $items[0] instanceof SEO_OnPage) {
            $pagina_seo = $items[0];
            $seoTitle = $pagina_seo->getTitle('ca') ?: null;
        }
    } catch (Exception $e) {
        // ignore and fallback
    }

    // 2) fallback: intentar carregar per URL relativa (varis intents)
    if (!$seoTitle) {
        $tries = ['/clinica.php','/clinica','/ca/clinica.php','/ca/clinica'];
        foreach ($tries as $r) {
            try {
                $tmp = SEO_OnPage::carregarPerUrl($r, 'ca');
                if ($tmp instanceof SEO_OnPage) { $pagina_seo = $tmp; $seoTitle = $pagina_seo->getTitle('ca') ?: null; break; }
            } catch (Exception $e) { }
        }
    }

    // 3) fallback general
    if (!$seoTitle) {
        $seoTitle = 'Serveis - Yanina Parisi - Psicòloga';
    }

    // meta description
    $seoDescription = null;
    if (isset($pagina_seo) && $pagina_seo instanceof SEO_OnPage) {
        $seoDescription = $pagina_seo->getMetaDescription('ca') ?: null;
    }
    if (!$seoDescription) $seoDescription = t('meta_description');

    // canonical
    $canonical = null;
    if (isset($pagina_seo) && $pagina_seo instanceof SEO_OnPage) {
        $canonical = $pagina_seo->getCanonicalUrl('ca');
    }
    if (!$canonical) $canonical = $base_url . '/ca/clinica.php';
    ?>

    <title><?php echo htmlspecialchars($seoTitle); ?></title>
    <meta name="description" content="<?php echo htmlspecialchars($seoDescription); ?>">
    <meta name="keywords" content="<?php echo t('meta_keywords'); ?>">
    <meta name="author" content="Yanina Parisi">
    <meta name="robots" content="index, follow">
    <meta name="theme-color" content="#aa9e6b">
    <link rel="canonical" href="<?php echo htmlspecialchars($canonical); ?>">
    <link rel="stylesheet" href="../css/estils.css">
    <link rel="icon" type="image/png" sizes="32x32" href="../img/Logo32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="../img/Logo16.png">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@7.0.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Libre+Baskerville:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/clinica.css">
    <link rel="stylesheet" href="../css/clinica-v2.css">
            <!-- Estils exclusius a clinica.css -->
    <?php
    // Open Graph / Twitter tags (mirant a la implementació de home.php)
    $og_title = isset($pagina_seo) && $pagina_seo instanceof SEO_OnPage ? $pagina_seo->getOgTitle('ca') : $seoTitle;
    $og_description = isset($pagina_seo) && $pagina_seo instanceof SEO_OnPage ? $pagina_seo->getOgDescription('ca') : $seoDescription;
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
    <meta property="og:type" content="<?php echo (isset($pagina_seo) ? ($pagina_seo->getTipoPagina() === 'articulo' ? 'article' : 'website') : 'website'); ?>">
    <meta property="og:url" content="<?php echo $og_url; ?>">
    <meta property="og:title" content="<?php echo htmlspecialchars($og_title); ?>">
    <meta property="og:description" content="<?php echo htmlspecialchars($og_description); ?>">
    <meta property="og:image" content="<?php echo htmlspecialchars($og_image); ?>">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:site_name" content="<?php echo htmlspecialchars(t('meta_og_site_name')); ?>">
    <meta property="og:locale" content="ca_ES">

    <?php
    $tw_title = isset($pagina_seo) && $pagina_seo instanceof SEO_OnPage ? $pagina_seo->getTwitterTitle('ca') : $seoTitle;
    $tw_description = isset($pagina_seo) && $pagina_seo instanceof SEO_OnPage ? $pagina_seo->getTwitterDescription('ca') : $seoDescription;
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

    <!-- Schema Markup JSON-LD (Psychologist / LocalBusiness) -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "Psychologist",
        "name": "Yanina Parisi",
        "description": "<?php echo htmlspecialchars($seoDescription); ?>",
        "url": "<?php echo 'https://' . $_SERVER['HTTP_HOST']; ?>",
        "telephone": "+34-XXX-XXX-XXX",
        "email": "info@yaninaparisi.com",
        "image": "<?php echo 'https://' . $_SERVER['HTTP_HOST']; ?>/img/img_2282.jpeg",
        "priceRange": "€€",
        "address": {
            "@type": "PostalAddress",
            "addressLocality": "Girona",
            "addressRegion": "Catalunya",
            "addressCountry": "ES"
        },
        "geo": {
            "@type": "GeoCoordinates",
            "latitude": "41.9794",
            "longitude": "2.8214"
        },
        "openingHours": "Mo-Fr 09:00-19:00",
        "serviceArea": {
            "@type": "Country",
            "name": "España"
        }
    }
    </script>
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
        <img src="../img/IMG_2282.jpeg" alt="" class="hero-img" aria-hidden="true">
        <div class="container hero-content">
            <h1>
                <?php echo htmlspecialchars(isset($pagina_seo) && $pagina_seo instanceof SEO_OnPage ? $pagina_seo->getH1('ca') : 'Transforma el teu benestar emocional'); ?>
            </h1>
            <h2 class="hero-subtitle">Teràpia i acompanyament psicològic d'alt impacte.</h2>
        </div>
    </section>    
    <main>
        <?php
            // Breadcrumbs: Home > Clínica (CAT)
            if (function_exists('render_breadcrumbs')) {
                render_breadcrumbs([
                    ['label' => t('nav_home'), 'url' => 'home.php'],
                    ['label' => t('nav_services')]
                ]);
            }
        ?>
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
                        <h3 class="clinica-repte-title">L'Ansietat que t'ofega</h3>
                        <p class="clinica-repte-sentiment">Si et sents... com si una alerta constant et seguís a tot arreu, amb pensaments negatius incontrolables, atacs de pànic, insomni o inquietud.</p>
                        <p class="clinica-repte-solucio">Et podem ajudar a... desconnectar l'alarma interna. T'ensenyarem tècniques pràctiques per gestionar els teus nervis, recuperar la calma i deixar que la por no dirigeixi les teves decisions. Tornaràs a sentir-te segur.</p>
                    </div>
                    <div class="clinica-slide">
                        <h3 class="clinica-repte-title">El pes de la tristesa profunda (depressió)</h3>
                        <p class="clinica-repte-sentiment">Si et sents... atrapat en un forat sense energia, sense interès per les coses que abans t'agradaven, amb un pes a l'ànim que no es va amb el son.</p>
                        <p class="clinica-repte-solucio">Et podem ajudar a... tornar a encendre la teva espurna. Treballarem per treure't d'aquest buit, recuperar la teva motivació i trobar un nou sentit de propòsit i alegria en el teu dia a dia.</p>
                    </div>
                    <div class="clinica-slide">
                        <h3 class="clinica-repte-title">Els pensaments intrusius que no callen (TOC)</h3>
                        <p class="clinica-repte-sentiment">Si et sents... presoner de rituals mentals o conductuals que creus que has de fer per evitar que passi alguna cosa dolenta, però que consumeixen el teu temps i la teva pau.</p>
                        <p class="clinica-repte-solucio">Et podem ajudar a... trencar el cicle del TOC. Aprendràs a desafiar els teus pensaments obsessius, a reduir les compulsions i a recuperar el control de la teva ment.</p>
                    </div>
                    <div class="clinica-slide">
                        <h3 class="clinica-repte-title">El dol que et deixa ancorat</h3>
                        <p class="clinica-repte-sentiment">Si et sents... desconnectat després d'una pèrdua (d'un ésser estimat, una relació, una feina...). Un dolor que no sembla marxar i que et dificulta seguir endavant.</p>
                        <p class="clinica-repte-solucio">Et podem ajudar a... honrar el que vas perdre sense que et detingui. T’acompanyarem en el procés de dolor per poder elaborar el dol, trobar un nou significat i reinvertir en la teva vida.</p>
                    </div>
                    <div class="clinica-slide">
                        <h3 class="clinica-repte-title">La manca de confiança en un mateix (baixa autoestima)</h3>
                        <p class="clinica-repte-sentiment">Si et sents... el teu pitjor crític, sempre dient-te que no n'hi ha prou, que no ho faràs bé, evitant reptes per por a fracassar.</p>
                        <p class="clinica-repte-solucio">Et podem ajudar a... silenciar el teu crític intern i convertir-te en el teu major aliat. Reconstruirem la teva autoimatge, descobrirem els teus punts forts i aprendrem a tractar-te amb la mateixa amabilitat que tractaries a un bon amic.</p>
                    </div>
                    <div class="clinica-slide">
                        <h3 class="clinica-repte-title">Les crisis que et fan qüestionar-ho tot (crisis vitals)</h3>
                        <p class="clinica-repte-sentiment">Si et sents... perdut, sense rumb, qüestionant les teves decisions, la teva carrera o les teves relacions. És com un terratrèmol que ho sacseja tot.</p>
                        <p class="clinica-repte-solucio">Et podem ajudar a... transformar la crisi en una oportunitat. Aquest no és el final, és un renaixement. T’ajudarem a trobar un nou sentit, a redefinir les teves metes i a sortir d'aquesta fase més fort i amb més claredat que mai.</p>
                    </div>
                </div>
            </div>
        </section>
        <!-- Fi clinica-supera -->
        <!-- frase motivadora -->
        <section class="quote-section">
            <div class="quote-content">
                <blockquote>
                    <p>No hi ha cap premi per aguantar més que ningú. La veritable força rau en reconèixer que necessites ajuda i donar el pas per demanar-la.</p>
                </blockquote>
            </div>
        </section>
        <!-- Fi frase motivadora -->

        <!-- Secció teràpia de parella i família -->
        <section class="clinica-parella pf-section v3" id="parella-familia" aria-labelledby="pf-title">
            <div class="container pf-inner">
                <!-- HERO (main emotional headline + CTA) -->
                <div class="pf-hero" role="region" aria-labelledby="pf-title">
                    <h2 id="pf-title" class="clinica-supera-title">Torna a connectar: reconstrueix la teva parella o la teva família amb eines reals</h2>
                    <p class="pf-hero-lead">Sigui que vulgueu salvar el que teniu o construir alguna cosa nova i més sòlida, t'ofereixo un espai segur i un mètode pràctic per a recuperar la connexió, el respecte i l'amor.</p>
                </div>

                <!-- Tiles (4 summaries). Full texts kept as hidden details for SEO/accessibility -->
                <div class="pf-tiles" role="list">
                    <!-- Tile A: Acompanyament / Noves Parelles -->
                    <article class="pf-tile" role="listitem" aria-labelledby="pf-a-title">
                        <div class="pf-tile-head"><span class="pf-icon"><i class="fas fa-seedling" aria-hidden="true"></i></span><h3 id="pf-a-title">Acompanyament per a noves parelles</h3></div>
                        <p class="pf-tile-lead"><strong>Construeix fonaments sòlids des del principi.</strong></p>
                        <button class="pf-toggle" aria-expanded="false" aria-controls="pf-a-detail">Llegir més</button>
                        <div id="pf-a-detail" class="pf-detail" hidden>
                            <p><strong>Si us trobeu...</strong> començant una relació i voleu fer-ho bé des del principi, o bé esteu reconstruint la vostra vida després d'una separació i voleu evitar els errors del passat.</p>
                            <p><strong>Us ajudaré a...</strong> aprendre les eines de comunicació i gestió emocional que us permetran crear una relació resilient, honesta i profundament satisfactòria des del dia zero.</p>
                        </div>
                    </article>

                    <!-- Tile B: Crisi de Parella (re-focus) -->
                    <article class="pf-tile" role="listitem" aria-labelledby="pf-b-title">
                        <div class="pf-tile-head"><span class="pf-icon"><i class="fas fa-heart-broken" aria-hidden="true"></i></span><h3 id="pf-b-title">Crisis de parella</h3></div>
                        <p class="pf-tile-lead"><strong>Quan la relació toca un punt crític.</strong></p>
                        <button class="pf-toggle" aria-expanded="false" aria-controls="pf-b-detail">Llegir més</button>
                        <div id="pf-b-detail" class="pf-detail" hidden>
                            <p><strong>Si us trobeu...</strong> en un moment on les discussions són freqüents, la desconnexió s'intensifica o apareixen decisions que divideixen el futur de la relació.</p>
                            <p><strong>Us ajudaré a...</strong> identificar els punts de ruptura, restaurar la comunicació i fer un pla clar (decidir si recuperar o transformar la relació) amb eines terapèutiques pràctiques i suport emocional.</p>
                        </div>
                    </article>

                    <!-- Tile C: Mediació Familiar -->
                    <article class="pf-tile" role="listitem" aria-labelledby="pf-c-title">
                        <div class="pf-tile-head"><span class="pf-icon"><i class="fas fa-handshake" aria-hidden="true"></i></span><h3 id="pf-c-title">Mediació familiar</h3></div>
                        <p class="pf-tile-lead"><strong>Solucions justes sense judici.</strong></p>
                        <button class="pf-toggle" aria-expanded="false" aria-controls="pf-c-detail">Llegir més</button>
                        <div id="pf-c-detail" class="pf-detail" hidden>
                            <p><strong>Si sentiu...</strong> que els conflictes familiars han creat una fractura i la comunicació està trencada.</p>
                            <p><strong>Us ajudaré a...</strong> trobar acords justos i voluntaris en un marc de respecte i diàleg, protegint relacions i estalviant desgast econòmic i emocional.</p>
                        </div>
                    </article>

                    <!-- Tile D: Suport en Crisi Familiars -->
                    <article class="pf-tile" role="listitem" aria-labelledby="pf-d-title">
                        <div class="pf-tile-head"><span class="pf-icon"><i class="fas fa-hands-helping" aria-hidden="true"></i></span><h3 id="pf-d-title">Suport en crisis familiars</h3></div>
                        <p class="pf-tile-lead"><strong>Navegar dins la tempesta i sortir més units.</strong></p>
                        <button class="pf-toggle" aria-expanded="false" aria-controls="pf-d-detail">Llegir més</button>
                        <div id="pf-d-detail" class="pf-detail" hidden>
                            <p><strong>Si a la vostra família...</strong> està patint una crisi per comportament d'un adolescent, una pèrdua o un esdeveniment que sacseja els vostres fonaments.</p>
                            <p><strong>Us ajudaré a...</strong> comprendre, reforçar vincles i convertir la crisi en una oportunitat per créixer.</p>
                        </div>
                    </article>
                </div>

                <!-- Full Method text kept visible below hero for SEO and clarity -->
                <section class="pf-method" aria-labelledby="pf-method-title">
                    <h3 id="pf-method-title">El meu mètode: Més enllà de "parlar dels problemes"</h3>
                    <p>No em limitaré a escoltar-te. El meu enfocament és pràctic i proactiu:</p>
                    <ul class="pf-method-list">
                        <li><strong>Anàlisi del cicle del conflicte:</strong> Identifiquem junts el patró que et manté atrapat.</li>
                        <li><strong>Eines de comunicació efectiva:</strong> T'ensenyo a expressar necessitats i a escoltar amb empatia.</li>
                        <li><strong>Gestió emocional intel·ligent:</strong> Aprendràs a regular les emocions per a evitar que controlin la relació.</li>
                        <li><strong>Pla d'acció concret:</strong> Treballarem amb objectius clars i passes assolibles per a veure resultats reals.</li>
                    </ul>
                    <p class="pf-method-goal"><strong>El meu objectiu és un:</strong> dotar-vos de les habilitats per a ser l'equip que sempre heu volgut ser.</p>
                </section>
            </div>
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

        <!-- Secció psicologia judicial i peritatge psicològic (redissenyada v2) -->
        <section class="clinica-judicial v2">
            <div class="cj-inner">
                <h2 id="cj-title" class="clinica-supera-title">Psicologia judicial: La prova pericial que pot decidir el teu cas</h2>
                <div class="cj-intro">
                    <h3 class="cj-subtitle">Quan el conflicte legal demana un informe psicològic sòlid</h3>
                    <p>En un procés judicial, les emocions i els fets sovint s'emboliquen. Un informe psicològic no és només un document; és l'eina que traduïx el teu estat mental, les teves capacitats i la realitat familiar en arguments sòlids i incontestables per al jutge.</p>
                    <p>No es tracta només de qui té la raó, sinó de qui pot demostrar-ho amb rigor i credibilitat. La meva experiència com a pèrit psicòloga col·legiada és el teu millor aliat per a aconseguir-ho.</p>
                </div>
                <div class="cj-cards">
                    <article class="cj-card" data-anim>
                        <div class="cj-card-head"><span class="cj-icon" aria-hidden="true"><i class="fas fa-users" aria-hidden="true"></i></span><h3>Informes per a custòdia i règim de visites</h3></div>
                        <div class="cj-card-body">
                            <p class="cj-summary"><strong>Si et trobes en...</strong> un procés de separació o divorci conflictiu, on es discuteix amb qui han de viure els fills o com s'han de fer les visites.</p>
                            <div class="cj-details" id="detail-1" hidden>
                                <p><strong>El meu informe demostrarà</strong> l'idoneitat parental de cada progenitor. Avaluaré el vincle afectiu, les habilitats parentals i les necessitats dels menors per proposar el millor règim de convivència per a ells, amb el seu benestar com a prioritat absoluta. Protegeix els teus fills amb un informe que parli per ells.</p>
                            </div>
                            <button class="cj-toggle" aria-expanded="false" aria-controls="detail-1">Llegir més</button>
                        </div>
                    </article>
                    <article class="cj-card" data-anim>
                        <div class="cj-card-head"><span class="cj-icon" aria-hidden="true"><i class="fas fa-exclamation-triangle" aria-hidden="true"></i></span><h3>Avaluació en casos de violència filio-parental (VFP)</h3></div>
                        <div class="cj-card-body">
                            <p class="cj-summary"><strong>Si et trobes en...</strong> una situació on els pares sou víctimes d'agressions, amenaces o mancances de respecte per part d'un fill, i sentiu que heu perdut el control i l'autoritat.</p>
                            <div class="cj-details" id="detail-2" hidden>
                                <p><strong>El meu informe identifica i avalua</strong> Les causes psicològiques que alimenten la violència. No només documento els fets, sinó que traço un pla d'intervenció per a aturar el cicle i reconstruir la relació familiar des d'una nova base, sempre que sigui possible.</p>
                            </div>
                            <button class="cj-toggle" aria-expanded="false" aria-controls="detail-2">Llegir més</button>
                        </div>
                    </article>
                    <article class="cj-card" data-anim>
                        <div class="cj-card-head"><span class="cj-icon" aria-hidden="true"><i class="fas fa-heartbeat" aria-hidden="true"></i></span><h3>Avaluació del dany psicològic</h3></div>
                        <div class="cj-card-body">
                            <p class="cj-summary"><strong>Si et trobes en...</strong> un procés per a reclamar una indemnització després de patir un accident, mobbing laboral (assetjament) o qualsevol situació traumàtica que t'hagi provocat un patiment psicològic verificable.</p>
                            <div class="cj-details" id="detail-3" hidden>
                                <p><strong>El meu informe quantifica</strong> l'impacte real del que has viscut. Connecto el fet causant amb els teus símptomes (ansietat, insomni, estrès postraumàtic) per a determinar el grau d'incapacitat o dany i sustentar la teva reclamació econòmica. Que et compensin també pel teu dolor emocional.</p>
                            </div>
                            <button class="cj-toggle" aria-expanded="false" aria-controls="detail-3">Llegir més</button>
                        </div>
                    </article>
                    <article class="cj-card" data-anim>
                        <div class="cj-card-head"><span class="cj-icon" aria-hidden="true"><i class="fas fa-brain" aria-hidden="true"></i></span><h3>Avaluació de la capacitat cognitiva i volitiva</h3></div>
                        <div class="cj-card-body">
                            <p class="cj-summary"><strong>Si et trobes en...</strong> un procés on es qüestiona la capacitat d'una persona per a prendre decisions (testar, administrar els seus béns, consentir...).</p>
                            <div class="cj-details" id="detail-4" hidden>
                                <p><strong>El meu informe determina</strong> el grau de discerniment de la persona. Avaluo de forma rigorosa si existeix algun trastorn que li impedeixi entendre les consequències dels seus actes, oferint al jutge una fotografia clara de la seva capacitat mental.</p>
                            </div>
                            <button class="cj-toggle" aria-expanded="false" aria-controls="detail-4">Llegir més</button>
                        </div>
                    </article>
                </div>
                <div class="cj-method-cta">
                    <h2 class="cj-method-title">El meu mètode: rigor científic i claredat legal</h2>
                    <ul class="cj-method-list">
                        <li>Entrevista en profunditat i avaluació psicològica amb les proves estandarditzades més reconegudes.</li>
                        <li>Anàlisi de tota la documentació del cas (informes mèdics, informes socials, etc.).</li>
                        <li>Redacció clara, concisa i contundent, entenedora per als operadors jurídics.</li>
                        <li>Assistència opcional a la Vista Oral per a defensar i explicar les meves conclusions amb convicció i solvència davant el tribunal.</li>
                    </ul>
                    <p class="cj-objectiu">El meu objectiu és un: que el jutge comprengui la realitat psicològica del cas sense cap mena de dubte.</p>
                    <blockquote class="cj-quote">En un judici, les paraules sense un fonament sòlid es les duu el vent. El meu informe és el fonament que la teva causa necessita.</blockquote>
                </div>
            </div>
        </section>
        <!-- Fi secció psicologia judicial i peritatge psicològic (v2) -->
        <!-- CTA final -->
        <section class="clinica-cta-final">
            <div class="clinica-cta-content spectacular">
                <div class="clinica-cta-copy">
                    <h2 class="clinica-cta-title">Estàs llest per transformar la teva vida?</h2>
                    <p class="clinica-cta-text">No deixis que el malestar emocional et limiti més temps. Contacta amb mi i definim un pla pràctic i sensible per a la vostra família o parella.</p>
                </div>
                <div class="clinica-cta-actions">
                    <a href="contacta.php" class="clinica-cta-button">Contacta ara <span class="chev" aria-hidden="true">›</span></a>
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
