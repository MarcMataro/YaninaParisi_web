<?php
session_start();

// Verificar si l'usuari ha iniciat sessió
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: index.php');
    exit;
}

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

    // 2) Altres submit: mantenir el comportament existent (guardar configuració)
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Libre+Baskerville:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="css/configuracion.css">
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Bar -->
        <header class="top-bar">
            <div class="top-bar-left">
                <button class="menu-toggle" id="menuToggle">
                    <i class="fas fa-bars"></i>
                </button>
                <div class="top-bar-info">
                    <h1>Configuración</h1>
                    <p class="date-today">Personaliza tu panel de control</p>
                </div>
            </div>
            <div class="top-bar-right">
                <div class="user-profile">
                    <img src="../img/Logo.png" alt="Profile" class="profile-img">
                    <span class="profile-name"><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Usuario'); ?></span>
                </div>
            </div>
        </header>

        <!-- Configuration Content: tabbed UI skeleton -->
        <div class="config-container">
            <?php if ($saved): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <span>Los cambios se han guardado correctamente</span>
                <button class="alert-close" onclick="this.parentElement.style.display='none'">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <?php endif; ?>

            <!-- Tabs navigation -->
            <div class="settings-tabs">
                <button class="tab-btn active" data-tab="users">Usuarios</button>
                <button class="tab-btn" data-tab="rates">Tarifas</button>
                <button class="tab-btn" data-tab="psych">Psicóloga</button>
                <button class="tab-btn" data-tab="other">Otros</button>
            </div>

            <!-- The Users tab uses its own form (separate). The main config form starts after the Users tab. -->

                <!-- Users tab -->
                <div id="users" class="tab-content active">
                    <div class="section-header">
                        <h2><i class="fas fa-users-cog"></i> Gestión de usuarios</h2>
                        <p>Usuarios que pueden acceder al panel de control</p>
                    </div>
                    <div class="config-section">
                        <p class="muted">Aquí puedes ver y gestionar los roles/permiso.</p>
                        <?php if (!$usersModel): ?>
                            <div class="alert alert-warning">La gestión de usuarios requiere conexión a base de datos. Revisa la configuración.</div>
                        <?php else: ?>
                            <?php
                                $users = $usersModel->llistar([], 200, 0);
                            ?>
                            <table class="table users-table">
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
                                                <button type="button" class="btn btn-secondary" disabled>Editar</button>
                                                <button type="button" class="btn btn-danger" disabled>Eliminar</button>
                                            </td>
                                        </tr>
                                    <?php endforeach; else: ?>
                                        <tr><td colspan="5">No hay usuarios registrados.</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>

                            <!-- Simple Add User form -->
                            <details class="add-user-panel" style="margin-top:18px;">
                                <summary class="btn btn-secondary">Añadir usuario</summary>
                                <div style="padding:16px; background:#fff; border-radius:8px; margin-top:10px; box-shadow:var(--shadow);">
                                    <form method="POST" action="configuracion.php">
                                        <input type="hidden" name="create_user" value="1">
                                        <div class="form-grid">
                                            <div class="form-group">
                                                <label for="user_nombre">Nombre</label>
                                                <input type="text" id="user_nombre" name="user_nombre" required>
                                            </div>
                                            <div class="form-group">
                                                <label for="user_apellidos">Apellidos</label>
                                                <input type="text" id="user_apellidos" name="user_apellidos" required>
                                            </div>
                                            <div class="form-group">
                                                <label for="user_email">Email</label>
                                                <input type="email" id="user_email" name="user_email" required>
                                            </div>
                                            <div class="form-group">
                                                <label for="user_password">Contraseña</label>
                                                <input type="password" id="user_password" name="user_password" required>
                                            </div>
                                            <div class="form-group">
                                                <label for="user_rol">Rol</label>
                                                <select id="user_rol" name="user_rol">
                                                    <option value="superadmin">Superadmin</option>
                                                    <option value="admin">Admin</option>
                                                    <option value="editor">Editor</option>
                                                    <option value="seo_manager">SEO Manager</option>
                                                    <option value="viewer">Viewer</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div style="margin-top:12px; display:flex; gap:10px; justify-content:flex-end;">
                                            <button type="submit" class="btn btn-save">Crear usuario</button>
                                        </div>
                                    </form>
                                </div>
                            </details>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Rates tab -->
                <form id="configForm" class="config-form" action="configuracion.php" method="POST">
                <div id="rates" class="tab-content">
                    <div class="section-header">
                        <h2><i class="fas fa-euro-sign"></i> Tarifas</h2>
                        <p>Gestiona las tarifas por tipo de sesión</p>
                    </div>
                    <div class="config-section">
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="tarifa_individual">Sesión Individual (€)</label>
                                <input type="number" id="tarifa_individual" name="tarifa_individual" value="<?php echo $config['tarifas']['individual']; ?>" min="0" step="5">
                            </div>
                            <div class="form-group">
                                <label for="tarifa_pareja">Terapia de Pareja (€)</label>
                                <input type="number" id="tarifa_pareja" name="tarifa_pareja" value="<?php echo $config['tarifas']['pareja']; ?>" min="0" step="5">
                            </div>
                            <div class="form-group">
                                <label for="tarifa_online">Terapia Online (€)</label>
                                <input type="number" id="tarifa_online" name="tarifa_online" value="<?php echo $config['tarifas']['online']; ?>" min="0" step="5">
                            </div>
                            <div class="form-group">
                                <label for="tarifa_infantil">Terapia Infantil (€)</label>
                                <input type="number" id="tarifa_infantil" name="tarifa_infantil" value="<?php echo $config['tarifas']['infantil']; ?>" min="0" step="5">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Psychologist tab -->
                <div id="psych" class="tab-content">
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
                <div id="other" class="tab-content">
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
    <script src="js/configuracion.js"></script>
    <script>
        // Simple tab switching for the configuracion page
        document.addEventListener('DOMContentLoaded', function() {
            const tabs = document.querySelectorAll('.settings-tabs .tab-btn');
            const contents = document.querySelectorAll('.tab-content');

            function activate(tabName) {
                tabs.forEach(t => t.classList.toggle('active', t.dataset.tab === tabName));
                contents.forEach(c => c.classList.toggle('active', c.id === tabName));
            }

            tabs.forEach(t => {
                t.addEventListener('click', function() {
                    activate(this.dataset.tab);
                });
            });

            // Ensure initial state respects default active tab
            const initial = document.querySelector('.settings-tabs .tab-btn.active');
            if (initial) activate(initial.dataset.tab);
        });
    </script>
</body>
</html>
