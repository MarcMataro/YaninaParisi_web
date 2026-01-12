<?php
/**
 * Dashboard - Panel de Control
 * 
 * Muestra estadísticas, sesiones del día y accesos rápidos.
 * Obtiene datos reales de la base de datos.
 * 
 * @author Marc Mataró
 * @version 2.0.0
 */

session_start();

// Manejo de inicio de sesión enviado desde la pantalla de login
require_once __DIR__ . '/../classes/connexio.php';
require_once __DIR__ . '/../classes/usuaris_panell.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['username'], $_POST['password'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    try {
        $pdo = Connexio::getInstance()->getConnexio();
    } catch (Exception $e) {
        // Si no hi ha connexió, redirigir al login amb error
        header('Location: index.php?error=1');
        exit;
    }

    $usersModel = new UsuarisPanell($pdo);
    $userRow = $usersModel->buscarPerEmail($username);

    if ($userRow && isset($userRow['password_hash']) && password_verify($password, $userRow['password_hash'])) {
        // Comprobaciones adicionales: activo y no bloqueado
        if (isset($userRow['activo']) && !$userRow['activo']) {
            header('Location: index.php?error=1');
            exit;
        }
        if (isset($userRow['bloqueado']) && $userRow['bloqueado']) {
            header('Location: index.php?error=1');
            exit;
        }

        // Autenticación correcta: inicializar sesión
        $_SESSION['logged_in'] = true;
        $_SESSION['user_id'] = $userRow['id_usuario'];
        $_SESSION['user_email'] = $userRow['email'];
        $_SESSION['user_name'] = trim(($userRow['nombre'] ?? '') . ' ' . ($userRow['apellidos'] ?? ''));
        $_SESSION['user_role'] = $userRow['rol'] ?? 'editor';

        // Actualizar último acceso
        try {
            $stmt = $pdo->prepare("UPDATE usuarios_panel SET ultimo_acceso = NOW(), intentos_login = 0 WHERE id_usuario = :id");
            $stmt->execute([':id' => $userRow['id_usuario']]);
        } catch (Exception $e) {
            // No crítico
        }

        // Redirigir al dashboard (GET) para evitar reenvío de formulario
        if (($userRow['rol'] ?? '') === 'seo_manager') {
            header('Location: gseo.php');
        } elseif (($userRow['rol'] ?? '') === 'editor') {
            header('Location: gblog.php');
        } else {
            header('Location: dashboard.php');
        }
        exit;
    }

    // Si no autenticó, llevar al login con error
    header('Location: index.php?error=1');
    exit;
}

// Verificar autenticación para acceso directo por GET
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: index.php');
    exit;
}

// Redirect SEO Manager to SEO page
if (($_SESSION['user_role'] ?? '') === 'seo_manager') {
    header('Location: gseo.php');
    exit;
}

// Redirect Editor to Blog page
if (($_SESSION['user_role'] ?? '') === 'editor') {
    header('Location: gblog.php');
    exit;
}

// Incluir clases necesarias
require_once '../classes/connexio.php';

// Obtener conexión a la base de datos
try {
    $connexio = Connexio::getInstance();
    $pdo = $connexio->getConnexio();
} catch (Exception $e) {
    die("Error de conexión: " . $e->getMessage());
}

// Configurar locale para fechas en español
setlocale(LC_TIME, 'es_ES.UTF-8', 'es_ES', 'spanish');

// Obtener nombre del día en español
$dies = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
$mesos = ['', 'enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
$diaSetmana = $dies[date('w')];
$dia = date('d');
$mes = $mesos[(int)date('m')];
$any = date('Y');
$dataAvui = "$diaSetmana, $dia de $mes de $any";
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Panel de Control</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@7.0.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Libre+Baskerville:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="css/dashboard-calendar.css?v=<?php echo time(); ?>">
</head>
<body>
    <link rel="icon" type="image/png" sizes="32x32" href="../img/Logo32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="../img/Logo16.png">
    <?php include 'includes/sidebar.php'; ?>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Bar -->
        <header class="top-bar">
            <div class="top-bar-left">
                <button class="menu-toggle" id="menuToggle">
                    <i class="fas fa-bars"></i>
                </button>
                <div class="top-bar-info">
                    <?php $__firstName = trim($_SESSION['user_name'] ?? 'Usuario'); $__firstName = $__firstName !== '' ? explode(' ', $__firstName)[0] : 'Usuario'; ?>
                    <h1>Bienvenida, <?php echo htmlspecialchars($__firstName); ?></h1>
                    <p class="date-today"><?php echo $dataAvui; ?></p>
                </div>
            </div>
            <div class="top-bar-right">
                <div class="user-profile">
                    <img src="../img/Logo.png" alt="Profile" class="profile-img">
                    <span class="profile-name"><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Usuario'); ?></span>
                </div>
            </div>
        </header>

    <!-- Dashboard Content -->
    <div class="content-wrapper">
            <!-- Stats Cards -->
            <section class="stats-section">
                <?php
                // Obtener estadísticas de contenido web
                try {
                    $stmt = $pdo->query("SELECT COUNT(*) as total FROM blog_entrades WHERE estat = 'publicat'");
                    $totalBlog = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
                    
                    $stmt = $pdo->query("SELECT COUNT(*) as total FROM faqs WHERE activa = 1");
                    $totalFAQs = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
                    
                    $stmt = $pdo->query("SELECT COUNT(*) as total FROM professionals WHERE actiu = 1");
                    $totalProfessionals = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
                    
                    $stmt = $pdo->query("SELECT COUNT(*) as total FROM ressenyes WHERE estat_moderacio = 'Aprovada'");
                    $totalRessenyes = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
                } catch (Exception $e) {
                    $totalBlog = 0;
                    $totalFAQs = 0;
                    $totalProfessionals = 0;
                    $totalRessenyes = 0;
                }
                ?>
                <div class="stat-card">
                    <div class="stat-icon patients">
                        <i class="fas fa-blog"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $totalBlog; ?></h3>
                        <p>Entradas de Blog</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon today">
                        <i class="fas fa-question-circle"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $totalFAQs; ?></h3>
                        <p>FAQs Activas</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon week">
                        <i class="fas fa-user-md"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $totalProfessionals; ?></h3>
                        <p>Profesionales</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon pending">
                        <i class="fas fa-star"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $totalRessenyes; ?></h3>
                        <p>Reseñas Aprobadas</p>
                    </div>
                </div>
            </section>

            <!-- Main Grid with Aside -->
            <div class="dashboard-grid-with-aside">
                <!-- Main Content Area -->
                <div class="main-content-area">
                    <!-- Recent Blog Posts -->
                    <section class="card day-calendar-card">
                        <div class="card-header">
                            <h2><i class="fas fa-blog"></i> Últimas Entradas del Blog</h2>
                            <a href="gblog.php" class="btn-link"><i class="fas fa-arrow-right"></i> Ver todas</a>
                        </div>
                        <div class="day-calendar">
                            <?php
                            try {
                                $stmt = $pdo->query("
                                    SELECT id_entrada, titol_ca, titol_es, data_publicacio, estat, visualitzacions
                                    FROM blog_entrades
                                    ORDER BY data_publicacio DESC
                                    LIMIT 5
                                ");
                                $recentPosts = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                
                                if (empty($recentPosts)): 
                            ?>
                                <div class="no-appointments">
                                    <i class="fas fa-pen-nib"></i>
                                    <p>No hay entradas de blog todavía</p>
                                    <a href="gblog.php" class="action-btn-main">
                                        <span>Crear nueva entrada</span>
                                    </a>
                                </div>
                            <?php else: ?>
                                <div class="blog-posts-list">
                                    <?php foreach ($recentPosts as $post): ?>
                                    <div class="blog-post-item">
                                        <div class="post-header">
                                            <h3 class="post-title"><?php echo htmlspecialchars($post['titol_es'] ?: $post['titol_ca']); ?></h3>
                                            <div class="post-actions">
                                                <a href="gblog.php?id=<?php echo $post['id_entrada']; ?>" class="btn-icon" title="Editar">✎</a>
                                            </div>
                                        </div>
                                        <div class="post-meta">
                                            <span><?php echo date('d/m/Y', strtotime($post['data_publicacio'])); ?></span>
                                            <span>•</span>
                                            <span><?php echo number_format($post['visualitzacions']); ?> visitas</span>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php 
                                endif;
                            } catch (Exception $e) {
                                echo '<div class="no-appointments"><p>Error al cargar entradas del blog</p></div>';
                            }
                            ?>
                        </div>
                    </section>
                </div>

                <!-- Sidebar Derecha (Aside) -->
                <aside class="dashboard-aside">
                    <!-- Quick Actions -->
                    <section class="card quick-actions-card">
                        <div class="card-header">
                            <h2><i class="fas fa-bolt"></i> Acciones Rápidas</h2>
                        </div>
                        <div class="quick-actions-aside">
                            <a href="gblog.php" class="action-btn-aside">
                                <i class="fas fa-plus-circle"></i>
                                <span>Nueva Entrada Blog</span>
                            </a>
                            <a href="gfaq.php" class="action-btn-aside">
                                <i class="fas fa-question-circle"></i>
                                <span>Gestionar FAQs</span>
                            </a>
                            <a href="gmedia.php" class="action-btn-aside">
                                <i class="fas fa-images"></i>
                                <span>Biblioteca Media</span>
                            </a>
                            <a href="gprofessionals.php" class="action-btn-aside">
                                <i class="fas fa-user-md"></i>
                                <span>Profesionales</span>
                            </a>
                            <a href="gressenyes.php" class="action-btn-aside">
                                <i class="fas fa-star"></i>
                                <span>Reseñas</span>
                            </a>
                            <a href="gseo.php" class="action-btn-aside">
                                <i class="fas fa-search"></i>
                                <span>SEO</span>
                            </a>
                        </div>
                    </section>

                    <!-- Recent Reviews -->
                    <section class="card upcoming-card">
                        <div class="card-header">
                            <h2><i class="fas fa-star"></i> Reseñas Recientes</h2>
                        </div>
                        <div class="upcoming-list">
                            <?php
                            try {
                                $stmt = $pdo->query("
                                    SELECT nom_client, puntuacio, data_creacio, estat_moderacio
                                    FROM ressenyes
                                    ORDER BY data_creacio DESC
                                    LIMIT 5
                                ");
                                $recentReviews = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                
                                if (empty($recentReviews)): 
                            ?>
                                <div class="empty-state">
                                    <i class="fas fa-star-half-alt"></i>
                                    <p>No hay reseñas todavía</p>
                                </div>
                            <?php else: ?>
                                <?php foreach ($recentReviews as $review): ?>
                                <div class="upcoming-item">
                                    <div class="upcoming-date">
                                        <div class="date-day"><?php echo date('d', strtotime($review['data_creacio'])); ?></div>
                                        <div class="date-month"><?php echo strtoupper(substr($mesos[(int)date('m', strtotime($review['data_creacio']))], 0, 3)); ?></div>
                                    </div>
                                    <div class="upcoming-info">
                                        <h4><?php echo htmlspecialchars($review['nom_client']); ?></h4>
                                        <span class="upcoming-time">
                                            <?php for ($i = 0; $i < 5; $i++): ?>
                                                <?php if ($i < $review['puntuacio']): ?>
                                                    <i class="fas fa-star" style="color: #ffc107;"></i>
                                                <?php else: ?>
                                                    <i class="far fa-star" style="color: #ffc107;"></i>
                                                <?php endif; ?>
                                            <?php endfor; ?>
                                        </span>
                                    </div>
                                    <div class="upcoming-price">
                                        <?php if ($review['estat_moderacio'] === 'Aprovada'): ?>
                                            <i class="fas fa-check-circle" style="color: #28a745;" title="Aprobada"></i>
                                        <?php elseif ($review['estat_moderacio'] === 'Pendent'): ?>
                                            <i class="fas fa-clock" style="color: #ffc107;" title="Pendiente"></i>
                                        <?php else: ?>
                                            <i class="fas fa-times-circle" style="color: #dc3545;" title="Rechazada"></i>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            <?php 
                                endif;
                            } catch (Exception $e) {
                                echo '<div class="empty-state"><p>Error al cargar reseñas</p></div>';
                            }
                            ?>
                        </div>
                    </section>
                </aside>
            </div>
        </div>
    </div>



    <script src="js/dashboard.js"></script>
</body>
</html>
