<?php
/**
 * API per obtenir dades d'un pacient
 * 
 * Endpoint AJAX per carregar dades de pacients sense recarregar la pàgina
 */

session_start();

// Verificar autenticació
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'No autoritzat']);
    exit;
}

// Headers per JSON
header('Content-Type: application/json');

// Incloure classes
require_once '../../classes/connexio.php';
require_once '../../classes/pacients.php';

try {
    // Connexió
    $connexio = Connexio::getInstance();
    $pdo = $connexio->getConnexio();
    
    // Obtenir ID del pacient
    $id = $_GET['id'] ?? null;
    
    if (!$id) {
        throw new Exception('ID de pacient no proporcionat');
    }
    
    // Crear objecte pacient i carregar dades
    $pacient = new Pacient($pdo);
    $pacient->id_pacient = $id;
    
    if ($pacient->llegirUn()) {
        // Retornar dades en format JSON
        echo json_encode([
            'success' => true,
            'pacient' => $pacient->toArray()
        ]);
    } else {
        throw new Exception('Pacient no trobat');
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
