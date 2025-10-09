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
	<style>
		/* Ajustes locales para la página de documentación */
		.docs-hero { padding: 26px 22px; }
		.docs-hero h1 { margin:0 0 8px 0; font-family: 'Libre Baskerville', serif; font-size:1.6rem; }
		.docs-hero p.lead { color:var(--color-dark); margin-bottom:6px; font-style:normal; }
		.docs-grid { display:flex; gap:24px; align-items:flex-start; }
		.docs-index { flex:0 0 300px; max-width:300px; }
		.docs-index ul { list-style:none; padding-left:0; }
		.docs-index a { display:block; padding:8px 10px; border-radius:8px; color:var(--color-dark); text-decoration:none; }
		.docs-index a:hover { background: rgba(var(--color-light),0.18); color:var(--color-accent); }
		.doc-body { flex:1 1 auto; }
		.doc-body h2 { font-size:1.15rem; margin-top:18px; }
		.doc-body p, .doc-body li { color:#333; font-size:0.98rem; line-height:1.7; }
		code { background:#f5f5f5; padding:2px 6px; border-radius:6px; }
		@media (max-width:900px) { .docs-grid { flex-direction:column; } .docs-index { max-width:none; } }
	</style>
</head>
<body>
	<?php include __DIR__ . '/../includes/sidebar.php'; ?>

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
				<h1>Introducción</h1>
				<p class="lead">Bienvenida al manual de uso del panel. Aquí encontrarás explicaciones prácticas y ordenadas para sacarle el máximo partido a las funcionalidades del sistema.</p>
				<p class="small-muted">Esta documentación está pensada para personas responsables de la gestión diaria de la consulta (turnos, pacientes, facturación y contenidos). No es necesario ser desarrolladora para seguirla.</p>
			</section>

			<div class="docs-grid" style="margin-top:18px">
				<nav class="docs-index card">
					<h3 style="margin:8px 10px">Índice</h3>
					<ul>
						<li><a href="#resumen">Resumen rápido</a></li>
						<li><a href="#secciones">Secciones del panel</a></li>
						<li><a href="#principios">Principios de uso</a></li>
						<li><a href="#consejos">Consejos prácticos</a></li>
						<li><a href="#seguridad">Seguridad y buenas prácticas</a></li>
						<li><a href="#ayuda">Dónde encontrar ayuda</a></li>
					</ul>
				</nav>

				<article class="doc-body card">
					<section id="resumen">
						<h2>Resumen rápido</h2>
						<p>El panel te permite administrar los elementos esenciales de la consulta: la agenda de sesiones, las fichas de pacientes, la facturación, y algunos aspectos relacionados con la presencia online (SEO). La interfaz está pensada para minimizar clics y mostrar la información más importante de un vistazo.</p>
					</section>

					<section id="secciones">
						<h2>Secciones del panel</h2>
						<p>A continuación se describen las áreas principales y su finalidad:</p>
						<ul>
							<li><strong>Dashboard:</strong> Resumen con tarjetas e indicadores que muestran actividad reciente y citas próximas.</li>
							<li><strong>Agenda / Sesiones:</strong> Crear, reprogramar y cancelar citas; ver el calendario por día, semana o mes.</li>
							<li><strong>Pacientes:</strong> Fichas con datos de contacto, historial de citas y notas clínicas (según la configuración de privacidad).</li>
							<li><strong>SEO (gSEO):</strong> Indicadores básicos de visibilidad y recomendaciones sencillas para mejorar el posicionamiento del sitio.</li>
							<li><strong>Facturación:</strong> Emisión y registro de facturas y recibos; gestión de pagos.</li>
							<li><strong>Configuración:</strong> Gestión de usuarios, tarifas y opciones del sistema.</li>
						</ul>
					</section>

					<section id="principios">
						<h2>Principios de uso</h2>
						<p>Para trabajar de forma eficaz y segura con el panel sigue estas recomendaciones:</p>
						<ol>
							<li><strong>Focaliza en la tarea:</strong> cada pantalla está optimizada para una función concreta (por ejemplo, la agenda para citas). Si necesitas datos combinados, revisa el Dashboard o exporta la información.</li>
							<li><strong>Guarda con frecuencia:</strong> pulsa <em>Guardar</em> tras editar formularios. Algunas áreas guardan cambios por separado para evitar conflictos.</li>
							<li><strong>Comprueba permisos:</strong> ciertas acciones requieren permisos de administrador (crear usuarios, cambiar tarifas). Si no ves una opción, es posible que no tengas permiso para usarla.</li>
						</ol>
					</section>

					<section id="consejos">
						<h2>Consejos prácticos</h2>
						<ul>
							<li>Utiliza la búsqueda de pacientes para acceder rápidamente a su historial antes de crear una nueva cita.</li>
							<li>Usa la vista semanal de la agenda para planificar sesiones y detectar huecos disponibles.</li>
							<li>Antes de realizar cambios masivos (exportaciones, importaciones, ajustes de tarifas), realiza una copia de seguridad de la base de datos.</li>
							<li>Si necesitas generar facturas periódicas, revisa las opciones en Facturación para automatizar el proceso.</li>
						</ul>
					</section>

					<section id="seguridad">
						<h2>Seguridad y buenas prácticas</h2>
						<p>La protección de datos es prioritaria. Algunas pautas clave:</p>
						<ul>
							<li>No compartas tus credenciales. Si alguien debe acceder, crea una cuenta con permisos limitados.</li>
							<li>Cambia la contraseña periódicamente y utiliza contraseñas robustas.</li>
							<li>En producción, asegúrate de servir el panel por HTTPS para proteger las sesiones.</li>
							<li>Realiza copias de seguridad regulares y guarda logs de actividad si el sistema lo permite.</li>
						</ul>
					</section>

					<section id="ayuda">
						<h2>Dónde encontrar ayuda</h2>
						<p>Si tienes dudas o encuentras problemas:</p>
						<ul>
							<li>Consulta esta documentación para la sección correspondiente.</li>
							<li>Contacta con el administrador del sistema o la persona responsable del mantenimiento.</li>
							<li>Si detectas un fallo crítico, describe el problema (pasos para reproducirlo, usuario afectado, capturas si es posible) y envíalo al equipo de soporte.</li>
						</ul>
					</section>

					<footer style="margin-top:18px; color:#666; font-size:0.95rem">
						<p>¿Quieres que convierta esta sección a PDF o que añada un editor en el panel para modificarla de forma directa? Puedo añadir ambas opciones si lo prefieres.</p>
					</footer>
				</article>
			</div>
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
</body>
</html>

