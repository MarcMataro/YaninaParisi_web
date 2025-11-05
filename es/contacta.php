<?php 
// Inicializar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Debug ANTES del procesamiento
echo "<!-- DEBUG INDEX ANTES: GET lang: " . ($_GET['lang'] ?? 'no definido') . " -->";
echo "<!-- DEBUG INDEX ANTES: Session lang antes: " . ($_SESSION['language'] ?? 'no definido') . " -->";

// Forzar idioma español en esta página
$_SESSION['language'] = 'es';
// Procesar cambio de idioma PRIMERO
if (isset($_GET['lang'])) {
    $lang = $_GET['lang'];
    if (in_array($lang, array('ca', 'es'))) {
        $_SESSION['language'] = $lang;
        header('Location: /' . $lang . '/home.php');
        exit;
    }
}
// Incluir sistema de traducción
include '../includes/lang.php';
// Ensure helper functions are available for breadcrumbs
include '../includes/functions.php';

// Procesar el formulario si se ha enviado
$message_sent = false;
$message_error = false;

if ($_POST) {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $subject = $_POST['subject'] ?? '';
    $message = $_POST['message'] ?? '';
    $privacy = isset($_POST['privacy']);
    
    // Validación básica
    if (!empty($name) && !empty($email) && !empty($message) && $privacy) {
        $message_sent = true;
    } else {
        $message_error = true;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contacto - Yanina Parisi</title>
    <meta name="description" content="Contacta con Yanina Parisi, psicóloga en Girona.">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@7.0.0/css/all.min.css">
    <link rel="stylesheet" href="../css/estils.css">
    <link rel="stylesheet" href="../css/contacte.css">
    <link rel="icon" type="image/png" sizes="32x32" href="../img/Logo32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="../img/Logo16.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Libre+Baskerville:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet">
</head>
<body>
    <?php include '_includes/navigation.php'; ?>

    <!-- Hero Section -->
    <section class="contact-hero">
        <div class="container">
            <div class="contact-hero-content">
                <h1>Contacta conmigo</h1>
                <p class="contact-hero-subtitle">
                    Estoy aquí para ayudarte. La primera consulta es completamente gratuita y sin compromiso.
                </p>
            </div>
        </div>
    </section>
    <?php
        // Breadcrumbs: Home > Contacto (ESP)
        if (function_exists('render_breadcrumbs')) {
            render_breadcrumbs([
                ['label' => t('nav_home'), 'url' => 'home.php'],
                ['label' => t('nav_contact')]
            ]);
        }
    ?>
    <!-- Main Content -->
    <section class="contact-main">
        <div class="container">
            <?php if ($message_sent): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <span>¡Gracias por contactarme!</span>
                </div>
            <?php endif; ?>
            
            <?php if ($message_error): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <span>Completa todos los campos obligatorios</span>
                </div>
            <?php endif; ?>

            <div class="contact-grid">
                <div class="contact-form-section" id="contact-form">
                    <div class="form-header">
                            <h2>Solicita una cita</h2>
                            <p>Completa el formulario para contactar. Puedes elegir fecha y hora directamente desde el calendario.</p>
                            <!-- Booking calendar -->
                            <div id="booking-calendar" class="booking-calendar" aria-label="Calendario de reservas">
                                <div class="cal-header">
                                    <button type="button" class="cal-prev" aria-label="Mes anterior">‹</button>
                                    <div class="cal-title" aria-live="polite"></div>
                                    <button type="button" class="cal-next" aria-label="Mes siguiente">›</button>
                                </div>
                                <div class="cal-grid">
                                    <!-- Days of week -->
                                </div>
                            </div>
                    </div>
                    
                    <form class="contact-form" method="POST" action="">
                        <input type="hidden" id="appointment" name="appointment" value="">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="name">
                                    <i class="fas fa-user"></i>
                                    Nombre completo *
                                </label>
                                <input type="text" id="name" name="name" required 
                                       placeholder="Tu nombre y apellidos"
                                       value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="email">
                                    <i class="fas fa-envelope"></i>
                                    Correo electrónico *
                                </label>
                                <input type="email" id="email" name="email" required 
                                       placeholder="ejemplo@correo.com"
                                       value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="message">
                                <i class="fas fa-comment"></i>
                                Mensaje *
                            </label>
                            <textarea id="message" name="message" required 
                                      placeholder="Tu mensaje..."><?php echo isset($_POST['message']) ? htmlspecialchars($_POST['message']) : ''; ?></textarea>
                        </div>
                        
                        <div class="form-group form-checkbox">
                            <label for="privacy" class="checkbox-label">
                                <input type="checkbox" id="privacy" name="privacy" required>
                                <span class="checkmark"></span>
                                Acepto la política de privacidad *
                            </label>
                        </div>
                        
                        <button type="submit" class="btn btn-primary btn-submit">
                            <i class="fas fa-paper-plane"></i>
                            Enviar mensaje
                        </button>
                    </form>
                </div>
                
                <div class="contact-info-section">
                    <div class="contact-info-header">
                        <h3>Información de contacto</h3>
                    </div>
                    
                    <div class="contact-methods">
                        <div class="contact-method">
                            <div class="contact-icon">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <div class="contact-details">
                                <h4>Correo electrónico</h4>
                                <p>info@yaninaparisi.com</p>
                            </div>
                        </div>
                        
                        <div class="contact-method">
                            <div class="contact-icon">
                                <i class="fas fa-phone"></i>
                            </div>
                            <div class="contact-details">
                                <h4>Teléfono</h4>
                                <p>+34 XXX XXX XXX</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php include '_includes/footer.php'; ?>

    <script>
        // Script para la navegación suave
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                document.querySelector(this.getAttribute('href')).scrollIntoView({
                    behavior: 'smooth'
                });
            });
        });

        // Script para el efecto scroll de la navegación
        window.addEventListener('scroll', function() {
            const header = document.querySelector('header');
            if (window.scrollY > 50) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }
        });

        // Script para el selector de idioma

        
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.lang-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    // Obtener el idioma del data attribute
                    const lang = this.getAttribute('data-lang');
                    console.log('Botón clickado, idioma:', lang);
                    
                    // Eliminar clase active de todos los botones (tanto desktop como móvil)
                    document.querySelectorAll('.lang-btn').forEach(b => b.classList.remove('active'));
                    // Añadir clase active a todos los botones del mismo idioma
                    document.querySelectorAll(`.lang-btn[data-lang="${lang}"]`).forEach(b => b.classList.add('active'));
                    
                    // Cerrar menú móvil si está abierto
                    const mobileMenuToggle = document.querySelector('.mobile-menu-toggle');
                    const navMenu = document.querySelector('.nav-menu ul');
                    if (mobileMenuToggle && navMenu) {
                        mobileMenuToggle.classList.remove('active');
                        navMenu.classList.remove('show');
                    }
                    
                    // Cambiar idioma
                    changeLanguage(lang);
                });
            });

            // Funcionalidad del menú hamburguesa
            const mobileMenuToggle = document.querySelector('.mobile-menu-toggle');
            const navMenu = document.querySelector('.nav-menu ul');

            if (mobileMenuToggle && navMenu) {
                mobileMenuToggle.addEventListener('click', function() {
                    this.classList.toggle('active');
                    navMenu.classList.toggle('show');
                });

                // Cerrar menú cuando se hace clic en un enlace
                document.querySelectorAll('.nav-menu ul li a').forEach(link => {
                    link.addEventListener('click', function() {
                        mobileMenuToggle.classList.remove('active');
                        navMenu.classList.remove('show');
                    });
                });

                // Cerrar menú cuando se hace clic fuera
                document.addEventListener('click', function(e) {
                    if (!mobileMenuToggle.contains(e.target) && !navMenu.contains(e.target)) {
                        mobileMenuToggle.classList.remove('active');
                        navMenu.classList.remove('show');
                    }
                });

                /* Simple calendar renderer (ES) */
                (function(){
                    const calendar = document.getElementById('booking-calendar');
                    if (!calendar) return;

                    const title = calendar.querySelector('.cal-title');
                    const grid = calendar.querySelector('.cal-grid');
                    const prev = calendar.querySelector('.cal-prev');
                    const next = calendar.querySelector('.cal-next');

                    const dow = ['Lu','Ma','Mi','Ju','Vi','Sa','Do'];
                    const today = new Date();
                    let viewDate = new Date(today.getFullYear(), today.getMonth(), 1);

                    function render(){
                        title.textContent = viewDate.toLocaleString(document.documentElement.lang || 'es', { month:'long', year:'numeric' });
                        grid.innerHTML = '';
                        // headers
                        dow.forEach(d=>{ const el=document.createElement('div'); el.className='dow'; el.textContent=d; grid.appendChild(el); });

                        const firstDow = new Date(viewDate.getFullYear(), viewDate.getMonth(), 1).getDay();
                        const startOffset = (firstDow + 6) % 7;

                        for(let i=0;i<startOffset;i++){ const empty=document.createElement('div'); empty.className='day empty'; grid.appendChild(empty); }

                        const daysInMonth = new Date(viewDate.getFullYear(), viewDate.getMonth()+1,0).getDate();
                        for(let d=1; d<=daysInMonth; d++){
                            const dt = new Date(viewDate.getFullYear(), viewDate.getMonth(), d);
                            const el = document.createElement('button'); el.type='button'; el.className='day';
                            el.innerHTML = '<span class="date">'+d+'</span>';
                            if (dt < new Date(today.getFullYear(), today.getMonth(), today.getDate())){
                                el.classList.add('disabled');
                                el.disabled = true;
                            }
                            if (dt.toDateString() === today.toDateString()) el.classList.add('today');
                            el.addEventListener('click', function(){ onDayClick(dt, el); });
                            grid.appendChild(el);
                            if (!el.disabled) {
                                checkDayFullyBooked(dt, el);
                            }
                        }
                    }

                    function toLocalISODate(date) {
                        const year = date.getFullYear();
                        const month = String(date.getMonth() + 1).padStart(2, '0');
                        const day = String(date.getDate()).padStart(2, '0');
                        return `${year}-${month}-${day}`;
                    }

                    function onDayClick(dateObj, el){
                        const isoDate = toLocalISODate(dateObj);
                        fetch('../api/get_availability.php?date=' + isoDate)
                            .then(res => res.json())
                            .then(json => {
                                if (!json || !json.success) {
                                    const timesFallback = [];
                                    for (let h = 9; h <= 20; h++) {
                                        if (h === 13 || h === 14) continue;
                                        timesFallback.push(String(h).padStart(2,'0')+':00');
                                    }
                                    showTimesPanel(dateObj, timesFallback);
                                    return;
                                }
                                showTimesPanel(dateObj, json.slots);
                            })
                            .catch(err => {
                                console.error('Error fetching availability', err);
                                const timesFallback = [];
                                for (let h = 9; h <= 20; h++) {
                                    if (h === 13 || h === 14) continue;
                                    timesFallback.push(String(h).padStart(2,'0')+':00');
                                }
                                showTimesPanel(dateObj, timesFallback);
                            });
                    }

                    function showTimesPanel(dateObj, times){
                        let panel = document.getElementById('times-panel');
                        if (!panel){
                            panel = document.createElement('div'); panel.id='times-panel'; panel.className='times-panel';
                            panel.innerHTML = '<button class="times-close" aria-label="Close">×</button><h4></h4><div class="times-list"></div>';
                            document.body.appendChild(panel);
                            panel.querySelector('.times-close').addEventListener('click', ()=>{ panel.style.display='none'; });
                        }
                        panel.querySelector('h4').textContent = dateObj.toLocaleDateString(document.documentElement.lang || 'es', { weekday:'long', year:'numeric', month:'long', day:'numeric' });
                        const list = panel.querySelector('.times-list'); list.innerHTML='';
                        times.forEach(t=>{
                            const timeText = (typeof t === 'string') ? t : (t.time || '');
                            const available = (typeof t === 'string') ? true : !!t.available;
                            const btn = document.createElement('button'); btn.type='button'; btn.textContent = timeText;
                            if (!available) {
                                btn.classList.add('occupied');
                                btn.disabled = true;
                                btn.title = 'Hora ocupada';
                            }
                            list.appendChild(btn);

                            btn.addEventListener('click', ()=>{
                                if (!available) return;
                                const iso = toLocalISODate(dateObj)+' '+timeText;
                                const input = document.getElementById('appointment');
                                if (input) input.value = iso;
                                panel.style.display='none';
                                document.querySelectorAll('#booking-calendar .day.selected').forEach(n=>n.classList.remove('selected'));
                                const allDays = document.querySelectorAll('#booking-calendar .day');
                                allDays.forEach(node=>{
                                    if (node.disabled) return;
                                    if (node.querySelector('.date') && node.querySelector('.date').textContent == dateObj.getDate()){
                                        node.classList.add('selected');
                                    }
                                });
                                const form = document.querySelector('.contact-form'); if (form) form.scrollIntoView({behavior:'smooth', block:'center'});
                            });
                        });
                        panel.style.display='block';
                    }

                    function checkDayFullyBooked(dateObj, dayEl){
                        const isoDate = toLocalISODate(dateObj);
                        fetch('../api/get_availability.php?date=' + isoDate)
                            .then(res => res.json())
                            .then(json => {
                                if (!json || !json.success) return;
                                if (json.session_count > 0) {
                                    dayEl.classList.add('has-sessions');
                                }
                                if (json.fully_booked) {
                                    dayEl.classList.add('fully-booked');
                                    dayEl.disabled = true;
                                    dayEl.title = 'Todo el día está reservado';
                                }
                            })
                            .catch(err => {
                                // ignore errors silently
                            });
                    }

                    prev.addEventListener('click', ()=>{ viewDate = new Date(viewDate.getFullYear(), viewDate.getMonth()-1,1); render(); });
                    next.addEventListener('click', ()=>{ viewDate = new Date(viewDate.getFullYear(), viewDate.getMonth()+1,1); render(); });

                    render();
                })();
            }
        });
    </script>
    <script src="../js/language.js"></script>
</body>
</html>
<?php 
// Inicializar sesión si no está iniciada

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Forzar idioma español en esta página
$_SESSION['language'] = 'es';
// Procesar cambio de idioma primero
if (isset($_GET['lang'])) {
    $lang = $_GET['lang'];
    if (in_array($lang, array('ca', 'es'))) {
        $_SESSION['language'] = $lang;
        header('Location: /' . $lang . '/home.php');
        exit;
    }
}
// Incluir sistema de traducción
include '../includes/lang.php';
// Asegurar helpers disponibles
include '../includes/functions.php';

// Procesar el formulario si se ha enviado
$message_sent = false;
$message_error = false;

if ($_POST) {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $subject = $_POST['subject'] ?? '';
    $message = $_POST['message'] ?? '';
    $privacy = isset($_POST['privacy']);
    
    // Validacio basica
    if (!empty($name) && !empty($email) && !empty($message) && $privacy) {
        $message_sent = true;
    } else {
        $message_error = true;
    }
}
?>
<!DOCTYPE html>
<html lang="<?php echo getCurrentLanguage(); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo getCurrentLanguage() == 'ca' ? 'Contacte - Yanina Parisi' : 'Contacto - Yanina Parisi'; ?></title>
    <meta name="description" content="<?php echo getCurrentLanguage() == 'ca' ? 'Contacta amb Yanina Parisi, psicologa a Girona.' : 'Contacta con Yanina Parisi, psicologa en Girona.'; ?>">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@7.0.0/css/all.min.css">
    <link rel="stylesheet" href="../css/estils.css">
    <link rel="stylesheet" href="../css/contacte.css">
    <link rel="icon" type="image/png" sizes="32x32" href="../img/Logo32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="../img/Logo16.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Libre+Baskerville:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet">
</head>
<body>
    <?php include '_includes/navigation.php'; ?>

    <!-- Hero Section -->
    <section class="contact-hero">
        <div class="container">
            <div class="contact-hero-content">
                <h1>Contacta conmigo</h1>
                <p class="contact-hero-subtitle">
                    Estoy aquí para ayudarte. La primera consulta es completamente gratuita y sin compromiso.
                </p>
            </div>
        </div>
    </section>

    <!-- Breadcrumbs: Home > Contact -->
    <?php
        if (function_exists('render_breadcrumbs')) {
            render_breadcrumbs([
                ['label' => t('nav_home'), 'url' => 'home.php'],
                ['label' => t('nav_contact')]
            ]);
        }
    ?>

    <!-- Main Content -->
    <section class="contact-main">
        <div class="container">
            <?php if ($message_sent): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <span>¡Gracias por contactarme!</span>
                </div>
            <?php endif; ?>
            
            <?php if ($message_error): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <span>Completa todos los campos obligatorios y acepta la política de privacidad.</span>
                </div>
            <?php endif; ?>

            <div class="contact-grid">
                <div class="contact-form-section" id="contact-form">
                    <div class="form-header">
                        <h2>Pide tu cita</h2>
                        <p>Completa el formulario para contactar.</p>
                    </div>
                    
                    <form class="contact-form" method="POST" action="">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="name">
                                    <i class="fas fa-user"></i>
                                    Nombre completo *
                                </label>
                                <input type="text" id="name" name="name" required 
                                       placeholder="Tu nombre y apellidos"
                                       value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="email">
                                    <i class="fas fa-envelope"></i>
                                    Correo electrónico *
                                </label>
                                <input type="email" id="email" name="email" required 
                                       placeholder="ejemplo@correo.com"
                                       value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="message">
                                <i class="fas fa-comment"></i>
                                Mensaje *
                            </label>
                            <textarea id="message" name="message" required 
                                      placeholder="Tu mensaje..."><?php echo isset($_POST['message']) ? htmlspecialchars($_POST['message']) : ''; ?></textarea>
                        </div>
                        
                        <div class="form-group form-checkbox">
                            <label for="privacy" class="checkbox-label">
                                <input type="checkbox" id="privacy" name="privacy" required>
                                <span class="checkmark"></span>
                                Acepto la política de privacidad *
                            </label>
                        </div>
                        
                        <button type="submit" class="btn btn-primary btn-submit">
                            <i class="fas fa-paper-plane"></i>
                            Enviar mensaje
                        </button>
                    </form>
                </div>
                
                <div class="contact-info-section">
                    <div class="contact-info-header">
                        <h3>Información de contacto</h3>
                    </div>
                    
                    <div class="contact-methods">
                        <div class="contact-method">
                            <div class="contact-icon">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <div class="contact-details">
                                <h4>Correo electrónico</h4>
                                <p>info@yaninaparisi.com</p>
                            </div>
                        </div>
                        
                        <div class="contact-method">
                            <div class="contact-icon">
                                <i class="fas fa-phone"></i>
                            </div>
                            <div class="contact-details">
                                <h4>Teléfono</h4>
                                <p>+34 XXX XXX XXX</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php include '_includes/footer.php'; ?>

    <script>
        // Script para la navegación suave
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                document.querySelector(this.getAttribute('href')).scrollIntoView({
                    behavior: 'smooth'
                });
            });
        });

        // Script para el efecto de scroll en la navegación
        window.addEventListener('scroll', function() {
            const header = document.querySelector('header');
            if (window.scrollY > 50) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }
        });

        // Script para el selector de idioma

        
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.lang-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    // Obtenir l'idioma del data attribute
                    const lang = this.getAttribute('data-lang');
                    console.log('Botón pulsado, idioma:', lang);
                    
                    // Eliminar classe active de tots els botons (tant desktop com mòbil)
                    document.querySelectorAll('.lang-btn').forEach(b => b.classList.remove('active'));
                    // Afegir classe active a tots els botons del mateix idioma
                    document.querySelectorAll(`.lang-btn[data-lang="${lang}"]`).forEach(b => b.classList.add('active'));
                    
                    // Tancar menú mòbil si està obert
                    const mobileMenuToggle = document.querySelector('.mobile-menu-toggle');
                    const navMenu = document.querySelector('.nav-menu ul');
                    if (mobileMenuToggle && navMenu) {
                        mobileMenuToggle.classList.remove('active');
                        navMenu.classList.remove('show');
                    }
                    
                    // Canviar idioma
                    changeLanguage(lang);
                });
            });

            // Funcionalitat del menú hamburguesa
            const mobileMenuToggle = document.querySelector('.mobile-menu-toggle');
            const navMenu = document.querySelector('.nav-menu ul');

            if (mobileMenuToggle && navMenu) {
                mobileMenuToggle.addEventListener('click', function() {
                    this.classList.toggle('active');
                    navMenu.classList.toggle('show');
                });

                // Tancar menú quan es clica un enllaç
                document.querySelectorAll('.nav-menu ul li a').forEach(link => {
                    link.addEventListener('click', function() {
                        mobileMenuToggle.classList.remove('active');
                        navMenu.classList.remove('show');
                    });
                });

                // Tancar menú quan es clica fora
                document.addEventListener('click', function(e) {
                    if (!mobileMenuToggle.contains(e.target) && !navMenu.contains(e.target)) {
                        mobileMenuToggle.classList.remove('active');
                        navMenu.classList.remove('show');
                    }
                });
            }
        });
    </script>
    <script src="../js/language.js"></script>
</body>
</html>