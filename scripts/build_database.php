<?php
/**
 * Script per construir la base de dades des de l'arxiu bdd.sql
 * Aquest script llegeix l'arxiu SQL i executa totes les consultes
 */

// Configuració
$sql_file = __DIR__ . '/../sql/bdd.sql';
$config_file = __DIR__ . '/../_data/connection.inc';

// Colors per la sortida del terminal
class Colors {
    const GREEN = "\033[32m";
    const RED = "\033[31m";
    const YELLOW = "\033[33m";
    const BLUE = "\033[34m";
    const RESET = "\033[0m";
}

echo Colors::BLUE . "==========================================\n" . Colors::RESET;
echo Colors::BLUE . "  CONSTRUCCIÓ DE LA BASE DE DADES\n" . Colors::RESET;
echo Colors::BLUE . "==========================================\n\n" . Colors::RESET;

// Comprovar que existeix l'arxiu SQL
if (!file_exists($sql_file)) {
    die(Colors::RED . "Error: No s'ha trobat l'arxiu SQL: {$sql_file}\n" . Colors::RESET);
}

// Comprovar que existeix l'arxiu de configuració
if (!file_exists($config_file)) {
    die(Colors::RED . "Error: No s'ha trobat l'arxiu de configuració: {$config_file}\n" . Colors::RESET);
}

// Carregar configuració de la base de dades
include $config_file;

if (!isset($db_config)) {
    die(Colors::RED . "Error: No s'ha trobat la configuració de la base de dades\n" . Colors::RESET);
}

echo Colors::YELLOW . "Configuració carregada:\n" . Colors::RESET;
echo "  - Host: " . $db_config['h'] . "\n";
echo "  - Usuari: " . $db_config['u'] . "\n";
echo "  - Base de dades: " . $db_config['d'] . "\n\n";

// Connectar a la base de dades
try {
    $mysqli = new mysqli(
        $db_config['h'],
        $db_config['u'],
        $db_config['p'],
        $db_config['d'],
        $db_config['t']
    );

    if ($mysqli->connect_error) {
        throw new Exception("Error de connexió: " . $mysqli->connect_error);
    }

    // Configurar charset UTF-8
    $mysqli->set_charset("utf8mb4");

    echo Colors::GREEN . "✓ Connexió establerta correctament\n\n" . Colors::RESET;

} catch (Exception $e) {
    die(Colors::RED . "Error: " . $e->getMessage() . "\n" . Colors::RESET);
}

// Llegir l'arxiu SQL
echo Colors::YELLOW . "Llegint l'arxiu SQL...\n" . Colors::RESET;
$sql_content = file_get_contents($sql_file);

if ($sql_content === false) {
    die(Colors::RED . "Error: No s'ha pogut llegir l'arxiu SQL\n" . Colors::RESET);
}

$file_size = filesize($sql_file);
echo Colors::GREEN . "✓ Arxiu llegit correctament (" . number_format($file_size / 1024, 2) . " KB)\n\n" . Colors::RESET;

// Dividir les consultes
echo Colors::YELLOW . "Processant consultes SQL...\n\n" . Colors::RESET;

// Eliminar comentaris i línies buides
$sql_content = preg_replace('/^--.*$/m', '', $sql_content);
$sql_content = preg_replace('/^\s*$/m', '', $sql_content);

// Dividir per ';' però respectant els delimiters
$queries = [];
$temp_query = '';
$delimiter = ';';
$in_delimiter_change = false;

$lines = explode("\n", $sql_content);

foreach ($lines as $line) {
    $line = trim($line);
    
    // Saltar línies buides i comentaris
    if (empty($line) || substr($line, 0, 2) === '--') {
        continue;
    }
    
    // Detectar canvis de delimiter
    if (stripos($line, 'DELIMITER') === 0) {
        $parts = explode(' ', $line);
        if (isset($parts[1])) {
            $delimiter = trim($parts[1]);
        }
        continue;
    }
    
    $temp_query .= $line . "\n";
    
    // Comprovar si la línia acaba amb el delimiter
    if (substr(rtrim($line), -strlen($delimiter)) === $delimiter) {
        // Eliminar el delimiter final
        $query = substr($temp_query, 0, -strlen($delimiter));
        $query = trim($query);
        
        if (!empty($query)) {
            $queries[] = $query;
        }
        
        $temp_query = '';
    }
}

// Afegir l'última consulta si n'hi ha
if (!empty(trim($temp_query))) {
    $queries[] = trim($temp_query);
}

$total_queries = count($queries);
echo Colors::BLUE . "Total de consultes a executar: {$total_queries}\n\n" . Colors::RESET;

// Executar les consultes
$executed = 0;
$errors = 0;
$start_time = microtime(true);

// Deshabilitar temporalment les comprovacions de claus foranes
$mysqli->query("SET FOREIGN_KEY_CHECKS = 0");

foreach ($queries as $index => $query) {
    $query_num = $index + 1;
    
    // Mostrar progrés cada 10 consultes
    if ($query_num % 10 === 0 || $query_num === 1) {
        echo Colors::YELLOW . "Executant consulta {$query_num}/{$total_queries}...\n" . Colors::RESET;
    }
    
    // Executar la consulta
    $result = $mysqli->multi_query($query);
    
    if ($result === false) {
        $errors++;
        $preview = substr($query, 0, 100);
        echo Colors::RED . "  ✗ Error en la consulta {$query_num}:\n" . Colors::RESET;
        echo Colors::RED . "    " . $mysqli->error . "\n" . Colors::RESET;
        echo Colors::RED . "    Consulta: " . $preview . "...\n\n" . Colors::RESET;
    } else {
        $executed++;
        
        // Netejar resultats pendents
        while ($mysqli->more_results()) {
            $mysqli->next_result();
            if ($res = $mysqli->store_result()) {
                $res->free();
            }
        }
    }
}

// Rehabilitar les comprovacions de claus foranes
$mysqli->query("SET FOREIGN_KEY_CHECKS = 1");

$end_time = microtime(true);
$execution_time = round($end_time - $start_time, 2);

// Resum final
echo "\n" . Colors::BLUE . "==========================================\n" . Colors::RESET;
echo Colors::BLUE . "  RESUM DE L'EXECUCIÓ\n" . Colors::RESET;
echo Colors::BLUE . "==========================================\n\n" . Colors::RESET;

echo "Total consultes: {$total_queries}\n";
echo Colors::GREEN . "Executades correctament: {$executed}\n" . Colors::RESET;

if ($errors > 0) {
    echo Colors::RED . "Errors: {$errors}\n" . Colors::RESET;
} else {
    echo Colors::GREEN . "Errors: 0\n" . Colors::RESET;
}

echo "Temps d'execució: {$execution_time} segons\n\n";

if ($errors === 0) {
    echo Colors::GREEN . "✓ Base de dades construïda correctament!\n" . Colors::RESET;
} else {
    echo Colors::YELLOW . "⚠ La base de dades s'ha construït amb alguns errors.\n" . Colors::RESET;
}

// Tancar connexió
$mysqli->close();

echo "\n" . Colors::BLUE . "==========================================\n\n" . Colors::RESET;
?>
