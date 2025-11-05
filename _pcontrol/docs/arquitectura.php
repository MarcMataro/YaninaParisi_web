<?php
session_start();

// Comprobar sesión y redirigir si no está autenticada
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
	header('Location: ../index.php');
	exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width,initial-scale=1">
	<title>Introducción — Documentación del Panel</title>
	<link rel="stylesheet" href="../css/dashboard.css">
	<link rel="stylesheet" href="../css/configuracion.css">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
	<!-- Load same webfont as dashboard for consistent typography -->
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Libre+Baskerville:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet">
	<style>
		/* Make docs typography match dashboard */
		html, body { font-family: 'Libre Baskerville', serif; font-size:16px; }
		/* Ajustes locales para la página de documentación */
		.docs-hero { padding: 26px 22px; }
		.docs-hero h1 { margin:0 0 8px 0; font-size:1.6rem; }
		.docs-hero p.lead { color:var(--color-dark); margin-bottom:6px; font-style:normal; }
		.docs-grid { display:flex; gap:24px; align-items:flex-start; }
		.docs-index { flex:0 0 300px; max-width:300px; }
		.docs-index ul { list-style:none; padding-left:0; }
		.docs-index a { display:block; padding:8px 10px; border-radius:8px; color:var(--color-dark); text-decoration:none; }
		.docs-index a:hover { background: rgba(var(--color-light),0.18); color:var(--color-accent); }
		.doc-body { flex:1 1 auto; }
		.doc-body h2 { font-size:1.15rem; margin-top:18px; }
		.doc-body p, .doc-body li { color:#333; font-size:1rem; line-height:1.7; }
		code { background:#f5f5f5; padding:2px 6px; border-radius:6px; }
		@media (max-width:900px) { .docs-grid { flex-direction:column; } .docs-index { max-width:none; } }
	</style>
</head>
<body>
	<link rel="icon" type="image/png" sizes="32x32" href="../../img/Logo32.png">
	<link rel="icon" type="image/png" sizes="16x16" href="../../img/Logo16.png">
	<?php include 'sidebar.php'; ?>

	<div class="main-content">
		<header class="top-bar">
			<div class="top-bar-left">
				<button class="menu-toggle" id="menuToggle"><i class="fas fa-bars"></i></button>
				<div class="top-bar-info">
					<h1>Documentación interna</h1>
					<p class="date-today">Guía de uso del panel de control</p>
				</div>
			</div>
		</header>

		<main class="dashboard-container">
			<section class="docs-hero card">
				<h1>Arquitectura web a nivel de SEO</h1>
				<p class="lead">En esta página se muestra la arquitectura web a nivel de SEO.</p>
				<p class="small-muted">Esta documentación está pensada para personas responsables de la gestión diaria de la consulta (turnos, pacientes, facturación y contenidos). No es necesario ser desarrolladora para seguirla.</p>
			    <br>
                <p class="small-muted">La arquitectura web a nivel de SEO se refiere a la estructura y organización de un sitio web con el objetivo de mejorar su visibilidad y posicionamiento en los motores de búsqueda. A continuación se presentan los aspectos clave a considerar:</p>
                <ul>
                    <li><strong>Estructura de URL:</strong> Las URL deben ser limpias, descriptivas y contener palabras clave relevantes.</li>
                    <li><strong>Navegación:</strong> La navegación debe ser intuitiva y permitir a los usuarios encontrar fácilmente la información que buscan.</li>
                    <li><strong>Contenido:</strong> El contenido debe ser de alta calidad, relevante y estar optimizado para palabras clave específicas.</li>
                    <li><strong>Etiquetas HTML:</strong> Utilizar etiquetas HTML adecuadas (como <code>&lt;title&gt;</code>, <code>&lt;meta&gt;</code>, <code>&lt;h1&gt;</code>, etc.) para ayudar a los motores de búsqueda a entender la estructura del contenido.</li>
                    <li><strong>Enlaces internos:</strong> Incluir enlaces internos que conecten diferentes secciones del sitio web y faciliten la navegación.</li>
                </ul>
            </section>
            <!-- Contenido de la documentación
            Se mostrará de forma gráfica la arquitectura web a nivel de SEO, con las diferentes
            secciones y páginas del sitio web, y cómo están interconectadas entre sí. -->
            <?php
            // Attempt to load the DOT diagram from the repository diagrams folder
            $dot_path = __DIR__ . '/../../diagrams/site-architecture.dot';
            $dot_content = null;
            if (file_exists($dot_path) && is_readable($dot_path)) {
                $dot_content = file_get_contents($dot_path);
            }
            ?>

            <section class="docs-architecture card" style="margin-top:18px;">
                <h2>Diagrama d'arquitectura (visual)</h2>
                <p class="small-muted">Si el navegador suporta renderitzat client-side, el gràfic s'hi dibuixarà automàticament. També tens disponible el codi DOT.</p>
                <div id="graphContainer" style="border:1px solid #e6e6e6;border-radius:8px;padding:12px;min-height:300px;background:#fff;overflow:auto;">
                    <div id="graph">Càrrega del gràfic...</div>
                </div>
                <div style="margin-top:10px;display:flex;gap:10px;align-items:center;">
                    <button id="downloadSvg" class="btn">Descarregar SVG</button>
                    <button id="showDot" class="btn">Mostrar DOT</button>
                    <span style="color:#666;font-size:0.95rem;">(Si el gràfic no es veu, comprova que <code>diagrams/site-architecture.dot</code> existeix al repo)</span>
                </div>
                <pre id="dotRaw" style="display:none;margin-top:12px;background:#f7f7f7;padding:12px;border-radius:8px;white-space:pre-wrap;overflow:auto;"><?php echo $dot_content !== null ? htmlspecialchars($dot_content) : "(No s'ha trobat el fitxer diagrams/site-architecture.dot)"; ?></pre>
            </section>

		</main>
	</div>

	<script>
		// Toggle sidebar (coherente amb la resta del panell)
		document.getElementById('menuToggle')?.addEventListener('click', function(){
			document.querySelector('.sidebar')?.classList.toggle('active');
		});
		// Anclas suaves
		document.querySelectorAll('.docs-index a').forEach(a => a.addEventListener('click', function(e){
			e.preventDefault(); const target = document.querySelector(this.getAttribute('href')); if(target) target.scrollIntoView({behavior:'smooth', block:'start'});
		}));
	</script>

	<!-- Viz.js (Graphviz compiled to WASM) from CDN to render DOT client-side -->
	<script src="https://cdnjs.cloudflare.com/ajax/libs/viz.js/2.1.2/viz.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/viz.js/2.1.2/full.render.js"></script>
	<script>
	(function(){
		const dotEl = document.getElementById('dotRaw');
		const graphEl = document.getElementById('graph');
		const downloadBtn = document.getElementById('downloadSvg');
		const showDotBtn = document.getElementById('showDot');
		const dot = dotEl ? dotEl.textContent.trim() : '';
		if (!dot) {
			if (graphEl) graphEl.textContent = 'No hi ha cap fitxer DOT disponible per renderitzar.';
			return;
		}
		if (typeof Viz === 'undefined') {
			if (graphEl) graphEl.textContent = "No s'ha pogut carregar Viz.js des del CDN.";
			return;
		}
		const viz = new Viz();
		viz.renderSVGElement(dot)
		.then(function(element){
			if (graphEl) { graphEl.innerHTML = ''; graphEl.appendChild(element); }
			if (downloadBtn) {
				downloadBtn.addEventListener('click', function(){
					const svg = element.outerHTML;
					const blob = new Blob([svg], {type: 'image/svg+xml;charset=utf-8'});
					const url = URL.createObjectURL(blob);
					const a = document.createElement('a'); a.href = url; a.download = 'site-architecture.svg'; document.body.appendChild(a); a.click(); a.remove(); URL.revokeObjectURL(url);
				});
			}
		})
		.catch(function(err){
			console.error('Viz error', err);
			if (graphEl) graphEl.textContent = 'Error al renderitzar el gràfic. Revisa la consola del navegador.';
		});

		if (showDotBtn) showDotBtn.addEventListener('click', function(){
			const pre = document.getElementById('dotRaw');
			if (!pre) return;
			pre.style.display = pre.style.display === 'none' ? 'block' : 'none';
		});
	})();
	</script>
</body>
</html>