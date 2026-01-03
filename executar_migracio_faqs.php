<?php
/**
 * Script per afegir el camp id_usuario a la taula faqs
 * Executar aquest fitxer una sola vegada des del navegador o CLI
 */

require_once __DIR__ . '/classes/connexio.php';

try {
    $pdo = Connexio::getInstance()->getConnexio();
    
    echo "<h2>Afegint camp id_usuario a la taula faqs...</h2>";
    
    // 1. Afegir columna i índex
    echo "<p>1. Afegint columna id_usuario i índex...</p>";
    $sql1 = "ALTER TABLE `faqs` 
             ADD COLUMN `id_usuario` INT NULL AFTER `data_actualitzacio`,
             ADD INDEX `idx_faq_usuario` (`id_usuario`)";
    
    $pdo->exec($sql1);
    echo "<p style='color: green;'>✓ Columna i índex afegits correctament</p>";
    
    // 2. Afegir foreign key
    echo "<p>2. Afegint foreign key...</p>";
    $sql2 = "ALTER TABLE `faqs`
             ADD CONSTRAINT `fk_faq_usuario` 
             FOREIGN KEY (`id_usuario`) REFERENCES `usuarios_panel`(`id_usuario`) 
             ON DELETE SET NULL 
             ON UPDATE CASCADE";
    
    $pdo->exec($sql2);
    echo "<p style='color: green;'>✓ Foreign key afegida correctament</p>";
    
    echo "<h3 style='color: green;'>✅ Procés completat amb èxit!</h3>";
    echo "<p>Ja pots utilitzar la funcionalitat de FAQs per a Editors.</p>";
    echo "<p><a href='_pcontrol/gfaq.php'>Anar a gestió de FAQs</a></p>";
    
} catch (PDOException $e) {
    // Capturar errors específics
    if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
        echo "<p style='color: orange;'>⚠️ La columna 'id_usuario' ja existeix. No cal fer res.</p>";
    } elseif (strpos($e->getMessage(), 'Duplicate key name') !== false) {
        echo "<p style='color: orange;'>⚠️ L'índex o la foreign key ja existeix. No cal fer res.</p>";
    } else {
        echo "<p style='color: red;'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error de connexió: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>
