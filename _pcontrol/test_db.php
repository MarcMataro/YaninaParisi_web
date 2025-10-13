<?php
session_start();

// Simular sessió per al test
$_SESSION['logged_in'] = true;

require_once '../classes/connexio.php';
require_once '../classes/entrades.php';

header('Content-Type: text/plain; charset=utf-8');

echo "=== TEST BASE DE DADES ===\n\n";

try {
    $connexio = Connexio::getInstance();
    $pdo = $connexio->getConnexio();
    echo "✓ Connexió PDO establerta\n\n";
    
    // Comprovar taula blog_entrades
    $stmt = $pdo->query("SHOW TABLES LIKE 'blog_entrades'");
    if ($stmt->rowCount() > 0) {
        echo "✓ Taula 'blog_entrades' existeix\n\n";
        
        // Comptar entrades
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM blog_entrades");
        $count = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "Total entrades a la BD: " . $count['total'] . "\n\n";
        
        // Provar el model
        $entradaModel = new Entrada($pdo);
        echo "✓ Model Entrada creat\n\n";
        
        echo "Provant llegirTots()...\n";
        $stmt = $entradaModel->llegirTots([], 10, 0, 'data_creacio', 'DESC');
        $entrades = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "✓ llegirTots() executat correctament\n";
        echo "Entrades retornades: " . count($entrades) . "\n\n";
        
        if (count($entrades) > 0) {
            echo "Primera entrada:\n";
            print_r($entrades[0]);
        }
        
    } else {
        echo "✗ Taula 'blog_entrades' NO existeix!\n";
        echo "\nTaules disponibles:\n";
        $stmt = $pdo->query("SHOW TABLES");
        while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            echo "  - " . $row[0] . "\n";
        }
    }
    
} catch (Exception $e) {
    echo "✗ ERROR: " . $e->getMessage() . "\n";
    echo "\nStack trace:\n" . $e->getTraceAsString();
}

echo "\n\n=== FI DEL TEST ===\n";
?>
