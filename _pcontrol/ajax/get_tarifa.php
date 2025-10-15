<?php
// AJAX: retorna les dades completes d'una tarifa per id (JSON)
require_once __DIR__ . '/../../classes/tarifes.php';
header('Content-Type: application/json');
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id > 0) {
    $tarifa = Tarifa::obtenirPerId($id);
    if ($tarifa) {
        echo json_encode($tarifa);
        exit;
    }
}
echo json_encode(['error' => 'No se ha encontrado la tarifa']);
exit;
