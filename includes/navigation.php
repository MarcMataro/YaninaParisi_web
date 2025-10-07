<!-- Capçalera -->
<header>
    <div class="container header-container">
        <div class="logo">
            <img src="img/logo.png" class="logo-nav" alt="Yanina Parisi" placeholder="Logo Yanina Parisi">
        </div>
        <nav>
            <ul>
                <li><a href="#inici"><?php echo t('nav_home'); ?></a></li>
                <li><a href="#serveis"><?php echo t('nav_services'); ?></a></li>
                <li><a href="#sobre-mi"><?php echo t('nav_about'); ?></a></li>
                <li><a href="#testimonis"><?php echo t('nav_testimonials'); ?></a></li>
                <li><a href="#tarifes"><?php echo t('nav_pricing'); ?></a></li>
                <li><a href="#contacte"><?php echo t('nav_contact'); ?></a></li>
            </ul>
        </nav>
        <div class="header-actions">
            <div class="language-selector">
                <button class="lang-btn <?php echo (getCurrentLanguage() == 'ca') ? 'active' : ''; ?>" onclick="changeLanguage('ca')">
                    <img src="img/cat.png" alt="Català" class="lang-flag">
                    <span class="lang-text">CA</span>
                </button>
                <button class="lang-btn <?php echo (getCurrentLanguage() == 'es') ? 'active' : ''; ?>" onclick="changeLanguage('es')">
                    <img src="img/esp.png" alt="Castellano" class="lang-flag">
                    <span class="lang-text">ES</span>
                </button>
            </div>
            <a href="#contacte" class="btn"><?php echo t('nav_appointment'); ?></a>
        </div>
    </div>
</header>