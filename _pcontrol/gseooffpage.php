<?php
session_start();
// Autenticació
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: index.php');
    exit;
}

require_once 'includes/role_check.php';

require_once __DIR__ . '/../classes/connexio.php';
require_once __DIR__ . '/../classes/seo_offpage_links.php';
require_once __DIR__ . '/../classes/seo_offpage_directories.php';

$tab = $_GET['tab'] ?? 'links'; // links | directories
// opcional: subviews handled by the included interfaces via GET params

// Processar formularis POST (fer la pàgina autònoma)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // --- Backlinks: crear i actualitzar ---
    if ($action === 'create_backlink') {
        try {
            $data = [
                'url_origen' => $_POST['url_origen'],
                'url_destino' => $_POST['url_destino'],
                'anchor_text' => $_POST['anchor_text'],
                'dominio_origen' => $_POST['dominio_origen'],
                'tipo_backlink' => $_POST['tipo_backlink'],
                'fecha_descubrimiento' => $_POST['fecha_descubrimiento'] ?? date('Y-m-d')
            ];
            // opcional
            if (!empty($_POST['da_origen'])) $data['da_origen'] = $_POST['da_origen'];
            if (!empty($_POST['dr_origen'])) $data['dr_origen'] = $_POST['dr_origen'];
            if (!empty($_POST['tf_origen'])) $data['tf_origen'] = $_POST['tf_origen'];
            if (!empty($_POST['cf_origen'])) $data['cf_origen'] = $_POST['cf_origen'];
            if (!empty($_POST['traffic_origen'])) $data['traffic_origen'] = $_POST['traffic_origen'];
            if (!empty($_POST['idioma_origen'])) $data['idioma_origen'] = $_POST['idioma_origen'];
            if (!empty($_POST['posicion_enlace'])) $data['posicion_enlace'] = $_POST['posicion_enlace'];
            if (!empty($_POST['contexto_backlink'])) $data['contexto_backlink'] = $_POST['contexto_backlink'];
            if (!empty($_POST['relevancia_tematica'])) $data['relevancia_tematica'] = $_POST['relevancia_tematica'];
            if (!empty($_POST['calidad_percibida'])) $data['calidad_percibida'] = $_POST['calidad_percibida'];
            if (!empty($_POST['prioridad'])) $data['prioridad'] = $_POST['prioridad'];
            if (!empty($_POST['campana_seo'])) $data['campana_seo'] = $_POST['campana_seo'];
            if (!empty($_POST['objetivo_seo'])) $data['objetivo_seo'] = $_POST['objetivo_seo'];
            if (!empty($_POST['notas_internas'])) $data['notas_internas'] = $_POST['notas_internas'];

            // Checkboxes
            $data['nofollow'] = isset($_POST['nofollow']) ? 1 : 0;
            $data['sponsored'] = isset($_POST['sponsored']) ? 1 : 0;
            $data['ugc'] = isset($_POST['ugc']) ? 1 : 0;

            $backlink = SEO_OffPage_Links::crear($data);
            $_SESSION['seo_saved'] = true;
            header('Location: gseooffpage.php?tab=links&saved=1&view=list');
            exit;
        } catch (Exception $e) {
            $_SESSION['seo_error'] = $e->getMessage();
            header('Location: gseooffpage.php?tab=links&error=1&view=create');
            exit;
        }
    }

    if ($action === 'update_backlink') {
        try {
            $id_offpage = $_POST['id_offpage'] ?? null;
            if (!$id_offpage) throw new Exception("ID de backlink no proporcionat");
            $backlink = new SEO_OffPage_Links($id_offpage);
            $data = [
                'url_origen' => $_POST['url_origen'],
                'url_destino' => $_POST['url_destino'],
                'anchor_text' => $_POST['anchor_text'],
                'dominio_origen' => $_POST['dominio_origen'],
                'tipo_backlink' => $_POST['tipo_backlink']
            ];
            if (isset($_POST['da_origen'])) $data['da_origen'] = $_POST['da_origen'] ?: null;
            if (isset($_POST['dr_origen'])) $data['dr_origen'] = $_POST['dr_origen'] ?: null;
            if (isset($_POST['tf_origen'])) $data['tf_origen'] = $_POST['tf_origen'] ?: null;
            if (isset($_POST['cf_origen'])) $data['cf_origen'] = $_POST['cf_origen'] ?: null;
            if (isset($_POST['traffic_origen'])) $data['traffic_origen'] = $_POST['traffic_origen'] ?: null;
            if (isset($_POST['idioma_origen'])) $data['idioma_origen'] = $_POST['idioma_origen'];
            if (isset($_POST['posicion_enlace'])) $data['posicion_enlace'] = $_POST['posicion_enlace'];
            if (isset($_POST['contexto_backlink'])) $data['contexto_backlink'] = $_POST['contexto_backlink'];
            if (isset($_POST['relevancia_tematica'])) $data['relevancia_tematica'] = $_POST['relevancia_tematica'];
            if (isset($_POST['calidad_percibida'])) $data['calidad_percibida'] = $_POST['calidad_percibida'];
            if (isset($_POST['prioridad'])) $data['prioridad'] = $_POST['prioridad'];
            if (isset($_POST['campana_seo'])) $data['campana_seo'] = $_POST['campana_seo'];
            if (isset($_POST['objetivo_seo'])) $data['objetivo_seo'] = $_POST['objetivo_seo'];
            if (isset($_POST['notas_internas'])) $data['notas_internas'] = $_POST['notas_internas'];
            if (isset($_POST['fecha_descubrimiento'])) $data['fecha_descubrimiento'] = $_POST['fecha_descubrimiento'];
            $data['nofollow'] = isset($_POST['nofollow']) ? 1 : 0;
            $data['sponsored'] = isset($_POST['sponsored']) ? 1 : 0;
            $data['ugc'] = isset($_POST['ugc']) ? 1 : 0;
            $backlink->actualitzarMultiplesCamps($data);
            $_SESSION['seo_saved'] = true;
            header('Location: gseooffpage.php?tab=links&saved=1&view=list');
            exit;
        } catch (Exception $e) {
            $_SESSION['seo_error'] = $e->getMessage();
            header('Location: gseooffpage.php?tab=links&error=1&view=edit&id_backlink=' . ($id_offpage ?? ''));
            exit;
        }
    }

    // --- Directoris: crear i actualitzar ---
    if ($action === 'create_directorio') {
        try {
            $data = [
                'nombre' => $_POST['nombre'] ?? '',
                'url' => $_POST['url'] ?? '',
                'categoria' => $_POST['categoria'] ?? 'psicologia',
                'da_directorio' => !empty($_POST['da_directorio']) ? $_POST['da_directorio'] : null,
                'costo' => $_POST['costo'] ?? 0,
                'idioma' => $_POST['idioma'] ?? 'es',
                'nofollow' => isset($_POST['nofollow']) ? 1 : 0,
                'permite_anchor_personalizado' => isset($_POST['permite_anchor_personalizado']) ? 1 : 0,
                'estado' => $_POST['estado'] ?? 'pendiente',
                'fecha_envio' => !empty($_POST['fecha_envio']) ? $_POST['fecha_envio'] : null,
                'fecha_aprobacion' => !empty($_POST['fecha_aprobacion']) ? $_POST['fecha_aprobacion'] : null,
                'notas' => $_POST['notas'] ?? null
            ];
            SEO_OffPage_Directories::crear($data);
            $_SESSION['seo_saved'] = true;
            header('Location: gseooffpage.php?tab=directories&saved=1&view=list');
            exit;
        } catch (Exception $e) {
            $_SESSION['seo_error'] = $e->getMessage();
            header('Location: gseooffpage.php?tab=directories&error=1&view=create');
            exit;
        }
    }

    if ($action === 'update_directorio') {
        try {
            $id_directorio = $_POST['id_directorio'] ?? null;
            if (!$id_directorio) throw new Exception("ID de directori no proporcionat");
            $directorio = new SEO_OffPage_Directories($id_directorio);
            $data = [
                'nombre' => $_POST['nombre'] ?? '',
                'url' => $_POST['url'] ?? '',
                'categoria' => $_POST['categoria'] ?? 'psicologia',
                'da_directorio' => !empty($_POST['da_directorio']) ? $_POST['da_directorio'] : null,
                'costo' => $_POST['costo'] ?? 0,
                'idioma' => $_POST['idioma'] ?? 'es',
                'nofollow' => isset($_POST['nofollow']) ? 1 : 0,
                'permite_anchor_personalizado' => isset($_POST['permite_anchor_personalizado']) ? 1 : 0,
                'estado' => $_POST['estado'] ?? 'pendiente',
                'fecha_envio' => !empty($_POST['fecha_envio']) ? $_POST['fecha_envio'] : null,
                'fecha_aprobacion' => !empty($_POST['fecha_aprobacion']) ? $_POST['fecha_aprobacion'] : null,
                'notas' => $_POST['notas'] ?? null
            ];
            $directorio->actualitzarMultiplesCamps($data);
            $_SESSION['seo_saved'] = true;
            header('Location: gseooffpage.php?tab=directories&saved=1&view=list');
            exit;
        } catch (Exception $e) {
            $_SESSION['seo_error'] = $e->getMessage();
            header('Location: gseooffpage.php?tab=directories&error=1&view=edit&id_directorio=' . ($id_directorio ?? ''));
            exit;
        }
    }
}

// Processar accions GET (eliminar backlink)
if (isset($_GET['action']) && $_GET['action'] === 'delete_backlink') {
    try {
        $id_offpage = $_GET['id_offpage'] ?? null;
        if (!$id_offpage) throw new Exception("ID de backlink no proporcionat");
        $backlink = new SEO_OffPage_Links($id_offpage);
        $backlink->eliminar();
        $_SESSION['seo_saved'] = true;
        header('Location: gseooffpage.php?tab=links&saved=1&view=list');
        exit;
    } catch (Exception $e) {
        $_SESSION['seo_error'] = $e->getMessage();
        header('Location: gseooffpage.php?tab=links&error=1&view=list');
        exit;
    }
}

// Processar accions GET (eliminar directori)
if (isset($_GET['action']) && $_GET['action'] === 'delete_directorio') {
    try {
        $id_directorio = $_GET['id_directorio'] ?? null;
        if (!$id_directorio) throw new Exception("ID de directori no proporcionat");
        $directorio = new SEO_OffPage_Directories($id_directorio);
        $directorio->eliminar();
        $_SESSION['seo_saved'] = true;
        header('Location: gseooffpage.php?tab=directories&saved=1&view=list');
        exit;
    } catch (Exception $e) {
        $_SESSION['seo_error'] = $e->getMessage();
        header('Location: gseooffpage.php?tab=directories&error=1&view=list');
        exit;
    }
}


?><!--
    Página de Gestión SEO Off-Page
    Estructura y estética basada en gseoonpage.php
-->
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestió SEO Off-Page</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@7.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="css/onpage.css">
    <link rel="stylesheet" href="css/offpage.css">
</head>
<body>
    <link rel="icon" type="image/png" sizes="32x32" href="../img/Logo32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="../img/Logo16.png">
<?php include 'includes/sidebar.php'; ?>
<div class="main-content">
    <header class="top-bar">
        <div class="top-bar-left">
            <h1><i class="fas fa-network-wired"></i> Gestió SEO Off-Page</h1>
        </div>
    </header>
    <div class="content-wrapper">
        <div style="display:flex;align-items:center;justify-content:space-between;gap:20px;margin-bottom:12px;">
            <div class="offpage-tabs" role="tablist">
                <a href="gseooffpage.php?tab=links" class="offpage-tab <?php echo $tab === 'links' ? 'active' : ''; ?>"><i class="fas fa-link"></i> Backlinks</a>
                <a href="gseooffpage.php?tab=directories" class="offpage-tab <?php echo $tab === 'directories' ? 'active' : ''; ?>"><i class="fas fa-folder-open"></i> Directorios</a>
            </div>
            <div>
                <button onclick="window.location.href='gseo.php?tab=offpage'" class="btn btn-sm btn-secondary"><i class="fas fa-arrow-left"></i> Volver a Off-Page General</button>
            </div>
        </div>

        <?php
        // Incluir la interfaz correspondiente
        if ($tab === 'directories') {
            include __DIR__ . '/includes/offpage_directories_interface.php';
        } else {
            include __DIR__ . '/includes/offpage_links_interface.php';
        }
        ?>

    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script src="js/dashboard.js"></script>
</body>
</html>