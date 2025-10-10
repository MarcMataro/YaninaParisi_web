<?php
// Detectar la pàgina actual
$current_page = basename($_SERVER['PHP_SELF']);
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
        <a href="dashboard.php" class="nav-item <?php echo ($current_page == 'dashboard.php') ? 'active' : ''; ?>">
            <i class="fas fa-home"></i>
            <span>Dashboard</span>
        </a>
        <a href="gpacients.php" class="nav-item <?php echo ($current_page == 'gpacients.php') ? 'active' : ''; ?>">
            <i class="fas fa-users"></i>
            <span>Pacientes</span>
        </a>
        <a href="gsessions.php" class="nav-item <?php echo ($current_page == 'gsessions.php') ? 'active' : ''; ?>">
            <i class="fas fa-calendar-alt"></i>
            <span>Sesiones</span>
        </a>
        <a href="gblog.php" class="nav-item <?php echo ($current_page == 'blog.php') ? 'active' : ''; ?>">
            <i class="fas fa-blog"></i>
            <span>Blog</span>
        </a>
        <a href="gseo.php" class="nav-item <?php echo ($current_page == 'gseo.php') ? 'active' : ''; ?>">
            <i class="fas fa-search"></i>
            <span>SEO</span>
        </a>
        <a href="facturacion.php" class="nav-item <?php echo ($current_page == 'facturacion.php') ? 'active' : ''; ?>">
            <i class="fas fa-file-invoice-dollar"></i>
            <span>Facturación</span>
        </a>
        <a href="configuracion.php" class="nav-item <?php echo ($current_page == 'configuracion.php') ? 'active' : ''; ?>">
            <i class="fas fa-cog"></i>
            <span>Configuración</span>
        </a>
        <a href="documentation.php" class="nav-item <?php echo ($current_page == 'documentation.php') ? 'active' : ''; ?>">
            <i class="fas fa-book"></i>
            <span>Documentació</span>
        </a>
    </nav>
    
    <div class="sidebar-footer">
        <a href="logout.php" class="logout-btn">
            <i class="fas fa-sign-out-alt"></i>
            <span>Cerrar Sesión</span>
        </a>
    </div>
</aside>
