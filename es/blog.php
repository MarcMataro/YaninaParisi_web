<?php
// Inicialitzar sessió si no està iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Forçar idioma espanyol en aquesta pàgina
$_SESSION['language'] = 'es';
// Processar canvi d'idioma PRIMER
if (isset($_GET['lang'])) {
    $lang = $_GET['lang'];
    if (in_array($lang, array('ca', 'es'))) {
        $_SESSION['language'] = $lang;
        header('Location: /' . $lang . '/home.php');
        exit;
    }
}
include '../includes/functions.php';
require_once '../classes/connexio.php';
require_once '../classes/entrades.php';
require_once '../classes/categories.php';
require_once '../classes/etiquetes.php';
require_once '../classes/rel_cat_ent.php';
require_once '../classes/rel_eti_ent.php';
require_once '../classes/usuaris_panell.php';
$connexio = Connexio::getInstance();
$pdo = $connexio->getConnexio();
$entradaModel = new Entrada($pdo);
$categoryModel = new Category($pdo);
$etiquetaModel = new Etiqueta($pdo);
$relCatEntModel = new RelacioEntradesCategories($pdo);
$relEtiEntModel = new RelacioEntradesEtiquetes($pdo);
// Preparar llistats de categories i etiquetes per als filtres
$catsSelect = $categoryModel->getForSelect(getCurrentLanguage(), true);
$etisSelect = $etiquetaModel->getForSelect(getCurrentLanguage(), true);
?>
<!DOCTYPE html>
<html lang="<?php echo getCurrentLanguage(); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php
    // SEO extraction for Spanish blog page (mirror of ca/blog.php)
    $base_url = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'];
    require_once __DIR__ . '/../classes/seo_onpage.php';

    $pagina_seo = null;
    $seoTitle = null;
    $lang = getCurrentLanguage();

    try {
        $items = SEO_OnPage::llistarPaginesActives('blog');
        if (!empty($items) && isset($items[0]) && $items[0] instanceof SEO_OnPage) {
            $pagina_seo = $items[0];
            $seoTitle = $pagina_seo->getTitle($lang) ?: null;
        }
    } catch (Exception $e) { }

    if (!$seoTitle) {
        $tries = ['/blog.php','/blog','/es/blog.php','/es/blog','/ca/blog.php','/ca/blog'];
        foreach ($tries as $r) {
            try {
                $tmp = SEO_OnPage::carregarPerUrl($r, $lang);
                if ($tmp instanceof SEO_OnPage) { $pagina_seo = $tmp; $seoTitle = $pagina_seo->getTitle($lang) ?: null; break; }
            } catch (Exception $e) { }
        }
    }

    if (!$seoTitle) {
        $seoTitle = ($lang === 'es') ? 'Blog - Yanina Parisi - Psicóloga' : 'Blog - Yanina Parisi - Psicòloga';
    }

    $seoDescription = null;
    if (isset($pagina_seo) && $pagina_seo instanceof SEO_OnPage) {
        $seoDescription = $pagina_seo->getMetaDescription($lang) ?: null;
    }
    if (!$seoDescription) $seoDescription = t('meta_description');

    $canonical = null;
    if (isset($pagina_seo) && $pagina_seo instanceof SEO_OnPage) {
        $canonical = $pagina_seo->getCanonicalUrl($lang);
    }
    if (!$canonical) $canonical = $base_url . '/es/blog.php';
    ?>
    <title><?php echo htmlspecialchars($seoTitle); ?></title>
    <meta name="description" content="<?php echo htmlspecialchars($seoDescription); ?>">
    <meta name="keywords" content="<?php echo t('meta_keywords'); ?>">
    <meta name="author" content="Yanina Parisi">
    <meta name="robots" content="index, follow">
    <meta name="theme-color" content="#aa9e6b">
    <link rel="canonical" href="<?php echo htmlspecialchars($canonical); ?>">

    <?php
    $og_title = isset($pagina_seo) && $pagina_seo instanceof SEO_OnPage ? $pagina_seo->getOgTitle($lang) : $seoTitle;
    $og_description = isset($pagina_seo) && $pagina_seo instanceof SEO_OnPage ? $pagina_seo->getOgDescription($lang) : $seoDescription;
    $og_image = null;
    if (isset($pagina_seo) && $pagina_seo instanceof SEO_OnPage) {
        $og_image = $pagina_seo->getOgImage();
    }
    if (!$og_image) { $og_image = '/img/Logo.png'; }
    if (!preg_match('#^https?://#i', $og_image)) {
        $og_image = (strpos($base_url, 'http') === 0 ? $base_url : 'https://' . $_SERVER['HTTP_HOST']) . '/' . ltrim($og_image, '/');
    }
    $og_url = htmlspecialchars($canonical ?: ($base_url . $_SERVER['REQUEST_URI']));
    ?>
    <meta property="og:type" content="<?php echo (isset($pagina_seo) ? ($pagina_seo->getTipoPagina() === 'articulo' ? 'article' : 'website') : 'website'); ?>">
    <meta property="og:url" content="<?php echo $og_url; ?>">
    <meta property="og:title" content="<?php echo htmlspecialchars($og_title); ?>">
    <meta property="og:description" content="<?php echo htmlspecialchars($og_description); ?>">
    <meta property="og:image" content="<?php echo htmlspecialchars($og_image); ?>">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:site_name" content="<?php echo htmlspecialchars(t('meta_og_site_name')); ?>">
    <meta property="og:locale" content="es_ES">

    <?php
    $tw_title = isset($pagina_seo) && $pagina_seo instanceof SEO_OnPage ? $pagina_seo->getTwitterTitle($lang) : $seoTitle;
    $tw_description = isset($pagina_seo) && $pagina_seo instanceof SEO_OnPage ? $pagina_seo->getTwitterDescription($lang) : $seoDescription;
    $tw_image = null;
    if (isset($pagina_seo) && $pagina_seo instanceof SEO_OnPage) $tw_image = $pagina_seo->getTwitterImage();
    if (!$tw_image) $tw_image = '/img/Logo.png';
    if (!preg_match('#^https?://#i', $tw_image)) {
        $tw_image = (strpos($base_url, 'http') === 0 ? $base_url : 'https://' . $_SERVER['HTTP_HOST']) . '/' . ltrim($tw_image, '/');
    }
    ?>
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?php echo htmlspecialchars($tw_title); ?>">
    <meta name="twitter:description" content="<?php echo htmlspecialchars($tw_description); ?>">
    <meta name="twitter:image" content="<?php echo htmlspecialchars($tw_image); ?>">

    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "WebPage",
        "name": "<?php echo htmlspecialchars($seoTitle); ?>",
        "description": "<?php echo htmlspecialchars($seoDescription); ?>",
        "url": "<?php echo htmlspecialchars($canonical); ?>"
    }
    </script>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@7.0.0/css/all.min.css">
    <link rel="stylesheet" href="../css/estils.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Libre+Baskerville:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" sizes="32x32" href="../img/Logo32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="../img/Logo16.png">
</head>
<body>
    <?php include '_includes/navigation.php'; ?>
    <!-- Hero Section Blog -->
    <section class="hero blog-hero">
        <div class="container hero-content">
            <h1 class="hero-title"><?php echo htmlspecialchars(isset($pagina_seo) && $pagina_seo instanceof SEO_OnPage ? $pagina_seo->getH1($lang) : t('nav_blog')); ?></h1>
            <h2 class="hero-subtitle">
                Descubre reflexiones, consejos y recursos para tu bienestar emocional.
            </h2>
        </div>
    </section>
    <main id="blog-main" class="container blog-main">
        <?php
            // Breadcrumbs: Home > Blog (ES)
            if (function_exists('render_breadcrumbs')) {
                render_breadcrumbs([
                    ['label' => t('nav_home'), 'url' => 'home.php'],
                    ['label' => t('nav_blog')]
                ]);
            }
        ?>
        <h2 style="font-size:1.5em;color:#a89968;margin-bottom:28px;">
            Últimas publicaciones
        </h2>
    <?php
        try {
            $connexio = Connexio::getInstance();
            $pdo = $connexio->getConnexio();
            $entradaModel = new Entrada($pdo);
            $categoryModel = new Category($pdo);
            $etiquetaModel = new Etiqueta($pdo);
            $relCatEntModel = new RelacioEntradesCategories($pdo);
            $relEtiEntModel = new RelacioEntradesEtiquetes($pdo);
            // Filtres
            $where = ["estat = 'publicat'", "visible = 1"];
            $params = [];
            if (!empty($_GET['cat'])) {
                $where[] = "id_entrada IN (SELECT id_entrada FROM blog_entrades_categories WHERE id_categoria = :cat)";
                $params[':cat'] = $_GET['cat'];
            }
            if (!empty($_GET['eti'])) {
                $where[] = "id_entrada IN (SELECT id_entrada FROM blog_entrades_etiquetes WHERE id_etiqueta = :eti)";
                $params[':eti'] = $_GET['eti'];
            }
            if (!empty($_GET['search'])) {
                $where[] = "(titol_ca LIKE :search_ca OR titol_es LIKE :search_es OR resum_ca LIKE :search_rca OR resum_es LIKE :search_res)";
                $params[':search_ca'] = '%' . $_GET['search'] . '%';
                $params[':search_es'] = '%' . $_GET['search'] . '%';
                $params[':search_rca'] = '%' . $_GET['search'] . '%';
                $params[':search_res'] = '%' . $_GET['search'] . '%';
            }
            $sql = "SELECT * FROM blog_entrades";
            if ($where) {
                $sql .= " WHERE " . implode(' AND ', $where);
            }
            $sql .= " ORDER BY data_publicacio DESC";
            $stmt = $pdo->prepare($sql);
            foreach ($params as $k => $v) {
                $stmt->bindValue($k, $v);
            }
            $stmt->execute();
            $entradas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            echo '<div style="color:#c00;padding:40px;text-align:center;">Error de conexión: ' . htmlspecialchars($e->getMessage()) . '</div>';
            $entradas = [];
        }
        $latest = array_slice($entradas, 0, 5);
        // Carregar categories i etiquetes per cada entrada
        foreach ($latest as &$entrada) {
            // Categories
            $cats = [];
            $catObjs = $relCatEntModel->obtenirCategoriesEntrada($entrada['id_entrada'], getCurrentLanguage(), true);
            foreach ($catObjs as $cat) {
                $cats[] = $cat['nom'];
            }
            $entrada['categories_noms'] = $cats;
            // Etiquetes
            $etis = [];
            $etiObjs = $relEtiEntModel->obtenirEtiquetesEntrada($entrada['id_entrada'], getCurrentLanguage(), true);
            foreach ($etiObjs as $eti) {
                $etis[] = $eti['nom'];
            }
            $entrada['etiquetes_noms'] = $etis;
            // Autor: cargar nombre completo desde la tabla usuarios_panel (UsuarisPanell)
            $autorNom = '';
            if (!empty($entrada['id_autor'])) {
                try {
                    $u = new UsuarisPanell($pdo);
                    $u->id_usuario = (int)$entrada['id_autor'];
                    if ($u->llegirPerId()) {
                        $autorNom = trim(($u->nombre ?? '') . ' ' . ($u->apellidos ?? ''));
                    }
                } catch (Exception $e) {
                    $autorNom = '';
                }
            }
            $entrada['autor_nom_complet'] = $autorNom;
        }
        unset($entrada);
        if (empty($latest)) {
            echo '<div style="text-align:center;padding:60px 0;color:#999;font-size:1.2em;">No hay ninguna entrada de blog para mostrar</div>';
        } else {
            $total = count($entradas);
            $lang = getCurrentLanguage();
            // --- PRIMERES 5 ENTRADES (1+2+2) ---
            $entrada = $latest[0];
            echo '<div class="blog-row">';
            echo '<div class="blog-content">';
            if (!empty($entrada['imatge_portada'])) {
                $imgSrc = resolve_media_url($entrada['imatge_portada']);
                echo '<div class="entrada-thumb">';
                echo '<img src="' . htmlspecialchars($imgSrc) . '" alt="Portada entrada" class="entrada-thumb-img">';
                echo '</div>';
            }
            $titol = $lang === 'ca' ? ($entrada['titol_ca'] ?? $entrada['titol_es']) : ($entrada['titol_es'] ?? $entrada['titol_ca']);
            $resum = $lang === 'ca' ? ($entrada['resum_ca'] ?? $entrada['resum_es']) : ($entrada['resum_es'] ?? $entrada['resum_ca']);
            echo '<h2 class="entrada-titulo" style="font-size:1.4em;color:#333;margin-bottom:8px;">' . htmlspecialchars($titol) . '</h2>';
            $tagsHtml = '';
            if (!empty($entrada['categories_noms'])) {
                $tagsHtml .= '<span style="margin-right:12px;color:#888;font-size:0.95em;"><i class="fas fa-folder"></i> ' . implode(', ', (array)$entrada['categories_noms']) . '</span>';
            }
            if (!empty($entrada['etiquetes_noms'])) {
                $tagsHtml .= '<span style="color:#888;font-size:0.95em;"><i class="fas fa-tag"></i> ' . implode(', ', (array)$entrada['etiquetes_noms']) . '</span>';
            }
            if ($tagsHtml) {
                echo '<div class="entrada-tags" style="margin-bottom:8px;">' . $tagsHtml . '</div>';
            }
            echo '<div class="entrada-meta" style="color:#888;font-size:0.95em;margin-bottom:10px;">';
            echo '<i class="fas fa-calendar-alt"></i> ' . date('d/m/Y', strtotime($entrada['data_publicacio']));
            if (!empty($entrada['autor_nom_complet'])) {
                echo ' &middot; <i class="fas fa-user"></i> ' . htmlspecialchars($entrada['autor_nom_complet']);
            }
            echo '</div>';
            if (!empty($resum)) {
                echo '<div class="entrada-resumen" style="color:#444;margin-bottom:10px;">' . html_entity_decode($resum, ENT_QUOTES | ENT_HTML5, 'UTF-8') . '</div>';
            }
            $slug = $lang === 'ca' ? ($entrada['slug_ca'] ?? $entrada['slug_es'] ?? '') : ($entrada['slug_es'] ?? $entrada['slug_ca'] ?? '');
            $href = $slug ? 'entrada.php?slug=' . rawurlencode($slug) : 'entrada.php?id=' . $entrada['id_entrada'];
            echo '<a class="entrada-link" style="color:#a89968;text-decoration:none;font-weight:600;" href="' . $href . '">Leer más</a>';
            echo '</div>';
            // Columna lateral (aside)
            echo '<aside class="blog-aside">';
            echo '<h3 style="font-size:1.1em;color:#a89968;margin-bottom:12px;">Filtrar</h3>';
            echo '<form method="get" action="blog.php" style="display:flex;flex-direction:column;gap:16px;">';
            echo '<div>';
            echo '<label for="cat" style="font-size:0.95em;color:#888;display:block;margin-bottom:4px;">Categoría</label>';
            echo '<select name="cat" id="cat" style="padding:6px 12px;border-radius:6px;border:1px solid #ccc;min-width:120px;width:100%;">';
            echo '<option value="">Todas</option>';
            foreach ($catsSelect as $cat) {
                $selected = (isset($_GET['cat']) && $_GET['cat'] == $cat['id_category']) ? 'selected' : '';
                echo '<option value="'.$cat['id_category'].'" '.$selected.'>'.htmlspecialchars($cat['nom']).'</option>';
            }
            echo '</select>';
            echo '</div>';
            echo '<div>';
            echo '<label for="eti" style="font-size:0.95em;color:#888;display:block;margin-bottom:4px;">Etiqueta</label>';
            echo '<select name="eti" id="eti" style="padding:6px 12px;border-radius:6px;border:1px solid #ccc;min-width:120px;width:100%;">';
            echo '<option value="">Todas</option>';
            foreach ($etisSelect as $eti) {
                $selected = (isset($_GET['eti']) && $_GET['eti'] == $eti['id_etiqueta']) ? 'selected' : '';
                echo '<option value="'.$eti['id_etiqueta'].'" '.$selected.'>'.htmlspecialchars($eti['nom']).'</option>';
            }
            echo '</select>';
            echo '</div>';
            echo '<div>';
            echo '<label for="search" style="font-size:0.95em;color:#888;display:block;margin-bottom:4px;">Buscar</label>';
            $searchVal = isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '';
            echo '<input type="text" name="search" id="search" value="'.$searchVal.'" style="padding:6px 12px;border-radius:6px;border:1px solid #ccc;min-width:120px;width:100%;">';
            echo '</div>';
            echo '<button type="submit" style="background:#a89968;color:#fff;padding:8px 18px;border:none;border-radius:6px;font-weight:600;cursor:pointer;width:100%;margin-top:8px;">Filtrar</button>';
            echo '</form>';
            echo '</aside>';
            echo '</div>';
            // 2+2 següents
            for ($i = 1; $i < count($latest); $i += 2) {
                echo '<div class="blog-row">';
                $itemsInRow = min(2, count($latest) - $i);
                for ($j = 0; $j < $itemsInRow; $j++) {
                    $entrada = $latest[$i + $j];
                    echo '<div class="blog-col">';
                    if (!empty($entrada['imatge_portada'])) {
                        $imgSrc = resolve_media_url($entrada['imatge_portada']);
                        echo '<div class="entrada-thumb">';
                        echo '<img src="' . htmlspecialchars($imgSrc) . '" alt="Portada entrada" class="entrada-thumb-img">';
                        echo '</div>';
                    }
                    $titol = $lang === 'ca' ? ($entrada['titol_ca'] ?? $entrada['titol_es']) : ($entrada['titol_es'] ?? $entrada['titol_ca']);
                    $resum = $lang === 'ca' ? ($entrada['resum_ca'] ?? $entrada['resum_es']) : ($entrada['resum_es'] ?? $entrada['resum_ca']);
                    echo '<h2 class="entrada-titulo" style="font-size:1.1em;color:#333;margin-bottom:8px;">' . htmlspecialchars($titol) . '</h2>';
                    $tagsHtml = '';
                    if (!empty($entrada['categories_noms'])) {
                        $tagsHtml .= '<span style="margin-right:12px;color:#888;font-size:0.95em;"><i class="fas fa-folder"></i> ' . implode(', ', (array)$entrada['categories_noms']) . '</span>';
                    }
                    if (!empty($entrada['etiquetes_noms'])) {
                        $tagsHtml .= '<span style="color:#888;font-size:0.95em;"><i class="fas fa-tag"></i> ' . implode(', ', (array)$entrada['etiquetes_noms']) . '</span>';
                    }
                    if ($tagsHtml) {
                        echo '<div class="entrada-tags" style="margin-bottom:8px;">' . $tagsHtml . '</div>';
                    }
                    echo '<div class="entrada-meta" style="color:#888;font-size:0.95em;margin-bottom:10px;">';
                    echo '<i class="fas fa-calendar-alt"></i> ' . date('d/m/Y', strtotime($entrada['data_publicacio']));
                    if (!empty($entrada['autor_nom_complet'])) {
                        echo ' &middot; <i class="fas fa-user"></i> ' . htmlspecialchars($entrada['autor_nom_complet']);
                    }
                    echo '</div>';
                    if (!empty($resum)) {
                        echo '<div class="entrada-resumen" style="color:#444;margin-bottom:10px;">' . html_entity_decode($resum, ENT_QUOTES | ENT_HTML5, 'UTF-8') . '</div>';
                    }
                    $slug = $lang === 'ca' ? ($entrada['slug_ca'] ?? $entrada['slug_es'] ?? '') : ($entrada['slug_es'] ?? $entrada['slug_ca'] ?? '');
                    $href = $slug ? 'entrada.php?slug=' . rawurlencode($slug) : 'entrada.php?id=' . $entrada['id_entrada'];
                    echo '<a class="entrada-link" style="color:#a89968;text-decoration:none;font-weight:600;" href="' . $href . '">Leer más</a>';
                    echo '</div>';
                }
                if ($itemsInRow === 1) {
                    echo '<div class="blog-col"></div>';
                }
                echo '</div>';
            }
            // --- ENTRADES ADDICIONALS (3x3) ---
            $restants = array_slice($entradas, 5);
            // Enriquir restants amb categories, etiquetes i autor perquè el JS tingui aquesta informació
            foreach ($restants as &$r) {
                // categories
                $cats = [];
                $catObjs = $relCatEntModel->obtenirCategoriesEntrada($r['id_entrada'], getCurrentLanguage(), true);
                foreach ($catObjs as $cat) $cats[] = $cat['nom'];
                $r['categories_noms'] = $cats;
                // etiquetes
                $etis = [];
                $etiObjs = $relEtiEntModel->obtenirEtiquetesEntrada($r['id_entrada'], getCurrentLanguage(), true);
                foreach ($etiObjs as $eti) $etis[] = $eti['nom'];
                $r['etiquetes_noms'] = $etis;
                // autor
                $autorNom = '';
                if (!empty($r['id_autor'])) {
                    try {
                        $u = new UsuarisPanell($pdo);
                        $u->id_usuario = (int)$r['id_autor'];
                        if ($u->llegirPerId()) $autorNom = trim(($u->nombre ?? '') . ' ' . ($u->apellidos ?? ''));
                    } catch (Exception $e) { $autorNom = ''; }
                }
                $r['autor_nom_complet'] = $autorNom;
            }
            unset($r);
            $numRestants = count($restants);
            if ($numRestants > 0) {
                                echo '<div id="entrades-addicionals-container"></div>';
                                echo '<button id="btn-mostra-mes" style="display:block;margin:32px auto 0 auto;background:#a89968;color:#fff;padding:12px 32px;border:none;border-radius:6px;font-size:1.1em;font-weight:600;cursor:pointer;">Mostrar más</button>';
                                ?>
                                <script>
                                const entradesAdd = <?php echo json_encode(array_values($restants), JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES); ?>;
                                let offset = 0;
                                const perPage = 9;
                                const lang = '<?php echo $lang; ?>';
                                function renderEntradesAdd() {
                                    const container = document.getElementById("entrades-addicionals-container");
                                    let html = "";
                                    let filesMostrades = 0;
                                    for (let i = offset; i < entradesAdd.length && filesMostrades < 3; i += 3, filesMostrades++) {
                                        html += `<div class="blog-row">`;
                                        for (let j = 0; j < 3; j++) {
                                            if (i+j < entradesAdd.length) {
                                                const entrada = entradesAdd[i+j];
                                                html += `<div class="blog-col">`;
                                                if (entrada.imatge_portada) {
                                                    html += `<div class="entrada-thumb"><img src="${entrada.imatge_portada.replace(/\"/g, '&quot;')}" alt="Portada entrada" class="entrada-thumb-img"></div>`;
                                                }
                                                const titol = lang === "ca" ? (entrada.titol_ca || entrada.titol_es) : (entrada.titol_es || entrada.titol_ca);
                                                const resum = lang === "ca" ? (entrada.resum_ca || entrada.resum_es) : (entrada.resum_es || entrada.resum_ca);
                                                html += `<h2 class=\"entrada-titulo\" style=\"font-size:1.1em;color:#333;margin-bottom:8px;\">${titol ? titol.replace(/</g, '&lt;') : ''}</h2>`;
                                                let tagsHtml = "";
                                                if (entrada.categories_noms && entrada.categories_noms.length) {
                                                    tagsHtml += `<span style=\"margin-right:12px;color:#888;font-size:0.95em;\"><i class=\"fas fa-folder\"></i> ${entrada.categories_noms.join(', ')}</span>`;
                                                }
                                                if (entrada.etiquetes_noms && entrada.etiquetes_noms.length) {
                                                    tagsHtml += `<span style=\"color:#888;font-size:0.95em;\"><i class=\"fas fa-tag\"></i> ${entrada.etiquetes_noms.join(', ')}</span>`;
                                                }
                                                if (tagsHtml) html += `<div class=\"entrada-tags\" style=\"margin-bottom:8px;\">${tagsHtml}</div>`;
                                                let metaDate = entrada.data_publicacio ? (entrada.data_publicacio.substr(8,2)+'/'+entrada.data_publicacio.substr(5,2)+'/'+entrada.data_publicacio.substr(0,4)) : '';
                                                let autorTxt = entrada.autor_nom_complet ? (' &middot; <i class=\"fas fa-user\"></i> ' + String(entrada.autor_nom_complet).replace(/</g,'&lt;')) : '';
                                                html += `<div class=\"entrada-meta\" style=\"color:#888;font-size:0.95em;margin-bottom:10px;\"><i class=\"fas fa-calendar-alt\"></i> ${metaDate}${autorTxt}</div>`;
                                                if (resum) html += `<div class=\"entrada-resumen\" style=\"color:#444;margin-bottom:10px;\">${resum}</div>`;
                                                const slug = lang === "ca" ? (entrada.slug_ca || entrada.slug_es || "") : (entrada.slug_es || entrada.slug_ca || "");
                                                const href = slug ? `entrada.php?slug=${encodeURIComponent(slug)}` : `entrada.php?id=${entrada.id_entrada}`;
                                                html += `<a class="entrada-link" style="color:#a89968;text-decoration:none;font-weight:600;" href="${href}">Leer más</a>`;
                                                html += `</div>`;
                                            } else {
                                                html += `<div class="blog-col"></div>`;
                                            }
                                        }
                                        html += `</div>`;
                                    }
                                    container.innerHTML += html;
                                    offset += filesMostrades * 3;
                                    if (offset >= entradesAdd.length) document.getElementById("btn-mostra-mes").style.display = "none";
                                }
                                // Only render additional entries when the user clicks the button
                                document.getElementById("btn-mostra-mes").addEventListener("click", renderEntradesAdd);
                                </script>
                                <?php
            }
        }
        ?>
    </main>
    <?php include '_includes/footer.php'; ?>
    <script src="../js/site-nav.js"></script>
    <script src="../js/blog-router.js"></script>
    <script src="../js/language.js"></script>
</body>
</html>
