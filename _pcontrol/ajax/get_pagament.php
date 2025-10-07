<?php
/**
 * AJAX - Obtenir dades d'un pagament
 * Retorna les dades d'un pagament específic per editar
 */

session_start();

// Verificar autenticació
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

require_once '../../classes/connexio.php';
require_once '../../classes/pagaments.php';

header('Content-Type: application/json');

try {
    $id_pagament = $_GET['id'] ?? 0;
    
    if ($id_pagament <= 0) {
        echo json_encode(['success' => false, 'message' => 'ID no válido']);
        exit;
    }
    
    $connexio = Connexio::getInstance();
    $pdo = $connexio->getConnexio();
    $pagament = new Pagament($pdo);
    
    $dades = $pagament->llegirUn($id_pagament);
    
    if ($dades) {
        echo json_encode([
            'success' => true,
            'pagament' => $dades
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Pago no encontrado'
        ]);
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>
