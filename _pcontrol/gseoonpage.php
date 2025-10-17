<?php
session_start();
// Verificar autenticació
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
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
				'canonical_url' => $_POST['canonical_url'] ?? null,
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
	foreach ($rows as $row) {
		$paginas_onpage[] = new SEO_OnPage($row['id_pagina']);
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
<?php include 'includes/sidebar.php'; ?>
<div class="main-content">
	<header class="top-bar">
		<div class="top-bar-left">
			<h1><i class="fas fa-file-alt"></i> Gestió SEO On Page</h1>
		</div>
	</header>
	<div class="content-wrapper" style="margin-top:32px;">
		<div class="onpage-table-container">
				<div class="onpage-table-header">
						<h2><i class="fas fa-list"></i> Pàgines SEO On Page</h2>
						<button class="btn btn-primary" onclick="openEditModal('new')"><i class="fas fa-plus"></i> Nova pàgina</button>
				</div>
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
													 <button class="btn btn-sm btn-secondary" onclick="openEditModal(<?php echo $pagina->getIdPagina(); ?>, '<?php echo addslashes(json_encode([
															 'id_pagina' => $pagina->getIdPagina(),
															 'url_relativa_ca' => $pagina->getUrlRelativaCa(),
															 'url_relativa_es' => $pagina->getUrlRelativaEs(),
															 'title_ca' => $pagina->getTitle('ca'),
															 'title_es' => $pagina->getTitle('es'),
															 'activa' => $pagina->isActiva()
													 ])); ?>')"><i class="fas fa-edit"></i> Edita</button>
													 <button class="btn btn-sm btn-danger" onclick="openDeleteModal(<?php echo $pagina->getIdPagina(); ?>)"><i class="fas fa-trash"></i> Elimina</button>
											 </td>
									 </tr>
							 <?php endforeach; ?>
						</tbody>
				</table>
		</div>

		<!-- Modal Afegir/Editar SEO On Page -->
		<div id="seoModal" class="modal" style="display:none;">
			<div class="modal-content">
				<span class="close" onclick="closeModal()">&times;</span>
				<form id="seoForm" method="POST" action="gseoonpage.php">
					<input type="hidden" name="action" value="save_onpage">
					<input type="hidden" name="id_pagina" id="modal_id_pagina">
					<div class="form-group">
						<label>URL (CA)</label>
						<input type="text" name="url_relativa_ca" id="modal_url_relativa_ca" required>
					</div>
					<div class="form-group">
						<label>URL (ES)</label>
						<input type="text" name="url_relativa_es" id="modal_url_relativa_es" required>
					</div>
					<div class="form-group">
						<label>Títol (CA)</label>
						<input type="text" name="title_ca" id="modal_title_ca" required>
					</div>
					<div class="form-group">
						<label>Títol (ES)</label>
						<input type="text" name="title_es" id="modal_title_es" required>
					</div>
					<div class="form-group">
						<label>Activa</label>
						<select name="activa" id="modal_activa">
							<option value="1">Sí</option>
							<option value="0">No</option>
						</select>
					</div>
					<button type="submit" class="btn btn-success">Desa</button>
				</form>
			</div>
		</div>

		<!-- Modal Eliminar SEO On Page -->
		<div id="deleteModal" class="modal" style="display:none;">
			<div class="modal-content">
				<span class="close" onclick="closeDeleteModal()">&times;</span>
				<form id="deleteForm" method="POST" action="gseoonpage.php">
					<input type="hidden" name="action" value="delete_onpage">
					<input type="hidden" name="id_pagina" id="delete_id_pagina">
					<p>Segur que vols eliminar aquesta pàgina SEO?</p>
					<button type="submit" class="btn btn-danger">Elimina</button>
					<button type="button" class="btn btn-secondary" onclick="closeDeleteModal()">Cancel·la</button>
				</form>
			</div>
		</div>

		<script>
		function openEditModal(id, dataJson) {
			document.getElementById('seoModal').style.display = 'block';
			if (id === 'new') {
				document.getElementById('modal_id_pagina').value = '';
				document.getElementById('modal_url_relativa_ca').value = '';
				document.getElementById('modal_url_relativa_es').value = '';
				document.getElementById('modal_title_ca').value = '';
				document.getElementById('modal_title_es').value = '';
				document.getElementById('modal_activa').value = '1';
			} else if (dataJson) {
				var data = JSON.parse(dataJson);
				document.getElementById('modal_id_pagina').value = data.id_pagina;
				document.getElementById('modal_url_relativa_ca').value = data.url_relativa_ca;
				document.getElementById('modal_url_relativa_es').value = data.url_relativa_es;
				document.getElementById('modal_title_ca').value = data.title_ca;
				document.getElementById('modal_title_es').value = data.title_es;
				document.getElementById('modal_activa').value = data.activa ? '1' : '0';
			}
		}
		function closeModal() {
			document.getElementById('seoModal').style.display = 'none';
		}
		function openDeleteModal(id) {
			document.getElementById('deleteModal').style.display = 'block';
			document.getElementById('delete_id_pagina').value = id;
		}
		function closeDeleteModal() {
			document.getElementById('deleteModal').style.display = 'none';
		}

		</script>
