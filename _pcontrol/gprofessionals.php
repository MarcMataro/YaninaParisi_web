<?php
/**
 * Gestión de Profesionales - Panel de Control
 *
 * Permite crear, editar, eliminar y listar profesionales con sus especialidades.
 * Mantiene coherencia visual con el dashboard.
 *
 * @author Marc Mataró
 * @version 1.0.0
 * @date 2026-01-01
 */

session_start();

// No-cache headers
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

// Verificar autenticación
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: index.php');
    exit;
}

require_once 'includes/role_check.php';

require_once __DIR__ . '/../classes/connexio.php';
require_once __DIR__ . '/../classes/professionals.php';
require_once __DIR__ . '/../classes/especialitats.php';
require_once __DIR__ . '/../classes/professional_especialitat.php';
require_once __DIR__ . '/../classes/professional_certificacions.php';

try {
    $connexio = Connexio::getInstance();
    $pdo = $connexio->getConnexio();
} catch (Exception $e) {
    die('Error de conexión: ' . $e->getMessage());
}

$profModel = Professionals::getInstance();
$espModel = Especialitats::getInstance();
$relModel = ProfessionalEspecialitat::getInstance();
$certModel = ProfessionalCertificacions::getInstance();

// CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

$mensaje = '';
$tipoMensaje = '';

// Si hay mensaje via GET (después de PRG)
if (!empty($_GET['msg'])) {
    $mensaje = urldecode($_GET['msg']);
    $tipoMensaje = $_GET['type'] ?? 'info';
}

// Procesar acciones POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $posted_csrf = $_POST['csrf_token'] ?? '';
    
    if (empty($posted_csrf) || !hash_equals($_SESSION['csrf_token'], $posted_csrf)) {
        $mensaje = 'Token de seguridad inválido (CSRF).';
        $tipoMensaje = 'error';
    } else {
        $accion = $_POST['accion'] ?? '';

        try {
            switch ($accion) {
                // ============= ACCIONES DE PROFESIONALES =============
                case 'crear':
                    $profModel->setNom($_POST['nom'] ?? '');
                    $profModel->setCognoms($_POST['cognoms'] ?? '');
                    $profModel->setEmail($_POST['email'] ?? '');
                    $profModel->setTelefon($_POST['telefon'] ?? null);
                    $profModel->setDescripcio($_POST['descripcio'] ?? null);
                    $profModel->setDescripcioEs($_POST['descripcio_es'] ?? null);
                    $profModel->setNumCollegiat($_POST['num_collegiat'] ?? null);
                    $profModel->setAnysExperiencia(!empty($_POST['anys_experiencia']) ? (int)$_POST['anys_experiencia'] : null);
                    $profModel->setFoto($_POST['foto'] ?? null);
                    $profModel->setActiu(isset($_POST['actiu']) ? true : false);
                    $profModel->setVisibleWeb(isset($_POST['visible_web']) ? true : false);

                    $id = $profModel->crear();
                    if ($id) {
                        // Assignar especialitats
                        if (!empty($_POST['especialitats']) && is_array($_POST['especialitats'])) {
                            $relModel->sincronitzarEspecialitats($id, $_POST['especialitats']);
                        }
                        
                        $mensaje = "Profesional creado correctamente (ID: {$id})";
                        $tipoMensaje = 'success';
                    }
                    break;

                case 'actualizar':
                    $id = (int)($_POST['id'] ?? 0);
                    if ($id && $profModel->llegirPerId($id)) {
                        $profModel->setNom($_POST['nom'] ?? '');
                        $profModel->setCognoms($_POST['cognoms'] ?? '');
                        $profModel->setEmail($_POST['email'] ?? '');
                        $profModel->setTelefon($_POST['telefon'] ?? null);
                        $profModel->setDescripcio($_POST['descripcio'] ?? null);
                        $profModel->setDescripcioEs($_POST['descripcio_es'] ?? null);
                        $profModel->setNumCollegiat($_POST['num_collegiat'] ?? null);
                        $profModel->setAnysExperiencia(!empty($_POST['anys_experiencia']) ? (int)$_POST['anys_experiencia'] : null);
                        $profModel->setFoto($_POST['foto'] ?? null);
                        $profModel->setActiu(isset($_POST['actiu']) ? true : false);
                        $profModel->setVisibleWeb(isset($_POST['visible_web']) ? true : false);

                        if ($profModel->actualitzar()) {
                            // Sincronitzar especialitats
                            $especialitats = $_POST['especialitats'] ?? [];
                            $relModel->sincronitzarEspecialitats($id, is_array($especialitats) ? $especialitats : []);
                            
                            $mensaje = 'Profesional actualizado correctamente.';
                            $tipoMensaje = 'success';
                        }
                    } else {
                        $mensaje = 'Profesional no encontrado.';
                        $tipoMensaje = 'error';
                    }
                    break;

                case 'eliminar':
                    $idEliminar = (int)($_POST['id'] ?? 0);
                    if ($profModel->eliminar($idEliminar)) {
                        $mensaje = 'Profesional eliminado correctamente.';
                        $tipoMensaje = 'success';
                    } else {
                        $mensaje = 'Error al eliminar el profesional.';
                        $tipoMensaje = 'error';
                    }
                    break;

                case 'guardar_certificacions':
                    $prof_id = (int)($_POST['professional_id'] ?? 0);
                    $cert_ca = trim($_POST['certificacions_ca'] ?? '');
                    $cert_es = trim($_POST['certificacions_es'] ?? '');
                    
                    // Si hay acción de eliminar
                    if (!empty($_POST['accion_eliminar']) && $_POST['accion_eliminar'] === 'eliminar_certificacions') {
                        if ($certModel->eliminarPerProfessional($prof_id)) {
                            $mensaje = 'Certificaciones eliminadas correctamente.';
                            $tipoMensaje = 'success';
                        }
                    } elseif (!empty($cert_ca) && !empty($cert_es)) {
                        $result = $certModel->guardarCertificacions($prof_id, $cert_ca, $cert_es);
                        if ($result) {
                            $mensaje = 'Certificaciones guardadas correctamente.';
                            $tipoMensaje = 'success';
                        } else {
                            $mensaje = 'Error al guardar las certificaciones.';
                            $tipoMensaje = 'error';
                        }
                    } else {
                        $mensaje = 'Ambos campos (Catalán y Español) son obligatorios.';
                        $tipoMensaje = 'error';
                    }
                    
                    // Redirect mantiendo el professional_id
                    $redirect = 'gprofessionals.php?vista=certificacions&id=' . $prof_id;
                    header('Location: ' . $redirect . '&msg=' . urlencode($mensaje) . '&type=' . $tipoMensaje);
                    exit;

                case 'afegir_certificacio':
                    $prof_id = (int)($_POST['professional_id'] ?? 0);
                    $nova_cert_ca = trim($_POST['nova_cert_ca'] ?? '');
                    $nova_cert_es = trim($_POST['nova_cert_es'] ?? '');
                    
                    if (!empty($nova_cert_ca) && !empty($nova_cert_es)) {
                        // Obtenir certificacions actuals
                        $certActuals = $certModel->obtenirPerProfessional($prof_id);
                        
                        if ($certActuals) {
                            // Afegir a les existents
                            $cert_ca = trim($certActuals['certificacions_ca']) . "\n" . $nova_cert_ca;
                            $cert_es = trim($certActuals['certificacions_es']) . "\n" . $nova_cert_es;
                        } else {
                            // Primer registre
                            $cert_ca = $nova_cert_ca;
                            $cert_es = $nova_cert_es;
                        }
                        
                        if ($certModel->guardarCertificacions($prof_id, $cert_ca, $cert_es)) {
                            $mensaje = 'Certificación añadida correctamente.';
                            $tipoMensaje = 'success';
                        } else {
                            $mensaje = 'Error al añadir la certificación.';
                            $tipoMensaje = 'error';
                        }
                    } else {
                        $mensaje = 'Ambos campos son obligatorios.';
                        $tipoMensaje = 'error';
                    }
                    
                    $redirect = 'gprofessionals.php?vista=certificacions&id=' . $prof_id;
                    header('Location: ' . $redirect . '&msg=' . urlencode($mensaje) . '&type=' . $tipoMensaje);
                    exit;

                case 'eliminar_una_certificacio':
                    $prof_id = (int)($_POST['professional_id'] ?? 0);
                    $idioma = $_POST['idioma'] ?? '';
                    $index = (int)($_POST['index'] ?? -1);
                    
                    $certActuals = $certModel->obtenirPerProfessional($prof_id);
                    
                    if ($certActuals && $index >= 0) {
                        if ($idioma === 'ca') {
                            $llistat = array_filter(explode("\n", $certActuals['certificacions_ca']));
                            $llistat = array_values($llistat); // Reindexar
                            unset($llistat[$index]);
                            $cert_ca = implode("\n", $llistat);
                            $cert_es = $certActuals['certificacions_es'];
                        } else {
                            $llistat = array_filter(explode("\n", $certActuals['certificacions_es']));
                            $llistat = array_values($llistat); // Reindexar
                            unset($llistat[$index]);
                            $cert_ca = $certActuals['certificacions_ca'];
                            $cert_es = implode("\n", $llistat);
                        }
                        
                        // Si ambdues estan buides, eliminar tot
                        if (empty(trim($cert_ca)) && empty(trim($cert_es))) {
                            $certModel->eliminarPerProfessional($prof_id);
                            $mensaje = 'Certificación eliminada. No quedan más certificaciones.';
                        } else {
                            $certModel->guardarCertificacions($prof_id, $cert_ca, $cert_es);
                            $mensaje = 'Certificación eliminada correctamente.';
                        }
                        $tipoMensaje = 'success';
                    } else {
                        $mensaje = 'Error al eliminar la certificación.';
                        $tipoMensaje = 'error';
                    }
                    
                    $redirect = 'gprofessionals.php?vista=certificacions&id=' . $prof_id;
                    header('Location: ' . $redirect . '&msg=' . urlencode($mensaje) . '&type=' . $tipoMensaje);
                    exit;

                case 'eliminar_totes_certificacions':
                    $prof_id = (int)($_POST['professional_id'] ?? 0);
                    
                    if ($certModel->eliminarPerProfessional($prof_id)) {
                        $mensaje = 'Todas las certificaciones han sido eliminadas.';
                        $tipoMensaje = 'success';
                    } else {
                        $mensaje = 'Error al eliminar las certificaciones.';
                        $tipoMensaje = 'error';
                    }
                    
                    $redirect = 'gprofessionals.php?vista=certificacions&id=' . $prof_id;
                    header('Location: ' . $redirect . '&msg=' . urlencode($mensaje) . '&type=' . $tipoMensaje);
                    exit;

                case 'toggle_actiu':
                    $id = (int)($_POST['id'] ?? 0);
                    if ($profModel->llegirPerId($id)) {
                        if ($profModel->getActiu()) {
                            $profModel->desactivar($id);
                            $mensaje = 'Profesional desactivado.';
                        } else {
                            $profModel->activar($id);
                            $mensaje = 'Profesional activado.';
                        }
                        $tipoMensaje = 'success';
                    }
                    break;

                // ============= ACCIONES DE ESPECIALITATS =============
                case 'crear_especialitat':
                    $espModel->setNom($_POST['nom_especialitat'] ?? '');
                    $espModel->setNomEs($_POST['nom_especialitat_es'] ?? null);
                    $espModel->setDescripcio($_POST['descripcio_especialitat'] ?? null);
                    $espModel->setDescripcioEs($_POST['descripcio_especialitat_es'] ?? null);

                    $id = $espModel->crear();
                    if ($id) {
                        $mensaje = "Especialidad creada correctamente (ID: {$id})";
                        $tipoMensaje = 'success';
                    }
                    break;

                case 'actualizar_especialitat':
                    $id = (int)($_POST['id_especialitat'] ?? 0);
                    if ($id && $espModel->llegirPerId($id)) {
                        $espModel->setNom($_POST['nom_especialitat'] ?? '');
                        $espModel->setNomEs($_POST['nom_especialitat_es'] ?? null);
                        $espModel->setDescripcio($_POST['descripcio_especialitat'] ?? null);
                        $espModel->setDescripcioEs($_POST['descripcio_especialitat_es'] ?? null);

                        if ($espModel->actualitzar()) {
                            $mensaje = 'Especialidad actualizada correctamente.';
                            $tipoMensaje = 'success';
                        }
                    } else {
                        $mensaje = 'Especialidad no encontrada.';
                        $tipoMensaje = 'error';
                    }
                    break;

                case 'eliminar_especialitat':
                    $idEliminar = (int)($_POST['id_especialitat'] ?? 0);
                    // Comprobar si hay profesionales con esta especialidad
                    $numProfs = $relModel->comptarProfessionalsEspecialitat($idEliminar);
                    if ($numProfs > 0) {
                        $mensaje = "No se puede eliminar. Hay {$numProfs} profesional(es) con esta especialidad.";
                        $tipoMensaje = 'error';
                    } else {
                        if ($espModel->eliminar($idEliminar)) {
                            $mensaje = 'Especialidad eliminada correctamente.';
                            $tipoMensaje = 'success';
                        } else {
                            $mensaje = 'Error al eliminar la especialidad.';
                            $tipoMensaje = 'error';
                        }
                    }
                    break;
            }
        } catch (Exception $e) {
            $mensaje = 'Error: ' . $e->getMessage();
            $tipoMensaje = 'error';
        }

        // PRG: evitar re-envío de formulario
        $redirect = 'gprofessionals.php';
        $separator = '?';
        if (strpos($accion, 'especialitat') !== false) {
            $redirect .= '?seccion=especialitats';
            $separator = '&';
        }
        header('Location: ' . $redirect . $separator . 'msg=' . urlencode($mensaje) . '&type=' . $tipoMensaje);
        exit;
    }
}

// GET: vistas y filtros
$seccion = $_GET['seccion'] ?? 'professionals'; // professionals o especialitats
$vista = $_GET['vista'] ?? 'lista';
$idEditar = $_GET['id'] ?? null;
$idEditarEsp = $_GET['id_especialitat'] ?? null;
$filtroActiu = isset($_GET['actiu']) ? $_GET['actiu'] : '';
$filtroVisible = isset($_GET['visible_web']) ? $_GET['visible_web'] : '';
$busqueda = $_GET['q'] ?? '';

// Obtener lista de profesionales
$filtros = [];
if ($filtroActiu !== '') $filtros['actiu'] = (int)$filtroActiu;
if ($filtroVisible !== '') $filtros['visible_web'] = (int)$filtroVisible;

if (!empty($busqueda)) {
    $professionals = $profModel->cercar($busqueda);
} else {
    $professionals = $profModel->llistar($filtros);
}

// Obtener todas las especialidades
$todasEspecialitats = $espModel->llistarTotes();

// Búsqueda de especialidades
$busquedaEsp = $_GET['q_esp'] ?? '';
if (!empty($busquedaEsp)) {
    $especialitats = $espModel->cercar($busquedaEsp);
} else {
    $especialitats = $todasEspecialitats;
}

// Si estamos editando, cargar datos
$profEditar = null;
$especialitatsEditar = [];
$certificacionsEditar = null;
if ($vista === 'editar' && $idEditar) {
    $profEditar = $profModel->obtenirPerId($idEditar);
    if ($profEditar) {
        $especialitatsEditar = $relModel->obtenirIdsEspecialitatsProfessional($idEditar);
        $certificacionsEditar = $certModel->obtenirPerProfessional($idEditar);
    }
}

// Si estamos editando especialidad
$espEditar = null;
if ($vista === 'editar' && $idEditarEsp) {
    $espEditar = $espModel->obtenirPerId($idEditarEsp);
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Gestión de Profesionales - Panel</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Libre+Baskerville:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="css/configuracion.css">
    <style>
        .professionals-table { width:100%; border-collapse:collapse; margin-top:20px; }
        .professionals-table th, .professionals-table td { padding:12px; border-bottom:1px solid #eee; text-align:left; }
        
        /* Tabs para secciones */
        .section-tabs { display:flex; gap:8px; margin-bottom:20px; border-bottom:2px solid #eee; }
        .section-tab { padding:12px 24px; background:transparent; border:none; cursor:pointer; font-size:16px; font-weight:500; color:#666; border-bottom:3px solid transparent; transition:all 0.3s; }
        .section-tab:hover { color:#333; background:#f8f9fa; }
        .section-tab.active { color:#007bff; border-bottom-color:#007bff; }
        .professionals-table th { background:#f8f9fa; font-weight:600; }
        .professionals-actions button { margin-right:8px; padding:6px 12px; }
        .filter-row { display:flex; gap:12px; align-items:center; margin-bottom:16px; flex-wrap:wrap; }
        .form-grid { display:grid; grid-template-columns:1fr 1fr; gap:16px; }
        .form-grid-full { grid-column: 1 / -1; }
        .badge { padding:4px 8px; border-radius:4px; font-size:12px; font-weight:500; }
        .badge-success { background:#d4edda; color:#155724; }
        .badge-danger { background:#f8d7da; color:#721c24; }
        .badge-warning { background:#fff3cd; color:#856404; }
        .especialitats-selector { display:flex; flex-direction:column; gap:8px; max-height:200px; overflow-y:auto; border:1px solid #ddd; padding:12px; border-radius:4px; }
        .especialitat-item { display:flex; align-items:center; gap:8px; }
        .foto-preview { max-width:100px; max-height:100px; margin-top:8px; border-radius:4px; }
        .stats-cards { display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:16px; margin-bottom:24px; }
        .stat-card { background:white; padding:20px; border-radius:8px; box-shadow:0 2px 4px rgba(0,0,0,0.1); }
        .stat-card h3 { margin:0 0 8px 0; color:#666; font-size:14px; }
        .stat-card .number { font-size:32px; font-weight:700; color:#333; }
    </style>
</head>
<body>
    <link rel="icon" type="image/png" sizes="32x32" href="../img/Logo32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="../img/Logo16.png">
    <?php include 'includes/sidebar.php'; ?>

    <div class="main-content">
        <header class="top-bar">
            <div class="top-bar-left">
                <button class="menu-toggle" id="menuToggle"><i class="fas fa-bars"></i></button>
                <div class="top-bar-info">
                    <h1>Gestión de Profesionales</h1>
                    <p class="date-today">Administrar profesionales y especialidades</p>
                </div>
            </div>
            <div class="top-bar-right">
                <div class="user-profile">
                    <img src="../img/Logo.png" alt="Profile" class="profile-img">
                    <span class="profile-name"><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Usuario'); ?></span>
                </div>
            </div>
        </header>

        <div class="content-wrapper">
            <!-- Tabs de secciones -->
            <div class="section-tabs">
                <button class="section-tab <?php echo $seccion === 'professionals' ? 'active' : ''; ?>" 
                        onclick="cambiarSeccion('professionals')">
                    <i class="fas fa-user-md"></i> Profesionales
                </button>
                <button class="section-tab <?php echo $seccion === 'especialitats' ? 'active' : ''; ?>" 
                        onclick="cambiarSeccion('especialitats')">
                    <i class="fas fa-briefcase-medical"></i> Especialidades
                </button>
            </div>

            <?php if ($seccion === 'professionals'): ?>
                <!-- SECCIÓN DE PROFESIONALES -->
            <?php if ($vista === 'lista'): ?>
                <div style="display:flex;justify-content:flex-end;margin-bottom:16px;">
                    <button class="btn btn-primary" onclick="mostrarVista('crear')">
                        <i class="fas fa-plus"></i> Nuevo Profesional
                    </button>
                </div>
            <?php else: ?>
                <div style="display:flex;justify-content:flex-end;margin-bottom:16px;">
                    <button class="btn btn-secondary" onclick="mostrarVista('lista')">
                        <i class="fas fa-arrow-left"></i> Volver al Listado
                    </button>
                </div>
            <?php endif; ?>

                <?php if ($mensaje): ?>
                    <div class="alert alert-<?php echo $tipoMensaje === 'success' ? 'success' : 'danger'; ?>">
                        <?php echo htmlspecialchars($mensaje); ?>
                    </div>
                <?php endif; ?>

                <?php if ($vista === 'lista'): ?>
                    <!-- Estadísticas -->
                    <div class="stats-cards">
                        <div class="stat-card">
                            <h3>Total Profesionales</h3>
                            <div class="number"><?php echo $profModel->comptar(); ?></div>
                        </div>
                        <div class="stat-card">
                            <h3>Activos</h3>
                            <div class="number"><?php echo $profModel->comptar(['actiu' => 1]); ?></div>
                        </div>
                        <div class="stat-card">
                            <h3>Visibles en Web</h3>
                            <div class="number"><?php echo $profModel->comptar(['visible_web' => 1]); ?></div>
                        </div>
                        <div class="stat-card">
                            <h3>Especialidades</h3>
                            <div class="number"><?php echo $espModel->comptar(); ?></div>
                        </div>
                    </div>

                    <!-- Filtros y búsqueda -->
                    <div class="card">
                        <div class="card-header">
                            <h2><i class="fas fa-filter"></i> Filtros y Búsqueda</h2>
                        </div>
                        <div class="card-body">
                            <form method="GET" action="gprofessionals.php" class="filter-row">
                                <input type="text" name="q" placeholder="Buscar por nombre, apellidos o email..." 
                                       value="<?php echo htmlspecialchars($busqueda); ?>" 
                                       class="form-control" style="flex:1; min-width:250px;">
                                
                                <select name="actiu" class="form-control" style="width:150px;">
                                    <option value="">Todos</option>
                                    <option value="1" <?php echo $filtroActiu === '1' ? 'selected' : ''; ?>>Activos</option>
                                    <option value="0" <?php echo $filtroActiu === '0' ? 'selected' : ''; ?>>Inactivos</option>
                                </select>
                                
                                <select name="visible_web" class="form-control" style="width:150px;">
                                    <option value="">Visibilidad Web</option>
                                    <option value="1" <?php echo $filtroVisible === '1' ? 'selected' : ''; ?>>Visibles</option>
                                    <option value="0" <?php echo $filtroVisible === '0' ? 'selected' : ''; ?>>Ocultos</option>
                                </select>
                                
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search"></i> Buscar
                                </button>
                                
                                <a href="gprofessionals.php" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> Limpiar
                                </a>
                            </form>
                        </div>
                    </div>

                    <!-- Lista de profesionales -->
                    <div class="card">
                        <div class="card-header">
                            <h2><i class="fas fa-list"></i> Listado de Profesionales (<?php echo count($professionals); ?>)</h2>
                        </div>
                        <div class="card-body">
                            <?php if (empty($professionals)): ?>
                                <p>No se encontraron profesionales.</p>
                            <?php else: ?>
                                <table class="professionals-table">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Nombre Completo</th>
                                            <th>Email</th>
                                            <th>Teléfono</th>
                                            <th>Nº Colegiado</th>
                                            <th>Experiencia</th>
                                            <th>Especialidades</th>
                                            <th>Estado</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($professionals as $prof): ?>
                                            <?php 
                                                $especialitats = $relModel->obtenirEspecialitatsProfessional($prof['id']);
                                                $especialitatsNoms = array_column($especialitats, 'nom');
                                            ?>
                                            <tr>
                                                <td><?php echo $prof['id']; ?></td>
                                                <td>
                                                    <strong><?php echo htmlspecialchars($prof['nom'] . ' ' . $prof['cognoms']); ?></strong>
                                                </td>
                                                <td><?php echo htmlspecialchars($prof['email']); ?></td>
                                                <td><?php echo htmlspecialchars($prof['telefon'] ?? '-'); ?></td>
                                                <td><?php echo htmlspecialchars($prof['num_collegiat'] ?? '-'); ?></td>
                                                <td><?php echo $prof['anys_experiencia'] ? $prof['anys_experiencia'] . ' años' : '-'; ?></td>
                                                <td>
                                                    <?php if (!empty($especialitatsNoms)): ?>
                                                        <small><?php echo implode(', ', array_map('htmlspecialchars', $especialitatsNoms)); ?></small>
                                                    <?php else: ?>
                                                        <small style="color:#999;">Sin especialidades</small>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if ($prof['actiu']): ?>
                                                        <span class="badge badge-success">Activo</span>
                                                    <?php else: ?>
                                                        <span class="badge badge-danger">Inactivo</span>
                                                    <?php endif; ?>
                                                    <?php if ($prof['visible_web']): ?>
                                                        <span class="badge badge-success">Web</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="professionals-actions">
                                                    <a href="?vista=editar&id=<?php echo $prof['id']; ?>" 
                                                       class="btn btn-sm btn-primary" title="Editar">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    
                                                    <a href="?vista=certificacions&id=<?php echo $prof['id']; ?>" 
                                                       class="btn btn-sm btn-info" title="Gestionar Certificaciones">
                                                        <i class="fas fa-certificate"></i>
                                                    </a>
                                                    
                                                    <form method="POST" style="display:inline;" onsubmit="return confirm('¿Cambiar estado activo/inactivo?');">
                                                        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                                        <input type="hidden" name="accion" value="toggle_actiu">
                                                        <input type="hidden" name="id" value="<?php echo $prof['id']; ?>">
                                                        <button type="submit" class="btn btn-sm btn-warning" title="Toggle Activo">
                                                            <i class="fas fa-toggle-<?php echo $prof['actiu'] ? 'on' : 'off'; ?>"></i>
                                                        </button>
                                                    </form>
                                                    
                                                    <form method="POST" style="display:inline;" onsubmit="return confirm('¿Eliminar este profesional?');">
                                                        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                                        <input type="hidden" name="accion" value="eliminar">
                                                        <input type="hidden" name="id" value="<?php echo $prof['id']; ?>">
                                                        <button type="submit" class="btn btn-sm btn-danger" title="Eliminar">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            <?php endif; ?>
                        </div>
                    </div>

                <?php elseif ($vista === 'crear'): ?>
                    <!-- Formulario de creación -->
                    <div class="card">
                        <div class="card-header">
                            <h2><i class="fas fa-plus"></i> Crear Nuevo Profesional</h2>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="gprofessionals.php">
                                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                <input type="hidden" name="accion" value="crear">
                                
                                <div class="form-grid">
                                    <div class="form-group">
                                        <label for="nom">Nombre *</label>
                                        <input type="text" id="nom" name="nom" class="form-control" required>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="cognoms">Apellidos *</label>
                                        <input type="text" id="cognoms" name="cognoms" class="form-control" required>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="email">Email *</label>
                                        <input type="email" id="email" name="email" class="form-control" required>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="telefon">Teléfono</label>
                                        <input type="text" id="telefon" name="telefon" class="form-control">
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="num_collegiat">Nº Colegiado</label>
                                        <input type="text" id="num_collegiat" name="num_collegiat" class="form-control">
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="anys_experiencia">Años de Experiencia</label>
                                        <input type="number" id="anys_experiencia" name="anys_experiencia" class="form-control" min="0">
                                    </div>
                                    
                                    <div class="form-group form-grid-full">
                                        <label for="foto">URL de la Foto</label>
                                        <input type="text" id="foto" name="foto" class="form-control" placeholder="../img/professionals/...">
                                    </div>
                                    
                                    <div class="form-group form-grid-full">
                                        <label for="descripcio">Descripción (Catalán)</label>
                                        <textarea id="descripcio" name="descripcio" class="form-control" rows="4"></textarea>
                                    </div>
                                    
                                    <div class="form-group form-grid-full">
                                        <label for="descripcio_es">Descripción (Español)</label>
                                        <textarea id="descripcio_es" name="descripcio_es" class="form-control" rows="4"></textarea>
                                    </div>
                                    
                                    <div class="form-group form-grid-full">
                                        <label>Especialidades</label>
                                        <div class="especialitats-selector">
                                            <?php foreach ($todasEspecialitats as $esp): ?>
                                                <div class="especialitat-item">
                                                    <input type="checkbox" 
                                                           id="esp_<?php echo $esp['id']; ?>" 
                                                           name="especialitats[]" 
                                                           value="<?php echo $esp['id']; ?>">
                                                    <label for="esp_<?php echo $esp['id']; ?>">
                                                        <?php echo htmlspecialchars($esp['nom']); ?>
                                                    </label>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label>
                                            <input type="checkbox" name="actiu" checked>
                                            Activo
                                        </label>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label>
                                            <input type="checkbox" name="visible_web" checked>
                                            Visible en Web
                                        </label>
                                    </div>
                                </div>
                                
                                <div class="form-actions" style="margin-top:20px; display:flex; gap:10px;">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Crear Profesional
                                    </button>
                                    <a href="gprofessionals.php" class="btn btn-secondary">
                                        <i class="fas fa-times"></i> Cancelar
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>

                <?php elseif ($vista === 'certificacions' && $idEditar): ?>
                    <!-- Gestión de Certificaciones -->
                    <?php 
                        $profCert = $profModel->obtenirPerId($idEditar);
                        if (!$profCert) {
                            echo '<div class="alert alert-danger">Profesional no encontrado.</div>';
                        } else {
                            $certificacions = $certModel->obtenirPerProfessional($idEditar);
                    ?>
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
                        <h2 style="margin:0;">
                            <i class="fas fa-certificate"></i> Certificaciones y Másters: 
                            <strong><?php echo htmlspecialchars($profCert['nom'] . ' ' . $profCert['cognoms']); ?></strong>
                        </h2>
                        <a href="gprofessionals.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Volver al Listado
                        </a>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <h2><i class="fas fa-graduation-cap"></i> Gestionar Certificaciones, Másters y Postgrados</h2>
                        </div>
                        <div class="card-body">
                            <?php
                                // Obtener las certificaciones como arrays
                                $certListCa = [];
                                $certListEs = [];
                                if ($certificacions) {
                                    $certListCa = array_filter(explode("\n", $certificacions['certificacions_ca']));
                                    $certListEs = array_filter(explode("\n", $certificacions['certificacions_es']));
                                }
                            ?>

                            <!-- Listado de certificaciones existentes -->
                            <?php if (!empty($certListCa) || !empty($certListEs)): ?>
                            <div style="margin-bottom:30px;">
                                <h3><i class="fas fa-list"></i> Certificaciones Actuales</h3>
                                
                                <div style="display:grid; grid-template-columns: 1fr 1fr; gap:20px; margin-top:15px;">
                                    <!-- Catalán -->
                                    <div>
                                        <h4 style="color:#2c3e50; margin-bottom:10px;">
                                            <i class="fas fa-flag"></i> Catalán (<?php echo count($certListCa); ?>)
                                        </h4>
                                        <ul style="list-style:none; padding:0;">
                                            <?php foreach ($certListCa as $index => $cert): ?>
                                                <li style="background:#f8f9fa; padding:10px; margin-bottom:8px; border-left:3px solid #3498db; display:flex; justify-content:space-between; align-items:center;">
                                                    <span><?php echo htmlspecialchars(trim($cert)); ?></span>
                                                    <form method="POST" style="display:inline;" onsubmit="return confirm('¿Eliminar esta certificación?');">
                                                        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                                        <input type="hidden" name="accion" value="eliminar_una_certificacio">
                                                        <input type="hidden" name="professional_id" value="<?php echo $idEditar; ?>">
                                                        <input type="hidden" name="idioma" value="ca">
                                                        <input type="hidden" name="index" value="<?php echo $index; ?>">
                                                        <button type="submit" class="btn btn-sm btn-danger" style="padding:4px 8px;">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>

                                    <!-- Español -->
                                    <div>
                                        <h4 style="color:#2c3e50; margin-bottom:10px;">
                                            <i class="fas fa-flag"></i> Español (<?php echo count($certListEs); ?>)
                                        </h4>
                                        <ul style="list-style:none; padding:0;">
                                            <?php foreach ($certListEs as $index => $cert): ?>
                                                <li style="background:#f8f9fa; padding:10px; margin-bottom:8px; border-left:3px solid #e74c3c; display:flex; justify-content:space-between; align-items:center;">
                                                    <span><?php echo htmlspecialchars(trim($cert)); ?></span>
                                                    <form method="POST" style="display:inline;" onsubmit="return confirm('¿Eliminar esta certificación?');">
                                                        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                                        <input type="hidden" name="accion" value="eliminar_una_certificacio">
                                                        <input type="hidden" name="professional_id" value="<?php echo $idEditar; ?>">
                                                        <input type="hidden" name="idioma" value="es">
                                                        <input type="hidden" name="index" value="<?php echo $index; ?>">
                                                        <button type="submit" class="btn btn-sm btn-danger" style="padding:4px 8px;">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                </div>

                                <div style="margin-top:15px;">
                                    <form method="POST" style="display:inline;" onsubmit="return confirm('¿Eliminar TODAS las certificaciones de este profesional?');">
                                        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                        <input type="hidden" name="accion" value="eliminar_totes_certificacions">
                                        <input type="hidden" name="professional_id" value="<?php echo $idEditar; ?>">
                                        <button type="submit" class="btn btn-danger">
                                            <i class="fas fa-trash-alt"></i> Eliminar Todas las Certificaciones
                                        </button>
                                    </form>
                                </div>
                            </div>
                            <hr style="margin:30px 0;">
                            <?php endif; ?>

                            <!-- Formulario para añadir nueva certificación -->
                            <div>
                                <h3><i class="fas fa-plus-circle"></i> Añadir Nueva Certificación</h3>
                                
                                <form method="POST" action="gprofessionals.php">
                                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                    <input type="hidden" name="accion" value="afegir_certificacio">
                                    <input type="hidden" name="professional_id" value="<?php echo $idEditar; ?>">
                                    
                                    <div class="alert alert-info" style="margin-bottom:20px;">
                                        <i class="fas fa-info-circle"></i> 
                                        <strong>Importante:</strong> Añade la misma certificación en ambos idiomas (catalán y español).
                                    </div>

                                    <div class="form-group">
                                        <label for="nova_cert_ca">
                                            <i class="fas fa-flag"></i> Certificación en Catalán *
                                        </label>
                                        <input type="text" id="nova_cert_ca" name="nova_cert_ca" 
                                               class="form-control" required
                                               placeholder="Ej: Màster en Psicologia Clínica - Universitat de Barcelona (2020)">
                                    </div>
                                    
                                    <div class="form-group" style="margin-top:15px;">
                                        <label for="nova_cert_es">
                                            <i class="fas fa-flag"></i> Certificación en Español *
                                        </label>
                                        <input type="text" id="nova_cert_es" name="nova_cert_es" 
                                               class="form-control" required
                                               placeholder="Ej: Máster en Psicología Clínica - Universidad de Barcelona (2020)">
                                    </div>
                                    
                                    <div class="form-actions" style="margin-top:20px; display:flex; gap:10px;">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-plus"></i> Añadir Certificación
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <?php } ?>

                <?php elseif ($vista === 'editar' && $profEditar): ?>
                    <!-- Formulario de edición -->
                    <div class="card">
                        <div class="card-header">
                            <h2><i class="fas fa-edit"></i> Editar Profesional</h2>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="gprofessionals.php">
                                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                <input type="hidden" name="accion" value="actualizar">
                                <input type="hidden" name="id" value="<?php echo $profEditar['id']; ?>">
                                
                                <div class="form-grid">
                                    <div class="form-group">
                                        <label for="nom">Nombre *</label>
                                        <input type="text" id="nom" name="nom" class="form-control" 
                                               value="<?php echo htmlspecialchars($profEditar['nom']); ?>" required>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="cognoms">Apellidos *</label>
                                        <input type="text" id="cognoms" name="cognoms" class="form-control" 
                                               value="<?php echo htmlspecialchars($profEditar['cognoms']); ?>" required>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="email">Email *</label>
                                        <input type="email" id="email" name="email" class="form-control" 
                                               value="<?php echo htmlspecialchars($profEditar['email']); ?>" required>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="telefon">Teléfono</label>
                                        <input type="text" id="telefon" name="telefon" class="form-control" 
                                               value="<?php echo htmlspecialchars($profEditar['telefon'] ?? ''); ?>">
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="num_collegiat">Nº Colegiado</label>
                                        <input type="text" id="num_collegiat" name="num_collegiat" class="form-control" 
                                               value="<?php echo htmlspecialchars($profEditar['num_collegiat'] ?? ''); ?>">
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="anys_experiencia">Años de Experiencia</label>
                                        <input type="number" id="anys_experiencia" name="anys_experiencia" class="form-control" 
                                               value="<?php echo $profEditar['anys_experiencia'] ?? ''; ?>" min="0">
                                    </div>
                                    
                                    <div class="form-group form-grid-full">
                                        <label for="foto">URL de la Foto</label>
                                        <input type="text" id="foto" name="foto" class="form-control" 
                                               value="<?php echo htmlspecialchars($profEditar['foto'] ?? ''); ?>">
                                        <?php if (!empty($profEditar['foto'])): ?>
                                            <img src="<?php echo htmlspecialchars($profEditar['foto']); ?>" 
                                                 alt="Preview" class="foto-preview">
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="form-group form-grid-full">
                                        <label for="descripcio">Descripción (Catalán)</label>
                                        <textarea id="descripcio" name="descripcio" class="form-control" rows="4"><?php echo htmlspecialchars($profEditar['descripcio'] ?? ''); ?></textarea>
                                    </div>
                                    
                                    <div class="form-group form-grid-full">
                                        <label for="descripcio_es">Descripción (Español)</label>
                                        <textarea id="descripcio_es" name="descripcio_es" class="form-control" rows="4"><?php echo htmlspecialchars($profEditar['descripcio_es'] ?? ''); ?></textarea>
                                    </div>
                                    
                                    <div class="form-group form-grid-full">
                                        <label>Especialidades</label>
                                        <div class="especialitats-selector">
                                            <?php foreach ($todasEspecialitats as $esp): ?>
                                                <div class="especialitat-item">
                                                    <input type="checkbox" 
                                                           id="esp_<?php echo $esp['id']; ?>" 
                                                           name="especialitats[]" 
                                                           value="<?php echo $esp['id']; ?>"
                                                           <?php echo in_array($esp['id'], $especialitatsEditar) ? 'checked' : ''; ?>>
                                                    <label for="esp_<?php echo $esp['id']; ?>">
                                                        <?php echo htmlspecialchars($esp['nom']); ?>
                                                    </label>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label>
                                            <input type="checkbox" name="actiu" <?php echo $profEditar['actiu'] ? 'checked' : ''; ?>>
                                            Activo
                                        </label>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label>
                                            <input type="checkbox" name="visible_web" <?php echo $profEditar['visible_web'] ? 'checked' : ''; ?>>
                                            Visible en Web
                                        </label>
                                    </div>
                                </div>
                                
                                <div class="form-actions" style="margin-top:20px; display:flex; gap:10px;">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Guardar Cambios
                                    </button>
                                    <a href="gprofessionals.php" class="btn btn-secondary">
                                        <i class="fas fa-times"></i> Cancelar
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>

                <?php else: ?>
                    <div class="alert alert-danger">
                        Profesional no encontrado.
                    </div>
                <?php endif; ?>
            
            <?php else: ?>
                <!-- SECCIÓN DE ESPECIALIDADES -->
                <?php if ($vista === 'lista'): ?>
                    <div style="display:flex;justify-content:flex-end;margin-bottom:16px;">
                        <button class="btn btn-primary" onclick="mostrarVista('crear', 'especialitats')">
                            <i class="fas fa-plus"></i> Nueva Especialidad
                        </button>
                    </div>

                    <?php if ($mensaje): ?>
                        <div class="alert alert-<?php echo $tipoMensaje === 'success' ? 'success' : 'danger'; ?>">
                            <?php echo htmlspecialchars($mensaje); ?>
                        </div>
                    <?php endif; ?>

                    <!-- Lista de especialidades -->
                    <div class="card">
                        <div class="card-header">
                            <h2><i class="fas fa-briefcase-medical"></i> Listado de Especialidades (<?php echo count($especialitats); ?>)</h2>
                        </div>
                        <div class="card-body">
                            <!-- Búsqueda -->
                            <form method="GET" action="gprofessionals.php" class="filter-row" style="margin-bottom:20px;">
                                <input type="hidden" name="seccion" value="especialitats">
                                <input type="text" name="q_esp" placeholder="Buscar especialidad..." 
                                       value="<?php echo htmlspecialchars($busquedaEsp); ?>" 
                                       class="form-control" style="flex:1; min-width:250px;">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search"></i> Buscar
                                </button>
                                <a href="gprofessionals.php?seccion=especialitats" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> Limpiar
                                </a>
                            </form>

                            <?php if (empty($especialitats)): ?>
                                <p>No se encontraron especialidades.</p>
                            <?php else: ?>
                                <table class="professionals-table">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Nombre</th>
                                            <th>Descripción</th>
                                            <th>Nº Profesionales</th>
                                            <th>Fecha Creación</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($especialitats as $esp): ?>
                                            <?php 
                                                $numProfs = $relModel->comptarProfessionalsEspecialitat($esp['id']);
                                            ?>
                                            <tr>
                                                <td><?php echo $esp['id']; ?></td>
                                                <td><strong><?php echo htmlspecialchars($esp['nom']); ?></strong></td>
                                                <td>
                                                    <?php if (!empty($esp['descripcio'])): ?>
                                                        <small><?php echo htmlspecialchars(substr($esp['descripcio'], 0, 100)); ?>
                                                        <?php echo strlen($esp['descripcio']) > 100 ? '...' : ''; ?></small>
                                                    <?php else: ?>
                                                        <small style="color:#999;">Sin descripción</small>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if ($numProfs > 0): ?>
                                                        <span class="badge badge-success"><?php echo $numProfs; ?> profesional(es)</span>
                                                    <?php else: ?>
                                                        <span style="color:#999;">0</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?php echo date('d/m/Y', strtotime($esp['created_at'])); ?></td>
                                                <td class="professionals-actions">
                                                    <a href="?seccion=especialitats&vista=editar&id_especialitat=<?php echo $esp['id']; ?>" 
                                                       class="btn btn-sm btn-primary" title="Editar">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    
                                                    <form method="POST" style="display:inline;" onsubmit="return confirm('¿Eliminar esta especialidad?<?php echo $numProfs > 0 ? ' (Tiene ' . $numProfs . ' profesional/es asignados)' : ''; ?>');">
                                                        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                                        <input type="hidden" name="accion" value="eliminar_especialitat">
                                                        <input type="hidden" name="id_especialitat" value="<?php echo $esp['id']; ?>">
                                                        <button type="submit" class="btn btn-sm btn-danger" 
                                                                title="Eliminar"
                                                                <?php echo $numProfs > 0 ? 'disabled' : ''; ?>>
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            <?php endif; ?>
                        </div>
                    </div>

                <?php elseif ($vista === 'crear'): ?>
                    <!-- Formulario de creación de especialidad -->
                    <div style="display:flex;justify-content:flex-end;margin-bottom:16px;">
                        <button class="btn btn-secondary" onclick="mostrarVista('lista', 'especialitats')">
                            <i class="fas fa-arrow-left"></i> Volver al Listado
                        </button>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <h2><i class="fas fa-plus"></i> Crear Nueva Especialidad</h2>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="gprofessionals.php">
                                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                <input type="hidden" name="accion" value="crear_especialitat">
                                
                                <div class="form-group">
                                    <label for="nom_especialitat">Nombre (Catalán) *</label>
                                    <input type="text" id="nom_especialitat" name="nom_especialitat" 
                                           class="form-control" required maxlength="150">
                                </div>
                                
                                <div class="form-group">
                                    <label for="nom_especialitat_es">Nombre (Español)</label>
                                    <input type="text" id="nom_especialitat_es" name="nom_especialitat_es" 
                                           class="form-control" maxlength="150">
                                </div>
                                
                                <div class="form-group">
                                    <label for="descripcio_especialitat">Descripción (Catalán)</label>
                                    <textarea id="descripcio_especialitat" name="descripcio_especialitat" 
                                              class="form-control" rows="4"></textarea>
                                </div>
                                
                                <div class="form-group">
                                    <label for="descripcio_especialitat_es">Descripción (Español)</label>
                                    <textarea id="descripcio_especialitat_es" name="descripcio_especialitat_es" 
                                              class="form-control" rows="4"></textarea>
                                </div>
                                
                                <div class="form-actions" style="margin-top:20px; display:flex; gap:10px;">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Crear Especialidad
                                    </button>
                                    <a href="gprofessionals.php?seccion=especialitats" class="btn btn-secondary">
                                        <i class="fas fa-times"></i> Cancelar
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>

                <?php elseif ($vista === 'editar' && $espEditar): ?>
                    <!-- Formulario de edición de especialidad -->
                    <div style="display:flex;justify-content:flex-end;margin-bottom:16px;">
                        <button class="btn btn-secondary" onclick="mostrarVista('lista', 'especialitats')">
                            <i class="fas fa-arrow-left"></i> Volver al Listado
                        </button>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <h2><i class="fas fa-edit"></i> Editar Especialidad</h2>
                        </div>
                        <div class="card-body">
                            <?php 
                                $numProfs = $relModel->comptarProfessionalsEspecialitat($espEditar['id']);
                                if ($numProfs > 0):
                            ?>
                                <div class="alert alert-warning" style="margin-bottom:15px;">
                                    <i class="fas fa-info-circle"></i> Esta especialidad está asignada a <strong><?php echo $numProfs; ?></strong> profesional(es).
                                </div>
                            <?php endif; ?>

                            <form method="POST" action="gprofessionals.php">
                                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                <input type="hidden" name="accion" value="actualizar_especialitat">
                                <input type="hidden" name="id_especialitat" value="<?php echo $espEditar['id']; ?>">
                                
                                <div class="form-group">
                                    <label for="nom_especialitat">Nombre (Catalán) *</label>
                                    <input type="text" id="nom_especialitat" name="nom_especialitat" 
                                           class="form-control" required maxlength="150"
                                           value="<?php echo htmlspecialchars($espEditar['nom']); ?>">
                                </div>
                                
                                <div class="form-group">
                                    <label for="nom_especialitat_es">Nombre (Español)</label>
                                    <input type="text" id="nom_especialitat_es" name="nom_especialitat_es" 
                                           class="form-control" maxlength="150"
                                           value="<?php echo htmlspecialchars($espEditar['nom_es'] ?? ''); ?>">
                                </div>
                                
                                <div class="form-group">
                                    <label for="descripcio_especialitat">Descripción (Catalán)</label>
                                    <textarea id="descripcio_especialitat" name="descripcio_especialitat" 
                                              class="form-control" rows="4"><?php echo htmlspecialchars($espEditar['descripcio'] ?? ''); ?></textarea>
                                </div>
                                
                                <div class="form-group">
                                    <label for="descripcio_especialitat_es">Descripción (Español)</label>
                                    <textarea id="descripcio_especialitat_es" name="descripcio_especialitat_es" 
                                              class="form-control" rows="4"><?php echo htmlspecialchars($espEditar['descripcio_es'] ?? ''); ?></textarea>
                                </div>
                                
                                <div class="form-actions" style="margin-top:20px; display:flex; gap:10px;">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Guardar Cambios
                                    </button>
                                    <a href="gprofessionals.php?seccion=especialitats" class="btn btn-secondary">
                                        <i class="fas fa-times"></i> Cancelar
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>

                <?php else: ?>
                    <div class="alert alert-danger">
                        Especialidad no encontrada.
                    </div>
                <?php endif; ?>

            <?php endif; // Fin de sección especialitats ?>

        </div>
    </div>

    <script>
        function cambiarSeccion(seccion) {
            window.location.href = 'gprofessionals.php?seccion=' + seccion;
        }

        function mostrarVista(vista, seccion) {
            let url = 'gprofessionals.php?vista=' + vista;
            if (seccion) {
                url += '&seccion=' + seccion;
            }
            window.location.href = url;
        }

        // Auto-hide alerts
        document.addEventListener('DOMContentLoaded', function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                setTimeout(() => {
                    alert.style.opacity = '0';
                    setTimeout(() => alert.remove(), 300);
                }, 5000);
            });
        });
    </script>
</body>
</html>
