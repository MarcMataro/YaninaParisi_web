<?php
// Mostrar una entrada per slug o id (Català)
if (session_status() === PHP_SESSION_NONE) session_start();
// Forçar idioma català
$_SESSION['language'] = 'ca';

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
    $row = $entradaModel->buscarPerSlug($slug, 'ca', true);
}

if (!$row && $id > 0) {
    $row = $entradaModel->llegirUn($id);
    // ensure published & visible
    if ($row && (!isset($row['estat']) || $row['estat'] !== Entrada::ESTAT_PUBLICAT || (isset($row['visible']) && !$row['visible']))) {
        $row = false;
    }
}

if (!$row) {
    http_response_code(404);
    ?>
    <!doctype html>
    <html lang="ca">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width,initial-scale=1">
        <title>Entrada no trobada | Yanina Parisi</title>
        <link rel="stylesheet" href="../css/estils.css">
    </head>
    <body>
        <?php include '_includes/navigation.php'; ?>
        <main class="container" style="max-width:900px;margin:60px auto;padding:32px;background:#fff;border-radius:12px;">
            <h1>Entrada no trobada</h1>
            <p>La entrada que cerques no existeix o no està publicada.</p>
            <p><a href="blog.php">Tornar al blog</a></p>
        </main>
        <?php include '_includes/footer.php'; ?>
    </body>
    </html>
    <?php
    exit;
}

// Si hem arribat aquí tenim $row
$entrada = $row;
// Actualitzar visualitzacions (no bloquejant la resposta)
if (!empty($entrada['id_entrada'])) {
    try {
        $entradaModel->incrementarVisualitzacions((int)$entrada['id_entrada']);
    } catch (Exception $e) {
        // ignore
    }
}

// Render
?><!doctype html>
<html lang="ca">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title><?php echo htmlspecialchars($entrada['meta_title_ca'] ?? $entrada['titol_ca'] ?? 'Entrada'); ?> | Yanina Parisi</title>
    <meta name="description" content="<?php echo htmlspecialchars($entrada['meta_description_ca'] ?? strip_tags($entrada['resum_ca'] ?? '')); ?>">
    <link rel="stylesheet" href="../css/estils.css">
</head>
<body>
    <?php include '_includes/navigation.php'; ?>
    <main class="container" style="max-width:900px;margin:60px auto;padding:32px;background:#fff;border-radius:12px;">
        <article>
            <h1><?php echo htmlspecialchars($entrada['titol_ca'] ?? $entrada['titol_es']); ?></h1>
            <div style="color:#888;margin-bottom:14px;"><i class="fas fa-calendar-alt"></i> <?php echo date('d/m/Y', strtotime($entrada['data_publicacio'])); ?></div>
            <?php if (!empty($entrada['imatge_portada'])): ?>
                <img src="<?php echo htmlspecialchars($entrada['imatge_portada']); ?>" alt="<?php echo htmlspecialchars($entrada['alt_imatge_ca'] ?? ''); ?>" style="width:100%;border-radius:8px;margin-bottom:16px;object-fit:cover;">
            <?php endif; ?>
            <div class="entrada-contenido">
                <?php echo $entrada['contingut_ca'] ?? $entrada['contingut_es']; ?>
            </div>
        </article>
        <p style="margin-top:28px;"><a href="blog.php">&larr; Tornar al blog</a></p>
    </main>
    <?php include '_includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@7.0.0/js/all.min.js"></script>
</body>
</html>
