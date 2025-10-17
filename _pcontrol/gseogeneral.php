<?php

session_start();

// Verificar autenticació
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: index.php');
    exit;
}

// Carregar classe SEO Global
require_once __DIR__ . '/../classes/seo_global.php';

// Processar formulari
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'save_global') {
        try {
            $seo_global = SEO_Global::carregarConfiguracio();
            if (!$seo_global) {
                throw new Exception("No s'ha pogut carregar la configuració SEO global");
            }
            $data = [];
            if (isset($_POST['site_title_ca'])) $data['site_title_ca'] = $_POST['site_title_ca'];
            if (isset($_POST['site_title_es'])) $data['site_title_es'] = $_POST['site_title_es'];
            if (isset($_POST['site_description_ca'])) $data['site_description_ca'] = $_POST['site_description_ca'];
            if (isset($_POST['site_description_es'])) $data['site_description_es'] = $_POST['site_description_es'];
            if (isset($_POST['default_title_template_ca'])) $data['default_title_template_ca'] = $_POST['default_title_template_ca'];
            if (isset($_POST['default_title_template_es'])) $data['default_title_template_es'] = $_POST['default_title_template_es'];
            if (isset($_POST['default_meta_template_ca'])) $data['default_meta_template_ca'] = $_POST['default_meta_template_ca'];
            if (isset($_POST['default_meta_template_es'])) $data['default_meta_template_es'] = $_POST['default_meta_template_es'];
            if (isset($_POST['facebook_url'])) $data['facebook_url'] = $_POST['facebook_url'];
            if (isset($_POST['twitter_url'])) $data['twitter_url'] = $_POST['twitter_url'];
            if (isset($_POST['linkedin_url'])) $data['linkedin_url'] = $_POST['linkedin_url'];
            if (isset($_POST['instagram_url'])) $data['instagram_url'] = $_POST['instagram_url'];
            if (isset($_POST['google_business_url'])) $data['google_business_url'] = $_POST['google_business_url'];
            if (isset($_POST['og_site_name'])) $data['og_site_name'] = $_POST['og_site_name'];
            if (isset($_POST['default_og_image'])) $data['default_og_image'] = $_POST['default_og_image'];
            if (isset($_POST['twitter_site'])) $data['twitter_site'] = $_POST['twitter_site'];
            if (isset($_POST['twitter_creator'])) $data['twitter_creator'] = $_POST['twitter_creator'];
            if (isset($_POST['default_twitter_image'])) $data['default_twitter_image'] = $_POST['default_twitter_image'];
            if (isset($_POST['default_meta_robots'])) $data['default_meta_robots'] = $_POST['default_meta_robots'];
            if (isset($_POST['google_site_verification'])) $data['google_site_verification'] = $_POST['google_site_verification'];
            if (isset($_POST['bing_verification'])) $data['bing_verification'] = $_POST['bing_verification'];
            if (isset($_POST['google_analytics_id'])) $data['google_analytics_id'] = $_POST['google_analytics_id'];
            if (isset($_POST['google_tag_manager_id'])) $data['google_tag_manager_id'] = $_POST['google_tag_manager_id'];
            if (isset($_POST['hreflang_default'])) $data['hreflang_default'] = $_POST['hreflang_default'];
            if (isset($_POST['default_priority'])) $data['default_priority'] = $_POST['default_priority'];
            if (isset($_POST['default_changefreq'])) $data['default_changefreq'] = $_POST['default_changefreq'];
            $seo_global->actualitzarMultiplesCamps($data);
            $_SESSION['seo_saved'] = true;
            header('Location: gseogeneral.php?saved=1');
            exit;
        } catch (Exception $e) {
            $_SESSION['seo_error'] = $e->getMessage();
            header('Location: gseogeneral.php?error=1');
            exit;
        }
    }
}

// Carregar configuració SEO Global
try {
    $seo_global = SEO_Global::carregarConfiguracio();
} catch (Exception $e) {
    $seo_global = null;
    $error_message = $e->getMessage();
}
$saved = isset($_GET['saved']) && $_GET['saved'] == '1';
$error = isset($_GET['error']) && $_GET['error'] == '1';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuració SEO Global</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@7.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="css/gseo.css">
</head>
<body>
<?php include 'includes/sidebar.php'; ?>
<div class="main-content">
    <header class="top-bar">
        <div class="top-bar-left">
            <h1><i class="fas fa-cogs"></i> Configuració SEO Global</h1>
        </div>
    </header>
    <div class="seo-container">
        <?php if ($saved): ?>
        <div class="alert alert-success"><i class="fas fa-check-circle"></i> Configuració guardada correctament</div>
        <?php endif; ?>
        <?php if ($error): ?>
        <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> Error: <?php echo htmlspecialchars($_SESSION['seo_error'] ?? 'Error desconegut'); ?></div>
        <?php unset($_SESSION['seo_error']); endif; ?>
        <!-- $seo_global is set and valid, debug output removed for production -->
        <?php if ($seo_global): ?>
        <form action="gseogeneral.php" method="POST" class="seo-form">
            <input type="hidden" name="action" value="save_global">
            <!-- 1. Site-Wide Meta Tags -->
            <section class="seo-section">
                <div class="section-header">
                    <div>
                        <h2><i class="fas fa-globe-americas"></i> Meta Tags Globales del Sitio</h2>
                        <p class="section-description">Títulos y descripciones generales que se utilizan en todo el sitio web</p>
                    </div>
                    <div class="section-badge badge-primary">Configuración Global</div>
                </div>
                <div class="form-row">
                    <div class="form-group-bilingual">
                        <div class="language-field">
                            <label>
                                <i class="fas fa-heading"></i> Título del Sitio (Català)
                                <span class="char-counter">Máx 70 caracteres</span>
                            </label>
                            <input type="text" name="site_title_ca" maxlength="70"
                                   value="<?php echo htmlspecialchars($seo_global->getSiteTitle('ca') ?? ''); ?>"
                                   placeholder="Psicòloga <?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Yanina Parisi'); ?> | Barcelona">
                            <small><i class="fas fa-info-circle"></i> Título principal del sitio en catalán</small>
                        </div>
                        <div class="language-field">
                            <label>
                                <i class="fas fa-heading"></i> Título del Sitio (Español)
                                <span class="char-counter">Máx 70 caracteres</span>
                            </label>
                            <input type="text" name="site_title_es" maxlength="70"
                                   value="<?php echo htmlspecialchars($seo_global->getSiteTitle('es') ?? ''); ?>"
                                   placeholder="Psicóloga <?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Yanina Parisi'); ?> | Barcelona">
                            <small><i class="fas fa-info-circle"></i> Título principal del sitio en español</small>
                        </div>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group-bilingual">
                        <div class="language-field">
                            <label>
                                <i class="fas fa-align-left"></i> Descripción del Sitio (Català)
                                <span class="char-counter">Máx 160 caracteres</span>
                            </label>
                            <textarea name="site_description_ca" rows="3" maxlength="160"
                                      placeholder="Descripció general del lloc web en català"><?php echo htmlspecialchars($seo_global->getSiteDescription('ca') ?? ''); ?></textarea>
                            <small><i class="fas fa-info-circle"></i> Descripción general del sitio en catalán</small>
                        </div>
                        <div class="language-field">
                            <label>
                                <i class="fas fa-align-left"></i> Descripción del Sitio (Español)
                                <span class="char-counter">Máx 160 caracteres</span>
                            </label>
                            <textarea name="site_description_es" rows="3" maxlength="160"
                                      placeholder="Descripción general del sitio web en español"><?php echo htmlspecialchars($seo_global->getSiteDescription('es') ?? ''); ?></textarea>
                            <small><i class="fas fa-info-circle"></i> Descripción general del sitio en español</small>
                        </div>
                    </div>
                </div>
            </section>
            <!-- 2. Default Meta Templates -->
            <section class="seo-section">
                <div class="section-header">
                    <div>
                        <h2><i class="fas fa-file-alt"></i> Plantillas por Defecto</h2>
                        <p class="section-description">Plantillas que se usan automáticamente para generar títulos y descripciones de páginas. Usa {page} como variable.</p>
                    </div>
                    <div class="section-badge badge-info">Dinámico</div>
                </div>
                <div class="form-row">
                    <div class="form-group-bilingual">
                        <div class="language-field">
                            <label>
                                <i class="fas fa-code"></i> Plantilla Título (Català)
                            </label>
                            <input type="text" name="default_title_template_ca" maxlength="100"
                                   value="<?php echo htmlspecialchars($seo_global->generarTitolPagina('{page}', 'ca') ?? ''); ?>"
                                   placeholder="{page} | Psicòloga <?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Yanina Parisi'); ?>">
                            <small><i class="fas fa-lightbulb"></i> Ejemplo: "Inici | Psicòloga Yanina Parisi"</small>
                        </div>
                        <div class="language-field">
                            <label>
                                <i class="fas fa-code"></i> Plantilla Título (Español)
                            </label>
                            <input type="text" name="default_title_template_es" maxlength="100"
                                   value="<?php echo htmlspecialchars($seo_global->generarTitolPagina('{page}', 'es') ?? ''); ?>"
                                   placeholder="{page} | Psicóloga <?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Yanina Parisi'); ?>">
                            <small><i class="fas fa-lightbulb"></i> Ejemplo: "Inicio | Psicóloga Yanina Parisi"</small>
                        </div>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group-bilingual">
                        <div class="language-field">
                            <label>
                                <i class="fas fa-code"></i> Plantilla Meta Description (Català)
                            </label>
                            <textarea name="default_meta_template_ca" rows="2" maxlength="160"
                                      placeholder="Plantilla con variables para descripciones"><?php echo htmlspecialchars($seo_global->generarMetaDescription([], 'ca') ?? ''); ?></textarea>
                            <small><i class="fas fa-lightbulb"></i> Puedes usar variables como {service}, {location}</small>
                        </div>
                        <div class="language-field">
                            <label>
                                <i class="fas fa-code"></i> Plantilla Meta Description (Español)
                            </label>
                            <textarea name="default_meta_template_es" rows="2" maxlength="160"
                                      placeholder="Plantilla con variables para descripciones"><?php echo htmlspecialchars($seo_global->generarMetaDescription([], 'es') ?? ''); ?></textarea>
                            <small><i class="fas fa-lightbulb"></i> Puedes usar variables como {service}, {location}</small>
                        </div>
                    </div>
                </div>
            </section>
            <!-- 4. Social Profiles Globales -->
            <section class="seo-section">
                <div class="section-header">
                    <div>
                        <h2><i class="fas fa-share-alt"></i> Perfiles de Redes Sociales</h2>
                        <p class="section-description">URLs de tus perfiles en redes sociales para Schema.org y Open Graph</p>
                    </div>
                    <div class="section-badge badge-success">Configurado</div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>
                            <i class="fab fa-facebook"></i> URL de Facebook
                        </label>
                        <input type="url" name="facebook_url"
                               value="<?php echo htmlspecialchars($seo_global->getFacebookUrl() ?? ''); ?>"
                               placeholder="https://www.facebook.com/tuPagina">
                    </div>
                    <div class="form-group">
                        <label>
                            <i class="fab fa-instagram"></i> URL de Instagram
                        </label>
                        <input type="url" name="instagram_url"
                               value="<?php echo htmlspecialchars($seo_global->getInstagramUrl() ?? ''); ?>"
                               placeholder="https://www.instagram.com/tuPerfil">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>
                            <i class="fab fa-linkedin"></i> URL de LinkedIn
                        </label>
                        <input type="url" name="linkedin_url"
                               value="<?php echo htmlspecialchars($seo_global->getLinkedInUrl() ?? ''); ?>"
                               placeholder="https://www.linkedin.com/in/tuPerfil">
                    </div>
                    <div class="form-group">
                        <label>
                            <i class="fab fa-twitter"></i> URL de Twitter/X
                        </label>
                        <input type="url" name="twitter_url"
                               value="<?php echo htmlspecialchars($seo_global->getTwitterUrl() ?? ''); ?>"
                               placeholder="https://twitter.com/tuUsuario">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>
                            <i class="fab fa-google"></i> URL de Google My Business
                        </label>
                        <input type="url" name="google_business_url"
                               value="<?php echo htmlspecialchars($seo_global->getGoogleBusinessUrl() ?? ''); ?>"
                               placeholder="https://g.page/tuNegocio">
                        <small><i class="fas fa-info-circle"></i> URL de tu ficha de Google My Business</small>
                    </div>
                </div>
            </section>
            <!-- 5. Global Open Graph -->
            <section class="seo-section">
                <div class="section-header">
                    <div>
                        <h2><i class="fab fa-facebook"></i> Open Graph Global</h2>
                        <p class="section-description">Configuración global para cuando se comparte tu web en Facebook, LinkedIn y WhatsApp</p>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>
                            <i class="fas fa-tag"></i> Nombre del Sitio (og:site_name)
                        </label>
                        <input type="text" name="og_site_name" maxlength="100"
                               value="<?php echo htmlspecialchars($seo_global->getOgSiteName() ?? ''); ?>"
                               placeholder="Consultori Psicològic Yanina Parisi">
                        <small><i class="fas fa-info-circle"></i> Nombre que aparece en todas las comparticiones</small>
                    </div>
                    <div class="form-group">
                        <label>
                            <i class="fas fa-image"></i> Imagen por Defecto (1200x630px)
                        </label>
                        <input type="url" name="default_og_image"
                               value="<?php echo htmlspecialchars($seo_global->getDefaultOgImage() ?? ''); ?>"
                               placeholder="https://www.psicologiayanina.com/img/og-default.jpg">
                        <small><i class="fas fa-info-circle"></i> Imagen que se usa cuando no hay una específica</small>
                    </div>
                </div>
            </section>
            <!-- 6. Global Twitter Card -->
            <section class="seo-section">
                <div class="section-header">
                    <div>
                        <h2><i class="fab fa-twitter"></i> Twitter Card Global</h2>
                        <p class="section-description">Configuración global para Twitter/X Cards</p>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>
                            <i class="fab fa-twitter"></i> @username del Sitio
                        </label>
                        <input type="text" name="twitter_site" maxlength="100"
                               value="<?php echo htmlspecialchars($seo_global->getTwitterSite() ?? ''); ?>"
                               placeholder="@yaninaparisi">
                        <small><i class="fas fa-info-circle"></i> Usuario de Twitter del sitio web</small>
                    </div>
                    <div class="form-group">
                        <label>
                            <i class="fas fa-user"></i> @username del Creador
                        </label>
                        <input type="text" name="twitter_creator" maxlength="100"
                               value="<?php echo htmlspecialchars($seo_global->getTwitterCreator() ?? ''); ?>"
                               placeholder="@yaninaparisi">
                        <small><i class="fas fa-info-circle"></i> Usuario personal del creador de contenido</small>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>
                            <i class="fas fa-image"></i> Imagen por Defecto Twitter (1200x675px)
                        </label>
                        <input type="url" name="default_twitter_image"
                               value="<?php echo htmlspecialchars($seo_global->getDefaultTwitterImage() ?? ''); ?>"
                               placeholder="https://www.psicologiayanina.com/img/twitter-default.jpg">
                        <small><i class="fas fa-info-circle"></i> Imagen que se usa en Twitter cuando no hay una específica</small>
                    </div>
                </div>
            </section>
            <!-- 7. Technical SEO Global -->
            <section class="seo-section">
                <div class="section-header">
                    <div>
                        <h2><i class="fas fa-cogs"></i> SEO Técnico Global</h2>
                        <p class="section-description">Códigos de verificación y herramientas de analítica</p>
                    </div>
                    <div class="section-badge badge-warning">Importante</div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>
                            <i class="fas fa-robot"></i> Meta Robots por Defecto
                        </label>
                        <input type="text" name="default_meta_robots" maxlength="100"
                               value="<?php echo htmlspecialchars($seo_global->getDefaultMetaRobots() ?? ''); ?>"
                               placeholder="index, follow">
                        <small><i class="fas fa-info-circle"></i> Directiva robots que se aplica por defecto</small>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>
                            <i class="fab fa-google"></i> Google Search Console Verification
                        </label>
                        <input type="text" name="google_site_verification"
                               value="<?php echo htmlspecialchars($seo_global->getGoogleSiteVerification() ?? ''); ?>"
                               placeholder="google-site-verification=xxxxxxxxxxxxxxxxx">
                        <small><i class="fas fa-info-circle"></i> Meta tag de verificación de Google Search Console</small>
                    </div>
                    <div class="form-group">
                        <label>
                            <i class="fas fa-search"></i> Bing Webmaster Verification
                        </label>
                        <input type="text" name="bing_verification"
                               value="<?php echo htmlspecialchars($seo_global->getBingVerification() ?? ''); ?>"
                               placeholder="msvalidate.01=xxxxxxxxxxxxxxxxx">
                        <small><i class="fas fa-info-circle"></i> Meta tag de verificación de Bing Webmaster Tools</small>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>
                            <i class="fas fa-chart-line"></i> Google Analytics ID
                        </label>
                        <input type="text" name="google_analytics_id" maxlength="50"
                               value="<?php echo htmlspecialchars($seo_global->getGoogleAnalyticsId() ?? ''); ?>"
                               placeholder="G-XXXXXXXXXX o UA-XXXXXXXXX-X">
                        <small><i class="fas fa-info-circle"></i> ID de Google Analytics 4 o Universal Analytics</small>
                    </div>
                    <div class="form-group">
                        <label>
                            <i class="fas fa-tag"></i> Google Tag Manager ID
                        </label>
                        <input type="text" name="google_tag_manager_id" maxlength="50"
                               value="<?php echo htmlspecialchars($seo_global->getGoogleTagManagerId() ?? ''); ?>"
                               placeholder="GTM-XXXXXX">
                        <small><i class="fas fa-info-circle"></i> ID de Google Tag Manager</small>
                    </div>
                </div>
            </section>
            <!-- 9. International SEO -->
            <section class="seo-section">
                <div class="section-header">
                    <div>
                        <h2><i class="fas fa-language"></i> SEO Internacional</h2>
                        <p class="section-description">Configuración de idiomas y hreflang para sitios multilingües</p>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>
                            <i class="fas fa-globe"></i> Idioma por Defecto
                        </label>
                        <select name="hreflang_default">
                            <option value="ca" <?php echo $seo_global->getHreflangDefault() === 'ca' ? 'selected' : ''; ?>>Català (ca)</option>
                            <option value="es" <?php echo $seo_global->getHreflangDefault() === 'es' ? 'selected' : ''; ?>>Español (es)</option>
                            <option value="en" <?php echo $seo_global->getHreflangDefault() === 'en' ? 'selected' : ''; ?>>English (en)</option>
                        </select>
                        <small><i class="fas fa-info-circle"></i> Idioma principal del sitio web</small>
                    </div>
                </div>
            </section>
            <!-- 10. Performance Global -->
            <section class="seo-section">
                <div class="section-header">
                    <div>
                        <h2><i class="fas fa-sitemap"></i> Sitemap Configuration</h2>
                        <p class="section-description">Valores por defecto para el archivo sitemap.xml</p>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>
                            <i class="fas fa-sort-amount-up"></i> Prioridad por Defecto
                        </label>
                        <select name="default_priority">
                            <option value="1.0" <?php echo $seo_global->getDefaultPriority() === '1.0' ? 'selected' : ''; ?>>1.0 (Máxima)</option>
                            <option value="0.8" <?php echo $seo_global->getDefaultPriority() === '0.8' ? 'selected' : ''; ?>>0.8 (Alta)</option>
                            <option value="0.6" <?php echo $seo_global->getDefaultPriority() === '0.6' ? 'selected' : ''; ?>>0.6 (Media)</option>
                            <option value="0.4" <?php echo $seo_global->getDefaultPriority() === '0.4' ? 'selected' : ''; ?>>0.4 (Baja)</option>
                            <option value="0.2" <?php echo $seo_global->getDefaultPriority() === '0.2' ? 'selected' : ''; ?>>0.2 (Mínima)</option>
                        </select>
                        <small><i class="fas fa-info-circle"></i> Prioridad que se asigna a las páginas en sitemap.xml</small>
                    </div>
                    <div class="form-group">
                        <label>
                            <i class="fas fa-clock"></i> Frecuencia de Cambio
                        </label>
                        <select name="default_changefreq">
                            <option value="always" <?php echo $seo_global->getDefaultChangefreq() === 'always' ? 'selected' : ''; ?>>Always (Siempre)</option>
                            <option value="hourly" <?php echo $seo_global->getDefaultChangefreq() === 'hourly' ? 'selected' : ''; ?>>Hourly (Cada hora)</option>
                            <option value="daily" <?php echo $seo_global->getDefaultChangefreq() === 'daily' ? 'selected' : ''; ?>>Daily (Diaria)</option>
                            <option value="weekly" <?php echo $seo_global->getDefaultChangefreq() === 'weekly' ? 'selected' : ''; ?>>Weekly (Semanal)</option>
                            <option value="monthly" <?php echo $seo_global->getDefaultChangefreq() === 'monthly' ? 'selected' : ''; ?>>Monthly (Mensual)</option>
                            <option value="yearly" <?php echo $seo_global->getDefaultChangefreq() === 'yearly' ? 'selected' : ''; ?>>Yearly (Anual)</option>
                            <option value="never" <?php echo $seo_global->getDefaultChangefreq() === 'never' ? 'selected' : ''; ?>>Never (Nunca)</option>
                        </select>
                        <small><i class="fas fa-info-circle"></i> Frecuencia estimada de actualización de páginas</small>
                    </div>
                </div>
            </section>
            <!-- Form Actions -->
            <div class="form-actions">
                <button type="button" class="btn btn-secondary" onclick="location.reload()">
                    <i class="fas fa-undo"></i> Descartar Cambios
                </button>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Guardar Configuración Global
                </button>
            </div>
        </form>
        <?php else: ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-triangle"></i>
            <span>Error al cargar la configuración SEO Global. <?php echo isset($error_message) ? htmlspecialchars($error_message) : ''; ?></span>
        </div>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
