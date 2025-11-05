<?php
/**
 * Gestió de Ressenyes - Panel de Control
 * Llista ressenyes (pendent/aprovat/rebutjat) i permet moderar-les.
 */
session_start();

// Autenticació
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: index.php');
    exit;
}

require_once __DIR__ . '/../classes/connexio.php';
require_once __DIR__ . '/../classes/ressenyes.php';

try {
    $connexio = Connexio::getInstance();
    $pdo = $connexio->getConnexio();
} catch (Exception $e) {
    die('Error de connexió: ' . $e->getMessage());
}

$rModel = new Ressenyes($pdo);

$missatge = '';
$tipusMissatge = '';

// Processar accions POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accio = $_POST['accio'] ?? '';
    $id = isset($_POST['id_ressenya']) ? (int)$_POST['id_ressenya'] : 0;

    switch ($accio) {
        case 'aprovar':
            if ($id && $rModel->setEstat($id, 'aprovat')) {
                $missatge = 'Ressenya aprovada.'; $tipusMissatge = 'success';
            } else { $missatge = 'Error aprovant la ressenya.'; $tipusMissatge = 'error'; }
            break;
        case 'rebutjar':
            if ($id && $rModel->setEstat($id, 'rebutjat')) {
                $missatge = 'Ressenya rebutjada.'; $tipusMissatge = 'success';
            } else { $missatge = 'Error rebutjant la ressenya.'; $tipusMissatge = 'error'; }
            break;
        case 'verificada':
            $val = isset($_POST['valor']) && $_POST['valor'] == '1' ? 1 : 0;
            if ($id && $rModel->setVerificada($id, (bool)$val)) {
                $missatge = 'Marca de verificació actualitzada.'; $tipusMissatge = 'success';
            } else { $missatge = 'Error actualitzant verificació.'; $tipusMissatge = 'error'; }
            break;
        case 'esborrar':
            if ($id && $rModel->delete($id)) {
                $missatge = 'Ressenya eliminada.'; $tipusMissatge = 'success';
            } else { $missatge = 'Error eliminant la ressenya.'; $tipusMissatge = 'error'; }
            break;
    }
}

// Filtres
$filtre = $_GET['filtre'] ?? 'pendent'; // pendent, aprovat, rebutjat, tots
$q = trim($_GET['q'] ?? '');

$opts = [];
if ($filtre !== 'tots') $opts['estat'] = $filtre;
if ($q !== '') $opts['q'] = $q;

$res = $rModel->list($opts);
$ressenyes = $res['data'] ?? [];

?>
<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Gestió de Ressenyes - Panel</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@7.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Libre+Baskerville:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="css/gpacients.css">
    <style>
        /* Restyling combobox and filter input for gressenyes admin */
        .card-actions form { display:flex; gap:10px; align-items:center; flex-wrap:wrap; }
        .card-actions .form-select,
        .card-actions .form-input {
            font-family: inherit;
            font-size: 14px;
            padding: 8px 10px;
            border: 1px solid #d1d5db; /* light gray */
            background: #fff;
            color: #111827;
            border-radius: 6px;
            outline: none;
            transition: box-shadow .12s ease, border-color .12s ease;
            box-shadow: none;
            height: 38px;
            -webkit-appearance: none;
            appearance: none;
        }
        .card-actions .form-select:focus,
        .card-actions .form-input:focus {
            border-color: #2563eb; /* blue-600 */
            box-shadow: 0 0 0 3px rgba(37,99,235,0.08);
        }
        /* make select show a subtle chevron on the right */
        .card-actions .form-select { padding-right: 36px; background-image: linear-gradient(45deg, transparent 50%, #6b7280 50%), linear-gradient(135deg, #6b7280 50%, transparent 50%); background-position: calc(100% - 18px) calc(1em + 2px), calc(100% - 13px) calc(1em + 2px); background-size: 6px 6px, 6px 6px; background-repeat: no-repeat; }
        /* responsive behaviour */
        @media (max-width:600px) {
            .card-actions .form-select,
            .card-actions .form-input,
            .card-actions .btn { width: 100%; }
            .card-actions form { gap:8px; }
        }
    </style>
</head>
<body>
    <link rel="icon" type="image/png" sizes="32x32" href="../img/Logo32.png">
    <?php include 'includes/sidebar.php'; ?>

    <div class="main-content">
        <header class="top-bar">
            <div class="top-bar-left">
                <button class="menu-toggle" id="menuToggle"><i class="fas fa-bars"></i></button>
                <div class="top-bar-info">
                    <h1>Ressenyes</h1>
                    <p class="date-today">Gestiona i modera les ressenyes rebudes</p>
                </div>
            </div>
            <div class="top-bar-right">
                <div class="user-profile"><img src="../img/Logo.png" class="profile-img"><span class="profile-name"><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Admin'); ?></span></div>
            </div>
        </header>

        <div class="content-wrapper">
            <?php if ($missatge): ?>
                <div class="alert alert-<?php echo $tipusMissatge ?: 'info'; ?>" id="alertMessage">
                    <i class="fas fa-<?php echo $tipusMissatge === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
                    <span><?php echo $missatge; ?></span>
                    <button class="alert-close" onclick="document.getElementById('alertMessage').remove()"><i class="fas fa-times"></i></button>
                </div>
            <?php endif; ?>

            <div class="card">
                <div class="card-header">
                    <h2><i class="fas fa-star"></i> Reseñas</h2>
                    <div class="card-actions">
                        <form method="GET" action="gressenyes.php" style="display:flex;gap:10px;align-items:center;">
                            <select name="filtre" class="form-select">
                                <option value="pendent" <?php echo $filtre==='pendent'?'selected':''; ?>>Pendientes</option>
                                <option value="aprovat" <?php echo $filtre==='aprovat'?'selected':''; ?>>Aprobadas</option>
                                <option value="rebutjat" <?php echo $filtre==='rebutjat'?'selected':''; ?>>Rechazadas</option>
                                <option value="tots" <?php echo $filtre==='tots'?'selected':''; ?>>Todas</option>
                            </select>
                            <input type="text" name="q" class="form-input" placeholder="Cerca text o pacient..." value="<?php echo htmlspecialchars($q); ?>">
                            <button class="btn" type="submit">Filtrar</button>
                        </form>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="patients-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Título</th>
                                <th>Puntuación</th>
                                <th>Paciente</th>
                                <th>Fecha</th>
                                <th>Estado</th>
                                <th>Verificada</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($ressenyes)): ?>
                                <tr><td colspan="8" class="text-center">No se han encontrado reseñas</td></tr>
                            <?php else: ?>
                                <?php foreach ($ressenyes as $r): ?>
                                    <tr>
                                        <td>#<?php echo $r['id_ressenya'] ?? '-'; ?></td>
                                        <td><?php echo htmlspecialchars($r['titol_ca'] ?? $r['titol_es'] ?? '-'); ?></td>
                                        <td><?php echo (int)($r['puntuacio'] ?? 0); ?> / 5</td>
                                        <td><?php echo htmlspecialchars($r['nom_pacient'] ?: ($r['pacient_id'] ? 'Pacient#' . $r['pacient_id'] : '-')); ?></td>
                                        <td><?php echo date('d/m/Y H:i', strtotime($r['data_creacio'] ?? $r['data_actualitzacio'] ?? 'now')); ?></td>
                                        <td><?php echo htmlspecialchars($r['estat'] ?? 'pendent'); ?></td>
                                        <td><?php echo !empty($r['verificada']) ? '<span class="badge badge-success">Sí</span>' : '<span class="badge">No</span>'; ?></td>
                                        <td>
                                            <div class="action-buttons">
                                                <button class="btn-action btn-view" onclick="veureRessenya(this)" data-title="<?php echo htmlspecialchars($r['titol_ca']); ?>" data-text="<?php echo htmlspecialchars($r['text_ressenya_ca']); ?>" data-nom="<?php echo htmlspecialchars($r['nom_pacient'] ?: 'Pacient#'.$r['pacient_id']); ?>">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <form method="POST" action="gressenyes.php" style="display:inline">
                                                    <input type="hidden" name="accio" value="aprovar">
                                                    <input type="hidden" name="id_ressenya" value="<?php echo $r['id_ressenya']; ?>">
                                                    <button type="submit" class="btn-action" title="Aprovar"><i class="fas fa-check"></i></button>
                                                </form>
                                                <form method="POST" action="gressenyes.php" style="display:inline">
                                                    <input type="hidden" name="accio" value="rebutjar">
                                                    <input type="hidden" name="id_ressenya" value="<?php echo $r['id_ressenya']; ?>">
                                                    <button type="submit" class="btn-action" title="Rebutjar"><i class="fas fa-times"></i></button>
                                                </form>
                                                <form method="POST" action="gressenyes.php" style="display:inline">
                                                    <input type="hidden" name="accio" value="verificada">
                                                    <input type="hidden" name="id_ressenya" value="<?php echo $r['id_ressenya']; ?>">
                                                    <input type="hidden" name="valor" value="<?php echo empty($r['verificada']) ? '1' : '0'; ?>">
                                                    <button type="submit" class="btn-action" title="Toggle Verificada"><i class="fas fa-user-check"></i></button>
                                                </form>
                                                <form method="POST" action="gressenyes.php" style="display:inline" onsubmit="return confirm('Segur que vols eliminar aquesta ressenya?');">
                                                    <input type="hidden" name="accio" value="esborrar">
                                                    <input type="hidden" name="id_ressenya" value="<?php echo $r['id_ressenya']; ?>">
                                                    <button type="submit" class="btn-action" title="Eliminar"><i class="fas fa-trash"></i></button>
                                                </form>
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
    </div>

    <!-- Modal per veure ressenya -->
    <div class="modal" id="modalRessenya">
        <div class="modal-content modal-large">
            <div class="modal-header">
                <h2 id="modalTitle">Reseña</h2>
                <button class="modal-close" onclick="tancarModalRessenya()">&times;</button>
            </div>
            <div class="modal-body">
                <p><strong>Autor:</strong> <span id="modalAuthor"></span></p>
                <hr>
                <div id="modalText" style="white-space:pre-wrap"></div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" onclick="tancarModalRessenya()">Cerrar</button>
            </div>
        </div>
    </div>

    <script>
        function veureRessenya(btn) {
            const title = btn.dataset.title || '';
            const text = btn.dataset.text || '';
            const nom = btn.dataset.nom || '';
            document.getElementById('modalTitle').textContent = title;
            document.getElementById('modalAuthor').textContent = nom;
            document.getElementById('modalText').textContent = text;
            document.getElementById('modalRessenya').classList.add('show');
            document.body.style.overflow = 'hidden';
        }
        function tancarModalRessenya() {
            document.getElementById('modalRessenya').classList.remove('show');
            document.body.style.overflow = 'auto';
        }
    </script>

</body>
</html>
