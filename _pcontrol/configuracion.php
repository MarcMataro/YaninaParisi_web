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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@7.0.0/css/all.min.css">
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


            <!-- Sistema de tabs modern i accessible -->
            <div class="settings-tabs">
                <button class="tab-btn active" onclick="switchTab('users')"><i class="fas fa-users-cog"></i> Usuarios</button>
                <button class="tab-btn" onclick="switchTab('rates')"><i class="fas fa-euro-sign"></i> Tarifas</button>
                <button class="tab-btn" onclick="switchTab('psych')"><i class="fas fa-user-md"></i> Psicóloga</button>
                <button class="tab-btn" onclick="switchTab('other')"><i class="fas fa-cogs"></i> Otros</button>
            </div>
            <div id="users-tab" class="tab-content active">
                <!-- CONTINGUT USUARIOS -->
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
            // Oculta tots els tabs
            document.querySelectorAll('.tab-content').forEach(t => t.classList.remove('active'));
            document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
            // Mostra el tab seleccionat
            const tabContent = document.getElementById(tab + '-tab');
            if (tabContent) tabContent.classList.add('active');
            // Activa el botó corresponent
            document.querySelectorAll('.tab-btn').forEach(btn => {
                if (btn.getAttribute('onclick').includes(tab)) {
                    btn.classList.add('active');
                }
            });
            // Actualitza la URL
            const url = new URL(window.location);
            url.searchParams.set('tab', tab);
            window.history.pushState({}, '', url);
        }
        // Inicialitza el tab actiu segons la URL
        document.addEventListener('DOMContentLoaded', function() {
            const params = new URLSearchParams(window.location.search);
            const tab = params.get('tab') || 'users';
            switchTab(tab);
        });
    </script>
</body>
</html>
