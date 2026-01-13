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
require_once __DIR__ . '/../classes/professional_photos.php';

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
$photosModel = ProfessionalPhotos::getInstance();

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
                    $profModel->setSubtitolCa($_POST['subtitol_ca'] ?? null);
                    $profModel->setSubtitolEs($_POST['subtitol_es'] ?? null);
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
                        $profModel->setSubtitolCa($_POST['subtitol_ca'] ?? null);
                        $profModel->setSubtitolEs($_POST['subtitol_es'] ?? null);
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

                case 'editar_certificacio':
                    $prof_id = (int)($_POST['professional_id'] ?? 0);
                    $index = (int)($_POST['index'] ?? -1);
                    $cert_ca_editada = trim($_POST['cert_ca'] ?? '');
                    $cert_es_editada = trim($_POST['cert_es'] ?? '');
                    
                    if ($index >= 0 && !empty($cert_ca_editada) && !empty($cert_es_editada)) {
                        $certActuals = $certModel->obtenirPerProfessional($prof_id);
                        
                        if ($certActuals) {
                            $llistatCa = array_filter(explode("\n", $certActuals['certificacions_ca']));
                            $llistatEs = array_filter(explode("\n", $certActuals['certificacions_es']));
                            $llistatCa = array_values($llistatCa);
                            $llistatEs = array_values($llistatEs);
                            
                            // Actualitzar l'element específic
                            $llistatCa[$index] = $cert_ca_editada;
                            $llistatEs[$index] = $cert_es_editada;
                            
                            $cert_ca = implode("\n", $llistatCa);
                            $cert_es = implode("\n", $llistatEs);
                            
                            if ($certModel->guardarCertificacions($prof_id, $cert_ca, $cert_es)) {
                                $mensaje = 'Certificación actualizada correctamente.';
                                $tipoMensaje = 'success';
                            } else {
                                $mensaje = 'Error al actualizar la certificación.';
                                $tipoMensaje = 'error';
                            }
                        }
                    } else {
                        $mensaje = 'Datos incompletos para editar.';
                        $tipoMensaje = 'error';
                    }
                    
                    $redirect = 'gprofessionals.php?vista=certificacions&id=' . $prof_id;
                    header('Location: ' . $redirect . '&msg=' . urlencode($mensaje) . '&type=' . $tipoMensaje);
                    exit;

                case 'eliminar_una_certificacio':
                    $prof_id = (int)($_POST['professional_id'] ?? 0);
                    $index = (int)($_POST['index'] ?? -1);
                    
                    $certActuals = $certModel->obtenirPerProfessional($prof_id);
                    
                    if ($certActuals && $index >= 0) {
                        $llistatCa = array_filter(explode("\n", $certActuals['certificacions_ca']));
                        $llistatEs = array_filter(explode("\n", $certActuals['certificacions_es']));
                        $llistatCa = array_values($llistatCa);
                        $llistatEs = array_values($llistatEs);
                        
                        // Eliminar l'element de tots dos idiomes
                        unset($llistatCa[$index]);
                        unset($llistatEs[$index]);
                        
                        $cert_ca = implode("\n", $llistatCa);
                        $cert_es = implode("\n", $llistatEs);
                        
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

                // ============= ACCIONES DE FOTOS =============
                case 'crear_foto':
                    $prof_id = (int)($_POST['professional_id'] ?? 0);
                    
                    $photosModel->netejar();
                    $photosModel->setProfessionalId($prof_id);
                    $photosModel->setImagePath($_POST['image_path'] ?? '');
                    $photosModel->setTitleCa($_POST['title_ca'] ?? '');
                    $photosModel->setTitleEs($_POST['title_es'] ?? '');
                    $photosModel->setDescriptionCa($_POST['description_ca'] ?? null);
                    $photosModel->setDescriptionEs($_POST['description_es'] ?? null);
                    $photosModel->setAltCa($_POST['alt_ca'] ?? '');
                    $photosModel->setAltEs($_POST['alt_es'] ?? '');
                    
                    try {
                        $photosModel->crear();
                        $mensaje = 'Foto creada correctamente.';
                        $tipoMensaje = 'success';
                    } catch (Exception $e) {
                        $mensaje = 'Error al crear la foto: ' . $e->getMessage();
                        $tipoMensaje = 'error';
                    }
                    
                    $redirect = 'gprofessionals.php?vista=fotos&id=' . $prof_id;
                    header('Location: ' . $redirect . '&msg=' . urlencode($mensaje) . '&type=' . $tipoMensaje);
                    exit;

                case 'editar_foto':
                    $foto_id = (int)($_POST['foto_id'] ?? 0);
                    $prof_id = (int)($_POST['professional_id'] ?? 0);
                    
                    $photosModel->netejar();
                    $photosModel->setId($foto_id);
                    $photosModel->setProfessionalId($prof_id);
                    $photosModel->setImagePath($_POST['image_path'] ?? '');
                    $photosModel->setTitleCa($_POST['title_ca'] ?? '');
                    $photosModel->setTitleEs($_POST['title_es'] ?? '');
                    $photosModel->setDescriptionCa($_POST['description_ca'] ?? null);
                    $photosModel->setDescriptionEs($_POST['description_es'] ?? null);
                    $photosModel->setAltCa($_POST['alt_ca'] ?? '');
                    $photosModel->setAltEs($_POST['alt_es'] ?? '');
                    
                    try {
                        $photosModel->actualitzar();
                        $mensaje = 'Foto actualizada correctamente.';
                        $tipoMensaje = 'success';
                    } catch (Exception $e) {
                        $mensaje = 'Error al actualizar la foto: ' . $e->getMessage();
                        $tipoMensaje = 'error';
                    }
                    
                    $redirect = 'gprofessionals.php?vista=fotos&id=' . $prof_id;
                    header('Location: ' . $redirect . '&msg=' . urlencode($mensaje) . '&type=' . $tipoMensaje);
                    exit;

                case 'eliminar_foto':
                    $foto_id = (int)($_POST['foto_id'] ?? 0);
                    $prof_id = (int)($_POST['professional_id'] ?? 0);
                    
                    try {
                        $photosModel->eliminar($foto_id);
                        $mensaje = 'Foto eliminada correctamente.';
                        $tipoMensaje = 'success';
                    } catch (Exception $e) {
                        $mensaje = 'Error al eliminar la foto: ' . $e->getMessage();
                        $tipoMensaje = 'error';
                    }
                    
                    $redirect = 'gprofessionals.php?vista=fotos&id=' . $prof_id;
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
    $profEditar = $profModel->obtenirPerId((int)$idEditar);
    if ($profEditar) {
        $especialitatsEditar = $relModel->obtenirIdsEspecialitatsProfessional($idEditar);
        $certificacionsEditar = $certModel->obtenirPerProfessional($idEditar);
    }
}

// Si estamos en vista de certificaciones o fotos, también cargar el profesional
if (($vista === 'certificacions' || $vista === 'fotos') && $idEditar && !$profEditar) {
    $profEditar = $profModel->obtenirPerId((int)$idEditar);
    if ($profEditar) {
        error_log("DEBUG: Profesional cargado correctamente: " . $profEditar['nom']);
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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@7.0.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Libre+Baskerville:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="css/configuracion.css">
    <style>
        /* Estilos copiados de gblog.css para consistencia (list-container y list-table) */
        .list-container {
            background: white;
            border-radius: 8px;
            overflow: hidden;
            width: 100%;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            margin-top: 20px;
        }

        .list-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.9rem;
            table-layout: fixed; /* Forzar ancho fijo para columnas */
        }

        .list-table thead {
            background: #f8f9fa;
            border-bottom: 2px solid #dee2e6;
        }

        .list-table th {
            padding: 15px 12px;
            text-align: left;
            font-weight: 600;
            color: #495057;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            white-space: nowrap;
        }

        .list-table tbody tr {
            border-bottom: 1px solid #f0f0f0;
            transition: all 0.2s ease;
        }

        .list-table tbody tr:hover {
            background: #f8f9fa;
        }

        .list-table td {
            padding: 16px 12px;
            color: #333;
            vertical-align: middle;
            word-wrap: break-word; /* Romper palabras largas */
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .list-table .text-center { text-align: center; }

        /* Legacy styles */
        .professionals-table { width:100%; border-collapse:collapse; margin-top:20px; }
        .professionals-table th, .professionals-table td { padding:12px; border-bottom:1px solid #eee; text-align:left; position:relative; z-index:1; }
        
        /* Responsive para tablas */
        @media (max-width: 1024px) {
            .list-table th:nth-child(3), /* Email */
            .list-table td:nth-child(3),
            .list-table th:nth-child(4), /* Telefon */
            .list-table td:nth-child(4) {
                display: none;
            }
        }
        
        @media (max-width: 768px) {
            .list-table th:nth-child(6), /* Experiencia */
            .list-table td:nth-child(6),
            .list-table th:nth-child(7), /* Especialidades */
            .list-table td:nth-child(7) {
                display: none;
            }
        }
        
        /* Badge estilos actualizados */
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            white-space: nowrap;
        }
        .status-activa { background-color: #d4edda; color: #155724; }
        .status-inactiva { background-color: #f8d7da; color: #721c24; }
        .status-web { background-color: #cce5ff; color: #004085; }
        .status-oculto { background-color: #fff3cd; color: #856404; }

        /* Tabs para secciones */
        .section-tabs { display:flex; gap:8px; margin-bottom:20px; border-bottom:2px solid #eee; }
        .section-tab { padding:12px 24px; background:transparent; border:none; cursor:pointer; font-size:16px; font-weight:500; color:#666; border-bottom:3px solid transparent; transition:all 0.3s; }
        .section-tab:hover { color:#333; background:#f8f9fa; }
        .section-tab.active { color:#007bff; border-bottom-color:#007bff; }
        .professionals-table th { background:#f8f9fa; font-weight:600; }
        .professionals-actions button { margin-right:8px; padding:6px 12px; }
        .filter-row { display:flex; gap:12px; align-items:center; margin-bottom:16px; flex-wrap:wrap; }
        .filter-row .form-control { 
            padding:10px 14px; 
            border:1px solid #ddd; 
            border-radius:6px; 
            font-size:14px;
            transition:all 0.2s ease;
            background:#fff;
        }
        .filter-row .form-control:focus { 
            outline:none; 
            border-color:#007bff; 
            box-shadow:0 0 0 3px rgba(0,123,255,0.1);
        }
        .filter-row input[type="text"].form-control { 
            flex:1; 
            min-width:250px;
        }
        .filter-row select.form-control { 
            min-width:150px;
            cursor:pointer;
            appearance:none;
            background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%23666' d='M6 9L1 4h10z'/%3E%3C/svg%3E");
            background-repeat:no-repeat;
            background-position:right 12px center;
            padding-right:36px;
        }
        .filter-row select.form-control:hover {
            border-color:#999;
        }
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
            <?php elseif ($vista !== 'certificacions' && $vista !== 'fotos'): ?>
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
                                <div class="list-container">
                                    <table class="list-table">
                                        <thead>
                                            <tr>
                                                <th style="width: 50px;">ID</th>
                                                <th>Nombre Completo</th>
                                                <th>Email</th>
                                                <th>Teléfono</th>
                                                <th>Nº Colegiado</th>
                                                <th>Experiencia</th>
                                                <th>Especialidades</th>
                                                <th class="text-center" style="width: 100px;">Estado</th>
                                                <th class="text-center" style="width: 140px;">Acciones</th>
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
                                                        <div class="item-name">
                                                            <strong><?php echo htmlspecialchars($prof['nom'] . ' ' . $prof['cognoms']); ?></strong>
                                                        </div>
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
                                                    <td class="text-center">
                                                        <div style="display:flex;flex-direction:column;gap:4px;align-items:center;">
                                                            <?php 
                                                            // Badge de estado activo/inactivo
                                                            if (isset($prof['actiu']) && $prof['actiu']) {
                                                                echo '<span class="status-badge status-activa"><i class="fas fa-check-circle"></i> Activo</span>';
                                                            } else {
                                                                echo '<span class="status-badge status-inactiva"><i class="fas fa-times-circle"></i> Inactivo</span>';
                                                            }
                                                            
                                                            // Badge de visibilidad web
                                                            if (isset($prof['visible_web']) && $prof['visible_web']) {
                                                                echo '<span class="status-badge status-web"><i class="fas fa-globe"></i> Web</span>';
                                                            } else {
                                                                echo '<span class="status-badge status-oculto"><i class="fas fa-eye-slash"></i> Oculto</span>';
                                                            }
                                                            ?>
                                                        </div>
                                                    </td>
                                                    <td class="text-center">
                                                        <div style="display:flex;gap:6px;justify-content:center;flex-wrap:wrap;">
                                                        <a href="?vista=editar&id=<?php echo $prof['id']; ?>" 
                                                           class="btn btn-sm btn-primary" 
                                                           title="Editar profesional"
                                                           style="border-radius:6px;padding:6px 10px;">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        
                                                        <a href="?vista=certificacions&id=<?php echo $prof['id']; ?>" 
                                                           class="btn btn-sm btn-info" 
                                                           title="Certificaciones y másters"
                                                           style="border-radius:6px;padding:6px 10px;">
                                                            <i class="fas fa-certificate"></i>
                                                        </a>
                                                        
                                                        <a href="?vista=fotos&id=<?php echo $prof['id']; ?>" 
                                                           class="btn btn-sm btn-success" 
                                                           title="Galería de fotos"
                                                           style="border-radius:6px;padding:6px 10px;">
                                                            <i class="fas fa-images"></i>
                                                        </a>
                                                        
                                                        <form method="POST" style="display:inline;margin:0;" onsubmit="return confirm('¿Cambiar estado activo/inactivo?');">
                                                            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                                            <input type="hidden" name="accion" value="toggle_actiu">
                                                            <input type="hidden" name="id" value="<?php echo $prof['id']; ?>">
                                                            <button type="submit" 
                                                                    class="btn btn-sm btn-warning" 
                                                                    title="<?php echo $prof['actiu'] ? 'Desactivar' : 'Activar'; ?> profesional"
                                                                    style="border-radius:6px;padding:6px 10px;">
                                                                <i class="fas fa-toggle-<?php echo $prof['actiu'] ? 'on' : 'off'; ?>"></i>
                                                            </button>
                                                        </form>
                                                        
                                                        <form method="POST" style="display:inline;margin:0;" onsubmit="return confirm('¿Eliminar este profesional? Esta acción no se puede deshacer.');">
                                                            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                                            <input type="hidden" name="accion" value="eliminar">
                                                            <input type="hidden" name="id" value="<?php echo $prof['id']; ?>">
                                                            <button type="submit" 
                                                                    class="btn btn-sm btn-danger" 
                                                                    title="Eliminar profesional"
                                                                    style="border-radius:6px;padding:6px 10px;">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
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
                                        <label for="subtitol_ca">Subtítulo (Catalán)</label>
                                        <input type="text" id="subtitol_ca" name="subtitol_ca" class="form-control" maxlength="50" placeholder="Ej: Psicòloga especialista">
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="subtitol_es">Subtítulo (Español)</label>
                                        <input type="text" id="subtitol_es" name="subtitol_es" class="form-control" maxlength="50" placeholder="Ej: Psicóloga especialista">
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
                                        <label for="foto">Foto del Professional</label>
                                        <div class="image-picker-container">
                                            <input type="hidden" id="foto" name="foto" value="">
                                            <div class="image-picker-controls">
                                                <button type="button" id="btnPickFoto" class="btn btn-secondary">
                                                    <i class="fas fa-images"></i> Seleccionar Imagen
                                                </button>
                                                <button type="button" id="btnClearFoto" class="btn btn-outline-secondary" style="display:none;">
                                                    <i class="fas fa-times"></i> Eliminar
                                                </button>
                                            </div>
                                            <div class="image-preview-container" id="fotoPreviewContainer" style="display:none; margin-top:15px;">
                                                <img id="fotoPreview" src="" alt="Preview" class="foto-preview" style="max-width:300px; max-height:300px; border-radius:8px; box-shadow:0 2px 8px rgba(0,0,0,0.1);">
                                                <p class="image-url-text" id="fotoUrlText" style="font-size:0.85rem; color:#666; margin-top:8px; word-break:break-all;"></p>
                                            </div>
                                        </div>
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
                                    $certListCa = array_values($certListCa);
                                    $certListEs = array_values($certListEs);
                                }
                            ?>

                            <!-- Listado de certificaciones existentes -->
                            <?php if (!empty($certListCa) || !empty($certListEs)): ?>
                            <div style="margin-bottom:30px;">
                                <h3><i class="fas fa-list"></i> Certificaciones Actuales</h3>
                                
                                <div style="margin-top:20px;">
                                    <style>
                                        .cert-display {
                                            padding: 8px;
                                            min-height: 20px;
                                        }
                                        .cert-edit {
                                            width: 100%;
                                            padding: 6px;
                                        }
                                        .btn-actions, .btn-edit-actions {
                                            display: inline-flex;
                                            gap: 5px;
                                        }
                                        .data-table tbody tr:hover {
                                            background-color: #f8f9fa;
                                        }
                                        .data-table td {
                                            vertical-align: middle;
                                        }
                                    </style>
                                    <table class="data-table" style="width:100%;">
                                        <thead>
                                            <tr>
                                                <th style="width:5%;">#</th>
                                                <th style="width:42%;">Catalán</th>
                                                <th style="width:42%;">Español</th>
                                                <th style="width:11%; text-align:center;">Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php 
                                            $maxCount = max(count($certListCa), count($certListEs));
                                            for ($i = 0; $i < $maxCount; $i++): 
                                                $certCa = $certListCa[$i] ?? '';
                                                $certEs = $certListEs[$i] ?? '';
                                            ?>
                                                <tr id="row-<?php echo $i; ?>">
                                                    <td style="text-align:center; font-weight:bold; color:#7f8c8d;">
                                                        <?php echo $i + 1; ?>
                                                    </td>
                                                    <td>
                                                        <div class="cert-display" id="display-ca-<?php echo $i; ?>">
                                                            <?php echo htmlspecialchars(trim($certCa)); ?>
                                                        </div>
                                                        <input type="text" class="form-control cert-edit" 
                                                               id="edit-ca-<?php echo $i; ?>" 
                                                               value="<?php echo htmlspecialchars(trim($certCa)); ?>"
                                                               style="display:none;">
                                                    </td>
                                                    <td>
                                                        <div class="cert-display" id="display-es-<?php echo $i; ?>">
                                                            <?php echo htmlspecialchars(trim($certEs)); ?>
                                                        </div>
                                                        <input type="text" class="form-control cert-edit" 
                                                               id="edit-es-<?php echo $i; ?>" 
                                                               value="<?php echo htmlspecialchars(trim($certEs)); ?>"
                                                               style="display:none;">
                                                    </td>
                                                    <td style="text-align:center;">
                                                        <div class="btn-actions" id="actions-<?php echo $i; ?>">
                                                            <button type="button" class="btn btn-sm btn-primary" 
                                                                    onclick="editarCertificacio(<?php echo $i; ?>)"
                                                                    title="Editar">
                                                                <i class="fas fa-edit"></i>
                                                            </button>
                                                            <form method="POST" style="display:inline;" 
                                                                  onsubmit="return confirm('¿Eliminar esta certificación en ambos idiomas?');">
                                                                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                                                <input type="hidden" name="accion" value="eliminar_una_certificacio">
                                                                <input type="hidden" name="professional_id" value="<?php echo $idEditar; ?>">
                                                                <input type="hidden" name="index" value="<?php echo $i; ?>">
                                                                <button type="submit" class="btn btn-sm btn-danger" title="Eliminar">
                                                                    <i class="fas fa-trash"></i>
                                                                </button>
                                                            </form>
                                                        </div>
                                                        <div class="btn-edit-actions" id="edit-actions-<?php echo $i; ?>" style="display:none;">
                                                            <button type="button" class="btn btn-sm btn-success" 
                                                                    onclick="guardarCertificacio(<?php echo $i; ?>)"
                                                                    title="Guardar">
                                                                <i class="fas fa-check"></i>
                                                            </button>
                                                            <button type="button" class="btn btn-sm btn-secondary" 
                                                                    onclick="cancelarEdicion(<?php echo $i; ?>)"
                                                                    title="Cancelar">
                                                                <i class="fas fa-times"></i>
                                                            </button>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endfor; ?>
                                        </tbody>
                                    </table>
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
                                        <label for="subtitol_ca">Subtítulo (Catalán)</label>
                                        <input type="text" id="subtitol_ca" name="subtitol_ca" class="form-control" maxlength="50"
                                               value="<?php echo htmlspecialchars($profEditar['subtitol_ca'] ?? ''); ?>" placeholder="Ej: Psicòloga especialista">
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="subtitol_es">Subtítulo (Español)</label>
                                        <input type="text" id="subtitol_es" name="subtitol_es" class="form-control" maxlength="50"
                                               value="<?php echo htmlspecialchars($profEditar['subtitol_es'] ?? ''); ?>" placeholder="Ej: Psicóloga especialista">
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
                                        <label for="foto_edit">Foto del Professional</label>
                                        <div class="image-picker-container">
                                            <input type="hidden" id="foto_edit" name="foto" value="<?php echo htmlspecialchars($profEditar['foto'] ?? ''); ?>">
                                            <div class="image-picker-controls">
                                                <button type="button" id="btnPickFotoEdit" class="btn btn-secondary">
                                                    <i class="fas fa-images"></i> Seleccionar Imagen
                                                </button>
                                                <button type="button" id="btnClearFotoEdit" class="btn btn-outline-secondary" <?php echo empty($profEditar['foto']) ? 'style="display:none;"' : ''; ?>>
                                                    <i class="fas fa-times"></i> Eliminar
                                                </button>
                                            </div>
                                            <div class="image-preview-container" id="fotoPreviewContainerEdit" <?php echo empty($profEditar['foto']) ? 'style="display:none;"' : 'style="margin-top:15px;"'; ?>>
                                                <img id="fotoPreviewEdit" src="<?php echo htmlspecialchars($profEditar['foto'] ?? ''); ?>" alt="Preview" class="foto-preview" style="max-width:300px; max-height:300px; border-radius:8px; box-shadow:0 2px 8px rgba(0,0,0,0.1);">
                                                <p class="image-url-text" id="fotoUrlTextEdit" style="font-size:0.85rem; color:#666; margin-top:8px; word-break:break-all;"><?php echo htmlspecialchars($profEditar['foto'] ?? ''); ?></p>
                                            </div>
                                        </div>
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

                <?php elseif ($vista === 'fotos' && $idEditar): ?>
                    <!-- Gestión de Fotos del Professional -->
                    <?php 
                    // Carregar el professional directament aquí per assegurar-nos que està disponible
                    $profFotos = $profModel->obtenirPerId((int)$idEditar);
                    
                    if (!$profFotos) {
                        echo '<div class="alert alert-danger">No se pudo cargar el profesional.</div>';
                    } else {
                        $fotos = $photosModel->llistarPerProfessional($idEditar);
                ?>
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
                    <h2 style="margin:0;"><i class="fas fa-images"></i> Fotos de <?php echo htmlspecialchars($profFotos['nom'] . ' ' . $profFotos['cognoms']); ?></h2>
                    <div>
                        <a href="gprofessionals.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Volver a Profesionales
                        </a>
                    </div>
                </div>

                <!-- Formulario para crear nueva foto -->
                <div class="card" style="margin-bottom:24px;">
                    <div class="card-header">
                        <h3 style="margin:0;"><i class="fas fa-plus-circle"></i> Añadir Nueva Foto</h3>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                            <input type="hidden" name="accion" value="crear_foto">
                            <input type="hidden" name="professional_id" value="<?php echo $idEditar; ?>">
                            
                            <div class="form-grid" style="grid-template-columns: 1fr 1fr;">
                                <div class="form-group">
                                    <label for="new_title_ca">Título (Catalán) *</label>
                                    <input type="text" id="new_title_ca" name="title_ca" class="form-control" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="new_title_es">Título (Español) *</label>
                                    <input type="text" id="new_title_es" name="title_es" class="form-control" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="new_alt_ca">Texto Alternativo (Catalán) *</label>
                                    <input type="text" id="new_alt_ca" name="alt_ca" class="form-control" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="new_alt_es">Texto Alternativo (Español) *</label>
                                    <input type="text" id="new_alt_es" name="alt_es" class="form-control" required>
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Imagen *</label>
                                <div class="image-picker-container">
                                    <input type="hidden" id="new_image_path" name="image_path" required>
                                    <div class="image-picker-controls">
                                        <button type="button" id="btnPickNewFoto" class="btn btn-secondary">
                                            <i class="fas fa-images"></i> Seleccionar Imagen
                                        </button>
                                        <button type="button" id="btnClearNewFoto" class="btn btn-outline-secondary" style="display:none;">
                                            <i class="fas fa-times"></i> Eliminar
                                        </button>
                                    </div>
                                    <div class="image-preview-container" id="newFotoPreviewContainer" style="display:none; margin-top:15px;">
                                        <img id="newFotoPreview" src="" alt="Preview" style="max-width:300px; max-height:300px; border-radius:8px; box-shadow:0 2px 8px rgba(0,0,0,0.1);">
                                        <p class="image-url-text" id="newFotoUrlText" style="font-size:0.85rem; color:#666; margin-top:8px; word-break:break-all;"></p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-grid" style="grid-template-columns: 1fr 1fr;">
                                <div class="form-group">
                                    <label for="new_description_ca">Descripción (Catalán)</label>
                                    <textarea id="new_description_ca" name="description_ca" class="form-control" rows="3"></textarea>
                                </div>
                                
                                <div class="form-group">
                                    <label for="new_description_es">Descripción (Español)</label>
                                    <textarea id="new_description_es" name="description_es" class="form-control" rows="3"></textarea>
                                </div>
                            </div>
                            
                            <div class="form-actions">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Guardar Foto
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Lista de fotos existentes -->
                <div class="card">
                    <div class="card-header">
                        <h3 style="margin:0;"><i class="fas fa-list"></i> Fotos Existentes (<?php echo count($fotos); ?>)</h3>
                    </div>
                    <div class="card-body">
                        <?php if (empty($fotos)): ?>
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i>
                                No hay fotos registradas para este profesional.
                            </div>
                        <?php else: ?>
                            <div style="display:grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap:20px;">
                                <?php foreach ($fotos as $foto): ?>
                                    <div class="card" style="margin:0;">
                                        <div style="position:relative;">
                                            <?php 
                                                $fotoPath = $foto['image_path'];
                                                if (strpos($fotoPath, 'http') !== 0) {
                                                    if (strpos($fotoPath, '../') !== 0 && strpos($fotoPath, 'img/') === 0) {
                                                        $fotoPath = '../' . $fotoPath;
                                                    } elseif (strpos($fotoPath, '../') !== 0 && strpos($fotoPath, 'img/') !== 0) {
                                                        $fotoPath = '../img/' . $fotoPath;
                                                    }
                                                }
                                            ?>
                                            <img src="<?php echo htmlspecialchars($fotoPath); ?>" 
                                                 alt="<?php echo htmlspecialchars($foto['alt_ca']); ?>"
                                                 style="width:100%; height:200px; object-fit:cover;">
                                        </div>
                                        <div class="card-body">
                                            <h4 style="margin:0 0 8px 0;"><?php echo htmlspecialchars($foto['title_ca']); ?></h4>
                                            <p style="font-size:0.9em; color:#666; margin-bottom:12px;">
                                                <?php echo htmlspecialchars($foto['description_ca'] ?: 'Sin descripción'); ?>
                                            </p>
                                            <div style="display:flex; gap:8px;">
                                                <button type="button" class="btn btn-sm btn-primary" onclick="editarFoto(<?php echo $foto['id']; ?>)">
                                                    <i class="fas fa-edit"></i> Editar
                                                </button>
                                                <form method="POST" style="display:inline;" onsubmit="return confirm('¿Eliminar esta foto?');">
                                                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                                    <input type="hidden" name="accion" value="eliminar_foto">
                                                    <input type="hidden" name="foto_id" value="<?php echo $foto['id']; ?>">
                                                    <input type="hidden" name="professional_id" value="<?php echo $idEditar; ?>">
                                                    <button type="submit" class="btn btn-sm btn-danger">
                                                        <i class="fas fa-trash"></i> Eliminar
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                        
                                        <!-- Modal de edición oculto por defecto -->
                                        <div id="editModal_<?php echo $foto['id']; ?>" style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(0,0,0,0.5); z-index:1000; padding:20px; overflow-y:auto;">
                                            <div style="max-width:800px; margin:40px auto; background:white; border-radius:8px; padding:24px;">
                                                <h3><i class="fas fa-edit"></i> Editar Foto</h3>
                                                <form method="POST">
                                                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                                    <input type="hidden" name="accion" value="editar_foto">
                                                    <input type="hidden" name="foto_id" value="<?php echo $foto['id']; ?>">
                                                    <input type="hidden" name="professional_id" value="<?php echo $idEditar; ?>">
                                                    
                                                    <div class="form-grid" style="grid-template-columns: 1fr 1fr;">
                                                        <div class="form-group">
                                                            <label>Título (Catalán) *</label>
                                                            <input type="text" name="title_ca" class="form-control" value="<?php echo htmlspecialchars($foto['title_ca']); ?>" required>
                                                        </div>
                                                        <div class="form-group">
                                                            <label>Título (Español) *</label>
                                                            <input type="text" name="title_es" class="form-control" value="<?php echo htmlspecialchars($foto['title_es']); ?>" required>
                                                        </div>
                                                        <div class="form-group">
                                                            <label>Alt (Catalán) *</label>
                                                            <input type="text" name="alt_ca" class="form-control" value="<?php echo htmlspecialchars($foto['alt_ca']); ?>" required>
                                                        </div>
                                                        <div class="form-group">
                                                            <label>Alt (Español) *</label>
                                                            <input type="text" name="alt_es" class="form-control" value="<?php echo htmlspecialchars($foto['alt_es']); ?>" required>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="form-group">
                                                        <label>Imagen *</label>
                                                        <div class="image-picker-container">
                                                            <input type="hidden" name="image_path" id="editFotoImagePath_<?php echo $foto['id']; ?>" value="<?php echo htmlspecialchars($foto['image_path']); ?>" required>
                                                            <div class="image-picker-controls">
                                                                <button type="button" class="btn btn-secondary btnPickEditFoto" data-foto-id="<?php echo $foto['id']; ?>">
                                                                    <i class="fas fa-images"></i> Seleccionar Imagen
                                                                </button>
                                                                <button type="button" class="btn btn-outline-secondary btnClearEditFoto" data-foto-id="<?php echo $foto['id']; ?>">
                                                                    <i class="fas fa-times"></i> Eliminar
                                                                </button>
                                                            </div>
                                                            <div class="image-preview-container" id="editFotoPreviewContainer_<?php echo $foto['id']; ?>" style="margin-top:15px;">
                                                                <img id="editFotoPreview_<?php echo $foto['id']; ?>" src="<?php echo htmlspecialchars($foto['image_path']); ?>" alt="Preview" class="foto-preview" style="max-width:300px; max-height:300px; border-radius:8px; box-shadow:0 2px 8px rgba(0,0,0,0.1);">
                                                                <p class="image-url-text" id="editFotoUrlText_<?php echo $foto['id']; ?>" style="font-size:0.85rem; color:#666; margin-top:8px; word-break:break-all;"><?php echo htmlspecialchars($foto['image_path']); ?></p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="form-grid" style="grid-template-columns: 1fr 1fr;">
                                                        <div class="form-group">
                                                            <label>Descripción (Catalán)</label>
                                                            <textarea name="description_ca" class="form-control" rows="3"><?php echo htmlspecialchars($foto['description_ca'] ?? ''); ?></textarea>
                                                        </div>
                                                        <div class="form-group">
                                                            <label>Descripción (Español)</label>
                                                            <textarea name="description_es" class="form-control" rows="3"><?php echo htmlspecialchars($foto['description_es'] ?? ''); ?></textarea>
                                                        </div>
                                                    </div>
                                                    
                                                    <div style="display:flex; gap:8px; margin-top:16px;">
                                                        <button type="submit" class="btn btn-primary">
                                                            <i class="fas fa-save"></i> Guardar Cambios
                                                        </button>
                                                        <button type="button" class="btn btn-secondary" onclick="cerrarModal(<?php echo $foto['id']; ?>)">
                                                            <i class="fas fa-times"></i> Cancelar
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php } // End else profesional encontrado ?>
            
            <?php endif; // End if/elseif vistas de professionals ?>
            
            <?php endif; // End if seccion === 'professionals' ?>
            
            <?php if ($seccion === 'especialitats'): ?>
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
                                <div class="list-container">
                                    <table class="list-table">
                                        <thead>
                                            <tr>
                                                <th style="width: 60px;">ID</th>
                                                <th style="width: 250px;">Nombre</th>
                                                <th style="width: 300px;">Descripción</th>
                                                <th class="text-center" style="width: 140px;">Nº Profesionales</th>
                                                <th style="width: 120px;">Fecha Creación</th>
                                                <th class="text-center" style="width: 120px;">Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($especialitats as $esp): ?>
                                                <?php 
                                                    $numProfs = $relModel->comptarProfessionalsEspecialitat($esp['id']);
                                                ?>
                                                <tr>
                                                    <td><?php echo $esp['id']; ?></td>
                                                    <td>
                                                        <div class="item-name">
                                                            <strong><?php echo htmlspecialchars($esp['nom']); ?></strong>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <?php if (!empty($esp['descripcio'])): ?>
                                                            <small><?php echo htmlspecialchars(substr($esp['descripcio'], 0, 100)); ?>
                                                            <?php echo strlen($esp['descripcio']) > 100 ? '...' : ''; ?></small>
                                                        <?php else: ?>
                                                            <small style="color:#999;">Sin descripción</small>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td class="text-center">
                                                    <?php if ($numProfs > 0): ?>
                                                        <span class="status-badge status-activa"><?php echo $numProfs; ?> profesional(es)</span>
                                                    <?php else: ?>
                                                        <span style="color:#999;">0</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?php echo date('d/m/Y', strtotime($esp['created_at'])); ?></td>
                                                <td class="text-center">
                                                    <div style="display:flex;gap:6px;justify-content:center;">
                                                        <a href="?seccion=especialitats&vista=editar&id_especialitat=<?php echo $esp['id']; ?>" 
                                                            class="btn btn-sm btn-primary" title="Editar" style="border-radius:6px;padding:6px 10px;">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        
                                                        <form method="POST" style="display:inline;margin:0;" onsubmit="return confirm('¿Eliminar esta especialidad?<?php echo $numProfs > 0 ? ' (Tiene ' . $numProfs . ' profesional/es asignados)' : ''; ?>');">
                                                        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                                        <input type="hidden" name="accion" value="eliminar_especialitat">
                                                        <input type="hidden" name="id_especialitat" value="<?php echo $esp['id']; ?>">
                                                        <button type="submit" class="btn btn-sm btn-danger" 
                                                                title="Eliminar"
                                                                style="border-radius:6px;padding:6px 10px;"
                                                                <?php echo $numProfs > 0 ? 'disabled' : ''; ?>>
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                                </div>
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
            
            <?php endif; // End if seccion === 'especialitats' ?>

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

            // --- Media picker helper for photo selection ---
            function openMediaPickerFor(callback) {
                try {
                    var picker = window.open('gmedia.php?picker=1&admin_picker=1', 'MediaPicker', 'width=900,height=600');
                } catch (err) {
                    console.error('open popup failed', err);
                    return;
                }
                function receive(e) {
                    try {
                        if (e.origin && e.origin !== window.location.origin) {
                            return;
                        }
                    } catch (err) {
                        console.error('origin check failed', err);
                    }
                    if (e.data && e.data.mediaUrl) {
                        try { callback(e.data); } catch (err) { console.error('picker callback error', err); }
                        window.removeEventListener('message', receive);
                    }
                }
                window.addEventListener('message', receive);
                setTimeout(function(){ try { window.removeEventListener('message', receive); } catch(e){} }, 30000);
            }

            // Wire up photo picker for CREATE form
            var btnPick = document.getElementById('btnPickFoto');
            var btnClear = document.getElementById('btnClearFoto');
            var input = document.getElementById('foto');
            var preview = document.getElementById('fotoPreview');
            var previewContainer = document.getElementById('fotoPreviewContainer');
            var urlText = document.getElementById('fotoUrlText');

            function setPhoto(url) {
                if (input) input.value = url || '';
                if (url) {
                    if (preview) preview.src = url;
                    if (previewContainer) previewContainer.style.display = 'block';
                    if (urlText) urlText.textContent = url;
                    if (btnClear) btnClear.style.display = 'inline-block';
                } else {
                    if (preview) preview.src = '';
                    if (previewContainer) previewContainer.style.display = 'none';
                    if (urlText) urlText.textContent = '';
                    if (btnClear) btnClear.style.display = 'none';
                }
            }

            if (btnPick) btnPick.addEventListener('click', function(){
                openMediaPickerFor(function(data){
                    setPhoto(data.mediaUrl);
                });
            });
            if (btnClear) btnClear.addEventListener('click', function(){ setPhoto(''); });

            // Wire up photo picker for EDIT form
            var btnPickEdit = document.getElementById('btnPickFotoEdit');
            var btnClearEdit = document.getElementById('btnClearFotoEdit');
            var inputEdit = document.getElementById('foto_edit');
            var previewEdit = document.getElementById('fotoPreviewEdit');
            var previewContainerEdit = document.getElementById('fotoPreviewContainerEdit');
            var urlTextEdit = document.getElementById('fotoUrlTextEdit');

            function setPhotoEdit(url) {
                if (inputEdit) inputEdit.value = url || '';
                if (url) {
                    if (previewEdit) previewEdit.src = url;
                    if (previewContainerEdit) previewContainerEdit.style.display = 'block';
                    if (urlTextEdit) urlTextEdit.textContent = url;
                    if (btnClearEdit) btnClearEdit.style.display = 'inline-block';
                } else {
                    if (previewEdit) previewEdit.src = '';
                    if (previewContainerEdit) previewContainerEdit.style.display = 'none';
                    if (urlTextEdit) urlTextEdit.textContent = '';
                    if (btnClearEdit) btnClearEdit.style.display = 'none';
                }
            }

            if (btnPickEdit) btnPickEdit.addEventListener('click', function(){
                openMediaPickerFor(function(data){
                    setPhotoEdit(data.mediaUrl);
                });
            });
            if (btnClearEdit) btnClearEdit.addEventListener('click', function(){ setPhotoEdit(''); });

            // Wire up photo picker for NEW FOTO in gallery
            var btnPickNewFoto = document.getElementById('btnPickNewFoto');
            var btnClearNewFoto = document.getElementById('btnClearNewFoto');
            var inputNewFoto = document.getElementById('new_image_path');
            var previewNewFoto = document.getElementById('newFotoPreview');
            var previewContainerNewFoto = document.getElementById('newFotoPreviewContainer');
            var urlTextNewFoto = document.getElementById('newFotoUrlText');

            function setNewFoto(url) {
                if (inputNewFoto) inputNewFoto.value = url || '';
                if (url) {
                    if (previewNewFoto) previewNewFoto.src = url;
                    if (previewContainerNewFoto) previewContainerNewFoto.style.display = 'block';
                    if (urlTextNewFoto) urlTextNewFoto.textContent = url;
                    if (btnClearNewFoto) btnClearNewFoto.style.display = 'inline-block';
                } else {
                    if (previewNewFoto) previewNewFoto.src = '';
                    if (previewContainerNewFoto) previewContainerNewFoto.style.display = 'none';
                    if (urlTextNewFoto) urlTextNewFoto.textContent = '';
                    if (btnClearNewFoto) btnClearNewFoto.style.display = 'none';
                }
            }

            if (btnPickNewFoto) btnPickNewFoto.addEventListener('click', function(){
                openMediaPickerFor(function(data){
                    setNewFoto(data.mediaUrl);
                });
            });
            if (btnClearNewFoto) btnClearNewFoto.addEventListener('click', function(){ setNewFoto(''); });

            // Wire up photo pickers for EDIT FOTO in gallery modals (dynamic)
            document.addEventListener('click', function(e) {
                // Selector d'imatge per editar foto
                if (e.target.closest('.btnPickEditFoto')) {
                    var btn = e.target.closest('.btnPickEditFoto');
                    var fotoId = btn.getAttribute('data-foto-id');
                    openMediaPickerFor(function(data) {
                        var input = document.getElementById('editFotoImagePath_' + fotoId);
                        var preview = document.getElementById('editFotoPreview_' + fotoId);
                        var previewContainer = document.getElementById('editFotoPreviewContainer_' + fotoId);
                        var urlText = document.getElementById('editFotoUrlText_' + fotoId);
                        
                        if (input) input.value = data.mediaUrl;
                        if (preview) preview.src = data.mediaUrl;
                        if (previewContainer) previewContainer.style.display = 'block';
                        if (urlText) urlText.textContent = data.mediaUrl;
                    });
                }
                
                // Eliminar imatge d'editar foto
                if (e.target.closest('.btnClearEditFoto')) {
                    var btn = e.target.closest('.btnClearEditFoto');
                    var fotoId = btn.getAttribute('data-foto-id');
                    
                    var input = document.getElementById('editFotoImagePath_' + fotoId);
                    var preview = document.getElementById('editFotoPreview_' + fotoId);
                    var previewContainer = document.getElementById('editFotoPreviewContainer_' + fotoId);
                    var urlText = document.getElementById('editFotoUrlText_' + fotoId);
                    
                    if (input) input.value = '';
                    if (preview) preview.src = '';
                    if (previewContainer) previewContainer.style.display = 'none';
                    if (urlText) urlText.textContent = '';
                }
            });

            // Functions for edit modal management
            window.editarFoto = function(fotoId) {
                var modal = document.getElementById('editModal_' + fotoId);
                if (modal) modal.style.display = 'block';
            };

            window.cerrarModal = function(fotoId) {
                var modal = document.getElementById('editModal_' + fotoId);
                if (modal) modal.style.display = 'none';
            };

            // Close modals on ESC key
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    var modals = document.querySelectorAll('[id^="editModal_"]');
                    modals.forEach(function(modal) {
                        modal.style.display = 'none';
                    });
                }
            });

            // Funciones para editar certificaciones inline
            window.editarCertificacio = function(index) {
                // Ocultar display, mostrar inputs
                document.getElementById('display-ca-' + index).style.display = 'none';
                document.getElementById('display-es-' + index).style.display = 'none';
                document.getElementById('edit-ca-' + index).style.display = 'block';
                document.getElementById('edit-es-' + index).style.display = 'block';
                
                // Cambiar botones
                document.getElementById('actions-' + index).style.display = 'none';
                document.getElementById('edit-actions-' + index).style.display = 'block';
            };

            window.cancelarEdicion = function(index) {
                // Mostrar display, ocultar inputs (y resetear valores)
                var inputCa = document.getElementById('edit-ca-' + index);
                var inputEs = document.getElementById('edit-es-' + index);
                var displayCa = document.getElementById('display-ca-' + index);
                var displayEs = document.getElementById('display-es-' + index);
                
                // Resetear valores a los originales
                inputCa.value = displayCa.textContent.trim();
                inputEs.value = displayEs.textContent.trim();
                
                displayCa.style.display = 'block';
                displayEs.style.display = 'block';
                inputCa.style.display = 'none';
                inputEs.style.display = 'none';
                
                // Cambiar botones
                document.getElementById('actions-' + index).style.display = 'block';
                document.getElementById('edit-actions-' + index).style.display = 'none';
            };

            window.guardarCertificacio = function(index) {
                var certCa = document.getElementById('edit-ca-' + index).value.trim();
                var certEs = document.getElementById('edit-es-' + index).value.trim();
                
                if (!certCa || !certEs) {
                    alert('Ambos campos son obligatorios.');
                    return;
                }
                
                // Crear formulario para enviar
                var form = document.createElement('form');
                form.method = 'POST';
                form.action = 'gprofessionals.php';
                
                var fields = {
                    'csrf_token': '<?php echo $csrf_token; ?>',
                    'accion': 'editar_certificacio',
                    'professional_id': '<?php echo $idEditar; ?>',
                    'index': index,
                    'cert_ca': certCa,
                    'cert_es': certEs
                };
                
                for (var key in fields) {
                    var input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = key;
                    input.value = fields[key];
                    form.appendChild(input);
                }
                
                document.body.appendChild(form);
                form.submit();
            };
        });
    </script>
    
</body>
</html>
