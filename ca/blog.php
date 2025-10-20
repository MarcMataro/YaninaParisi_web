<?php
// Inicialitzar sessió si no està iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Debug ABANS del processament
echo "<!-- DEBUG INDEX ABANS: GET lang: " . ($_GET['lang'] ?? 'no definit') . " -->";
echo "<!-- DEBUG INDEX ABANS: Session lang abans: " . ($_SESSION['language'] ?? 'no definit') . " -->";

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
    <title><?php echo t('nav_blog'); ?> | Yanina Parisi</title>
    <meta name="description" content="Consulta les últimes entrades del blog de Yanina Parisi.">
    <link rel="stylesheet" href="../css/estils.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@7.0.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Libre+Baskerville:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet">
</head>
<body>
    <?php include '_includes/navigation.php'; ?>
    <!-- Hero Section Blog -->
    <section class="hero blog-hero">
        <div class="container hero-content">
            <h1 class="hero-title">Blog</h1>
            <h2 class="hero-subtitle">
                Descobreix reflexions, consells i recursos per al teu benestar emocional.
            </h2>
        </div>
    </section>
    <main class="container" style="max-width:900px;margin:0 auto 60px auto;background:#fff;border-radius:12px;padding:32px;">
        <h2 style="font-size:1.5em;color:#a89968;margin-bottom:28px;">
            Últimes publicacions
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
        }
        unset($entrada);
        if (empty($latest)) {
            echo '<div style="text-align:center;padding:60px 0;color:#999;font-size:1.2em;">No hi ha entrades de blog per mostrar</div>';
        } else {
            // Primera entrada destacada + columna lateral
            $entrada = $latest[0];
            echo '<div style="display:flex;gap:32px;align-items:flex-start;margin-bottom:40px;">';
            echo '<div style="flex:0 0 80%;">';
            if (!empty($entrada['imatge_portada'])) {
                $imgSrc = strpos($entrada['imatge_portada'], '../img/') === 0 ? $entrada['imatge_portada'] : '../img/' . $entrada['imatge_portada'];
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
            echo '<a class="entrada-link" style="color:#a89968;text-decoration:none;font-weight:600;" href="entrada.php?id=' . $entrada['id_entrada'] . '">Llegir més</a>';
            echo '</div>';
            // Columna lateral (20%)
            echo '<aside style="flex:0 0 20%;background:#f7f7f7;border-radius:8px;padding:18px;min-height:220px;">';
            echo '<h3 style="font-size:1.1em;color:#a89968;margin-bottom:12px;">Filtrar</h3>';
            // Formulari de filtres
            echo '<form method="get" action="blog.php" style="display:flex;flex-direction:column;gap:16px;">';
            echo '<div>';
            echo '<label for="cat" style="font-size:0.95em;color:#888;display:block;margin-bottom:4px;">Categoria</label>';
            echo '<select name="cat" id="cat" style="padding:6px 12px;border-radius:6px;border:1px solid #ccc;min-width:120px;width:100%;">';
            echo '<option value="">Totes</option>';
            foreach ($catsSelect as $cat) {
                $selected = (isset($_GET['cat']) && $_GET['cat'] == $cat['id_category']) ? 'selected' : '';
                echo '<option value="'.$cat['id_category'].'" '.$selected.'>'.htmlspecialchars($cat['nom']).'</option>';
            }
            echo '</select>';
            echo '</div>';
            echo '<div>';
            echo '<label for="eti" style="font-size:0.95em;color:#888;display:block;margin-bottom:4px;">Etiqueta</label>';
            echo '<select name="eti" id="eti" style="padding:6px 12px;border-radius:6px;border:1px solid #ccc;min-width:120px;width:100%;">';
            echo '<option value="">Totes</option>';
            foreach ($etisSelect as $eti) {
                $selected = (isset($_GET['eti']) && $_GET['eti'] == $eti['id_etiqueta']) ? 'selected' : '';
                echo '<option value="'.$eti['id_etiqueta'].'" '.$selected.'>'.htmlspecialchars($eti['nom']).'</option>';
            }
            echo '</select>';
            echo '</div>';
            echo '<div>';
            echo '<label for="search" style="font-size:0.95em;color:#888;display:block;margin-bottom:4px;">Cercar</label>';
            $searchVal = isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '';
            echo '<input type="text" name="search" id="search" value="'.$searchVal.'" style="padding:6px 12px;border-radius:6px;border:1px solid #ccc;min-width:120px;width:100%;">';
            echo '</div>';
            echo '<button type="submit" style="background:#a89968;color:#fff;padding:8px 18px;border:none;border-radius:6px;font-weight:600;cursor:pointer;width:100%;margin-top:8px;">Filtrar</button>';
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
                    echo '<a class="entrada-link" style="color:#a89968;text-decoration:none;font-weight:600;" href="entrada.php?id=' . $entrada['id_entrada'] . '">Llegir més</a>';
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
    <?php include '_includes/footer.php'; ?>
    <script>
        // Script per a la navegació suau
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                document.querySelector(this.getAttribute('href')).scrollIntoView({
                    behavior: 'smooth'
                });
            });
        });

        // Script per l'efecte scroll de la navegació
        window.addEventListener('scroll', function() {
            const header = document.querySelector('header');
            if (window.scrollY > 50) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }
        });

        // Script per al selector d'idioma

        
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.lang-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    // Obtenir l'idioma del data attribute
                    const lang = this.getAttribute('data-lang');
                    console.log('Botó clickat, idioma:', lang);
                    
                    // Eliminar classe active de tots els botons (tant desktop com mòbil)
                    document.querySelectorAll('.lang-btn').forEach(b => b.classList.remove('active'));
                    // Afegir classe active a tots els botons del mateix idioma
                    document.querySelectorAll(`.lang-btn[data-lang="${lang}"]`).forEach(b => b.classList.add('active'));
                    
                    // Tancar menú mòbil si està obert
                    const mobileMenuToggle = document.querySelector('.mobile-menu-toggle');
                    const navMenu = document.querySelector('.nav-menu ul');
                    if (mobileMenuToggle && navMenu) {
                        mobileMenuToggle.classList.remove('active');
                        navMenu.classList.remove('show');
                    }
                    
                    // Canviar idioma
                    changeLanguage(lang);
                });
            });

            // Funcionalitat del menú hamburguesa
            const mobileMenuToggle = document.querySelector('.mobile-menu-toggle');
            const navMenu = document.querySelector('.nav-menu ul');

            if (mobileMenuToggle && navMenu) {
                mobileMenuToggle.addEventListener('click', function() {
                    this.classList.toggle('active');
                    navMenu.classList.toggle('show');
                });

                // Tancar menú quan es clica un enllaç
                document.querySelectorAll('.nav-menu ul li a').forEach(link => {
                    link.addEventListener('click', function() {
                        mobileMenuToggle.classList.remove('active');
                        navMenu.classList.remove('show');
                    });
                });

                // Tancar menú quan es clica fora
                document.addEventListener('click', function(e) {
                    if (!mobileMenuToggle.contains(e.target) && !navMenu.contains(e.target)) {
                        mobileMenuToggle.classList.remove('active');
                        navMenu.classList.remove('show');
                    }
                });
            }
        });
    </script>
    <script src="../js/language.js"></script>
</body>
</html>
