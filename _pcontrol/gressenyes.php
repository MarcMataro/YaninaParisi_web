<?php
/**
 * Gestió de Ressenyes - Panel de Control
 * Llista ressenyes (pendent/aprovat/rebutjat) i permet moderar-les.
 */
session_start();

// Autenticació
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: index.php');
    exit;
}

require_once 'includes/role_check.php';

require_once __DIR__ . '/../classes/connexio.php';
require_once __DIR__ . '/../classes/ressenyes.php';
require_once __DIR__ . '/../classes/ressenya_tokens.php';
require_once __DIR__ . '/../classes/pacients.php';
require_once __DIR__ . '/../classes/video_reviews.php';
require_once __DIR__ . '/../classes/PHPMailer.php';

try {
    $connexio = Connexio::getInstance();
    $pdo = $connexio->getConnexio();
} catch (Exception $e) {
    die('Error de connexió: ' . $e->getMessage());
}

$rModel = new Ressenyes($pdo);
$vModel = VideoReviews::getInstance();

$missatge = '';
$tipusMissatge = '';

// Processar accions POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accio = $_POST['accio'] ?? '';
    $id = isset($_POST['id_ressenya']) ? (int)$_POST['id_ressenya'] : 0;
    $id_video = isset($_POST['id_video']) ? (int)$_POST['id_video'] : 0;

    switch ($accio) {
        // --- TEXT REVIEWS ---
        case 'enviar_peticio':
            $email = trim($_POST['email_peticio'] ?? '');
            $lang = $_POST['lang_peticio'] ?? 'ca';
            
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $missatge = "Format d'email invàlid."; 
                $tipusMissatge = 'error';
            } else {
                $pModel = new Pacient($pdo);
                $pacientData = $pModel->cercarPerEmail($email);
                
                // Allow sending even if not found in DB
                $targetId = 0; // ID 0 for unregistered patients
                $providedName = trim($_POST['nom_peticio'] ?? '');
                $displayName = $providedName;

                if ($pacientData) {
                    $targetId = (int)$pacientData['id_pacient'];
                    // Use DB name if no manual name provided
                    if (empty($displayName)) {
                        $displayName = $pacientData['nom'] . ' ' . $pacientData['cognoms'];
                    }
                }
                
                // Fallback name
                if (empty($displayName)) {
                     $displayName = ($lang == 'es') ? 'Cliente' : 'Client'; 
                }

                $tModel = new RessenyaTokens($pdo);
                // 168 hores de validesa (7 dies)
                $newToken = $tModel->createToken($targetId, 168);
                
                if ($newToken) {
                        // Construir URL
                        $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https://' : 'http://';
                        $host = $_SERVER['HTTP_HOST'];
                        $baseUrl = $protocol . $host;
                        // Assumim estructura: /ca/opina.php o /es/opina.php
                        $link = $baseUrl . '/' . $lang . '/opina.php?token=' . $newToken;
                        
                        // Enviar mail
                        try {
                            $smtp_config_path = __DIR__ . '/../_configs/smtp_config.php';
                            
                            // Verificar que el fitxer de configuració existeix
                            if (!file_exists($smtp_config_path)) {
                                throw new Exception("Fitxer de configuració SMTP no trobat: $smtp_config_path");
                            }

                            $mail = new PHPMailer($smtp_config_path);
                            // La configuració es carrega automàticament des del constructor
                            
                            $mail->addAddress($email, $displayName);
                            $mail->Subject = ($lang == 'es') ? 'Petición de reseña - Yanina Parisi' : 'Petició de ressenya - Yanina Parisi';
                            
                            // Emprem text pla com al formulari de contacte per assegurar l'entrega
                            $mail->isHTML = false; 

                            if ($lang == 'es') {
                                $greeting = (!empty($displayName) && $displayName !== 'Cliente') ? "Hola " . $displayName . "," : "Hola,";
                                $body = "$greeting\n\n";
                                $body .= "Esperamos que te encuentres bien.\n";
                                $body .= "Nos gustaría mucho conocer tu opinión sobre las sesiones. Tu feedback es muy importante.\n\n";
                                $body .= "Puedes dejar tu reseña en el siguiente enlace (vence en 7 días):\n";
                                $body .= "$link\n\n";
                                $body .= "Gracias,\nYanina Parisi";
                            } else {
                                $greeting = (!empty($displayName) && $displayName !== 'Client') ? "Hola " . $displayName . "," : "Hola,";
                                $body = "$greeting\n\n";
                                $body .= "Esperem que et trobis bé.\n";
                                $body .= "Ens agradaria molt conèixer la teva opinió sobre les sessions. El teu feedback és molt important.\n\n";
                                $body .= "Pots deixar la teva ressenya al següent enllaç (caduca en 7 dies):\n";
                                $body .= "$link\n\n";
                                $body .= "Gràcies,\nYanina Parisi";
                            }
                            
                            // Assegurar codificació correcta del cos
                            $mail->Body = $body;
                            
                            // Debugging explícit
                            // echo "<!-- DEBUG: Sending to $email ($displayName) using config $smtp_config_path -->";
                            $mail->addReplyTo($mail->From, $mail->FromName); // Add Reply-To explicitly to match Contact form behavior if needed

                            if ($mail->send()) {
                                // LOG DE CONFIRMACIÓ AL SERVIDOR
                                $logEntry = date('Y-m-d H:i:s') . " - EXITED: tramesa a $email ($displayName) OK.\n";
                                file_put_contents(__DIR__ . '/mail_log.txt', $logEntry, FILE_APPEND);

                                $missatge = "<b>CORREU ENVIAT CORRECTAMENT</b><br>Destinatari: $email<br>Nom: $displayName<br><br>Enllaç directe (per si de cas): <a href=\"$link\" target=\"_blank\">$link</a>";
                                $tipusMissatge = 'success';
                            } else {
                                $logEntry = date('Y-m-d H:i:s') . " - ERROR: tramesa a $email ($displayName) FALLIDA. Error: " . $mail->ErrorInfo . "\n";
                                file_put_contents(__DIR__ . '/mail_log.txt', $logEntry, FILE_APPEND);

                                $missatge = "Error enviant correu: " . $mail->ErrorInfo . "<br><br>Pots enviar-li aquest enllaç manualment: <a href=\"$link\" target=\"_blank\">$link</a>";
                                $tipusMissatge = 'warning'; 
                            }

                        } catch (Exception $e) {
                             $missatge = "Error intern preparant correu: " . $e->getMessage() . "<br><br>Enllaç manual: <a href=\"$link\" target=\"_blank\">$link</a>";
                             $tipusMissatge = 'warning';
                        }
                    } else {
                        $missatge = "Error generant el token."; 
                        $tipusMissatge = 'error';
                    }
                }
            break;
            
        case 'aprovar':
            if ($id && $rModel->setEstat($id, 'aprovat')) {
                $missatge = 'Ressenya aprovada.'; $tipusMissatge = 'success';
            } else { $missatge = 'Error aprovant la ressenya.'; $tipusMissatge = 'error'; }
            break;
        case 'rebutjar':
            if ($id && $rModel->setEstat($id, 'rebutjat')) {
                $missatge = 'Ressenya rebutjada.'; $tipusMissatge = 'success';
            } else { $missatge = 'Error rebutjant la ressenya.'; $tipusMissatge = 'error'; }
            break;
        case 'verificada':
            $val = isset($_POST['valor']) && $_POST['valor'] == '1' ? 1 : 0;
            if ($id && $rModel->setVerificada($id, (bool)$val)) {
                $missatge = 'Marca de verificació actualitzada.'; $tipusMissatge = 'success';
            } else { $missatge = 'Error actualitzant verificació.'; $tipusMissatge = 'error'; }
            break;
        case 'esborrar':
            if ($id && $rModel->delete($id)) {
                $missatge = 'Ressenya eliminada.'; $tipusMissatge = 'success';
            } else { $missatge = 'Error eliminant la ressenya.'; $tipusMissatge = 'error'; }
            break;

        // --- VIDEO REVIEWS ---
        case 'create_video':
            $url = $_POST['url'] ?? '';
            $title_ca = $_POST['title_ca'] ?? '';
            $title_es = $_POST['title_es'] ?? '';
            $is_public = isset($_POST['is_public']) ? true : false;
            
            if ($url && $title_ca && $title_es) {
                if ($vModel->create($url, $title_ca, $title_es, null, $is_public)) {
                    $missatge = 'Videorressenya creada correctament.'; $tipusMissatge = 'success';
                } else {
                    $missatge = 'Error creant la videorressenya.'; $tipusMissatge = 'error';
                }
            } else {
                $missatge = 'Falten camps obligatoris.'; $tipusMissatge = 'error';
            }
            break;

        case 'edit_video':
            if ($id_video) {
                $data = [
                    'youtube_url' => $_POST['url'] ?? '',
                    'title_ca' => $_POST['title_ca'] ?? '',
                    'title_es' => $_POST['title_es'] ?? '',
                    'is_public' => isset($_POST['is_public']) ? 1 : 0
                ];
                if (isset($_POST['position']) && is_numeric($_POST['position'])) {
                    $data['position'] = (int)$_POST['position'];
                }

                if ($vModel->update($id_video, $data)) {
                    $missatge = 'Videorressenya actualitzada.'; $tipusMissatge = 'success';
                } else {
                    $missatge = 'Error actualitzant la videorressenya.'; $tipusMissatge = 'error';
                }
            }
            break;

        case 'delete_video':
            if ($id_video && $vModel->delete($id_video)) {
                $missatge = 'Videorressenya eliminada.'; $tipusMissatge = 'success';
            } else {
                $missatge = 'Error eliminant videorressenya.'; $tipusMissatge = 'error';
            }
            break;

        case 'toggle_video_visibility':
            if ($id_video) {
                $newState = $vModel->toggleVisibility($id_video);
                if ($newState !== false) {
                    $missatge = 'Visibilitat canviada.'; $tipusMissatge = 'success';
                } else {
                    $missatge = 'Error canviant visibilitat.'; $tipusMissatge = 'error';
                }
            }
            break;
    }
}

// Filtres TEXT
$filtre = $_GET['filtre'] ?? 'pendent'; // pendent, aprovat, rebutjat, tots
$q = trim($_GET['q'] ?? '');

$opts = [];
if ($filtre !== 'tots') $opts['estat'] = $filtre;
if ($q !== '') $opts['q'] = $q;

$res = $rModel->list($opts);
$ressenyes = $res['data'] ?? [];

// Load VIDEOS (all)
$videos = $vModel->getAll(false); // get all, not just public

?>
<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Gestió de Ressenyes - Panel</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@7.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Libre+Baskerville:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="css/gpacients.css">
    <style>
        /* Restyling combobox and filter input for gressenyes admin */
        .card-actions { display: flex; gap: 10px; align-items: center; flex-wrap: wrap; }
        .card-actions form { display:flex; gap:10px; align-items:center; margin: 0; }
        .card-actions .form-select,
        .card-actions .form-input {
            font-family: inherit;
            font-size: 14px;
            padding: 8px 10px;
            border: 1px solid #d1d5db; /* light gray */
            background: #fff;
            color: #111827;
            border-radius: 6px;
            outline: none;
            transition: box-shadow .12s ease, border-color .12s ease;
            box-shadow: none;
            height: 38px;
            -webkit-appearance: none;
            appearance: none;
        }
        .card-actions .form-select:focus,
        .card-actions .form-input:focus {
            border-color: #2563eb; /* blue-600 */
            box-shadow: 0 0 0 3px rgba(37,99,235,0.08);
        }
        /* make select show a subtle chevron on the right */
        .card-actions .form-select { padding-right: 36px; background-image: linear-gradient(45deg, transparent 50%, #6b7280 50%), linear-gradient(135deg, #6b7280 50%, transparent 50%); background-position: calc(100% - 18px) calc(1em + 2px), calc(100% - 13px) calc(1em + 2px); background-size: 6px 6px, 6px 6px; background-repeat: no-repeat; }
        /* responsive behaviour */
        @media (max-width:600px) {
            .card-actions .form-select,
            .card-actions .form-input,
            .card-actions .btn { width: 100%; }
            .card-actions form { gap:8px; }
        }
    </style>
</head>
<body>
    <link rel="icon" type="image/png" sizes="32x32" href="../img/Logo32.png">
    <?php include 'includes/sidebar.php'; ?>

    <div class="main-content">
        <header class="top-bar">
            <div class="top-bar-left">
                <button class="menu-toggle" id="menuToggle"><i class="fas fa-bars"></i></button>
                <div class="top-bar-info">
                    <h1>Ressenyes</h1>
                    <p class="date-today">Gestiona i modera les ressenyes rebudes</p>
                </div>
            </div>
            <div class="top-bar-right">
                <div class="user-profile"><img src="../img/Logo.png" class="profile-img"><span class="profile-name"><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Admin'); ?></span></div>
            </div>
        </header>

        <div class="content-wrapper">
            <?php if ($missatge): ?>
                <div class="alert alert-<?php echo $tipusMissatge ?: 'info'; ?>" id="alertMessage">
                    <i class="fas fa-<?php echo $tipusMissatge === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
                    <span><?php echo $missatge; ?></span>
                    <button class="alert-close" onclick="document.getElementById('alertMessage').remove()"><i class="fas fa-times"></i></button>
                </div>
            <?php endif; ?>

            <!-- TEXT REVIEWS (OLD) -->
            <div class="card">
                <div class="card-header">
                    <h2><i class="fas fa-star"></i> Reseñas (Texto)</h2>
                    <div class="card-actions">
                        <form method="GET" action="gressenyes.php" style="display:flex;gap:10px;align-items:center;">
                            <select name="filtre" class="form-select">
                                <option value="pendent" <?php echo $filtre==='pendent'?'selected':''; ?>>Pendientes</option>
                                <option value="aprovat" <?php echo $filtre==='aprovat'?'selected':''; ?>>Aprobadas</option>
                                <option value="rebutjat" <?php echo $filtre==='rebutjat'?'selected':''; ?>>Rechazadas</option>
                                <option value="tots" <?php echo $filtre==='tots'?'selected':''; ?>>Todas</option>
                            </select>
                            <input type="text" name="q" class="form-input" placeholder="Cerca text o pacient..." value="<?php echo htmlspecialchars($q); ?>">
                            <button class="btn" type="submit">Filtrar</button>
                        </form>
                        <button class="btn" onclick="obrirModalPeticio()" type="button" style="background-color:#10b981; color:white;"><i class="fas fa-paper-plane"></i> Enviar Invitació</button>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="patients-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Título</th>
                                <th>Puntuación</th>
                                <th>Paciente</th>
                                <th>Fecha</th>
                                <th>Estado</th>
                                <th>Verificada</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($ressenyes)): ?>
                                <tr><td colspan="8" class="text-center">No se han encontrado reseñas</td></tr>
                            <?php else: ?>
                                <?php foreach ($ressenyes as $r): ?>
                                    <tr>
                                        <td>#<?php echo $r['id_ressenya'] ?? '-'; ?></td>
                                        <td><?php echo htmlspecialchars($r['titol_ca'] ?? $r['titol_es'] ?? '-'); ?></td>
                                        <td><?php echo (int)($r['puntuacio'] ?? 0); ?> / 5</td>
                                        <td><?php echo htmlspecialchars($r['nom_pacient'] ?: ($r['pacient_id'] ? 'Pacient#' . $r['pacient_id'] : '-')); ?></td>
                                        <td><?php echo date('d/m/Y H:i', strtotime($r['data_creacio'] ?? $r['data_actualitzacio'] ?? 'now')); ?></td>
                                        <td><?php echo htmlspecialchars($r['estat'] ?? 'pendent'); ?></td>
                                        <td><?php echo !empty($r['verificada']) ? '<span class="badge badge-success">Sí</span>' : '<span class="badge">No</span>'; ?></td>
                                        <td>
                                            <div class="action-buttons">
                                                <button class="btn-action btn-view" onclick="veureRessenya(this)" data-title="<?php echo htmlspecialchars($r['titol_ca']); ?>" data-text="<?php echo htmlspecialchars($r['text_ressenya_ca']); ?>" data-nom="<?php echo htmlspecialchars($r['nom_pacient'] ?: 'Pacient#'.$r['pacient_id']); ?>">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <form method="POST" action="gressenyes.php" style="display:inline">
                                                    <input type="hidden" name="accio" value="aprovar">
                                                    <input type="hidden" name="id_ressenya" value="<?php echo $r['id_ressenya']; ?>">
                                                    <button type="submit" class="btn-action" title="Aprovar"><i class="fas fa-check"></i></button>
                                                </form>
                                                <form method="POST" action="gressenyes.php" style="display:inline">
                                                    <input type="hidden" name="accio" value="rebutjar">
                                                    <input type="hidden" name="id_ressenya" value="<?php echo $r['id_ressenya']; ?>">
                                                    <button type="submit" class="btn-action" title="Rebutjar"><i class="fas fa-times"></i></button>
                                                </form>
                                                <form method="POST" action="gressenyes.php" style="display:inline">
                                                    <input type="hidden" name="accio" value="verificada">
                                                    <input type="hidden" name="id_ressenya" value="<?php echo $r['id_ressenya']; ?>">
                                                    <input type="hidden" name="valor" value="<?php echo empty($r['verificada']) ? '1' : '0'; ?>">
                                                    <button type="submit" class="btn-action" title="Toggle Verificada"><i class="fas fa-user-check"></i></button>
                                                </form>
                                                <form method="POST" action="gressenyes.php" style="display:inline" onsubmit="return confirm('Segur que vols eliminar aquesta ressenya?');">
                                                    <input type="hidden" name="accio" value="esborrar">
                                                    <input type="hidden" name="id_ressenya" value="<?php echo $r['id_ressenya']; ?>">
                                                    <button type="submit" class="btn-action" title="Eliminar"><i class="fas fa-trash"></i></button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- SECTION VIDEO REVIEWS -->
            <div class="card" style="margin-top: 30px;">
                <div class="card-header">
                    <h2><i class="fab fa-youtube"></i> Video Reviews</h2>
                    <div class="card-actions">
                        <button class="btn" onclick="obrirModalVideo()" type="button" style="background-color:#2563eb; color:white;">
                            <i class="fas fa-plus"></i> Afegir Vídeo
                        </button>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="patients-table">
                        <thead>
                            <tr>
                                <th style="width:80px">Img</th>
                                <th>Títol (CA/ES)</th>
                                <th>URL / ID</th>
                                <th>Posició</th>
                                <th>Visible</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($videos)): ?>
                                <tr><td colspan="6" class="text-center">No hi ha ressenyes de vídeo.</td></tr>
                            <?php else: ?>
                                <?php foreach ($videos as $v): 
                                    $vId = $v['id'];
                                    $ytId = $vModel->extractYoutubeId($v['youtube_url']);
                                    $thumb = $ytId ? "https://img.youtube.com/vi/$ytId/default.jpg" : "";
                                ?>
                                    <tr>
                                        <td>
                                            <?php if($thumb): ?>
                                                <img src="<?php echo $thumb; ?>" alt="Thumb" style="height:40px; border-radius:4px;">
                                            <?php else: ?>
                                                <i class="fas fa-film"></i>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <strong>CA:</strong> <?php echo htmlspecialchars($v['title_ca']); ?><br>
                                            <strong>ES:</strong> <?php echo htmlspecialchars($v['title_es']); ?>
                                        </td>
                                        <td style="font-size:0.85em; color:#666;">
                                            <a href="<?php echo htmlspecialchars($v['youtube_url']); ?>" target="_blank"><?php echo htmlspecialchars($v['youtube_url']); ?></a>
                                        </td>
                                        <td><?php echo (int)$v['position']; ?></td>
                                        <td>
                                            <form method="POST" style="display:inline">
                                                <input type="hidden" name="accio" value="toggle_video_visibility">
                                                <input type="hidden" name="id_video" value="<?php echo $vId; ?>">
                                                <button type="submit" class="badge <?php echo $v['is_public'] ? 'badge-success' : 'badge'; ?>" style="border:none; cursor:pointer;">
                                                    <?php echo $v['is_public'] ? 'Sí' : 'No'; ?>
                                                </button>
                                            </form>
                                        </td>
                                        <td>
                                            <div class="action-buttons">
                                                <button class="btn-action" onclick="editarVideo(
                                                    <?php echo $vId; ?>, 
                                                    '<?php echo addslashes($v['youtube_url']); ?>', 
                                                    '<?php echo addslashes($v['title_ca']); ?>', 
                                                    '<?php echo addslashes($v['title_es']); ?>', 
                                                    <?php echo $v['position']; ?>,
                                                    <?php echo $v['is_public']; ?>
                                                )" title="Editar">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <form method="POST" style="display:inline" onsubmit="return confirm('Segur que vols eliminar aquest vídeo?');">
                                                    <input type="hidden" name="accio" value="delete_video">
                                                    <input type="hidden" name="id_video" value="<?php echo $vId; ?>">
                                                    <button type="submit" class="btn-action" title="Eliminar"><i class="fas fa-trash"></i></button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>

    <!-- Modal per veure ressenya -->
    <div class="modal" id="modalRessenya">
        <div class="modal-content modal-large">
            <div class="modal-header">
                <h2 id="modalTitle">Reseña</h2>
                <button class="modal-close" onclick="tancarModalRessenya()">&times;</button>
            </div>
            <div class="modal-body">
                <p><strong>Autor:</strong> <span id="modalAuthor"></span></p>
                <hr>
                <div id="modalText" style="white-space:pre-wrap"></div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" onclick="tancarModalRessenya()">Cerrar</button>
            </div>
        </div>
    </div>

    <!-- Modal per enviar peticio -->
    <div class="modal" id="modalPeticio">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Enviar invitació de ressenya</h2>
                <button class="modal-close" onclick="tancarModalPeticio()">&times;</button>
            </div>
            <div class="modal-body">
                <form method="POST" action="gressenyes.php" id="formPeticio">
                    <input type="hidden" name="accio" value="enviar_peticio">
                    <div style="margin-bottom:15px;">
                        <label style="display:block;margin-bottom:5px;">Email del pacient:</label>
                        <input type="email" name="email_peticio" class="form-input" style="width:100%" required placeholder="client@exemple.com">
                    </div>
                    <div style="margin-bottom:15px;">
                        <label style="display:block;margin-bottom:5px;">Nom (Opcional):</label>
                        <input type="text" name="nom_peticio" class="form-input" style="width:100%" placeholder="Nom del pacient (si no hi és a la BD)">
                    </div>
                    <div style="margin-bottom:15px;">
                        <label style="display:block;margin-bottom:5px;">Idioma del correu:</label>
                        <select name="lang_peticio" class="form-select" style="width:100%">
                            <option value="ca">Català</option>
                            <option value="es">Castellano</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" onclick="tancarModalPeticio()">Cancel·lar</button>
                <button class="btn btn-primary" onclick="document.getElementById('formPeticio').submit()">Enviar Invitació</button>
            </div>
        </div>
    </div>

    <!-- Modal Create/Edit Video -->
    <div class="modal" id="modalVideo">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modalVideoTitle">Nova Videorressenya</h2>
                <button class="modal-close" onclick="tancarModalVideo()">&times;</button>
            </div>
            <div class="modal-body">
                <form method="POST" action="gressenyes.php" id="formVideo">
                    <input type="hidden" name="accio" id="formVideoAccio" value="create_video">
                    <input type="hidden" name="id_video" id="video_id_field" value="">

                    <div style="margin-bottom:15px;">
                        <label style="display:block;margin-bottom:5px;">YouTube URL:</label>
                        <input type="text" name="url" id="video_url" class="form-input" style="width:100%" required placeholder="https://www.youtube.com/watch?v=...">
                    </div>
                    
                    <div style="display:flex; gap:15px; margin-bottom:15px;">
                        <div style="flex:1">
                            <label style="display:block;margin-bottom:5px;">Títol (Cat):</label>
                            <input type="text" name="title_ca" id="video_title_ca" class="form-input" style="width:100%" required>
                        </div>
                        <div style="flex:1">
                            <label style="display:block;margin-bottom:5px;">Título (Esp):</label>
                            <input type="text" name="title_es" id="video_title_es" class="form-input" style="width:100%" required>
                        </div>
                    </div>

                    <div style="display:flex; gap:15px; margin-bottom:15px;">
                        <div style="flex:1">
                            <label style="display:block;margin-bottom:5px;">Posició:</label>
                            <input type="number" name="position" id="video_position" class="form-input" style="width:100%" placeholder="Auto">
                        </div>
                        <div style="flex:1; display:flex; align-items:flex-end; padding-bottom:10px;">
                            <label style="cursor:pointer; display:flex; align-items:center; gap:8px;">
                                <input type="checkbox" name="is_public" id="video_public" checked style="width:18px; height:18px;">
                                <span style="margin-left:8px">Públicament visible</span>
                            </label>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" onclick="tancarModalVideo()">Cancel·lar</button>
                <button class="btn btn-primary" onclick="document.getElementById('formVideo').submit()">Guardar</button>
            </div>
        </div>
    </div>

    <script>
        function veureRessenya(btn) {
            const title = btn.dataset.title || '';
            const text = btn.dataset.text || '';
            const nom = btn.dataset.nom || '';
            document.getElementById('modalTitle').textContent = title;
            document.getElementById('modalAuthor').textContent = nom;
            document.getElementById('modalText').textContent = text;
            document.getElementById('modalRessenya').classList.add('show');
            document.body.style.overflow = 'hidden';
        }
        function tancarModalRessenya() {
            document.getElementById('modalRessenya').classList.remove('show');
            document.body.style.overflow = 'auto';
        }

        function obrirModalPeticio() {
            document.getElementById('modalPeticio').classList.add('show');
            document.body.style.overflow = 'hidden';
        }
        function tancarModalPeticio() {
            document.getElementById('modalPeticio').classList.remove('show');
            document.body.style.overflow = 'auto';
        }

        // --- VIDEO MODAL SCRIPTS ---
        function obrirModalVideo(isEdit = false) {
            document.getElementById('modalVideoTitle').textContent = isEdit ? 'Editar Videorressenya' : 'Nova Videorressenya';
            document.getElementById('formVideoAccio').value = isEdit ? 'edit_video' : 'create_video';
            document.getElementById('video_id_field').disabled = !isEdit; 
            
            if (!isEdit) {
                // Clear form
                document.getElementById('formVideo').reset();
                document.getElementById('video_id_field').value = '';
                document.getElementById('formVideoAccio').value = 'create_video';
            }
            
            document.getElementById('modalVideo').classList.add('show');
            document.body.style.overflow = 'hidden';
        }

        function editarVideo(id, url, t_ca, t_es, pos, is_public) {
            obrirModalVideo(true);
            document.getElementById('video_id_field').value = id;
            document.getElementById('video_url').value = url;
            document.getElementById('video_title_ca').value = t_ca;
            document.getElementById('video_title_es').value = t_es;
            document.getElementById('video_position').value = pos;
            document.getElementById('video_public').checked = (is_public == 1);
        }

        function tancarModalVideo() {
            document.getElementById('modalVideo').classList.remove('show');
            document.body.style.overflow = 'auto';
        }
    </script>

</body>
</html>
