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
	<title>Pacientes — Documentación del Panel</title>
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
		.docs-hero p.lead { color:var(--color-dark); margin-bottom:6px; }
		.docs-grid { display:flex; gap:24px; align-items:flex-start; }
		.docs-index { flex:0 0 300px; max-width:300px; }
		.docs-index ul { list-style:none; padding-left:0; }
		.docs-index a { display:block; padding:8px 10px; border-radius:8px; color:var(--color-dark); text-decoration:none; }
		.docs-index a:hover { background: rgba(var(--color-light),0.18); color:var(--color-accent); }
		.doc-body { flex:1 1 auto; }
		.doc-body h2 { font-size:1.15rem; margin-top:18px; }
		.doc-body p, .doc-body li { color:#333; font-size:1rem; line-height:1.7; }
		code { background:#f5f5f5; padding:2px 6px; border-radius:6px; }
		pre.codeblock { background:#f7f7f7; padding:12px; border-radius:8px; overflow:auto; }
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
					<p class="date-today">Guía de la sección Pacientes</p>
				</div>
			</div>
		</header>

		<main class="dashboard-container">
			<section class="docs-hero card">
				<h1>Pacientes</h1>
				<p class="lead">Explicación detallada de `_pcontrol/gpacients.php`: acciones, campos, interacciones con la base de datos y elementos de la interfaz.</p>
				<p class="small-muted">Esta página documenta el fichero que gestiona la ficha y la lista de pacientes en el panel de control. Incluye ejemplos, campos esperados y recomendaciones de mantenimiento.</p>
			</section>

			<div class="docs-grid" style="margin-top:18px">
				<nav class="docs-index card">
					<h3 style="margin:8px 10px">Índice</h3>
					<ul>
						<li><a href="#resumen">Resumen rápido</a></li>
						<li><a href="#estructura">Estructura de `gpacients.php`</a></li>
						<li><a href="#acciones">Acciones POST</a></li>
						<li><a href="#campos">Campos del formulario</a></li>
						<li><a href="#ui">Interfaz y comportamiento</a></li>
						<li><a href="#tokens">Generación de tokens</a></li>
						<li><a href="#archivos">Archivos relacionados</a></li>
						<li><a href="#errores">Errores comunes y soluciones</a></li>
						<li><a href="#extender">Cómo extenderlo</a></li>
					</ul>
				</nav>

				<article class="doc-body card">
					<section id="guia">
						<h2>Guía rápida para el usuario</h2>
						<p>Esta guía explica, en lenguaje sencillo, cómo usar la sección <strong>Pacientes</strong> del panel. Está pensada para la persona que gestiona la consulta y necesita realizar tareas diarias sin entrar en detalles técnicos.</p>

						<h3>1) Acceder a la lista de pacientes</h3>
						<p>Al abrir <em>Pacientes</em> verás una lista con los pacientes registrados. En la parte superior hay tarjetas con estadísticas (total, activos, en seguimiento, altas) para tener un resumen rápido.</p>

						<h3>2) Buscar o filtrar</h3>
						<p>Usa la caja de búsqueda para encontrar un paciente por nombre o apellidos: escribe y pulsa <em>Enter</em>.</p>
						<p>También puedes aplicar filtros rápidos (Todos, Activos, Seguimiento) con los botones situados en la barra de acciones.</p>

						<h3>3) Crear un nuevo paciente</h3>
						<ol>
							<li>Pulsa el botón <strong>Nuevo Paciente</strong>.</li>
							<li>Se abrirá un formulario. Rellena al menos <strong>Nombre</strong> y <strong>Apellidos</strong>. Los demás campos son opcionales pero recomendados (teléfono, email, fecha de nacimiento, dirección).</li>
							<li>Si el paciente tiene alergias, medicación o antecedentes, añádelos en los campos correspondientes para dejar constancia.</li>
							<li>Pulsa <strong>Guardar</strong>. Si todo va bien verás un mensaje de confirmación y el paciente aparecerá en la lista.</li>
						</ol>

						<h3>4) Ver detalles</h3>
						<p>En la columna <em>Acciones</em> de cada fila hay un botón con el icono de ojo. Púlsalo para abrir un modal con los datos completos del paciente (contacto, historial y observaciones).</p>

						<h3>5) Editar un paciente</h3>
						<ol>
							<li>Pulsa el botón de editar (icono de lápiz) junto al paciente que quieres modificar.</li>
							<li>Se abrirá el mismo formulario que para crear; modifica los campos necesarios y pulsa <strong>Guardar</strong>.</li>
							<li>Comprueba el mensaje de éxito y revisa la ficha del paciente si lo deseas.</li>
						</ol>

						<h3>6) Cambiar el estado</h3>
						<p>El botón de estado permite alternar entre estados como <em>Activo</em>, <em>Inactivo</em>, <em>Alta</em> o <em>Seguimiento</em>. Utilízalo para indicar el estado actual del paciente (por ejemplo, marcar como <em>Alta</em> cuando termina el tratamiento).</p>

						<h3>7) Generar enlace para opinar (token)</h3>
						<p>Cuando un paciente está en estado <strong>Alta</strong>, aparece un botón de llave que genera un token y crea un enlace público para que el paciente deje una reseña/opinión. Pulsa el botón y copia el enlace que se muestra para enviarlo por email o mensaje.</p>

						<h3>8) Buenas prácticas y privacidad</h3>
						<ul>
							<li>Rellena siempre los datos de contacto (teléfono/email) para poder comunicar cambios o enviar enlaces.</li>
							<li>No compartas el acceso al panel; crea cuentas separadas si otras personas deben usarlo.</li>
							<li>Trata los campos de información médica como datos sensibles: anota únicamente lo necesario y respeta la normativa de protección de datos vigente.</li>
						</ul>

						<h3>9) ¿Qué hacer si algo falla?</h3>
						<ul>
							<li>Si no puedes guardar un paciente, revisa que los campos obligatorios estén completos y vuelve a intentarlo.</li>
							<li>Si no aparece ningún paciente, prueba a limpiar los filtros y la búsqueda o pregunta al administrador por un posible problema de conexión.</li>
							<li>Si el token no se genera, avisa al encargado técnico indicando el ID del paciente y la acción que intentaste.</li>
						</ul>
					</section>

					<section id="resumen">
						<h2>Resumen rápido</h2>
						<p>`_pcontrol/gpacients.php` es la página de gestión de pacientes del panel. Se encarga de mostrar la lista, estadísticas, filtrar/buscar, crear y editar pacientes, cambiar su estado y generar tokens de opinión para pacientes dados de alta.</p>
					</section>

					<section id="estructura">
						<h2>Estructura general</h2>
						<p>Los pasos principales que realiza el fichero son:</p>
						<ol>
							<li>Inicia la sesión y comprueba que el usuario está autenticado.</li>
							<li>Incluye las clases necesarias: `Connexio` y `Pacient` (y `RessenyaTokens` cuando se necesita).</li>
							<li>Obtiene la instancia de conexión (`Connexio::getInstance()->getConnexio()`).</li>
							<li>Instancia un objeto `Pacient` y prepara variables para mensajes.</li>
							<li>Procesa las peticiones `POST` diferenciadas por el campo `accio` (crear, actualitzar, canviar_estat, generar_token).</li>
							<li>Procesa parámetros `GET` para elegir la vista (`vista`, `id`, `filtre`, `cerca`).</li>
							<li>Consulta la base de datos mediante métodos de la clase `Pacient` (`llegirTots`, `cercarPerNom`, `llegirUn`, `obtenirEstadistiques`).</li>
							<li>Renderiza la UI (tarjetas de estadísticas, tabla de pacientes, modales y formularios) y enlaza scripts `js/gpacients.js` y `js/dashboard.js`.</li>
						</ol>

						<h3>Fragmento inicial</h3>
						<pre class="codeblock"><code><?php echo htmlspecialchars("// Autenticación y carga de clases\nsession_start();\nrequire_once '../classes/connexio.php';\nrequire_once '../classes/pacients.php';\n\$connexio = Connexio::getInstance();\n\$pdo = \$connexio->getConnexio();\n\$pacient = new Pacient(\$pdo);"); ?></code></pre>
					</section>

					<section id="acciones">
						<h2>Acciones POST</h2>
						<p>Las operaciones principales se distinguen mediante `$_POST['accio']`:</p>
						<ul>
							<li><strong>crear</strong>: crea un nuevo paciente leyendo los campos del formulario y llamando a `Pacient->crear()`.</li>
							<li><strong>actualitzar</strong>: actualiza un paciente existente; exige `id_pacient` y los campos a modificar, llama a `Pacient->actualitzar()`.</li>
							<li><strong>canviar_estat</strong>: cambia el estado del paciente a los valores usados en la UI (p. ej. `Activo`, `Inactivo`, `Alta`, `Seguimiento`) mediante `Pacient->canviarEstat()`.</li>
							<li><strong>generar_token</strong>: genera un token de opinión (usa `RessenyaTokens->createToken`) y construye un enlace público tipo `/ca/opina.php?token=...`.</li>
						</ul>

						<h3>Ejemplo de petición (simulación)</h3>
						<pre class="codeblock"><code><?php echo htmlspecialchars("POST /_pcontrol/gpacients.php\nContent-Type: application/x-www-form-urlencoded\n\naccio=crear&nom=Ana&cognoms=García&email=ana@example.com&telefon=666555444"); ?></code></pre>
					</section>

					<section id="campos">
						<h2>Campos del formulario</h2>
						<p>Los campos usados al crear/actualizar son (los marcados con * suelen ser obligatorios desde UI):</p>
						<ul>
							<li>`nom` *: nombre</li>
							<li>`cognoms` *: apellidos</li>
							<li>`data_naixement`: fecha de nacimiento (formato ISO yyyy-mm-dd)</li>
							<li>`sexe`: sexo</li>
							<li>`telefon`, `email`</li>
							<li>`adreca`, `ciutat`, `codi_postal`</li>
							<li>`antecedents_medics`, `medicacio_actual`, `alergies` (textos)</li>
							<li>`contacte_emergencia_nom`, `contacte_emergencia_telefon`, `contacte_emergencia_relacio`</li>
							<li>`estat`: `Activo` | `Inactivo` | `Alta` | `Seguimiento`</li>
							<li>`observacions` (notas internas)</li>
						</ul>
					</section>

					<section id="ui">
						<h2>Interfaz y comportamiento</h2>
						<p>Elementos relevantes en la UI:</p>
						<ul>
							<li><strong>Estadísticas:</strong> tarjetas con totales (`$stats`) extraídos por `Pacient->obtenirEstadistiques()`.</li>
							<li><strong>Búsqueda y filtros:</strong> `searchInput` y `filtre` se usan para filtrar la consulta que devuelve `$pacients`.</li>
							<li><strong>Tabla de pacientes:</strong> columnas: ID, nombre completo, edad (calculada en PHP), teléfono, email, estado, fecha registro y acciones.</li>
							<li><strong>Acciones:</strong> ver (abrir modal), editar (abrir modal con datos), cambiar estado y generar token (cuando el paciente está en `Alta`).</li>
							<li><strong>Modales:</strong> formularios para crear/editar (`#modalPacient`) y visualización de detalles (`#modalDetalls`).</li>
							<li><strong>Archivos JS/CSS:</strong> `js/gpacients.js`, `js/dashboard.js`, `css/gpacients.css` y `css/dashboard.css` controlan comportamiento y estilos.</li>
						</ul>
					</section>

					<section id="tokens">
						<h2>Generación de tokens</h2>
						<p>La acción `generar_token` utiliza la clase `RessenyaTokens` para crear un token temporal (duración en minutos en el ejemplo: 168). El resultado es un enlace público que se construye con el host actual y apunta a `/ca/opina.php?token=...`.</p>
						<pre class="codeblock"><code><?php echo htmlspecialchars("// Ejemplo simplificado\nrequire_once '../classes/ressenya_tokens.php';\n\$tModel = new RessenyaTokens(\$pdo);\n\$token = \$tModel->createToken(\$id_pacient, 168);\n// enlace: https://tu-dominio/ca/opina.php?token=TOKEN"); ?></code></pre>
					</section>

					<section id="archivos">
						<h2>Archivos relacionados</h2>
						<ul>
							<li><code>_pcontrol/gpacients.php</code> — fichero principal que documentamos.</li>
							<li><code>_pcontrol/js/gpacients.js</code> — lógica cliente (abrir modales, enviar formularios con confirmaciones, cargar detalles por AJAX).</li>
							<li><code>_pcontrol/css/gpacients.css</code> — estilos específicos de la sección.</li>
							<li><code>classes/pacients.php</code> — modelo que implementa `crear`, `actualitzar`, `llegirTots`, `cercarPerNom`, `llegirUn`, `obtenirEstadistiques`.</li>
							<li><code>classes/connexio.php</code> — conexión a la base de datos (PDO).</li>
							<li><code>classes/ressenya_tokens.php</code> — creación de tokens de opinión.</li>
						</ul>
					</section>

					<section id="errores">
						<h2>Errores comunes y soluciones</h2>
						<ul>
							<li><strong>Error de conexión a BD:</strong> revisar `connexio.php` y las credenciales en el entorno; habilitar excepciones PDO para ver el mensaje.</li>
							<li><strong>Faltan includes:</strong> si aparecen errores de clase no encontrada, comprobar rutas relativas (`require_once '../classes/...'`).</li>
							<li><strong>Sesión no iniciada:</strong> el usuario es redirigido a `index.php`. Comprueba que `$_SESSION['logged_in']` se establece al iniciar sesión.</li>
							<li><strong>Datos inválidos al guardar:</strong> añadir validación server-side en `gpacients.php` o en métodos de `Pacient` para evitar entradas erróneas.</li>
							<li><strong>Token no generado:</strong> comprobar que `RessenyaTokens` funciona y que la tabla correspondiente existe y tiene permisos de escritura.</li>
						</ul>
					</section>

					<section id="extender">
						<h2>Cómo extenderlo</h2>
						<ul>
							<li>Para añadir un campo nuevo: 1) añadir columna en la tabla de la BD, 2) exponerlo en `classes/pacients.php` (atributo y lectura/escritura), 3) actualizar los formularios en el modal y 4) mostrarlo en la tabla si procede.</li>
							<li>Validación: usar filtros `filter_var()` y validación adicional en `Pacient->crear()` / `Pacient->actualitzar()` para mantener integridad.</li>
							<li>Separar la lógica: si crece la complejidad, mover el procesamiento de POST a un controlador separado o convertir la sección en API JSON para que el front consuma desde `js/gpacients.js`.</li>
						</ul>
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
