<?php
// Formulario para enviar una reseña en español
if (session_status() === PHP_SESSION_NONE) session_start();

// Forzar idioma español para esta página
$_SESSION['language'] = 'es';

include '../includes/lang.php';
include '../includes/functions.php';

// Incluir clases de acceso a BD y modelo
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
    // No podemos continuar sin base de datos
    $message_error = true;
    $errors[] = 'No se ha podido conectar con la base de datos.';
}

// Instanciamos el gestor de tokens (si hace falta)
try {
    $tModel = new RessenyaTokens($db);
} catch (Exception $e) {
    // No fatal: operaciones que dependan de tokens fallarán
    $tModel = null;
}

// Verificar token (GET o POST) — sólo con token se permite enviar reseñas
$token_from_get = $_GET['token'] ?? null;
$token_from_post = $_POST['token'] ?? null;
$token_value = $token_from_post ?? $token_from_get;
$token_valid = false;
$token_row = null;
if ($token_value && $tModel) {
    $token_row = $tModel->getByToken($token_value);
    if ($token_row) $token_valid = true;
}

// No permitimos envíos basados en sesión: sólo token
$allow_form = $token_valid;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$message_error) {
    // Asegurar que el envío está permitido: sólo con token válido
    $post_token = $_POST['token'] ?? null;
    if (!$token_valid) {
        $errors[] = 'No está autorizado para enviar reseñas. Necesita un enlace de valoración (token) válido.';
    }
    // revalidar token POST si existe
    if ($post_token && $tModel) {
        $post_token_row = $tModel->getByToken($post_token);
        if (!$post_token_row) {
            $errors[] = 'Token inválido o caducado.';
        } else {
            $token_row = $post_token_row; // usar esta fila válida
            $token_valid = true;
        }
    }
    // Recogida y saneamiento básico
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

    // Política de privacidad obligatoria (checkbox)
    $accept_privacy = isset($_POST['accept_privacy']);
    if (!$accept_privacy) {
        $errors[] = 'Debe aceptar la política de privacidad para enviar la reseña.';
    }

    // Validaciones mínimas en servidor
    if ($titol_ca === '') $errors[] = 'El título es obligatorio.';
    if ($text_ressenya_ca === '') $errors[] = 'El texto de la reseña es obligatorio.';
    if ($puntuacio === null || $puntuacio < 1 || $puntuacio > 5) $errors[] = 'La puntuación debe estar entre 1 y 5.';

    if (empty($errors)) {
        // Determinar pacient_id (para trazabilidad): sólo a partir del token
        $pacient_id_for_insert = null;
        if ($token_valid && $token_row) {
            $pacient_id_for_insert = (int)$token_row['pacient_id'];
        }
        // IMPORTANTE: Los campos en castellano en la BDD son NOT NULL.
        // Como no pedimos catalán al usuario, rellenaremos los campos en catalán con el mismo texto en castellano
        // (esto evita errores de NOT NULL y mantiene coherencia; si desea traducción automática, hay que integrar un servicio externo).

        $data = [
            'pacient_id' => $pacient_id_for_insert,
            'nom_pacient' => $nom_pacient ?: null,
            'inicials' => $inicials ?: null,
            'edat' => $edat,
            'titol_ca' => $titol_ca,
            'titol_es' => $titol_ca, // rellenar campo es
            'text_ressenya_ca' => $text_ressenya_ca,
            'text_ressenya_es' => $text_ressenya_ca, // duplicado para satisfacer NOT NULL
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
            // Si el envío ha venido vía token, consumimos el token (marcar como usado)
            $token_to_consume = $_POST['token'] ?? $token_from_get ?? null;
            if ($token_to_consume && $tModel) {
                $tModel->consumeToken($token_to_consume);
            }
            // Evitar reenvíos: PRG
            // Redirigimos mostrando mensaje de éxito (sin token)
            header('Location: ?sent=1');
            exit;
        }
    } else {
        $message_error = true;
    }

}

// Mostrar mensaje de envío si venimos redirigidos
if (isset($_GET['sent']) && $_GET['sent'] == '1') {
    $message_sent = true;
}

?><!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Opina - Deja una reseña</title>
    <link rel="stylesheet" href="../css/estils.css">
    <link rel="stylesheet" href="../css/contacte.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@7.0.0/css/all.min.css">
</head>
<body>
    <?php include '_includes/navigation.php'; ?>

    <section class="contact-hero">
        <div class="container">
            <div class="contact-hero-content">
                <h1>Comparte tu experiencia</h1>
                <p class="contact-hero-subtitle">Tu opinión ayuda a otras personas. Puedes enviarla de forma anónima si lo prefieres.</p>
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
                <div class="alert alert-success"><i class="fas fa-check-circle"></i> ¡Gracias! Tu reseña se ha enviado y está pendiente de moderación.</div>
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
                        <h2>Escribe tu reseña</h2>
                        <p>Completa el formulario en español. Los campos en catalán se rellenarán automáticamente para compatibilidad con la BDD.</p>
                    </div>

                    <?php if ($allow_form): ?>
                    <form class="contact-form" method="POST" action="">
                        <?php if ($token_valid && !empty($token_value)): ?>
                            <input type="hidden" name="token" value="<?php echo htmlspecialchars($token_value); ?>">
                        <?php endif; ?>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="nom_pacient"><i class="fas fa-user"></i> Nombre o seudónimo</label>
                                <input type="text" id="nom_pacient" name="nom_pacient" placeholder="Opcional" value="<?php echo isset($_POST['nom_pacient']) ? htmlspecialchars($_POST['nom_pacient']) : ''; ?>">
                            </div>

                            <div class="form-group">
                                <label for="inicials"><i class="fas fa-id-badge"></i> Iniciales</label>
                                <input type="text" id="inicials" name="inicials" maxlength="10" placeholder="p.ej. J.P." value="<?php echo isset($_POST['inicials']) ? htmlspecialchars($_POST['inicials']) : ''; ?>">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="edat"><i class="fas fa-calendar-alt"></i> Edad</label>
                                <input type="number" id="edat" name="edat" min="0" max="120" placeholder="Opcional" value="<?php echo isset($_POST['edat']) ? (int)$_POST['edat'] : ''; ?>">
                            </div>

                            <div class="form-group">
                                <label for="puntuacio"><i class="fas fa-star"></i> Puntuación *</label>
                                <select id="puntuacio" name="puntuacio" required>
                                    <option value="">--</option>
                                    <?php for ($i=1;$i<=5;$i++): ?>
                                        <option value="<?php echo $i; ?>" <?php echo (isset($_POST['puntuacio']) && (int)$_POST['puntuacio']=== $i) ? 'selected' : ''; ?>><?php echo $i; ?> / 5</option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="titol_ca"><i class="fas fa-heading"></i> Título de la reseña *</label>
                            <input type="text" id="titol_ca" name="titol_ca" maxlength="150" required placeholder="Título breve" value="<?php echo isset($_POST['titol_ca']) ? htmlspecialchars($_POST['titol_ca']) : ''; ?>">
                        </div>

                        <div class="form-group">
                            <label for="text_ressenya_ca"><i class="fas fa-comment-alt"></i> Texto de la reseña *</label>
                            <textarea id="text_ressenya_ca" name="text_ressenya_ca" rows="8" required placeholder="Explica tu experiencia..."><?php echo isset($_POST['text_ressenya_ca']) ? htmlspecialchars($_POST['text_ressenya_ca']) : ''; ?></textarea>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="data_terapia">Fecha aproximada de la terapia</label>
                                <input type="date" id="data_terapia" name="data_terapia" value="<?php echo isset($_POST['data_terapia']) ? htmlspecialchars($_POST['data_terapia']) : ''; ?>">
                            </div>

                            <div class="form-group">
                                <label for="tipus_terapia">Tipo de terapia</label>
                                <select id="tipus_terapia" name="tipus_terapia">
                                    <?php $ops = ['individual'=>'Individual','parella'=>'Pareja','familiar'=>'Familiar','online'=>'Online','presencial'=>'Presencial']; foreach($ops as $key=>$label): ?>
                                        <option value="<?php echo $key; ?>" <?php echo (isset($_POST['tipus_terapia']) && $_POST['tipus_terapia']===$key) ? 'selected' : ''; ?>><?php echo $label; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="form-group form-checkbox">
                            <label class="checkbox-label">
                                <input type="checkbox" name="autoritzacio_publicacio" <?php echo isset($_POST['autoritzacio_publicacio']) ? 'checked' : ''; ?>>
                                <span class="checkmark" aria-hidden="true"></span>
                                Autorizo la publicación de mi reseña
                            </label>
                        </div>

                        <div class="form-group form-checkbox">
                            <label class="checkbox-label">
                                <input type="checkbox" name="mostrar_nom" <?php echo isset($_POST['mostrar_nom']) ? 'checked' : ''; ?>>
                                <span class="checkmark" aria-hidden="true"></span>
                                Mostrar mi nombre
                            </label>
                        </div>

                        <div class="form-group form-checkbox">
                            <label class="checkbox-label">
                                <input type="checkbox" name="mostrar_inicials" <?php echo isset($_POST['mostrar_inicials']) ? 'checked' : ''; ?>>
                                <span class="checkmark" aria-hidden="true"></span>
                                Mostrar mis iniciales
                            </label>
                        </div>

                        <div class="form-group form-checkbox">
                            <label class="checkbox-label">
                                <input type="checkbox" name="accept_privacy" required>
                                <span class="checkmark" aria-hidden="true"></span>
                                Acepto la política de privacidad *
                            </label>
                        </div>

                        <button type="submit" class="btn btn-primary btn-submit"><i class="fas fa-paper-plane"></i> Enviar reseña</button>
                    </form>
                    <?php else: ?>
                        <div class="alert alert-info">Para enviar una reseña necesitas un enlace con token (lo recibirás por correo después de una cita). Si tienes el token, pégalo aquí para continuar:</div>
                        <form method="GET" action="" class="token-entry-form" style="margin-top:1rem;">
                            <div class="form-row">
                                <div class="form-group" style="flex:1;">
                                    <label for="token_input">Token</label>
                                    <input type="text" id="token_input" name="token" placeholder="Introduce el token" required style="width:100%;">
                                </div>
                                <div class="form-group" style="align-self:flex-end;margin-left:0.5rem;">
                                    <button type="submit" class="btn">Validar token</button>
                                </div>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>

                <div class="contact-info-section">
                    <div class="contact-info-header"><h3>Notas sobre privacidad</h3></div>
                    <p>Si marcas <strong>Mostrar mi nombre</strong> se publicará tal cual; si marcas <strong>Mostrar mis iniciales</strong> solo aparecerán las iniciales. Si no marcas ninguna, la reseña se puede publicar como anónima según la autorización.</p>
                    <p>Las reseñas pasan por moderación antes de ser visibles públicamente.</p>
                </div>
            </div>
        </div>
    </section>

    <?php include '_includes/footer.php'; ?>

    <script>
            // Script para la navegación suave
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function (e) {
                    e.preventDefault();
                    document.querySelector(this.getAttribute('href')).scrollIntoView({
                        behavior: 'smooth'
                    });
                });
            });

            // Script para el efecto scroll de la navegación
            window.addEventListener('scroll', function() {
                const header = document.querySelector('header');
                if (window.scrollY > 50) {
                    header.classList.add('scrolled');
                } else {
                    header.classList.remove('scrolled');
                }
            });

            // Script para el selector de idioma
            document.addEventListener('DOMContentLoaded', function() {
                document.querySelectorAll('.lang-btn').forEach(btn => {
                    btn.addEventListener('click', function() {
                        // Obtener el idioma del data attribute
                        const lang = this.getAttribute('data-lang');
                        console.log('Botón clickado, idioma:', lang);
                    
                        // Eliminar clase active de todos los botones (tanto desktop como móvil)
                        document.querySelectorAll('.lang-btn').forEach(b => b.classList.remove('active'));
                        // Añadir clase active a todos los botones del mismo idioma
                        document.querySelectorAll(`.lang-btn[data-lang="${lang}"]`).forEach(b => b.classList.add('active'));
                    
                        // Cerrar menú móvil si está abierto
                        const mobileMenuToggle = document.querySelector('.mobile-menu-toggle');
                        const navMenu = document.querySelector('.nav-menu ul');
                        if (mobileMenuToggle && navMenu) {
                            mobileMenuToggle.classList.remove('active');
                            navMenu.classList.remove('show');
                        }
                    
                        // Cambiar idioma
                        changeLanguage(lang);
                    });
                });

                // Funcionalidad del menú hamburguesa
                const mobileMenuToggle = document.querySelector('.mobile-menu-toggle');
                const navMenu = document.querySelector('.nav-menu ul');

                if (mobileMenuToggle && navMenu) {
                    mobileMenuToggle.addEventListener('click', function() {
                        this.classList.toggle('active');
                        navMenu.classList.toggle('show');
                    });

                    // Cerrar menú cuando se hace clic en un enlace
                    document.querySelectorAll('.nav-menu ul li a').forEach(link => {
                        link.addEventListener('click', function() {
                            mobileMenuToggle.classList.remove('active');
                            navMenu.classList.remove('show');
                        });
                    });

                    // Cerrar menú cuando se hace clic fuera
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
