<?php
// Mostrar una entrada por slug o id (Español)
if (session_status() === PHP_SESSION_NONE) session_start();
// Forzar idioma español
$_SESSION['language'] = 'es';

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../classes/connexio.php';
require_once __DIR__ . '/../classes/entrades.php';

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
    http_response_code(404);
    ?>
    <!doctype html>
    <html lang="es">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width,initial-scale=1">
        <title>Entrada no encontrada | Yanina Parisi</title>
        <link rel="stylesheet" href="../css/estils.css">
    </head>
    <body>
        <?php include '_includes/navigation.php'; ?>
        <main class="container" style="max-width:900px;margin:60px auto;padding:32px;background:#fff;border-radius:12px;">
            <h1>Entrada no encontrada</h1>
            <p>La entrada que buscas no existe o no está publicada.</p>
            <p><a href="blog.php">Volver al blog</a></p>
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

?><!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title><?php echo htmlspecialchars($entrada['meta_title_es'] ?? $entrada['titol_es'] ?? 'Entrada'); ?> | Yanina Parisi</title>
    <meta name="description" content="<?php echo htmlspecialchars($entrada['meta_description_es'] ?? strip_tags($entrada['resum_es'] ?? '')); ?>">
    <link rel="stylesheet" href="../css/estils.css">
</head>
<body>
    <?php include '_includes/navigation.php'; ?>
    <main class="container" style="max-width:900px;margin:60px auto;padding:32px;background:#fff;border-radius:12px;">
        <article>
            <h1><?php echo htmlspecialchars($entrada['titol_es'] ?? $entrada['titol_ca']); ?></h1>
            <div style="color:#888;margin-bottom:14px;"><i class="fas fa-calendar-alt"></i> <?php echo date('d/m/Y', strtotime($entrada['data_publicacio'])); ?></div>
            <?php if (!empty($entrada['imatge_portada'])): ?>
                <img src="<?php echo htmlspecialchars($entrada['imatge_portada']); ?>" alt="<?php echo htmlspecialchars($entrada['alt_imatge_es'] ?? ''); ?>" style="width:100%;border-radius:8px;margin-bottom:16px;object-fit:cover;">
            <?php endif; ?>
            <div class="entrada-contenido">
                <?php echo $entrada['contingut_es'] ?? $entrada['contingut_ca']; ?>
            </div>
        </article>
        <p style="margin-top:28px;"><a href="blog.php">&larr; Volver al blog</a></p>
    </main>
    <?php include '_includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@7.0.0/js/all.min.js"></script>
</body>
</html>
