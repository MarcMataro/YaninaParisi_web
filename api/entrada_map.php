<?php
// Small JSON endpoint to map entry slugs between languages
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../classes/connexio.php';
require_once __DIR__ . '/../classes/entrades.php';

$conn = Connexio::getInstance();
$pdo = $conn->getConnexio();
$entradaModel = new Entrada($pdo);

$slug = isset($_GET['slug']) ? trim((string)$_GET['slug']) : '';
$lang = isset($_GET['lang']) ? trim((string)$_GET['lang']) : '';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$out = ['success' => false];

if ($slug !== '' && in_array($lang, ['ca','es'])) {
    // Troba l'entrada actual (pot ser en qualsevol idioma)
    $row = $entradaModel->buscarPerSlug($slug, $lang, true);
    $target_id = null;
    $target_slug = null;
    if ($row) {
        $target_lang = ($lang === 'ca') ? 'es' : 'ca';
        $target_slug = $row['slug_' . $target_lang] ?? null;
        if ($target_slug) {
            $target_row = $entradaModel->buscarPerSlug($target_slug, $target_lang, true);
            if ($target_row) {
                $target_id = $target_row['id_entrada'];
            }
        }
        // Si no hi ha slug alternatiu o no troba l'entrada germana, busca per id_entrada igual
        if (!$target_id && isset($row['id_entrada'])) {
            $sql = "SELECT * FROM blog_entrades WHERE id_entrada = :id AND estat = 'publicat' AND visible = 1";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':id', $row['id_entrada'], PDO::PARAM_INT);
            $stmt->execute();
            $target_row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($target_row) {
                $target_id = $target_row['id_entrada'];
                $target_slug = $target_row['slug_' . $target_lang] ?? null;
            }
        }
        $out = [
            'success' => true,
            'id_entrada' => $target_id ?? $row['id_entrada'],
            'slug_ca' => $row['slug_ca'] ?? null,
            'slug_es' => $row['slug_es'] ?? null,
            'titol_ca' => $row['titol_ca'] ?? null,
            'titol_es' => $row['titol_es'] ?? null,
        ];
        // Si existeix slug alternatiu, retorna'l per prioritat
        if ($target_slug) {
            $out['slug_' . $target_lang] = $target_slug;
        }
    }
} elseif ($id > 0 && in_array($lang, ['ca','es'])) {
    // Busca per id_entrada i retorna el slug correcte de l'idioma destí
    $sql = "SELECT * FROM blog_entrades WHERE id_entrada = :id AND estat = 'publicat' AND visible = 1";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row) {
        $slug_dest = $row['slug_' . $lang] ?? null;
        $out = [
            'success' => true,
            'id_entrada' => $row['id_entrada'] ?? null,
            'slug_ca' => $row['slug_ca'] ?? null,
            'slug_es' => $row['slug_es'] ?? null,
            'slug_dest' => $slug_dest,
            'titol_ca' => $row['titol_ca'] ?? null,
            'titol_es' => $row['titol_es'] ?? null,
        ];
    }
}

echo json_encode($out, JSON_UNESCAPED_UNICODE);
exit;
