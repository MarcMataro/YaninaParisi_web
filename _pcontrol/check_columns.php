<?php
require_once '../classes/connexio.php';

$conn = Connexio::getInstance()->getConnexio();
$stmt = $conn->query('DESCRIBE blog_entrades');

echo "<h2>Columnes de blog_entrades:</h2>";
echo "<pre>";
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo $row['Field'] . "\n";
}
echo "</pre>";

// També obtenim una entrada d'exemple
$stmt2 = $conn->query('SELECT * FROM blog_entrades LIMIT 1');
$entrada = $stmt2->fetch(PDO::FETCH_ASSOC);

echo "<h2>Claus de l'entrada:</h2>";
echo "<pre>";
if ($entrada) {
    foreach (array_keys($entrada) as $key) {
        echo "$key => " . (isset($entrada[$key]) ? substr($entrada[$key], 0, 50) : 'NULL') . "\n";
    }
} else {
    echo "No hi ha entrades a la base de dades";
}
echo "</pre>";
