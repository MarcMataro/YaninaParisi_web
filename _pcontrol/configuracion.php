<?php
// Verificar si l'usuari ha iniciat sessió
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: index.php');
    exit;
}
?>
<!-- Modal: Afegir tarifa -->
<div id="modalAddTarifa" class="modal" style="display:none;">
    <div class="modal-content" style="width:100%;max-width:700px;box-sizing:border-box;">
        <span class="close" onclick="document.getElementById('modalAddTarifa').style.display='none'">&times;</span>
        <form method="POST" action="configuracion.php">
            <input type="hidden" name="add_tarifa" value="1">
            <h3 style="margin-bottom:18px;">Afegir tarifa</h3>
            <div class="form-grid" style="display:flex;flex-wrap:wrap;gap:16px;">
                <div class="form-group" style="flex:1 1 220px;min-width:0;">
                    <label for="add_nom_servei_es">Nom servei</label>
                    <input type="text" id="add_nom_servei_es" name="add_nom_servei_es" required style="width:100%;box-sizing:border-box;">
                </div>
                <div class="form-group" style="flex:1 1 220px;min-width:0;">
                    <label for="add_tipus_servei">Tipus</label>
                    <select id="add_tipus_servei" name="add_tipus_servei" required style="width:100%;box-sizing:border-box;">
                        <option value="individual">Individual</option>
                        <option value="parella">Parella</option>
                        <option value="familiar">Familiar</option>
                        <option value="grup">Grup</option>
                        <option value="evaluacio">Avaluació</option>
                        <option value="urgent">Urgent</option>
                        <option value="pack">Pack</option>
                    </select>
                </div>
                <div class="form-group" style="flex:1 1 120px;min-width:0;">
                    <label for="add_durada_minuts">Durada (min)</label>
                    <input type="number" id="add_durada_minuts" name="add_durada_minuts" min="1" required style="width:100%;box-sizing:border-box;">
                </div>
                <div class="form-group" style="flex:1 1 120px;min-width:0;">
                    <label for="add_preu_base">Preu base (€)</label>
                    <input type="number" id="add_preu_base" name="add_preu_base" step="0.01" min="0" required style="width:100%;box-sizing:border-box;">
                </div>
                <div class="form-group" style="flex:1 1 120px;min-width:0;">
                    <label for="add_preu_promocio">Preu promoció (€)</label>
                    <input type="number" id="add_preu_promocio" name="add_preu_promocio" step="0.01" min="0" style="width:100%;box-sizing:border-box;">
                </div>
                <div class="form-group" style="flex:1 1 80px;min-width:0;">
                    <label for="add_iva_percentatge">IVA (%)</label>
                    <input type="number" id="add_iva_percentatge" name="add_iva_percentatge" step="0.01" min="0" max="100" value="21.00" style="width:100%;box-sizing:border-box;">
                </div>
                <div class="form-group" style="flex:1 1 80px;min-width:0;">
                    <label for="add_moneda">Moneda</label>
                    <input type="text" id="add_moneda" name="add_moneda" maxlength="3" value="EUR" required style="width:100%;box-sizing:border-box;">
                </div>
            </div>
            <div style="margin-top:12px; display:flex; gap:10px; justify-content:flex-end;">
                <button type="submit" class="btn btn-save">Crear tarifa</button>
            </div>
        </form>
    </div>
</div>

                    <!-- Modal: Editar tarifa -->
                    <div id="modalEditTarifa" class="modal" style="display:none;">
                        <div class="modal-content" style="width:100%;max-width:700px;box-sizing:border-box;">
                            <span class="close" onclick="document.getElementById('modalEditTarifa').style.display='none'">&times;</span>
                            <form method="POST" action="configuracion.php">
                                <input type="hidden" name="edit_tarifa" value="1">
                                <input type="hidden" name="edit_id_tarifa" id="edit_id_tarifa">
                                <h3 style="margin-bottom:18px;">Editar tarifa</h3>
                                <div class="form-grid" style="display:flex;flex-wrap:wrap;gap:16px;">
                                    <div class="form-group" style="flex:1 1 220px;min-width:0;">
                                        <label for="edit_nom_servei_es">Nom servei</label>
                                        <input type="text" id="edit_nom_servei_es" name="edit_nom_servei_es" required style="width:100%;box-sizing:border-box;">
                                    </div>
                                    <div class="form-group" style="flex:1 1 220px;min-width:0;">
                                        <label for="edit_tipus_servei">Tipus</label>
                                        <select id="edit_tipus_servei" name="edit_tipus_servei" required style="width:100%;box-sizing:border-box;">
                                            <option value="individual">Individual</option>
                                            <option value="parella">Parella</option>
                                            <option value="familiar">Familiar</option>
                                            <option value="grup">Grup</option>
                                            <option value="evaluacio">Avaluació</option>
                                            <option value="urgent">Urgent</option>
                                            <option value="pack">Pack</option>
                                        </select>
                                    </div>
                                    <div class="form-group" style="flex:1 1 120px;min-width:0;">
                                        <label for="edit_durada_minuts">Durada (min)</label>
                                        <input type="number" id="edit_durada_minuts" name="edit_durada_minuts" min="1" required style="width:100%;box-sizing:border-box;">
                                    </div>
                                    <div class="form-group" style="flex:1 1 120px;min-width:0;">
                                        <label for="edit_preu_base">Preu base (€)</label>
                                        <input type="number" id="edit_preu_base" name="edit_preu_base" step="0.01" min="0" required style="width:100%;box-sizing:border-box;">
                                    </div>
                                    <div class="form-group" style="flex:1 1 120px;min-width:0;">
                                        <label for="edit_preu_promocio">Preu promoció (€)</label>
                                        <input type="number" id="edit_preu_promocio" name="edit_preu_promocio" step="0.01" min="0" style="width:100%;box-sizing:border-box;">
                                    </div>
                                    <div class="form-group" style="flex:1 1 80px;min-width:0;">
                                        <label for="edit_iva_percentatge">IVA (%)</label>
                                        <input type="number" id="edit_iva_percentatge" name="edit_iva_percentatge" step="0.01" min="0" max="100" value="21.00" style="width:100%;box-sizing:border-box;">
                                    </div>
                                    <div class="form-group" style="flex:1 1 80px;min-width:0;">
                                        <label for="edit_moneda">Moneda</label>
                                        <input type="text" id="edit_moneda" name="edit_moneda" maxlength="3" value="EUR" required style="width:100%;box-sizing:border-box;">
                                    </div>
                                </div>
                                <div style="margin-top:12px; display:flex; gap:10px; justify-content:flex-end;">
                                    <button type="submit" class="btn btn-save">Desar canvis</button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Modal: Eliminar tarifa -->
                    <div id="modalDeleteTarifa" class="modal" style="display:none;">
                        <div class="modal-content" style="width:100%;max-width:400px;box-sizing:border-box;">
                            <span class="close" onclick="document.getElementById('modalDeleteTarifa').style.display='none'">&times;</span>
                            <form method="POST" action="configuracion.php">
                                <input type="hidden" name="delete_tarifa" value="1">
                                <input type="hidden" name="delete_id_tarifa" id="delete_id_tarifa">
                                <h3>Eliminar tarifa</h3>
                                <p>Estàs segur que vols eliminar aquesta tarifa? Aquesta acció no es pot desfer.</p>
                                <div style="margin-top:18px; display:flex; gap:10px; justify-content:flex-end;">
                                    <button type="submit" class="btn btn-danger">Eliminar</button>
                                    <button type="button" class="btn btn-secondary" onclick="document.getElementById('modalDeleteTarifa').style.display='none'">Cancel·lar</button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <script>
                    // Prepare tarifes data for JS
                    var tarifesData = {};
                    <?php if ($tarifes): foreach ($tarifes as $t): ?>
                    tarifesData[<?php echo $t->id_tarifa; ?>] = {
                        id: <?php echo $t->id_tarifa; ?>,
                        nom_servei_es: <?php echo json_encode($t->nom_servei_es); ?>,
                        tipus_servei: <?php echo json_encode($t->tipus_servei); ?>,
                        durada_minuts: <?php echo json_encode($t->durada_minuts); ?>,
                        preu_base: <?php echo json_encode($t->preu_base); ?>,
                        preu_promocio: <?php echo json_encode($t->preu_promocio); ?>,
                        iva_percentatge: <?php echo json_encode($t->iva_percentatge); ?>,
                        moneda: <?php echo json_encode($t->moneda); ?>
                    };
                    <?php endforeach; endif; ?>

                    function openAddTarifaModal() {
                        document.getElementById('add_nom_servei_es').value = '';
                        document.getElementById('add_tipus_servei').value = 'individual';
                        document.getElementById('add_durada_minuts').value = '';
                        document.getElementById('add_preu_base').value = '';
                        document.getElementById('add_preu_promocio').value = '';
                        document.getElementById('add_iva_percentatge').value = '21.00';
                        document.getElementById('add_moneda').value = 'EUR';
                        document.getElementById('modalAddTarifa').style.display = 'block';
                    }

                    function openEditTarifaModal(id) {
                        var t = tarifesData[id];
                        if (!t) return;
                        document.getElementById('edit_id_tarifa').value = t.id;
                        document.getElementById('edit_nom_servei_es').value = t.nom_servei_es;
                        document.getElementById('edit_tipus_servei').value = t.tipus_servei;
                        document.getElementById('edit_durada_minuts').value = t.durada_minuts;
                        document.getElementById('edit_preu_base').value = t.preu_base;
                        document.getElementById('edit_preu_promocio').value = t.preu_promocio;
                        document.getElementById('edit_iva_percentatge').value = t.iva_percentatge;
                        document.getElementById('edit_moneda').value = t.moneda;
                        document.getElementById('modalEditTarifa').style.display = 'block';
                    }

                    function openDeleteTarifaModal(id) {
                        document.getElementById('delete_id_tarifa').value = id;
                        document.getElementById('modalDeleteTarifa').style.display = 'block';
                    }
                    </script>
<?php
/**
 * Configuració - Panel de Control
 *
 * Gestió d'usuaris i tarifes amb control d'accés igual que gseo.php
 */
// Dades de configuració (després es connectarà amb BD)
$default_professional = trim($_SESSION['user_name'] ?? 'Yanina Parisi');
$config = [
    'nombre' => $default_professional,
    'email' => 'yanina@psicologiayanina.com',
    'telefono' => '+34 972 123 45 67',
    'colegiada' => 'COL-12345',
    'direccion' => 'Carrer de la Pau, 23, Girona',
    'especialidades' => ['Terapia Cognitivo-Conductual', 'Terapia de Pareja', 'Terapia Infantil'],
    'duracion_sesion' => 60,
    'tiempo_descanso' => 15,
    'horario_inicio' => '09:00',
    'horario_fin' => '20:00',
    'tarifas' => [
        'individual' => 60,
        'pareja' => 80,
        'online' => 50,
        'infantil' => 55
    ],
    'notificaciones_email' => true,
    'notificaciones_citas' => true,
    'idioma' => 'es'
];

// Procesar formulario si se envía
// Inicialitzar models i connexió a BD per a la gestió d'usuaris
require_once __DIR__ . '/../classes/connexio.php';
require_once __DIR__ . '/../classes/usuaris_panell.php';

$db = null;
try {
    $db = Connexio::getInstance()->getConnexio();
} catch (Exception $e) {
    // Si no hi ha connexió a BD, deixem $db a null i la funcionalitat d'usuaris quedarà desactivada
    error_log('No s\'ha pogut connectar a la BD des de configuracion.php: ' . $e->getMessage());
}

$usersModel = $db ? new UsuarisPanell($db) : null;

// Procesar form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1) Crear usuari des del formulari d'usuaris (botó amb name="create_user")
    if (isset($_POST['create_user']) && $usersModel) {
        $u_email = trim($_POST['user_email'] ?? '');
        $u_nombre = trim($_POST['user_nombre'] ?? '');
        $u_apellidos = trim($_POST['user_apellidos'] ?? '');
        $u_password = $_POST['user_password'] ?? '';
        $u_rol = $_POST['user_rol'] ?? 'editor';

        // Validacions bàsiques
        if (!filter_var($u_email, FILTER_VALIDATE_EMAIL) || empty($u_nombre) || empty($u_apellidos) || empty($u_password)) {
            header('Location: configuracion.php?user_error=invalid');
            exit;
        }

        // Comprovar si l'email ja existeix
        if ($usersModel->existeixEmail($u_email)) {
            header('Location: configuracion.php?user_error=exists');
            exit;
        }

        // Preparar objecte i crear
        $usersModel->email = $u_email;
        $usersModel->nombre = $u_nombre;
        $usersModel->apellidos = $u_apellidos;
        $usersModel->rol = $u_rol;
        $usersModel->activo = 1;
        $usersModel->creado_por = $_SESSION['user_id'] ?? null;

        $res = $usersModel->crear($u_password);
        if ($res) {
            header('Location: configuracion.php?user_created=1');
            exit;
        }

        header('Location: configuracion.php?user_error=save');
        exit;
    }

    // 2) Afegir tarifa
    if (isset($_POST['add_tarifa']) && class_exists('Tarifa')) {
        $t = new Tarifa();
        $t->nom_servei_es = trim($_POST['add_nom_servei_es'] ?? '');
        $t->tipus_servei = $_POST['add_tipus_servei'] ?? '';
        $t->durada_minuts = intval($_POST['add_durada_minuts'] ?? 0);
        $t->preu_base = floatval($_POST['add_preu_base'] ?? 0);
        $t->preu_promocio = $_POST['add_preu_promocio'] !== '' ? floatval($_POST['add_preu_promocio']) : null;
        $t->iva_percentatge = floatval($_POST['add_iva_percentatge'] ?? 21.00);
        $t->moneda = $_POST['add_moneda'] ?? 'EUR';
        $res = $t->crear();
        if ($res) {
            header('Location: configuracion.php?tarifa_added=1');
            exit;
        }
        header('Location: configuracion.php?tarifa_error=add');
        exit;
    }

    // 3) Editar tarifa
    if (isset($_POST['edit_tarifa']) && class_exists('Tarifa')) {
        $id = intval($_POST['edit_id_tarifa'] ?? 0);
        if ($id) {
            $t = Tarifa::obtenirPerId($id);
            if ($t) {
                $t->nom_servei_es = trim($_POST['edit_nom_servei_es'] ?? '');
                $t->tipus_servei = $_POST['edit_tipus_servei'] ?? '';
                $t->durada_minuts = intval($_POST['edit_durada_minuts'] ?? 0);
                $t->preu_base = floatval($_POST['edit_preu_base'] ?? 0);
                $t->preu_promocio = $_POST['edit_preu_promocio'] !== '' ? floatval($_POST['edit_preu_promocio']) : null;
                $t->iva_percentatge = floatval($_POST['edit_iva_percentatge'] ?? 21.00);
                $t->moneda = $_POST['edit_moneda'] ?? 'EUR';
                $res = $t->actualitzar();
                if ($res) {
                    header('Location: configuracion.php?tarifa_edited=1');
                    exit;
                }
            }
        }
        header('Location: configuracion.php?tarifa_error=edit');
        exit;
    }

    // 4) Eliminar tarifa
    if (isset($_POST['delete_tarifa']) && class_exists('Tarifa')) {
        $id = intval($_POST['delete_id_tarifa'] ?? 0);
        if ($id) {
            $t = Tarifa::obtenirPerId($id);
            if ($t) {
                $res = $t->eliminar();
                if ($res) {
                    header('Location: configuracion.php?tarifa_deleted=1');
                    exit;
                }
            }
        }
        header('Location: configuracion.php?tarifa_error=delete');
        exit;
    }
    if (isset($_POST['edit_user']) && $usersModel) {
        $id = intval($_POST['edit_user_id'] ?? 0);
        $nom = trim($_POST['edit_user_nombre'] ?? '');
        $apellidos = trim($_POST['edit_user_apellidos'] ?? '');
        $email = trim($_POST['edit_user_email'] ?? '');
        $password = $_POST['edit_user_password'] ?? '';
        $rol = $_POST['edit_user_rol'] ?? 'editor';
        if ($id && $nom && $apellidos && $email && filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $usersModel->id_usuario = $id;
            $usersModel->nombre = $nom;
            $usersModel->apellidos = $apellidos;
            $usersModel->email = $email;
            $usersModel->rol = $rol;
            if ($password) {
                $res = $usersModel->actualitzarAmbPassword($password);
            } else {
                $res = $usersModel->actualitzarSensePassword();
            }
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
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>

    <!-- Main Content: només gestió d'usuaris -->
    <div class="main-content">
        <header class="top-bar">
            <div class="top-bar-left">
                <h1>Gestió d'usuaris</h1>
            </div>
        </header>
    <div class="content-wrapper" style="margin-top:32px;">
            <section class="card">
                <div class="card-header">
                    <h2><i class="fas fa-users-cog"></i> Usuaris del panell</h2>
                </div>
                <div class="config-section">
                    <?php if (!$usersModel): ?>
                        <div class="alert alert-warning">La gestió d'usuaris requereix connexió a base de dades. Revisa la configuració.</div>
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
                            rol: <?php echo json_encode($u['rol']); ?>
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
                            document.getElementById('edit_user_password').value = '';
                            document.getElementById('modalEditUser').style.display = 'block';
                        }

                        function openDeleteUserModal(id) {
                            document.getElementById('delete_user_id').value = id;
                            document.getElementById('modalDeleteUser').style.display = 'block';
                        }
                        </script>
                        <table class="table users-table" style="width:100%;margin-bottom:24px;">
                            <thead>
                                <tr><th>Nom</th><th>Email</th><th>Rol</th><th>Actiu</th><th>Accions</th></tr>
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
                                    <tr><td colspan="5">No hi ha usuaris registrats.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                        <button type="button" class="btn btn-primary" onclick="openAddUserModal()">
                            <i class="fas fa-user-plus"></i> Afegir usuari
                        </button>
                        <script>
                        function openAddUserModal() {
                            document.getElementById('user_nombre').value = '';
                            document.getElementById('user_apellidos').value = '';
                            document.getElementById('user_email').value = '';
                            document.getElementById('user_password').value = '';
                            document.getElementById('user_rol').value = 'editor';
                            document.getElementById('modalAddUser').style.display = 'block';
                        }
                        </script>
                        <!-- Modal add user -->
                        <!-- Modal edit user -->
                        <div id="modalEditUser" class="modal" style="display:none;">
                            <div class="modal-content" style="width:100%;max-width:500px;box-sizing:border-box;">
                                <span class="close" onclick="document.getElementById('modalEditUser').style.display='none'">&times;</span>
                                <form method="POST" action="configuracion.php">
                                    <input type="hidden" name="edit_user" value="1">
                                    <input type="hidden" name="edit_user_id" id="edit_user_id">
                                    <h3 style="margin-bottom:18px;">Editar usuari</h3>
                                    <div class="form-grid" style="display:flex;flex-wrap:wrap;gap:16px;">
                                        <div class="form-group" style="flex:1 1 220px;min-width:0;">
                                            <label for="edit_user_nombre">Nom</label>
                                            <input type="text" id="edit_user_nombre" name="edit_user_nombre" required style="width:100%;box-sizing:border-box;">
                                        </div>
                                        <div class="form-group" style="flex:1 1 220px;min-width:0;">
                                            <label for="edit_user_apellidos">Cognoms</label>
                                            <input type="text" id="edit_user_apellidos" name="edit_user_apellidos" required style="width:100%;box-sizing:border-box;">
                                        </div>
                                        <div class="form-group" style="flex:1 1 220px;min-width:0;">
                                            <label for="edit_user_email">Email</label>
                                            <input type="email" id="edit_user_email" name="edit_user_email" required style="width:100%;box-sizing:border-box;">
                                        </div>
                                        <div class="form-group" style="flex:1 1 220px;min-width:0;">
                                            <label for="edit_user_password">Contrasenya (deixar en blanc per no canviar)</label>
                                            <input type="password" id="edit_user_password" name="edit_user_password" style="width:100%;box-sizing:border-box;">
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
        <!-- Tarifes Management Card -->
        <div class="content-wrapper" style="margin-top:32px;">
            <section class="card">
                <div class="card-header">
                    <h2><i class="fas fa-euro-sign"></i> Tarifes</h2>
                </div>
                <div class="config-section">
                    <?php require_once __DIR__ . '/../classes/tarifes.php'; ?>
                    <?php $tarifes = Tarifa::obtenirTotes(); ?>
                    <table class="table tarifas-table" style="width:100%;margin-bottom:24px;">
                        <thead>
                            <tr>
                                <th>Servei</th>
                                <th>Tipus</th>
                                <th>Durada</th>
                                <th>Preu base</th>
                                <th>Promoció</th>
                                <th>IVA (%)</th>
                                <th>Moneda</th>
                                <th>Disponible</th>
                                <th>Visible</th>
                                <th>Destacat</th>
                                <th>Accions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($tarifes): foreach ($tarifes as $t): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($t->nom_servei_es); ?></td>
                                    <td><?php echo htmlspecialchars($t->tipus_servei); ?></td>
                                    <td><?php echo htmlspecialchars($t->durada_minuts); ?> min</td>
                                    <td><?php echo number_format($t->preu_base, 2); ?></td>
                                    <td><?php echo $t->preu_promocio !== null ? number_format($t->preu_promocio, 2) : '-'; ?></td>
                                    <td><?php echo number_format($t->iva_percentatge, 2); ?></td>
                                    <td><?php echo htmlspecialchars($t->moneda); ?></td>
                                    <td><i class="fas fa-<?php echo $t->disponible ? 'check' : 'times'; ?>" style="color:<?php echo $t->disponible ? '#22c55e' : '#ef4444'; ?>;"></i></td>
                                    <td><i class="fas fa-<?php echo $t->visible_web ? 'eye' : 'eye-slash'; ?>" style="color:<?php echo $t->visible_web ? '#3b82f6' : '#6b7280'; ?>;"></i></td>
                                    <td><i class="fas fa-<?php echo $t->destacat ? 'star' : 'star-half-alt'; ?>" style="color:<?php echo $t->destacat ? '#f59e42' : '#d1d5db'; ?>;"></i></td>
                                    <td>
                                        <button type="button" class="btn btn-secondary" onclick="openEditTarifaModal(<?php echo $t->id_tarifa; ?>)"><i class="fas fa-edit"></i></button>
                                        <button type="button" class="btn btn-danger" onclick="openDeleteTarifaModal(<?php echo $t->id_tarifa; ?>)"><i class="fas fa-trash"></i></button>
                                    </td>
                                </tr>
                            <?php endforeach; else: ?>
                                <tr><td colspan="11">No hi ha tarifes registrades.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                    <button type="button" class="btn btn-primary" onclick="openAddTarifaModal()">
                        <i class="fas fa-plus"></i> Afegir tarifa
                    </button>
                    <!-- Modals for add/edit/delete will be added next step -->
                </div>
            </section>
        </div>
    </div>
            <div id="rates-tab" class="tab-content">
                <!-- CONTINGUT TARIFAS -->
                <div class="section-header">
                    <h2><i class="fas fa-euro-sign"></i> Tarifas</h2>
                    <p>Gestiona las tarifas por tipo de sesión</p>
                </div>
                <div class="config-section">
                    <button type="button" class="btn btn-primary" onclick="abrirModalTarifa()"><i class="fas fa-plus"></i> Añadir tarifa</button>
                    <table class="table tarifas-table" style="margin-top:18px;">
                        <thead>
                            <tr>
                                <th>Servicio</th>
                                <th>Tipo</th>
                                <th>Duración</th>
                                <th>Precio base</th>
                                <th>Promoción</th>
                                <th>IVA (%)</th>
                                <th>Moneda</th>
                                <th>Disponible</th>
                                <th>Visible</th>
                                <th>Destacado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            require_once __DIR__ . '/../classes/tarifes.php';
                            $tarifas = Tarifa::obtenerTodas();
                            if ($tarifas):
                                foreach ($tarifas as $t): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($t->nom_servei_es); ?></td>
                                        <td><?php echo htmlspecialchars($t->tipus_servei); ?></td>
                                        <td><?php echo htmlspecialchars($t->durada_minuts); ?> min</td>
                                        <td><?php echo number_format($t->preu_base, 2); ?></td>
                                        <td><?php echo $t->preu_promocio !== null ? number_format($t->preu_promocio, 2) : '-'; ?></td>
                                        <td><?php echo number_format($t->iva_percentatge, 2); ?></td>
                                        <td><?php echo htmlspecialchars($t->moneda); ?></td>
                                        <td><i class="fas fa-<?php echo $t->disponible ? 'check' : 'times'; ?>" style="color:<?php echo $t->disponible ? '#22c55e' : '#ef4444'; ?>;"></i></td>
                                        <td><i class="fas fa-<?php echo $t->visible_web ? 'eye' : 'eye-slash'; ?>" style="color:<?php echo $t->visible_web ? '#3b82f6' : '#6b7280'; ?>;"></i></td>
                                        <td><i class="fas fa-<?php echo $t->destacat ? 'star' : 'star-half-alt'; ?>" style="color:<?php echo $t->destacat ? '#f59e42' : '#d1d5db'; ?>;"></i></td>
                                        <td>
                                            <button type="button" class="btn btn-secondary" onclick="abrirModalTarifaEditar(<?php echo $t->id_tarifa; ?>)"><i class="fas fa-edit"></i></button>
                                            <button type="button" class="btn btn-danger" onclick="abrirModalTarifaEliminar(<?php echo $t->id_tarifa; ?>)"><i class="fas fa-trash"></i></button>
                                        </td>
                                    </tr>
                                <?php endforeach;
                            else: ?>
                                <tr><td colspan="11">No hay tarifas registradas.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <!-- Modal para crear/editar tarifa -->
                <div id="modalTarifa" class="modal" style="display:none;">
                    <div class="modal-content" style="max-width:700px;">
                        <span class="close" onclick="cerrarModalTarifa()">&times;</span>
                        <form id="formTarifa" method="POST" action="configuracion.php">
                            <input type="hidden" name="id_tarifa" id="id_tarifa">
                            <h3 id="modalTarifaTitulo">Añadir tarifa</h3>
                            <div class="form-grid">
                                <div class="form-group">
                                    <label for="nom_servei_es">Nombre del servicio</label>
                                    <input type="text" name="nom_servei_es" id="nom_servei_es" required>
                                </div>
                                <div class="form-group">
                                    <label for="tipus_servei">Tipo de servicio</label>
                                    <select name="tipus_servei" id="tipus_servei" required>
                                        <option value="individual">Individual</option>
                                        <option value="pareja">Pareja</option>
                                        <option value="familiar">Familiar</option>
                                        <option value="grupo">Grupo</option>
                                        <option value="evaluacion">Evaluación</option>
                                        <option value="urgente">Urgente</option>
                                        <option value="pack">Pack</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="durada_minuts">Duración (minutos)</label>
                                    <input type="number" name="durada_minuts" id="durada_minuts" min="1" required>
                                </div>
                                <div class="form-group">
                                    <label for="preu_base">Precio base (€)</label>
                                    <input type="number" name="preu_base" id="preu_base" step="0.01" min="0" required>
                                </div>
                                <div class="form-group">
                                    <label for="preu_promocio">Precio promoción (€)</label>
                                    <input type="number" name="preu_promocio" id="preu_promocio" step="0.01" min="0">
                                </div>
                                <div class="form-group">
                                    <label for="iva_percentatge">IVA (%)</label>
                                    <input type="number" name="iva_percentatge" id="iva_percentatge" step="0.01" min="0" max="100" value="21.00">
                                </div>
                                <div class="form-group">
                                    <label for="moneda">Moneda</label>
                                    <input type="text" name="moneda" id="moneda" maxlength="3" value="EUR" required>
                                </div>
                                <div class="form-group">
                                    <label for="disponible">Disponible</label>
                                    <select name="disponible" id="disponible">
                                        <option value="1">Sí</option>
                                        <option value="0">No</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="visible_web">Visible en la web</label>
                                    <select name="visible_web" id="visible_web">
                                        <option value="1">Sí</option>
                                        <option value="0">No</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="destacat">Destacado</label>
                                    <select name="destacat" id="destacat">
                                        <option value="1">Sí</option>
                                        <option value="0">No</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="modalitat">Modalidad</label>
                                    <select name="modalitat" id="modalitat">
                                        <option value="presencial">Presencial</option>
                                        <option value="online">Online</option>
                                        <option value="hibrida">Híbrida</option>
                                        <option value="telefonica">Telefónica</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="sessions_pack">Sesiones (si es pack)</label>
                                    <input type="number" name="sessions_pack" id="sessions_pack" min="1" value="1">
                                </div>
                                <div class="form-group">
                                    <label for="validesa_dies">Validez (días)</label>
                                    <input type="number" name="validesa_dies" id="validesa_dies" min="0">
                                </div>
                                <div class="form-group">
                                    <label for="requisits">Requisitos/condiciones</label>
                                    <textarea name="requisits" id="requisits"></textarea>
                                </div>
                                <div class="form-group">
                                    <label for="beneficios_es">Beneficios</label>
                                    <textarea name="beneficios_es" id="beneficios_es"></textarea>
                                </div>
                                <div class="form-group">
                                    <label for="ordre_visualitzacio">Orden visualización</label>
                                    <input type="number" name="ordre_visualitzacio" id="ordre_visualitzacio" min="0" value="0">
                                </div>
                                <div class="form-group">
                                    <label for="color_etiqueta">Color etiqueta</label>
                                    <input type="color" name="color_etiqueta" id="color_etiqueta" value="#3B82F6">
                                </div>
                            </div>
                            <div style="margin-top:18px; display:flex; gap:10px; justify-content:flex-end;">
                                <button type="submit" class="btn btn-save">Guardar</button>
                                <button type="button" class="btn btn-secondary" onclick="cerrarModalTarifa()">Cancelar</button>
                            </div>
                        </form>
                    </div>
                </div>
                <!-- Modal para eliminar tarifa -->
                <div id="modalTarifaEliminar" class="modal" style="display:none;">
                    <div class="modal-content" style="max-width:400px;">
                        <span class="close" onclick="cerrarModalTarifaEliminar()">&times;</span>
                        <form id="formTarifaEliminar" method="POST" action="configuracion.php">
                            <input type="hidden" name="id_tarifa_eliminar" id="id_tarifa_eliminar">
                            <h3>¿Eliminar tarifa?</h3>
                            <p>¿Seguro que quieres eliminar esta tarifa? Esta acción no se puede deshacer.</p>
                            <div style="margin-top:18px; display:flex; gap:10px; justify-content:flex-end;">
                                <button type="submit" class="btn btn-danger">Eliminar</button>
                                <button type="button" class="btn btn-secondary" onclick="cerrarModalTarifaEliminar()">Cancelar</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Psychologist tab -->
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
                                <input type="text" id="nombre" name="nombre" value="<?php echo $config['nombre']; ?>">
                            </div>
                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" id="email" name="email" value="<?php echo $config['email']; ?>">
                            </div>
                            <div class="form-group">
                                <label for="telefono">Teléfono</label>
                                <input type="tel" id="telefono" name="telefono" value="<?php echo $config['telefono']; ?>">
                            </div>
                            <div class="form-group full-width">
                                <label for="direccion">Dirección</label>
                                <input type="text" id="direccion" name="direccion" value="<?php echo $config['direccion']; ?>">
                            </div>
                            <div class="form-group full-width">
                                <label for="especialidades">Especialidades</label>
                                <input type="text" id="especialidades" name="especialidades" value="<?php echo implode(', ', $config['especialidades']); ?>">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Other settings tab -->
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
                                    <option value="es" <?php echo $config['idioma'] == 'es' ? 'selected' : ''; ?>>Español</option>
                                    <option value="ca" <?php echo $config['idioma'] == 'ca' ? 'selected' : ''; ?>>Català</option>
                                    <option value="en" <?php echo $config['idioma'] == 'en' ? 'selected' : ''; ?>>English</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="notificaciones_email">Notificaciones por email</label>
                                <input type="checkbox" id="notificaciones_email" name="notificaciones_email" <?php echo $config['notificaciones_email'] ? 'checked' : ''; ?>>
                            </div>
                        </div>
                    </div>
                </div>

            </form>
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
    </script>
</body>
</html>
