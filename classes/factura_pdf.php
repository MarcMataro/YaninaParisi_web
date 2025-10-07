<?php
/**
 * Classe per generar factures en PDF
 * Utilitza FPDF per crear documents PDF amb format professional
 * 
 * @author Marc Mataró
 * @version 1.0.0
 */

require_once(__DIR__ . '/../vendor/fpdf.php');

class FacturaPDF extends FPDF {
    
    private $empresa = [
        'nom' => 'Yanina Parisi',
        'cif' => 'B12345678',
        'adreca' => 'Carrer Example, 123',
        'ciutat' => '08001 Barcelona',
        'telefon' => '+34 123 456 789',
        'email' => 'info@yaninaparisi.com',
        'web' => 'www.yaninaparisi.com'
    ];
    
    /**
     * Capçalera del PDF
     */
    function Header() {
        // Logo (si existeix)
        if (file_exists(__DIR__ . '/../img/Logo.png')) {
            $this->Image(__DIR__ . '/../img/Logo.png', 10, 6, 30);
        }
        
        // Informació de l'empresa a la dreta
        $this->SetFont('Arial', 'B', 14);
        $this->Cell(80);
        $this->Cell(100, 7, mb_convert_encoding($this->empresa['nom'], 'ISO-8859-1', 'UTF-8'), 0, 1, 'R');
        
        $this->SetFont('Arial', '', 9);
        $this->Cell(80);
        $this->Cell(100, 5, 'CIF: ' . $this->empresa['cif'], 0, 1, 'R');
        $this->Cell(80);
        $this->Cell(100, 5, mb_convert_encoding($this->empresa['adreca'], 'ISO-8859-1', 'UTF-8'), 0, 1, 'R');
        $this->Cell(80);
        $this->Cell(100, 5, mb_convert_encoding($this->empresa['ciutat'], 'ISO-8859-1', 'UTF-8'), 0, 1, 'R');
        $this->Cell(80);
        $this->Cell(100, 5, 'Tel: ' . $this->empresa['telefon'], 0, 1, 'R');
        
        $this->Ln(10);
    }
    
    /**
     * Peu de pàgina
     */
    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, mb_convert_encoding('Página ', 'ISO-8859-1', 'UTF-8') . $this->PageNo() . '/{nb}', 0, 0, 'C');
    }
    
    /**
     * Generar factura completa
     */
    public function generarFactura($dades_factura) {
        $this->AliasNbPages();
        $this->AddPage();
        
        // Títol FACTURA
        $this->SetFont('Arial', 'B', 20);
        $this->SetTextColor(204, 204, 167); // Color corporatiu
        $this->Cell(0, 10, 'FACTURA', 0, 1, 'C');
        $this->Ln(5);
        
        // Número de factura i data
        $this->SetFont('Arial', 'B', 12);
        $this->SetTextColor(0, 0, 0);
        $this->Cell(0, 7, mb_convert_encoding('Número: ', 'ISO-8859-1', 'UTF-8') . $dades_factura['numero_factura'], 0, 1);
        $this->Cell(0, 7, 'Fecha: ' . date('d/m/Y', strtotime($dades_factura['data_factura'])), 0, 1);
        $this->Ln(5);
        
        // Dades del client
        $this->SetFont('Arial', 'B', 11);
        $this->Cell(0, 7, mb_convert_encoding('DATOS DEL CLIENTE', 'ISO-8859-1', 'UTF-8'), 0, 1);
        $this->SetFont('Arial', '', 10);
        $this->Cell(0, 6, mb_convert_encoding($dades_factura['client']['nom'], 'ISO-8859-1', 'UTF-8'), 0, 1);
        if (!empty($dades_factura['client']['dni'])) {
            $this->Cell(0, 6, 'DNI/NIF: ' . $dades_factura['client']['dni'], 0, 1);
        }
        if (!empty($dades_factura['client']['adreca'])) {
            $this->Cell(0, 6, mb_convert_encoding($dades_factura['client']['adreca'], 'ISO-8859-1', 'UTF-8'), 0, 1);
        }
        if (!empty($dades_factura['client']['telefon'])) {
            $this->Cell(0, 6, mb_convert_encoding('Teléfono: ', 'ISO-8859-1', 'UTF-8') . $dades_factura['client']['telefon'], 0, 1);
        }
        $this->Ln(10);
        
        // Taula de conceptes
        $this->SetFont('Arial', 'B', 10);
        $this->SetFillColor(204, 204, 167);
        $this->Cell(90, 8, 'Concepto', 1, 0, 'L', true);
        $this->Cell(30, 8, 'Fecha', 1, 0, 'C', true);
        $this->Cell(40, 8, mb_convert_encoding('Método de pago', 'ISO-8859-1', 'UTF-8'), 1, 0, 'C', true);
        $this->Cell(30, 8, 'Importe', 1, 1, 'R', true);
        
        // Línies de conceptes
        $this->SetFont('Arial', '', 10);
        $total = 0;
        foreach ($dades_factura['conceptes'] as $concepte) {
            $this->Cell(90, 7, mb_convert_encoding($concepte['descripcio'], 'ISO-8859-1', 'UTF-8'), 1);
            $this->Cell(30, 7, date('d/m/Y', strtotime($concepte['data'])), 1, 0, 'C');
            $this->Cell(40, 7, mb_convert_encoding($concepte['metode_pagament'], 'ISO-8859-1', 'UTF-8'), 1, 0, 'C');
            $this->Cell(30, 7, number_format($concepte['import'], 2) . ' EUR', 1, 1, 'R');
            $total += $concepte['import'];
        }
        
        // Total
        $this->SetFont('Arial', 'B', 12);
        $this->Cell(160, 10, 'TOTAL', 1, 0, 'R', true);
        $this->Cell(30, 10, number_format($total, 2) . ' EUR', 1, 1, 'R', true);
        
        $this->Ln(10);
        
        // Peu informatiu
        $this->SetFont('Arial', 'I', 9);
        $this->MultiCell(0, 5, mb_convert_encoding('Gracias por confiar en nuestros servicios. Para cualquier consulta, no dude en contactarnos.', 'ISO-8859-1', 'UTF-8'), 0, 'C');
    }
}
