<?php
/**
 * Gestió de Pacients - Panell de Control
 * 
 * Pàgina per gestionar els pacients: crear, editar, visualitzar i canviar estats.
 * Utilitza la classe Pacient per totes les operacions.
 * 
 * @author Marc Mataró
 * @version 1.0.0
 */

session_start();

// Verificar autenticació
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: index.php');
    exit;
}

// Incloure les classes necessàries
require_once '../classes/connexio.php';
require_once '../classes/pacients.php';

// Obtenir connexió a la base de dades
try {
    $connexio = Connexio::getInstance();
    $pdo = $connexio->getConnexio();
} catch (Exception $e) {
    die("Error de connexió: " . $e->getMessage());
}

// Inicialitzar objecte Pacient
$pacient = new Pacient($pdo);

// Variables per gestionar missatges
$missatge = '';
$tipusMissatge = '';

// Processar accions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accio = $_POST['accio'] ?? '';
    
    switch ($accio) {
        case 'crear':
            // Crear nou pacient
            $pacient->nom = $_POST['nom'] ?? '';
            $pacient->cognoms = $_POST['cognoms'] ?? '';
            $pacient->data_naixement = $_POST['data_naixement'] ?? null;
            $pacient->sexe = $_POST['sexe'] ?? null;
            $pacient->telefon = $_POST['telefon'] ?? null;
            $pacient->email = $_POST['email'] ?? null;
            $pacient->adreca = $_POST['adreca'] ?? null;
            $pacient->ciutat = $_POST['ciutat'] ?? null;
            $pacient->codi_postal = $_POST['codi_postal'] ?? null;
            $pacient->antecedents_medics = $_POST['antecedents_medics'] ?? null;
            $pacient->medicacio_actual = $_POST['medicacio_actual'] ?? null;
            $pacient->alergies = $_POST['alergies'] ?? null;
            $pacient->contacte_emergencia_nom = $_POST['contacte_emergencia_nom'] ?? null;
            $pacient->contacte_emergencia_telefon = $_POST['contacte_emergencia_telefon'] ?? null;
            $pacient->contacte_emergencia_relacio = $_POST['contacte_emergencia_relacio'] ?? null;
            $pacient->estat = $_POST['estat'] ?? 'Activo';
            $pacient->observacions = $_POST['observacions'] ?? null;
            
            $id = $pacient->crear();
            if ($id) {
                $missatge = "Pacient creat correctament amb ID: {$id}";
                $tipusMissatge = 'success';
            } else {
                $missatge = "Error al crear el pacient. Comprova les dades.";
                $tipusMissatge = 'error';
            }
            break;
            
        case 'actualitzar':
            // Actualitzar pacient existent
            $pacient->id_pacient = $_POST['id_pacient'] ?? 0;
            $pacient->nom = $_POST['nom'] ?? '';
            $pacient->cognoms = $_POST['cognoms'] ?? '';
            $pacient->data_naixement = $_POST['data_naixement'] ?? null;
            $pacient->sexe = $_POST['sexe'] ?? null;
            $pacient->telefon = $_POST['telefon'] ?? null;
            $pacient->email = $_POST['email'] ?? null;
            $pacient->adreca = $_POST['adreca'] ?? null;
            $pacient->ciutat = $_POST['ciutat'] ?? null;
            $pacient->codi_postal = $_POST['codi_postal'] ?? null;
            $pacient->antecedents_medics = $_POST['antecedents_medics'] ?? null;
            $pacient->medicacio_actual = $_POST['medicacio_actual'] ?? null;
            $pacient->alergies = $_POST['alergies'] ?? null;
            $pacient->contacte_emergencia_nom = $_POST['contacte_emergencia_nom'] ?? null;
            $pacient->contacte_emergencia_telefon = $_POST['contacte_emergencia_telefon'] ?? null;
            $pacient->contacte_emergencia_relacio = $_POST['contacte_emergencia_relacio'] ?? null;
            $pacient->estat = $_POST['estat'] ?? 'Activo';
            $pacient->observacions = $_POST['observacions'] ?? null;
            
            if ($pacient->actualitzar()) {
                $missatge = "Pacient actualitzat correctament";
                $tipusMissatge = 'success';
            } else {
                $missatge = "Error al actualitzar el pacient";
                $tipusMissatge = 'error';
            }
            break;
            
        case 'canviar_estat':
            // Canviar estat del pacient
            $pacient->id_pacient = $_POST['id_pacient'] ?? 0;
            $nouEstat = $_POST['nou_estat'] ?? '';
            
            if ($pacient->canviarEstat($nouEstat)) {
                $missatge = "Estat canviat a '{$nouEstat}' correctament";
                $tipusMissatge = 'success';
            } else {
                $missatge = "Error al canviar l'estat";
                $tipusMissatge = 'error';
            }
            break;
    }
}

// Processar accions GET (visualització, edició)
$vista = $_GET['vista'] ?? 'llista';
$idPacient = $_GET['id'] ?? null;
$filtre = $_GET['filtre'] ?? 'tots';
$cerca = $_GET['cerca'] ?? '';

// Obtenir llista de pacients segons filtres
if (!empty($cerca)) {
    $stmt = $pacient->cercarPerNom($cerca);
} else {
    switch ($filtre) {
        case 'actius':
            $stmt = $pacient->llegirTots('Activo');
            break;
        case 'inactius':
            $stmt = $pacient->llegirTots('Inactivo');
            break;
        case 'alta':
            $stmt = $pacient->llegirTots('Alta');
            break;
        case 'seguiment':
            $stmt = $pacient->llegirTots('Seguimiento');
            break;
        default:
            $stmt = $pacient->llegirTots();
    }
}

$pacients = $stmt->fetchAll();

// DEBUG: Descomentar la siguiente línea para ver los datos recuperados
// echo '<pre>'; print_r($pacients); echo '</pre>'; die();

// Obtenir estadístiques
$stats = $pacient->obtenirEstadistiques();

// Si estem editant, carregar dades del pacient
$pacientEditar = null;
if ($vista === 'editar' && $idPacient) {
    $pacient->id_pacient = $idPacient;
    if ($pacient->llegirUn()) {
        $pacientEditar = $pacient;
    }
}
?>
<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestió de Pacients - Panel de Control</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Libre+Baskerville:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="css/gpacients.css?v=<?php echo time(); ?>">
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
                    <h1>Gestión de Pacientes</h1>
                    <p class="date-today">Administra la información de tus pacientes</p>
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

        <!-- Estadístiques -->
        <div class="stats-container">
            <div class="stat-card">
                <div class="stat-icon" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $stats['total']; ?></h3>
                    <p>Total Pacientes</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                    <i class="fas fa-user-check"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $stats['actius']; ?></h3>
                    <p>Activos</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                    <i class="fas fa-user-clock"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $stats['seguiment']; ?></h3>
                    <p>Seguimiento</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
                    <i class="fas fa-user-shield"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $stats['alta']; ?></h3>
                    <p>Alta</p>
                </div>
            </div>
        </div>

        <!-- Barra d'accions -->
        <div class="actions-bar">
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="text" id="searchInput" placeholder="Buscar por nombre o apellidos..." 
                       value="<?php echo htmlspecialchars($cerca); ?>"
                       onkeyup="if(event.key === 'Enter') buscarPacient()">
            </div>
            
            <div class="filter-buttons">
                <button class="filter-btn <?php echo $filtre === 'tots' ? 'active' : ''; ?>" onclick="filtrarPacients('tots')">
                    <i class="fas fa-list"></i> Todos
                </button>
                <button class="filter-btn <?php echo $filtre === 'actius' ? 'active' : ''; ?>" onclick="filtrarPacients('actius')">
                    <i class="fas fa-check-circle"></i> Activos
                </button>
                <button class="filter-btn <?php echo $filtre === 'seguiment' ? 'active' : ''; ?>" onclick="filtrarPacients('seguiment')">
                    <i class="fas fa-heartbeat"></i> Seguimiento
                </button>
            </div>
            
            <button class="btn btn-primary" onclick="mostrarFormulari('nou')">
                <i class="fas fa-user-plus"></i> Nuevo Paciente
            </button>
        </div>

        <!-- Taula de Pacients -->
        <?php if ($vista === 'llista' || empty($vista)): ?>
        <div class="card">
            <div class="card-header">
                <h2><i class="fas fa-users"></i> Lista de Pacientes</h2>
            </div>
            
            <div class="table-responsive">
                <table class="patients-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre Completo</th>
                            <th>Edad</th>
                            <th>Teléfono</th>
                            <th>Email</th>
                            <th>Estado</th>
                            <th>Registro</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($pacients)): ?>
                        <tr>
                            <td colspan="9" class="text-center">
                                <i class="fas fa-inbox" style="font-size: 3rem; color: #ccc; margin: 20px 0;"></i>
                                <p>No se encontraron pacientes</p>
                            </td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($pacients as $p): ?>
                        <tr>
                            <td><strong>#<?php echo $p['id_pacient']; ?></strong></td>
                            <td>
                                <div class="patient-name">
                                    <div class="patient-avatar">
                                        <?php 
                                        $inicials = strtoupper(substr($p['nom'], 0, 1) . substr($p['cognoms'], 0, 1));
                                        echo $inicials;
                                        ?>
                                    </div>
                                    <div>
                                        <strong><?php echo htmlspecialchars($p['nom'] . ' ' . $p['cognoms']); ?></strong>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <?php 
                                if ($p['data_naixement']) {
                                    $edat = date_diff(date_create($p['data_naixement']), date_create('today'))->y;
                                    echo $edat . ' años';
                                } else {
                                    echo '-';
                                }
                                ?>
                            </td>
                            <td><?php echo htmlspecialchars($p['telefon'] ?? '-'); ?></td>
                            <td><?php echo htmlspecialchars($p['email'] ?? '-'); ?></td>
                            <td>
                                <?php 
                                $estado = $p['estat'] ?? 'Activo';
                                $estadoClass = strtolower($estado);
                                ?>
                                <span class="badge badge-<?php echo $estadoClass; ?>">
                                    <?php echo htmlspecialchars($estado); ?>
                                </span>
                            </td>
                            <td><?php echo date('d/m/Y', strtotime($p['data_registre'])); ?></td>
                            <td>
                                <div class="action-buttons">
                                    <button class="btn-action btn-view" onclick="veureDetalls(<?php echo $p['id_pacient']; ?>)" title="Ver detalles">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn-action btn-edit" onclick="editarPacient(<?php echo $p['id_pacient']; ?>)" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn-action btn-status" onclick="canviarEstat(<?php echo $p['id_pacient']; ?>, '<?php echo $p['estat']; ?>')" title="Cambiar estado">
                                        <i class="fas fa-exchange-alt"></i>
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
        <?php endif; ?>
        
        </div><!-- .content-wrapper -->

    </div><!-- .main-content -->

    <!-- Modal para Nuevo/Editar Paciente -->
    <div class="modal" id="modalPacient">
        <div class="modal-content modal-large">
            <div class="modal-header">
                <h2 id="modalTitle"><i class="fas fa-user-plus"></i> Nuevo Paciente</h2>
                <button class="modal-close" onclick="tancarModal()">&times;</button>
            </div>
            <form id="formPacient" method="POST" action="gpacients.php">
                <input type="hidden" name="accio" id="accioForm" value="crear">
                <input type="hidden" name="id_pacient" id="idPacient" value="">
                
                <div class="modal-body">
                    <!-- Datos Personales -->
                    <div class="form-section">
                        <h3><i class="fas fa-user"></i> Datos Personales</h3>
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="nom">Nombre *</label>
                                <input type="text" id="nom" name="nom" required maxlength="50">
                            </div>
                            <div class="form-group">
                                <label for="cognoms">Apellidos *</label>
                                <input type="text" id="cognoms" name="cognoms" required maxlength="100">
                            </div>
                            <div class="form-group">
                                <label for="data_naixement">Fecha de Nacimiento</label>
                                <input type="date" id="data_naixement" name="data_naixement">
                            </div>
                            <div class="form-group">
                                <label for="sexe">Sexo</label>
                                <select id="sexe" name="sexe">
                                    <option value="">Selecciona...</option>
                                    <option value="Hombre">Hombre</option>
                                    <option value="Mujer">Mujer</option>
                                    <option value="Otro">Otro</option>
                                    <option value="No especificado">No especificado</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="estat">Estado</label>
                                <select id="estat" name="estat">
                                    <option value="Activo">Activo</option>
                                    <option value="Inactivo">Inactivo</option>
                                    <option value="Alta">Alta</option>
                                    <option value="Seguimiento">Seguimiento</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Datos de Contacto -->
                    <div class="form-section">
                        <h3><i class="fas fa-address-book"></i> Datos de Contacto</h3>
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="telefon">Teléfono</label>
                                <input type="tel" id="telefon" name="telefon" maxlength="20" placeholder="+34 666 55 55 55">
                            </div>
                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" id="email" name="email" maxlength="100">
                            </div>
                            <div class="form-group full-width">
                                <label for="adreca">Dirección</label>
                                <input type="text" id="adreca" name="adreca">
                            </div>
                            <div class="form-group">
                                <label for="ciutat">Ciudad</label>
                                <input type="text" id="ciutat" name="ciutat" maxlength="50">
                            </div>
                            <div class="form-group">
                                <label for="codi_postal">Código Postal</label>
                                <input type="text" id="codi_postal" name="codi_postal" maxlength="10">
                            </div>
                        </div>
                    </div>

                    <!-- Información Médica -->
                    <div class="form-section">
                        <h3><i class="fas fa-notes-medical"></i> Información Médica Relevante</h3>
                        <div class="form-grid">
                            <div class="form-group full-width">
                                <label for="antecedents_medics">Antecedentes Médicos</label>
                                <textarea id="antecedents_medics" name="antecedents_medics" rows="3"></textarea>
                            </div>
                            <div class="form-group full-width">
                                <label for="medicacio_actual">Medicación Actual</label>
                                <textarea id="medicacio_actual" name="medicacio_actual" rows="2"></textarea>
                            </div>
                            <div class="form-group full-width">
                                <label for="alergies">Alergias</label>
                                <textarea id="alergies" name="alergies" rows="2"></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Contacto de Emergencia -->
                    <div class="form-section">
                        <h3><i class="fas fa-phone-alt"></i> Contacto de Emergencia</h3>
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="contacte_emergencia_nom">Nombre del Contacto</label>
                                <input type="text" id="contacte_emergencia_nom" name="contacte_emergencia_nom" maxlength="100">
                            </div>
                            <div class="form-group">
                                <label for="contacte_emergencia_telefon">Teléfono del Contacto</label>
                                <input type="tel" id="contacte_emergencia_telefon" name="contacte_emergencia_telefon" maxlength="20">
                            </div>
                            <div class="form-group">
                                <label for="contacte_emergencia_relacio">Relación</label>
                                <input type="text" id="contacte_emergencia_relacio" name="contacte_emergencia_relacio" maxlength="50" placeholder="Familiar, amigo, etc.">
                            </div>
                        </div>
                    </div>

                    <!-- Observaciones -->
                    <div class="form-section">
                        <h3><i class="fas fa-sticky-note"></i> Observaciones</h3>
                        <div class="form-group">
                            <label for="observacions">Notas generales</label>
                            <textarea id="observacions" name="observacions" rows="3"></textarea>
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
        <div class="modal-content modal-large">
            <div class="modal-header">
                <h2><i class="fas fa-user"></i> Detalles del Paciente</h2>
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

    <script src="js/dashboard.js"></script>
    <script src="js/gpacients.js"></script>
</body>
</html>
