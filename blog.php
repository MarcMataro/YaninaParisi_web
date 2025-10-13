<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (isset($_GET['lang'])) {
    if (in_array($_GET['lang'], array('ca', 'es'))) {
        $_SESSION['language'] = $_GET['lang'];
    }
    $redirect_url = strtok($_SERVER["REQUEST_URI"], '?');
    header('Location: ' . $redirect_url);
    exit;
}
include 'includes/lang.php';
?>
<!DOCTYPE html>
<html lang="<?php echo getCurrentLanguage(); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo t('nav_blog'); ?> | Yanina Parisi</title>
    <meta name="description" content="Consulta les últimes entrades del blog de Yanina Parisi.">
    <link rel="stylesheet" href="css/estils.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Libre+Baskerville:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet">
</head>
<body>
    <?php include 'includes/navigation.php'; ?>
    <!-- Hero Section Blog -->
    <section class="hero blog-hero">
        <div class="container hero-content">
            <h1 class="hero-title"><?php echo t('blog_hero_title'); ?></h1>
            <h2 class="hero-subtitle">
                <?php echo getCurrentLanguage() === 'ca' ? t('blog_hero_subtitle') : t('blog_hero_subtitle_es'); ?>
            </h2>
        </div>
    </section>
    <main class="container" style="max-width:900px;margin:0 auto 60px auto;background:#fff;border-radius:12px;padding:32px;">
        <h2 style="font-size:1.5em;color:#a89968;margin-bottom:28px;">
            <?php echo getCurrentLanguage() === 'ca' ? t('blog_latest_title') : t('blog_latest_title_es'); ?>
        </h2>
    <?php
    require_once __DIR__ . '/classes/connexio.php';
    require_once __DIR__ . '/classes/entrades.php';
    require_once __DIR__ . '/classes/categories.php';
    require_once __DIR__ . '/classes/etiquetes.php';
    require_once __DIR__ . '/classes/rel_cat_ent.php';
    require_once __DIR__ . '/classes/rel_eti_ent.php';
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
        <?php
        require_once __DIR__ . '/classes/connexio.php';
        require_once __DIR__ . '/classes/entrades.php';
        require_once __DIR__ . '/classes/categories.php';
        require_once __DIR__ . '/classes/etiquetes.php';
        require_once __DIR__ . '/classes/rel_cat_ent.php';
        require_once __DIR__ . '/classes/rel_eti_ent.php';
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
        }
        unset($entrada);
        if (empty($latest)) {
            echo '<div style="text-align:center;padding:60px 0;color:#999;font-size:1.2em;">'.(getCurrentLanguage() === 'ca' ? t('blog_no_entries') : t('blog_no_entries_es')).'</div>';
        } else {
            // Primera entrada destacada + columna lateral
            $entrada = $latest[0];
            echo '<div style="display:flex;gap:32px;align-items:flex-start;margin-bottom:40px;">';
            echo '<div style="flex:0 0 80%;">';
            if (!empty($entrada['imatge_portada'])) {
                $imgSrc = strpos($entrada['imatge_portada'], 'img/') === 0 ? $entrada['imatge_portada'] : 'img/' . $entrada['imatge_portada'];
                echo '<img src="' . htmlspecialchars($imgSrc) . '" alt="Portada entrada" style="width:100%;border-radius:8px;margin-bottom:16px;object-fit:cover;">';
            }
            $lang = getCurrentLanguage();
            $titol = $lang === 'ca' ? ($entrada['titol_ca'] ?? $entrada['titol_es']) : ($entrada['titol_es'] ?? $entrada['titol_ca']);
            $resum = $lang === 'ca' ? ($entrada['resum_ca'] ?? $entrada['resum_es']) : ($entrada['resum_es'] ?? $entrada['resum_ca']);
            echo '<h2 class="entrada-titulo" style="font-size:1.4em;color:#333;margin-bottom:8px;">' . htmlspecialchars($titol) . '</h2>';
            // Categories i etiquetes
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
            echo '</div>';
            if (!empty($resum)) {
                echo '<div class="entrada-resumen" style="color:#444;margin-bottom:10px;">' . html_entity_decode($resum, ENT_QUOTES | ENT_HTML5, 'UTF-8') . '</div>';
            }
            echo '<a class="entrada-link" style="color:#a89968;text-decoration:none;font-weight:600;" href="entrada.php?id=' . $entrada['id_entrada'] . '">'.($lang === 'ca' ? t('blog_read_more') : t('blog_read_more_es')).'</a>';
            echo '</div>';
            // Columna lateral (20%)
            echo '<aside style="flex:0 0 20%;background:#f7f7f7;border-radius:8px;padding:18px;min-height:220px;">';
            echo '<h3 style="font-size:1.1em;color:#a89968;margin-bottom:12px;">Filtrar</h3>';
            // Formulari de filtres
            echo '<form method="get" action="blog.php" style="display:flex;flex-direction:column;gap:16px;">';
            echo '<div>';
            echo '<label for="cat" style="font-size:0.95em;color:#888;display:block;margin-bottom:4px;">'.(getCurrentLanguage() === 'ca' ? 'Categoria' : 'Categoría').'</label>';
            echo '<select name="cat" id="cat" style="padding:6px 12px;border-radius:6px;border:1px solid #ccc;min-width:120px;width:100%;">';
            echo '<option value="">'.(getCurrentLanguage() === 'ca' ? 'Totes' : 'Todas').'</option>';
            foreach ($catsSelect as $cat) {
                $selected = (isset($_GET['cat']) && $_GET['cat'] == $cat['id_category']) ? 'selected' : '';
                echo '<option value="'.$cat['id_category'].'" '.$selected.'>'.htmlspecialchars($cat['nom']).'</option>';
            }
            echo '</select>';
            echo '</div>';
            echo '<div>';
            echo '<label for="eti" style="font-size:0.95em;color:#888;display:block;margin-bottom:4px;">'.(getCurrentLanguage() === 'ca' ? 'Etiqueta' : 'Etiqueta').'</label>';
            echo '<select name="eti" id="eti" style="padding:6px 12px;border-radius:6px;border:1px solid #ccc;min-width:120px;width:100%;">';
            echo '<option value="">'.(getCurrentLanguage() === 'ca' ? 'Totes' : 'Todas').'</option>';
            foreach ($etisSelect as $eti) {
                $selected = (isset($_GET['eti']) && $_GET['eti'] == $eti['id_etiqueta']) ? 'selected' : '';
                echo '<option value="'.$eti['id_etiqueta'].'" '.$selected.'>'.htmlspecialchars($eti['nom']).'</option>';
            }
            echo '</select>';
            echo '</div>';
            echo '<div>';
            echo '<label for="search" style="font-size:0.95em;color:#888;display:block;margin-bottom:4px;">'.(getCurrentLanguage() === 'ca' ? 'Cerca' : 'Buscar').'</label>';
            $searchVal = isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '';
            echo '<input type="text" name="search" id="search" value="'.$searchVal.'" style="padding:6px 12px;border-radius:6px;border:1px solid #ccc;min-width:120px;width:100%;">';
            echo '</div>';
            echo '<button type="submit" style="background:#a89968;color:#fff;padding:8px 18px;border:none;border-radius:6px;font-weight:600;cursor:pointer;width:100%;margin-top:8px;">'.(getCurrentLanguage() === 'ca' ? 'Filtrar' : 'Filtrar').'</button>';
            echo '</form>';
            echo '</aside>';
            echo '</div>';
            // Las otras cuatro entradas en filas de dos
            for ($i = 1; $i < count($latest); $i += 2) {
                echo '<div style="display:flex;gap:32px;margin-bottom:32px;">';
                $itemsInRow = min(2, count($latest) - $i);
                for ($j = 0; $j < $itemsInRow; $j++) {
                    $entrada = $latest[$i + $j];
                    echo '<div style="flex:1;">';
                    if (!empty($entrada['imatge_portada'])) {
                        $imgSrc = strpos($entrada['imatge_portada'], 'img/') === 0 ? $entrada['imatge_portada'] : 'img/' . $entrada['imatge_portada'];
                        echo '<img src="' . htmlspecialchars($imgSrc) . '" alt="Portada entrada" style="width:100%;border-radius:8px;margin-bottom:16px;object-fit:cover;">';
                    }
                    $titol = $lang === 'ca' ? ($entrada['titol_ca'] ?? $entrada['titol_es']) : ($entrada['titol_es'] ?? $entrada['titol_ca']);
                    $resum = $lang === 'ca' ? ($entrada['resum_ca'] ?? $entrada['resum_es']) : ($entrada['resum_es'] ?? $entrada['resum_ca']);
                    echo '<h2 class="entrada-titulo" style="font-size:1.1em;color:#333;margin-bottom:8px;">' . htmlspecialchars($titol) . '</h2>';
                    // Categories i etiquetes
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
                    echo '</div>';
                    if (!empty($resum)) {
                        echo '<div class="entrada-resumen" style="color:#444;margin-bottom:10px;">' . html_entity_decode($resum, ENT_QUOTES | ENT_HTML5, 'UTF-8') . '</div>';
                    }
                    echo '<a class="entrada-link" style="color:#a89968;text-decoration:none;font-weight:600;" href="entrada.php?id=' . $entrada['id_entrada'] . '">'.($lang === 'ca' ? t('blog_read_more') : t('blog_read_more_es')).'</a>';
                    echo '</div>';
                }
                // Si solo hay una entrada, añadir un div vacío para ocupar el otro 50%
                if ($itemsInRow === 1) {
                    echo '<div style="flex:1;"></div>';
                }
                echo '</div>';
            }
        }
        ?>
    </main>
    <?php include 'includes/footer.php'; ?>
    <script>
        // Efecte scroll per al header
        document.addEventListener('scroll', function() {
            const header = document.querySelector('header');
            if (window.scrollY > 50) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }
        });
        // Script per al selector d'idioma
        function changeLanguage(lang) {
            window.location.href = window.location.pathname + '?lang=' + lang;
        }
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.lang-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const lang = this.getAttribute('data-lang');
                    document.querySelectorAll('.lang-btn').forEach(b => b.classList.remove('active'));
                    document.querySelectorAll(`.lang-btn[data-lang="${lang}"]`).forEach(b => b.classList.add('active'));
                    changeLanguage(lang);
                });
            });
        });
    </script>
</body>
</html>
