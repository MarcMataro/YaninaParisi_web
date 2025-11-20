<?php
session_start();

// Prevent caching
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

// Authentication
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: index.php');
    exit;
}

require_once 'includes/role_check.php';

require_once __DIR__ . '/../classes/connexio.php';
require_once __DIR__ . '/../classes/tarifes.php';

try {
    $db = Connexio::getInstance()->getConnexio();
} catch (Exception $e) {
    die('Error de connexió: ' . $e->getMessage());
}

$model = new Tarifes($db);

// CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
}
$csrf_token = $_SESSION['csrf_token'];

$flash = null;

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $posted_csrf = $_POST['csrf_token'] ?? '';
    if (empty($posted_csrf) || !hash_equals($_SESSION['csrf_token'], $posted_csrf)) {
        $flash = ['type' => 'danger', 'text' => 'Token de seguretat invàlid (CSRF).'];
    } else {
        // Create
        if (isset($_POST['create'])) {
            $m = new Tarifes($db);
            // assign fields (basic sanitization)
            $m->nom_servei_ca = $_POST['nom_servei_ca'] ?? '';
            $m->nom_servei_es = $_POST['nom_servei_es'] ?? '';
            $m->tipus_servei = $_POST['tipus_servei'] ?? '';
            $m->descripcio_ca = $_POST['descripcio_ca'] ?? '';
            $m->descripcio_es = $_POST['descripcio_es'] ?? '';
            $m->durada_minuts = intval($_POST['durada_minuts'] ?? 0);
            $m->preu_base = str_replace(',', '.', trim($_POST['preu_base'] ?? '0'));
            $m->preu_promocio = $_POST['preu_promocio'] !== '' ? str_replace(',', '.', trim($_POST['preu_promocio'])) : null;
            $m->iva_percentatge = $_POST['iva_percentatge'] ?? '21.00';
            $m->moneda = $_POST['moneda'] ?? 'EUR';
            $m->disponible = isset($_POST['disponible']) ? 1 : 0;
            $m->visible_web = isset($_POST['visible_web']) ? 1 : 0;
            $m->destacat = isset($_POST['destacat']) ? 1 : 0;
            $m->modalitat = $_POST['modalitat'] ?? 'presencial';
            $m->sessions_pack = intval($_POST['sessions_pack'] ?? 1);
            $m->validesa_dies = $_POST['validesa_dies'] !== '' ? intval($_POST['validesa_dies']) : null;
            $m->requisits = $_POST['requisits'] ?? '';
            $m->beneficios_ca = $_POST['beneficios_ca'] ?? '';
            $m->beneficios_es = $_POST['beneficios_es'] ?? '';
            $m->ordre_visualitzacio = intval($_POST['ordre_visualitzacio'] ?? 0);
            $m->color_etiqueta = $_POST['color_etiqueta'] ?? '#3B82F6';
            $m->data_inici_promocio = $_POST['data_inici_promocio'] ?? null;
            $m->data_fi_promocio = $_POST['data_fi_promocio'] ?? null;

            $errors = $m->validate();
            if (!empty($errors)) {
                $flash = ['type' => 'danger', 'text' => 'Errors: ' . implode(', ', $errors)];
            } else {
                if ($m->crear()) {
                    header('Location: gtarifas.php?created=1');
                    exit;
                } else {
                    $flash = ['type' => 'danger', 'text' => 'No s\'ha pogut crear la tarifa.'];
                }
            }
        }

        // Edit
        if (isset($_POST['edit'])) {
            $id = intval($_POST['id_tarifa'] ?? 0);
            if ($id && $model->llegirPerId($id)) {
                $model->nom_servei_ca = $_POST['nom_servei_ca'] ?? $model->nom_servei_ca;
                $model->nom_servei_es = $_POST['nom_servei_es'] ?? $model->nom_servei_es;
                $model->tipus_servei = $_POST['tipus_servei'] ?? $model->tipus_servei;
                $model->descripcio_ca = $_POST['descripcio_ca'] ?? $model->descripcio_ca;
                $model->descripcio_es = $_POST['descripcio_es'] ?? $model->descripcio_es;
                $model->durada_minuts = intval($_POST['durada_minuts'] ?? $model->durada_minuts);
                $model->preu_base = str_replace(',', '.', trim($_POST['preu_base'] ?? $model->preu_base));
                $model->preu_promocio = $_POST['preu_promocio'] !== '' ? str_replace(',', '.', trim($_POST['preu_promocio'])) : null;
                $model->iva_percentatge = $_POST['iva_percentatge'] ?? $model->iva_percentatge;
                $model->moneda = $_POST['moneda'] ?? $model->moneda;
                $model->disponible = isset($_POST['disponible']) ? 1 : 0;
                $model->visible_web = isset($_POST['visible_web']) ? 1 : 0;
                $model->destacat = isset($_POST['destacat']) ? 1 : 0;
                $model->modalitat = $_POST['modalitat'] ?? $model->modalitat;
                $model->sessions_pack = intval($_POST['sessions_pack'] ?? $model->sessions_pack);
                $model->validesa_dies = $_POST['validesa_dies'] !== '' ? intval($_POST['validesa_dies']) : $model->validesa_dies;
                $model->requisits = $_POST['requisits'] ?? $model->requisits;
                $model->beneficios_ca = $_POST['beneficios_ca'] ?? $model->beneficios_ca;
                $model->beneficios_es = $_POST['beneficios_es'] ?? $model->beneficios_es;
                $model->ordre_visualitzacio = intval($_POST['ordre_visualitzacio'] ?? $model->ordre_visualitzacio);
                $model->color_etiqueta = $_POST['color_etiqueta'] ?? $model->color_etiqueta;
                $model->data_inici_promocio = $_POST['data_inici_promocio'] ?? $model->data_inici_promocio;
                $model->data_fi_promocio = $_POST['data_fi_promocio'] ?? $model->data_fi_promocio;

                $errors = $model->validate();
                if (!empty($errors)) {
                    $flash = ['type' => 'danger', 'text' => 'Errors: ' . implode(', ', $errors)];
                } else {
                    if ($model->actualitzar()) {
                        header('Location: gtarifas.php?edited=1');
                        exit;
                    } else {
                        $flash = ['type' => 'danger', 'text' => 'No s\'ha pogut actualitzar la tarifa.'];
                    }
                }
            } else {
                $flash = ['type' => 'danger', 'text' => 'Tarifa no trobada.'];
            }
        }

        // Delete
        if (isset($_POST['delete'])) {
            $id = intval($_POST['delete_id'] ?? 0);
            if ($id && $model->eliminar($id)) {
                header('Location: gtarifas.php?deleted=1');
                exit;
            }
            $flash = ['type' => 'danger', 'text' => 'No s\'ha pogut eliminar la tarifa.'];
        }
    }
}

// Read list
$filters = [];
if (isset($_GET['visible_web'])) $filters['visible_web'] = (int)$_GET['visible_web'];
$tarifes = $model->listar($filters, 500, 0);

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Gestión de Tarifas</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@7.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Libre+Baskerville:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="css/configuracion.css">
    <style>
        .tarifas-table td, .tarifas-table th { padding:10px; }
        .actions-cell { white-space:nowrap; }
        .flash { padding:10px;border-radius:6px;margin-bottom:12px; }
        .flash-success { background:#e6ffed;color:#0f5132;border:1px solid #b7f3c9; }
        .flash-danger { background:#ffe6e6;color:#6b0f0f;border:1px solid #f3b7b7; }
        /* Modal styles: ensure modal fits viewport and content scrolls when tall */
        .modal {
            position: fixed;
            inset: 0;
            display: none; /* toggled inline via JS */
            align-items: center;
            justify-content: center;
            background: rgba(0,0,0,0.45);
            z-index: 1000;
            padding: 20px; /* allow some spacing on small viewports */
            overflow: auto; /* enable scrolling when modal content is larger than viewport */
        }
        .modal-content {
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.15);
            width: 100%;
            max-width: 900px;
            max-height: calc(100vh - 80px); /* leave room for padding */
            overflow: auto; /* scroll internal content */
            position: relative;
            padding: 18px;
        }
        .modal .close {
            position: absolute;
            right: 12px;
            top: 8px;
            font-size: 22px;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <link rel="icon" type="image/png" sizes="32x32" href="../img/Logo32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="../img/Logo16.png">
    <?php include 'includes/sidebar.php'; ?>

    <div class="main-content">
        <header class="top-bar">
            <div class="top-bar-left">
                <h1>Gestión de Tarifas</h1>
            </div>
        </header>

        <div class="content-wrapper" style="margin-top:20px;">
            <section class="card">
                <div class="card-header">
                    <h2><i class="fas fa-euro-sign"></i> Tarifas</h2>
                </div>
                <div class="config-section">
                    <?php if ($flash): ?>
                        <div class="flash <?php echo $flash['type'] === 'danger' ? 'flash-danger' : 'flash-success'; ?>"><?php echo htmlspecialchars($flash['text']); ?></div>
                    <?php endif; ?>
                    <?php if (isset($_GET['created'])): ?><div class="flash flash-success">Tarifa creada.</div><?php endif; ?>
                    <?php if (isset($_GET['edited'])): ?><div class="flash flash-success">Tarifa actualizada.</div><?php endif; ?>
                    <?php if (isset($_GET['deleted'])): ?><div class="flash flash-success">Tarifa eliminada.</div><?php endif; ?>

                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
                        <div></div>
                        <button class="btn btn-primary" onclick="openAdd()"><i class="fas fa-plus"></i> Nueva tarifa</button>
                    </div>

                    <table class="table tarifas-table" style="width:100%;border-collapse:collapse;">
                        <thead>
                            <tr style="text-align:left;border-bottom:1px solid #e6e6e6;"><th>Nombre (ca)</th><th>Tipo</th><th>Duración</th><th>Precio</th><th>Visible</th><th>Acciones</th></tr>
                        </thead>
                        <tbody>
                            <?php if ($tarifes): foreach ($tarifes as $t): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($t['nom_servei_ca']); ?></td>
                                    <td><?php echo htmlspecialchars($t['tipus_servei']); ?></td>
                                    <td><?php echo (int)$t['durada_minuts']; ?> min</td>
                                    <td><?php echo htmlspecialchars($t['preu_base']); ?> <?php echo htmlspecialchars($t['moneda']); ?></td>
                                    <td><input type="checkbox" disabled <?php echo !empty($t['visible_web']) ? 'checked' : ''; ?>></td>
                                    <td class="actions-cell">
                                        <button class="btn btn-secondary" onclick="openEdit(<?php echo (int)$t['id_tarifa']; ?>)">Editar</button>
                                        <button class="btn btn-danger" onclick="openDelete(<?php echo (int)$t['id_tarifa']; ?>)">Eliminar</button>
                                    </td>
                                </tr>
                            <?php endforeach; else: ?>
                                <tr><td colspan="6">No hay tarifas definidas.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </div>

    <!-- Add modal -->
    <div id="modalAdd" class="modal" style="display:none;">
        <div class="modal-content" style="max-width:800px;">
            <span class="close" onclick="closeModal('modalAdd')">&times;</span>
            <form method="POST" action="gtarifas.php">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                <input type="hidden" name="create" value="1">
                <h3>Nova tarifa</h3>
                <div class="form-grid">
                    <div class="form-group full-width">
                        <label for="nom_servei_ca">Nom (CA)</label>
                        <input id="nom_servei_ca" name="nom_servei_ca" required>
                    </div>
                    <div class="form-group full-width">
                        <label for="nom_servei_es">Nom (ES)</label>
                        <input id="nom_servei_es" name="nom_servei_es" required>
                    </div>
                    <div class="form-group">
                        <label for="tipus_servei">Tipus</label>
                        <select id="tipus_servei" name="tipus_servei">
                            <?php foreach (Tarifes::$TIPUS_SERVEI as $ts): ?>
                                <option value="<?php echo htmlspecialchars($ts); ?>"><?php echo htmlspecialchars($ts); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="durada_minuts">Durada (min)</label>
                        <input type="number" id="durada_minuts" name="durada_minuts" value="60">
                    </div>
                    <div class="form-group">
                        <label for="preu_base">Preu base</label>
                        <input id="preu_base" name="preu_base" required>
                    </div>
                    <div class="form-group">
                        <label for="preu_promocio">Preu promoció</label>
                        <input id="preu_promocio" name="preu_promocio">
                    </div>
                    <div class="form-group full-width">
                        <label for="descripcio_ca">Descripció (CA)</label>
                        <textarea id="descripcio_ca" name="descripcio_ca" rows="4"></textarea>
                    </div>
                    <div class="form-group full-width">
                        <label for="descripcio_es">Descripció (ES)</label>
                        <textarea id="descripcio_es" name="descripcio_es" rows="4"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="iva_percentatge">IVA (%)</label>
                        <input id="iva_percentatge" name="iva_percentatge" value="21.00">
                    </div>
                    <div class="form-group">
                        <label for="moneda">Moneda</label>
                        <input id="moneda" name="moneda" value="EUR">
                    </div>
                    <div class="form-group">
                        <label for="disponible">Disponible</label>
                        <input type="checkbox" id="disponible" name="disponible" value="1" checked>
                    </div>
                    <div class="form-group">
                        <label for="visible_web">Visible web</label>
                        <input type="checkbox" id="visible_web" name="visible_web" value="1" checked>
                    </div>
                    <div class="form-group">
                        <label for="destacat">Destacat</label>
                        <input type="checkbox" id="destacat" name="destacat" value="1">
                    </div>
                    <div class="form-group">
                        <label for="modalitat">Modalitat</label>
                        <select id="modalitat" name="modalitat">
                            <?php foreach (Tarifes::$MODALITATS as $m): ?>
                                <option value="<?php echo htmlspecialchars($m); ?>"><?php echo htmlspecialchars($m); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="sessions_pack">Sessions (pack)</label>
                        <input type="number" id="sessions_pack" name="sessions_pack" value="1" min="1">
                    </div>
                    <div class="form-group">
                        <label for="validesa_dies">Validesa (dies)</label>
                        <input type="number" id="validesa_dies" name="validesa_dies" value="" min="0">
                    </div>
                    <div class="form-group full-width">
                        <label for="requisits">Requisits</label>
                        <textarea id="requisits" name="requisits" rows="3"></textarea>
                    </div>
                    <div class="form-group full-width">
                        <label for="beneficios_ca">Beneficis (CA)</label>
                        <textarea id="beneficios_ca" name="beneficios_ca" rows="2"></textarea>
                    </div>
                    <div class="form-group full-width">
                        <label for="beneficios_es">Beneficis (ES)</label>
                        <textarea id="beneficios_es" name="beneficios_es" rows="2"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="ordre_visualitzacio">Ordre visualització</label>
                        <input type="number" id="ordre_visualitzacio" name="ordre_visualitzacio" value="0">
                    </div>
                    <div class="form-group">
                        <label for="color_etiqueta">Color etiqueta</label>
                        <input type="color" id="color_etiqueta" name="color_etiqueta" value="#3B82F6">
                    </div>
                    <div class="form-group">
                        <label for="data_inici_promocio">Data inici promoció</label>
                        <input type="date" id="data_inici_promocio" name="data_inici_promocio" value="">
                    </div>
                    <div class="form-group">
                        <label for="data_fi_promocio">Data fi promoció</label>
                        <input type="date" id="data_fi_promocio" name="data_fi_promocio" value="">
                    </div>
                </div>
                <div style="margin-top:12px; display:flex; justify-content:flex-end; gap:10px;">
                    <button type="submit" class="btn btn-save">Crear</button>
                    <button type="button" class="btn btn-secondary" onclick="closeModal('modalAdd')">Cancelar</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit modal -->
    <div id="modalEdit" class="modal" style="display:none;">
        <div class="modal-content" style="max-width:800px;">
            <span class="close" onclick="closeModal('modalEdit')">&times;</span>
            <form method="POST" action="gtarifas.php">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                <input type="hidden" name="edit" value="1">
                <input type="hidden" id="edit_id" name="id_tarifa" value="">
                <h3>Editar tarifa</h3>
                <div class="form-grid">
                    <div class="form-group full-width">
                        <label for="edit_nom_servei_ca">Nom (CA)</label>
                        <input id="edit_nom_servei_ca" name="nom_servei_ca" required>
                    </div>
                    <div class="form-group full-width">
                        <label for="edit_nom_servei_es">Nom (ES)</label>
                        <input id="edit_nom_servei_es" name="nom_servei_es" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_tipus_servei">Tipus</label>
                        <select id="edit_tipus_servei" name="tipus_servei">
                            <?php foreach (Tarifes::$TIPUS_SERVEI as $ts): ?>
                                <option value="<?php echo htmlspecialchars($ts); ?>"><?php echo htmlspecialchars($ts); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="edit_durada_minuts">Durada (min)</label>
                        <input type="number" id="edit_durada_minuts" name="durada_minuts">
                    </div>
                    <div class="form-group">
                        <label for="edit_preu_base">Preu base</label>
                        <input id="edit_preu_base" name="preu_base" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_preu_promocio">Preu promoció</label>
                        <input id="edit_preu_promocio" name="preu_promocio">
                    </div>
                    <div class="form-group">
                        <label for="edit_iva_percentatge">IVA (%)</label>
                        <input id="edit_iva_percentatge" name="iva_percentatge" value="21.00">
                    </div>
                    <div class="form-group">
                        <label for="edit_moneda">Moneda</label>
                        <input id="edit_moneda" name="moneda" value="EUR">
                    </div>
                    <div class="form-group full-width">
                        <label for="edit_descripcio_ca">Descripció (CA)</label>
                        <textarea id="edit_descripcio_ca" name="descripcio_ca" rows="4"></textarea>
                    </div>
                    <div class="form-group full-width">
                        <label for="edit_descripcio_es">Descripció (ES)</label>
                        <textarea id="edit_descripcio_es" name="descripcio_es" rows="4"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="edit_disponible">Disponible</label>
                        <input type="checkbox" id="edit_disponible" name="disponible" value="1">
                    </div>
                    <div class="form-group">
                        <label for="edit_visible_web">Visible web</label>
                        <input type="checkbox" id="edit_visible_web" name="visible_web" value="1">
                    </div>
                    <div class="form-group">
                        <label for="edit_destacat">Destacat</label>
                        <input type="checkbox" id="edit_destacat" name="destacat" value="1">
                    </div>
                    <div class="form-group">
                        <label for="edit_modalitat">Modalitat</label>
                        <select id="edit_modalitat" name="modalitat">
                            <?php foreach (Tarifes::$MODALITATS as $m): ?>
                                <option value="<?php echo htmlspecialchars($m); ?>"><?php echo htmlspecialchars($m); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="edit_sessions_pack">Sessions (pack)</label>
                        <input type="number" id="edit_sessions_pack" name="sessions_pack" min="1">
                    </div>
                    <div class="form-group">
                        <label for="edit_validesa_dies">Validesa (dies)</label>
                        <input type="number" id="edit_validesa_dies" name="validesa_dies" min="0">
                    </div>
                    <div class="form-group full-width">
                        <label for="edit_requisits">Requisits</label>
                        <textarea id="edit_requisits" name="requisits" rows="3"></textarea>
                    </div>
                    <div class="form-group full-width">
                        <label for="edit_beneficios_ca">Beneficis (CA)</label>
                        <textarea id="edit_beneficios_ca" name="beneficios_ca" rows="2"></textarea>
                    </div>
                    <div class="form-group full-width">
                        <label for="edit_beneficios_es">Beneficis (ES)</label>
                        <textarea id="edit_beneficios_es" name="beneficios_es" rows="2"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="edit_ordre_visualitzacio">Ordre visualització</label>
                        <input type="number" id="edit_ordre_visualitzacio" name="ordre_visualitzacio">
                    </div>
                    <div class="form-group">
                        <label for="edit_color_etiqueta">Color etiqueta</label>
                        <input type="color" id="edit_color_etiqueta" name="color_etiqueta" value="#3B82F6">
                    </div>
                    <div class="form-group">
                        <label for="edit_data_inici_promocio">Data inici promoció</label>
                        <input type="date" id="edit_data_inici_promocio" name="data_inici_promocio">
                    </div>
                    <div class="form-group">
                        <label for="edit_data_fi_promocio">Data fi promoció</label>
                        <input type="date" id="edit_data_fi_promocio" name="data_fi_promocio">
                    </div>
                </div>
                <div style="margin-top:12px; display:flex; justify-content:flex-end; gap:10px;">
                    <button type="submit" class="btn btn-save">Guardar</button>
                    <button type="button" class="btn btn-secondary" onclick="closeModal('modalEdit')">Cancelar</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete modal -->
    <div id="modalDelete" class="modal" style="display:none;">
        <div class="modal-content" style="max-width:480px;">
            <span class="close" onclick="closeModal('modalDelete')">&times;</span>
            <form method="POST" action="gtarifas.php">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                <input type="hidden" name="delete" value="1">
                <input type="hidden" id="delete_id" name="delete_id" value="">
                <h3>Eliminar tarifa</h3>
                <p>Estàs segur que vols eliminar aquesta tarifa?</p>
                <div style="margin-top:12px; display:flex; justify-content:flex-end; gap:10px;">
                    <button type="submit" class="btn btn-danger">Eliminar</button>
                    <button type="button" class="btn btn-secondary" onclick="closeModal('modalDelete')">Cancelar</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Prepare data map for modals
        const tarifasData = {};
        <?php foreach ($tarifes as $t): ?>
            tarifasData[<?php echo (int)$t['id_tarifa']; ?>] = <?php echo json_encode($t, JSON_UNESCAPED_UNICODE); ?>;
        <?php endforeach; ?>

        function openAdd(){
            const m = document.getElementById('modalAdd');
            m.style.display = 'flex';
            // prevent body scroll when modal open
            document.body.style.overflow = 'hidden';
        }
        function closeModal(id){
            const m = document.getElementById(id);
            if(m) m.style.display = 'none';
            document.body.style.overflow = '';
        }

        function openEdit(id){
            const t = tarifasData[id];
            if(!t) return alert('Tarifa no trobada');
            document.getElementById('edit_id').value = t.id_tarifa;
            document.getElementById('edit_nom_servei_ca').value = t.nom_servei_ca || '';
            document.getElementById('edit_nom_servei_es').value = t.nom_servei_es || '';
            document.getElementById('edit_tipus_servei').value = t.tipus_servei || '';
            document.getElementById('edit_durada_minuts').value = t.durada_minuts || '';
            document.getElementById('edit_preu_base').value = t.preu_base || '';
            document.getElementById('edit_preu_promocio').value = t.preu_promocio || '';
            document.getElementById('edit_iva_percentatge').value = t.iva_percentatge || '';
            document.getElementById('edit_moneda').value = t.moneda || '';
            document.getElementById('edit_descripcio_ca').value = t.descripcio_ca || '';
            document.getElementById('edit_descripcio_es').value = t.descripcio_es || '';
            document.getElementById('edit_disponible').checked = !!t.disponible;
            document.getElementById('edit_visible_web').checked = !!t.visible_web;
            document.getElementById('edit_destacat').checked = !!t.destacat;
            document.getElementById('edit_modalitat').value = t.modalitat || '';
            document.getElementById('edit_sessions_pack').value = t.sessions_pack || '';
            document.getElementById('edit_validesa_dies').value = t.validesa_dies || '';
            document.getElementById('edit_requisits').value = t.requisits || '';
            document.getElementById('edit_beneficios_ca').value = t.beneficios_ca || '';
            document.getElementById('edit_beneficios_es').value = t.beneficios_es || '';
            document.getElementById('edit_ordre_visualitzacio').value = t.ordre_visualitzacio || 0;
            if (document.getElementById('edit_color_etiqueta')) document.getElementById('edit_color_etiqueta').value = t.color_etiqueta || '#3B82F6';
            // promotion dates (ensure format YYYY-MM-DD or empty)
            if (document.getElementById('edit_data_inici_promocio')) document.getElementById('edit_data_inici_promocio').value = t.data_inici_promocio ? t.data_inici_promocio.split(' ')[0] : '';
            if (document.getElementById('edit_data_fi_promocio')) document.getElementById('edit_data_fi_promocio').value = t.data_fi_promocio ? t.data_fi_promocio.split(' ')[0] : '';
            document.getElementById('modalEdit').style.display='block';
        }

        function openDelete(id){ document.getElementById('delete_id').value = id; document.getElementById('modalDelete').style.display='block'; }

        // Close modal when clicking the overlay (outside .modal-content)
        document.querySelectorAll('.modal').forEach(function(modal){
            modal.addEventListener('click', function(e){
                if (e.target === modal) {
                    modal.style.display = 'none';
                    document.body.style.overflow = '';
                }
            });
            // Prevent clicks inside modal-content from closing it
            const content = modal.querySelector('.modal-content');
            if (content) content.addEventListener('click', function(e){ e.stopPropagation(); });
        });
    </script>

</body>
</html>
