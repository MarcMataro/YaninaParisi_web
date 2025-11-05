<?php
// Formulari per enviar una ressenya en català
if (session_status() === PHP_SESSION_NONE) session_start();

// Forçar idioma català per aquesta pàgina
$_SESSION['language'] = 'ca';

include '../includes/lang.php';
include '../includes/functions.php';

// Incloure classes d'accés a BD i model
require_once __DIR__ . '/../classes/connexio.php';
require_once __DIR__ . '/../classes/ressenyes.php';
require_once __DIR__ . '/../classes/ressenya_tokens.php';

$message_sent = false;
$message_error = false;
$errors = [];

try {
    $db = Connexio::getInstance()->getConnexio();
    $rModel = new Ressenyes($db);
} catch (Exception $e) {
    // No podem continuar sense base de dades
    $message_error = true;
    $errors[] = 'No s\'ha pogut connectar amb la base de dades.';
}

// Instanciem el gestor de tokens (si cal)
try {
    $tModel = new RessenyaTokens($db);
} catch (Exception $e) {
    // No fatal: operacions que depenen de tokens fallaran
    $tModel = null;
}

// Verificar token (GET o POST) — només amb token es permet enviar ressenyes
$token_from_get = $_GET['token'] ?? null;
$token_from_post = $_POST['token'] ?? null;
$token_value = $token_from_post ?? $token_from_get;
$token_valid = false;
$token_row = null;
if ($token_value && $tModel) {
    $token_row = $tModel->getByToken($token_value);
    if ($token_row) $token_valid = true;
}

// No permetem enviaments basats en sessió: només token
$allow_form = $token_valid;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$message_error) {
    // Assegurar que l'enviament està permès: només amb token vàlid
    $post_token = $_POST['token'] ?? null;
    if (!$token_valid) {
        $errors[] = 'No està autoritzat per enviar ressenyes. Necessites un enllaç de valoració (token) vàlid.';
    }
    // revalidar token POST si n'hi ha
    if ($post_token && $tModel) {
        $post_token_row = $tModel->getByToken($post_token);
        if (!$post_token_row) {
            $errors[] = 'Token invàlid o caducat.';
        } else {
            $token_row = $post_token_row; // utilitzar aquesta fila vàlida
            $token_valid = true;
        }
    }
    // Recollida i sanejament bàsic
    $nom_pacient = trim($_POST['nom_pacient'] ?? '');
    $inicials = trim($_POST['inicials'] ?? '');
    $edat = isset($_POST['edat']) && $_POST['edat'] !== '' ? (int)$_POST['edat'] : null;
    $titol_ca = trim($_POST['titol_ca'] ?? '');
    $text_ressenya_ca = trim($_POST['text_ressenya_ca'] ?? '');
    $puntuacio = isset($_POST['puntuacio']) ? (int)$_POST['puntuacio'] : null;
    $data_terapia = trim($_POST['data_terapia'] ?? '');
    $tipus_terapia = $_POST['tipus_terapia'] ?? 'individual';
    $autoritzacio_publicacio = isset($_POST['autoritzacio_publicacio']) ? 1 : 0;
    $mostrar_nom = isset($_POST['mostrar_nom']) ? 1 : 0;
    $mostrar_inicials = isset($_POST['mostrar_inicials']) ? 1 : 0;

    // Política de privacitat obligatòria (checkbox)
    $accept_privacy = isset($_POST['accept_privacy']);
    if (!$accept_privacy) {
        $errors[] = 'Has d\'acceptar la política de privacitat per enviar la ressenya.';
    }

    // Validacions mínimes en servidor
    if ($titol_ca === '') $errors[] = 'El títol és obligatori.';
    if ($text_ressenya_ca === '') $errors[] = 'El text de la ressenya és obligatori.';
    if ($puntuacio === null || $puntuacio < 1 || $puntuacio > 5) $errors[] = 'La puntuació ha de ser entre 1 i 5.';

    if (empty($errors)) {
        // Determinar pacient_id (per traçabilitat): només a partir del token
        $pacient_id_for_insert = null;
        if ($token_valid && $token_row) {
            $pacient_id_for_insert = (int)$token_row['pacient_id'];
        }
        // IMPORTANT: Els camps en castellà a la BDD són NOT NULL.
        // Com no demanem castellà a l'usuari, omplirem els camps en castellà amb el mateix text en català
        // (això evita errors de NOT NULL i manté coherència; si voleu traducció automàtica, cal integrar un servei extern).

        $data = [
            'pacient_id' => $pacient_id_for_insert,
            'nom_pacient' => $nom_pacient ?: null,
            'inicials' => $inicials ?: null,
            'edat' => $edat,
            'titol_ca' => $titol_ca,
            'titol_es' => $titol_ca, // pleca al camp es
            'text_ressenya_ca' => $text_ressenya_ca,
            'text_ressenya_es' => $text_ressenya_ca, // duplicat per satisfacció NOT NULL
            'puntuacio' => $puntuacio,
            'data_terapia' => $data_terapia ?: null,
            'tipus_terapia' => in_array($tipus_terapia, ['individual','parella','familiar','online','presencial']) ? $tipus_terapia : 'individual',
            'estat' => 'pendent',
            'verificada' => 0,
            'autoritzacio_publicacio' => $autoritzacio_publicacio,
            'mostrar_nom' => $mostrar_nom,
            'mostrar_inicials' => $mostrar_inicials,
            'likes' => 0,
            'reportada' => 0
        ];

        $created = $rModel->create($data);
        if ($created === false) {
            $message_error = true;
            $le = $rModel->getLastError();
            if (is_array($le) && isset($le['details'])) {
                foreach ($le['details'] as $k => $v) $errors[] = $v;
            } else {
                $errors[] = is_array($le) ? implode(' | ', $le) : (string)$le;
            }
        } else {
            // Si l'enviament ha vingut via token, consumim el token (marca com usada)
            $token_to_consume = $_POST['token'] ?? $token_from_get ?? null;
            if ($token_to_consume && $tModel) {
                $tModel->consumeToken($token_to_consume);
            }
            // Evitar re-enviaments: PRG
            // Redirigim mostrant missatge d'èxit (sense token)
            header('Location: ?sent=1');
            exit;
        }
    } else {
        $message_error = true;
    }

}

// Mostrar missatge d'enviament si venim redirigits
if (isset($_GET['sent']) && $_GET['sent'] == '1') {
    $message_sent = true;
}

?><!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Opina - Deixa una ressenya</title>
    <link rel="stylesheet" href="../css/estils.css">
    <link rel="stylesheet" href="../css/contacte.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@7.0.0/css/all.min.css">
</head>
<body>
    <?php include '_includes/navigation.php'; ?>

    <section class="contact-hero">
        <div class="container">
            <div class="contact-hero-content">
                <h1>Comparteix la teva experiència</h1>
                <p class="contact-hero-subtitle">La teva opinió ajuda altres persones. Pots enviar-la de forma anònima si ho prefereixes.</p>
            </div>
        </div>
    </section>

    <?php if (function_exists('render_breadcrumbs')) {
        render_breadcrumbs([
            ['label' => t('nav_home'), 'url' => 'home.php'],
            ['label' => 'Opina']
        ]);
    } ?>

    <section class="contact-main">
        <div class="container">
            <?php if ($message_sent): ?>
                <div class="alert alert-success"><i class="fas fa-check-circle"></i> Gràcies! La teva ressenya s'ha enviat i està pendent de moderació.</div>
            <?php endif; ?>

            <?php if ($message_error): ?>
                <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i>
                    <ul>
                    <?php foreach ($errors as $err): ?>
                        <li><?php echo htmlspecialchars($err); ?></li>
                    <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <div class="contact-grid">
                <div class="contact-form-section" id="opina-form">
                    <div class="form-header">
                        <h2>Escriu la teva ressenya</h2>
                        <p>Completa el formulari en català. Els camps en castellà s'ompliran automàticament per compatibilitat amb la BDD.</p>
                    </div>

                    <?php if ($allow_form): ?>
                    <form class="contact-form" method="POST" action="">
                        <?php if ($token_valid && !empty($token_value)): ?>
                            <input type="hidden" name="token" value="<?php echo htmlspecialchars($token_value); ?>">
                        <?php endif; ?>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="nom_pacient"><i class="fas fa-user"></i> Nom o pseudònim</label>
                                <input type="text" id="nom_pacient" name="nom_pacient" placeholder="Opcional" value="<?php echo isset($_POST['nom_pacient']) ? htmlspecialchars($_POST['nom_pacient']) : ''; ?>">
                            </div>

                            <div class="form-group">
                                <label for="inicials"><i class="fas fa-id-badge"></i> Inicials</label>
                                <input type="text" id="inicials" name="inicials" maxlength="10" placeholder="p.ex. J.P." value="<?php echo isset($_POST['inicials']) ? htmlspecialchars($_POST['inicials']) : ''; ?>">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="edat"><i class="fas fa-calendar-alt"></i> Edat</label>
                                <input type="number" id="edat" name="edat" min="0" max="120" placeholder="Opcional" value="<?php echo isset($_POST['edat']) ? (int)$_POST['edat'] : ''; ?>">
                            </div>

                            <div class="form-group">
                                <label for="puntuacio"><i class="fas fa-star"></i> Puntuació *</label>
                                <select id="puntuacio" name="puntuacio" required>
                                    <option value="">--</option>
                                    <?php for ($i=1;$i<=5;$i++): ?>
                                        <option value="<?php echo $i; ?>" <?php echo (isset($_POST['puntuacio']) && (int)$_POST['puntuacio']=== $i) ? 'selected' : ''; ?>><?php echo $i; ?> / 5</option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="titol_ca"><i class="fas fa-heading"></i> Títol de la ressenya *</label>
                            <input type="text" id="titol_ca" name="titol_ca" maxlength="150" required placeholder="Títol breu" value="<?php echo isset($_POST['titol_ca']) ? htmlspecialchars($_POST['titol_ca']) : ''; ?>">
                        </div>

                        <div class="form-group">
                            <label for="text_ressenya_ca"><i class="fas fa-comment-alt"></i> Text de la ressenya *</label>
                            <textarea id="text_ressenya_ca" name="text_ressenya_ca" rows="8" required placeholder="Explica la teva experiència..."><?php echo isset($_POST['text_ressenya_ca']) ? htmlspecialchars($_POST['text_ressenya_ca']) : ''; ?></textarea>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="data_terapia">Data aproximada de la teràpia</label>
                                <input type="date" id="data_terapia" name="data_terapia" value="<?php echo isset($_POST['data_terapia']) ? htmlspecialchars($_POST['data_terapia']) : ''; ?>">
                            </div>

                            <div class="form-group">
                                <label for="tipus_terapia">Tipus de teràpia</label>
                                <select id="tipus_terapia" name="tipus_terapia">
                                    <?php $ops = ['individual'=>'Individual','parella'=>'Parella','familiar'=>'Familiar','online'=>'Online','presencial'=>'Presencial']; foreach($ops as $key=>$label): ?>
                                        <option value="<?php echo $key; ?>" <?php echo (isset($_POST['tipus_terapia']) && $_POST['tipus_terapia']===$key) ? 'selected' : ''; ?>><?php echo $label; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="form-group form-checkbox">
                            <label class="checkbox-label">
                                <input type="checkbox" name="autoritzacio_publicacio" <?php echo isset($_POST['autoritzacio_publicacio']) ? 'checked' : ''; ?>>
                                <span class="checkmark" aria-hidden="true"></span>
                                Autoritzo la publicació de la meva ressenya
                            </label>
                        </div>

                        <div class="form-group form-checkbox">
                            <label class="checkbox-label">
                                <input type="checkbox" name="mostrar_nom" <?php echo isset($_POST['mostrar_nom']) ? 'checked' : ''; ?>>
                                <span class="checkmark" aria-hidden="true"></span>
                                Mostrar el meu nom
                            </label>
                        </div>

                        <div class="form-group form-checkbox">
                            <label class="checkbox-label">
                                <input type="checkbox" name="mostrar_inicials" <?php echo isset($_POST['mostrar_inicials']) ? 'checked' : ''; ?>>
                                <span class="checkmark" aria-hidden="true"></span>
                                Mostrar les meves inicials
                            </label>
                        </div>

                        <div class="form-group form-checkbox">
                            <label class="checkbox-label">
                                <input type="checkbox" name="accept_privacy" required>
                                <span class="checkmark" aria-hidden="true"></span>
                                Accepto la política de privacitat *
                            </label>
                        </div>

                        <button type="submit" class="btn btn-primary btn-submit"><i class="fas fa-paper-plane"></i> Enviar ressenya</button>
                    </form>
                    <?php else: ?>
                        <div class="alert alert-info">Per enviar una ressenya necessites un enllaç amb token (el rebràs per correu després d'una cita). Si tens el token, enganxa'l aquí per continuar:</div>
                        <form method="GET" action="" class="token-entry-form" style="margin-top:1rem;">
                            <div class="form-row">
                                <div class="form-group" style="flex:1;">
                                    <label for="token_input">Token</label>
                                    <input type="text" id="token_input" name="token" placeholder="Introdueix el token" required style="width:100%;">
                                </div>
                                <div class="form-group" style="align-self:flex-end;margin-left:0.5rem;">
                                    <button type="submit" class="btn">Validar token</button>
                                </div>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>

                <div class="contact-info-section">
                    <div class="contact-info-header"><h3>Notes sobre privacitat</h3></div>
                    <p>Si marques <strong>Mostrar el meu nom</strong> es publicarà tal qual; si marques <strong>Mostrar les meves inicials</strong> només apareixeran les inicials. Si no marques cap, la ressenya es pot publicar com anònima segons l'autorització.</p>
                    <p>Les ressenyes passen per moderació abans de ser visibles públicament.</p>
                </div>
            </div>
        </div>
    </section>

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
