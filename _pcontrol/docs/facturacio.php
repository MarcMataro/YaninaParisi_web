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
    <title>Facturación — Guía de Usuario</title>
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
                    <p class="date-today">Guía de uso de Facturación</p>
                </div>
            </div>
        </header>

        <main class="dashboard-container">
            <section class="docs-hero card">
                <h1>Facturación</h1>
                <p class="lead">Guía práctica para gestionar pagos: registrar cobros, marcar facturado, descargar facturas, anular pagos y gestionar sesiones sin pagar.</p>
                <p class="small-muted">Esta guía está pensada para el personal encargado de la administración y contabilidad de la consulta. Explica los pasos habituales y buenas prácticas.</p>
            </section>

            <div class="docs-grid" style="margin-top:18px">
                <nav class="docs-index card">
                    <h3 style="margin:8px 10px">Índice</h3>
                    <ul>
                        <li><a href="#resumen">Resumen</a></li>
                        <li><a href="#estadisticas">Qué muestran las estadísticas</a></li>
                        <li><a href="#filtrar">Filtrar y buscar pagos</a></li>
                        <li><a href="#nuevo">Registrar un nuevo pago</a></li>
                        <li><a href="#pagos-sin-pagar">Registrar pago rápido (sesiones sin pagar)</a></li>
                        <li><a href="#marcar-facturado">Marcar como facturado y descargar factura</a></li>
                        <li><a href="#anular">Anular un pago</a></li>
                        <li><a href="#eliminar">Eliminar un registro</a></li>
                        <li><a href="#buenas">Buenas prácticas</a></li>
                        <li><a href="#problemas">Qué hacer si surge un problema</a></li>
                    </ul>
                </nav>

                <article class="doc-body card">
                    <section id="resumen">
                        <h2>Resumen</h2>
                        <p>La sección de <strong>Facturación</strong> permite administrar los cobros de las sesiones: ver listados, filtrar por estado o método, registrar pagos manualmente, marcar pagos como facturados (e introducir un número de factura), descargar facturas en PDF cuando estén disponibles y anular o eliminar registros si es necesario.</p>
                    </section>

                    <section id="estadisticas">
                        <h2>Qué muestran las estadísticas</h2>
                        <ul>
                            <li><strong>Ingresos Totales:</strong> suma de todos los pagos registrados en el periodo.</li>
                            <li><strong>Pagos Completados:</strong> importe y número de pagos que ya están pagados.</li>
                            <li><strong>Pagos Pendientes:</strong> importe y número de pagos aún no cobrados.</li>
                            <li><strong>Este Mes:</strong> ingresos y número de pagos del mes en curso.</li>
                        </ul>
                        <p>Estas tarjetas ayudan a tener una visión rápida de la situación económica de la consulta.</p>
                    </section>

                    <section id="filtrar">
                        <h2>Filtrar y buscar pagos</h2>
                        <p>Usa los filtros para localizar pagos concretos:</p>
                        <ul>
                            <li><strong>Estado:</strong> Completado, Pendiente, Anulado.</li>
                            <li><strong>Método de pago:</strong> Efectivo, Tarjeta, Transferencia, Bizum.</li>
                            <li><strong>Facturación:</strong> filtrar por facturados o no facturados.</li>
                            <li><strong>Rango de fechas:</strong> fecha desde/hasta para limitar la búsqueda.</li>
                        </ul>
                        <p>Pulsa <em>Filtrar</em> para aplicar las opciones o <em>Limpiar</em> para volver a la vista completa.</p>
                    </section>

                    <section id="nuevo">
                        <h2>Registrar un nuevo pago</h2>
                        <ol>
                            <li>Pulsa <strong>Nuevo Pago</strong>.</li>
                            <li>Selecciona la <strong>sesión</strong> correspondiente (si está en la lista de sesiones sin pagar aparecerá en el selector).</li>
                            <li>Introduce la <strong>fecha de pago</strong>, el <strong>importe</strong>, el <strong>método de pago</strong> y el <strong>estado</strong> (normalmente <em>Completado</em> o <em>Pendiente</em>).</li>
                            <li>Si el pago ya tiene factura, marca <em>Marcar como facturado</em> e introduce el <strong>número de factura</strong>.</li>
                            <li>Añade observaciones si necesitas anotar un detalle (por ejemplo, forma de cobro, descuento aplicado, incidencia).</li>
                            <li>Pulsa <strong>Guardar</strong>. Verás un mensaje de confirmación y el pago aparecerá en la lista.</li>
                        </ol>
                        <p>Si una sesión tiene precio predefinido, al seleccionar la sesión el sistema suele autocompletar el importe y los datos del paciente.</p>
                    </section>

                    <section id="pagos-sin-pagar">
                        <h2>Registrar pago rápido (desde Sesiones Sin Pagar)</h2>
                        <p>En la sección <em>Sesiones Sin Pagar</em> aparecen las sesiones realizadas que aún no tienen pago. Para registrar un pago rápido:</p>
                        <ol>
                            <li>Localiza la sesión en la lista de <em>Sesiones Sin Pagar</em>.</li>
                            <li>Pulsa <strong>Registrar Pago</strong> junto a la sesión; se abrirá un formulario con el importe ya completado.</li>
                            <li>Confirma método de pago, fecha y estado y guarda.</li>
                        </ol>
                        <p>Es la forma más rápida de marcar como cobrado una sesión desde la vista de control.</p>
                    </section>

                    <section id="marcar-facturado">
                        <h2>Marcar como facturado y descargar factura</h2>
                        <p>Si el pago corresponde a una factura, puedes marcarlo como facturado:</p>
                        <ol>
                            <li>En la lista de pagos, si el pago está <em>Completado</em>, pulsa el botón <em>Marcar como facturado</em>.</li>
                            <li>Introduce el <strong>número de factura</strong> (ej.: 2025-001) y confirma.</li>
                            <li>Una vez marcado, si el sistema genera facturas en PDF, aparecerá un botón para <strong>Descargar factura</strong>.</li>
                        </ol>
                        <p>Guarda el número de factura para la contabilidad y evita duplicados.</p>
                    </section>

                    <section id="anular">
                        <h2>Anular un pago</h2>
                        <p>Si un pago fue erróneo o se devolvió, anúlalo en lugar de editarlo para mantener el historial:</p>
                        <ol>
                            <li>Pulsa <strong>Anular pago</strong> en la fila correspondiente.</li>
                            <li>Indica un motivo en el formulario (opcional, pero recomendable).</li>
                            <li>Confirma. El pago quedará marcado como <em>Anulado</em> y no se considerará en estadísticas de ingresos.</li>
                        </ol>
                        <p>Usa la función <em>Eliminar</em> solo si el registro debe ser borrado por completo (por ejemplo, duplicados o pruebas).</p>
                    </section>

                    <section id="eliminar">
                        <h2>Eliminar un registro</h2>
                        <p>Eliminar un pago borra el registro de la base de datos. Antes de eliminar, comprueba que no afecta a la contabilidad. Si dudas, marca como anulado y consulta al responsable contable.</p>
                    </section>

                    <section id="buenas">
                        <h2>Buenas prácticas</h2>
                        <ul>
                            <li>Registra los pagos lo antes posible tras el cobro para mantener las estadísticas al día.</li>
                            <li>Usa el campo de observaciones para indicar descuentos, reembolsos o incidencias.</li>
                            <li>Evita duplicar pagos: revisa la sesión y la ficha del paciente antes de crear un nuevo pago.</li>
                            <li>Marca claramente las facturas con números únicos y consistentes para la contabilidad.</li>
                            <li>Si anulas pagos, registra el motivo para futuras auditorías.</li>
                        </ul>
                    </section>

                    <section id="problemas">
                        <h2>Qué hacer si surge un problema</h2>
                        <ul>
                            <li>El pago no se guarda: revisa los campos obligatorios (sesión, importe, fecha) y vuelve a intentarlo.</li>
                            <li>No aparece la sesión en <em>Sesiones Sin Pagar</em>: verifica que la sesión está registrada y no tiene ya pago asociado.</li>
                            <li>La factura no se descarga: comprueba que el registro está marcado como facturado y pregunta al administrador si la generación de PDFs está habilitada.</li>
                            <li>Importes incorrectos: corrige mediante editar (si procede) o anular y volver a crear el pago con los datos correctos.</li>
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
        // Smooth anchors
        document.querySelectorAll('.docs-index a').forEach(a => a.addEventListener('click', function(e){
            e.preventDefault(); const target = document.querySelector(this.getAttribute('href')); if(target) target.scrollIntoView({behavior:'smooth', block:'start'});
        }));
    </script>
</body>
</html>
