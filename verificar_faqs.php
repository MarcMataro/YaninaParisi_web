<?php
require_once __DIR__ . '/classes/connexio.php';

$pdo = Connexio::getInstance()->getConnexio();
$stmt = $pdo->query('DESCRIBE faqs');

echo "Estructura de la taula 'faqs':\n\n";
echo str_pad('Field', 25) . str_pad('Type', 20) . str_pad('Null', 6) . str_pad('Key', 5) . "\n";
echo str_repeat('-', 60) . "\n";

while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo str_pad($row['Field'], 25) . 
         str_pad($row['Type'], 20) . 
         str_pad($row['Null'], 6) . 
         str_pad($row['Key'], 5) . "\n";
}

// Verificar foreign keys
echo "\n\nForeign Keys de la taula 'faqs':\n\n";
$stmt = $pdo->query("SELECT 
    CONSTRAINT_NAME, 
    COLUMN_NAME, 
    REFERENCED_TABLE_NAME, 
    REFERENCED_COLUMN_NAME 
FROM information_schema.KEY_COLUMN_USAGE 
WHERE TABLE_SCHEMA = 'db_cp_yanina_parisi' 
AND TABLE_NAME = 'faqs' 
AND REFERENCED_TABLE_NAME IS NOT NULL");

while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "- {$row['CONSTRAINT_NAME']}: {$row['COLUMN_NAME']} -> {$row['REFERENCED_TABLE_NAME']}.{$row['REFERENCED_COLUMN_NAME']}\n";
}

echo "\n✅ Verificació completada!\n";
?>
