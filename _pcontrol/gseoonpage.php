<?php
session_start();
// Verificar autenticació (mateix comportament que a gseo.php)
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
	// Si demanem debug, no fem redirect: mostrem informació de sessió i cookies
	if (isset($_GET['debug']) && $_GET['debug'] == '1') {
		echo "<pre style='background:#f8f9fa;padding:12px;border:1px solid #ddd;border-radius:6px;'>";
		echo "DEBUG: Accés sense autenticació\n";
		echo "Session ID: " . session_id() . "\n\n";
		echo "\$_SESSION:\n";
		print_r($_SESSION);
		echo "\n\n\$_COOKIE:\n";
		print_r($_COOKIE);
		echo "</pre>";
		exit;
	}
	header('Location: index.php');
	exit;
}

require_once __DIR__ . '/../classes/seo_onpage.php';

// Processar formulari On Page SEO
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$action = $_POST['action'] ?? '';
	if ($action === 'save_onpage' || $action === 'create_onpage') {
		try {
			$id_pagina = $_POST['id_pagina'] ?? null;
			$data = [
				'url_relativa_ca' => $_POST['url_relativa_ca'] ?? '',
				'url_relativa_es' => $_POST['url_relativa_es'] ?? '',
				'titulo_pagina' => $_POST['titulo_pagina'] ?? '',
				'tipo_pagina' => $_POST['tipo_pagina'] ?? 'landing',
				'title_ca' => $_POST['title_ca'] ?? '',
				'meta_description_ca' => $_POST['meta_description_ca'] ?? '',
				'h1_ca' => $_POST['h1_ca'] ?? '',
				'contenido_principal_ca' => $_POST['contenido_principal_ca'] ?? null,
				'title_es' => $_POST['title_es'] ?? '',
				'meta_description_es' => $_POST['meta_description_es'] ?? '',
				'h1_es' => $_POST['h1_es'] ?? '',
				'contenido_principal_es' => $_POST['contenido_principal_es'] ?? null,
				'slug_ca' => $_POST['slug_ca'] ?? null,
				'slug_es' => $_POST['slug_es'] ?? null,
				'meta_robots' => $_POST['meta_robots'] ?? 'index, follow',
				'canonical_url_ca' => $_POST['canonical_url_ca'] ?? null,
				'canonical_url_es' => $_POST['canonical_url_es'] ?? null,
				'priority' => $_POST['priority'] ?? '0.8',
				'changefreq' => $_POST['changefreq'] ?? 'monthly',
				'focus_keyword_ca' => $_POST['focus_keyword_ca'] ?? null,
				'focus_keyword_es' => $_POST['focus_keyword_es'] ?? null,
				'keywords_secundarias_ca' => $_POST['keywords_secundarias_ca'] ?? null,
				'keywords_secundarias_es' => $_POST['keywords_secundarias_es'] ?? null,
				'og_title_ca' => $_POST['og_title_ca'] ?? null,
				'og_title_es' => $_POST['og_title_es'] ?? null,
				'og_description_ca' => $_POST['og_description_ca'] ?? null,
				'og_description_es' => $_POST['og_description_es'] ?? null,
				'og_image' => $_POST['og_image'] ?? null,
				'twitter_title_ca' => $_POST['twitter_title_ca'] ?? null,
				'twitter_title_es' => $_POST['twitter_title_es'] ?? null,
				'twitter_description_ca' => $_POST['twitter_description_ca'] ?? null,
				'twitter_description_es' => $_POST['twitter_description_es'] ?? null,
				'twitter_image' => $_POST['twitter_image'] ?? null,
				'featured_image' => $_POST['featured_image'] ?? null,
				'alt_image_ca' => $_POST['alt_image_ca'] ?? null,
				'alt_image_es' => $_POST['alt_image_es'] ?? null,
				'activa' => isset($_POST['activa']) ? 1 : 0,
				'fecha_publicacion' => $_POST['fecha_publicacion'] ?? null
			];
			if ($id_pagina) {
				$pagina = new SEO_OnPage($id_pagina);
				$pagina->actualitzarMultiplesCamps($data);
				$pagina->actualitzarMetriques();
				$pagina->calcularSeoScore();
				$_SESSION['seo_saved'] = true;
				header('Location: gseoonpage.php?saved=1');
			} else {
				$id_nueva = SEO_OnPage::crear($data);
				if ($id_nueva) {
					$pagina = new SEO_OnPage($id_nueva);
					$pagina->actualitzarMetriques();
					$pagina->calcularSeoScore();
					$_SESSION['seo_saved'] = true;
					header('Location: gseoonpage.php?saved=1&created=' . $id_nueva);
				} else {
					throw new Exception("No s'ha pogut crear la pàgina");
				}
			}
			exit;
		} catch (Exception $e) {
			$_SESSION['seo_error'] = $e->getMessage();
			header('Location: gseoonpage.php?error=1');
			exit;
		}
	} elseif ($action === 'delete_onpage') {
		try {
			$id_pagina = $_POST['id_pagina'] ?? null;
			if ($id_pagina) {
				$pagina = new SEO_OnPage($id_pagina);
				$pagina->eliminar();
				$_SESSION['seo_saved'] = true;
				header('Location: gseoonpage.php?saved=1&deleted=1');
			} else {
				throw new Exception("ID de pàgina no proporcionat");
			}
			exit;
		} catch (Exception $e) {
			$_SESSION['seo_error'] = $e->getMessage();
			header('Location: gseoonpage.php?error=1');
			exit;
		}
	}
}

// Carregar pàgines SEO On-Page
$is_new = isset($_GET['new']) && $_GET['new'] == '1';
$paginas_onpage = [];
$pagina_edit = null;
$tipo_filtro = $_GET['tipo'] ?? 'all';
$seo_onpage_stats = null;
try {
	if (isset($_GET['edit']) && $_GET['edit']) {
		$pagina_edit = new SEO_OnPage($_GET['edit']);
	}
	$conn = Connexio::getInstance();
	$pdo = $conn->getConnexio();
	$sql = "SELECT id_pagina FROM seo_onpage_paginas ORDER BY fecha_publicacion DESC";
	$stmt = $pdo->prepare($sql);
	$stmt->execute();
	$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
	$paginas_onpage = [];
	$load_errors = [];
	$db_row_ids = array_map(function($r){ return $r['id_pagina']; }, $rows ?: []);
	$db_rows_count = count($rows ?: []);
	foreach ($rows as $row) {
		try {
			$paginas_onpage[] = new SEO_OnPage($row['id_pagina']);
		} catch (Exception $e) {
			// collect but continue
			$load_errors[] = ['id' => $row['id_pagina'], 'error' => $e->getMessage()];
		}
	}
	$seo_onpage_stats = SEO_OnPage::calcularEstadistiquesGlobals();
} catch (Exception $e) {
	$error_message = $e->getMessage();
}
$saved = isset($_GET['saved']) && $_GET['saved'] == '1';
$error = isset($_GET['error']) && $_GET['error'] == '1';
?>
<!DOCTYPE html>
<html lang="es">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Gestió SEO On Page</title>
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@7.0.0/css/all.min.css">
	<link rel="stylesheet" href="css/dashboard.css">
	<link rel="stylesheet" href="css/onpage.css">
</head>
<body>
	<link rel="icon" type="image/png" sizes="32x32" href="../img/Logo32.png">
	<link rel="icon" type="image/png" sizes="16x16" href="../img/Logo16.png">
<?php include 'includes/sidebar.php'; ?>
<div class="main-content">
	<header class="top-bar">
		<div class="top-bar-left">
			<h1><i class="fas fa-file-alt"></i> Gestió SEO On Page</h1>
		</div>
	</header>
	<div class="content-wrapper" style="margin-top:32px;">
		<div class="onpage-table-container">
				<div class="onpage-table-header" style="display: flex; align-items: center; justify-content: space-between; gap: 20px; margin-bottom: 18px;">
					<h2 style="margin: 0;"><i class="fas fa-list"></i> Pàgines SEO On Page</h2>
					<button type="button" onclick="openNewModal()" class="btn btn-small btn-primary" style="margin-left: 12px;"><i class="fas fa-plus"></i> Nova pàgina</button>
				</div>
				<style>
				.onpage-table-header {
					padding: 8px;
				}
				.onpage-table {
					margin-top: 0;
				}
				</style>
				<table class="onpage-table">
						<thead>
								<tr>
										<th>ID</th>
										<th>URL (CA)</th>
										<th>URL (ES)</th>
										<th>Títol (CA)</th>
										<th>Títol (ES)</th>
										<th>Activa</th>
										<th>Accions</th>
								</tr>
						</thead>
						<tbody>
							 <?php foreach ($paginas_onpage as $pagina): ?>
									 <tr>
											 <td><?php echo htmlspecialchars($pagina->getIdPagina()); ?></td>
											 <td><?php echo htmlspecialchars($pagina->getUrlRelativaCa()); ?></td>
											 <td><?php echo htmlspecialchars($pagina->getUrlRelativaEs()); ?></td>
											 <td><?php echo htmlspecialchars($pagina->getTitle('ca')); ?></td>
											 <td><?php echo htmlspecialchars($pagina->getTitle('es')); ?></td>
											 <td><?php echo ($pagina->isActiva() ? 'Sí' : 'No'); ?></td>
											 <td>
													<?php $json_payload = htmlspecialchars(json_encode([
														'id_pagina' => $pagina->getIdPagina(),
														'url_relativa_ca' => $pagina->getUrlRelativaCa(),
														'url_relativa_es' => $pagina->getUrlRelativaEs(),
														'titulo_pagina' => $pagina->getTituloPagina(),
														'tipo_pagina' => $pagina->getTipoPagina(),
														'title_ca' => $pagina->getTitle('ca'),
														'meta_description_ca' => $pagina->getMetaDescription('ca'),
														'h1_ca' => $pagina->getH1('ca'),
															'contenido_principal_ca' => $pagina->getContenido('ca'),
														'title_es' => $pagina->getTitle('es'),
														'meta_description_es' => $pagina->getMetaDescription('es'),
														'h1_es' => $pagina->getH1('es'),
															'contenido_principal_es' => $pagina->getContenido('es'),
														'slug_ca' => $pagina->getSlug('ca'),
														'slug_es' => $pagina->getSlug('es'),
														'meta_robots' => $pagina->getMetaRobots(),
														'canonical_url_ca' => $pagina->getCanonicalUrl('ca'),
														'canonical_url_es' => $pagina->getCanonicalUrl('es'),
														'priority' => $pagina->getPriority(),
														'changefreq' => $pagina->getChangefreq(),
														'focus_keyword_ca' => $pagina->getFocusKeyword('ca'),
														'focus_keyword_es' => $pagina->getFocusKeyword('es'),
														'keywords_secundarias_ca' => $pagina->getKeywordsSecundarias('ca'),
														'keywords_secundarias_es' => $pagina->getKeywordsSecundarias('es'),
														'og_title_ca' => $pagina->getOgTitle('ca'),
														'og_title_es' => $pagina->getOgTitle('es'),
														'og_description_ca' => $pagina->getOgDescription('ca'),
														'og_description_es' => $pagina->getOgDescription('es'),
														'og_image' => $pagina->getOgImage(),
														'twitter_title_ca' => $pagina->getTwitterTitle('ca'),
														'twitter_title_es' => $pagina->getTwitterTitle('es'),
														'twitter_description_ca' => $pagina->getTwitterDescription('ca'),
														'twitter_description_es' => $pagina->getTwitterDescription('es'),
														'twitter_image' => $pagina->getTwitterImage(),
														'featured_image' => $pagina->getFeaturedImage(),
														'alt_image_ca' => $pagina->getAltImage('ca'),
														'alt_image_es' => $pagina->getAltImage('es'),
														'activa' => $pagina->isActiva(),
														'fecha_publicacion' => $pagina->getFechaPublicacion()
													]), ENT_QUOTES, 'UTF-8'); ?>
													<button type="button" data-json="<?php echo $json_payload; ?>" onclick="(function(btn){ try{ openEditModal(<?php echo $pagina->getIdPagina(); ?>, btn.dataset.json); }catch(e){ console.error(e); } })(this)" class="btn btn-small btn-secondary"><i class="fas fa-edit"></i> Edita</button>
													<button class="btn btn-small btn-danger" onclick="openDeleteModal(<?php echo $pagina->getIdPagina(); ?>)"><i class="fas fa-trash"></i> Elimina</button>
											 </td>
									 </tr>
							 <?php endforeach; ?>
						</tbody>
				</table>
		</div>

				<?php if (isset($_GET['debug']) && $_GET['debug'] == '1'): ?>
					<div style="background:#e9f7ef;border:1px solid #c7eed8;padding:12px;border-radius:8px;margin-top:16px;color:#155724;">
						<strong>Debug DB:</strong> Filas devueltas por la consulta: <?php echo $db_rows_count ?? 0; ?>.
						<div style="margin-top:6px;"><strong>IDs:</strong> <?php echo htmlspecialchars(json_encode($db_row_ids ?? [], JSON_UNESCAPED_UNICODE)); ?></div>
						<div style="margin-top:6px;"><strong>Objetos cargados:</strong> <?php echo count($paginas_onpage); ?></div>
					</div>
				<?php endif; ?>

				<?php if (!empty($load_errors)): ?>
					<div style="background:#fff3cd;border:1px solid #ffeeba;padding:12px;border-radius:8px;margin-top:16px;color:#856404;">
						<strong>Debug:</strong> Hi ha errors carregant algunes pàgines. Mostrant <?php echo count($paginas_onpage); ?> pàgines, errors: <?php echo count($load_errors); ?>.
						<details style="margin-top:8px;"><summary>Mostrar errors</summary><pre><?php echo htmlspecialchars(json_encode($load_errors, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE)); ?></pre></details>
					</div>
				<?php endif; ?>

		<!-- Modal Afegir/Editar SEO On Page -->
		<div id="seoOnPageModal" onclick="closeModal()" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background-color:rgba(0,0,0,0.6); z-index:99999; animation:fadeIn 0.3s ease-out;">
			<div class="modal-container" onclick="event.stopPropagation()" style="background-color:#fff; border-radius:20px; box-shadow:0 20px 60px rgba(0,0,0,0.3); max-width:800px; width:90%; max-height:90vh; overflow-y:auto; position:absolute; top:50%; left:50%; transform:translate(-50%, -50%); animation:slideIn 0.3s ease-out;">
				<div class="modal-header" style="padding:30px 40px 20px; border-bottom:1px solid #e0e0e0; display:flex; justify-content:space-between; align-items:center;">
					<h2 style="margin:0; font-size:1.8rem; font-weight:700; color:#333; letter-spacing:0.5px;">Configuració SEO de la pàgina</h2>
					<button type="button" onclick="closeModal()" style="background:none; border:none; font-size:2rem; color:#999; cursor:pointer; padding:0; line-height:1; transition:color 0.2s;">&times;</button>
				</div>
				<div class="modal-body" style="padding:30px 40px;">
					<form id="seoOnPageForm" method="post" action="gseoonpage.php">
						<input type="hidden" name="action" value="save_onpage">
						<input type="hidden" name="id_pagina" id="modal_id_pagina">

						<fieldset style="border:1px solid #ddd; border-radius:10px; padding:20px; margin-bottom:25px;">
							<legend style="font-weight:600; color:#555; padding:0 10px;">Informació Bàsica</legend>
							<div class="form-row" style="display:flex; gap:20px; margin-bottom:15px;">
								<div class="form-group" style="flex:1;">
									<label for="modal_url_relativa_ca" style="display:block; margin-bottom:5px; font-weight:500; color:#333;">URL (CA)</label>
									<input type="text" name="url_relativa_ca" id="modal_url_relativa_ca" required style="width:100%; padding:10px; border:1px solid #ccc; border-radius:8px; font-size:1rem;">
								</div>
								<div class="form-group" style="flex:1;">
									<label for="modal_url_relativa_es" style="display:block; margin-bottom:5px; font-weight:500; color:#333;">URL (ES)</label>
									<input type="text" name="url_relativa_es" id="modal_url_relativa_es" required style="width:100%; padding:10px; border:1px solid #ccc; border-radius:8px; font-size:1rem;">
								</div>
							</div>
							<div class="form-group" style="margin-bottom:15px;">
								<label for="modal_titulo_pagina" style="display:block; margin-bottom:5px; font-weight:500; color:#333;">Títol visible</label>
								<input type="text" name="titulo_pagina" id="modal_titulo_pagina" style="width:100%; padding:10px; border:1px solid #ccc; border-radius:8px; font-size:1rem;">
							</div>
							<div class="form-row" style="display:flex; gap:20px; margin-bottom:15px;">
								<div class="form-group" style="flex:1;">
									<label for="modal_tipo_pagina" style="display:block; margin-bottom:5px; font-weight:500; color:#333;">Tipus de pàgina</label>
									<select name="tipo_pagina" id="modal_tipo_pagina" style="width:100%; padding:10px; border:1px solid #ccc; border-radius:8px; font-size:1rem;">
										<option value="home">Home</option>
										<option value="sobre-mi">Sobre Mí</option>
										<option value="servicios">Servicios</option>
										<option value="blog">Blog</option>
										<option value="articulo">Artículo</option>
										<option value="contacto">Contacto</option>
										<option value="legal">Legal</option>
										<option value="landing">Landing</option>
									</select>
								</div>
								<div class="form-group" style="flex:1;">
									<label for="modal_activa" style="display:block; margin-bottom:5px; font-weight:500; color:#333;">Activa</label>
									<select name="activa" id="modal_activa" style="width:100%; padding:10px; border:1px solid #ccc; border-radius:8px; font-size:1rem;">
										<option value="1">Sí</option>
										<option value="0">No</option>
									</select>
								</div>
							</div>
							<div class="form-group">
								<label for="modal_fecha_publicacion" style="display:block; margin-bottom:5px; font-weight:500; color:#333;">Data publicació</label>
								<input type="date" name="fecha_publicacion" id="modal_fecha_publicacion" style="width:100%; padding:10px; border:1px solid #ccc; border-radius:8px; font-size:1rem;">
							</div>
						</fieldset>

						<fieldset style="border:1px solid #ddd; border-radius:10px; padding:20px; margin-bottom:25px;">
							<legend style="font-weight:600; color:#555; padding:0 10px;">Contingut Català</legend>
							<div class="form-group" style="margin-bottom:15px;">
								<label for="modal_title_ca" style="display:block; margin-bottom:5px; font-weight:500; color:#333;">Meta Title (CA)</label>
								<input type="text" name="title_ca" id="modal_title_ca" required style="width:100%; padding:10px; border:1px solid #ccc; border-radius:8px; font-size:1rem;">
							</div>
							<div class="form-group" style="margin-bottom:15px;">
								<label for="modal_meta_description_ca" style="display:block; margin-bottom:5px; font-weight:500; color:#333;">Meta Description (CA)</label>
								<textarea name="meta_description_ca" id="modal_meta_description_ca" style="width:100%; padding:10px; border:1px solid #ccc; border-radius:8px; font-size:1rem; min-height:80px;"></textarea>
							</div>
							<div class="form-group" style="margin-bottom:15px;">
								<label for="modal_h1_ca" style="display:block; margin-bottom:5px; font-weight:500; color:#333;">H1 (CA)</label>
								<input type="text" name="h1_ca" id="modal_h1_ca" style="width:100%; padding:10px; border:1px solid #ccc; border-radius:8px; font-size:1rem;">
							</div>
							<div class="form-group" style="margin-bottom:15px;">
								<label for="modal_contenido_principal_ca" style="display:block; margin-bottom:5px; font-weight:500; color:#333;">Contingut principal (CA)</label>
								<textarea name="contenido_principal_ca" id="modal_contenido_principal_ca" style="width:100%; padding:10px; border:1px solid #ccc; border-radius:8px; font-size:1rem; min-height:100px;"></textarea>
							</div>
							<div class="form-row" style="display:flex; gap:20px;">
								<div class="form-group" style="flex:1;">
									<label for="modal_slug_ca" style="display:block; margin-bottom:5px; font-weight:500; color:#333;">Slug (CA)</label>
									<input type="text" name="slug_ca" id="modal_slug_ca" style="width:100%; padding:10px; border:1px solid #ccc; border-radius:8px; font-size:1rem;">
								</div>
								<div class="form-group" style="flex:1;">
									<label for="modal_focus_keyword_ca" style="display:block; margin-bottom:5px; font-weight:500; color:#333;">Focus Keyword (CA)</label>
									<input type="text" name="focus_keyword_ca" id="modal_focus_keyword_ca" style="width:100%; padding:10px; border:1px solid #ccc; border-radius:8px; font-size:1rem;">
								</div>
							</div>
						</fieldset>

						<fieldset style="border:1px solid #ddd; border-radius:10px; padding:20px; margin-bottom:25px;">
							<legend style="font-weight:600; color:#555; padding:0 10px;">Contingut Castellà</legend>
							<div class="form-group" style="margin-bottom:15px;">
								<label for="modal_title_es" style="display:block; margin-bottom:5px; font-weight:500; color:#333;">Meta Title (ES)</label>
								<input type="text" name="title_es" id="modal_title_es" required style="width:100%; padding:10px; border:1px solid #ccc; border-radius:8px; font-size:1rem;">
							</div>
							<div class="form-group" style="margin-bottom:15px;">
								<label for="modal_meta_description_es" style="display:block; margin-bottom:5px; font-weight:500; color:#333;">Meta Description (ES)</label>
								<textarea name="meta_description_es" id="modal_meta_description_es" style="width:100%; padding:10px; border:1px solid #ccc; border-radius:8px; font-size:1rem; min-height:80px;"></textarea>
							</div>
							<div class="form-group" style="margin-bottom:15px;">
								<label for="modal_h1_es" style="display:block; margin-bottom:5px; font-weight:500; color:#333;">H1 (ES)</label>
								<input type="text" name="h1_es" id="modal_h1_es" style="width:100%; padding:10px; border:1px solid #ccc; border-radius:8px; font-size:1rem;">
							</div>
							<div class="form-group" style="margin-bottom:15px;">
								<label for="modal_contenido_principal_es" style="display:block; margin-bottom:5px; font-weight:500; color:#333;">Contingut principal (ES)</label>
								<textarea name="contenido_principal_es" id="modal_contenido_principal_es" style="width:100%; padding:10px; border:1px solid #ccc; border-radius:8px; font-size:1rem; min-height:100px;"></textarea>
							</div>
							<div class="form-row" style="display:flex; gap:20px;">
								<div class="form-group" style="flex:1;">
									<label for="modal_slug_es" style="display:block; margin-bottom:5px; font-weight:500; color:#333;">Slug (ES)</label>
									<input type="text" name="slug_es" id="modal_slug_es" style="width:100%; padding:10px; border:1px solid #ccc; border-radius:8px; font-size:1rem;">
								</div>
								<div class="form-group" style="flex:1;">
									<label for="modal_focus_keyword_es" style="display:block; margin-bottom:5px; font-weight:500; color:#333;">Focus Keyword (ES)</label>
									<input type="text" name="focus_keyword_es" id="modal_focus_keyword_es" style="width:100%; padding:10px; border:1px solid #ccc; border-radius:8px; font-size:1rem;">
								</div>
							</div>
						</fieldset>

						<fieldset style="border:1px solid #ddd; border-radius:10px; padding:20px; margin-bottom:25px;">
							<legend style="font-weight:600; color:#555; padding:0 10px;">SEO Avançat</legend>
							<div class="form-row" style="display:flex; gap:20px; margin-bottom:15px;">
								<div class="form-group" style="flex:1;">
									<label for="modal_meta_robots" style="display:block; margin-bottom:5px; font-weight:500; color:#333;">Meta Robots</label>
									<input type="text" name="meta_robots" id="modal_meta_robots" style="width:100%; padding:10px; border:1px solid #ccc; border-radius:8px; font-size:1rem;" value="index, follow">
								</div>
								<div class="form-group" style="flex:1;">
									<label for="modal_canonical_url_ca" style="display:block; margin-bottom:5px; font-weight:500; color:#333;">Canonical URL (CA)</label>
									<input type="text" name="canonical_url_ca" id="modal_canonical_url_ca" style="width:100%; padding:10px; border:1px solid #ccc; border-radius:8px; font-size:1rem;">
								</div>
								<div class="form-group" style="flex:1;">
									<label for="modal_canonical_url_es" style="display:block; margin-bottom:5px; font-weight:500; color:#333;">Canonical URL (ES)</label>
									<input type="text" name="canonical_url_es" id="modal_canonical_url_es" style="width:100%; padding:10px; border:1px solid #ccc; border-radius:8px; font-size:1rem;">
								</div>
							</div>
							<div class="form-row" style="display:flex; gap:20px; margin-bottom:15px;">
								<div class="form-group" style="flex:1;">
									<label for="modal_priority" style="display:block; margin-bottom:5px; font-weight:500; color:#333;">Prioritat</label>
									<input type="text" name="priority" id="modal_priority" style="width:100%; padding:10px; border:1px solid #ccc; border-radius:8px; font-size:1rem;" value="0.8">
								</div>
								<div class="form-group" style="flex:1;">
									<label for="modal_changefreq" style="display:block; margin-bottom:5px; font-weight:500; color:#333;">Changefreq</label>
									<select name="changefreq" id="modal_changefreq" style="width:100%; padding:10px; border:1px solid #ccc; border-radius:8px; font-size:1rem;">
										<option value="always">Always</option>
										<option value="hourly">Hourly</option>
										<option value="daily">Daily</option>
										<option value="weekly">Weekly</option>
										<option value="monthly" selected>Monthly</option>
										<option value="yearly">Yearly</option>
										<option value="never">Never</option>
									</select>
								</div>
							</div>
							<div class="form-group" style="margin-bottom:15px;">
								<label for="modal_keywords_secundarias_ca" style="display:block; margin-bottom:5px; font-weight:500; color:#333;">Keywords Secundàries (CA)</label>
								<input type="text" name="keywords_secundarias_ca" id="modal_keywords_secundarias_ca" style="width:100%; padding:10px; border:1px solid #ccc; border-radius:8px; font-size:1rem;">
							</div>
							<div class="form-group">
								<label for="modal_keywords_secundarias_es" style="display:block; margin-bottom:5px; font-weight:500; color:#333;">Keywords Secundàries (ES)</label>
								<input type="text" name="keywords_secundarias_es" id="modal_keywords_secundarias_es" style="width:100%; padding:10px; border:1px solid #ccc; border-radius:8px; font-size:1rem;">
							</div>
						</fieldset>

						<fieldset style="border:1px solid #ddd; border-radius:10px; padding:20px;">
							<legend style="font-weight:600; color:#555; padding:0 10px;">Open Graph i Imatges</legend>
							<div class="form-row" style="display:flex; gap:20px; margin-bottom:15px;">
								<div class="form-group" style="flex:1;">
									<label for="modal_og_title_ca" style="display:block; margin-bottom:5px; font-weight:500; color:#333;">OG Title (CA)</label>
									<input type="text" name="og_title_ca" id="modal_og_title_ca" style="width:100%; padding:10px; border:1px solid #ccc; border-radius:8px; font-size:1rem;">
								</div>
								<div class="form-group" style="flex:1;">
									<label for="modal_og_title_es" style="display:block; margin-bottom:5px; font-weight:500; color:#333;">OG Title (ES)</label>
									<input type="text" name="og_title_es" id="modal_og_title_es" style="width:100%; padding:10px; border:1px solid #ccc; border-radius:8px; font-size:1rem;">
								</div>
							</div>
							<div class="form-row" style="display:flex; gap:20px; margin-bottom:15px;">
								<div class="form-group" style="flex:1;">
									<label for="modal_og_description_ca" style="display:block; margin-bottom:5px; font-weight:500; color:#333;">OG Description (CA)</label>
									<textarea name="og_description_ca" id="modal_og_description_ca" style="width:100%; padding:10px; border:1px solid #ccc; border-radius:8px; font-size:1rem; min-height:60px;"></textarea>
								</div>
								<div class="form-group" style="flex:1;">
									<label for="modal_og_description_es" style="display:block; margin-bottom:5px; font-weight:500; color:#333;">OG Description (ES)</label>
									<textarea name="og_description_es" id="modal_og_description_es" style="width:100%; padding:10px; border:1px solid #ccc; border-radius:8px; font-size:1rem; min-height:60px;"></textarea>
								</div>
							</div>
							<div class="form-group" style="margin-bottom:15px;">
								<label for="modal_og_image" style="display:block; margin-bottom:5px; font-weight:500; color:#333;">OG Image</label>
								<input type="text" name="og_image" id="modal_og_image" style="width:100%; padding:10px; border:1px solid #ccc; border-radius:8px; font-size:1rem;">
							</div>
							<div class="form-row" style="display:flex; gap:20px; margin-bottom:15px;">
								<div class="form-group" style="flex:1;">
									<label for="modal_twitter_title_ca" style="display:block; margin-bottom:5px; font-weight:500; color:#333;">Twitter Title (CA)</label>
									<input type="text" name="twitter_title_ca" id="modal_twitter_title_ca" style="width:100%; padding:10px; border:1px solid #ccc; border-radius:8px; font-size:1rem;">
								</div>
								<div class="form-group" style="flex:1;">
									<label for="modal_twitter_title_es" style="display:block; margin-bottom:5px; font-weight:500; color:#333;">Twitter Title (ES)</label>
									<input type="text" name="twitter_title_es" id="modal_twitter_title_es" style="width:100%; padding:10px; border:1px solid #ccc; border-radius:8px; font-size:1rem;">
								</div>
							</div>
							<div class="form-row" style="display:flex; gap:20px; margin-bottom:15px;">
								<div class="form-group" style="flex:1;">
									<label for="modal_twitter_description_ca" style="display:block; margin-bottom:5px; font-weight:500; color:#333;">Twitter Description (CA)</label>
									<textarea name="twitter_description_ca" id="modal_twitter_description_ca" style="width:100%; padding:10px; border:1px solid #ccc; border-radius:8px; font-size:1rem; min-height:60px;"></textarea>
								</div>
								<div class="form-group" style="flex:1;">
									<label for="modal_twitter_description_es" style="display:block; margin-bottom:5px; font-weight:500; color:#333;">Twitter Description (ES)</label>
									<textarea name="twitter_description_es" id="modal_twitter_description_es" style="width:100%; padding:10px; border:1px solid #ccc; border-radius:8px; font-size:1rem; min-height:60px;"></textarea>
								</div>
							</div>
							<div class="form-row" style="display:flex; gap:20px; margin-bottom:15px;">
								<div class="form-group" style="flex:1;">
									<label for="modal_twitter_image" style="display:block; margin-bottom:5px; font-weight:500; color:#333;">Twitter Image</label>
									<input type="text" name="twitter_image" id="modal_twitter_image" style="width:100%; padding:10px; border:1px solid #ccc; border-radius:8px; font-size:1rem;">
								</div>
								<div class="form-group" style="flex:1;">
									<label for="modal_featured_image" style="display:block; margin-bottom:5px; font-weight:500; color:#333;">Featured Image</label>
									<input type="text" name="featured_image" id="modal_featured_image" style="width:100%; padding:10px; border:1px solid #ccc; border-radius:8px; font-size:1rem;">
								</div>
							</div>
							<div class="form-row" style="display:flex; gap:20px;">
								<div class="form-group" style="flex:1;">
									<label for="modal_alt_image_ca" style="display:block; margin-bottom:5px; font-weight:500; color:#333;">Alt Image (CA)</label>
									<input type="text" name="alt_image_ca" id="modal_alt_image_ca" style="width:100%; padding:10px; border:1px solid #ccc; border-radius:8px; font-size:1rem;">
								</div>
								<div class="form-group" style="flex:1;">
									<label for="modal_alt_image_es" style="display:block; margin-bottom:5px; font-weight:500; color:#333;">Alt Image (ES)</label>
									<input type="text" name="alt_image_es" id="modal_alt_image_es" style="width:100%; padding:10px; border:1px solid #ccc; border-radius:8px; font-size:1rem;">
								</div>
							</div>
						</fieldset>

						<div class="modal-footer" style="padding:20px 0 0; border-top:1px solid #e0e0e0; display:flex; justify-content:flex-end; gap:15px;">
							<button type="button" onclick="closeModal()" class="btn btn-secondary" style="padding:12px 24px; font-size:1rem; border-radius:8px;">Cancel·lar</button>
							<button type="submit" class="btn btn-primary" style="padding:12px 24px; font-size:1rem; border-radius:8px;">Desar</button>
						</div>
					</form>
				</div>
			</div>
		</div>

		<style>
			.modal {
				position: fixed;
				z-index: 9999;
				left: 0;
				top: 0;
				width: 100vw;
				height: 100vh;
				background: rgba(0,0,0,0.25);
				display: flex;
				align-items: center;
				justify-content: center;
			}
			.seo-modal-content {
				background: #fff;
				border-radius: 16px;
				box-shadow: 0 8px 32px rgba(0,0,0,0.18);
				padding: 32px 28px 24px 28px;
				max-width: 680px;
				width: 95vw;
				max-height: 90vh;
				overflow-y: auto;
				position: relative;
			}
			/* SEO modal form vertical style */
			.seo-form {
				display: flex;
				flex-direction: column;
				gap: 18px;
			}
			.seo-form .form-group {
				display: flex;
				flex-direction: column;
				margin-bottom: 0;
			}
			.seo-form label {
				font-weight: 600;
				color: #474742;
				margin-bottom: 5px;
				font-size: 0.98rem;
			}
			.seo-form input,
			.seo-form select,
			.seo-form textarea {
				padding: 8px 10px;
				border-radius: 6px;
				border: 1px solid #eceae2;
				font-size: 0.97rem;
				font-family: 'Libre Baskerville', serif;
				background: #faf9f6;
				resize: vertical;
			}
			.seo-form textarea {
				min-height: 38px;
				max-height: 120px;
			}
			.seo-modal-content .close {
				position: absolute;
				top: 18px;
				right: 22px;
				font-size: 1.7rem;
				color: #aa9e6b;
				cursor: pointer;
				font-weight: bold;
				background: none;
				border: none;
			}
			@media (max-width: 700px) {
				.seo-modal-content {
					padding: 18px 6vw 18px 6vw;
					max-width: 98vw;
				}
				.seo-form-grid {
					grid-template-columns: 1fr;
					gap: 14px 0;
				}
			}
			</style>

		<style>
		@keyframes fadeIn {
			from { opacity: 0; }
			to { opacity: 1; }
		}
		@keyframes slideIn {
			from { transform:translate(-50%, calc(-50% - 50px)); opacity:0; }
			to { transform:translate(-50%, -50%); opacity:1; }
		}
		</style>

		<!-- Modal Eliminar SEO On Page -->
		<div id="deleteModal" class="modal" style="display:none;">
			<div class="modal-content">
				<span class="close" onclick="closeDeleteModal()">&times;</span>
				<form id="deleteForm" method="POST" action="gseoonpage.php">
					<input type="hidden" name="action" value="delete_onpage">
					<input type="hidden" name="id_pagina" id="delete_id_pagina">
					<p>Segur que vols eliminar aquesta pàgina SEO?</p>
					<button type="submit" class="btn btn-small btn-danger">Elimina</button>
					<button type="button" class="btn btn-small btn-secondary" onclick="closeDeleteModal()">Cancel·la</button>
				</form>
			</div>
		</div>

		<script>
		// Delete modal handlers
		function openDeleteModal(id) {
			document.getElementById('deleteModal').style.display = 'block';
			document.getElementById('delete_id_pagina').value = id;
		}
		function closeDeleteModal() {
			document.getElementById('deleteModal').style.display = 'none';
		}

		// Open modal for new page
		function openNewModal() {
			setDefaults();
			var overlay = document.getElementById('seoOnPageModal');
			console.log('openNewModal called, overlay:', overlay);
			if (overlay) overlay.style.display = 'block';
			// small timeout to allow CSS animation and then focus
			setTimeout(function(){
				var field = document.getElementById('modal_url_relativa_ca');
				if(field) field.focus();
			}, 60);
		}

		// Open modal for editing an existing page. 'data' is a JSON string.
		function openEditModal(id, data) {
			try {
				var parsed = typeof data === 'string' ? JSON.parse(data) : data;
				populateFields(parsed);
				document.getElementById('seoOnPageModal').style.display = 'block';
				setTimeout(function(){
					var field = document.getElementById('modal_title_ca');
					if(field) field.focus();
				}, 60);
			} catch (e) {
				console.error('openEditModal parse error', e);
				// fallback: just open empty modal
				openNewModal();
			}
		}

		// Close overlay modal
		function closeModal() {
			document.getElementById('seoOnPageModal').style.display = 'none';
		}

		// Populate defaults for new record
		function setDefaults() {
			var safe = function(id, v){ var el = document.getElementById(id); if(el) el.value = v; };
			safe('modal_id_pagina','');
			safe('modal_url_relativa_ca','');
			safe('modal_url_relativa_es','');
			safe('modal_titulo_pagina','');
			safe('modal_tipo_pagina','landing');
			safe('modal_activa','1');
			safe('modal_fecha_publicacion', new Date().toISOString().split('T')[0]);
			safe('modal_title_ca','');
			safe('modal_meta_description_ca','');
			safe('modal_h1_ca','');
			safe('modal_contenido_principal_ca','');
			safe('modal_slug_ca','');
			safe('modal_focus_keyword_ca','');
			safe('modal_title_es','');
			safe('modal_meta_description_es','');
			safe('modal_h1_es','');
			safe('modal_contenido_principal_es','');
			safe('modal_slug_es','');
			safe('modal_focus_keyword_es','');
			safe('modal_meta_robots','index, follow');
			safe('modal_canonical_url_ca','');
			safe('modal_canonical_url_es','');
			safe('modal_priority','0.8');
			safe('modal_changefreq','monthly');
			safe('modal_keywords_secundarias_ca','');
			safe('modal_keywords_secundarias_es','');
			safe('modal_og_title_ca','');
			safe('modal_og_title_es','');
			safe('modal_og_description_ca','');
			safe('modal_og_description_es','');
			safe('modal_og_image','');
			safe('modal_twitter_title_ca','');
			safe('modal_twitter_title_es','');
			safe('modal_twitter_description_ca','');
			safe('modal_twitter_description_es','');
			safe('modal_twitter_image','');
			safe('modal_featured_image','');
			safe('modal_alt_image_ca','');
			safe('modal_alt_image_es','');
		}

		// Fill fields from object
		function populateFields(data) {
			var safe = function(id, v){ var el = document.getElementById(id); if(el) el.value = (v !== undefined && v !== null) ? v : ''; };
			safe('modal_id_pagina', data.id_pagina || '');
			safe('modal_url_relativa_ca', data.url_relativa_ca || '');
			safe('modal_url_relativa_es', data.url_relativa_es || '');
			safe('modal_titulo_pagina', data.titulo_pagina || '');
			safe('modal_tipo_pagina', data.tipo_pagina || 'landing');
			safe('modal_activa', data.activa ? '1' : '0');
			safe('modal_fecha_publicacion', data.fecha_publicacion || '');
			safe('modal_title_ca', data.title_ca || '');
			safe('modal_meta_description_ca', data.meta_description_ca || '');
			safe('modal_h1_ca', data.h1_ca || '');
			safe('modal_contenido_principal_ca', data.contenido_principal_ca || '');
			safe('modal_slug_ca', data.slug_ca || '');
			safe('modal_focus_keyword_ca', data.focus_keyword_ca || '');
			safe('modal_title_es', data.title_es || '');
			safe('modal_meta_description_es', data.meta_description_es || '');
			safe('modal_h1_es', data.h1_es || '');
			safe('modal_contenido_principal_es', data.contenido_principal_es || '');
			safe('modal_slug_es', data.slug_es || '');
			safe('modal_focus_keyword_es', data.focus_keyword_es || '');
			safe('modal_meta_robots', data.meta_robots || 'index, follow');
			safe('modal_canonical_url_ca', data.canonical_url_ca || '');
			safe('modal_canonical_url_es', data.canonical_url_es || '');
			safe('modal_priority', data.priority || '0.8');
			safe('modal_changefreq', data.changefreq || 'monthly');
			safe('modal_keywords_secundarias_ca', data.keywords_secundarias_ca || '');
			safe('modal_keywords_secundarias_es', data.keywords_secundarias_es || '');
			safe('modal_og_title_ca', data.og_title_ca || '');
			safe('modal_og_title_es', data.og_title_es || '');
			safe('modal_og_description_ca', data.og_description_ca || '');
			safe('modal_og_description_es', data.og_description_es || '');
			safe('modal_og_image', data.og_image || '');
			safe('modal_twitter_title_ca', data.twitter_title_ca || '');
			safe('modal_twitter_title_es', data.twitter_title_es || '');
			safe('modal_twitter_description_ca', data.twitter_description_ca || '');
			safe('modal_twitter_description_es', data.twitter_description_es || '');
			safe('modal_twitter_image', data.twitter_image || '');
			safe('modal_featured_image', data.featured_image || '');
			safe('modal_alt_image_ca', data.alt_image_ca || '');
			safe('modal_alt_image_es', data.alt_image_es || '');
		}

		// Close modal with Escape key
		document.addEventListener('keydown', function(e) {
			if (e.key === 'Escape') {
				var overlay = document.getElementById('seoOnPageModal');
				if (overlay && overlay.style.display === 'block') closeModal();
			}
		});

		// Global error handler for easier debugging in the admin
		window.addEventListener('error', function(e){
			console.error('Global error:', e.message, 'at', e.filename + ':' + e.lineno);
		});

		</script>
