<?php
// Detectar la pàgina actual
$current_page = basename($_SERVER['PHP_SELF']);
$user_role = $_SESSION['user_role'] ?? 'viewer';
?>
<!-- Sidebar -->
<aside class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <img src="../img/Logo.png" alt="Yanina Parisi" class="sidebar-logo">
        <h2>Panel de Control</h2>
        <button class="sidebar-toggle" id="sidebarToggle">
            <i class="fas fa-times"></i>
        </button>
    </div>
    
    <nav class="sidebar-nav">
        <?php if ($user_role !== 'seo_manager' && $user_role !== 'editor'): ?>
        <a href="dashboard.php" class="nav-item <?php echo ($current_page == 'dashboard.php') ? 'active' : ''; ?>">
            <i class="fas fa-home"></i>
            <span>Dashboard</span>
        </a>
        <!-- Temporalment ocult - Descomentar per activar
        <a href="gpacients.php" class="nav-item <?php echo ($current_page == 'gpacients.php') ? 'active' : ''; ?>">
            <i class="fas fa-users"></i>
            <span>Pacientes</span>
        </a>
        -->
        <!-- Temporalment ocult - Descomentar per activar
        <a href="gsessions.php" class="nav-item <?php echo ($current_page == 'gsessions.php') ? 'active' : ''; ?>">
            <i class="fas fa-calendar-alt"></i>
            <span>Sesiones</span>
        </a>
        -->
        <a href="gprofessionals.php" class="nav-item <?php echo ($current_page == 'gprofessionals.php') ? 'active' : ''; ?>">
            <i class="fas fa-user-md"></i>
            <span>Profesionales</span>
        </a>
        <?php endif; ?>

        <?php if ($user_role !== 'seo_manager'): ?>
        <a href="gblog.php" class="nav-item <?php echo ($current_page == 'gblog.php') ? 'active' : ''; ?>">
            <i class="fas fa-blog"></i>
            <span>Blog</span>
        </a>
        <?php endif; ?>

        <?php if ($user_role !== 'seo_manager'): ?>
        <a href="gfaq.php" class="nav-item <?php echo ($current_page == 'gfaq.php') ? 'active' : ''; ?>">
            <i class="fas fa-question-circle"></i>
            <span>FAQ's</span>
        </a>
        <?php endif; ?>

        <?php if ($user_role !== 'seo_manager'): ?>
        <a href="gmedia.php" class="nav-item <?php echo ($current_page == 'gmedia.php') ? 'active' : ''; ?>">
            <i class="fas fa-images"></i>
            <span>Media</span>
        </a>
        <?php endif; ?>

        <?php if ($user_role !== 'editor'): ?>
        <a href="gseo.php" class="nav-item <?php echo ($current_page == 'gseo.php') ? 'active' : ''; ?>">
            <i class="fas fa-search"></i>
            <span>SEO</span>
        </a>
        <?php endif; ?>

        <?php if ($user_role !== 'seo_manager' && $user_role !== 'editor'): ?>
        <a href="gressenyes.php" class="nav-item <?php echo ($current_page == 'gressenyes.php') ? 'active' : ''; ?>">
            <i class="fas fa-star"></i>
            <span>Reseñas</span>
        </a>
        <!-- Temporalment ocult - Descomentar per activar
        <a href="facturacion.php" class="nav-item <?php echo ($current_page == 'facturacion.php') ? 'active' : ''; ?>">
            <i class="fas fa-file-invoice-dollar"></i>
            <span>Facturación</span>
        </a>
        -->
        <a href="gtarifas.php" class="nav-item <?php echo ($current_page == 'tarifas.php') ? 'active' : ''; ?>">
            <i class="fas fa-calculator"></i>
            <span>Tarifas</span>
        </a>
        <a href="configuracion.php" class="nav-item <?php echo ($current_page == 'configuracion.php') ? 'active' : ''; ?>">
            <i class="fas fa-cog"></i>
            <span>Configuración</span>
        </a>
        <a href="documentation.php" class="nav-item <?php echo ($current_page == 'documentation.php') ? 'active' : ''; ?>">
            <i class="fas fa-book"></i>
            <span>Documentación</span>
        </a>
        <?php endif; ?>
    </nav>
    
    <div class="sidebar-footer">
        <a href="logout.php" class="logout-btn">
            <i class="fas fa-sign-out-alt"></i>
            <span>Cerrar Sesión</span>
        </a>
    </div>
</aside>
