<?php
/**
 * _pcontrol/generar_token_ressenya.php
 *
 * Script d'administració per generar tokens d'un sol ús per permetre que
 * pacients enviïn ressenyes via l'enllaç /ca/opina.php?token=...
 *
 * Funcionament:
 *  - CLI: php generar_token_ressenya.php <pacient_id> [hours_valid]
 *  - Web: formulari que mostra una llista de pacients i genera token al submit
 *
 * IMPORTANT: aquest script NO està protegit per defecte. Protegiu-lo amb
 * autenticació HTTP, restriccions d'IP o movent-lo fora del webroot en producció.
 */

if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/../classes/connexio.php';
require_once __DIR__ . '/../classes/ressenya_tokens.php';

// Obtain DB
try {
    $db = Connexio::getInstance()->getConnexio();
} catch (Exception $e) {
    echo "Error: no s\'ha pogut connectar amb la base de dades.\n";
    exit(1);
}

$tokenModel = new RessenyaTokens($db);

// CLI mode
if (php_sapi_name() === 'cli') {
    global $argv;
    if (!isset($argv[1])) {
        echo "Ús: php generar_token_ressenya.php <pacient_id> [hours_valid]\n";
        exit(1);
    }
    $pacient_id = (int)$argv[1];
    $hours = isset($argv[2]) ? (int)$argv[2] : 72;
    $token = $tokenModel->createToken($pacient_id, $hours);
    if ($token === false) {
        echo "No s'ha pogut generar el token.\n";
        exit(1);
    }
    echo "Token generat: $token\n";
    echo "URL d'opina: /ca/opina.php?token=$token\n";
    exit(0);
}

// Web mode
// WARNING: verify access control before using on producció
$message = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pacient_id = isset($_POST['pacient_id']) ? (int)$_POST['pacient_id'] : 0;
    $hours = isset($_POST['hours']) ? (int)$_POST['hours'] : 72;
    if ($pacient_id <= 0) {
        $message = ['type' => 'error', 'text' => 'Pacient invàlid'];
    } else {
        $token = $tokenModel->createToken($pacient_id, $hours);
        if ($token === false) {
            $message = ['type' => 'error', 'text' => 'No s\'ha pogut crear el token'];
        } else {
            $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
            $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
            $url = $protocol . '://' . $host . '/ca/opina.php?token=' . $token;
            $message = ['type' => 'success', 'text' => 'Token generat amb èxit', 'token' => $token, 'url' => $url];
        }
    }
}

// Obtenir llista reduïda de pacients per al formulari
$pacients = [];
try {
    // La taula `pacients` utilitza la clau primària `id_pacient` segons l'esquema.
    // Seleccionem amb alias `id` per compatibilitat amb el formulari.
    $stmt = $db->prepare('SELECT id_pacient AS id, nom, cognoms, email FROM pacients ORDER BY id_pacient DESC LIMIT 200');
    $stmt->execute();
    $pacients = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    // si la taula no existeix o hi ha un error, el formulari seguirà mostrant-se buit
    error_log('Error obtenint pacients: ' . $e->getMessage());
}

?><!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Generar token ressenya</title>
    <link rel="stylesheet" href="../css/estils.css">
    <style>body{font-family:Arial,Helvetica,sans-serif;padding:20px} .box{max-width:800px;margin:0 auto} .alert{padding:10px;margin-bottom:10px;border-radius:4px} .alert-success{background:#e6ffed;border:1px solid #b6f0c9} .alert-error{background:#ffecec;border:1px solid #f1b6b6}</style>
</head>
<body>
    <div class="box">
        <h1>Generar token per enviar ressenya</h1>
        <p style="color:#a00">ATENCIÓ: aquest script ha d'estar protegit. No deixis aquest fitxer accessible públicament sense autènticació.</p>

        <?php if ($message): ?>
            <?php if ($message['type'] === 'success'): ?>
                <div class="alert alert-success">
                    <strong><?php echo htmlspecialchars($message['text']); ?></strong><br>
                    Token: <code><?php echo htmlspecialchars($message['token']); ?></code><br>
                    URL: <a href="<?php echo htmlspecialchars($message['url']); ?>"><?php echo htmlspecialchars($message['url']); ?></a>
                </div>
            <?php else: ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($message['text']); ?></div>
            <?php endif; ?>
        <?php endif; ?>

        <form method="POST" action="">
            <div style="margin-bottom:10px">
                <label for="pacient_id">Selecciona pacient</label><br>
                <select id="pacient_id" name="pacient_id" style="width:100%;padding:8px">
                    <option value="">-- triar pacient --</option>
                    <?php foreach ($pacients as $p): ?>
                        <?php $label = sprintf('#%d — %s %s (%s)', $p['id'], $p['nom'] ?? '', $p['cognoms'] ?? '', $p['email'] ?? ''); ?>
                        <option value="<?php echo (int)$p['id']; ?>"><?php echo htmlspecialchars($label); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div style="margin-bottom:10px">
                <label for="hours">Validesa (hores)</label><br>
                <input type="number" id="hours" name="hours" value="72" min="1" style="width:120px;padding:6px">
            </div>

            <button type="submit" class="btn btn-primary">Generar token</button>
        </form>
    </div>
</body>
</html>
