<?php 
// Inicialitzar sessio si no esta iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Processar canvi d'idioma PRIMER (exactament igual que index.php)
if (isset($_GET['lang'])) {
    if (in_array($_GET['lang'], array('ca', 'es'))) {
        $_SESSION['language'] = $_GET['lang'];
    }
    // Redirigir per evitar reenviar formulari
    $redirect_url = strtok($_SERVER["REQUEST_URI"], '?');
    header('Location: ' . $redirect_url);
    exit;
}

// Incluir sistema de traduccio
include 'includes/lang.php';

// Processar el formulari si s'ha enviat
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/estils.css">
    <link rel="stylesheet" href="css/contacte.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Libre+Baskerville:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet">
</head>
<body>
    <?php include 'includes/navigation.php'; ?>

    <!-- Hero Section -->
    <section class="contact-hero">
        <div class="container">
            <div class="contact-hero-content">
                <h1><?php echo getCurrentLanguage() == 'ca' ? 'Contacta amb mi' : 'Contacta conmigo'; ?></h1>
                <p class="contact-hero-subtitle">
                    <?php echo getCurrentLanguage() == 'ca' ? 
                        'Estic aqui per ajudar-te. La primera consulta es completament gratuita i sense compromis.' : 
                        'Estoy aqui para ayudarte. La primera consulta es completamente gratuita y sin compromiso.'; ?>
                </p>
            </div>
        </div>
    </section>

    <!-- Main Content -->
    <section class="contact-main">
        <div class="container">
            <?php if ($message_sent): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <span><?php echo getCurrentLanguage() == 'ca' ? 'Gracies per contactar-me!' : 'Gracias por contactarme!'; ?></span>
                </div>
            <?php endif; ?>
            
            <?php if ($message_error): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <span><?php echo getCurrentLanguage() == 'ca' ? 'Completa tots els camps obligatoris.' : 'Completa todos los campos obligatorios.'; ?></span>
                </div>
            <?php endif; ?>

            <div class="contact-grid">
                <div class="contact-form-section" id="contact-form">
                    <div class="form-header">
                        <h2><?php echo getCurrentLanguage() == 'ca' ? 'Demana la teva cita' : 'Pide tu cita'; ?></h2>
                        <p><?php echo getCurrentLanguage() == 'ca' ? 'Completa el formulari per contactar.' : 'Completa el formulario para contactar.'; ?></p>
                    </div>
                    
                    <form class="contact-form" method="POST" action="">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="name">
                                    <i class="fas fa-user"></i>
                                    <?php echo getCurrentLanguage() == 'ca' ? 'Nom complet *' : 'Nombre completo *'; ?>
                                </label>
                                <input type="text" id="name" name="name" required 
                                       placeholder="<?php echo getCurrentLanguage() == 'ca' ? 'El teu nom i cognoms' : 'Tu nombre y apellidos'; ?>"
                                       value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="email">
                                    <i class="fas fa-envelope"></i>
                                    <?php echo getCurrentLanguage() == 'ca' ? 'Correu electronic *' : 'Correo electronico *'; ?>
                                </label>
                                <input type="email" id="email" name="email" required 
                                       placeholder="<?php echo getCurrentLanguage() == 'ca' ? 'exemple@correu.com' : 'ejemplo@correo.com'; ?>"
                                       value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="message">
                                <i class="fas fa-comment"></i>
                                <?php echo getCurrentLanguage() == 'ca' ? 'Missatge *' : 'Mensaje *'; ?>
                            </label>
                            <textarea id="message" name="message" required 
                                      placeholder="<?php echo getCurrentLanguage() == 'ca' ? 'El teu missatge...' : 'Tu mensaje...'; ?>"><?php echo isset($_POST['message']) ? htmlspecialchars($_POST['message']) : ''; ?></textarea>
                        </div>
                        
                        <div class="form-group form-checkbox">
                            <label for="privacy" class="checkbox-label">
                                <input type="checkbox" id="privacy" name="privacy" required>
                                <span class="checkmark"></span>
                                <?php echo getCurrentLanguage() == 'ca' ? 'Accepto la politica de privacitat *' : 'Acepto la politica de privacidad *'; ?>
                            </label>
                        </div>
                        
                        <button type="submit" class="btn btn-primary btn-submit">
                            <i class="fas fa-paper-plane"></i>
                            <?php echo getCurrentLanguage() == 'ca' ? 'Enviar missatge' : 'Enviar mensaje'; ?>
                        </button>
                    </form>
                </div>
                
                <div class="contact-info-section">
                    <div class="contact-info-header">
                        <h3><?php echo getCurrentLanguage() == 'ca' ? 'Informacio de contacte' : 'Informacion de contacto'; ?></h3>
                    </div>
                    
                    <div class="contact-methods">
                        <div class="contact-method">
                            <div class="contact-icon">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <div class="contact-details">
                                <h4><?php echo getCurrentLanguage() == 'ca' ? 'Correu electronic' : 'Correo electronico'; ?></h4>
                                <p>info@yaninaparisi.com</p>
                            </div>
                        </div>
                        
                        <div class="contact-method">
                            <div class="contact-icon">
                                <i class="fas fa-phone"></i>
                            </div>
                            <div class="contact-details">
                                <h4><?php echo getCurrentLanguage() == 'ca' ? 'Telefon' : 'Telefono'; ?></h4>
                                <p>+34 XXX XXX XXX</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>

    <script>
        // Script per al selector d'idioma (exactament igual que index.php)
        function changeLanguage(lang) {
            console.log('Canviant idioma a:', lang);
            window.location.href = window.location.pathname + '?lang=' + lang;
        }
        
        document.addEventListener('DOMContentLoaded', function() {
            console.log('DOM carregat en contacta.php');
            
            // Selector d'idioma - exactament com index.php
            document.querySelectorAll('.lang-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const lang = this.getAttribute('data-lang');
                    console.log('Boto clickat, idioma:', lang);
                    
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
                    
                    changeLanguage(lang);
                });
            });
            
            // Script per a la navegacio suau
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function (e) {
                    e.preventDefault();
                    const target = document.querySelector(this.getAttribute('href'));
                    if (target) {
                        target.scrollIntoView({
                            behavior: 'smooth'
                        });
                    }
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

        // Script per l'efecte scroll de la navegacio
        window.addEventListener('scroll', function() {
            const header = document.querySelector('header');
            if (header) {
                if (window.scrollY > 50) {
                    header.classList.add('scrolled');
                } else {
                    header.classList.remove('scrolled');
                }
            }
        });
    </script>
</body>
</html>