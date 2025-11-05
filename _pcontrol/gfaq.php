<?php
/**
 * Gestión de FAQs - Panel de Control
 *
 * Permite crear, editar, eliminar y listar FAQs según la estructura de la tabla `faqs`.
 * Estilísticamente sigue la plantilla del dashboard para mantener coherencia visual.
 *
 * @author
 * @version 1.0
 */

session_start();

// Verificar autenticación
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: index.php');
    exit;
}

require_once __DIR__ . '/../classes/connexio.php';
require_once __DIR__ . '/../classes/faqs.php';

try {
    $connexio = Connexio::getInstance();
    $pdo = $connexio->getConnexio();
} catch (Exception $e) {
    die('Error de conexión: ' . $e->getMessage());
}

$faqModel = new Faq($pdo);

$mensaje = '';
$tipoMensaje = '';

// Si hay mensaje via GET (después de PRG), usarlo
if (!empty($_GET['msg'])) {
    $mensaje = urldecode($_GET['msg']);
    $tipoMensaje = $_GET['type'] ?? 'info';
}

// Procesar acciones POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';

    switch ($accion) {
        case 'crear':
            $faqModel->pregunta_ca = $_POST['pregunta_ca'] ?? '';
            $faqModel->pregunta_es = $_POST['pregunta_es'] ?? '';
            $faqModel->resposta_ca = $_POST['resposta_ca'] ?? '';
            $faqModel->resposta_es = $_POST['resposta_es'] ?? '';
            $faqModel->categoria = $_POST['categoria'] ?? 'general';
            $faqModel->ordre = $_POST['ordre'] ?? 0;
            $faqModel->activa = isset($_POST['activa']) ? true : false;
            $faqModel->destacada = isset($_POST['destacada']) ? true : false;
            $faqModel->meta_title_ca = $_POST['meta_title_ca'] ?? null;
            $faqModel->meta_title_es = $_POST['meta_title_es'] ?? null;
            $faqModel->meta_description_ca = $_POST['meta_description_ca'] ?? null;
            $faqModel->meta_description_es = $_POST['meta_description_es'] ?? null;
            $faqModel->slug_ca = $_POST['slug_ca'] ?? null;
            $faqModel->slug_es = $_POST['slug_es'] ?? null;

            $id = $faqModel->crear();
            if ($id) {
                $mensaje = "FAQ creada correctamente (ID: {$id})";
                $tipoMensaje = 'success';
            } else {
                $mensaje = 'Error al crear la FAQ. Comprueba los campos obligatorios.';
                $tipoMensaje = 'error';
            }
            // PRG: evitar re-envío de formulario
            header('Location: gfaq.php?msg=' . urlencode($mensaje) . '&type=' . $tipoMensaje);
            exit;
            break;

        case 'actualizar':
            $faqModel->id_faq = $_POST['id_faq'] ?? 0;
            $faqModel->pregunta_ca = $_POST['pregunta_ca'] ?? '';
            $faqModel->pregunta_es = $_POST['pregunta_es'] ?? '';
            $faqModel->resposta_ca = $_POST['resposta_ca'] ?? '';
            $faqModel->resposta_es = $_POST['resposta_es'] ?? '';
            $faqModel->categoria = $_POST['categoria'] ?? 'general';
            $faqModel->ordre = $_POST['ordre'] ?? 0;
            $faqModel->activa = isset($_POST['activa']) ? true : false;
            $faqModel->destacada = isset($_POST['destacada']) ? true : false;
            $faqModel->meta_title_ca = $_POST['meta_title_ca'] ?? null;
            $faqModel->meta_title_es = $_POST['meta_title_es'] ?? null;
            $faqModel->meta_description_ca = $_POST['meta_description_ca'] ?? null;
            $faqModel->meta_description_es = $_POST['meta_description_es'] ?? null;
            $faqModel->slug_ca = $_POST['slug_ca'] ?? null;
            $faqModel->slug_es = $_POST['slug_es'] ?? null;

            if ($faqModel->actualitzar()) {
                $mensaje = 'FAQ actualizada correctamente.';
                $tipoMensaje = 'success';
            } else {
                $mensaje = 'Error al actualizar la FAQ.';
                $tipoMensaje = 'error';
            }
            header('Location: gfaq.php?msg=' . urlencode($mensaje) . '&type=' . $tipoMensaje);
            exit;
            break;

        case 'eliminar':
            $idEliminar = $_POST['id_faq'] ?? 0;
            if ($faqModel->eliminar($idEliminar)) {
                $mensaje = 'FAQ eliminada correctamente.';
                $tipoMensaje = 'success';
            } else {
                $mensaje = 'Error al eliminar la FAQ.';
                $tipoMensaje = 'error';
            }
            header('Location: gfaq.php?msg=' . urlencode($mensaje) . '&type=' . $tipoMensaje);
            exit;
            break;

        case 'toggle_activa':
            $id = $_POST['id_faq'] ?? 0;
            if ($faqModel->toggleActiva($id)) {
                $mensaje = 'Estado de visibilidad actualizado.';
                $tipoMensaje = 'success';
            } else {
                $mensaje = 'Error al actualizar el estado.';
                $tipoMensaje = 'error';
            }
            header('Location: gfaq.php?msg=' . urlencode($mensaje) . '&type=' . $tipoMensaje);
            exit;
            break;

        case 'toggle_destacada':
            $id = $_POST['id_faq'] ?? 0;
            if ($faqModel->toggleDestacada($id)) {
                $mensaje = 'Estado de destacada actualizado.';
                $tipoMensaje = 'success';
            } else {
                $mensaje = 'Error al actualizar destacada.';
                $tipoMensaje = 'error';
            }
            header('Location: gfaq.php?msg=' . urlencode($mensaje) . '&type=' . $tipoMensaje);
            exit;
            break;
    }

}

// GET: vistas y filtros
$vista = $_GET['vista'] ?? 'lista';
$idEditar = $_GET['id'] ?? null;
$filtroCategoria = $_GET['categoria'] ?? '';
$filtroActiva = isset($_GET['activa']) ? $_GET['activa'] : '';
$busqueda = $_GET['q'] ?? '';

// Obtener lista de FAQs según filtros
$opts = [];
if (!empty($filtroCategoria)) $opts['categoria'] = $filtroCategoria;
if ($filtroActiva !== '') $opts['activa'] = $filtroActiva === '1' ? true : false;
// Nota: búsqueda por texto simple en pregunta_es
if (!empty($busqueda)) {
    // búsqueda simple: utilizamos LIKE sobre pregunta_es
    $sql = "SELECT * FROM faqs WHERE pregunta_es LIKE :q OR resposta_es LIKE :q ORDER BY categoria, ordre, id_faq";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':q' => "%{$busqueda}%"]);
    $faqs = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    $faqs = $faqModel->llistar($opts);
}

// Si estamos editando, cargar datos
$faqEditar = null;
if ($vista === 'editar' && $idEditar) {
    $faqEditar = $faqModel->obtenirPerId($idEditar);
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Gestión de FAQs - Panel</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Libre+Baskerville:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="css/configuracion.css">
    <style>
        /* Pequeños ajustes específicos para la vista de FAQs */
        .faqs-table { width:100%; border-collapse:collapse; }
        .faqs-table th, .faqs-table td { padding:10px; border-bottom:1px solid #eee; text-align:left; }
        .faqs-actions button { margin-right:6px; }
        .filter-row { display:flex; gap:10px; align-items:center; margin-bottom:12px; }
        .form-grid { display:grid; grid-template-columns:1fr 1fr; gap:12px; }

        /* Estils específics per als inputs i selects del formulari de filtres */
        form[action="gfaq.php"] .form-control {
            padding: 8px 12px;
            border: 1px solid rgba(0,0,0,0.12);
            border-radius: 8px;
            background: #fff;
            font-size: 0.95rem;
            color: var(--color-dark);
            transition: box-shadow 0.15s ease, border-color 0.15s ease;
            height: 38px;
            line-height: 1.2;
        }

        form[action="gfaq.php"] select.form-control {
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
            padding-right: 36px; /* espacio para el chevron */
            background-image: linear-gradient(45deg, transparent 50%, #666 50%), linear-gradient(135deg, #666 50%, transparent 50%);
            background-position: calc(100% - 18px) calc(50% - 6px), calc(100% - 12px) calc(50% - 6px);
            background-size: 6px 6px, 6px 6px;
            background-repeat: no-repeat;
        }

        form[action="gfaq.php"] .form-control:focus {
            outline: none;
            border-color: rgba(0,0,0,0.2);
            box-shadow: 0 4px 12px rgba(0,0,0,0.06);
        }

        /* Ajustes responsivos para que el input de búsqueda ocupe más ancho en pantallas pequeñas */
        @media (max-width: 900px) {
            form[action="gfaq.php"] .form-control { height:40px; }
            form[action="gfaq.php"] input[name="q"] { width: 100% !important; }
        }
        @media (max-width:900px) { .form-grid { grid-template-columns:1fr; } }

        /* modal styles are provided by css/configuracion.css which we load above */

    /* Improvements specific to the "Nueva FAQ" modal: sizing and layout */
        #modalCrear .modal-content {
            width: min(920px, 96vw);
            max-width: 920px;
            padding: 20px 22px;
        }

        #modalCrear .modal-header { display:flex; align-items:center; justify-content:space-between; gap:12px; padding-bottom:6px; }
        #modalCrear .modal-header h2 { margin:0; font-size:1.05rem; }

        #modalCrear .form-grid { grid-template-columns: 1fr 1fr; gap:14px; align-items:start; }
        #modalCrear .form-grid textarea { grid-column: 1 / -1; min-height: 140px; }
        #modalCrear label { display:block; margin-bottom:6px; font-weight:600; font-size:0.95rem; color:var(--color-dark); }
        #modalCrear .form-control { width:100%; box-sizing:border-box; }

        #modalCrear .modal-footer { display:flex; justify-content:flex-end; gap:8px; padding-top:12px; border-top:1px solid rgba(0,0,0,0.06); margin-top:12px; }

        /* Responsive: single column on small screens */
        @media (max-width:900px) {
            #modalCrear .form-grid { grid-template-columns: 1fr; }
            #modalCrear .modal-content { padding: 14px; }
        }

        /* Edit form styling: make the edit card look polished and aligned */
        .card.edit-card { padding: 18px; box-shadow: 0 6px 20px rgba(0,0,0,0.06); }
        .card.edit-card .card-header { margin-bottom: 12px; }
        .card.edit-card .edit-form { display:block; }
        .card.edit-card .form-grid { grid-template-columns: 1fr 1fr; gap:14px; }
        .card.edit-card .form-grid textarea { grid-column: 1 / -1; min-height: 160px; padding:10px; }
        .card.edit-card label { display:block; margin-bottom:6px; font-weight:600; color:var(--color-dark); }
    .card.edit-card .form-control { padding:10px 12px; border-radius:8px; border:1px solid rgba(0,0,0,0.12); box-shadow:none; width:100%; }
        .card.edit-card .form-control:focus { box-shadow: 0 6px 18px rgba(0,0,0,0.06); border-color: rgba(0,0,0,0.18); }
        .card.edit-card .controls { display:flex; justify-content:flex-end; gap:8px; margin-top:12px; }
        .card.edit-card .controls .btn-small { padding:8px 14px; }
        @media (max-width:900px) { .card.edit-card .form-grid { grid-template-columns: 1fr; } .card.edit-card .form-grid textarea { min-height:120px; } }

    /* Ensure modal inputs and textareas also take full width and are a bit taller */
    #modalCrear .form-control { width:100%; }
    #modalCrear textarea.form-control { min-height:180px; resize:vertical; }
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
                    <h1>Gestión de FAQs</h1>
                    <p class="date-today">Crear y gestionar preguntas frecuentes del sitio</p>
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

            <?php if (!empty($mensaje)): ?>
            <div class="alert alert-<?php echo $tipoMensaje; ?>" id="alertMessage">
                <i class="fas fa-<?php echo $tipoMensaje === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
                <span><?php echo htmlspecialchars($mensaje); ?></span>
                <button class="alert-close" onclick="document.getElementById('alertMessage').remove()"><i class="fas fa-times"></i></button>
            </div>
            <?php endif; ?>

            <div class="card">
                <div class="card-header">
                    <h2><i class="fas fa-question-circle"></i> Lista de FAQs</h2>
                    <div>
                        <button id="btnNuevaFaq" class="btn-small" onclick="openModal('modalCrear')"><i class="fas fa-plus"></i> Nueva FAQ</button>
                    </div>
                </div>

                <div style="margin-bottom:12px;">
                    <form method="GET" action="gfaq.php" style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
                        <input type="text" name="q" placeholder="Buscar texto en pregunta/respuesta..." value="<?php echo htmlspecialchars($busqueda); ?>" class="form-control" style="width:320px;">
                        <select name="categoria" class="form-control">
                            <option value="">Todas las categorías</option>
                            <option value="general">General</option>
                            <option value="terapia">Terapia</option>
                            <option value="tarifes">Tarifes</option>
                            <option value="tecnica">Técnica</option>
                            <option value="primera_visita">Primera visita</option>
                            <option value="urgencies">Urgencies</option>
                        </select>
                        <select name="activa" class="form-control">
                            <option value="">Cualquiera</option>
                            <option value="1">Activas</option>
                            <option value="0">Inactivas</option>
                        </select>
                        <button class="btn-small" type="submit">Filtrar</button>
                    </form>
                </div>

                <div class="table-responsive">
                    <table class="faqs-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Pregunta (ES)</th>
                                <th>Categoría</th>
                                <th>Activa</th>
                                <th>Destacada</th>
                                <th>Orden</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($faqs)): ?>
                                <tr><td colspan="7">No se han encontrado FAQs.</td></tr>
                            <?php else: foreach ($faqs as $f): ?>
                                <tr>
                                    <td><?php echo $f['id_faq']; ?></td>
                                    <td><?php echo htmlspecialchars($f['pregunta_es']); ?></td>
                                    <td><?php echo $f['categoria']; ?></td>
                                    <td><?php echo $f['activa'] ? 'Sí' : 'No'; ?></td>
                                    <td><?php echo $f['destacada'] ? 'Sí' : 'No'; ?></td>
                                    <td><?php echo $f['ordre']; ?></td>
                                    <td class="faqs-actions">
                                        <a class="btn-small" href="gfaq.php?vista=editar&id=<?php echo $f['id_faq']; ?>">Editar</a>
                                        <form method="POST" action="gfaq.php" style="display:inline-block;" onsubmit="return confirm('¿Eliminar FAQ #<?php echo $f['id_faq']; ?>?');">
                                            <input type="hidden" name="accion" value="eliminar">
                                            <input type="hidden" name="id_faq" value="<?php echo $f['id_faq']; ?>">
                                            <button class="btn-small" type="submit">Eliminar</button>
                                        </form>
                                        <form method="POST" action="gfaq.php" style="display:inline-block;">
                                            <input type="hidden" name="accion" value="toggle_activa">
                                            <input type="hidden" name="id_faq" value="<?php echo $f['id_faq']; ?>">
                                            <button class="btn-small" type="submit"><?php echo $f['activa'] ? 'Ocultar' : 'Mostrar'; ?></button>
                                        </form>
                                        <form method="POST" action="gfaq.php" style="display:inline-block;">
                                            <input type="hidden" name="accion" value="toggle_destacada">
                                            <input type="hidden" name="id_faq" value="<?php echo $f['id_faq']; ?>">
                                            <button class="btn-small" type="submit"><?php echo $f['destacada'] ? 'Quitar destaque' : 'Destacar'; ?></button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <?php if ($vista === 'editar' && $faqEditar): ?>
            <div class="card edit-card">
                <div class="card-header"><h2><i class="fas fa-edit"></i> Editar FAQ #<?php echo $faqEditar['id_faq']; ?></h2></div>
                <form method="POST" action="gfaq.php" class="edit-form">
                    <input type="hidden" name="accion" value="actualizar">
                    <input type="hidden" name="id_faq" value="<?php echo $faqEditar['id_faq']; ?>">
                    <div class="form-grid">
                        <div>
                            <label>Pregunta (ES)</label>
                            <input type="text" name="pregunta_es" class="form-control" value="<?php echo htmlspecialchars($faqEditar['pregunta_es']); ?>" required>
                        </div>
                        <div>
                            <label>Pregunta (CA)</label>
                            <input type="text" name="pregunta_ca" class="form-control" value="<?php echo htmlspecialchars($faqEditar['pregunta_ca']); ?>" required>
                        </div>
                        <div>
                            <label>Respuesta (ES)</label>
                            <textarea name="resposta_es" class="form-control" rows="4" required><?php echo htmlspecialchars($faqEditar['resposta_es']); ?></textarea>
                        </div>
                        <div>
                            <label>Respuesta (CA)</label>
                            <textarea name="resposta_ca" class="form-control" rows="4" required><?php echo htmlspecialchars($faqEditar['resposta_ca']); ?></textarea>
                        </div>
                        <div>
                            <label>Categoria</label>
                            <select name="categoria" class="form-control">
                                <?php $cats = ['general','terapia','tarifes','tecnica','primera_visita','urgencies']; foreach($cats as $c): ?>
                                    <option value="<?php echo $c; ?>" <?php echo $faqEditar['categoria'] === $c ? 'selected' : ''; ?>><?php echo $c; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label>Orden</label>
                            <input type="number" name="ordre" class="form-control" value="<?php echo $faqEditar['ordre']; ?>">
                        </div>
                        <div>
                            <label>Meta title (ES)</label>
                            <input type="text" name="meta_title_es" class="form-control" value="<?php echo htmlspecialchars($faqEditar['meta_title_es']); ?>">
                        </div>
                        <div>
                            <label>Meta title (CA)</label>
                            <input type="text" name="meta_title_ca" class="form-control" value="<?php echo htmlspecialchars($faqEditar['meta_title_ca']); ?>">
                        </div>
                        <div>
                            <label>Meta description (ES)</label>
                            <input type="text" name="meta_description_es" class="form-control" value="<?php echo htmlspecialchars($faqEditar['meta_description_es']); ?>">
                        </div>
                        <div>
                            <label>Meta description (CA)</label>
                            <input type="text" name="meta_description_ca" class="form-control" value="<?php echo htmlspecialchars($faqEditar['meta_description_ca']); ?>">
                        </div>
                        <div>
                            <label>Slug (ES)</label>
                            <input type="text" name="slug_es" class="form-control" value="<?php echo htmlspecialchars($faqEditar['slug_es']); ?>">
                        </div>
                        <div>
                            <label>Slug (CA)</label>
                            <input type="text" name="slug_ca" class="form-control" value="<?php echo htmlspecialchars($faqEditar['slug_ca']); ?>">
                        </div>
                        <div style="display:flex;align-items:center;gap:10px;">
                            <label><input type="checkbox" name="activa" <?php echo $faqEditar['activa'] ? 'checked' : ''; ?>> Activa</label>
                            <label><input type="checkbox" name="destacada" <?php echo $faqEditar['destacada'] ? 'checked' : ''; ?>> Destacada</label>
                        </div>
                    </div>
                    <div style="margin-top:12px;">
                        <button class="btn-small" type="submit">Guardar cambios</button>
                        <a class="btn-small" href="gfaq.php">Cancelar</a>
                    </div>
                </form>
            </div>
            <?php endif; ?>

        </div>
    </div>

    <!-- Modal crear -->
    <div class="modal" id="modalCrear" style="display:none;">
        <div class="modal-content modal-large">
            <div class="modal-header">
                <h2><i class="fas fa-plus"></i> Nueva FAQ</h2>
                <button class="modal-close" aria-label="Cerrar" onclick="closeModal('modalCrear')">&times;</button>
            </div>
            <form method="POST" action="gfaq.php">
                <input type="hidden" name="accion" value="crear">
                <div class="modal-body">
                    <div class="form-grid">
                        <div>
                            <label>Pregunta (ES)</label>
                            <input type="text" name="pregunta_es" class="form-control" required>
                        </div>
                        <div>
                            <label>Pregunta (CA)</label>
                            <input type="text" name="pregunta_ca" class="form-control" required>
                        </div>
                        <div>
                            <label>Respuesta (ES)</label>
                            <textarea name="resposta_es" class="form-control" rows="4" required></textarea>
                        </div>
                        <div>
                            <label>Respuesta (CA)</label>
                            <textarea name="resposta_ca" class="form-control" rows="4" required></textarea>
                        </div>
                        <div>
                            <label>Categoria</label>
                            <select name="categoria" class="form-control">
                                <option value="general">general</option>
                                <option value="terapia">terapia</option>
                                <option value="tarifes">tarifes</option>
                                <option value="tecnica">tecnica</option>
                                <option value="primera_visita">primera_visita</option>
                                <option value="urgencies">urgencies</option>
                            </select>
                        </div>
                        <div>
                            <label>Orden</label>
                            <input type="number" name="ordre" class="form-control" value="0">
                        </div>
                        <!-- Slugs are generated automatically; inputs removed from modal -->
                        <div style="display:flex;gap:8px;align-items:center;">
                            <label><input type="checkbox" name="activa" checked> Activa</label>
                            <label><input type="checkbox" name="destacada"> Destacada</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn-small" type="submit">Crear FAQ</button>
                    <button type="button" class="btn-small" onclick="closeModal('modalCrear')">Cancelar</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Toggle sidebar
        document.getElementById('menuToggle')?.addEventListener('click', function(){ document.querySelector('.sidebar')?.classList.toggle('active'); });
        /* Modal controller: openModal / closeModal with focus trap, escape and click-outside behaviour */
        (function(){
            var focusableSelectors = 'a[href], area[href], input:not([disabled]):not([type="hidden"]), select:not([disabled]), textarea:not([disabled]), button:not([disabled]), [tabindex]:not([tabindex="-1"])';
            var modalState = {};

            window.openModal = function(id) {
                var modal = document.getElementById(id);
                if (!modal) return;
                if (modal.classList.contains('open')) return;
                modal.classList.add('open');
                modal.style.display = 'flex';
                document.body.style.overflow = 'hidden';
                modal.setAttribute('aria-hidden','false');
                modalState[id] = { previousActive: document.activeElement };

                // focus first focusable element
                var focusables = Array.prototype.slice.call(modal.querySelectorAll(focusableSelectors));
                if (focusables.length) {
                    focusables[0].focus();
                } else {
                    var content = modal.querySelector('.modal-content');
                    if (content) {
                        content.setAttribute('tabindex', '-1');
                        content.focus();
                    }
                }

                // keydown handler for Escape and Tab trapping
                modalState[id].keydown = function(e){
                    if (e.key === 'Escape') { closeModal(id); return; }
                    if (e.key === 'Tab') {
                        var f = Array.prototype.slice.call(modal.querySelectorAll(focusableSelectors));
                        if (!f.length) { e.preventDefault(); return; }
                        var i = f.indexOf(document.activeElement);
                        if (e.shiftKey && i === 0) { e.preventDefault(); f[f.length - 1].focus(); }
                        else if (!e.shiftKey && i === f.length - 1) { e.preventDefault(); f[0].focus(); }
                    }
                };
                document.addEventListener('keydown', modalState[id].keydown);

                // click outside to close
                modalState[id].clickHandler = function(e){ if (e.target === modal) closeModal(id); };
                modal.addEventListener('click', modalState[id].clickHandler);

                // attach close handlers (buttons with .modal-close or data-modal-close)
                var closeButtons = modal.querySelectorAll('.modal-close, [data-modal-close]');
                closeButtons.forEach(function(btn){
                    btn.addEventListener('click', function(){ closeModal(id); });
                });
            };

            window.closeModal = function(id) {
                var modal = document.getElementById(id);
                if (!modal || !modal.classList.contains('open')) return;
                modal.classList.remove('open');
                modal.style.display = 'none';
                modal.setAttribute('aria-hidden','true');
                document.body.style.overflow = '';

                var state = modalState[id];
                if (state) {
                    document.removeEventListener('keydown', state.keydown);
                    modal.removeEventListener('click', state.clickHandler);
                    try { if (state.previousActive && typeof state.previousActive.focus === 'function') state.previousActive.focus(); } catch(e){}
                    delete modalState[id];
                }
            };

            // Add a small helper so the button still works if JS attaches after user clicked
            var btn = document.getElementById('btnNuevaFaq');
            if (btn) btn.addEventListener('click', function(e){ e.preventDefault(); openModal('modalCrear'); });
        })();
    </script>
</body>
</html>
