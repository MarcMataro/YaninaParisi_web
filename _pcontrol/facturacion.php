<?php
/**
 * Gestión de Facturación - Panel de Control
 * 
 * Página para gestionar los pagos de las sesiones terapéuticas.
 * Utiliza la clase Pagament para todas las operaciones.
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
require_once '../classes/pagaments.php';
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
$pagament = new Pagament($pdo);
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
            // Crear nuevo pago
            $pagament->id_sessio = $_POST['id_sessio'] ?? 0;
            $pagament->data_pagament = $_POST['data_pagament'] ?? date('Y-m-d');
            $pagament->import = $_POST['import'] ?? 0.00;
            $pagament->metode_pagament = $_POST['metode_pagament'] ?? 'Efectivo';
            $pagament->estat = $_POST['estat'] ?? 'Completado';
            $pagament->facturat = isset($_POST['facturat']) ? 1 : 0;
            $pagament->numero_factura = $_POST['numero_factura'] ?? null;
            $pagament->observacions = $_POST['observacions'] ?? null;
            
            if ($pagament->crear()) {
                $_SESSION['missatge'] = "Pago registrado correctamente con ID: {$pagament->id_pagament}";
                $_SESSION['tipusMissatge'] = 'success';
                header('Location: facturacion.php');
                exit;
            } else {
                $_SESSION['missatge'] = "Error al registrar el pago. Comprueba los datos.";
                $_SESSION['tipusMissatge'] = 'error';
                header('Location: facturacion.php');
                exit;
            }
            break;
            
        case 'actualitzar':
            // Actualizar pago existente
            $pagament->id_pagament = $_POST['id_pagament'] ?? 0;
            $pagament->id_sessio = $_POST['id_sessio'] ?? 0;
            $pagament->data_pagament = $_POST['data_pagament'] ?? date('Y-m-d');
            $pagament->import = $_POST['import'] ?? 0.00;
            $pagament->metode_pagament = $_POST['metode_pagament'] ?? 'Efectivo';
            $pagament->estat = $_POST['estat'] ?? 'Completado';
            $pagament->facturat = isset($_POST['facturat']) ? 1 : 0;
            $pagament->numero_factura = $_POST['numero_factura'] ?? null;
            $pagament->observacions = $_POST['observacions'] ?? null;
            
            // DEBUG: Log per veure l'estat rebut
            error_log("DEBUG Facturacion - Actualitzar pagament #{$pagament->id_pagament} - Estat POST: " . ($_POST['estat'] ?? 'NULL') . " - Estat Objecte: {$pagament->estat}");
            
            if ($pagament->actualitzar()) {
                // DEBUG: Verificar estat després d'actualitzar
                $dades_verificacio = $pagament->llegirUn($pagament->id_pagament);
                error_log("DEBUG Facturacion - Després actualitzar - Estat BD: " . ($dades_verificacio['estat'] ?? 'NULL'));
                
                $_SESSION['missatge'] = "Pago actualizado correctamente";
                $_SESSION['tipusMissatge'] = 'success';
                header('Location: facturacion.php');
                exit;
            } else {
                $_SESSION['missatge'] = "Error al actualizar el pago";
                $_SESSION['tipusMissatge'] = 'error';
                header('Location: facturacion.php');
                exit;
            }
            break;
            
        case 'anular':
            // Anular pago
            $id_pagament = $_POST['id_pagament'] ?? 0;
            $motiu = $_POST['motiu'] ?? 'Anulado por el usuario';
            
            if ($pagament->anularPagament($id_pagament, $motiu)) {
                $missatge = "Pago anulado correctamente";
                $tipusMissatge = 'success';
            } else {
                $missatge = "Error al anular el pago";
                $tipusMissatge = 'error';
            }
            break;
            
        case 'marcar_facturat':
            // Marcar como facturado
            $id_pagament = $_POST['id_pagament'] ?? 0;
            $numero_factura = $_POST['numero_factura'] ?? '';
            
            if ($pagament->marcarComFacturat($id_pagament, $numero_factura)) {
                $missatge = "Pago marcado como facturado";
                $tipusMissatge = 'success';
            } else {
                $missatge = "Error al marcar como facturado";
                $tipusMissatge = 'error';
            }
            break;
            
        case 'desmarcar_facturat':
            // Desmarcar facturado
            $id_pagament = $_POST['id_pagament'] ?? 0;
            
            if ($pagament->desmarcarFacturat($id_pagament)) {
                $missatge = "Pago desmarcado como facturado";
                $tipusMissatge = 'success';
            } else {
                $missatge = "Error al desmarcar facturado";
                $tipusMissatge = 'error';
            }
            break;
            
        case 'eliminar':
            // Eliminar pago
            $id_pagament = $_POST['id_pagament'] ?? 0;
            if ($pagament->eliminar($id_pagament)) {
                $missatge = "Pago eliminado correctamente";
                $tipusMissatge = 'success';
            } else {
                $missatge = "Error al eliminar el pago";
                $tipusMissatge = 'error';
            }
            break;
    }
}

// Processar accions GET (AJAX)
if (isset($_GET['accio'])) {
    $accio = $_GET['accio'];
    
    if ($accio === 'obtenir' && isset($_GET['id'])) {
        // Obtenir dades d'un pagament per AJAX
        $id_pagament = intval($_GET['id']);
        $dades = $pagament->llegirUn($id_pagament);
        
        header('Content-Type: application/json');
        if ($dades) {
            echo json_encode($dades);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Pago no encontrado']);
        }
        exit;
    }
}

// Procesar filtros GET
$filtre_estat = $_GET['estat'] ?? null;
$filtre_metode = $_GET['metode'] ?? null;
$data_inici = $_GET['data_inici'] ?? null;
$data_fi = $_GET['data_fi'] ?? null;
$filtre_facturat = isset($_GET['facturat']) ? ($_GET['facturat'] === '1' ? true : false) : null;

// Obtener lista de pagos según filtros
$pagaments = $pagament->llegirTots($filtre_estat, $filtre_metode, $data_inici, $data_fi, $filtre_facturat);

// Obtener estadísticas
$stats = $pagament->obtenirEstadistiques();

// Obtener últimos pagos
$ultimsPagaments = $pagament->ultimsPagaments(5);

// Obtener sesiones sin pagar
$sessionsSensePagar = $pagament->sessionsSensePagar();

// Obtener pagos no facturados
$pagamentsNoFacturats = $pagament->pagamentsNoFacturats();

// Obtener todos los pacientes para el selector
$pacients = $pacient->llegirTots();

// Obtener ingresos del mes actual
$ingressosMes = $pagament->ingressosAquestMes();

// Arrays de traducción catalán -> castellano (ja no necessaris perquè la BD està en castellà)
$traduccio_metodes = [
    'Efectivo' => 'Efectivo',
    'Tarjeta' => 'Tarjeta',
    'Transferencia' => 'Transferencia',
    'Bizum' => 'Bizum'
];

$traduccio_estats = [
    'Completado' => 'Completado',
    'Pendiente' => 'Pendiente',
    'Anulado' => 'Anulado'
];

$icons_estats = [
    'Completado' => 'check-circle',
    'Pendiente' => 'clock',
    'Anulado' => 'times-circle'
];

// Recuperar missatges de la sessió
$missatge = $_SESSION['missatge'] ?? null;
$tipusMissatge = $_SESSION['tipusMissatge'] ?? null;
unset($_SESSION['missatge'], $_SESSION['tipusMissatge']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Facturación - Yanina Parisi</title>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@7.0.0/css/all.min.css">
    
    <!-- Fonts -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Libre+Baskerville:wght@400;700&display=swap">
    
    <!-- Estilos -->
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="css/facturacion.css">
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
                    <h1>Gestión de Pagos</h1>
                    <p class="date-today">Administración de cobros y facturación</p>
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
        
        <!-- Botón Nuevo Pago -->
        <div class="page-actions">
            <button class="btn btn-primary" onclick="mostrarFormulari('nou')">
                <i class="fas fa-plus"></i> Nuevo Pago
            </button>
        </div>
            
        <!-- Estadísticas -->
        <div class="stats-container">
            <div class="stat-card">
                <div class="stat-icon" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                    <i class="fas fa-money-bill-wave"></i>
                </div>
                <div class="stat-info">
                    <h3><?= number_format($stats['import_total'] ?? 0, 2) ?> €</h3>
                    <p>Ingresos Totales</p>
                    <span class="stat-detail"><?= $stats['total_pagaments'] ?? 0 ?> pagos</span>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon" style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-info">
                    <h3><?= number_format($stats['import_completat'] ?? 0, 2) ?> €</h3>
                    <p>Pagos Completados</p>
                    <span class="stat-detail"><?= $stats['pagaments_completats'] ?? 0 ?> pagos</span>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-info">
                    <h3><?= number_format($stats['import_pendent'] ?? 0, 2) ?> €</h3>
                    <p>Pagos Pendientes</p>
                    <span class="stat-detail"><?= $stats['pagaments_pendents'] ?? 0 ?> pagos</span>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <div class="stat-info">
                    <h3><?= number_format($ingressosMes['import_total'] ?? 0, 2) ?> €</h3>
                    <p>Este Mes</p>
                    <span class="stat-detail"><?= $ingressosMes['num_pagaments'] ?? 0 ?> pagos</span>
                    </div>
                </div>
            </div>
            
            <!-- Filtros -->
            <div class="card">
                <div class="card-header">
                    <h2><i class="fas fa-filter"></i> Filtros</h2>
                </div>
                <div class="card-body">
                    <form method="GET" class="filters-form">
                        <div class="filter-group">
                            <label for="estat">Estado:</label>
                            <select id="estat" name="estat" onchange="this.form.submit()">
                                <option value="">Todos</option>
                                <option value="Completado" <?= $filtre_estat === 'Completado' ? 'selected' : '' ?>>Completados</option>
                                <option value="Pendiente" <?= $filtre_estat === 'Pendiente' ? 'selected' : '' ?>>Pendientes</option>
                                <option value="Anulado" <?= $filtre_estat === 'Anulado' ? 'selected' : '' ?>>Anulados</option>
                            </select>
                        </div>
                        
                        <div class="filter-group">
                            <label for="metode">Método de Pago:</label>
                            <select id="metode" name="metode" onchange="this.form.submit()">
                                <option value="">Todos</option>
                                <option value="Efectivo" <?= $filtre_metode === 'Efectivo' ? 'selected' : '' ?>>Efectivo</option>
                                <option value="Tarjeta" <?= $filtre_metode === 'Tarjeta' ? 'selected' : '' ?>>Tarjeta</option>
                                <option value="Transferencia" <?= $filtre_metode === 'Transferencia' ? 'selected' : '' ?>>Transferencia</option>
                                <option value="Bizum" <?= $filtre_metode === 'Bizum' ? 'selected' : '' ?>>Bizum</option>
                            </select>
                        </div>
                        
                        <div class="filter-group">
                            <label for="facturat">Facturación:</label>
                            <select id="facturat" name="facturat" onchange="this.form.submit()">
                                <option value="">Todos</option>
                                <option value="1" <?= $filtre_facturat === true ? 'selected' : '' ?>>Facturados</option>
                                <option value="0" <?= $filtre_facturat === false ? 'selected' : '' ?>>No Facturados</option>
                            </select>
                        </div>
                        
                        <div class="filter-group">
                            <label for="data_inici">Desde:</label>
                            <input type="date" id="data_inici" name="data_inici" value="<?= htmlspecialchars($data_inici ?? '') ?>">
                        </div>
                        
                        <div class="filter-group">
                            <label for="data_fi">Hasta:</label>
                            <input type="date" id="data_fi" name="data_fi" value="<?= htmlspecialchars($data_fi ?? '') ?>">
                        </div>
                        
                        <div class="filter-actions">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i> Filtrar
                            </button>
                            <a href="facturacion.php" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Limpiar
                            </a>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Lista de Pagos -->
            <div class="card">
                <div class="card-header">
                    <h2><i class="fas fa-list"></i> Lista de Pagos</h2>
                    <span class="badge"><?= count($pagaments) ?> resultados</span>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Fecha Pago</th>
                                    <th>Paciente</th>
                                    <th>Sesión</th>
                                    <th>Importe</th>
                                    <th>Método</th>
                                    <th>Estado</th>
                                    <th>Facturado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($pagaments)): ?>
                                    <tr>
                                        <td colspan="9" class="text-center">
                                            <div class="empty-state">
                                                <i class="fas fa-inbox"></i>
                                                <p>No hay pagos registrados</p>
                                            </div>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($pagaments as $pag): ?>
                                        <!-- DEBUG: Mostrar claus disponibles -->
                                        <?php if (isset($_GET['debug'])): ?>
                                        <tr>
                                            <td colspan="9" style="background: #fff3cd; padding: 10px;">
                                                <strong>DEBUG - Claus disponibles:</strong>
                                                <?= implode(', ', array_keys($pag)) ?>
                                                <br><strong>metode_pagament:</strong> <?= isset($pag['metode_pagament']) ? $pag['metode_pagament'] : 'NO EXISTEIX' ?>
                                                <br><strong>estat:</strong> <?= isset($pag['estat']) ? $pag['estat'] : 'NO EXISTEIX' ?>
                                                <br><strong>facturat:</strong> <?= isset($pag['facturat']) ? $pag['facturat'] : 'NO EXISTEIX' ?>
                                            </td>
                                        </tr>
                                        <?php endif; ?>
                                        <tr>
                                            <td><strong>#<?= $pag['id_pagament'] ?></strong></td>
                                            <td><?= date('d/m/Y', strtotime($pag['data_pagament'])) ?></td>
                                            <td>
                                                <div class="patient-info">
                                                    <strong><?= htmlspecialchars($pag['nom_pacient'] . ' ' . $pag['cognoms_pacient']) ?></strong>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="session-info">
                                                    <span class="session-date"><?= date('d/m/Y', strtotime($pag['data_sessio'])) ?></span>
                                                    <span class="session-type badge-info"><?= htmlspecialchars($pag['tipus_sessio']) ?></span>
                                                </div>
                                            </td>
                                            <td><strong class="amount"><?= number_format($pag['import'], 2) ?> €</strong></td>
                                            <td>
                                                <span class="table-badge badge-<?= strtolower($pag['metode_pagament']) ?>">
                                                    <?= htmlspecialchars($pag['metode_pagament']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="table-badge badge-<?= strtolower($pag['estat']) ?>">
                                                    <i class="fas fa-<?= $pag['estat'] === 'Completado' ? 'check-circle' : ($pag['estat'] === 'Pendiente' ? 'clock' : 'times-circle') ?>"></i>
                                                    <?= htmlspecialchars($pag['estat']) ?>
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <?php if ($pag['facturat']): ?>
                                                    <span class="table-badge badge-success" title="<?= htmlspecialchars($pag['numero_factura'] ?? '') ?>">
                                                        <i class="fas fa-file-invoice"></i> Sí
                                                    </span>
                                                <?php else: ?>
                                                    <span class="table-badge badge-secondary">
                                                        <i class="fas fa-minus-circle"></i> No
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="action-buttons">
                                                    <button class="btn-action btn-view" 
                                                            onclick="veureDetalls(<?= $pag['id_pagament'] ?>)"
                                                            title="Ver detalles">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    
                                                    <?php if ($pag['facturat']): ?>
                                                        <a href="generar_factura.php?id=<?= $pag['id_pagament'] ?>" 
                                                           class="btn-action btn-download" 
                                                           title="Descargar factura PDF"
                                                           target="_blank">
                                                            <i class="fas fa-file-pdf"></i>
                                                        </a>
                                                    <?php endif; ?>
                                                    
                                                    <?php if ($pag['estat'] !== 'Anulado'): ?>
                                                        <button class="btn-action btn-edit" 
                                                                onclick="editarPagament(<?= $pag['id_pagament'] ?>)"
                                                                title="Editar">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                        
                                                        <?php if (!$pag['facturat'] && $pag['estat'] === 'Completado'): ?>
                                                            <button class="btn-action btn-invoice" 
                                                                    onclick="marcarFacturat(<?= $pag['id_pagament'] ?>)"
                                                                    title="Marcar como facturado">
                                                                <i class="fas fa-file-invoice"></i>
                                                            </button>
                                                        <?php endif; ?>
                                                        
                                                        <?php if ($pag['estat'] !== 'Anulado'): ?>
                                                            <button class="btn-action btn-cancel" 
                                                                    onclick="anularPagament(<?= $pag['id_pagament'] ?>)"
                                                                    title="Anular pago">
                                                                <i class="fas fa-ban"></i>
                                                            </button>
                                                        <?php endif; ?>
                                                    <?php endif; ?>
                                                    
                                                    <button class="btn-action btn-delete" 
                                                            onclick="confirmarEliminar(<?= $pag['id_pagament'] ?>)"
                                                            title="Eliminar">
                                                        <i class="fas fa-trash"></i>
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
            </div>
            
            <!-- Sesiones sin pagar -->
            <?php if (!empty($sessionsSensePagar)): ?>
            <div class="card">
                <div class="card-header">
                    <h2><i class="fas fa-exclamation-triangle"></i> Sesiones Sin Pagar</h2>
                    <span class="badge badge-warning"><?= count($sessionsSensePagar) ?></span>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Fecha Sesión</th>
                                    <th>Paciente</th>
                                    <th>Tipo</th>
                                    <th>Precio</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($sessionsSensePagar as $sess): ?>
                                    <tr>
                                        <td><?= date('d/m/Y', strtotime($sess['data_sessio'])) ?></td>
                                        <td><?= htmlspecialchars($sess['nom_pacient'] . ' ' . $sess['cognoms_pacient']) ?></td>
                                        <td><span class="badge badge-info"><?= htmlspecialchars($sess['tipus_sessio']) ?></span></td>
                                        <td><strong><?= number_format($sess['preu_sessio'], 2) ?> €</strong></td>
                                        <td>
                                            <button class="btn btn-sm btn-success" onclick="registrarPagamentRapid(<?= $sess['id_sessio'] ?>, <?= $sess['preu_sessio'] ?>)">
                                                <i class="fas fa-plus"></i> Registrar Pago
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Modal Formulario Pago -->
    <div id="modalPagament" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="modalTitle"><i class="fas fa-money-bill-wave"></i> Nuevo Pago</h3>
                <button class="modal-close" onclick="tancarModal()">&times;</button>
            </div>
            <form id="formPagament" method="POST" action="facturacion.php">
                <input type="hidden" name="accio" id="accioForm" value="crear">
                <input type="hidden" name="id_pagament" id="idPagament" value="">
                
                <div class="modal-body">
                    <div class="form-grid">
                        <!-- Selección de sesión -->
                        <div class="form-group full-width">
                            <label for="id_sessio">Sesión <span class="required">*</span></label>
                            <select id="id_sessio" name="id_sessio" required onchange="carregarPreuSessio()">
                                <option value="">Selecciona una sesión...</option>
                                        <?php
                                        // Mostrar només les sessions realitzades que encara no tenen pagament completat
                                        // Utilitzem l'array $sessionsSensePagar obtingut al principi de la pàgina
                                        if (!empty($sessionsSensePagar)):
                                            foreach ($sessionsSensePagar as $sess):
                                        ?>
                                            <option value="<?= $sess['id_sessio'] ?>" 
                                                    data-preu="<?= $sess['preu_sessio'] ?>"
                                                    data-pacient="<?= htmlspecialchars($sess['nom_pacient'] . ' ' . $sess['cognoms_pacient']) ?>"
                                                    data-date="<?= $sess['data_sessio'] ?>"
                                                    data-tipus="<?= htmlspecialchars($sess['tipus_sessio']) ?>">
                                                <?= date('d/m/Y', strtotime($sess['data_sessio'])) ?> - 
                                                <?= htmlspecialchars($sess['nom_pacient'] . ' ' . $sess['cognoms_pacient']) ?> - 
                                                <?= htmlspecialchars($sess['tipus_sessio']) ?> -
                                                <?= number_format($sess['preu_sessio'], 2) ?> €
                                            </option>
                                        <?php
                                            endforeach;
                                        else:
                                        ?>
                                            <option value="">No hay sesiones disponibles para seleccionar</option>
                                        <?php endif; ?>
                            </select>
                            <div id="resumSessio" class="session-summary" style="display:none; margin-top:10px; padding:10px; background:#f8f9fa; border:1px solid #e9ecef; border-radius:4px;">
                                <div><strong>Sessió:</strong> <span id="resumSessioData">-</span></div>
                                <div><strong>Pacient:</strong> <span id="resumSessioPacient">-</span></div>
                                <div><strong>Tipus:</strong> <span id="resumSessioTipus">-</span></div>
                                <div><strong>Preu:</strong> <span id="resumSessioPreu">-</span> €</div>
                            </div>
                        </div>
                        
                        <!-- Fecha de pago -->
                        <div class="form-group">
                            <label for="data_pagament">Fecha de Pago <span class="required">*</span></label>
                            <input type="date" id="data_pagament" name="data_pagament" 
                                   value="<?= date('Y-m-d') ?>" required>
                        </div>
                        
                        <!-- Importe -->
                        <div class="form-group">
                            <label for="import">Importe (€) <span class="required">*</span></label>
                            <input type="number" id="import" name="import" 
                                   step="0.01" min="0" required>
                        </div>
                        
                        <!-- Método de pago -->
                        <div class="form-group">
                            <label for="metode_pagament">Método de Pago <span class="required">*</span></label>
                            <select id="metode_pagament" name="metode_pagament" required>
                                <option value="Efectivo">Efectivo</option>
                                <option value="Tarjeta">Tarjeta</option>
                                <option value="Transferencia">Transferencia</option>
                                <option value="Bizum">Bizum</option>
                            </select>
                        </div>
                        
                        <!-- Estado -->
                        <div class="form-group">
                            <label for="estat_pagament">Estado <span class="required">*</span></label>
                            <select id="estat_pagament" name="estat" required>
                                <option value="Completado">Completado</option>
                                <option value="Pendiente">Pendiente</option>
                                <option value="Anulado">Anulado</option>
                            </select>
                        </div>
                        
                        <!-- Facturado -->
                        <div class="form-group checkbox-group">
                            <label>
                                <input type="checkbox" id="facturat" name="facturat" 
                                       onchange="toggleNumeroFactura()">
                                <span>Marcar como facturado</span>
                            </label>
                        </div>
                        
                        <!-- Número de factura -->
                        <div class="form-group" id="grupoNumeroFactura" style="display: none;">
                            <label for="numero_factura">Número de Factura</label>
                            <input type="text" id="numero_factura" name="numero_factura" 
                                   placeholder="Ej: 2025-001">
                        </div>
                        
                        <!-- Observaciones -->
                        <div class="form-group full-width">
                            <label for="observacions">Observaciones</label>
                            <textarea id="observacions" name="observacions" rows="3" 
                                      placeholder="Notas adicionales..."></textarea>
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
    
    <!-- Modal Detalles -->
    <div id="modalDetalls" class="modal">
        <div class="modal-content modal-large">
            <div class="modal-header">
                <h3><i class="fas fa-info-circle"></i> Detalles del Pago</h3>
                <button class="modal-close" onclick="tancarModalDetalls()">&times;</button>
            </div>
            <div class="modal-body" id="detallsContent">
                <!-- Contenido cargado dinámicamente -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="tancarModalDetalls()">
                    <i class="fas fa-times"></i> Cerrar
                </button>
            </div>
        </div>
    </div>
    
    <!-- Modal Marcar Facturado -->
    <div id="modalFacturat" class="modal">
        <div class="modal-content modal-small">
            <div class="modal-header">
                <h3><i class="fas fa-file-invoice"></i> Marcar como Facturado</h3>
                <button class="modal-close" onclick="tancarModalFacturat()">&times;</button>
            </div>
            <form id="formFacturat" method="POST" action="facturacion.php">
                <input type="hidden" name="accio" value="marcar_facturat">
                <input type="hidden" name="id_pagament" id="idPagamentFacturat" value="">
                
                <div class="modal-body">
                    <div class="form-group">
                        <label for="numero_factura_modal">Número de Factura <span class="required">*</span></label>
                        <input type="text" id="numero_factura_modal" name="numero_factura" 
                               placeholder="Ej: 2025-001" required>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="tancarModalFacturat()">
                        <i class="fas fa-times"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-check"></i> Confirmar
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Modal Anular Pago -->
    <div id="modalAnular" class="modal">
        <div class="modal-content modal-small">
            <div class="modal-header">
                <h3><i class="fas fa-ban"></i> Anular Pago</h3>
                <button class="modal-close" onclick="tancarModalAnular()">&times;</button>
            </div>
            <form id="formAnular" method="POST" action="facturacion.php">
                <input type="hidden" name="accio" value="anular">
                <input type="hidden" name="id_pagament" id="idPagamentAnular" value="">
                
                <div class="modal-body">
                    <p class="warning-message">
                        <i class="fas fa-exclamation-triangle"></i>
                        ¿Estás seguro de que quieres anular este pago?
                    </p>
                    <div class="form-group">
                        <label for="motiu">Motivo de anulación</label>
                        <textarea id="motiu" name="motiu" rows="3" 
                                  placeholder="Indica el motivo de la anulación..."></textarea>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="tancarModalAnular()">
                        <i class="fas fa-times"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-ban"></i> Anular Pago
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <script src="js/facturacion.js"></script>
    <script>
        // Toggle del menú lateral
        const menuToggle = document.getElementById('menuToggle');
        const sidebar = document.getElementById('sidebar');
        const sidebarToggle = document.getElementById('sidebarToggle');
        
        if (menuToggle) {
            menuToggle.addEventListener('click', () => {
                sidebar.classList.add('active');
            });
        }
        
        if (sidebarToggle) {
            sidebarToggle.addEventListener('click', () => {
                sidebar.classList.remove('active');
            });
        }
    </script>
</body>
</html>
