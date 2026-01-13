<?php
// Activar errors per debug temporal
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Inicialitzar sessió si no està iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Forçar idioma català en aquesta pàgina
$_SESSION['language'] = 'ca';
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
    // SEO extraction: follow the same pattern as home.php / clinica.php
    $base_url = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'];
    require_once __DIR__ . '/../classes/seo_onpage.php';

    $pagina_seo = null;
    $seoTitle = null;
    $lang = getCurrentLanguage();

    // 1) intentar carregar per tipus 'blog' (pàgina configurada)
    try {
        $items = SEO_OnPage::llistarPaginesActives('blog');
        if (!empty($items) && isset($items[0]) && $items[0] instanceof SEO_OnPage) {
            $pagina_seo = $items[0];
            $seoTitle = $pagina_seo->getTitle($lang) ?: null;
        }
    } catch (Exception $e) { }

    // 2) fallback: intentar carregar per URL relativa
    if (!$seoTitle) {
        $tries = ['/blog.php','/blog','/ca/blog.php','/ca/blog','/es/blog.php','/es/blog'];
        foreach ($tries as $r) {
            try {
                $tmp = SEO_OnPage::carregarPerUrl($r, $lang);
                if ($tmp instanceof SEO_OnPage) { $pagina_seo = $tmp; $seoTitle = $pagina_seo->getTitle($lang) ?: null; break; }
            } catch (Exception $e) { }
        }
    }

    // 3) fallback genèric
    if (!$seoTitle) {
        $seoTitle = ($lang === 'es') ? 'Blog - Yanina Parisi - Psicóloga' : 'Blog - Yanina Parisi - Psicòloga';
    }

    // meta description
    $seoDescription = null;
    if (isset($pagina_seo) && $pagina_seo instanceof SEO_OnPage) {
        $seoDescription = $pagina_seo->getMetaDescription($lang) ?: null;
    }
    if (!$seoDescription) $seoDescription = t('meta_description');

    // canonical
    $canonical = null;
    if (isset($pagina_seo) && $pagina_seo instanceof SEO_OnPage) {
        $canonical = $pagina_seo->getCanonicalUrl($lang);
    }
    if (!$canonical) $canonical = $base_url . (($lang === 'es') ? '/es/blog.php' : '/ca/blog.php');
    ?>
    <title><?php echo htmlspecialchars($seoTitle); ?></title>
    <meta name="description" content="<?php echo htmlspecialchars($seoDescription); ?>">
    <meta name="keywords" content="<?php echo t('meta_keywords'); ?>">
    <meta name="author" content="Yanina Parisi">
    <meta name="robots" content="index, follow">
    <meta name="theme-color" content="#aa9e6b">
    <link rel="canonical" href="<?php echo htmlspecialchars($canonical); ?>">

    <!-- Open Graph -->
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
    <meta property="og:locale" content="<?php echo $lang === 'ca' ? 'ca_ES' : 'es_ES'; ?>">

    <!-- Twitter -->
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

    <!-- Minimal JSON-LD for the listing -->
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
        <img src="<?php echo htmlspecialchars(resolve_media_url('img/IMG_2285.png')); ?>" alt="Portada" class="hero-img">
        <div class="container hero-content">
            <h1 class="hero-title"><?php echo htmlspecialchars(isset($pagina_seo) && $pagina_seo instanceof SEO_OnPage ? $pagina_seo->getH1($lang) : t('nav_blog')); ?></h1>
            <h2 class="hero-subtitle">
                Descobreix reflexions, consells i recursos per al teu benestar emocional.
            </h2>
        </div>
    </section>
    <main id="blog-main" class="container blog-main">
        <?php
            // Breadcrumbs: Home > Blog
            if (function_exists('render_breadcrumbs')) {
                render_breadcrumbs([
                    ['label' => t('nav_home'), 'url' => 'home.php'],
                    ['label' => t('nav_blog')]
                ]);
            }
        ?>
        
        <!-- Filtres -->
        <div class="blog-filters">
            <h3>Filtrar entrades</h3>
            <form method="get" action="blog.php" class="blog-filters-form">
                <div class="blog-filter-group">
                    <label for="cat">Categoria</label>
                    <select name="cat" id="cat">
                        <option value="">Totes</option>
                        <?php foreach ($catsSelect as $cat): ?>
                            <option value="<?php echo $cat['id_category']; ?>" <?php echo (isset($_GET['cat']) && $_GET['cat'] == $cat['id_category']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat['nom']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="blog-filter-group">
                    <label for="eti">Etiqueta</label>
                    <select name="eti" id="eti">
                        <option value="">Totes</option>
                        <?php foreach ($etisSelect as $eti): ?>
                            <option value="<?php echo $eti['id_etiqueta']; ?>">
                                <?php echo htmlspecialchars($eti['nom']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="blog-filter-group">
                    <label for="search">Cercar</label>
                    <input type="text" name="search" id="search" value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>" placeholder="Paraules clau...">
                </div>
                <button type="submit" class="blog-filter-btn">Aplicar filtres</button>
            </form>
        </div>
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
        
        if (empty($entradas)) {
            echo '<div style="text-align:center;padding:60px 0;color:#999;font-size:1.2em;">No hi ha entrades de blog per mostrar</div>';
        } else {
            $lang = getCurrentLanguage();
            
            // Carregar categories, etiquetes i autor per totes les entrades
            foreach ($entradas as &$entrada) {
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
            // Autor: carregar nom complet de la taula usuarios_panel (UsuarisPanell)
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
            $entrada['autor_nom'] = $autorNom;
        }
        unset($entrada);
            
            // Mostrar les primeres 9 entrades
            $primeres = array_slice($entradas, 0, 9);
            
            echo '<div class="blog-grid">';
            
            // Renderitzar cada entrada com a targeta
            foreach ($primeres as $entrada) {
                echo '<article class="blog-card">';
                
                // Imatge
                if (!empty($entrada['imatge_portada'])) {
                    $imgSrc = resolve_media_url($entrada['imatge_portada']);
                    $titol = $lang === 'ca' ? ($entrada['titol_ca'] ?? $entrada['titol_es']) : ($entrada['titol_es'] ?? $entrada['titol_ca']);
                    $slug = $lang === 'ca' ? ($entrada['slug_ca'] ?? $entrada['slug_es'] ?? '') : ($entrada['slug_es'] ?? $entrada['slug_ca'] ?? '');
                    $href = $slug ? 'entrada.php?slug=' . rawurlencode($slug) : 'entrada.php?id=' . $entrada['id_entrada'];
                    echo '<a href="' . $href . '" class="blog-card-image">';
                    echo '<img src="' . htmlspecialchars($imgSrc) . '" alt="' . htmlspecialchars($titol) . '">';
                    echo '</a>';
                }
                
                echo '<div class="blog-card-content">';
                
                // Títol
                $titol = $lang === 'ca' ? ($entrada['titol_ca'] ?? $entrada['titol_es']) : ($entrada['titol_es'] ?? $entrada['titol_ca']);
                $slug = $lang === 'ca' ? ($entrada['slug_ca'] ?? $entrada['slug_es'] ?? '') : ($entrada['slug_es'] ?? $entrada['slug_ca'] ?? '');
                $href = $slug ? 'entrada.php?slug=' . rawurlencode($slug) : 'entrada.php?id=' . $entrada['id_entrada'];
                echo '<h3 class="blog-card-title"><a href="' . $href . '" style="color:inherit;text-decoration:none;">' . htmlspecialchars($titol) . '</a></h3>';
                
                // Meta (data i autor)
                echo '<div class="blog-card-meta">';
                echo '<span><i class="fas fa-calendar-alt"></i> ' . date('d/m/Y', strtotime($entrada['data_publicacio'])) . '</span>';
                if (!empty($entrada['autor_nom_complet'])) {
                    echo '<span><i class="fas fa-user"></i> ' . htmlspecialchars($entrada['autor_nom_complet']) . '</span>';
                }
                echo '</div>';
                
                // Tags (categories i etiquetes)
                if (!empty($entrada['categories_noms']) || !empty($entrada['etiquetes_noms'])) {
                    echo '<div class="blog-card-tags">';
                    if (!empty($entrada['categories_noms'])) {
                        foreach ((array)$entrada['categories_noms'] as $cat) {
                            echo '<span><i class="fas fa-folder"></i> ' . htmlspecialchars($cat) . '</span>';
                        }
                    }
                    if (!empty($entrada['etiquetes_noms'])) {
                        foreach ((array)$entrada['etiquetes_noms'] as $eti) {
                            echo '<span><i class="fas fa-tag"></i> ' . htmlspecialchars($eti) . '</span>';
                        }
                    }
                    echo '</div>';
                }
                
                // Resum
                $resum = $lang === 'ca' ? ($entrada['resum_ca'] ?? $entrada['resum_es']) : ($entrada['resum_es'] ?? $entrada['resum_ca']);
                if (!empty($resum)) {
                    echo '<div class="blog-card-summary">' . html_entity_decode($resum, ENT_QUOTES | ENT_HTML5, 'UTF-8') . '</div>';
                }
                
                // Enllaç
                echo '<a class="blog-card-link" href="' . $href . '">Llegir més <i class="fas fa-arrow-right"></i></a>';
                
                echo '</div>';
                echo '</article>';
            }
            
            echo '</div>';
            
            // --- ENTRADES ADDICIONALS ---
            $restants = array_slice($entradas, 9);
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
                                echo '<button id="btn-mostra-mes" class="blog-load-more">Mostrar més</button>';
                                ?>
                                <script>
                                const entradesAdd = <?php echo json_encode(array_values($restants), JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES); ?>;
                                let offset = 0;
                                const lang = '<?php echo $lang; ?>';
                                
                                function renderEntradesAdd() {
                                    const container = document.getElementById("entrades-addicionals-container");
                                    const grid = document.createElement('div');
                                    grid.className = 'blog-grid';
                                    
                                    for (let i = offset; i < Math.min(offset + 9, entradesAdd.length); i++) {
                                        const entrada = entradesAdd[i];
                                        const article = document.createElement('article');
                                        article.className = 'blog-card';
                                        
                                        const titol = lang === 'ca' ? (entrada.titol_ca || entrada.titol_es) : (entrada.titol_es || entrada.titol_ca);
                                        const resum = lang === 'ca' ? (entrada.resum_ca || entrada.resum_es) : (entrada.resum_es || entrada.resum_ca);
                                        const slug = lang === 'ca' ? (entrada.slug_ca || entrada.slug_es || '') : (entrada.slug_es || entrada.slug_ca || '');
                                        const href = slug ? `entrada.php?slug=${encodeURIComponent(slug)}` : `entrada.php?id=${entrada.id_entrada}`;
                                        
                                        // Imatge
                                        if (entrada.imatge_portada) {
                                            const imgLink = document.createElement('a');
                                            imgLink.href = href;
                                            imgLink.className = 'blog-card-image';
                                            imgLink.innerHTML = `<img src="${entrada.imatge_portada}" alt="${titol}">`;
                                            article.appendChild(imgLink);
                                        }
                                        
                                        const content = document.createElement('div');
                                        content.className = 'blog-card-content';
                                        
                                        // Títol
                                        content.innerHTML += `<h3 class="blog-card-title"><a href="${href}" style="color:inherit;text-decoration:none;">${titol}</a></h3>`;
                                        
                                        // Meta
                                        let metaDate = entrada.data_publicacio ? (entrada.data_publicacio.substr(8,2)+'/'+entrada.data_publicacio.substr(5,2)+'/'+entrada.data_publicacio.substr(0,4)) : '';
                                        let metaHtml = `<div class="blog-card-meta"><span><i class="fas fa-calendar-alt"></i> ${metaDate}</span>`;
                                        if (entrada.autor_nom_complet) {
                                            metaHtml += `<span><i class="fas fa-user"></i> ${entrada.autor_nom_complet}</span>`;
                                        }
                                        metaHtml += '</div>';
                                        content.innerHTML += metaHtml;
                                        
                                        // Tags
                                        if ((entrada.categories_noms && entrada.categories_noms.length) || (entrada.etiquetes_noms && entrada.etiquetes_noms.length)) {
                                            let tagsHtml = '<div class="blog-card-tags">';
                                            if (entrada.categories_noms && entrada.categories_noms.length) {
                                                entrada.categories_noms.forEach(cat => {
                                                    tagsHtml += `<span><i class="fas fa-folder"></i> ${cat}</span>`;
                                                });
                                            }
                                            if (entrada.etiquetes_noms && entrada.etiquetes_noms.length) {
                                                entrada.etiquetes_noms.forEach(eti => {
                                                    tagsHtml += `<span><i class="fas fa-tag"></i> ${eti}</span>`;
                                                });
                                            }
                                            tagsHtml += '</div>';
                                            content.innerHTML += tagsHtml;
                                        }
                                        
                                        // Resum
                                        if (resum) {
                                            content.innerHTML += `<div class="blog-card-summary">${resum}</div>`;
                                        }
                                        
                                        // Enllaç
                                        content.innerHTML += `<a class="blog-card-link" href="${href}">Llegir més <i class="fas fa-arrow-right"></i></a>`;
                                        
                                        article.appendChild(content);
                                        grid.appendChild(article);
                                    }
                                    
                                    container.appendChild(grid);
                                    offset += 9;
                                    
                                    if (offset >= entradesAdd.length) {
                                        document.getElementById("btn-mostra-mes").style.display = "none";
                                    }
                                }
                                
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
