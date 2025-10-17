<!-- Capçalera -->
<header>
    <div class="header-container">
        <div class="logo">
            <a href="#inici">
                <img src="../img/logo.png" class="logo-nav" alt="Yanina Parisi" placeholder="Logo Yanina Parisi">
            </a>
        </div>
        
        <nav class="nav-menu">
            <ul>
                <li><a href="index.php">Inici</a></li>
                <li><a href="clinica.php">Consulta</a></li>
                <li><a href="#serveis-especials" class="love-link">Dues ànimes</a></li>
                <li><a href="blog.php">Blog</a></li>
                <li><a href="sobremi.php">Sobre mi</a></li>
                <li><a href="contacta.php">Contacta</a></li>
                <!-- Selector d'idiomes dins del menú mòbil -->
                <li class="mobile-language-selector">
                    <div class="mobile-language-buttons">
                        <button class="lang-btn mobile-lang <?php echo (getCurrentLanguage() == 'ca') ? 'active' : ''; ?>" data-lang="ca">
                            <img src="../img/cat.png" alt="Català" class="lang-flag">
                            <span class="lang-text">Català</span>
                        </button>
                        <button class="lang-btn mobile-lang <?php echo (getCurrentLanguage() == 'es') ? 'active' : ''; ?>" data-lang="es">
                            <img src="../img/esp.png" alt="Castellano" class="lang-flag">
                            <span class="lang-text">Español</span>
                        </button>
                    </div>
                </li>
            </ul>
        </nav>
        
        <div class="header-actions">
            <div class="language-selector">
                <button class="lang-btn <?php echo (getCurrentLanguage() == 'ca') ? 'active' : ''; ?>" data-lang="ca">
                    <img src="../img/cat.png" alt="Català" class="lang-flag">
                    <span class="lang-text">CA</span>
                </button>
                <button class="lang-btn <?php echo (getCurrentLanguage() == 'es') ? 'active' : ''; ?>" data-lang="es">
                    <img src="../img/esp.png" alt="Castellano" class="lang-flag">
                    <span class="lang-text">ES</span>
                </button>
            </div>
        </div>
        
        <div class="header-right">
            <!-- Botó hamburguesa per mòbil -->
            <button class="mobile-menu-toggle" aria-label="Toggle menu">
                <span></span>
                <span></span>
                <span></span>
            </button>
        </div>
    </div>
</header>