<?php
/**
 * Generar Factura PDF
 * 
 * Endpoint per generar i descarregar factures en PDF
 * 
 * @author Marc Mataró
 * @version 1.0.0
 */

session_start();

// Verificar autenticació
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: index.php');
    exit;
}

// Incluir classes necessàries
require_once '../classes/connexio.php';
require_once '../classes/pagaments.php';
require_once '../classes/sessions.php';
require_once '../classes/pacients.php';
require_once '../classes/factura_pdf.php';

// Obtenir ID del pagament
$id_pagament = $_GET['id'] ?? 0;

if ($id_pagament == 0) {
    die('Error: No s\'ha especificat cap pagament');
}

// Obtenir connexió
try {
    $connexio = Connexio::getInstance();
    $pdo = $connexio->getConnexio();
} catch (Exception $e) {
    die('Error de connexió: ' . $e->getMessage());
}

// Obtenir dades del pagament
$pagament = new Pagament($pdo);
$dades_pagament = $pagament->llegirUn($id_pagament);

if (!$dades_pagament) {
    die('Error: Pagament no trobat');
}

// Obtenir dades de la sessió
$session = new Session($pdo);
$dades_sessio = $session->llegirUna($dades_pagament['id_sessio']);

if (!$dades_sessio) {
    die('Error: Sessió no trobada');
}

// Obtenir dades del pacient
$pacient = new Pacient($pdo);
$dades_pacient = $pacient->llegirUn($dades_sessio['id_pacient']);

if (!$dades_pacient) {
    die('Error: Pacient no trobat');
}

// Preparar dades per la factura
$dades_factura = [
    'numero_factura' => $dades_pagament['numero_factura'] ?? 'F-' . str_pad($id_pagament, 6, '0', STR_PAD_LEFT),
    'data_factura' => $dades_pagament['data_pagament'],
    'client' => [
        'nom' => $dades_pacient['nom'] . ' ' . $dades_pacient['cognoms'],
        'dni' => $dades_pacient['dni'] ?? '',
        'adreca' => $dades_pacient['adreca'] ?? '',
        'telefon' => $dades_pacient['telefon'] ?? ''
    ],
    'conceptes' => [
        [
            'descripcio' => 'Sesión de terapia - ' . $dades_sessio['tipus_sessio'],
            'data' => $dades_sessio['data_sessio'],
            'metode_pagament' => $dades_pagament['metode_pagament'],
            'import' => $dades_pagament['import']
        ]
    ]
];

// Si hi ha observacions, afegir-les
if (!empty($dades_pagament['observacions'])) {
    $dades_factura['conceptes'][0]['descripcio'] .= ' - ' . $dades_pagament['observacions'];
}

// Generar PDF
$pdf = new FacturaPDF();
$pdf->generarFactura($dades_factura);

// Nom del fitxer
$nom_fitxer = 'Factura_' . $dades_factura['numero_factura'] . '.pdf';

// Descarregar PDF
$pdf->Output('D', $nom_fitxer);
