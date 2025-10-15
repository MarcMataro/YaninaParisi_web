<?php
/**
 * Gestió SEO - Panel de Control
 * 
 * Gestiona el SEO global, on-page, off-page, tècnic i analítics del lloc web.
 * 
 * @author Marc Mataró
 * @version 3.0.0
 */

session_start();

// Verificar autenticació
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: index.php');
    exit;
}

// Carregar classes SEO
require_once __DIR__ . '/../classes/seo_global.php';
require_once __DIR__ . '/../classes/seo_onpage.php';
require_once __DIR__ . '/../classes/seo_offpage_links.php';
require_once __DIR__ . '/../classes/seo_offpage_directories.php';

// Processar formularis
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'save_global') {
        try {
            $seo_global = SEO_Global::carregarConfiguracio();
            
            if (!$seo_global) {
                throw new Exception("No s'ha pogut carregar la configuració SEO global");
            }
            
            // Preparar dades per actualitzar
            $data = [];
            
            // 1. Site-wide meta tags
            if (isset($_POST['site_title_ca'])) $data['site_title_ca'] = $_POST['site_title_ca'];
            if (isset($_POST['site_title_es'])) $data['site_title_es'] = $_POST['site_title_es'];
            if (isset($_POST['site_description_ca'])) $data['site_description_ca'] = $_POST['site_description_ca'];
            if (isset($_POST['site_description_es'])) $data['site_description_es'] = $_POST['site_description_es'];
            
            // 2. Templates
            if (isset($_POST['default_title_template_ca'])) $data['default_title_template_ca'] = $_POST['default_title_template_ca'];
            if (isset($_POST['default_title_template_es'])) $data['default_title_template_es'] = $_POST['default_title_template_es'];
            if (isset($_POST['default_meta_template_ca'])) $data['default_meta_template_ca'] = $_POST['default_meta_template_ca'];
            if (isset($_POST['default_meta_template_es'])) $data['default_meta_template_es'] = $_POST['default_meta_template_es'];
            
            // 4. Social profiles
            if (isset($_POST['facebook_url'])) $data['facebook_url'] = $_POST['facebook_url'];
            if (isset($_POST['twitter_url'])) $data['twitter_url'] = $_POST['twitter_url'];
            if (isset($_POST['linkedin_url'])) $data['linkedin_url'] = $_POST['linkedin_url'];
            if (isset($_POST['instagram_url'])) $data['instagram_url'] = $_POST['instagram_url'];
            if (isset($_POST['google_business_url'])) $data['google_business_url'] = $_POST['google_business_url'];
            
            // 5. Open Graph
            if (isset($_POST['og_site_name'])) $data['og_site_name'] = $_POST['og_site_name'];
            if (isset($_POST['default_og_image'])) $data['default_og_image'] = $_POST['default_og_image'];
            
            // 6. Twitter Card
            if (isset($_POST['twitter_site'])) $data['twitter_site'] = $_POST['twitter_site'];
            if (isset($_POST['twitter_creator'])) $data['twitter_creator'] = $_POST['twitter_creator'];
            if (isset($_POST['default_twitter_image'])) $data['default_twitter_image'] = $_POST['default_twitter_image'];
            
            // 7. Technical SEO
            if (isset($_POST['default_meta_robots'])) $data['default_meta_robots'] = $_POST['default_meta_robots'];
            if (isset($_POST['google_site_verification'])) $data['google_site_verification'] = $_POST['google_site_verification'];
            if (isset($_POST['bing_verification'])) $data['bing_verification'] = $_POST['bing_verification'];
            if (isset($_POST['google_analytics_id'])) $data['google_analytics_id'] = $_POST['google_analytics_id'];
            if (isset($_POST['google_tag_manager_id'])) $data['google_tag_manager_id'] = $_POST['google_tag_manager_id'];
            
            // 9. International SEO
            if (isset($_POST['hreflang_default'])) $data['hreflang_default'] = $_POST['hreflang_default'];
            
            // 10. Performance
            if (isset($_POST['default_priority'])) $data['default_priority'] = $_POST['default_priority'];
            if (isset($_POST['default_changefreq'])) $data['default_changefreq'] = $_POST['default_changefreq'];
            
            // Actualitzar tots els camps
            $seo_global->actualitzarMultiplesCamps($data);
            
            $_SESSION['seo_saved'] = true;
            header('Location: gseo.php?saved=1&tab=global');
            exit;
            
        } catch (Exception $e) {
            $_SESSION['seo_error'] = $e->getMessage();
            header('Location: gseo.php?error=1&tab=global');
            exit;
        }
    } elseif ($action === 'save_onpage' || $action === 'create_onpage') {
        // Crear o actualitzar pàgina SEO On-Page
        try {
            $id_pagina = $_POST['id_pagina'] ?? null;
            
            $data = [
                'url_relativa' => $_POST['url_relativa'] ?? '',
                'titulo_pagina' => $_POST['titulo_pagina'] ?? '',
                'tipo_pagina' => $_POST['tipo_pagina'] ?? 'landing',
                'title_ca' => $_POST['title_ca'] ?? '',
                'meta_description_ca' => $_POST['meta_description_ca'] ?? '',
                'h1_ca' => $_POST['h1_ca'] ?? '',
                'contenido_principal_ca' => $_POST['contenido_principal_ca'] ?? null,
                'title_es' => $_POST['title_es'] ?? '',
                'meta_description_es' => $_POST['meta_description_es'] ?? '',
                'h1_es' => $_POST['h1_es'] ?? '',
                'contenido_principal_es' => $_POST['contenido_principal_es'] ?? null,
                'slug_ca' => $_POST['slug_ca'] ?? null,
                'slug_es' => $_POST['slug_es'] ?? null,
                'meta_robots' => $_POST['meta_robots'] ?? 'index, follow',
                'canonical_url' => $_POST['canonical_url'] ?? null,
                'priority' => $_POST['priority'] ?? '0.8',
                'changefreq' => $_POST['changefreq'] ?? 'monthly',
                'focus_keyword_ca' => $_POST['focus_keyword_ca'] ?? null,
                'focus_keyword_es' => $_POST['focus_keyword_es'] ?? null,
                'keywords_secundarias_ca' => $_POST['keywords_secundarias_ca'] ?? null,
                'keywords_secundarias_es' => $_POST['keywords_secundarias_es'] ?? null,
                'og_title_ca' => $_POST['og_title_ca'] ?? null,
                'og_title_es' => $_POST['og_title_es'] ?? null,
                'og_description_ca' => $_POST['og_description_ca'] ?? null,
                'og_description_es' => $_POST['og_description_es'] ?? null,
                'og_image' => $_POST['og_image'] ?? null,
                'twitter_title_ca' => $_POST['twitter_title_ca'] ?? null,
                'twitter_title_es' => $_POST['twitter_title_es'] ?? null,
                'twitter_description_ca' => $_POST['twitter_description_ca'] ?? null,
                'twitter_description_es' => $_POST['twitter_description_es'] ?? null,
                'twitter_image' => $_POST['twitter_image'] ?? null,
                'featured_image' => $_POST['featured_image'] ?? null,
                'alt_image_ca' => $_POST['alt_image_ca'] ?? null,
                'alt_image_es' => $_POST['alt_image_es'] ?? null,
                'activa' => isset($_POST['activa']) ? 1 : 0,
                'fecha_publicacion' => $_POST['fecha_publicacion'] ?? null
            ];
            
            if ($id_pagina) {
                // Actualitzar pàgina existent
                $pagina = new SEO_OnPage($id_pagina);
                $pagina->actualitzarMultiplesCamps($data);
                $pagina->actualitzarMetriques();
                $pagina->calcularSeoScore();
                $_SESSION['seo_saved'] = true;
                header('Location: gseo.php?saved=1&tab=onpage');
            } else {
                // Crear nova pàgina
                $id_nueva = SEO_OnPage::crear($data);
                if ($id_nueva) {
                    $pagina = new SEO_OnPage($id_nueva);
                    $pagina->actualitzarMetriques();
                    $pagina->calcularSeoScore();
                    $_SESSION['seo_saved'] = true;
                    header('Location: gseo.php?saved=1&tab=onpage&created=' . $id_nueva);
                } else {
                    throw new Exception("No s'ha pogut crear la pàgina");
                }
            }
            exit;
            
        } catch (Exception $e) {
            $_SESSION['seo_error'] = $e->getMessage();
            header('Location: gseo.php?error=1&tab=onpage');
            exit;
        }
    } elseif ($action === 'delete_onpage') {
        // Eliminar pàgina
        try {
            $id_pagina = $_POST['id_pagina'] ?? null;
            if ($id_pagina) {
                $pagina = new SEO_OnPage($id_pagina);
                $pagina->eliminar();
                $_SESSION['seo_saved'] = true;
                header('Location: gseo.php?saved=1&tab=onpage&deleted=1');
            } else {
                throw new Exception("ID de pàgina no proporcionat");
            }
            exit;
        } catch (Exception $e) {
            $_SESSION['seo_error'] = $e->getMessage();
            header('Location: gseo.php?error=1&tab=onpage');
            exit;
        }
    } elseif ($action === 'create_backlink') {
        // Crear nou backlink
        try {
            $data = [
                'url_origen' => $_POST['url_origen'],
                'url_destino' => $_POST['url_destino'],
                'anchor_text' => $_POST['anchor_text'],
                'dominio_origen' => $_POST['dominio_origen'],
                'tipo_backlink' => $_POST['tipo_backlink'],
                'fecha_descubrimiento' => $_POST['fecha_descubrimiento'] ?? date('Y-m-d')
            ];
            
            // Camps opcionals
            if (!empty($_POST['da_origen'])) $data['da_origen'] = $_POST['da_origen'];
            if (!empty($_POST['dr_origen'])) $data['dr_origen'] = $_POST['dr_origen'];
            if (!empty($_POST['tf_origen'])) $data['tf_origen'] = $_POST['tf_origen'];
            if (!empty($_POST['cf_origen'])) $data['cf_origen'] = $_POST['cf_origen'];
            if (!empty($_POST['traffic_origen'])) $data['traffic_origen'] = $_POST['traffic_origen'];
            if (!empty($_POST['idioma_origen'])) $data['idioma_origen'] = $_POST['idioma_origen'];
            if (!empty($_POST['posicion_enlace'])) $data['posicion_enlace'] = $_POST['posicion_enlace'];
            if (!empty($_POST['contexto_backlink'])) $data['contexto_backlink'] = $_POST['contexto_backlink'];
            if (!empty($_POST['relevancia_tematica'])) $data['relevancia_tematica'] = $_POST['relevancia_tematica'];
            if (!empty($_POST['calidad_percibida'])) $data['calidad_percibida'] = $_POST['calidad_percibida'];
            if (!empty($_POST['prioridad'])) $data['prioridad'] = $_POST['prioridad'];
            if (!empty($_POST['campana_seo'])) $data['campana_seo'] = $_POST['campana_seo'];
            if (!empty($_POST['objetivo_seo'])) $data['objetivo_seo'] = $_POST['objetivo_seo'];
            if (!empty($_POST['notas_internas'])) $data['notas_internas'] = $_POST['notas_internas'];
            
            // Checkboxes
            $data['nofollow'] = isset($_POST['nofollow']) ? 1 : 0;
            $data['sponsored'] = isset($_POST['sponsored']) ? 1 : 0;
            $data['ugc'] = isset($_POST['ugc']) ? 1 : 0;
            
            $backlink = SEO_OffPage_Links::crear($data);
            
            $_SESSION['seo_saved'] = true;
            header('Location: gseo.php?saved=1&tab=offpage&view=list');
            exit;
            
        } catch (Exception $e) {
            $_SESSION['seo_error'] = $e->getMessage();
            header('Location: gseo.php?error=1&tab=offpage&view=create');
            exit;
        }
    } elseif ($action === 'update_backlink') {
        // Actualitzar backlink existent
        try {
            $id_offpage = $_POST['id_offpage'] ?? null;
            if (!$id_offpage) {
                throw new Exception("ID de backlink no proporcionat");
            }
            
            $backlink = new SEO_OffPage_Links($id_offpage);
            
            $data = [
                'url_origen' => $_POST['url_origen'],
                'url_destino' => $_POST['url_destino'],
                'anchor_text' => $_POST['anchor_text'],
                'dominio_origen' => $_POST['dominio_origen'],
                'tipo_backlink' => $_POST['tipo_backlink']
            ];
            
            // Camps opcionals
            if (isset($_POST['da_origen'])) $data['da_origen'] = $_POST['da_origen'] ?: null;
            if (isset($_POST['dr_origen'])) $data['dr_origen'] = $_POST['dr_origen'] ?: null;
            if (isset($_POST['tf_origen'])) $data['tf_origen'] = $_POST['tf_origen'] ?: null;
            if (isset($_POST['cf_origen'])) $data['cf_origen'] = $_POST['cf_origen'] ?: null;
            if (isset($_POST['traffic_origen'])) $data['traffic_origen'] = $_POST['traffic_origen'] ?: null;
            if (isset($_POST['idioma_origen'])) $data['idioma_origen'] = $_POST['idioma_origen'];
            if (isset($_POST['posicion_enlace'])) $data['posicion_enlace'] = $_POST['posicion_enlace'];
            if (isset($_POST['contexto_backlink'])) $data['contexto_backlink'] = $_POST['contexto_backlink'];
            if (isset($_POST['relevancia_tematica'])) $data['relevancia_tematica'] = $_POST['relevancia_tematica'];
            if (isset($_POST['calidad_percibida'])) $data['calidad_percibida'] = $_POST['calidad_percibida'];
            if (isset($_POST['prioridad'])) $data['prioridad'] = $_POST['prioridad'];
            if (isset($_POST['campana_seo'])) $data['campana_seo'] = $_POST['campana_seo'];
            if (isset($_POST['objetivo_seo'])) $data['objetivo_seo'] = $_POST['objetivo_seo'];
            if (isset($_POST['notas_internas'])) $data['notas_internas'] = $_POST['notas_internas'];
            if (isset($_POST['fecha_descubrimiento'])) $data['fecha_descubrimiento'] = $_POST['fecha_descubrimiento'];
            
            // Checkboxes
            $data['nofollow'] = isset($_POST['nofollow']) ? 1 : 0;
            $data['sponsored'] = isset($_POST['sponsored']) ? 1 : 0;
            $data['ugc'] = isset($_POST['ugc']) ? 1 : 0;
            
            $backlink->actualitzarMultiplesCamps($data);
            
            $_SESSION['seo_saved'] = true;
            header('Location: gseo.php?saved=1&tab=offpage&view=list');
            exit;
            
        } catch (Exception $e) {
            $_SESSION['seo_error'] = $e->getMessage();
            header('Location: gseo.php?error=1&tab=offpage&view=edit&id_backlink=' . ($id_offpage ?? ''));
            exit;
        }
    } elseif ($action === 'save_offpage') {
        header('Location: gseo.php?saved=1&tab=offpage');
        exit;
    }
    
    // Crear directori
    if ($action === 'create_directorio') {
        try {
            $data = [
                'nombre' => $_POST['nombre'] ?? '',
                'url' => $_POST['url'] ?? '',
                'categoria' => $_POST['categoria'] ?? 'psicologia',
                'da_directorio' => !empty($_POST['da_directorio']) ? $_POST['da_directorio'] : null,
                'costo' => $_POST['costo'] ?? 0,
                'idioma' => $_POST['idioma'] ?? 'es',
                'nofollow' => isset($_POST['nofollow']) ? 1 : 0,
                'permite_anchor_personalizado' => isset($_POST['permite_anchor_personalizado']) ? 1 : 0,
                'estado' => $_POST['estado'] ?? 'pendiente',
                'fecha_envio' => !empty($_POST['fecha_envio']) ? $_POST['fecha_envio'] : null,
                'fecha_aprobacion' => !empty($_POST['fecha_aprobacion']) ? $_POST['fecha_aprobacion'] : null,
                'notas' => $_POST['notas'] ?? null
            ];
            
            SEO_OffPage_Directories::crear($data);
            
            $_SESSION['seo_saved'] = true;
            header('Location: gseo.php?saved=1&tab=offpage&subtab=directories&view=list');
            exit;
            
        } catch (Exception $e) {
            $_SESSION['seo_error'] = $e->getMessage();
            header('Location: gseo.php?error=1&tab=offpage&subtab=directories&view=create');
            exit;
        }
    }

    
    
    // Actualitzar directori
    if ($action === 'update_directorio') {
        try {
            $id_directorio = $_POST['id_directorio'] ?? null;
            if (!$id_directorio) {
                throw new Exception("ID de directori no proporcionat");
            }
            
            $directorio = new SEO_OffPage_Directories($id_directorio);
            
            $data = [
                'nombre' => $_POST['nombre'] ?? '',
                'url' => $_POST['url'] ?? '',
                'categoria' => $_POST['categoria'] ?? 'psicologia',
                'da_directorio' => !empty($_POST['da_directorio']) ? $_POST['da_directorio'] : null,
                'costo' => $_POST['costo'] ?? 0,
                'idioma' => $_POST['idioma'] ?? 'es',
                'nofollow' => isset($_POST['nofollow']) ? 1 : 0,
                'permite_anchor_personalizado' => isset($_POST['permite_anchor_personalizado']) ? 1 : 0,
                'estado' => $_POST['estado'] ?? 'pendiente',
                'fecha_envio' => !empty($_POST['fecha_envio']) ? $_POST['fecha_envio'] : null,
                'fecha_aprobacion' => !empty($_POST['fecha_aprobacion']) ? $_POST['fecha_aprobacion'] : null,
                'notas' => $_POST['notas'] ?? null
            ];
            
            $directorio->actualitzarMultiplesCamps($data);
            
            $_SESSION['seo_saved'] = true;
            header('Location: gseo.php?saved=1&tab=offpage&subtab=directories&view=list');
            exit;
            
        } catch (Exception $e) {
            $_SESSION['seo_error'] = $e->getMessage();
            header('Location: gseo.php?error=1&tab=offpage&subtab=directories&view=edit&id=' . ($id_directorio ?? ''));
            exit;
        }
    } else {
        header('Location: gseo.php?saved=1&tab=' . ($_GET['tab'] ?? 'onpage'));
        exit;
    }
}

// Processar accions GET (eliminar backlink)
if (isset($_GET['action']) && $_GET['action'] === 'delete_backlink') {
    try {
        $id_offpage = $_GET['id_offpage'] ?? null;
        if (!$id_offpage) {
            throw new Exception("ID de backlink no proporcionat");
        }
        
        $backlink = new SEO_OffPage_Links($id_offpage);
        $backlink->eliminar();
        
        $_SESSION['seo_saved'] = true;
        header('Location: gseo.php?saved=1&tab=offpage&subtab=backlinks&view=list');
        exit;
        
    } catch (Exception $e) {
        $_SESSION['seo_error'] = $e->getMessage();
        header('Location: gseo.php?error=1&tab=offpage&subtab=backlinks&view=list');
        exit;
    }
}

// Processar accions GET (eliminar directori)
if (isset($_GET['action']) && $_GET['action'] === 'delete_directorio') {
    try {
        $id_directorio = $_GET['id_directorio'] ?? null;
        if (!$id_directorio) {
            throw new Exception("ID de directori no proporcionat");
        }
        
        $directorio = new SEO_OffPage_Directories($id_directorio);
        $directorio->eliminar();
        
        $_SESSION['seo_saved'] = true;
        header('Location: gseo.php?saved=1&tab=offpage&subtab=directories&view=list');
        exit;
        
    } catch (Exception $e) {
        $_SESSION['seo_error'] = $e->getMessage();
        header('Location: gseo.php?error=1&tab=offpage&subtab=directories&view=list');
        exit;
    }
}

// (Eliminat: handlers de SEO Técnic desactivats)

$saved = isset($_GET['saved']) && $_GET['saved'] == '1';
$error = isset($_GET['error']) && $_GET['error'] == '1';
$activeTab = $_GET['tab'] ?? 'global';

// Carregar configuració SEO Global
try {
    $seo_global = SEO_Global::carregarConfiguracio();
    // Calcular puntuació de la configuració SEO Global
    $seo_global_score = $seo_global ? $seo_global->calcularPuntuacioConfiguracio() : null;
} catch (Exception $e) {
    $seo_global = null;
    $seo_global_score = null;
    $error_message = $e->getMessage();
}

// Carregar pàgines SEO On-Page
$paginas_onpage = [];
$pagina_edit = null;
$tipo_filtro = $_GET['tipo'] ?? 'all';
$seo_onpage_stats = null;

try {
    if (isset($_GET['edit']) && $_GET['edit']) {
        // Carregar pàgina per editar
        $pagina_edit = new SEO_OnPage($_GET['edit']);
    }
    
    // Llistar pàgines actives (filtrat per tipus si cal)
    $paginas_onpage = SEO_OnPage::llistarPaginesActives($tipo_filtro !== 'all' ? $tipo_filtro : null);
    
    // Calcular estadístiques globals d'On-Page SEO
    $seo_onpage_stats = SEO_OnPage::calcularEstadistiquesGlobals();
} catch (Exception $e) {
    $error_message = $e->getMessage();
}

// Carregar estadístiques SEO Off-Page
$seo_offpage_stats = null;
try {
    $seo_offpage_stats = SEO_OffPage_Links::obtenirEstadistiquesGlobals();
} catch (Exception $e) {
    error_log("Error carregant estadístiques Off-Page Links: " . $e->getMessage());
}

// Carregar estadístiques SEO Off-Page Directoris
$seo_directories_stats = null;
try {
    $seo_directories_stats = SEO_OffPage_Directories::obtenirEstadistiquesGlobals();
} catch (Exception $e) {
    error_log("Error carregant estadístiques Off-Page Directoris: " . $e->getMessage());
}

// (SEO Técnico integrado desactivado)

// Dades estàtiques per les altres pestanyes (Technical, Content, Analytics)
$seoConfig = [
    'meta_title_ca' => 'Yanina Parisi - Psicòloga col·legiada a Girona',
    'meta_title_es' => 'Yanina Parisi - Psicóloga colegiada en Girona',
    'meta_description_ca' => 'Psicòloga col·legiada especialitzada en teràpia de parella, adults i psicologia judicial a Girona. Primera sessió gratuïta.',
    'meta_description_es' => 'Psicóloga colegiada especializada en terapia de pareja, adultos y psicología judicial en Girona. Primera sesión gratuita.',
    'meta_keywords_ca' => 'psicòloga girona, teràpia parella, psicologia judicial, salut mental adults',
    'meta_keywords_es' => 'psicóloga girona, terapia pareja, psicología judicial, salud mental adultos',
    'og_title_ca' => 'Yanina Parisi - Psicòloga col·legiada a Girona',
    'og_title_es' => 'Yanina Parisi - Psicóloga colegiada en Girona',
    'og_description_ca' => 'Psicòloga especialitzada en teràpia de parella, adults i psicologia judicial.',
    'og_description_es' => 'Psicóloga especializada en terapia de pareja, adultos y psicología judicial.',
    'og_image' => 'https://www.psicologiayanina.com/img/og-image.jpg',
    'twitter_title_ca' => 'Yanina Parisi - Psicòloga a Girona',
    'twitter_title_es' => 'Yanina Parisi - Psicóloga en Girona',
    'twitter_description_ca' => 'Teràpia psicològica professional a Girona',
    'twitter_description_es' => 'Terapia psicológica profesional en Girona',
    'twitter_image' => 'https://www.psicologiayanina.com/img/twitter-card.jpg',
    'google_analytics' => 'G-XXXXXXXXXX',
    'google_search_console' => 'google-site-verification=xxxxxxxxxxxxxxxxx',
    'facebook_pixel' => '1234567890123456',
    'canonical_url' => 'https://www.psicologiayanina.com',
    'robots_txt' => "User-agent: *\nDisallow: /_pcontrol/\nAllow: /\nSitemap: https://www.psicologiayanina.com/sitemap.xml",
    'sitemap_url' => 'https://www.psicologiayanina.com/sitemap.xml',
    'structured_data' => '{"@context":"https://schema.org","@type":"ProfessionalService"}',
    'alt_tags' => 'yes',
    'h1_optimization' => 'yes',
    'internal_linking' => '15',
    'page_speed' => '92'
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión SEO - Panel de Control</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@7.0.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Libre+Baskerville:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="css/gseo.css">
    <link rel="stylesheet" href="css/onpage.css">
    <link rel="stylesheet" href="css/offpage.css">
</head>
<body>
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
                    <h1><i class="fas fa-search"></i> Gestión SEO</h1>
                    <p class="date-today">Optimiza el posicionamiento de tu web</p>
                </div>
            </div>
            <div class="top-bar-right">
                <div class="user-profile">
                    <img src="../img/Logo.png" alt="Profile" class="profile-img">
                    <span class="profile-name"><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Usuario'); ?></span>
                </div>
            </div>
        </header>

        <!-- SEO Content -->
        <div class="seo-container">
            <?php if ($saved): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <span>La configuración SEO se ha guardado correctamente</span>
                <button class="alert-close" onclick="this.parentElement.style.display='none'">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <?php endif; ?>
            
            <?php if ($error): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i>
                <span>Error al guardar: <?php echo htmlspecialchars($_SESSION['seo_error'] ?? 'Error desconocido'); ?></span>
                <button class="alert-close" onclick="this.parentElement.style.display='none'">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <?php unset($_SESSION['seo_error']); ?>
            <?php endif; ?>

            <?php
            // Calcular la puntuació global com la mitjana de les 3 cards de la segona fila
            // Obtenir valors disponibles amb fallbacks
            $cfg_score = isset($seo_global_score['score']) ? (int)$seo_global_score['score'] : null;
            $on_score = isset($seo_onpage_stats['score']) ? (int)$seo_onpage_stats['score'] : null;
            // off-page combined: si hi ha dades, calcular combinat (60% backlinks, 40% directoris)
            $backlinks_score = isset($seo_offpage_stats['score_global']) ? (int)$seo_offpage_stats['score_global'] : null;
            $dirs_score = isset($seo_directories_stats['score_global']) ? (int)$seo_directories_stats['score_global'] : null;
            $off_score = null;
            if ($backlinks_score !== null || $dirs_score !== null) {
                $b = $backlinks_score !== null ? $backlinks_score : 0;
                $d = $dirs_score !== null ? $dirs_score : 0;
                // si només un està disponible, usar-lo directament
                if ($backlinks_score === null) $off_score = $d;
                elseif ($dirs_score === null) $off_score = $b;
                else $off_score = (int) round(($b * 0.6) + ($d * 0.4));
            }

            // Aplicar ponderacions: On-Page 50%, Off-Page 30%, Config 20%
            $weights = [
                'cfg' => 0.20,
                'on'  => 0.50,
                'off' => 0.30,
            ];

            $weighted_sum = 0;
            $weight_total = 0;

            if (is_numeric($on_score)) {
                $weighted_sum += $on_score * $weights['on'];
                $weight_total += $weights['on'];
            }
            if (is_numeric($off_score)) {
                $weighted_sum += $off_score * $weights['off'];
                $weight_total += $weights['off'];
            }
            if (is_numeric($cfg_score)) {
                $weighted_sum += $cfg_score * $weights['cfg'];
                $weight_total += $weights['cfg'];
            }

            if ($weight_total > 0) {
                // Normalize by sum of used weights
                $main_score = (int) round($weighted_sum / $weight_total);
            } else {
                $main_score = null;
            }
            ?>

            <!-- SEO Score Dashboard -->
            <div class="seo-score-dashboard">
                <!-- Nueva Card: Configuración SEO Global -->
                <div class="score-card score-config">
                    <div class="score-header">
                        <i class="fas fa-cogs"></i>
                        <h3>Configuración SEO Global</h3>
                    </div>
                    <?php if ($seo_global_score): ?>
                    <div class="score-circle">
                        <svg width="100%" height="100%" viewBox="0 0 120 120">
                            <circle cx="60" cy="60" r="50" fill="none" stroke="#e9ecef" stroke-width="8"></circle>
                            <circle cx="60" cy="60" r="50" fill="none" 
                                    stroke="<?php echo $seo_global_score['score'] >= 75 ? '#27ae60' : ($seo_global_score['score'] >= 60 ? '#c2b280' : '#e67e22'); ?>" 
                                    stroke-width="8" 
                                    stroke-dasharray="314" 
                                    stroke-dashoffset="<?php echo 314 - (314 * $seo_global_score['score'] / 100); ?>" 
                                    transform="rotate(-90 60 60)" class="score-progress"></circle>
                        </svg>
                        <div class="score-number"><?php echo $seo_global_score['score']; ?><span>/100</span></div>
                    </div>
                    <p class="score-status"><?php echo htmlspecialchars($seo_global_score['estat']); ?></p>
                    
                    <!-- Detalle de secciones -->
                    <div class="config-details">
                        <div class="config-detail-item">
                            <span class="detail-label"><i class="fas fa-tags"></i> Meta Tags:</span>
                            <span class="detail-value"><?php echo $seo_global_score['detalles']['meta_tags']['percentatge']; ?>%</span>
                        </div>
                        <div class="config-detail-item">
                            <span class="detail-label"><i class="fas fa-code"></i> Schema:</span>
                            <span class="detail-value"><?php echo $seo_global_score['detalles']['schema']['percentatge']; ?>%</span>
                        </div>
                        <div class="config-detail-item">
                            <span class="detail-label"><i class="fas fa-share-alt"></i> Social:</span>
                            <span class="detail-value"><?php echo $seo_global_score['detalles']['social']['percentatge']; ?>%</span>
                        </div>
                        <div class="config-detail-item">
                            <span class="detail-label"><i class="fas fa-chart-bar"></i> Analytics:</span>
                            <span class="detail-value"><?php echo $seo_global_score['detalles']['technical']['percentatge']; ?>%</span>
                        </div>
                    </div>
                    <?php else: ?>
                    <div class="score-circle">
                        <svg width="100%" height="100%" viewBox="0 0 120 120">
                            <circle cx="60" cy="60" r="50" fill="none" stroke="#e9ecef" stroke-width="8"></circle>
                        </svg>
                        <div class="score-number">-<span>/100</span></div>
                    </div>
                    <p class="score-status">No disponible</p>
                    <?php endif; ?>
                </div>
                
                <!-- Card 2: On-Page SEO -->
                <div class="score-card score-onpage">
                    <div class="score-header">
                        <i class="fas fa-file-code"></i>
                        <h3>On-Page SEO</h3>
                    </div>
                    <?php if ($seo_onpage_stats): ?>
                    <div class="score-circle">
                        <svg width="100%" height="100%" viewBox="0 0 120 120">
                            <circle cx="60" cy="60" r="50" fill="none" stroke="#e9ecef" stroke-width="8"></circle>
                            <circle cx="60" cy="60" r="50" fill="none" 
                                    stroke="<?php echo $seo_onpage_stats['score'] >= 75 ? '#27ae60' : ($seo_onpage_stats['score'] >= 60 ? '#c2b280' : '#e67e22'); ?>" 
                                    stroke-width="8" 
                                    stroke-dasharray="314" 
                                    stroke-dashoffset="<?php echo 314 - (314 * $seo_onpage_stats['score'] / 100); ?>" 
                                    transform="rotate(-90 60 60)" class="score-progress"></circle>
                        </svg>
                        <div class="score-number"><?php echo $seo_onpage_stats['score']; ?><span>/100</span></div>
                    </div>
                    <p class="score-status"><?php echo htmlspecialchars($seo_onpage_stats['estat']); ?></p>
                    
                    <!-- Detalle de On-Page -->
                    <div class="config-details">
                        <div class="config-detail-item">
                            <span class="detail-label"><i class="fas fa-file-alt"></i> Total Páginas:</span>
                            <span class="detail-value"><?php echo $seo_onpage_stats['total_paginas']; ?></span>
                        </div>
                        <div class="config-detail-item">
                            <span class="detail-label"><i class="fas fa-tags"></i> Meta Tags:</span>
                            <span class="detail-value"><?php echo $seo_onpage_stats['detalles']['meta_tags']['percentatge']; ?>%</span>
                        </div>
                        <div class="config-detail-item">
                            <span class="detail-label"><i class="fas fa-heading"></i> Contenido:</span>
                            <span class="detail-value"><?php echo $seo_onpage_stats['detalles']['content']['percentatge']; ?>%</span>
                        </div>
                        <div class="config-detail-item">
                            <span class="detail-label"><i class="fas fa-share-alt"></i> Social:</span>
                            <span class="detail-value"><?php echo $seo_onpage_stats['detalles']['social']['percentatge']; ?>%</span>
                        </div>
                        <div class="config-detail-item">
                            <span class="detail-label"><i class="fas fa-images"></i> Imágenes:</span>
                            <span class="detail-value"><?php echo $seo_onpage_stats['detalles']['images']['percentatge']; ?>%</span>
                        </div>
                        <div class="config-detail-item">
                            <span class="detail-label"><i class="fas fa-check-circle"></i> Optimizadas:</span>
                            <span class="detail-value"><?php echo $seo_onpage_stats['paginas_optimizadas']; ?></span>
                        </div>
                        <div class="config-detail-item">
                            <span class="detail-label"><i class="fas fa-exclamation-triangle"></i> A Mejorar:</span>
                            <span class="detail-value"><?php echo $seo_onpage_stats['paginas_mejorar']; ?></span>
                        </div>
                    </div>
                    <?php else: ?>
                    <div class="score-circle">
                        <svg width="100%" height="100%" viewBox="0 0 120 120">
                            <circle cx="60" cy="60" r="50" fill="none" stroke="#e9ecef" stroke-width="8"></circle>
                        </svg>
                        <div class="score-number">-<span>/100</span></div>
                    </div>
                    <p class="score-status">Sin páginas</p>
                    <?php endif; ?>
                </div>
                
                <!-- Card 3: Off-Page SEO -->
                <div class="score-card score-offpage">
                    <div class="score-header">
                        <i class="fas fa-link"></i>
                        <h3>Off-Page SEO</h3>
                    </div>
                    <?php 
                    // Combinar estadístiques de backlinks i directoris
                    $has_offpage_data = ($seo_offpage_stats && $seo_offpage_stats['total'] > 0) || 
                                        ($seo_directories_stats && $seo_directories_stats['total'] > 0);
                    
                    if ($has_offpage_data):
                        // Calcular score combinat (60% backlinks, 40% directoris)
                        $backlinks_score = $seo_offpage_stats['score_global'] ?? 0;
                        $directories_score = $seo_directories_stats['score_global'] ?? 0;
                        $combined_score = round(($backlinks_score * 0.6) + ($directories_score * 0.4));
                    ?>
                    <div class="score-circle">
                        <svg width="100%" height="100%" viewBox="0 0 120 120">
                            <circle cx="60" cy="60" r="50" fill="none" stroke="#e9ecef" stroke-width="8"></circle>
                            <circle cx="60" cy="60" r="50" fill="none" 
                                    stroke="<?php echo $combined_score >= 75 ? '#27ae60' : ($combined_score >= 60 ? '#c2b280' : '#e67e22'); ?>" 
                                    stroke-width="8" 
                                    stroke-dasharray="314" 
                                    stroke-dashoffset="<?php echo 314 - (314 * $combined_score / 100); ?>" 
                                    transform="rotate(-90 60 60)" class="score-progress"></circle>
                        </svg>
                        <div class="score-number"><?php echo $combined_score; ?><span>/100</span></div>
                    </div>
                    <p class="score-status">
                        <?php 
                        if ($combined_score >= 75) echo 'Excelente';
                        elseif ($combined_score >= 60) echo 'Bueno';
                        elseif ($combined_score >= 40) echo 'Regular';
                        else echo 'Necesita mejora';
                        ?>
                    </p>
                    
                    <!-- Detalle de Off-Page -->
                    <div class="config-details">
                        <div class="config-detail-item">
                            <span class="detail-label"><i class="fas fa-link"></i> Backlinks:</span>
                            <span class="detail-value"><?php echo $seo_offpage_stats['total'] ?? 0; ?></span>
                        </div>
                        <div class="config-detail-item">
                            <span class="detail-label"><i class="fas fa-list-ul"></i> Directorios:</span>
                            <span class="detail-value"><?php echo $seo_directories_stats['total'] ?? 0; ?></span>
                        </div>
                        <div class="config-detail-item">
                            <span class="detail-label"><i class="fas fa-check-circle"></i> Activos:</span>
                            <span class="detail-value">
                                <?php echo ($seo_offpage_stats['activos'] ?? 0) + ($seo_directories_stats['activos'] ?? 0); ?>
                            </span>
                        </div>
                        <div class="config-detail-item">
                            <span class="detail-label"><i class="fas fa-trophy"></i> DA Promedio:</span>
                            <span class="detail-value">
                                <?php 
                                $da_avg = (($seo_offpage_stats['da_promedio'] ?? 0) + ($seo_directories_stats['da_promedio'] ?? 0)) / 2;
                                echo round($da_avg); 
                                ?>
                            </span>
                        </div>
                        <div class="config-detail-item">
                            <span class="detail-label"><i class="fas fa-check"></i> DoFollow:</span>
                            <span class="detail-value">
                                <?php echo ($seo_offpage_stats['dofollow'] ?? 0) + ($seo_directories_stats['dofollow'] ?? 0); ?>
                            </span>
                        </div>
                        <div class="config-detail-item">
                            <span class="detail-label"><i class="fas fa-euro-sign"></i> Coste Anual:</span>
                            <span class="detail-value"><?php echo number_format($seo_directories_stats['costo_total_anual'] ?? 0, 0); ?>€</span>
                        </div>
                    </div>
                    <?php else: ?>
                    <div class="score-circle">
                        <svg width="100%" height="100%" viewBox="0 0 120 120">
                            <circle cx="60" cy="60" r="50" fill="none" stroke="#e9ecef" stroke-width="8"></circle>
                        </svg>
                        <div class="score-number">-<span>/100</span></div>
                    </div>
                    <p class="score-status">Sin datos Off-Page</p>
                    <div class="config-details">
                        <div class="config-detail-item">
                            <span class="detail-label"><i class="fas fa-info-circle"></i> Info:</span>
                            <span class="detail-value">Añade backlinks y directorios</span>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
                
                <!-- Card 4: Informació SEO (nova) -->
                <div class="score-card score-info">
                    <div class="score-header">
                        <i class="fas fa-chart-line"></i>
                        <h3>Puntuación SEO Global</h3>
                    </div>
                    <div class="score-circle">
                        <svg width="100%" height="100%" viewBox="0 0 120 120">
                            <circle cx="60" cy="60" r="50" fill="none" stroke="#e9ecef" stroke-width="8"></circle>
                            <?php if ($main_score !== null): 
                                $dash = 314 - (314 * $main_score / 100);
                                $stroke = $main_score >= 75 ? '#27ae60' : ($main_score >= 60 ? '#c2b280' : '#e67e22');
                            ?>
                            <circle cx="60" cy="60" r="50" fill="none" 
                                    stroke="<?php echo $stroke; ?>" stroke-width="8" 
                                    stroke-dasharray="314" 
                                    stroke-dashoffset="<?php echo $dash; ?>" 
                                    transform="rotate(-90 60 60)" class="score-progress"></circle>
                            <?php endif; ?>
                        </svg>
                        <div class="score-number"><?php echo $main_score !== null ? $main_score : '-'; ?><span>/100</span></div>
                    </div>
                    <p class="score-status"><?php echo $main_score !== null ? ($main_score >= 75 ? 'Excelente' : ($main_score >= 60 ? 'Buena optimización' : 'Necesita mejora')) : 'No disponible'; ?></p>
                </div>
                
            </div>

            <!-- Tabs -->
            <div class="seo-tabs">
                <button class="tab-btn <?php echo $activeTab === 'global' ? 'active' : ''; ?>" onclick="switchTab('global')">
                    <i class="fas fa-globe-americas"></i> SEO Global
                </button>
                <button class="tab-btn <?php echo $activeTab === 'onpage' ? 'active' : ''; ?>" onclick="switchTab('onpage')">
                    <i class="fas fa-file-code"></i> SEO On-Page
                </button>
                <button class="tab-btn <?php echo $activeTab === 'offpage' ? 'active' : ''; ?>" onclick="switchTab('offpage')">
                    <i class="fas fa-globe"></i> SEO Off-Page
                </button>
                <!-- SEO Técnico tab button removed (functionality disabled) -->
            </div>

            <!-- SEO Global Tab -->
            <?php include 'includes/general_interface.php'; ?>

            <!-- On-Page SEO Tab -->
            <?php include 'includes/onpage_interface.php'; ?>

            <!-- Off-Page SEO Tab with Sub-tabs -->
            <div id="offpage-tab" class="tab-content <?php echo $activeTab === 'offpage' ? 'active' : ''; ?>">
                <?php 
                $activeSubTab = $_GET['subtab'] ?? 'backlinks';
                ?>
                
                <!-- Sub-tabs for Off-Page -->
                <div class="offpage-subtabs">
                    <button class="subtab-btn <?php echo $activeSubTab === 'backlinks' ? 'active' : ''; ?>" 
                            onclick="switchOffPageSubTab('backlinks')">
                        <i class="fas fa-link"></i> Backlinks
                    </button>
                    <button class="subtab-btn <?php echo $activeSubTab === 'directories' ? 'active' : ''; ?>" 
                            onclick="switchOffPageSubTab('directories')">
                        <i class="fas fa-list-ul"></i> Directorios
                    </button>
                </div>
                
                <!-- Backlinks Content -->
                <div id="offpage-backlinks-content" class="offpage-subtab-content <?php echo $activeSubTab === 'backlinks' ? 'active' : ''; ?>">
                    <?php include 'includes/offpage_links_interface.php'; ?>
                </div>
                
                <!-- Directories Content -->
                <div id="offpage-directories-content" class="offpage-subtab-content <?php echo $activeSubTab === 'directories' ? 'active' : ''; ?>">
                    <?php include 'includes/offpage_directories_interface.php'; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="js/dashboard.js"></script>
    <script>
        // Canviar pestanyes
        function switchTab(tab) {
            // Ocultar tots els tabs
            document.querySelectorAll('.tab-content').forEach(t => t.classList.remove('active'));
            document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
            
            // Mostrar el tab seleccionat
            const tabContent = document.getElementById(tab + '-tab');
            if (tabContent) {
                tabContent.classList.add('active');
            }
            
            // Activar el botó corresponent
            document.querySelectorAll('.tab-btn').forEach(btn => {
                if (btn.getAttribute('onclick').includes(tab)) {
                    btn.classList.add('active');
                }
            });
            
            // Actualitzar URL
            const url = new URL(window.location);
            url.searchParams.set('tab', tab);
            window.history.pushState({}, '', url);
        }
        
        // Canviar sub-pestanyes d'Off-Page
        function switchOffPageSubTab(subtab) {
            // Ocultar tots els sub-tabs
            document.querySelectorAll('.offpage-subtab-content').forEach(t => t.classList.remove('active'));
            document.querySelectorAll('.subtab-btn').forEach(b => b.classList.remove('active'));
            
            // Mostrar el sub-tab seleccionat
            const subtabContent = document.getElementById('offpage-' + subtab + '-content');
            if (subtabContent) {
                subtabContent.classList.add('active');
            }
            
            // Activar el botó corresponent
            document.querySelectorAll('.subtab-btn').forEach(btn => {
                if (btn.getAttribute('onclick').includes(subtab)) {
                    btn.classList.add('active');
                }
            });
            
            // Actualitzar URL
            const url = new URL(window.location);
            url.searchParams.set('tab', 'offpage');
            url.searchParams.set('subtab', subtab);
            window.history.pushState({}, '', url);
        }
        
        // Actualitzar comptador de caràcters
        function updateCharCounter(input, counterId) {
            const counter = document.getElementById(counterId);
            const length = input.value.length;
            const maxLength = input.getAttribute('maxlength');
            counter.textContent = length + '/' + maxLength;
            
            // Canviar color segons la proximitat al límit
            if (length > maxLength * 0.9) {
                counter.style.color = '#e74c3c';
            } else if (length > maxLength * 0.7) {
                counter.style.color = '#f39c12';
            } else {
                counter.style.color = '#27ae60';
            }
        }
        
        // Inicialitzar comptadors al carregar
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('[oninput*="updateCharCounter"]').forEach(input => {
                const counterId = input.getAttribute('oninput').match(/updateCharCounter\(this, '([^']+)'\)/)[1];
                updateCharCounter(input, counterId);
            });
        });
    </script>
</body>
</html>
