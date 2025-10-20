<?php
session_start();

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
// Evitar problemes amb headers enviats abans (bufferització) i inicialitzar dependències
ob_start();

// Inicialitzar connexió i models anticipadament perquè el codi HTML/JS que segueix pugui usar
// les variables ($tarifes, $users) i perquè les crides a header() en el processament POST funcionin.
require_once __DIR__ . '/../classes/connexio.php';
require_once __DIR__ . '/../classes/usuaris_panell.php';
// Tarifes removed: tariff management was removed per request.

$db = null;
try {
    $db = Connexio::getInstance()->getConnexio();
} catch (Exception $e) {
    error_log('configuracion.php: no s\'ha pogut connectar a la BD: ' . $e->getMessage());
}

$usersModel = $db ? new UsuarisPanell($db) : null;

// Carregar llistes que s'utilitzen en el JS/HTML per evitar avisos
$users = $usersModel ? $usersModel->llistar([], 200, 0) : [];
// (tarifes removed)

$saved = isset($_GET['saved']) && $_GET['saved'] == '1';
// Ensure $config default exists to avoid undefined index notices. If your app
// loads a configuration array elsewhere, replace this initializer.
$config = $config ?? [
    'nombre' => '',
    'email' => '',
    'telefono' => '',
    'direccion' => '',
    'especialidades' => [],
    'idioma' => 'ca',
    'notificaciones_email' => 1,
];

// CSRF token simple (low-risk): generar si no existeix
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
}
$csrf_token = $_SESSION['csrf_token'];
/**
 * Configuració - Panel de Control
 *
 * Gestió d'usuaris i tarifes amb control d'accés igual que gseo.php
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

            // Apply updates
            $usersModel->nombre = $nom;
            $usersModel->apellidos = $apellidos;
            $usersModel->email = $email;
            $usersModel->rol = $rol;

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
    <style>
        /* Minimal flash styles */
        .flash-wrapper { max-width:1100px; margin:8px auto; }
        .flash { padding:10px 14px; border-radius:6px; font-weight:600; }
        .flash-success { background:#e6ffed; color:#0f5132; border:1px solid #b7f3c9; }
        .flash-danger { background:#ffe6e6; color:#6b0f0f; border:1px solid #f3b7b7; }
    </style>
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>

    <?php
    // Flash messages: map common GET params to human messages and show once.
    $flash = [];
    if (isset($_GET['user_created'])) $flash[] = ['type' => 'success', 'text' => 'Usuari creat correctament.'];
    if (isset($_GET['user_edited'])) $flash[] = ['type' => 'success', 'text' => 'Usuari actualitzat.'];
    if (isset($_GET['user_deleted'])) $flash[] = ['type' => 'success', 'text' => 'Usuari eliminat.'];
    if (isset($_GET['user_error'])) $flash[] = ['type' => 'danger', 'text' => 'Error en operació d\'usuari: ' . htmlspecialchars($_GET['user_error'])];
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
    ?>

    <!-- Main Content: només gestió d'usuaris -->
    <div class="main-content">
        <header class="top-bar">
            <div class="top-bar-left">
                <h1>Configuraciones generales de la web</h1>
            </div>
        </header>
    </div>
    <div class="content-wrapper" style="margin-top:32px;">
        <section class="card">
            <div class="card-header">
                <h2><i class="fas fa-users-cog"></i> Usuarios del sistema</h2>
            </div>
            <div class="config-section">
                <?php if (!$usersModel): ?>
                    <div class="alert alert-warning">La gestión de usuarios requiere conexión a base de datos. Revisa la configuración.</div>
                <?php else: ?>
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
                            <?php if ($users): foreach ($users as $u): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($u['nombre'] . ' ' . $u['apellidos']); ?></td>
                                    <td><?php echo htmlspecialchars($u['email']); ?></td>
                                    <td><?php echo htmlspecialchars($u['rol']); ?></td>
                                    <td><input type="checkbox" <?php echo $u['activo'] ? 'checked' : ''; ?> disabled></td>
                                    <td>
                                        <button type="button" class="btn btn-secondary" onclick="openEditUserModal(<?php echo $u['id_usuario']; ?>)">Editar</button>
                                        <button type="button" class="btn btn-danger" onclick="openDeleteUserModal(<?php echo $u['id_usuario']; ?>)">Eliminar</button>
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
                    </script>
                    <!-- Modal add user -->
                    <!-- Modal edit user -->
                    <div id="modalEditUser" class="modal" style="display:none;">
                        <div class="modal-content" style="width:100%;max-width:500px;box-sizing:border-box;">
                            <span class="close" onclick="document.getElementById('modalEditUser').style.display='none'">&times;</span>
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
                                        <label for="edit_user_notificaciones_email">Notificacions email</label>
                                        <input type="checkbox" id="edit_user_notificaciones_email" name="edit_user_notificaciones_email" value="1" style="transform:scale(1.2);margin-left:8px;">
                                    </div>
                                    <div class="form-group" style="flex:1 1 220px;min-width:0;">
                                        <label for="edit_user_rol">Rol</label>
                                        <select id="edit_user_rol" name="edit_user_rol" style="width:100%;box-sizing:border-box;">
                                            <option value="superadmin">Superadmin</option>
                                            <option value="admin">Admin</option>
                                            <option value="editor">Editor</option>
                                            <option value="seo_manager">SEO Manager</option>
                                            <option value="viewer">Viewer</option>
                                        </select>
                                    </div>
                                </div>
                                <div style="margin-top:12px; display:flex; gap:10px; justify-content:flex-end;">
                                    <button type="submit" class="btn btn-save">Desar canvis</button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Modal delete user -->
                    <div id="modalDeleteUser" class="modal" style="display:none;">
                        <div class="modal-content" style="width:100%;max-width:400px;box-sizing:border-box;">
                            <span class="close" onclick="document.getElementById('modalDeleteUser').style.display='none'">&times;</span>
                            <form method="POST" action="configuracion.php">
                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                                <input type="hidden" name="delete_user" value="1">
                                <input type="hidden" name="delete_user_id" id="delete_user_id">
                                <h3>Eliminar usuari</h3>
                                <p>Estàs segur que vols eliminar aquest usuari? Aquesta acció no es pot desfer.</p>
                                <div style="margin-top:18px; display:flex; gap:10px; justify-content:flex-end;">
                                    <button type="submit" class="btn btn-danger">Eliminar</button>
                                    <button type="button" class="btn btn-secondary" onclick="document.getElementById('modalDeleteUser').style.display='none'">Cancel·lar</button>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div id="modalAddUser" class="modal" style="display:none;">
                        <div class="modal-content" style="width:100%;max-width:500px;box-sizing:border-box;">
                            <span class="close" onclick="document.getElementById('modalAddUser').style.display='none'">&times;</span>
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
                                            <option value="superadmin">Superadmin</option>
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
                <?php endif; ?>
            </div>
        </section>
    </div>
        <div id="psych-tab" class="tab-content">
            <div class="section-header">
                <h2><i class="fas fa-user-md"></i> Datos de la psicóloga</h2>
                <p>Datos personales y de contacto</p>
            </div>
            <div class="config-section">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="nombre">Nombre completo</label>
                        <input type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars($config['nombre'] ?? '', ENT_QUOTES); ?>">
                    </div>
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($config['email'] ?? '', ENT_QUOTES); ?>">
                    </div>
                    <div class="form-group">
                        <label for="telefono">Teléfono</label>
                        <input type="tel" id="telefono" name="telefono" value="<?php echo htmlspecialchars($config['telefono'] ?? '', ENT_QUOTES); ?>">
                    </div>
                    <div class="form-group full-width">
                        <label for="direccion">Dirección</label>
                        <input type="text" id="direccion" name="direccion" value="<?php echo htmlspecialchars($config['direccion'] ?? '', ENT_QUOTES); ?>">
                    </div>
                    <div class="form-group full-width">
                        <label for="especialidades">Especialidades</label>
                        <input type="text" id="especialidades" name="especialidades" value="<?php echo htmlspecialchars(implode(', ', $config['especialidades'] ?? []), ENT_QUOTES); ?>">
                    </div>
                </div>
            </div>
        </div>
        <div id="other-tab" class="tab-content">
            <div class="section-header">
                <h2><i class="fas fa-cogs"></i> Otros ajustes</h2>
                <p>Ajustes varios del panel</p>
            </div>
            <div class="config-section">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="idioma">Idioma</label>
                        <select id="idioma" name="idioma">
                            <option value="es" <?php echo (($config['idioma'] ?? '') == 'es') ? 'selected' : ''; ?>>Español</option>
                            <option value="ca" <?php echo (($config['idioma'] ?? '') == 'ca') ? 'selected' : ''; ?>>Català</option>
                            <option value="en" <?php echo (($config['idioma'] ?? '') == 'en') ? 'selected' : ''; ?>>English</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="notificaciones_email">Notificaciones por email</label>
                        <input type="checkbox" id="notificaciones_email" name="notificaciones_email" <?php echo !empty($config['notificaciones_email']) ? 'checked' : ''; ?>>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="js/dashboard.js"></script>
    <script>
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
        });
        // Modal helpers: ensure only one modal visible at once
        function hideAllModals() {
            document.querySelectorAll('.modal').forEach(function(m){
                if (m && m.style) m.style.display = 'none';
            });
        }
        function showModal(id) {
            hideAllModals();
            var el = document.getElementById(id);
            if (el) el.style.display = 'block';
        }
    </script>

    
    </script>
</body>
</html>