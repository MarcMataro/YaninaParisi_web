<?php
session_start();

// Debug helper: enable detailed PHP errors only when explicitly requested via ?debug=1
// This keeps production behavior unchanged but helps local troubleshooting.
if (isset($_GET['debug']) && $_GET['debug'] === '1') {
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);
    // A small HTML comment marker so the page isn't altered visually but we can
    // confirm the debug flag was applied when viewing source.
    echo "<!-- DEBUG: display_errors enabled -->\n";
}

// Headers per evitar cache
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

// Verificar autenticación
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    // Si és una petició AJAX, retornar JSON
    if (isset($_POST['action']) || (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest')) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'No autenticado', 'redirect' => 'index.php']);
        exit;
    }
    // Si no, redirigir normalment
    header('Location: index.php');
    exit;
}

require_once 'includes/role_check.php';

// Evitar problemes amb headers enviats abans (bufferització) i inicialitzar dependències
ob_start();

// Inicialitzar connexió i models anticipadament perquè el codi HTML/JS que segueix pugui usar
// les variables ($tarifes, $users) i perquè les crides a header() en el processament POST funcionin.
require_once __DIR__ . '/../classes/connexio.php';
require_once __DIR__ . '/../classes/usuaris_panell.php';
require_once __DIR__ . '/../classes/psicologa_data.php';
// Tarifes removed: tariff management was removed per request.

$db = null;
try {
    $db = Connexio::getInstance()->getConnexio();
} catch (Exception $e) {
    error_log('configuracion.php: no s\'ha pogut connectar a la BD: ' . $e->getMessage());
}

$usersModel = $db ? new UsuarisPanell($db) : null;

// Model per a la informació de la psicòloga
$psicologaModel = $db ? new PsicologaData($db) : null;
$psicologa = null;
if ($psicologaModel) {
    $rows = $psicologaModel->listar(1, 0);
    $psicologa = $rows ? $rows[0] : null;
}

// Scan image directories for foto_perfil selector (search in img/ and img/media/)
$available_images = [];
$rootDir = realpath(__DIR__ . '/..');
$scanDirs = [
    realpath(__DIR__ . '/../img'),
    realpath(__DIR__ . '/../img/media')
];
foreach ($scanDirs as $d) {
    if ($d && is_dir($d)) {
        foreach (glob($d . '/*.{jpg,jpeg,png,gif,webp,svg}', GLOB_BRACE) as $f) {
            // Build URL relative to this script: ../img/...
            $rel = str_replace('\\', '/', str_replace($rootDir, '', $f));
            $url = '../' . ltrim($rel, '/');
            $available_images[$url] = basename($f);
        }
    }
}
// unique and sort by filename
if (!empty($available_images)) {
    asort($available_images, SORT_STRING);
}

// Carregar llistes que s'utilitzen en el JS/HTML per evitar avisos
$users = $usersModel ? $usersModel->llistar([], 200, 0) : [];
// (tarifes removed)

$saved = isset($_GET['saved']) && $_GET['saved'] == '1';

// If we preserved old psicologa form values after redirect, load them here and clear
$psicologa_old = $_SESSION['psicologa_old'] ?? null;
if (isset($_SESSION['psicologa_old'])) {
    unset($_SESSION['psicologa_old']);
}

// CSRF token simple (low-risk): generar si no existeix
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
}
$csrf_token = $_SESSION['csrf_token'];
/**
 * Configuració - Panel de Control
 *
  */
// Dades de configuració (després es connectarà amb BD)
// Procesar formulario si se envía
// Inicialitzar models i connexió a BD per a la gestió d'usuaris
// Note: connexion/classes already initialitzades més amunt. Reuse $db and $usersModel.

// Procesar form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF validation: token must be present and must match session token
    $posted_csrf = $_POST['csrf_token'] ?? '';
    if (empty($posted_csrf) || !hash_equals($_SESSION['csrf_token'] ?? '', $posted_csrf)) {
        // Invalid or missing token
        header('Location: configuracion.php?error=csrf');
        exit;
    }

    // --- Psicòloga: Crear
    if (isset($_POST['create_psicologa']) && $psicologaModel) {
        $m = new PsicologaData($db);
        $m->nom_complet_ca = trim($_POST['nom_complet_ca'] ?? '');
        $m->nom_complet_es = trim($_POST['nom_complet_es'] ?? '');
        $m->titulacio_ca = trim($_POST['titulacio_ca'] ?? '');
        $m->titulacio_es = trim($_POST['titulacio_es'] ?? '');
        $m->foto_perfil = trim($_POST['foto_perfil'] ?? '') ?: null;
        $m->alt_foto_ca = trim($_POST['alt_foto_ca'] ?? '') ?: null;
        $m->alt_foto_es = trim($_POST['alt_foto_es'] ?? '') ?: null;
        $m->email_professional = trim($_POST['email_professional'] ?? '') ?: null;
        $m->telefon_professional = trim($_POST['telefon_professional'] ?? '') ?: null;
        $m->linkedin_url = trim($_POST['linkedin_url'] ?? '') ?: null;
        $m->instagram_professional = trim($_POST['instagram_professional'] ?? '') ?: null;
        $m->num_collegiat = trim($_POST['num_collegiat'] ?? '');
        $m->college_professional = trim($_POST['college_professional'] ?? '');

        $errs = $m->validate();
        if (!empty($errs)) {
            // Preserve submitted values in session so we can prefill the form after redirect
            $_SESSION['psicologa_old'] = $_POST;
            header('Location: configuracion.php?psicologa_error=' . urlencode(implode(',', $errs)) . '&action=create');
            exit;
        }
        if ($m->crear()) {
            header('Location: configuracion.php?psicologa_created=1');
            exit;
        }
        $_SESSION['psicologa_old'] = $_POST;
        header('Location: configuracion.php?psicologa_error=save&action=create');
        exit;
    }

    // --- Psicòloga: Editar
    if (isset($_POST['edit_psicologa']) && $psicologaModel) {
        $id = intval($_POST['edit_psicologa_id'] ?? 0);
        if ($id && $psicologaModel->llegirPerId($id)) {
            $psicologaModel->nom_complet_ca = trim($_POST['nom_complet_ca'] ?? $psicologaModel->nom_complet_ca);
            $psicologaModel->nom_complet_es = trim($_POST['nom_complet_es'] ?? $psicologaModel->nom_complet_es);
            $psicologaModel->titulacio_ca = trim($_POST['titulacio_ca'] ?? $psicologaModel->titulacio_ca);
            $psicologaModel->titulacio_es = trim($_POST['titulacio_es'] ?? $psicologaModel->titulacio_es);
            $psicologaModel->foto_perfil = trim($_POST['foto_perfil'] ?? $psicologaModel->foto_perfil) ?: null;
            $psicologaModel->alt_foto_ca = trim($_POST['alt_foto_ca'] ?? $psicologaModel->alt_foto_ca) ?: null;
            $psicologaModel->alt_foto_es = trim($_POST['alt_foto_es'] ?? $psicologaModel->alt_foto_es) ?: null;
            $psicologaModel->email_professional = trim($_POST['email_professional'] ?? $psicologaModel->email_professional) ?: null;
            $psicologaModel->telefon_professional = trim($_POST['telefon_professional'] ?? $psicologaModel->telefon_professional) ?: null;
            $psicologaModel->linkedin_url = trim($_POST['linkedin_url'] ?? $psicologaModel->linkedin_url) ?: null;
            $psicologaModel->instagram_professional = trim($_POST['instagram_professional'] ?? $psicologaModel->instagram_professional) ?: null;
            $psicologaModel->num_collegiat = trim($_POST['num_collegiat'] ?? $psicologaModel->num_collegiat);
            $psicologaModel->college_professional = trim($_POST['college_professional'] ?? $psicologaModel->college_professional);

            $errs = $psicologaModel->validate();
            if (!empty($errs)) {
                $_SESSION['psicologa_old'] = $_POST;
                header('Location: configuracion.php?psicologa_error=' . urlencode(implode(',', $errs)) . '&action=edit');
                exit;
            }
            if ($psicologaModel->actualitzar()) {
                header('Location: configuracion.php?psicologa_edited=1');
                exit;
            }
        }
        $_SESSION['psicologa_old'] = $_POST;
        header('Location: configuracion.php?psicologa_error=edit&action=edit');
        exit;
    }

    // 1) Crear usuari des del formulari d'usuaris (botó amb name="create_user")
    if (isset($_POST['create_user']) && $usersModel) {
        $u_email = trim($_POST['user_email'] ?? '');
        $u_nombre = trim($_POST['user_nombre'] ?? '');
        $u_apellidos = trim($_POST['user_apellidos'] ?? '');
        $u_password = $_POST['user_password'] ?? '';
        $u_rol = $_POST['user_rol'] ?? 'editor';
        $u_telefono = trim($_POST['user_telefono'] ?? '');
        $u_idioma = $_POST['user_idioma'] ?? 'ca';
        $u_notif_email = isset($_POST['user_notificaciones_email']) ? 1 : 0;

        // Validacions bàsiques
        if (!filter_var($u_email, FILTER_VALIDATE_EMAIL) || empty($u_nombre) || empty($u_apellidos) || empty($u_password)) {
            header('Location: configuracion.php?user_error=invalid');
            exit;
        }

        // Security check: Admin cannot create Superadmin
        $currentUserRole = $_SESSION['user_role'] ?? 'editor';
        if ($currentUserRole === 'admin' && $u_rol === 'superadmin') {
            header('Location: configuracion.php?user_error=permisos');
            exit;
        }

        // Comprovar si l'email ja existeix
        $tempModel = new UsuarisPanell($db);
        if ($tempModel->existeixEmail($u_email)) {
            header('Location: configuracion.php?user_error=exists');
            exit;
        }

        // Preparar objecte i crear
        $newUser = new UsuarisPanell($db);
        $newUser->email = $u_email;
        $newUser->nombre = $u_nombre;
        $newUser->apellidos = $u_apellidos;
        $newUser->rol = $u_rol;
        $newUser->telefono = $u_telefono;
        $newUser->idioma = $u_idioma;
        $newUser->notificaciones_email = $u_notif_email;
        $newUser->activo = 1;
        $newUser->creado_por = $_SESSION['user_id'] ?? null;

        $res = $newUser->crear($u_password);
        if ($res) {
            header('Location: configuracion.php?user_created=1');
            exit;
        }

        header('Location: configuracion.php?user_error=save');
        exit;
    }

    // POST handling removed
    if (isset($_POST['edit_user']) && $usersModel) {
        $id = intval($_POST['edit_user_id'] ?? 0);
        $nom = trim($_POST['edit_user_nombre'] ?? '');
        $apellidos = trim($_POST['edit_user_apellidos'] ?? '');
        $email = trim($_POST['edit_user_email'] ?? '');
        $password = $_POST['edit_user_password'] ?? '';
        $rol = $_POST['edit_user_rol'] ?? 'editor';
        $telefono = trim($_POST['edit_user_telefono'] ?? '');
        $activo = isset($_POST['edit_user_activo']) ? 1 : 0;
        $idioma = $_POST['edit_user_idioma'] ?? 'ca';
        $notif_email = isset($_POST['edit_user_notificaciones_email']) ? 1 : 0;

        if ($id && $nom && $apellidos && $email && filter_var($email, FILTER_VALIDATE_EMAIL)) {
            // Load existing user to preserve fields we don't overwrite (notably password_hash)
            $usersModel->id_usuario = $id;
            if (!$usersModel->llegirPerId()) {
                header('Location: configuracion.php?user_error=notfound');
                exit;
            }

            // Check if email is used by another user
            $row = $usersModel->buscarPerEmail($email);
            if ($row && intval($row['id_usuario']) !== intval($id)) {
                header('Location: configuracion.php?user_error=email_exists');
                exit;
            }

            // Security check: Admin cannot edit Superadmin or promote to Superadmin
            $currentUserRole = $_SESSION['user_role'] ?? 'editor';
            if ($currentUserRole === 'admin') {
                // Check if target user is superadmin
                if (($usersModel->rol ?? '') === 'superadmin') {
                    header('Location: configuracion.php?user_error=permisos');
                    exit;
                }
                // Check if trying to set role to superadmin
                if ($rol === 'superadmin') {
                    header('Location: configuracion.php?user_error=permisos');
                    exit;
                }
            }

            // Apply updates
            $usersModel->nombre = $nom;
            $usersModel->apellidos = $apellidos;
            $usersModel->email = $email;
            $usersModel->rol = $rol;
            // Optional fields
            $usersModel->telefono = $telefono;
            $usersModel->activo = $activo;
            $usersModel->idioma = $idioma;
            $usersModel->notificaciones_email = $notif_email;

            if (!empty($password)) {
                // Hash and set password_hash so actualitzar() will persist it
                $usersModel->password_hash = password_hash($password, PASSWORD_DEFAULT);
            }

            $res = $usersModel->actualitzar();
            if ($res) {
                header('Location: configuracion.php?user_edited=1');
                exit;
            }
        }
        header('Location: configuracion.php?user_error=edit');
        exit;
    }

    // 3) Eliminar usuari
    if (isset($_POST['delete_user']) && $usersModel) {
        $id = intval($_POST['delete_user_id'] ?? 0);
        if ($id) {
            $usersModel->id_usuario = $id;
            
            // Security check: Admin cannot delete Superadmin
            $currentUserRole = $_SESSION['user_role'] ?? 'editor';
            if ($currentUserRole === 'admin') {
                if ($usersModel->llegirPerId()) {
                    if (($usersModel->rol ?? '') === 'superadmin') {
                        header('Location: configuracion.php?user_error=permisos');
                        exit;
                    }
                }
            }

            $res = $usersModel->eliminar(true); // hard delete
            if ($res) {
                header('Location: configuracion.php?user_deleted=1');
                exit;
            }
        }
        header('Location: configuracion.php?user_error=delete');
        exit;
    }

    // 4) Altres submit: mantenir el comportament existent (guardar configuració)
    $_SESSION['config_saved'] = true;
    header('Location: configuracion.php?saved=1');
    exit;
}

$saved = isset($_GET['saved']) && $_GET['saved'] == '1';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuración - Panel de Control</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@7.0.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Libre+Baskerville:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="css/configuracion.css">
    <link rel="stylesheet" href="css/configuracion-psicologa.css">
</head>
<body>
    <link rel="icon" type="image/png" sizes="32x32" href="../img/Logo32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="../img/Logo16.png">
    <?php 
    include 'includes/sidebar.php'; 
    // Flash messages: map common GET params to human messages and show once.
    $flash = [];
    if (isset($_GET['user_created'])) $flash[] = ['type' => 'success', 'text' => 'Usuari creat correctament.'];
    if (isset($_GET['user_edited'])) $flash[] = ['type' => 'success', 'text' => 'Usuari actualitzat.'];
    if (isset($_GET['user_deleted'])) $flash[] = ['type' => 'success', 'text' => 'Usuari eliminat.'];
    if (isset($_GET['user_error'])) {
        $msg = htmlspecialchars($_GET['user_error']);
        if ($_GET['user_error'] === 'permisos') $msg = 'No tens permisos per realitzar aquesta acció sobre un Superadmin.';
        $flash[] = ['type' => 'danger', 'text' => 'Error en operació d\'usuari: ' . $msg];
    }
    // Tarifes flash messages removed
    if (isset($_GET['error']) && $_GET['error'] === 'csrf') $flash[] = ['type' => 'danger', 'text' => 'Error de seguretat (CSRF).'];
    if (!empty($flash)) {
        echo '<div class="flash-wrapper" style="padding:12px;max-width:1100px;margin:12px auto;">';
        foreach ($flash as $f) {
            $cls = $f['type'] === 'success' ? 'flash-success' : 'flash-danger';
            echo '<div class="flash ' . $cls . '" style="padding:10px 14px;border-radius:6px;margin-bottom:8px;">' . htmlspecialchars($f['text']) . '</div>';
        }
        echo '</div>';
    }

    // Psicòloga flash messages
    if (isset($_GET['psicologa_created'])) {
        echo '<div class="flash-wrapper"><div class="flash flash-success">Informació de la psicòloga creada.</div></div>';
    }
    if (isset($_GET['psicologa_edited'])) {
        echo '<div class="flash-wrapper"><div class="flash flash-success">Informació de la psicòloga actualitzada.</div></div>';
    }
    if (isset($_GET['psicologa_error'])) {
        echo '<div class="flash-wrapper"><div class="flash flash-danger">Error psicòloga: ' . htmlspecialchars($_GET['psicologa_error']) . '</div></div>';
    }
    ?>

    <!-- Main Content: només gestió d'usuaris -->
    <div class="main-content">
        <header class="top-bar">
            <div class="top-bar-left">
                <h1>Configuraciones generales de la web</h1>
            </div>
        </header>
    <div class="content-wrapper" style="margin-top:32px;">
        <section class="card">
            <div class="config-section">
                <div class="card-header">
                    <h2><i class="fas fa-users-cog"></i> Usuarios del sistema</h2>
                </div>
                <?php $users = $usersModel->llistar([], 200, 0); ?>
                <script>
                // Prepare user data for JS
                var usersData = {};
                <?php if ($users): foreach ($users as $u): ?>
                usersData[<?php echo $u['id_usuario']; ?>] = {
                    id: <?php echo $u['id_usuario']; ?>,
                    nombre: <?php echo json_encode($u['nombre']); ?>,
                    apellidos: <?php echo json_encode($u['apellidos']); ?>,
                    email: <?php echo json_encode($u['email']); ?>,
                    rol: <?php echo json_encode($u['rol']); ?>,
                    telefono: <?php echo json_encode($u['telefono'] ?? ''); ?>,
                    activo: <?php echo (!empty($u['activo']) ? 'true' : 'false'); ?>,
                    idioma: <?php echo json_encode($u['idioma'] ?? 'ca'); ?>,
                    notificaciones_email: <?php echo (!empty($u['notificaciones_email']) ? 'true' : 'false'); ?>
                };
                <?php endforeach; endif; ?>

                function openEditUserModal(id) {
                    var u = usersData[id];
                    if (!u) return;
                    document.getElementById('edit_user_id').value = u.id;
                    document.getElementById('edit_user_nombre').value = u.nombre;
                    document.getElementById('edit_user_apellidos').value = u.apellidos;
                    document.getElementById('edit_user_email').value = u.email;
                    document.getElementById('edit_user_rol').value = u.rol;
                    document.getElementById('edit_user_telefono').value = u.telefono || '';
                    document.getElementById('edit_user_activo').checked = !!u.activo;
                    document.getElementById('edit_user_idioma').value = u.idioma || 'ca';
                    document.getElementById('edit_user_notificaciones_email').checked = !!u.notificaciones_email;
                    document.getElementById('edit_user_password').value = '';
                    hideAllModals();
                    showModal('modalEditUser');
                }

                function openDeleteUserModal(id) {
                    document.getElementById('delete_user_id').value = id;
                    hideAllModals();
                    showModal('modalDeleteUser');
                }
                </script>
                <table class="table users-table" style="width:100%;margin-bottom:24px;">
                    <thead>
                        <tr><th>Nombre</th><th>Email</th><th>Rol</th><th>Activo</th><th>Acciones</th></tr>
                    </thead>
                    <tbody>
                        <?php 
                        $currentUserRole = $_SESSION['user_role'] ?? 'editor';
                        if ($users): foreach ($users as $u): 
                            $isSuperAdminTarget = ($u['rol'] === 'superadmin');
                            $canEdit = true;
                            if ($currentUserRole === 'admin' && $isSuperAdminTarget) {
                                $canEdit = false;
                            }
                        ?>
                            <tr>
                                <td><?php echo htmlspecialchars($u['nombre'] . ' ' . $u['apellidos']); ?></td>
                                <td><?php echo htmlspecialchars($u['email']); ?></td>
                                <td><?php echo htmlspecialchars($u['rol']); ?></td>
                                <td><input type="checkbox" <?php echo $u['activo'] ? 'checked' : ''; ?> disabled></td>
                                <td>
                                    <?php if ($canEdit): ?>
                                    <button type="button" class="btn btn-secondary" onclick="openEditUserModal(<?php echo $u['id_usuario']; ?>)">Editar</button>
                                    <button type="button" class="btn btn-danger" onclick="openDeleteUserModal(<?php echo $u['id_usuario']; ?>)">Eliminar</button>
                                    <?php else: ?>
                                    <span style="color:#999;font-size:0.9em;font-style:italic;">Protegit</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; else: ?>
                            <tr><td colspan="5">No hay usuarios registrados.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
                <button type="button" class="btn btn-primary" onclick="openAddUserModal()">
                    <i class="fas fa-user-plus"></i> Añadir usuario
                </button>
            </div>
            <div class="config-section" style="margin-top:32px;">
                <div class="card-header">
                    <h2><i class="fas fa-user"></i> Datos personales y de contacto</h2>
                </div>
                <!-- Psicòloga info: si existeix, mostrar resum i botó editar; si no, botó per afegir -->
                <div style="margin-top:18px; padding:14px; border:1px dashed #e6e6e6; border-radius:6px;">
                    <h3 style="margin:0 0 8px 0;">Información de contacto e identificación</h3>
                    <?php if ($psicologa): ?>
                        <p style="margin:0 0 8px 0;"><strong><?php echo htmlspecialchars($psicologa['nom_complet_ca']); ?></strong> — <?php echo htmlspecialchars($psicologa['titulacio_ca']); ?></p>
                        <p style="margin:0 0 8px 0;">Col·legiat: <?php echo htmlspecialchars($psicologa['num_collegiat']); ?> (<?php echo htmlspecialchars($psicologa['college_professional']); ?>)</p>
                        <div style="display:flex;gap:8px;margin-top:8px;"><button class="btn btn-secondary" type="button" onclick="openEditPsicologaModal(<?php echo (int)$psicologa['id_info']; ?>)"><i class="fas fa-edit"></i> Editar perfil</button></div>
                    <?php else: ?>
                        <p style="margin:0 0 8px 0;">No hay información de contacto e identificación. Crea el registro con los datos básicos.</p>
                        <div style="display:flex;gap:8px;margin-top:8px;"><button class="btn btn-primary" type="button" onclick="openAddPsicologaModal()"><i class="fas fa-plus"></i> Añadir perfil</button></div>
                    <?php endif; ?>
                </div>
            </div>
                <!-- Modal add user -->
                <!-- Modal edit user -->
                <div id="modalEditUser" class="modal" style="display:none;">
                    <div class="modal-content" style="width:100%;max-width:500px;box-sizing:border-box;">
                        <span class="close" onclick="hideAllModals()">&times;</span>
                        <form method="POST" action="configuracion.php">
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                            <input type="hidden" name="edit_user" value="1">
                            <input type="hidden" name="edit_user_id" id="edit_user_id">
                            <h3 style="margin-bottom:18px;">Editar usuario</h3>
                            <div class="form-grid" style="display:flex;flex-wrap:wrap;gap:16px;">
                                <div class="form-group" style="flex:1 1 220px;min-width:0;">
                                    <label for="edit_user_nombre">Nombre</label>
                                    <input type="text" id="edit_user_nombre" name="edit_user_nombre" required style="width:100%;box-sizing:border-box;">
                                </div>
                                <div class="form-group" style="flex:1 1 220px;min-width:0;">
                                    <label for="edit_user_apellidos">Apellidos</label>
                                    <input type="text" id="edit_user_apellidos" name="edit_user_apellidos" required style="width:100%;box-sizing:border-box;">
                                </div>
                                <div class="form-group" style="flex:1 1 220px;min-width:0;">
                                    <label for="edit_user_email">Email</label>
                                    <input type="email" id="edit_user_email" name="edit_user_email" required style="width:100%;box-sizing:border-box;">
                                </div>
                                <div class="form-group" style="flex:1 1 220px;min-width:0;">
                                    <label for="edit_user_password">Contraseña (dejar en blanco para no cambiar)</label>
                                    <input type="password" id="edit_user_password" name="edit_user_password" style="width:100%;box-sizing:border-box;">
                                </div>
                                <div class="form-group" style="flex:1 1 220px;min-width:0;">
                                    <label for="edit_user_telefono">Teléfono</label>
                                    <input type="tel" id="edit_user_telefono" name="edit_user_telefono" style="width:100%;box-sizing:border-box;">
                                </div>
                                <div class="form-group" style="flex:1 1 220px;min-width:0;">
                                    <label for="edit_user_activo">Activo</label>
                                    <input type="checkbox" id="edit_user_activo" name="edit_user_activo" value="1" style="transform:scale(1.2);margin-left:8px;">
                                </div>
                                <div class="form-group" style="flex:1 1 220px;min-width:0;">
                                    <label for="edit_user_idioma">Idioma</label>
                                    <select id="edit_user_idioma" name="edit_user_idioma" style="width:100%;box-sizing:border-box;">
                                        <option value="ca">Català</option>
                                        <option value="es">Español</option>
                                        <option value="en">English</option>
                                    </select>
                                </div>
                                <div class="form-group" style="flex:1 1 220px;min-width:0;">
                                    <label for="edit_user_notificaciones_email">Notificaciones email</label>
                                    <input type="checkbox" id="edit_user_notificaciones_email" name="edit_user_notificaciones_email" value="1" style="transform:scale(1.2);margin-left:8px;">
                                </div>
                                <div class="form-group" style="flex:1 1 220px;min-width:0;">
                                    <label for="edit_user_rol">Rol</label>
                                    <select id="edit_user_rol" name="edit_user_rol" style="width:100%;box-sizing:border-box;">
                                        <?php if ($currentUserRole === 'superadmin'): ?>
                                        <option value="superadmin">Superadmin</option>
                                        <?php endif; ?>
                                        <option value="admin">Admin</option>
                                        <option value="editor">Editor</option>
                                        <option value="seo_manager">SEO Manager</option>
                                        <option value="viewer">Viewer</option>
                                    </select>
                                </div>
                            </div>
                            <div style="margin-top:12px; display:flex; gap:10px; justify-content:flex-end;">
                                <button type="submit" class="btn btn-save">Guardar cambios</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Modal delete user -->
                <div id="modalDeleteUser" class="modal" style="display:none;">
                    <div class="modal-content" style="width:100%;max-width:400px;box-sizing:border-box;">
                        <span class="close" onclick="hideAllModals()">&times;</span>
                        <form method="POST" action="configuracion.php">
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                            <input type="hidden" name="delete_user" value="1">
                            <input type="hidden" name="delete_user_id" id="delete_user_id">
                            <h3>Eliminar usuario</h3>
                            <p>¿Estás seguro de que quieres eliminar este usuario? Esta acción no se puede deshacer.</p>
                            <div style="margin-top:18px; display:flex; gap:10px; justify-content:flex-end;">
                                <button type="submit" class="btn btn-danger">Eliminar</button>
                                <button type="button" class="btn btn-secondary" onclick="hideAllModals()">Cancelar</button>
                            </div>
                        </form>
                    </div>
                </div>
                <div id="modalAddUser" class="modal" style="display:none;">
                    <div class="modal-content" style="width:100%;max-width:500px;box-sizing:border-box;">
                        <span class="close" onclick="hideAllModals()">&times;</span>
                        <form method="POST" action="configuracion.php">
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                            <input type="hidden" name="create_user" value="1">
                            <h3 style="margin-bottom:18px;">Afegir usuari</h3>
                            <div class="form-grid" style="display:flex;flex-wrap:wrap;gap:16px;">
                                <div class="form-group" style="flex:1 1 220px;min-width:0;">
                                    <label for="user_nombre">Nom</label>
                                    <input type="text" id="user_nombre" name="user_nombre" required style="width:100%;box-sizing:border-box;">
                                </div>
                                <div class="form-group" style="flex:1 1 220px;min-width:0;">
                                    <label for="user_apellidos">Cognoms</label>
                                    <input type="text" id="user_apellidos" name="user_apellidos" required style="width:100%;box-sizing:border-box;">
                                </div>
                                <div class="form-group" style="flex:1 1 220px;min-width:0;">
                                    <label for="user_email">Email</label>
                                    <input type="email" id="user_email" name="user_email" required style="width:100%;box-sizing:border-box;">
                                </div>
                                <div class="form-group" style="flex:1 1 220px;min-width:0;">
                                    <label for="user_password">Contrasenya</label>
                                    <input type="password" id="user_password" name="user_password" required style="width:100%;box-sizing:border-box;">
                                </div>
                                <div class="form-group" style="flex:1 1 220px;min-width:0;">
                                    <label for="user_rol">Rol</label>
                                    <select id="user_rol" name="user_rol" style="width:100%;box-sizing:border-box;">
                                        <?php if ($currentUserRole === 'superadmin'): ?>
                                        <option value="superadmin">Superadmin</option>
                                        <?php endif; ?>
                                        <option value="admin">Admin</option>
                                        <option value="editor">Editor</option>
                                        <option value="seo_manager">SEO Manager</option>
                                        <option value="viewer">Viewer</option>
                                    </select>
                                </div>
                                <div class="form-group" style="flex:1 1 220px;min-width:0;">
                                    <label for="user_telefono">Telèfon</label>
                                    <input type="tel" id="user_telefono" name="user_telefono" style="width:100%;box-sizing:border-box;">
                                </div>
                                <div class="form-group" style="flex:1 1 220px;min-width:0;">
                                    <label for="user_idioma">Idioma</label>
                                    <select id="user_idioma" name="user_idioma" style="width:100%;box-sizing:border-box;">
                                        <option value="ca">Català</option>
                                        <option value="es">Español</option>
                                        <option value="en">English</option>
                                    </select>
                                </div>
                                <div class="form-group" style="flex:1 1 220px;min-width:0;">
                                    <label for="user_notificaciones_email">Notificacions email</label>
                                    <input type="checkbox" id="user_notificaciones_email" name="user_notificaciones_email" value="1" style="transform:scale(1.2);margin-left:8px;">
                                </div>
                            </div>
                            <div style="margin-top:12px; display:flex; gap:10px; justify-content:flex-end;">
                                <button type="submit" class="btn btn-save">Crear usuari</button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Modal Add Psicòloga -->
                <div id="modalAddPsicologa" class="modal" style="display:none;">
                    <div class="modal-content" style="width:100%;max-width:700px;box-sizing:border-box;">
                        <span class="close" onclick="hideAllModals()">&times;</span>
                        <form method="POST" action="configuracion.php">
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                            <input type="hidden" name="create_psicologa" value="1">
                            <h3 style="margin-bottom:18px;">Añadir información de la psicóloga</h3>
                            <div class="psicologa-form">
                                <div class="psicologa-avatar">
                                    <div style="font-weight:700;color:#333;margin-bottom:6px;">Foto</div>
                                    <div id="foto_perfil_preview"><img id="foto_perfil_img_preview" src="<?php echo htmlspecialchars($psicologa_old['foto_perfil'] ?? ($psicologa['foto_perfil'] ?? '')); ?>" alt="Foto perfil" style="max-width:120px;max-height:120px;display:<?php echo (!empty($psicologa_old['foto_perfil'] ?? ($psicologa['foto_perfil'] ?? '')) ? 'block' : 'none'); ?>;border-radius:8px;object-fit:cover;border:1px solid #eee;padding:4px;background:#fff;"></div>
                                    <select id="foto_perfil" name="foto_perfil" style="width:100%;box-sizing:border-box;">
                                        <option value="">-- Cap imatge (deixar en blanc) --</option>
                                        <?php foreach ($available_images as $img_url => $img_name): ?>
                                        <option value="<?php echo htmlspecialchars($img_url); ?>" <?php echo (isset($psicologa_old['foto_perfil']) && $psicologa_old['foto_perfil'] === $img_url) ? 'selected' : ''; ?>><?php echo htmlspecialchars($img_name); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="psicologa-fields">
                                    <div class="psicologa-row"><label for="nom_complet_ca">Nom (CA)</label><input type="text" id="nom_complet_ca" name="nom_complet_ca" required value="<?php echo htmlspecialchars($psicologa_old['nom_complet_ca'] ?? ''); ?>"></div>
                                    <div class="psicologa-row"><label for="nom_complet_es">Nom (ES)</label><input type="text" id="nom_complet_es" name="nom_complet_es" required value="<?php echo htmlspecialchars($psicologa_old['nom_complet_es'] ?? ''); ?>"></div>
                                    <div class="psicologa-row"><label for="titulacio_ca">Titulació (CA)</label><input type="text" id="titulacio_ca" name="titulacio_ca" required value="<?php echo htmlspecialchars($psicologa_old['titulacio_ca'] ?? ''); ?>"></div>
                                    <div class="psicologa-row"><label for="titulacio_es">Titulació (ES)</label><input type="text" id="titulacio_es" name="titulacio_es" required value="<?php echo htmlspecialchars($psicologa_old['titulacio_es'] ?? ''); ?>"></div>
                                    <div class="psicologa-row"><label for="alt_foto_ca">Alt foto (CA)</label><input type="text" id="alt_foto_ca" name="alt_foto_ca" value="<?php echo htmlspecialchars($psicologa_old['alt_foto_ca'] ?? ''); ?>"></div>
                                    <div class="psicologa-row"><label for="alt_foto_es">Alt foto (ES)</label><input type="text" id="alt_foto_es" name="alt_foto_es" value="<?php echo htmlspecialchars($psicologa_old['alt_foto_es'] ?? ''); ?>"></div>
                                    <div class="psicologa-row"><label for="email_professional">Email professional</label><input type="email" id="email_professional" name="email_professional" value="<?php echo htmlspecialchars($psicologa_old['email_professional'] ?? ''); ?>"></div>
                                    <div class="psicologa-row"><label for="telefon_professional">Telèfon</label><input type="tel" id="telefon_professional" name="telefon_professional" value="<?php echo htmlspecialchars($psicologa_old['telefon_professional'] ?? ''); ?>"></div>
                                    <div class="psicologa-row"><label for="linkedin_url">LinkedIn</label><input type="text" id="linkedin_url" name="linkedin_url" value="<?php echo htmlspecialchars($psicologa_old['linkedin_url'] ?? ''); ?>"></div>
                                    <div class="psicologa-row"><label for="instagram_professional">Instagram</label><input type="text" id="instagram_professional" name="instagram_professional" value="<?php echo htmlspecialchars($psicologa_old['instagram_professional'] ?? ''); ?>"></div>
                                    <div class="psicologa-row"><label for="num_collegiat">Num. col·legiat</label><input type="text" id="num_collegiat" name="num_collegiat" required value="<?php echo htmlspecialchars($psicologa_old['num_collegiat'] ?? ''); ?>"></div>
                                    <div class="psicologa-row"><label for="college_professional">Col·legi</label><input type="text" id="college_professional" name="college_professional" required value="<?php echo htmlspecialchars($psicologa_old['college_professional'] ?? ''); ?>"></div>
                                </div>
                            </div>
                            <div style="margin-top:12px; display:flex; gap:10px; justify-content:flex-end;">
                                <button type="submit" class="btn btn-save">Crear</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Modal Edit Psicòloga -->
                <div id="modalEditPsicologa" class="modal" style="display:none;">
                    <div class="modal-content" style="width:100%;max-width:700px;box-sizing:border-box;">
                        <span class="close" onclick="hideAllModals()">&times;</span>
                        <form method="POST" action="configuracion.php">
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                            <input type="hidden" name="edit_psicologa" value="1">
                            <input type="hidden" name="edit_psicologa_id" id="edit_psicologa_id">
                            <h3 style="margin-bottom:18px;">Editar información de la psicóloga</h3>
                            <div class="psicologa-form">
                                <div class="psicologa-avatar">
                                    <div style="font-weight:700;color:#333;margin-bottom:6px;">Foto</div>
                                    <div id="edit_foto_perfil_preview"><img id="edit_foto_perfil_img_preview" src="<?php echo htmlspecialchars($psicologa_old['foto_perfil'] ?? ($psicologa['foto_perfil'] ?? '')); ?>" alt="Foto perfil" style="max-width:120px;max-height:120px;display:<?php echo (!empty($psicologa_old['foto_perfil'] ?? ($psicologa['foto_perfil'] ?? '')) ? 'block' : 'none'); ?>;border-radius:8px;object-fit:cover;border:1px solid #eee;padding:4px;background:#fff;"></div>
                                    <select id="edit_foto_perfil" name="foto_perfil" style="width:100%;box-sizing:border-box;">
                                        <option value="">-- Cap imatge (deixar en blanc) --</option>
                                        <?php foreach ($available_images as $img_url => $img_name): ?>
                                        <option value="<?php echo htmlspecialchars($img_url); ?>" <?php echo ((isset($psicologa_old['foto_perfil']) && $psicologa_old['foto_perfil'] === $img_url) || (empty($psicologa_old) && isset($psicologa['foto_perfil']) && $psicologa['foto_perfil'] === $img_url)) ? 'selected' : ''; ?>><?php echo htmlspecialchars($img_name); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="psicologa-fields">
                                    <div class="psicologa-row"><label for="edit_nom_complet_ca">Nom (CA)</label><input type="text" id="edit_nom_complet_ca" name="nom_complet_ca" required value="<?php echo htmlspecialchars($psicologa_old['nom_complet_ca'] ?? ($psicologa['nom_complet_ca'] ?? '')); ?>"></div>
                                    <div class="psicologa-row"><label for="edit_nom_complet_es">Nom (ES)</label><input type="text" id="edit_nom_complet_es" name="nom_complet_es" required value="<?php echo htmlspecialchars($psicologa_old['nom_complet_es'] ?? ($psicologa['nom_complet_es'] ?? '')); ?>"></div>
                                    <div class="psicologa-row"><label for="edit_titulacio_ca">Titulació (CA)</label><input type="text" id="edit_titulacio_ca" name="titulacio_ca" required value="<?php echo htmlspecialchars($psicologa_old['titulacio_ca'] ?? ($psicologa['titulacio_ca'] ?? '')); ?>"></div>
                                    <div class="psicologa-row"><label for="edit_titulacio_es">Titulació (ES)</label><input type="text" id="edit_titulacio_es" name="titulacio_es" required value="<?php echo htmlspecialchars($psicologa_old['titulacio_es'] ?? ($psicologa['titulacio_es'] ?? '')); ?>"></div>
                                    <div class="psicologa-row"><label for="edit_alt_foto_ca">Alt foto (CA)</label><input type="text" id="edit_alt_foto_ca" name="alt_foto_ca" value="<?php echo htmlspecialchars($psicologa_old['alt_foto_ca'] ?? ($psicologa['alt_foto_ca'] ?? '')); ?>"></div>
                                    <div class="psicologa-row"><label for="edit_alt_foto_es">Alt foto (ES)</label><input type="text" id="edit_alt_foto_es" name="alt_foto_es" value="<?php echo htmlspecialchars($psicologa_old['alt_foto_es'] ?? ($psicologa['alt_foto_es'] ?? '')); ?>"></div>
                                    <div class="psicologa-row"><label for="edit_email_professional">Email professional</label><input type="email" id="edit_email_professional" name="email_professional" value="<?php echo htmlspecialchars($psicologa_old['email_professional'] ?? ($psicologa['email_professional'] ?? '')); ?>"></div>
                                    <div class="psicologa-row"><label for="edit_telefon_professional">Telèfon</label><input type="tel" id="edit_telefon_professional" name="telefon_professional" value="<?php echo htmlspecialchars($psicologa_old['telefon_professional'] ?? ($psicologa['telefon_professional'] ?? '')); ?>"></div>
                                    <div class="psicologa-row"><label for="edit_linkedin_url">LinkedIn</label><input type="text" id="edit_linkedin_url" name="linkedin_url" value="<?php echo htmlspecialchars($psicologa_old['linkedin_url'] ?? ($psicologa['linkedin_url'] ?? '')); ?>"></div>
                                    <div class="psicologa-row"><label for="edit_instagram_professional">Instagram</label><input type="text" id="edit_instagram_professional" name="instagram_professional" value="<?php echo htmlspecialchars($psicologa_old['instagram_professional'] ?? ($psicologa['instagram_professional'] ?? '')); ?>"></div>
                                    <div class="psicologa-row"><label for="edit_num_collegiat">Num. col·legiat</label><input type="text" id="edit_num_collegiat" name="num_collegiat" required value="<?php echo htmlspecialchars($psicologa_old['num_collegiat'] ?? ($psicologa['num_collegiat'] ?? '')); ?>"></div>
                                    <div class="psicologa-row"><label for="edit_college_professional">Col·legi</label><input type="text" id="edit_college_professional" name="college_professional" required value="<?php echo htmlspecialchars($psicologa_old['college_professional'] ?? ($psicologa['college_professional'] ?? '')); ?>"></div>
                                </div>
                            </div>
                            <div style="margin-top:12px; display:flex; gap:10px; justify-content:flex-end;">
                                <button type="submit" class="btn btn-save">Guardar cambios</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </section>
    </div>
    <script src="js/dashboard.js"></script>
    <script>
        function openAddUserModal() {
                        document.getElementById('user_nombre').value = '';
                        document.getElementById('user_apellidos').value = '';
                        document.getElementById('user_email').value = '';
                        document.getElementById('user_password').value = '';
                        document.getElementById('user_rol').value = 'editor';
                        document.getElementById('user_telefono').value = '';
                        document.getElementById('user_idioma').value = 'ca';
                        document.getElementById('user_notificaciones_email').checked = true;
                        hideAllModals();
                        showModal('modalAddUser');
                    }
        function switchTab(tab) {
            document.querySelectorAll('.tab-content').forEach(t => t.classList.remove('active'));
            document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
            const tabContent = document.getElementById(tab + '-tab');
            if (tabContent) tabContent.classList.add('active');
            const btn = document.querySelector('.tab-btn[data-tab="' + tab + '"]');
            if (btn) btn.classList.add('active');
            const url = new URL(window.location);
            url.searchParams.set('tab', tab);
            window.history.pushState({}, '', url);
        }
        document.addEventListener('DOMContentLoaded', function() {
            // Inicialitza el tab actiu segons la URL
            const params = new URLSearchParams(window.location.search);
            const tab = params.get('tab') || 'users';
            switchTab(tab);
            // Event listeners per als botons de pestanya
            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    const tab = this.getAttribute('data-tab');
                    switchTab(tab);
                });
            });
            // If server returned a psicologa error and indicated which action, reopen modal and show errors
            var serverAction = <?php echo json_encode($_GET['action'] ?? ''); ?>;
            var serverError = <?php echo json_encode($_GET['psicologa_error'] ?? ''); ?>;
            if (serverError) {
                if (serverAction === 'edit') {
                    // populate edit errors box and open edit modal
                    var el = document.getElementById('editPsicologaErrors');
                    if (el) { el.style.display = 'block'; el.innerText = serverError; el.style.color = '#6b0f0f'; el.style.background = '#ffe6e6'; el.style.padding = '8px'; el.style.border = '1px solid #f3b7b7'; }
                    // ensure the edit modal is shown
                    hideAllModals();
                    showModal('modalEditPsicologa');
                } else {
                    var el2 = document.getElementById('addPsicologaErrors');
                    if (el2) { el2.style.display = 'block'; el2.innerText = serverError; el2.style.color = '#6b0f0f'; el2.style.background = '#ffe6e6'; el2.style.padding = '8px'; el2.style.border = '1px solid #f3b7b7'; }
                    hideAllModals();
                    showModal('modalAddPsicologa');
                }
            }
        });
        // Modal helpers: ensure only one modal visible at once
        function hideAllModals() {
            document.querySelectorAll('.modal').forEach(function(m){
                if (m && m.style) m.style.display = 'none';
            });
            // restore body scroll
            document.body.style.overflow = '';
        }
        function showModal(id) {
            hideAllModals();
            var el = document.getElementById(id);
            if (el) {
                // use flex so the CSS centering for .modal (display:flex) applies
                el.style.display = 'flex';
                // prevent body scroll while modal open
                document.body.style.overflow = 'hidden';
            }
        }

        // Close modal when clicking on the overlay (outside .modal-content).
        // Attach a click listener to each modal that closes it only when the
        // click target is the modal element itself (the overlay). This avoids
        // the race where clicking an "Open" button (outside the modal) opens
        // the modal and then the global document click handler immediately
        // closes it because the click event bubbles.
        document.querySelectorAll('.modal').forEach(function(m){
            m.addEventListener('click', function(e){
                if (e.target === m) {
                    m.style.display = 'none';
                    document.body.style.overflow = '';
                }
            });
        });

        // Psicòloga data for JS (single record expected)
        var psicologaData = {};
        <?php if ($psicologa): ?>
        psicologaData[<?php echo (int)$psicologa['id_info']; ?>] = {
            id: <?php echo (int)$psicologa['id_info']; ?>,
            nom_complet_ca: <?php echo json_encode($psicologa['nom_complet_ca']); ?>,
            nom_complet_es: <?php echo json_encode($psicologa['nom_complet_es']); ?>,
            titulacio_ca: <?php echo json_encode($psicologa['titulacio_ca']); ?>,
            titulacio_es: <?php echo json_encode($psicologa['titulacio_es']); ?>,
            foto_perfil: <?php echo json_encode($psicologa['foto_perfil']); ?>,
            alt_foto_ca: <?php echo json_encode($psicologa['alt_foto_ca']); ?>,
            alt_foto_es: <?php echo json_encode($psicologa['alt_foto_es']); ?>,
            email_professional: <?php echo json_encode($psicologa['email_professional']); ?>,
            telefon_professional: <?php echo json_encode($psicologa['telefon_professional']); ?>,
            linkedin_url: <?php echo json_encode($psicologa['linkedin_url']); ?>,
            instagram_professional: <?php echo json_encode($psicologa['instagram_professional']); ?>,
            num_collegiat: <?php echo json_encode($psicologa['num_collegiat']); ?>,
            college_professional: <?php echo json_encode($psicologa['college_professional']); ?>
        };
        <?php endif; ?>

        function openAddPsicologaModal() {
            // clear fields
            var ids = ['nom_complet_ca','nom_complet_es','titulacio_ca','titulacio_es','foto_perfil','alt_foto_ca','alt_foto_es','email_professional','telefon_professional','linkedin_url','instagram_professional','num_collegiat','college_professional'];
            ids.forEach(function(id){ var el = document.getElementById(id); if(el) el.value = ''; });
            // update preview for cleared select
            try { updatePreview('foto_perfil','foto_perfil_preview'); } catch(e){}
            hideAllModals();
            showModal('modalAddPsicologa');
        }

        function openEditPsicologaModal(id) {
            var p = psicologaData[id];
            if (!p) return;
            document.getElementById('edit_psicologa_id').value = p.id;
            document.getElementById('edit_nom_complet_ca').value = p.nom_complet_ca || '';
            document.getElementById('edit_nom_complet_es').value = p.nom_complet_es || '';
            document.getElementById('edit_titulacio_ca').value = p.titulacio_ca || '';
            document.getElementById('edit_titulacio_es').value = p.titulacio_es || '';
            document.getElementById('edit_foto_perfil').value = p.foto_perfil || '';
            document.getElementById('edit_alt_foto_ca').value = p.alt_foto_ca || '';
            document.getElementById('edit_alt_foto_es').value = p.alt_foto_es || '';
            document.getElementById('edit_email_professional').value = p.email_professional || '';
            document.getElementById('edit_telefon_professional').value = p.telefon_professional || '';
            document.getElementById('edit_linkedin_url').value = p.linkedin_url || '';
            document.getElementById('edit_instagram_professional').value = p.instagram_professional || '';
            document.getElementById('edit_num_collegiat').value = p.num_collegiat || '';
            document.getElementById('edit_college_professional').value = p.college_professional || '';
            // update preview for selected image in edit modal
            try { updatePreview('edit_foto_perfil','edit_foto_perfil_preview'); } catch(e){}
            hideAllModals();
            showModal('modalEditPsicologa');
        }

        // Client-side validation to show field-level errors before submit
        (function(){
            var addForm = document.querySelector('#modalAddPsicologa form');
            if (addForm) {
                addForm.addEventListener('submit', function(e){
                    var errors = [];
                    var required = ['nom_complet_ca','nom_complet_es','titulacio_ca','titulacio_es','num_collegiat','college_professional'];
                    required.forEach(function(id){
                        var el = document.getElementById(id);
                        if (!el || !el.value.trim()) errors.push(el ? (el.previousElementSibling ? el.previousElementSibling.innerText + ' és obligatori' : id + ' required') : id + ' missing');
                    });
                    var email = document.getElementById('email_professional');
                    if (email && email.value.trim()) {
                        var re = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@(([^<>()[\]\\.,;:\s@\"]+\.)+[^<>()[\]\\.,;:\s@\"]{2,})$/i;
                        if (!re.test(email.value.trim())) errors.push('Email professional no té un format vàlid');
                    }
                    if (errors.length) {
                        e.preventDefault();
                        var box = document.getElementById('addPsicologaErrors');
                        if (box) { box.style.display='block'; box.innerHTML = '<ul style="margin:0 0 0 18px;padding:6px 8px;">' + errors.map(function(x){return '<li>'+x+'</li>';}).join('') + '</ul>'; box.style.color='#6b0f0f'; box.style.background='#ffe6e6'; box.style.border='1px solid #f3b7b7'; }
                        // scroll to top of modal to show errors
                        var mc = document.querySelector('#modalAddPsicologa .modal-content'); if (mc) mc.scrollTop = 0;
                        return false;
                    }
                });
            }

            var editForm = document.querySelector('#modalEditPsicologa form');
            if (editForm) {
                editForm.addEventListener('submit', function(e){
                    var errors = [];
                    var required = ['edit_nom_complet_ca','edit_nom_complet_es','edit_titulacio_ca','edit_titulacio_es','edit_num_collegiat','edit_college_professional'];
                    required.forEach(function(id){
                        var el = document.getElementById(id);
                        if (!el || !el.value.trim()) errors.push(el ? (el.previousElementSibling ? el.previousElementSibling.innerText + ' és obligatori' : id + ' required') : id + ' missing');
                    });
                    var email = document.getElementById('edit_email_professional');
                    if (email && email.value.trim()) {
                        var re = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@(([^<>()[\]\\.,;:\s@\"]+\.)+[^<>()[\]\\.,;:\s@\"]{2,})$/i;
                        if (!re.test(email.value.trim())) errors.push('Email professional no té un format vàlid');
                    }
                    if (errors.length) {
                        e.preventDefault();
                        var box = document.getElementById('editPsicologaErrors');
                        if (box) { box.style.display='block'; box.innerHTML = '<ul style="margin:0 0 0 18px;padding:6px 8px;">' + errors.map(function(x){return '<li>'+x+'</li>';}).join('') + '</ul>'; box.style.color='#6b0f0f'; box.style.background='#ffe6e6'; box.style.border='1px solid #f3b7b7'; }
                        var mc = document.querySelector('#modalEditPsicologa .modal-content'); if (mc) mc.scrollTop = 0;
                        return false;
                    }
                });
            }
        })();

        // Preview helper for foto_perfil selects
        function updatePreview(selectId, previewId) {
            var sel = document.getElementById(selectId);
            var wrapper = document.getElementById(previewId);
            if (!sel || !wrapper) return;
            var img = wrapper.querySelector('img');
            if (!img) return;
            if (sel.value) {
                img.src = sel.value;
                img.style.display = 'block';
            } else {
                img.src = '';
                img.style.display = 'none';
            }
        }

        // Attach change listeners for preview update (elements exist because script is at bottom)
        (function(){
            var s = document.getElementById('foto_perfil');
            if (s) s.addEventListener('change', function(){ updatePreview('foto_perfil','foto_perfil_preview'); });
            var se = document.getElementById('edit_foto_perfil');
            if (se) se.addEventListener('change', function(){ updatePreview('edit_foto_perfil','edit_foto_perfil_preview'); });
            // If an image is already selected in edit modal data, populate its preview
            <?php if ($psicologa): ?>
            try { updatePreview('edit_foto_perfil','edit_foto_perfil_preview'); } catch(e) {}
            <?php endif; ?>
        })();
    </script>
</body>
</html>