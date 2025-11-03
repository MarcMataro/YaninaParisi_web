<?php
// Mostrar una entrada por slug o id (Español)
if (session_status() === PHP_SESSION_NONE) session_start();
// Forzar idioma español
$_SESSION['language'] = 'es';

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../classes/connexio.php';
require_once __DIR__ . '/../classes/entrades.php';
require_once __DIR__ . '/../classes/usuaris_panell.php';

$connexio = Connexio::getInstance();
$pdo = $connexio->getConnexio();
$entradaModel = new Entrada($pdo);

$row = false;
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$slug = isset($_GET['slug']) ? trim((string)$_GET['slug']) : '';

if ($slug !== '') {
    $row = $entradaModel->buscarPerSlug($slug, 'es', true);
}

if (!$row && $id > 0) {
    $row = $entradaModel->llegirUn($id);
    if ($row && (!isset($row['estat']) || $row['estat'] !== Entrada::ESTAT_PUBLICAT || (isset($row['visible']) && !$row['visible']))) {
        $row = false;
    }
}

if (!$row) {
    ?>
    <!doctype html>
    <html lang="es">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width,initial-scale=1">
        <title>Entrada no disponible | Yanina Parisi</title>
        <link rel="stylesheet" href="../css/estils.css">
    </head>
    <body>
        <?php include '_includes/navigation.php'; ?>
        <main class="container" style="max-width:900px;margin:60px auto;padding:32px;background:#fff;border-radius:12px;">
            <h1>Entrada no disponible en este idioma</h1>
            <p>Esta entrada no está publicada o traducida en castellano. Puedes consultar la versión en catalán si existeix, o volver al blog.</p>
            <p><a href="<?php echo dirname($_SERVER['SCRIPT_NAME']) . '/blog.php'; ?>">&larr; Volver al blog</a></p>
        </main>
        <?php include '_includes/footer.php'; ?>
    </body>
    </html>
    <?php
    exit;
}

// Si tenemos $row
$entrada = $row;
if (!empty($entrada['id_entrada'])) {
    try {
        $entradaModel->incrementarVisualitzacions((int)$entrada['id_entrada']);
    } catch (Exception $e) {
        // ignore
    }
}

// Cargar nombre completo del autor para mostrar en la cabecera
$entrada['autor_nom_complet'] = '';
if (!empty($entrada['id_autor'])) {
    try {
        $u = new UsuarisPanell($pdo);
        $u->id_usuario = (int)$entrada['id_autor'];
        if ($u->llegirPerId()) $entrada['autor_nom_complet'] = trim(($u->nombre ?? '') . ' ' . ($u->apellidos ?? ''));
    } catch (Exception $e) {
        $entrada['autor_nom_complet'] = '';
    }
}

?><!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title><?php echo htmlspecialchars($entrada['meta_title_es'] ?? $entrada['titol_es'] ?? 'Entrada'); ?> | Yanina Parisi</title>
    <meta name="description" content="<?php echo htmlspecialchars($entrada['meta_description_es'] ?? strip_tags($entrada['resum_es'] ?? '')); ?>">
    <link rel="stylesheet" href="../css/estils.css">
    <link rel="icon" type="image/png" sizes="32x32" href="../img/Logo32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="../img/Logo16.png">
</head>
<body>
    <?php include '_includes/navigation.php'; ?>
    <?php
        // Prepare title and image URL
        $title = htmlspecialchars($entrada['titol_es'] ?? $entrada['titol_ca']);
        $imgUrl = '';
        if (!empty($entrada['imatge_portada'])) {
            $imgUrl = resolve_media_url($entrada['imatge_portada']);
        }
    ?>
    <?php if ($imgUrl): ?>
        <!-- DEBUG ENTRY HERO: raw_imagen_portada="<?php echo htmlspecialchars($entrada['imatge_portada']); ?>" resolved_img="<?php echo htmlspecialchars($imgUrl); ?>" -->
        <section class="entry-hero">
            <img src="<?php echo htmlspecialchars($imgUrl); ?>" alt="<?php echo htmlspecialchars($entrada['alt_imatge_es'] ?? ''); ?>" class="entry-hero-img">
            <div class="entry-hero-content">
                <h1><?php echo $title; ?></h1>
            </div>
        </section>
    <?php endif; ?>

    <main class="container" style="max-width:900px;margin:60px auto;padding:32px;background:#fff;border-radius:12px;">

        <?php
            // Breadcrumbs: Home > Blog > Post (ES)
            if (function_exists('render_breadcrumbs')) {
                render_breadcrumbs([
                    ['label' => t('nav_home'), 'url' => '/es/home.php'],
                    ['label' => t('nav_blog'), 'url' => '/es/blog.php'],
                    ['label' => $title]
                ]);
            }
        ?>

        <article>
            <div style="color:#888;margin-bottom:14px;">
                <i class="fas fa-calendar-alt"></i> <?php echo date('d/m/Y', strtotime($entrada['data_publicacio'])); ?>
                <?php if (!empty($entrada['autor_nom_complet'])): ?>
                    &middot; <i class="fas fa-user"></i> <?php echo htmlspecialchars($entrada['autor_nom_complet']); ?>
                <?php endif; ?>
            </div>
            <?php if (!$imgUrl && !empty($entrada['imatge_portada'])): ?>
                <img src="<?php echo htmlspecialchars($entrada['imatge_portada']); ?>" alt="<?php echo htmlspecialchars($entrada['alt_imatge_es'] ?? ''); ?>" style="width:100%;border-radius:8px;margin-bottom:16px;object-fit:cover;">
            <?php endif; ?>
            <div class="entrada-contenido">
                <?php echo $entrada['contingut_es'] ?? $entrada['contingut_ca']; ?>
            </div>
        </article>
    <p style="margin-top:28px;"><a href="<?php echo dirname($_SERVER['SCRIPT_NAME']) . '/blog.php'; ?>">&larr; Volver al blog</a></p>
    </main>
    <?php include '_includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@7.0.0/js/all.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Libre+Baskerville:wght@700&display=swap" rel="stylesheet">
    <script src="../js/language.js"></script>
    <script src="../js/site-nav.js"></script>
</body>
</html>
