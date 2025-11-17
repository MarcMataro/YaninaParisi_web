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
    <title>Sesiones — Documentación del Panel</title>
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
                    <p class="date-today">Guía de la sección Sesiones</p>
                </div>
            </div>
        </header>

        <main class="dashboard-container">
            <section class="docs-hero card">
                <h1>Sesiones / Agenda</h1>
                <p class="lead">Guía práctica para gestionar la agenda: crear, editar, reprogramar, cancelar sesiones y vincularlas a pacientes.</p>
                <p class="small-muted">Esta guía está dirigida a la persona que gestiona las citas y la agenda diaria de la consulta. No requiere conocimientos técnicos.</p>
            </section>

            <div class="docs-grid" style="margin-top:18px">
                <nav class="docs-index card">
                    <h3 style="margin:8px 10px">Índice</h3>
                    <ul>
                        <li><a href="#resumen">Resumen</a></li>
                        <li><a href="#ver">Ver la agenda</a></li>
                        <li><a href="#crear">Crear una sesión</a></li>
                        <li><a href="#editar">Editar / Reprogramar</a></li>
                        <li><a href="#cancelar">Cancelar o marcar como no asistido</a></li>
                        <li><a href="#vincular">Vincular sesión a paciente</a></li>
                        <li><a href="#recordatorios">Recordatorios y comunicación</a></li>
                        <li><a href="#buenas">Buenas prácticas</a></li>
                        <li><a href="#problemas">Qué hacer si hay problemas</a></li>
                    </ul>
                </nav>

                <article class="doc-body card">
                    <section id="resumen">
                        <h2>Resumen</h2>
                        <p>La sección <strong>Sesiones</strong> (Agenda) permite planificar y gestionar las citas de los pacientes: visualizar el calendario, crear nuevas sesiones, editar o reprogramar, cancelar y llevar un seguimiento del estado (confirmada, pendiente, cancelada, no asistió). También permite registrar pagos o marcar la sesión como cobrada si la interfaz lo incorpora.</p>
                    </section>

                    <section id="ver">
                        <h2>Ver la agenda</h2>
                        <p>Al acceder verás normalmente una vista de calendario (día/semana/mes) o una lista con las sesiones del día. Utiliza los controles para cambiar la vista y navegar por fechas.</p>
                        <ul>
                            <li><strong>Vista diaria:</strong> ideal para el seguimiento del día a día.</li>
                            <li><strong>Vista semanal:</strong> útil para planificar horarios y detectar huecos.</li>
                            <li><strong>Vista mensual:</strong> para una visión general y planificación a largo plazo.</li>
                        </ul>
                    </section>

                    <section id="crear">
                        <h2>Crear una sesión</h2>
                        <ol>
                            <li>Pulsa el botón <strong>Nuevo</strong> o haz clic en la casilla del calendario en la hora deseada.</li>
                            <li>Rellena los datos básicos: fecha, hora, duración, tipo de sesión (consulta primera visita, seguimiento, online, presencial) y, opcionalmente, la sala o ubicación.</li>
                            <li>Vincula la sesión a un paciente existente (busca por nombre). Si es una sesión para un nuevo paciente, crea la ficha primero o añade los datos mínimos.</li>
                            <li>Añade notas internas si necesitas instrucciones para la sesión.</li>
                            <li>Confirma y guarda la sesión. Verás la cita en la agenda en la fecha y hora seleccionadas.</li>
                        </ol>
                    </section>

                    <section id="editar">
                        <h2>Editar / Reprogramar</h2>
                        <p>Para modificar una sesión, ábrela y pulsa <em>Editar</em> o arrástrala en la vista calendario a la nueva hora/fecha (si la interfaz lo permite).</p>
                        <p>Cuando reprogramas, avisa al paciente si procede y registra la razón en las notas si es necesario.</p>
                    </section>

                    <section id="cancelar">
                        <h2>Cancelar o marcar como no asistido</h2>
                        <p>Si el paciente cancela, marca la sesión como <em>Cancelada</em> o <em>No asistió</em> según corresponda. Algunas acciones útiles:</p>
                        <ul>
                            <li>Anota el motivo de la cancelación en las notas.</li>
                            <li>Si la política lo exige, registra si hay derecho a reembolso o penalización.</li>
                            <li>Reprograma la sesión o coloca al paciente en la lista de espera si procede.</li>
                        </ul>
                    </section>

                    <section id="vincular">
                        <h2>Vincular sesión a paciente</h2>
                        <p>Siempre que sea posible, vincula las sesiones a la ficha del paciente. Esto permite:</p>
                        <ul>
                            <li>Ver el historial de citas del paciente.</li>
                            <li>Acceder rápidamente al contacto para enviar recordatorios.</li>
                            <li>Relacionar facturación o notas clínicas con la cita.</li>
                        </ul>
                        <p>Busca al paciente por nombre al crear la sesión; si no existe, crea la ficha antes de guardar la sesión.</p>
                    </section>

                    <section id="recordatorios">
                        <h2>Recordatorios y comunicación</h2>
                        <p>Si el sistema incluye avisos o recordatorios automatizados, comprueba las opciones de envío antes de activar mensajes masivos.</p>
                        <ul>
                            <li>Confirma que el paciente tiene teléfono o correo para enviar SMS/email.</li>
                            <li>Personaliza el texto del recordatorio si la herramienta lo permite.</li>
                            <li>Registra si el paciente confirma o solicita cambio de hora.</li>
                        </ul>
                    </section>

                    <section id="buenas">
                        <h2>Buenas prácticas</h2>
                        <ul>
                            <li>Actualiza la agenda en tiempo real y evita duplicar citas para la misma hora.</li>
                            <li>Utiliza la vista semanal para detectar huecos y optimizar la planificación.</li>
                            <li>Registra siempre el estado de la sesión (confirmada, pendiente, cancelada, no asistió).</li>
                            <li>Si trabajas con salas múltiples, indica la ubicación para evitar solapamientos.</li>
                        </ul>
                    </section>

                    <section id="problemas">
                        <h2>Qué hacer si hay problemas</h2>
                        <ul>
                            <li>No puedo crear la sesión: revisa fecha/hora y campos obligatorios, y vuelve a intentarlo.</li>
                            <li>La sesión no aparece en la agenda: limpia filtros y comprueba la vista/fecha seleccionada.</li>
                            <li>Coincidencia de horarios: revisa otras sesiones y ajusta la duración; marca salas para evitar solapamientos.</li>
                            <li>Errores de comunicación (recordatorios): verifica datos de contacto del paciente y configura el servicio de envío.</li>
                        </ul>
                    </section>
                </article>
            </div>
        </main>
    </div>

    <script>
        document.getElementById('menuToggle')?.addEventListener('click', function(){
            document.querySelector('.sidebar')?.classList.toggle('active');
        });
        document.querySelectorAll('.docs-index a').forEach(a => a.addEventListener('click', function(e){
            e.preventDefault(); const target = document.querySelector(this.getAttribute('href')); if(target) target.scrollIntoView({behavior:'smooth', block:'start'});
        }));
    </script>
</body>
</html>
