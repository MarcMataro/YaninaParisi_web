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
    
    // Validación detallada
    $errors = [];
    if (empty($name)) $errors[] = 'Nombre completo';
    if (empty($email)) $errors[] = 'Correo electrónico';
    if (empty($message)) $errors[] = 'Mensaje';
    if (!$privacy) $errors[] = 'Aceptar la política de privacidad';
    
    if (empty($errors)) {
        $lang = getCurrentLanguage();
        
        // Cargar PHPMailer
        require_once __DIR__ . '/../classes/PHPMailer.php';
        $config_file = __DIR__ . '/../_configs/smtp_config.php';
        
        try {
            // 1. Correo al cliente
            $mail_client = new PHPMailer($config_file);
            
            $client_subject = $lang === 'ca' ? 'Gràcies per contactar amb mi - Yanina Parisi' : 'Gracias por contactar conmigo - Yanina Parisi';
            $client_message = $lang === 'ca' 
                ? "Hola " . $name . ",\n\nGràcies per posar-te en contacte amb mi. He rebut el teu missatge i et respondré el més aviat possible.\n\nEstic aquí per ajudar-te en el teu procés de creixement personal i benestar emocional.\n\nUna abraçada,\nYanina Parisi\nPsicòloga\n\ninfo@yaninaparisi.com"
                : "Hola " . $name . ",\n\nGracias por ponerte en contacto conmigo. He recibido tu mensaje y te responderé lo antes posible.\n\nEstoy aquí para ayudarte en tu proceso de crecimiento personal y bienestar emocional.\n\nUn abrazo,\nYanina Parisi\nPsicóloga\n\ninfo@yaninaparisi.com";
            
            $mail_client->addAddress($email, $name);
            $mail_client->Subject = $client_subject;
            $mail_client->Body = $client_message;
            
            $client_sent = $mail_client->send();
            
            if (!$client_sent) {
                throw new Exception("Error correo cliente: " . $mail_client->ErrorInfo);
            }
            
            // 2. Correo a la psicóloga
            $mail_psychologist = new PHPMailer($config_file);
            
            $psychologist_message = "Nueva consulta recibida:\n\n";
            $psychologist_message .= "Nombre: " . $name . "\n";
            $psychologist_message .= "Email: " . $email . "\n";
            $psychologist_message .= "Mensaje:\n" . $message . "\n\n";
            $psychologist_message .= "Idioma del formulario: " . strtoupper($lang);
            
            $mail_psychologist->addAddress('info@yaninaparisi.com', 'Yanina Parisi');
            $mail_psychologist->addReplyTo($email, $name);
            $mail_psychologist->Subject = "Nueva consulta desde el formulario de contacto";
            $mail_psychologist->Body = $psychologist_message;
            
            $psychologist_sent = $mail_psychologist->send();
            
            if (!$psychologist_sent) {
                throw new Exception("Error correo psicóloga: " . $mail_psychologist->ErrorInfo);
            }
            
            $message_sent = true;
            
        } catch (Exception $e) {
            $message_error = true;
            $error_message = "Error: " . $e->getMessage();
            error_log("PHPMailer: " . $e->getMessage());
        }
    } else {
        $message_error = true;
        $error_message = "Faltan los siguientes campos: " . implode(', ', $errors);
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
                    <span><?php echo isset($error_message) ? $error_message : 'Error en el formulario'; ?></span>
                </div>
            <?php endif; ?>

            <div class="contact-grid">
                <div class="contact-form-section" id="contact-form">
                    <div class="form-header">
                            <h2>Solicita una cita</h2>
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
            }
        });
    </script>
    <script src="../js/language.js"></script>
</body>
</html>
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