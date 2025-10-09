<?php
/**
 * Gestión de Sesiones - Panel de Control
 * 
 * Página para gestionar las sesiones terapéuticas: crear, editar, visualizar y cambiar estados.
 * Utiliza la clase Session para todas las operaciones.
 * 
 * @author Marc Mataró
 * @version 1.0.0
 */

session_start();

// Verificar autenticación
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: index.php');
    exit;
}

// Incluir las clases necesarias
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

// Variables para gestionar mensajes
$missatge = '';
$tipusMissatge = '';

// Processar accions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accio = $_POST['accio'] ?? '';
    
    switch ($accio) {
        case 'crear':
            // Crear nueva sesión
            $session->id_pacient = $_POST['id_pacient'] ?? 0;
            $session->data_sessio = $_POST['data_sessio'] ?? null;
            $session->hora_inici = $_POST['hora_inici'] ?? null;
            $session->hora_fi = $_POST['hora_fi'] ?? null;
            $session->tipus_sessio = $_POST['tipus_sessio'] ?? 'Individual';
            $session->estat_sessio = $_POST['estat_sessio'] ?? 'Programada';
            $session->ubicacio = $_POST['ubicacio'] ?? 'Presencial';
            $session->preu_sessio = $_POST['preu_sessio'] ?? 0.00;
            $session->notes_terapeuta = $_POST['notes_terapeuta'] ?? null;
            
            $id = $session->crear();
            if ($id) {
                $missatge = "Sesión creada correctamente con ID: {$id}";
                $tipusMissatge = 'success';
            } else {
                $missatge = "Error al crear la sesión. Comprueba los datos.";
                $tipusMissatge = 'error';
            }
            break;
            
        case 'actualitzar':
            // Actualizar sesión existente
            $session->id_sessio = $_POST['id_sessio'] ?? 0;
            $session->id_pacient = $_POST['id_pacient'] ?? 0;
            $session->data_sessio = $_POST['data_sessio'] ?? null;
            $session->hora_inici = $_POST['hora_inici'] ?? null;
            $session->hora_fi = $_POST['hora_fi'] ?? null;
            $session->tipus_sessio = $_POST['tipus_sessio'] ?? 'Individual';
            $session->estat_sessio = $_POST['estat_sessio'] ?? 'Programada';
            $session->ubicacio = $_POST['ubicacio'] ?? 'Presencial';
            $session->preu_sessio = $_POST['preu_sessio'] ?? 0.00;
            $session->notes_terapeuta = $_POST['notes_terapeuta'] ?? null;
            
            if ($session->actualitzar()) {
                $missatge = "Sesión actualizada correctamente";
                $tipusMissatge = 'success';
            } else {
                $missatge = "Error al actualizar la sesión";
                $tipusMissatge = 'error';
            }
            break;
            
        case 'canviar_estat':
            // Cambiar estado de la sesión
            $session->id_sessio = $_POST['id_sessio'] ?? 0;
            $nouEstat = $_POST['nou_estat'] ?? '';
            
            if ($session->canviarEstat($nouEstat)) {
                $missatge = "Estado cambiado a '{$nouEstat}' correctamente";
                $tipusMissatge = 'success';
            } else {
                $missatge = "Error al cambiar el estado";
                $tipusMissatge = 'error';
            }
            break;
            
        case 'eliminar':
            // Eliminar sesión
            $session->id_sessio = $_POST['id_sessio'] ?? 0;
            if ($session->eliminar()) {
                $missatge = "Sesión eliminada correctamente";
                $tipusMissatge = 'success';
            } else {
                $missatge = "Error al eliminar la sesión";
                $tipusMissatge = 'error';
            }
            break;
    }
}

// Processar accions GET (AJAX)
if (isset($_GET['accio'])) {
    $accio = $_GET['accio'];
    
    if ($accio === 'obtenir' && isset($_GET['id'])) {
        // Obtenir dades d'una sessió per AJAX
        $id_sessio = intval($_GET['id']);
        $session->id_sessio = $id_sessio;
        
        header('Content-Type: application/json');
        if ($session->llegirUna()) {
            // Retornar les dades com a array associatiu
            $dades = [
                'id_sessio' => $session->id_sessio,
                'id_pacient' => $session->id_pacient,
                'data_sessio' => $session->data_sessio,
                'hora_inici' => $session->hora_inici,
                'hora_fi' => $session->hora_fi,
                'tipus_sessio' => $session->tipus_sessio,
                'estat_sessio' => $session->estat_sessio,
                'ubicacio' => $session->ubicacio,
                'preu_sessio' => $session->preu_sessio,
                'notes_terapeuta' => $session->notes_terapeuta,
                'data_creacio' => $session->data_creacio
            ];
            echo json_encode($dades);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Sesión no encontrada']);
        }
        exit;
    }
}

// Procesar acciones GET (visualización, filtros)
$filtre = $_GET['filtre'] ?? 'tots';
$id_pacient_filtre = $_GET['pacient'] ?? null;
$data_inici = $_GET['data_inici'] ?? null;
$data_fi = $_GET['data_fi'] ?? null;

// Obtener lista de sesiones según filtros
$estat_filtre = null;
switch ($filtre) {
    case 'programades':
        $estat_filtre = 'Programada';
        break;
    case 'realitzades':
        $estat_filtre = 'Realizada';
        break;
    case 'cancel·lades':
        $estat_filtre = 'Cancelada';
        break;
    case 'no_assistides':
        $estat_filtre = 'No asistida';
        break;
}

$stmt = $session->llegirTotes($id_pacient_filtre, $estat_filtre, $data_inici, $data_fi);
$sessions = $stmt->fetchAll();

// Obtener estadísticas
$stats = $session->obtenirEstadistiques();

// Obtener próximas sesiones (7 días)
$properes = $session->properesSessions(7);
$properesSessions = $properes->fetchAll();

// Obtener sesiones de hoy
$avui = $session->sessionsAvui();
$sessionsAvui = $avui->fetchAll();

// Obtener lista de pacientes para los selectores
$stmtPacients = $pacient->llegirTots('Activo');
$pacients = $stmtPacients->fetchAll();

// Calcular ingresos del mes
$ingressosMes = $session->ingressosAquestMes();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Sesiones - Panel de Control</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Libre+Baskerville:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="css/gsessions.css?v=<?php echo time(); ?>">
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
                    <h1>Gestión de Sesiones</h1>
                    <p class="date-today">Administra las sesiones terapéuticas</p>
                </div>
            </div>
            <div class="top-bar-right">
                <div class="user-profile">
                    <img src="../img/Logo.png" alt="Profile" class="profile-img">
                    <span class="profile-name"><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Usuario'); ?></span>
                </div>
            </div>
        </header>

        <!-- Contenedor principal con padding -->
        <div class="content-wrapper">
        
        <!-- Mensajes -->
        <?php if (!empty($missatge)): ?>
        <div class="alert alert-<?php echo $tipusMissatge; ?>" id="alertMessage">
            <i class="fas fa-<?php echo $tipusMissatge === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
            <span><?php echo htmlspecialchars($missatge); ?></span>
            <button class="alert-close" onclick="document.getElementById('alertMessage').remove()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <?php endif; ?>

        <!-- Estadísticas -->
        <div class="stats-container">
            <div class="stat-card">
                <div class="stat-icon" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $stats['total']; ?></h3>
                    <p>Total Sesiones</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $stats['programades']; ?></h3>
                    <p>Programadas</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $stats['realitzades']; ?></h3>
                    <p>Realizadas</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background: linear-gradient(135deg, #ffd89b 0%, #19547b 100%);">
                    <i class="fas fa-euro-sign"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo number_format($ingressosMes, 2, ',', '.'); ?>€</h3>
                    <p>Ingresos del Mes</p>
                </div>
            </div>
        </div>

        <!-- Resumen de Hoy y Próximas -->
        <div class="quick-view-container">
            <div class="quick-view-card">
                <div class="quick-view-header">
                    <h3><i class="fas fa-calendar-day"></i> Sesiones de Hoy</h3>
                    <span class="quick-count"><?php echo count($sessionsAvui); ?></span>
                </div>
                <div class="quick-view-body">
                    <?php if (empty($sessionsAvui)): ?>
                        <p class="no-data"><i class="fas fa-coffee"></i> No hay sesiones programadas para hoy</p>
                    <?php else: ?>
                        <?php foreach ($sessionsAvui as $s): ?>
                        <div class="session-item">
                            <div class="session-time">
                                <i class="fas fa-clock"></i> <?php echo substr($s['hora_inici'], 0, 5); ?> - <?php echo substr($s['hora_fi'], 0, 5); ?>
                            </div>
                            <div class="session-patient">
                                <strong><?php echo htmlspecialchars($s['nom_complet_pacient']); ?></strong>
                                <span class="session-type"><?php echo $s['tipus_sessio']; ?> - <?php echo $s['ubicacio']; ?></span>
                            </div>
                            <span class="badge badge-<?php echo strtolower(str_replace(' ', '-', $s['estat_sessio'])); ?>">
                                <?php echo $s['estat_sessio']; ?>
                            </span>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <div class="quick-view-card">
                <div class="quick-view-header">
                    <h3><i class="fas fa-calendar-week"></i> Próximos 7 Días</h3>
                    <span class="quick-count"><?php echo count($properesSessions); ?></span>
                </div>
                <div class="quick-view-body">
                    <?php if (empty($properesSessions)): ?>
                        <p class="no-data"><i class="fas fa-calendar-times"></i> No hay sesiones próximas</p>
                    <?php else: ?>
                        <?php foreach ($properesSessions as $s): ?>
                        <div class="session-item">
                            <div class="session-date">
                                <i class="fas fa-calendar"></i> <?php echo date('d/m', strtotime($s['data_sessio'])); ?>
                                <span><?php echo substr($s['hora_inici'], 0, 5); ?></span>
                            </div>
                            <div class="session-patient">
                                <strong><?php echo htmlspecialchars($s['nom_complet_pacient']); ?></strong>
                                <span class="session-type"><?php echo $s['tipus_sessio']; ?></span>
                            </div>
                            <span class="session-price"><?php echo number_format($s['preu_sessio'], 2); ?>€</span>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Barra de acciones -->
        <div class="actions-bar">
            <div class="filter-buttons">
                <button class="filter-btn <?php echo $filtre === 'tots' ? 'active' : ''; ?>" onclick="filtrarSessions('tots')">
                    <i class="fas fa-list"></i> Todas
                </button>
                <button class="filter-btn <?php echo $filtre === 'programades' ? 'active' : ''; ?>" onclick="filtrarSessions('programades')">
                    <i class="fas fa-clock"></i> Programadas
                </button>
                <button class="filter-btn <?php echo $filtre === 'realitzades' ? 'active' : ''; ?>" onclick="filtrarSessions('realitzades')">
                    <i class="fas fa-check-circle"></i> Realizadas
                </button>
                <button class="filter-btn <?php echo $filtre === 'cancel·lades' ? 'active' : ''; ?>" onclick="filtrarSessions('cancel·lades')">
                    <i class="fas fa-times-circle"></i> Canceladas
                </button>
            </div>
            
            <div class="action-buttons-right">
                <select id="filtrePatient" class="filter-select" onchange="filtrarPerPacient(this.value)">
                    <option value="">Todos los pacientes</option>
                    <?php foreach ($pacients as $p): ?>
                    <option value="<?php echo $p['id_pacient']; ?>" <?php echo $id_pacient_filtre == $p['id_pacient'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($p['nom'] . ' ' . $p['cognoms']); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
                
                <button class="btn btn-primary" onclick="mostrarFormulari('nou')">
                    <i class="fas fa-calendar-plus"></i> Nueva Sesión
                </button>
            </div>
        </div>

        <!-- Tabla de Sesiones -->
        <div class="card">
            <div class="card-header">
                <h2><i class="fas fa-calendar-alt"></i> Lista de Sesiones</h2>
            </div>
            
            <div class="table-responsive">
                <table class="sessions-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Fecha</th>
                            <th>Horario</th>
                            <th>Paciente</th>
                            <th>Tipo</th>
                            <th>Ubicación</th>
                            <th>Precio</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($sessions)): ?>
                        <tr>
                            <td colspan="9" class="text-center">
                                <i class="fas fa-inbox" style="font-size: 3rem; color: #ccc; margin: 20px 0;"></i>
                                <p>No se encontraron sesiones</p>
                            </td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($sessions as $s): ?>
                        <tr class="<?php echo strtotime($s['data_sessio']) < strtotime('today') ? 'session-past' : ''; ?>">
                            <td><strong>#<?php echo $s['id_sessio']; ?></strong></td>
                            <td>
                                <div class="session-date-cell">
                                    <i class="fas fa-calendar"></i>
                                    <?php echo date('d/m/Y', strtotime($s['data_sessio'])); ?>
                                </div>
                            </td>
                            <td>
                                <div class="session-time-cell">
                                    <i class="fas fa-clock"></i>
                                    <?php echo substr($s['hora_inici'], 0, 5); ?> - <?php echo substr($s['hora_fi'], 0, 5); ?>
                                </div>
                            </td>
                            <td>
                                <div class="patient-info">
                                    <div class="patient-avatar-small">
                                        <?php 
                                        $inicials = strtoupper(substr($s['nom'], 0, 1) . substr($s['cognoms'], 0, 1));
                                        echo $inicials;
                                        ?>
                                    </div>
                                    <strong><?php echo htmlspecialchars($s['nom_complet_pacient']); ?></strong>
                                </div>
                            </td>
                            <td>
                                <span class="tipo-badge tipo-<?php echo strtolower($s['tipus_sessio']); ?>">
                                    <?php echo $s['tipus_sessio']; ?>
                                </span>
                            </td>
                            <td>
                                <span class="ubicacio-badge ubicacio-<?php echo strtolower($s['ubicacio']); ?>">
                                    <i class="fas fa-<?php echo $s['ubicacio'] === 'Online' ? 'video' : 'building'; ?>"></i>
                                    <?php echo $s['ubicacio']; ?>
                                </span>
                            </td>
                            <td><strong><?php echo number_format($s['preu_sessio'], 2); ?>€</strong></td>
                            <td>
                                <span class="badge badge-<?php echo strtolower(str_replace(' ', '-', $s['estat_sessio'])); ?>">
                                    <?php echo $s['estat_sessio']; ?>
                                </span>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <button class="btn-action btn-view" onclick="veureDetalls(<?php echo $s['id_sessio']; ?>)" title="Ver detalles">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn-action btn-edit" onclick="editarSession(<?php echo $s['id_sessio']; ?>)" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn-action btn-status" onclick="canviarEstat(<?php echo $s['id_sessio']; ?>, '<?php echo $s['estat_sessio']; ?>')" title="Cambiar estado">
                                        <i class="fas fa-exchange-alt"></i>
                                    </button>
                                    <button class="btn-action btn-delete" onclick="confirmarEliminar(<?php echo $s['id_sessio']; ?>)" title="Eliminar">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        </div><!-- .content-wrapper -->

    </div><!-- .main-content -->

    <!-- Modal para Nueva/Editar Sesión -->
    <div class="modal" id="modalSession">
        <div class="modal-content modal-large">
            <div class="modal-header">
                <h2 id="modalTitle"><i class="fas fa-calendar-plus"></i> Nueva Sesión</h2>
                <button class="modal-close" onclick="tancarModal()">&times;</button>
            </div>
            <form id="formSession" method="POST" action="gsessions.php">
                <input type="hidden" name="accio" id="accioForm" value="crear">
                <input type="hidden" name="id_sessio" id="idSession" value="">
                
                <div class="modal-body">
                    <!-- Datos Básicos -->
                    <div class="form-section">
                        <h3><i class="fas fa-info-circle"></i> Información Básica</h3>
                        <div class="form-grid">
                            <div class="form-group full-width">
                                <label for="id_pacient">Paciente *</label>
                                <select id="id_pacient" name="id_pacient" required>
                                    <option value="">Selecciona un paciente...</option>
                                    <?php foreach ($pacients as $p): ?>
                                    <option value="<?php echo $p['id_pacient']; ?>">
                                        <?php echo htmlspecialchars($p['nom'] . ' ' . $p['cognoms']); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="data_sessio">Fecha *</label>
                                <input type="date" id="data_sessio" name="data_sessio" required>
                            </div>
                            <div class="form-group">
                                <label for="hora_inici">Hora Inicio *</label>
                                <input type="time" id="hora_inici" name="hora_inici" required>
                            </div>
                            <div class="form-group">
                                <label for="hora_fi">Hora Fin *</label>
                                <input type="time" id="hora_fi" name="hora_fi" required>
                            </div>
                        </div>
                    </div>

                    <!-- Tipo y Ubicación -->
                    <div class="form-section">
                        <h3><i class="fas fa-cog"></i> Configuración</h3>
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="tipus_sessio">Tipo de Sesión *</label>
                                <select id="tipus_sessio" name="tipus_sessio" required>
                                    <option value="Individual">Individual</option>
                                    <option value="Pareja">Pareja</option>
                                    <option value="Familiar">Familiar</option>
                                    <option value="Grupo">Grupo</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="ubicacio">Ubicación *</label>
                                <select id="ubicacio" name="ubicacio" required>
                                    <option value="Presencial">Presencial</option>
                                    <option value="Online">Online</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="estat_sessio">Estado</label>
                                <select id="estat_sessio" name="estat_sessio">
                                    <option value="Programada">Programada</option>
                                    <option value="Realizada">Realizada</option>
                                    <option value="Cancelada">Cancelada</option>
                                    <option value="No asistida">No asistida</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="preu_sessio">Precio (€) *</label>
                                <input type="number" id="preu_sessio" name="preu_sessio" step="0.01" min="0" required placeholder="0.00">
                            </div>
                        </div>
                    </div>

                    <!-- Notas -->
                    <div class="form-section">
                        <h3><i class="fas fa-sticky-note"></i> Notas del Terapeuta</h3>
                        <div class="form-group">
                            <label for="notes_terapeuta">Observaciones y notas de la sesión</label>
                            <textarea id="notes_terapeuta" name="notes_terapeuta" rows="5" placeholder="Incluye observaciones relevantes sobre la sesión..."></textarea>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="tancarModal()">
                        <i class="fas fa-times"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal para Ver Detalles -->
    <div class="modal" id="modalDetalls">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-calendar-alt"></i> Detalles de la Sesión</h2>
                <button class="modal-close" onclick="tancarModalDetalls()">&times;</button>
            </div>
            <div class="modal-body" id="detallsContent">
                <!-- Se llenará dinámicamente con JavaScript -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="tancarModalDetalls()">
                    <i class="fas fa-times"></i> Cerrar
                </button>
            </div>
        </div>
    </div>

    <!-- Modal para Cambiar Estado -->
    <div class="modal modal-small" id="modalCanviarEstat">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-exchange-alt"></i> Cambiar Estado</h2>
                <button class="modal-close" onclick="tancarModalEstat()">&times;</button>
            </div>
            <form method="POST" action="gsessions.php">
                <input type="hidden" name="accio" value="canviar_estat">
                <input type="hidden" name="id_sessio" id="idSessionEstat">
                
                <div class="modal-body">
                    <div class="form-group">
                        <label for="nou_estat">Nuevo Estado:</label>
                        <select id="nou_estat" name="nou_estat" class="form-control" required>
                            <option value="">Selecciona...</option>
                            <option value="Programada">Programada</option>
                            <option value="Realizada">Realizada</option>
                            <option value="Cancelada">Cancelada</option>
                            <option value="No asistida">No asistida</option>
                        </select>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="tancarModalEstat()">
                        <i class="fas fa-times"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-check"></i> Cambiar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="js/dashboard.js"></script>
    <script src="js/gsessions.js"></script>
</body>
</html>
