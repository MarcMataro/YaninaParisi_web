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
	<title>FAQ — Documentación del Panel</title>
	<link rel="stylesheet" href="../css/dashboard.css">
	<link rel="stylesheet" href="../css/configuracion.css">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Libre+Baskerville:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet">
	<style>
		html, body { font-family: 'Libre Baskerville', serif; font-size:16px; }
		.docs-hero { padding: 26px 22px; }
		.docs-hero h1 { margin:0 0 8px 0; font-size:1.6rem; }
		.docs-grid { display:flex; gap:24px; align-items:flex-start; }
		.docs-index { flex:0 0 300px; max-width:300px; }
		.docs-index ul { list-style:none; padding-left:0; }
		.docs-index a { display:block; padding:8px 10px; border-radius:8px; color:var(--color-dark); text-decoration:none; }
		.docs-index a:hover { background: rgba(var(--color-light),0.18); color:var(--color-accent); }
		.doc-body { flex:1 1 auto; }
		.doc-body h2 { font-size:1.15rem; margin-top:18px; }
		.doc-body p, .doc-body li { color:#333; font-size:1rem; line-height:1.7; }
		code { background:#f5f5f5; padding:2px 6px; border-radius:6px; }
		.faq-q { font-weight:700; margin-top:12px }
		.faq-a { margin:6px 0 14px 0 }
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
					<p class="date-today">Preguntas frecuentes (FAQ)</p>
				</div>
			</div>
		</header>

		<main class="dashboard-container">
			<section class="docs-hero card">
				<h1>Preguntas frecuentes (FAQ)</h1>
				<p class="lead">Respuestas claras y prácticas a las dudas más habituales sobre el uso del panel de control.</p>
				<p class="small-muted">Si no encuentras la respuesta aquí, apunta el problema y contacta con el responsable técnico o con el administrador del sistema.</p>
			</section>

			<div class="docs-grid" style="margin-top:18px">
				<nav class="docs-index card">
					<h3 style="margin:8px 10px">Índice</h3>
					<ul>
						<li><a href="#login">Acceso y autenticación</a></li>
						<li><a href="#usuarios">Usuarios y permisos</a></li>
						<li><a href="#agenda">Agenda / Sesiones</a></li>
						<li><a href="#pacientes">Pacientes</a></li>
						<li><a href="#facturacion">Facturación y pagos</a></li>
						<li><a href="#media">Gestor de medios</a></li>
						<li><a href="#blog">Blog y contenidos</a></li>
						<li><a href="#seo">SEO</a></li>
						<li><a href="#seguridad">Seguridad y backups</a></li>
						<li><a href="#errores">Errores comunes</a></li>
						<li><a href="#soporte">Contacto y soporte</a></li>
					</ul>
				</nav>

				<article class="doc-body card">
					<section id="login">
						<h2>Acceso y autenticación</h2>
						<p class="faq-q">¿Qué hago si no puedo entrar al panel?</p>
						<p class="faq-a">Comprueba que usas el usuario y contraseña correctos. Si olvidaste la contraseña, solicita el restablecimiento al administrador (no hay recuperación automática desde la UI en la versión actual). Asegúrate de que la URL es la correcta y que tu navegador acepta cookies. Si recibes un error de base de datos, avisa al técnico indicando el mensaje exacto.</p>

						<p class="faq-q">¿Por qué me redirige a la página de login automáticamente?</p>
						<p class="faq-a">La sesión puede haber caducado o el navegador ha borrado las cookies. Inicia sesión de nuevo. Si ocurre frecuentemente, revisa la configuración de sesión del servidor o del proxy que use el hosting.</p>
					</section>

					<section id="usuarios">
						<h2>Usuarios y permisos</h2>
						<p class="faq-q">¿Cómo añado un nuevo usuario al panel?</p>
						<p class="faq-a">Accede a la sección de configuración/usuarios (si tienes permisos de administrador). Rellena los datos y asigna el rol adecuado. Si no tienes opción visible, contacta con el administrador para que cree la cuenta por ti.</p>

						<p class="faq-q">¿Qué permisos existen y qué puedo hacer con cada uno?</p>
						<p class="faq-a">Normalmente hay roles de administrador y usuario. El administrador puede crear usuarios, cambiar tarifas y acceder a todas las secciones. Los usuarios con permisos limitados pueden gestionar citas, pacientes o facturación según lo configurado. Si necesitas un permiso específico, solicita al administrador que lo active para tu cuenta.</p>
					</section>

					<section id="agenda">
						<h2>Agenda / Sesiones</h2>
						<p class="faq-q">¿Cómo reprogramo una sesión?</p>
						<p class="faq-a">Abre la sesión desde la agenda, pulsa editar y modifica la fecha/hora. Guarda los cambios. Si la sesión está asociada a facturación, revisa que la nueva fecha siga cumpliendo políticas internas.</p>

						<p class="faq-q">¿Puedo enviar recordatorios automáticos?</p>
						<p class="faq-a">En la versión actual, los recordatorios se gestionan fuera del panel o mediante integraciones específicas. Consulta con el administrador si se ha previsto alguna integración (SMS/Email) en el sistema.</p>
					</section>

					<section id="pacientes">
						<h2>Pacientes</h2>
						<p class="faq-q">¿Cómo debo introducir datos sensibles?</p>
						<p class="faq-a">Registra solo la información estrictamente necesaria y evita introducir datos que no sean relevantes para la gestión de la consulta. Respeta la normativa de protección de datos vigente. Usa campos de notas privadas para información clínica limitada y asegúrate de que el acceso está restringido a personal autorizado.</p>

						<p class="faq-q">No encuentro un paciente: ¿qué hago?</p>
						<p class="faq-a">Limpia los filtros y búsquedas, prueba con variaciones del nombre o apellidos, y comprueba que no haya errores tipográficos. Si aún así no aparece, solicita al administrador que revise la base de datos (posible problema de sincronización).</p>
					</section>

					<section id="facturacion">
						<h2>Facturación y pagos</h2>
						<p class="faq-q">¿Cómo registro un pago?</p>
						<p class="faq-a">Accede a Facturación, pulsa 'Nuevo Pago' o 'Generar Factura' según el flujo. Rellena los datos del paciente, la sesión asociada (si corresponde) y el importe. Guarda para registrar el pago. Consulta la guía de facturación para pasos detallados.</p>

						<p class="faq-q">¿Se pueden anular facturas?</p>
						<p class="faq-a">Sí, existe la opción de anular. Busca la factura y usa la acción 'Anular'. El sistema registrará la anulación. Si necesitas eliminar definitivamente una entrada, consulta con administración por motivos de auditoría.</p>
					</section>

					<section id="media">
						<h2>Gestor de medios</h2>
						<p class="faq-q">¿Qué tipos de archivo están permitidos?</p>
						<p class="faq-a">Imágenes (jpg, png, gif) y vídeos en los formatos permitidos por el sistema. Hay límites de tamaño (imágenes ≈ 12 MB, vídeos ≈ 300 MB). Si necesitas otro formato, consulta al administrador.</p>

						<p class="faq-q">La imagen no aparece en la entrada, ¿qué compruebo?</p>
						<p class="faq-a">Verifica que la imagen se subió correctamente al gestor de medios, que la URL es accesible y que no hay errores de permisos. Prueba a abrir la URL directamente en el navegador.</p>
					</section>

					<section id="blog">
						<h2>Blog y contenidos</h2>
						<p class="faq-q">¿Cómo programo una entrada para que se publique en una fecha determinada?</p>
						<p class="faq-a">Al crear/editar la entrada, en 'Configuración General' selecciona el estado 'Programado' y fija la 'Fecha de Publicación'. Guarda; la entrada se publicará automáticamente en esa fecha si el servidor está correctamente configurado.</p>

						<p class="faq-q">¿Puedo insertar imágenes desde el gestor de medios?</p>
						<p class="faq-a">Sí. El editor TinyMCE está configurado para abrir el selector de medios. Selecciona la imagen y se insertará con su URL y atributos. Asegúrate de definir el texto 'alt' para accesibilidad y SEO.</p>
					</section>

					<section id="seo">
						<h2>SEO</h2>
						<p class="faq-q">¿Qué campos debo completar para mejorar el SEO?</p>
						<p class="faq-a">Rellena Meta título, Meta descripción, slug amigable y texto ALT de la imagen de portada. Además, aplica la checklist SEO que encontrarás en la guía del Blog (título, H1, primeros párrafos, subtítulos y enlaces internos).</p>
					</section>

					<section id="seguridad">
						<h2>Seguridad y backups</h2>
						<p class="faq-q">¿Se realizan copias de seguridad automáticamente?</p>
						<p class="faq-a">Depende de la configuración del hosting. Consulta con el administrador técnico para confirmar la política de backups. Es recomendable mantener copias regulares y exportaciones puntuales antes de cambios críticos.</p>

						<p class="faq-q">¿Qué hago si encuentro un fallo de seguridad?</p>
						<p class="faq-a">No intentes solucionarlo por tu cuenta. Anota los pasos para reproducir el fallo, captura pantalla si es posible y notifica inmediatamente al administrador con prioridad alta.</p>
					</section>

					<section id="errores">
						<h2>Errores comunes</h2>
						<p class="faq-q">Pantalla blanca o 500 Internal Server Error</p>
						<p class="faq-a">Puede ser un error de PHP o de la base de datos. Revisa los logs del servidor (si tienes acceso) o informa al técnico con la hora y la acción que estabas realizando.</p>

						<p class="faq-q">Errores AJAX (acciones sin respuesta)</p>
						<p class="faq-a">Comprueba la consola del navegador para ver el error o la respuesta JSON; a menudo indica falta de autenticación o un error en la consulta. Si no tienes permiso para ver la consola, copia el mensaje devuelto (si aparece) y pásalo al equipo técnico.</p>
					</section>

					<section id="soporte">
						<h2>Contacto y soporte</h2>
						<p class="faq-q">¿A quién contacto para soporte?</p>
						<p class="faq-a">Contacta con la persona responsable del mantenimiento o con el administrador del sistema. Proporciona una descripción clara, pasos para reproducir y capturas. Si el problema afecta a facturación o datos personales, avisa con prioridad.</p>

						<p class="faq-q">¿Qué información debo adjuntar al reportar un problema?</p>
						<p class="faq-a">Incluye: usuario con el que trabajabas, hora aproximada, acción realizada (ej. crear pago), URL de la página, y capturas o mensajes de error. Esto acelera la resolución.</p>
					</section>
				</article>
			</div>
		</main>
	</div>

	<script>
		// Toggle sidebar
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
