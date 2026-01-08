<?php
require_once __DIR__ . '/classes/connexio.php';
require_once __DIR__ . '/classes/especialitats.php';

echo "<h1>Test Especialitats</h1>";

try {
    $conn = Connexio::getInstance();
    echo "<p>✓ Connexió establerta</p>";
    
    // Test directe amb PDO
    $stmt = $conn->getPDO()->query("SELECT COUNT(*) as total FROM especialitats");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "<p><strong>Total especialitats (consulta directa):</strong> " . $result['total'] . "</p>";
    
    // Test amb la classe Especialitats
    $espModel = Especialitats::getInstance();
    $totes = $espModel->llistarTotes();
    echo "<p><strong>Total especialitats (via classe):</strong> " . count($totes) . "</p>";
    
    if (count($totes) > 0) {
        echo "<h3>Llista d'especialitats:</h3><ul>";
        foreach ($totes as $esp) {
            echo "<li>ID: {$esp['id']} - {$esp['nom']}</li>";
        }
        echo "</ul>";
    } else {
        echo "<p style='color:red;'>⚠️ No s'han trobat especialitats!</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color:red;'><strong>ERROR:</strong> " . $e->getMessage() . "</p>";
}
?>
