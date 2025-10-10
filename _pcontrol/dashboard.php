<?php
/**
 * Dashboard - Panel de Control
 * 
 * Muestra estadísticas, sesiones del día y accesos rápidos.
 * Obtiene datos reales de la base de datos.
 * 
 * @author Marc Mataró
 * @version 2.0.0
 */

session_start();

// Manejo de inicio de sesión enviado desde la pantalla de login
require_once __DIR__ . '/../classes/connexio.php';
require_once __DIR__ . '/../classes/usuaris_panell.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['username'], $_POST['password'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    try {
        $pdo = Connexio::getInstance()->getConnexio();
    } catch (Exception $e) {
        // Si no hi ha connexió, redirigir al login amb error
        header('Location: index.php?error=1');
        exit;
    }

    $usersModel = new UsuarisPanell($pdo);
    $userRow = $usersModel->buscarPerEmail($username);

    if ($userRow && isset($userRow['password_hash']) && password_verify($password, $userRow['password_hash'])) {
        // Comprobaciones adicionales: activo y no bloqueado
        if (isset($userRow['activo']) && !$userRow['activo']) {
            header('Location: index.php?error=1');
            exit;
        }
        if (isset($userRow['bloqueado']) && $userRow['bloqueado']) {
            header('Location: index.php?error=1');
            exit;
        }

        // Autenticación correcta: inicializar sesión
        $_SESSION['logged_in'] = true;
        $_SESSION['user_id'] = $userRow['id_usuario'];
        $_SESSION['user_email'] = $userRow['email'];
        $_SESSION['user_name'] = trim(($userRow['nombre'] ?? '') . ' ' . ($userRow['apellidos'] ?? ''));
        $_SESSION['user_role'] = $userRow['rol'] ?? 'editor';

        // Actualizar último acceso
        try {
            $stmt = $pdo->prepare("UPDATE usuarios_panel SET ultimo_acceso = NOW(), intentos_login = 0 WHERE id_usuario = :id");
            $stmt->execute([':id' => $userRow['id_usuario']]);
        } catch (Exception $e) {
            // No crítico
        }

        // Redirigir al dashboard (GET) para evitar reenvío de formulario
        header('Location: dashboard.php');
        exit;
    }

    // Si no autenticó, llevar al login con error
    header('Location: index.php?error=1');
    exit;
}

// Verificar autenticación para acceso directo por GET
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: index.php');
    exit;
}

// Incluir clases necesarias
require_once '../classes/connexio.php';
require_once '../classes/sessions.php';
require_once '../classes/pacients.php';

// Obtener conexión a la base de datos
try {
    $connexio = Connexio::getInstance();
    $pdo = $connexio->getConnexio();
} catch (Exception $e) {
    die("Error de conexión: " . $e->getMessage());
}

// Inicializar objetos
$session = new Session($pdo);
$pacient = new Pacient($pdo);

// Obtener estadísticas de pacientes
$statsPacients = $pacient->obtenirEstadistiques();

// Obtener estadísticas de sesiones
$statsSessions = $session->obtenirEstadistiques();

// Obtener sesiones de hoy
$sessionsAvui = $session->sessionsAvui();
$today_appointments = $sessionsAvui->fetchAll(PDO::FETCH_ASSOC);

// Obtener sesiones de esta semana
$iniciSetmana = date('Y-m-d', strtotime('monday this week'));
$fiSetmana = date('Y-m-d', strtotime('sunday this week'));
$stmtSetmana = $session->llegirTotes(null, 'Programada', $iniciSetmana, $fiSetmana);
$sessionsSetmana = $stmtSetmana->rowCount();

// Calcular ingresos del mes
$ingressosMes = $session->ingressosAquestMes();

// Configurar locale para fechas en español
setlocale(LC_TIME, 'es_ES.UTF-8', 'es_ES', 'spanish');

// Obtener nombre del día en español
$dies = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
$mesos = ['', 'enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
$diaSetmana = $dies[date('w')];
$dia = date('d');
$mes = $mesos[(int)date('m')];
$any = date('Y');
$dataAvui = "$diaSetmana, $dia de $mes de $any";

// Obtener próximas sesiones (7 días)
$properes = $session->properesSessions(7);
$properesSessions = $properes->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Panel de Control</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Libre+Baskerville:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="css/dashboard-calendar.css?v=<?php echo time(); ?>">
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
                    <?php $__firstName = trim($_SESSION['user_name'] ?? 'Usuario'); $__firstName = $__firstName !== '' ? explode(' ', $__firstName)[0] : 'Usuario'; ?>
                    <h1>Bienvenida, <?php echo htmlspecialchars($__firstName); ?></h1>
                    <p class="date-today"><?php echo $dataAvui; ?></p>
                </div>
            </div>
            <div class="top-bar-right">
                <div class="user-profile">
                    <img src="../img/Logo.png" alt="Profile" class="profile-img">
                    <span class="profile-name"><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Usuario'); ?></span>
                </div>
            </div>
        </header>

    <!-- Dashboard Content -->
    <div class="content-wrapper">
            <!-- Stats Cards -->
            <section class="stats-section">
                <div class="stat-card">
                    <div class="stat-icon patients">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $statsPacients['total']; ?></h3>
                        <p>Pacientes Totales</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon today">
                        <i class="fas fa-calendar-day"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo count($today_appointments); ?></h3>
                        <p>Sesiones Hoy</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon week">
                        <i class="fas fa-calendar-week"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $sessionsSetmana; ?></h3>
                        <p>Sesiones Esta Semana</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon pending">
                        <i class="fas fa-euro-sign"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo number_format($ingressosMes, 0, ',', '.'); ?>€</h3>
                        <p>Ingresos del Mes</p>
                    </div>
                </div>
            </section>

            <!-- Main Grid with Aside -->
            <div class="dashboard-grid-with-aside">
                <!-- Main Content Area -->
                <div class="main-content-area">
                    <!-- Day Calendar View -->
                    <section class="card day-calendar-card">
                        <div class="card-header">
                            <h2><i class="fas fa-calendar-day"></i> Agenda de Hoy</h2>
                            <div class="calendar-date">
                                <div class="calendar-day-num"><?php echo date('d'); ?></div>
                                <div class="calendar-month"><?php echo strtoupper(substr($mes, 0, 3)); ?></div>
                            </div>
                        </div>
                        <div class="day-calendar">
                            <?php if (empty($today_appointments)): ?>
                                <div class="no-appointments">
                                    <i class="fas fa-coffee"></i>
                                    <p>No hay sesiones programadas para hoy</p>
                                    <button class="action-btn-main" onclick="obrirModalSession()">
                                        <span>Programar sesión</span>
                                    </button>
                                </div>
                            <?php else: ?>
                                <div class="timeline">
                                    <?php 
                                    $horaInici = 8; // 8:00
                                    $horaFi = 20;   // 20:00
                                    
                                    for ($hora = $horaInici; $hora <= $horaFi; $hora++): 
                                        $horaStr = sprintf('%02d:00', $hora);
                                    ?>
                                        <div class="timeline-hour">
                                            <div class="hour-label"><?php echo $horaStr; ?></div>
                                            <div class="hour-line"></div>
                                            <div class="hour-slots">
                                                <?php foreach ($today_appointments as $apt): 
                                                    $aptHora = (int)substr($apt['hora_inici'], 0, 2);
                                                    if ($aptHora == $hora):
                                                        $horaIniciFormat = substr($apt['hora_inici'], 0, 5);
                                                        $horaFiFormat = substr($apt['hora_fi'], 0, 5);
                                                        $duracio = (strtotime($apt['hora_fi']) - strtotime($apt['hora_inici'])) / 60;
                                                        $alturaPercentatge = ($duracio / 60) * 100;
                                                        
                                                        // Determinar color según estado
                                                        $colorClass = '';
                                                        switch ($apt['estat_sessio']) {
                                                            case 'Programada':
                                                                $colorClass = 'programada';
                                                                break;
                                                            case 'Realitzada':
                                                                $colorClass = 'realitzada';
                                                                break;
                                                            case 'Cancel·lada':
                                                                $colorClass = 'cancelada';
                                                                break;
                                                            default:
                                                                $colorClass = 'programada';
                                                        }
                                                ?>
                                                    <div class="appointment-slot <?php echo $colorClass; ?>" style="height: <?php echo $alturaPercentatge; ?>%;">
                                                        <div class="slot-time"><?php echo $horaIniciFormat; ?> - <?php echo $horaFiFormat; ?></div>
                                                        <div class="slot-patient"><?php echo htmlspecialchars($apt['nom_complet_pacient']); ?></div>
                                                        <div class="slot-type"><?php echo $apt['tipus_sessio']; ?> • <?php echo $apt['ubicacio']; ?></div>
                                                    </div>
                                                <?php 
                                                    endif;
                                                endforeach; 
                                                ?>
                                            </div>
                                        </div>
                                    <?php endfor; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </section>
                </div>

                <!-- Sidebar Derecha (Aside) -->
                <aside class="dashboard-aside">
                    <!-- Quick Actions -->
                    <section class="card quick-actions-card">
                        <div class="card-header">
                            <h2><i class="fas fa-bolt"></i> Acciones Rápidas</h2>
                        </div>
                        <div class="quick-actions-aside">
                            <button class="action-btn-aside" onclick="obrirModalSession()">
                                <i class="fas fa-calendar-plus"></i>
                                <span>Nueva Sesión</span>
                            </button>
                            <button class="action-btn-aside" onclick="obrirModalPacient()">
                                <i class="fas fa-user-plus"></i>
                                <span>Nuevo Paciente</span>
                            </button>
                            <a href="gpacients.php" class="action-btn-aside">
                                <i class="fas fa-users"></i>
                                <span>Ver Pacientes</span>
                            </a>
                            <a href="gsessions.php" class="action-btn-aside">
                                <i class="fas fa-calendar-alt"></i>
                                <span>Ver Sesiones</span>
                            </a>
                            <a href="estadisticas.php" class="action-btn-aside">
                                <i class="fas fa-chart-bar"></i>
                                <span>Estadísticas</span>
                            </a>
                            <a href="facturacion.php" class="action-btn-aside">
                                <i class="fas fa-file-invoice-dollar"></i>
                                <span>Facturación</span>
                            </a>
                        </div>
                    </section>

                    <!-- Upcoming Appointments -->
                    <section class="card upcoming-card">
                        <div class="card-header">
                            <h2><i class="fas fa-calendar-week"></i> Próximas Sesiones</h2>
                        </div>
                        <div class="upcoming-list">
                            <?php if (empty($properesSessions)): ?>
                                <div class="empty-state">
                                    <i class="fas fa-calendar-check"></i>
                                    <p>No hay sesiones próximas</p>
                                </div>
                            <?php else: ?>
                                <?php 
                                $count = 0;
                                foreach ($properesSessions as $apt): 
                                    if ($count >= 5) break; // Máximo 5
                                    $count++;
                                    $fecha = date('d/m', strtotime($apt['data_sessio']));
                                    $diaNom = $dies[date('w', strtotime($apt['data_sessio']))];
                                ?>
                                <div class="upcoming-item">
                                    <div class="upcoming-date">
                                        <div class="date-day"><?php echo date('d', strtotime($apt['data_sessio'])); ?></div>
                                        <div class="date-month"><?php echo strtoupper(substr($mesos[(int)date('m', strtotime($apt['data_sessio']))], 0, 3)); ?></div>
                                    </div>
                                    <div class="upcoming-info">
                                        <h4><?php echo htmlspecialchars($apt['nom_complet_pacient']); ?></h4>
                                        <span class="upcoming-time">
                                            <i class="fas fa-clock"></i> <?php echo substr($apt['hora_inici'], 0, 5); ?> - <?php echo $apt['tipus_sessio']; ?>
                                        </span>
                                    </div>
                                    <div class="upcoming-price">
                                        <?php echo number_format($apt['preu_sessio'], 0); ?>€
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </section>
                </aside>
            </div>
        </div>
    </div>

    <!-- Modal para Nueva Sesión -->
    <div class="modal" id="modalSession">
        <div class="modal-content modal-large">
            <div class="modal-header">
                <h2><i class="fas fa-calendar-plus"></i> Nueva Sesión</h2>
                <button class="modal-close" onclick="tancarModalSession()">&times;</button>
            </div>
            <form id="formSession" method="POST" action="gsessions.php">
                <input type="hidden" name="accio" value="crear">
                
                <div class="modal-body">
                    <!-- Datos Básicos -->
                    <div class="form-section">
                        <h3><i class="fas fa-info-circle"></i> Información Básica</h3>
                        <div class="form-grid">
                            <div class="form-group full-width">
                                <label for="id_pacient">Paciente *</label>
                                <select id="id_pacient" name="id_pacient" required class="form-control">
                                    <option value="">Selecciona un paciente...</option>
                                    <?php 
                                    $pacientObj = new Pacient($pdo);
                                    $stmtPacients = $pacientObj->llegirTots();
                                    $pacients = $stmtPacients->fetchAll(PDO::FETCH_ASSOC);
                                    foreach ($pacients as $p): 
                                    ?>
                                    <option value="<?php echo $p['id_pacient']; ?>">
                                        <?php echo htmlspecialchars($p['nom'] . ' ' . $p['cognoms']); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="data_sessio">Fecha *</label>
                                <input type="date" id="data_sessio" name="data_sessio" required class="form-control" value="<?php echo date('Y-m-d'); ?>">
                            </div>
                            <div class="form-group">
                                <label for="hora_inici">Hora Inicio *</label>
                                <input type="time" id="hora_inici" name="hora_inici" required class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="hora_fi">Hora Fin *</label>
                                <input type="time" id="hora_fi" name="hora_fi" required class="form-control">
                            </div>
                        </div>
                    </div>

                    <!-- Tipo y Ubicación -->
                    <div class="form-section">
                        <h3><i class="fas fa-cog"></i> Configuración</h3>
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="tipus_sessio">Tipo de Sesión *</label>
                                <select id="tipus_sessio" name="tipus_sessio" required class="form-control">
                                    <option value="Individual">Individual</option>
                                    <option value="Parella">Pareja</option>
                                    <option value="Familiar">Familiar</option>
                                    <option value="Grup">Grupo</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="ubicacio">Ubicación *</label>
                                <select id="ubicacio" name="ubicacio" required class="form-control">
                                    <option value="Presencial">Presencial</option>
                                    <option value="Online">Online</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="estat_sessio">Estado</label>
                                <select id="estat_sessio" name="estat_sessio" class="form-control">
                                    <option value="Programada" selected>Programada</option>
                                    <option value="Realitzada">Realizada</option>
                                    <option value="Cancel·lada">Cancelada</option>
                                    <option value="No assistida">No asistida</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="preu_sessio">Precio (€) *</label>
                                <input type="number" id="preu_sessio" name="preu_sessio" step="0.01" min="0" required placeholder="60.00" class="form-control" value="60">
                            </div>
                        </div>
                    </div>

                    <!-- Notas -->
                    <div class="form-section">
                        <h3><i class="fas fa-sticky-note"></i> Notas del Terapeuta</h3>
                        <div class="form-group">
                            <label for="notes_terapeuta">Observaciones y notas de la sesión</label>
                            <textarea id="notes_terapeuta" name="notes_terapeuta" rows="4" placeholder="Incluye observaciones relevantes sobre la sesión..." class="form-control"></textarea>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="tancarModalSession()">
                        <i class="fas fa-times"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal para Nuevo Paciente -->
    <div class="modal" id="modalPacient">
        <div class="modal-content modal-large">
            <div class="modal-header">
                <h2><i class="fas fa-user-plus"></i> Nuevo Paciente</h2>
                <button class="modal-close" onclick="tancarModalPacient()">&times;</button>
            </div>
            <form id="formPacient" method="POST" action="gpacients.php">
                <input type="hidden" name="accio" value="crear">
                
                <div class="modal-body">
                    <!-- Datos Personales -->
                    <div class="form-section">
                        <h3><i class="fas fa-user"></i> Datos Personales</h3>
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="nom">Nombre *</label>
                                <input type="text" id="nom" name="nom" required maxlength="50" class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="cognoms">Apellidos *</label>
                                <input type="text" id="cognoms" name="cognoms" required maxlength="100" class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="data_naixement">Fecha de Nacimiento</label>
                                <input type="date" id="data_naixement" name="data_naixement" class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="sexe">Sexo</label>
                                <select id="sexe" name="sexe" class="form-control">
                                    <option value="">Selecciona...</option>
                                    <option value="Hombre">Hombre</option>
                                    <option value="Mujer">Mujer</option>
                                    <option value="Otro">Otro</option>
                                    <option value="No especificado">No especificado</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Datos de Contacto -->
                    <div class="form-section">
                        <h3><i class="fas fa-address-book"></i> Datos de Contacto</h3>
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="telefon">Teléfono *</label>
                                <input type="tel" id="telefon" name="telefon" maxlength="20" placeholder="+34 666 55 55 55" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" id="email" name="email" maxlength="100" class="form-control">
                            </div>
                            <div class="form-group full-width">
                                <label for="adreca">Dirección</label>
                                <input type="text" id="adreca" name="adreca" class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="ciutat">Ciudad</label>
                                <input type="text" id="ciutat" name="ciutat" maxlength="50" class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="codi_postal">Código Postal</label>
                                <input type="text" id="codi_postal" name="codi_postal" maxlength="10" class="form-control">
                            </div>
                        </div>
                    </div>

                    <!-- Información Médica -->
                    <div class="form-section">
                        <h3><i class="fas fa-notes-medical"></i> Información Médica</h3>
                        <div class="form-group">
                            <label for="antecedents_medics">Antecedentes Médicos</label>
                            <textarea id="antecedents_medics" name="antecedents_medics" rows="3" class="form-control"></textarea>
                        </div>
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="medicacio_actual">Medicación Actual</label>
                                <textarea id="medicacio_actual" name="medicacio_actual" rows="2" class="form-control"></textarea>
                            </div>
                            <div class="form-group">
                                <label for="alergies">Alergias</label>
                                <textarea id="alergies" name="alergies" rows="2" class="form-control"></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Contacto de Emergencia -->
                    <div class="form-section">
                        <h3><i class="fas fa-phone-alt"></i> Contacto de Emergencia</h3>
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="contacte_emergencia_nom">Nombre</label>
                                <input type="text" id="contacte_emergencia_nom" name="contacte_emergencia_nom" maxlength="100" class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="contacte_emergencia_telefon">Teléfono</label>
                                <input type="tel" id="contacte_emergencia_telefon" name="contacte_emergencia_telefon" maxlength="20" class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="contacte_emergencia_relacio">Relación</label>
                                <input type="text" id="contacte_emergencia_relacio" name="contacte_emergencia_relacio" maxlength="50" placeholder="Ej: Madre, Esposo..." class="form-control">
                            </div>
                        </div>
                    </div>

                    <!-- Observaciones -->
                    <div class="form-section">
                        <h3><i class="fas fa-clipboard"></i> Observaciones</h3>
                        <div class="form-group">
                            <label for="observacions">Notas adicionales sobre el paciente</label>
                            <textarea id="observacions" name="observacions" rows="3" class="form-control"></textarea>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="tancarModalPacient()">
                        <i class="fas fa-times"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <link rel="stylesheet" href="css/gsessions.css">
    <link rel="stylesheet" href="css/gpacients.css">
    <script src="js/dashboard.js"></script>
    <script>
        // Funciones para abrir modales
        function obrirModalSession() {
            document.getElementById('modalSession').classList.add('show');
            document.body.style.overflow = 'hidden';
        }

        function tancarModalSession() {
            document.getElementById('modalSession').classList.remove('show');
            document.body.style.overflow = 'auto';
        }

        function obrirModalPacient() {
            document.getElementById('modalPacient').classList.add('show');
            document.body.style.overflow = 'hidden';
        }

        function tancarModalPacient() {
            document.getElementById('modalPacient').classList.remove('show');
            document.body.style.overflow = 'auto';
        }

        // Auto-sugerencia de hora fin según tipo de sesión
        document.getElementById('tipus_sessio')?.addEventListener('change', function() {
            const horaIniciInput = document.getElementById('hora_inici');
            const horaFiInput = document.getElementById('hora_fi');
            
            if (horaIniciInput.value) {
                const duracions = {
                    'Individual': 60,
                    'Parella': 90,
                    'Familiar': 90,
                    'Grup': 120
                };
                
                const duracio = duracions[this.value] || 60;
                const horaInici = new Date('2000-01-01 ' + horaIniciInput.value);
                const horaFi = new Date(horaInici.getTime() + duracio * 60000);
                const horaFiStr = horaFi.toTimeString().slice(0, 5);
                horaFiInput.value = horaFiStr;
            }
        });

        document.getElementById('hora_inici')?.addEventListener('change', function() {
            const tipusSessio = document.getElementById('tipus_sessio').value;
            const horaFiInput = document.getElementById('hora_fi');
            
            if (tipusSessio && this.value) {
                const duracions = {
                    'Individual': 60,
                    'Parella': 90,
                    'Familiar': 90,
                    'Grup': 120
                };
                
                const duracio = duracions[tipusSessio] || 60;
                const horaInici = new Date('2000-01-01 ' + this.value);
                const horaFi = new Date(horaInici.getTime() + duracio * 60000);
                const horaFiStr = horaFi.toTimeString().slice(0, 5);
                horaFiInput.value = horaFiStr;
            }
        });

        // Cerrar modales al hacer clic fuera
        document.getElementById('modalSession')?.addEventListener('click', function(e) {
            if (e.target === this) {
                tancarModalSession();
            }
        });

        document.getElementById('modalPacient')?.addEventListener('click', function(e) {
            if (e.target === this) {
                tancarModalPacient();
            }
        });

        // Cerrar con tecla ESC
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                tancarModalSession();
                tancarModalPacient();
            }
        });
    </script>
</body>
</html>
