<?php
/**
 * Classe Pagament
 * 
 * Gestiona totes les operacions relacionades amb els pagaments del sistema.
 * Aquesta classe implementa el patró Active Record per interactuar amb la taula 'pagaments'.
 * 
 * Funcionalitats principals:
 * - Registrar i gestionar pagaments de sessions
 * - Control de mètodes de pagament (Efectivo, Tarjeta, Transferencia, Bizum)
 * - Gestió d'estats (Pendiente, Completado, Anulado)
 * - Vinculació amb facturació
 * - Generar informes financers i estadístiques
 * - Consultes per data, estat i mètode de pagament
 * 
 * @author Marc Mataró
 * @version 1.0.0
 * @date 2025-10-06
 */

class Pagament {
    
    // ============================================
    // PROPIETATS DE LA CLASSE
    // ============================================
    
    /**
     * @var PDO Connexió a la base de dades
     */
    private $conn;
    
    /**
     * @var string Nom de la taula
     */
    private $table = 'pagaments';
    
    /**
     * @var int ID únic del pagament
     */
    public $id_pagament;
    
    /**
     * @var int ID de la sessió associada al pagament
     */
    public $id_sessio;
    
    /**
     * @var string Data del pagament (format: YYYY-MM-DD)
     */
    public $data_pagament;
    
    /**
     * @var float Import del pagament
     */
    public $import;
    
    /**
     * @var string Mètode de pagament ('Efectivo', 'Tarjeta', 'Transferencia', 'Bizum')
     */
    public $metode_pagament;
    
    /**
     * @var string Estat del pagament ('Pendiente', 'Completado', 'Anulado')
     */
    public $estat;
    
    /**
     * @var bool Indica si el pagament està facturat
     */
    public $facturat;
    
    /**
     * @var string Número de factura associat (si està facturat)
     */
    public $numero_factura;
    
    /**
     * @var string Observacions o notes sobre el pagament
     */
    public $observacions;
    
    /**
     * @var string Data i hora de registre del pagament
     */
    public $data_registre;
    
    // ============================================
    // CONSTRUCTOR
    // ============================================
    
    /**
     * Constructor de la classe
     * 
     * Inicialitza la connexió a la base de dades.
     * 
     * @param PDO $db Connexió PDO a la base de dades
     */
    public function __construct($db) {
        $this->conn = $db;
    }
    
    // ============================================
    // MÈTODES CRUD BÀSICS
    // ============================================
    
    /**
     * Crear un nou pagament
     * 
     * Insereix un nou registre de pagament a la base de dades.
     * 
     * @return bool True si s'ha creat correctament, false en cas contrari
     */
    public function crear() {
        $query = "INSERT INTO " . $this->table . " 
                  SET id_sessio = :id_sessio,
                      data_pagament = :data_pagament,
                      import = :import,
                      metode_pagament = :metode_pagament,
                      estat = :estat,
                      facturat = :facturat,
                      numero_factura = :numero_factura,
                      observacions = :observacions";
        
        $stmt = $this->conn->prepare($query);
        
        // Netejar les dades
        $this->id_sessio = htmlspecialchars(strip_tags($this->id_sessio));
        $this->data_pagament = htmlspecialchars(strip_tags($this->data_pagament));
        $this->import = htmlspecialchars(strip_tags($this->import));
        $this->metode_pagament = htmlspecialchars(strip_tags($this->metode_pagament));
        $this->estat = htmlspecialchars(strip_tags($this->estat ?? 'Completado'));
        $this->facturat = $this->facturat ?? false;
        $this->numero_factura = htmlspecialchars(strip_tags($this->numero_factura ?? ''));
        $this->observacions = htmlspecialchars(strip_tags($this->observacions ?? ''));
        
        // Vincular paràmetres
        $stmt->bindParam(':id_sessio', $this->id_sessio);
        $stmt->bindParam(':data_pagament', $this->data_pagament);
        $stmt->bindParam(':import', $this->import);
        $stmt->bindParam(':metode_pagament', $this->metode_pagament);
        $stmt->bindParam(':estat', $this->estat);
        $stmt->bindParam(':facturat', $this->facturat, PDO::PARAM_BOOL);
        $stmt->bindParam(':numero_factura', $this->numero_factura);
        $stmt->bindParam(':observacions', $this->observacions);
        
        if($stmt->execute()) {
            $this->id_pagament = $this->conn->lastInsertId();
            return true;
        }
        
        return false;
    }
    
    /**
     * Llegir tots els pagaments amb filtres opcionals
     * 
     * Obté una llista de tots els pagaments amb possibilitat de filtrar
     * per estat, mètode de pagament, rang de dates, etc.
     * 
     * @param string|null $estat Estat del pagament (opcional)
     * @param string|null $metode Mètode de pagament (opcional)
     * @param string|null $data_inici Data d'inici del rang (opcional)
     * @param string|null $data_fi Data de fi del rang (opcional)
     * @param bool|null $facturat Si està facturat o no (opcional)
     * @return array Array de pagaments
     */
    public function llegirTots($estat = null, $metode = null, $data_inici = null, $data_fi = null, $facturat = null) {
        $query = "SELECT p.id_pagament,
                         p.id_sessio,
                         p.data_pagament,
                         p.import,
                         p.metode_pagament,
                         p.estat,
                         p.facturat,
                         p.numero_factura,
                         p.observacions,
                         p.data_registre,
                         s.data_sessio, 
                         s.hora_inici,
                         s.tipus_sessio,
                         pac.nom as nom_pacient, 
                         pac.cognoms as cognoms_pacient
                  FROM " . $this->table . " p
                  INNER JOIN sessions s ON p.id_sessio = s.id_sessio
                  INNER JOIN pacients pac ON s.id_pacient = pac.id_pacient
                  WHERE 1=1";
        
        // Afegir filtres
        if ($estat !== null) {
            $query .= " AND p.estat = :estat";
        }
        if ($metode !== null) {
            $query .= " AND p.metode_pagament = :metode";
        }
        if ($data_inici !== null) {
            $query .= " AND p.data_pagament >= :data_inici";
        }
        if ($data_fi !== null) {
            $query .= " AND p.data_pagament <= :data_fi";
        }
        if ($facturat !== null) {
            $query .= " AND p.facturat = :facturat";
        }
        
        $query .= " ORDER BY p.data_pagament DESC, p.data_registre DESC";
        
        $stmt = $this->conn->prepare($query);
        
        // Vincular paràmetres dels filtres
        if ($estat !== null) {
            $stmt->bindParam(':estat', $estat);
        }
        if ($metode !== null) {
            $stmt->bindParam(':metode', $metode);
        }
        if ($data_inici !== null) {
            $stmt->bindParam(':data_inici', $data_inici);
        }
        if ($data_fi !== null) {
            $stmt->bindParam(':data_fi', $data_fi);
        }
        if ($facturat !== null) {
            $stmt->bindParam(':facturat', $facturat, PDO::PARAM_BOOL);
        }
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Llegir un pagament específic
     * 
     * Obté la informació completa d'un pagament pel seu ID.
     * 
     * @param int $id ID del pagament
     * @return array|false Dades del pagament o false si no existeix
     */
    public function llegirUn($id) {
        $query = "SELECT p.id_pagament,
                         p.id_sessio,
                         p.data_pagament,
                         p.import,
                         p.metode_pagament,
                         p.estat,
                         p.facturat,
                         p.numero_factura,
                         p.observacions,
                         p.data_registre,
                         s.data_sessio, 
                         s.hora_inici,
                         s.tipus_sessio,
                         s.preu_sessio,
                         pac.nom as nom_pacient, 
                         pac.cognoms as cognoms_pacient,
                         pac.email as email_pacient,
                         pac.telefon as telefon_pacient
                  FROM " . $this->table . " p
                  INNER JOIN sessions s ON p.id_sessio = s.id_sessio
                  INNER JOIN pacients pac ON s.id_pacient = pac.id_pacient
                  WHERE p.id_pagament = :id
                  LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Actualitzar un pagament existent
     * 
     * Modifica les dades d'un pagament existent a la base de dades.
     * 
     * @return bool True si s'ha actualitzat correctament, false en cas contrari
     */
    public function actualitzar() {
        $query = "UPDATE " . $this->table . "
                  SET id_sessio = :id_sessio,
                      data_pagament = :data_pagament,
                      import = :import,
                      metode_pagament = :metode_pagament,
                      estat = :estat,
                      facturat = :facturat,
                      numero_factura = :numero_factura,
                      observacions = :observacions
                  WHERE id_pagament = :id_pagament";
        
        $stmt = $this->conn->prepare($query);
        
        // Netejar les dades
        $this->id_pagament = htmlspecialchars(strip_tags($this->id_pagament));
        $this->id_sessio = htmlspecialchars(strip_tags($this->id_sessio));
        $this->data_pagament = htmlspecialchars(strip_tags($this->data_pagament));
        $this->import = htmlspecialchars(strip_tags($this->import));
        $this->metode_pagament = htmlspecialchars(strip_tags($this->metode_pagament));
        $this->estat = htmlspecialchars(strip_tags($this->estat));
        $this->facturat = $this->facturat ?? false;
        $this->numero_factura = htmlspecialchars(strip_tags($this->numero_factura ?? ''));
        $this->observacions = htmlspecialchars(strip_tags($this->observacions ?? ''));
        
        // Vincular paràmetres
        $stmt->bindParam(':id_pagament', $this->id_pagament);
        $stmt->bindParam(':id_sessio', $this->id_sessio);
        $stmt->bindParam(':data_pagament', $this->data_pagament);
        $stmt->bindParam(':import', $this->import);
        $stmt->bindParam(':metode_pagament', $this->metode_pagament);
        $stmt->bindParam(':estat', $this->estat);
        $stmt->bindParam(':facturat', $this->facturat, PDO::PARAM_BOOL);
        $stmt->bindParam(':numero_factura', $this->numero_factura);
        $stmt->bindParam(':observacions', $this->observacions);
        
        return $stmt->execute();
    }
    
    /**
     * Eliminar un pagament
     * 
     * Esborra un pagament de la base de dades.
     * 
     * @param int $id ID del pagament a eliminar
     * @return bool True si s'ha eliminat correctament, false en cas contrari
     */
    public function eliminar($id) {
        $query = "DELETE FROM " . $this->table . " WHERE id_pagament = :id";
        
        $stmt = $this->conn->prepare($query);
        $id = htmlspecialchars(strip_tags($id));
        $stmt->bindParam(':id', $id);
        
        return $stmt->execute();
    }
    
    // ============================================
    // MÈTODES DE NEGOCI
    // ============================================
    
    /**
     * Registrar pagament per una sessió
     * 
     * Crea un nou pagament associat a una sessió específica.
     * 
     * @param int $id_sessio ID de la sessió
     * @param float $import Import del pagament
     * @param string $metode Mètode de pagament
     * @param string|null $data_pagament Data del pagament (opcional, per defecte avui)
     * @param string|null $observacions Observacions (opcional)
     * @return bool True si s'ha registrat correctament
     */
    public function registrarPagament($id_sessio, $import, $metode, $data_pagament = null, $observacions = null) {
        $this->id_sessio = $id_sessio;
        $this->import = $import;
        $this->metode_pagament = $metode;
        $this->data_pagament = $data_pagament ?? date('Y-m-d');
        $this->estat = 'Completado';
        $this->facturat = false;
        $this->observacions = $observacions;
        
        return $this->crear();
    }
    
    /**
     * Marcar pagament com anulat
     * 
     * Canvia l'estat d'un pagament a 'Anulat'.
     * 
     * @param int $id_pagament ID del pagament
     * @param string|null $motiu Motiu de l'anul·lació
     * @return bool True si s'ha anulat correctament
     */
    public function anularPagament($id_pagament, $motiu = null) {
        $pagament = $this->llegirUn($id_pagament);
        if (!$pagament) {
            return false;
        }
        
        $this->id_pagament = $id_pagament;
        $this->id_sessio = $pagament['id_sessio'];
        $this->data_pagament = $pagament['data_pagament'];
        $this->import = $pagament['import'];
        $this->metode_pagament = $pagament['metode_pagament'];
        $this->estat = 'Anulado';
        $this->facturat = $pagament['facturat'];
        $this->numero_factura = $pagament['numero_factura'];
        $this->observacions = $motiu ?? $pagament['observacions'];
        
        return $this->actualitzar();
    }
    
    /**
     * Marcar pagament com facturat
     * 
     * Actualitza el pagament indicant que ja s'ha facturat.
     * 
     * @param int $id_pagament ID del pagament
     * @param string $numero_factura Número de factura
     * @return bool True si s'ha marcat correctament
     */
    public function marcarComFacturat($id_pagament, $numero_factura) {
        $pagament = $this->llegirUn($id_pagament);
        if (!$pagament) {
            return false;
        }
        
        $this->id_pagament = $id_pagament;
        $this->id_sessio = $pagament['id_sessio'];
        $this->data_pagament = $pagament['data_pagament'];
        $this->import = $pagament['import'];
        $this->metode_pagament = $pagament['metode_pagament'];
        $this->estat = $pagament['estat'];
        $this->facturat = true;
        $this->numero_factura = $numero_factura;
        $this->observacions = $pagament['observacions'];
        
        return $this->actualitzar();
    }
    
    /**
     * Desmarcar pagament com facturat
     * 
     * Actualitza el pagament eliminant la marca de facturat.
     * 
     * @param int $id_pagament ID del pagament
     * @return bool True si s'ha desmarcat correctament
     */
    public function desmarcarFacturat($id_pagament) {
        $pagament = $this->llegirUn($id_pagament);
        if (!$pagament) {
            return false;
        }
        
        $this->id_pagament = $id_pagament;
        $this->id_sessio = $pagament['id_sessio'];
        $this->data_pagament = $pagament['data_pagament'];
        $this->import = $pagament['import'];
        $this->metode_pagament = $pagament['metode_pagament'];
        $this->estat = $pagament['estat'];
        $this->facturat = false;
        $this->numero_factura = null;
        $this->observacions = $pagament['observacions'];
        
        return $this->actualitzar();
    }
    
    // ============================================
    // MÈTODES DE CONSULTA
    // ============================================
    
    /**
     * Obtenir pagaments per sessió
     * 
     * Llista tots els pagaments associats a una sessió específica.
     * 
     * @param int $id_sessio ID de la sessió
     * @return array Array de pagaments
     */
    public function pagamentsPerSessio($id_sessio) {
        $query = "SELECT * FROM " . $this->table . "
                  WHERE id_sessio = :id_sessio
                  ORDER BY data_pagament DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_sessio', $id_sessio);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtenir pagaments per pacient
     * 
     * Llista tots els pagaments d'un pacient específic.
     * 
     * @param int $id_pacient ID del pacient
     * @return array Array de pagaments
     */
    public function pagamentsPerPacient($id_pacient) {
        $query = "SELECT p.id_pagament, p.id_sessio, p.data_pagament, p.import,
                         p.metode_pagament, p.estat, p.facturat, p.numero_factura,
                         p.observacions, p.data_registre,
                         s.data_sessio, s.tipus_sessio
                  FROM " . $this->table . " p
                  INNER JOIN sessions s ON p.id_sessio = s.id_sessio
                  WHERE s.id_pacient = :id_pacient
                  ORDER BY p.data_pagament DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_pacient', $id_pacient);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtenir pagaments pendents
     * 
     * Llista tots els pagaments amb estat 'Pendiente'.
     * 
     * @return array Array de pagaments pendents
     */
    public function pagamentsPendents() {
        return $this->llegirTots('Pendiente');
    }
    
    /**
     * Obtenir pagaments no facturats
     * 
     * Llista tots els pagaments completats que encara no s'han facturat.
     * 
     * @return array Array de pagaments no facturats
     */
    public function pagamentsNoFacturats() {
        $query = "SELECT p.id_pagament, p.id_sessio, p.data_pagament, p.import,
                         p.metode_pagament, p.estat, p.facturat, p.numero_factura,
                         p.observacions, p.data_registre,
                         s.data_sessio, 
                         s.tipus_sessio,
                         pac.nom as nom_pacient, 
                         pac.cognoms as cognoms_pacient
                  FROM " . $this->table . " p
                  INNER JOIN sessions s ON p.id_sessio = s.id_sessio
                  INNER JOIN pacients pac ON s.id_pacient = pac.id_pacient
                  WHERE p.facturat = 0 AND p.estat = 'Completado'
                  ORDER BY p.data_pagament ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtenir pagaments per factura
     * 
     * Llista tots els pagaments associats a un número de factura.
     * 
     * @param string $numero_factura Número de factura
     * @return array Array de pagaments
     */
    public function pagamentsPerFactura($numero_factura) {
        $query = "SELECT p.id_pagament, p.id_sessio, p.data_pagament, p.import,
                         p.metode_pagament, p.estat, p.facturat, p.numero_factura,
                         p.observacions, p.data_registre,
                         s.data_sessio, 
                         s.tipus_sessio,
                         pac.nom as nom_pacient, 
                         pac.cognoms as cognoms_pacient
                  FROM " . $this->table . " p
                  INNER JOIN sessions s ON p.id_sessio = s.id_sessio
                  INNER JOIN pacients pac ON s.id_pacient = pac.id_pacient
                  WHERE p.numero_factura = :numero_factura
                  ORDER BY p.data_pagament DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':numero_factura', $numero_factura);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // ============================================
    // MÈTODES ESTADÍSTICS I D'INFORMES
    // ============================================
    
    /**
     * Obtenir estadístiques generals
     * 
     * Retorna un resum d'estadístiques dels pagaments:
     * - Total de pagaments
     * - Import total
     * - Pagaments per estat
     * - Pagaments per mètode
     * - Pagaments facturats vs no facturats
     * 
     * @return array Array amb estadístiques
     */
    public function obtenirEstadistiques() {
        // Total i import
        $query = "SELECT 
                    COUNT(*) as total_pagaments,
                    SUM(import) as import_total,
                    SUM(CASE WHEN estat = 'Completado' THEN import ELSE 0 END) as import_completat,
                    SUM(CASE WHEN estat = 'Pendiente' THEN import ELSE 0 END) as import_pendent,
                    COUNT(CASE WHEN estat = 'Completado' THEN 1 END) as pagaments_completats,
                    COUNT(CASE WHEN estat = 'Pendiente' THEN 1 END) as pagaments_pendents,
                    COUNT(CASE WHEN estat = 'Anulado' THEN 1 END) as pagaments_anulats,
                    COUNT(CASE WHEN facturat = 1 THEN 1 END) as pagaments_facturats,
                    COUNT(CASE WHEN facturat = 0 AND estat = 'Completado' THEN 1 END) as pagaments_no_facturats
                  FROM " . $this->table;
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $estadistiques = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Per mètode de pagament
        $query = "SELECT metode_pagament, COUNT(*) as total, SUM(import) as import
                  FROM " . $this->table . "
                  WHERE estat = 'Completado'
                  GROUP BY metode_pagament";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $per_metode = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $estadistiques['per_metode'] = $per_metode;
        
        return $estadistiques;
    }
    
    /**
     * Obtenir ingressos per mes
     * 
     * Calcula els ingressos mensuals dels últims 12 mesos.
     * 
     * @return array Array amb els ingressos per mes
     */
    public function ingressosPerMes() {
        $query = "SELECT 
                    DATE_FORMAT(data_pagament, '%Y-%m') as mes,
                    SUM(import) as import_total,
                    COUNT(*) as num_pagaments
                  FROM " . $this->table . "
                  WHERE estat = 'Completado' 
                    AND data_pagament >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
                  GROUP BY DATE_FORMAT(data_pagament, '%Y-%m')
                  ORDER BY mes ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtenir ingressos d'aquest mes
     * 
     * Calcula els ingressos del mes actual.
     * 
     * @return array Dades d'ingressos del mes
     */
    public function ingressosAquestMes() {
        $query = "SELECT 
                    COUNT(*) as num_pagaments,
                    SUM(import) as import_total
                  FROM " . $this->table . "
                  WHERE estat = 'Completado'
                    AND YEAR(data_pagament) = YEAR(CURDATE())
                    AND MONTH(data_pagament) = MONTH(CURDATE())";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtenir ingressos per rang de dates
     * 
     * Calcula els ingressos en un rang de dates específic.
     * 
     * @param string $data_inici Data d'inici (YYYY-MM-DD)
     * @param string $data_fi Data de fi (YYYY-MM-DD)
     * @return array Dades d'ingressos del període
     */
    public function ingressosPerPeriode($data_inici, $data_fi) {
        $query = "SELECT 
                    COUNT(*) as num_pagaments,
                    SUM(import) as import_total,
                    AVG(import) as import_mitja,
                    MIN(import) as import_minim,
                    MAX(import) as import_maxim
                  FROM " . $this->table . "
                  WHERE estat = 'Completado'
                    AND data_pagament BETWEEN :data_inici AND :data_fi";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':data_inici', $data_inici);
        $stmt->bindParam(':data_fi', $data_fi);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtenir resum per mètode de pagament
     * 
     * Genera un resum dels pagaments agrupats per mètode.
     * 
     * @param string|null $data_inici Data d'inici (opcional)
     * @param string|null $data_fi Data de fi (opcional)
     * @return array Array amb resum per mètode
     */
    public function resumPerMetode($data_inici = null, $data_fi = null) {
        $query = "SELECT 
                    metode_pagament,
                    COUNT(*) as num_pagaments,
                    SUM(import) as import_total,
                    AVG(import) as import_mitja
                  FROM " . $this->table . "
                  WHERE estat = 'Completado'";
        
        if ($data_inici !== null && $data_fi !== null) {
            $query .= " AND data_pagament BETWEEN :data_inici AND :data_fi";
        }
        
        $query .= " GROUP BY metode_pagament ORDER BY import_total DESC";
        
        $stmt = $this->conn->prepare($query);
        
        if ($data_inici !== null && $data_fi !== null) {
            $stmt->bindParam(':data_inici', $data_inici);
            $stmt->bindParam(':data_fi', $data_fi);
        }
        
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtenir els últims pagaments
     * 
     * Retorna els últims N pagaments registrats.
     * 
     * @param int $limit Nombre de pagaments a retornar (per defecte 10)
     * @return array Array de pagaments
     */
    public function ultimsPagaments($limit = 10) {
        $query = "SELECT p.id_pagament, p.id_sessio, p.data_pagament, p.import,
                         p.metode_pagament, p.estat, p.facturat, p.numero_factura,
                         p.observacions, p.data_registre,
                         s.data_sessio, 
                         s.tipus_sessio,
                         pac.nom as nom_pacient, 
                         pac.cognoms as cognoms_pacient
                  FROM " . $this->table . " p
                  INNER JOIN sessions s ON p.id_sessio = s.id_sessio
                  INNER JOIN pacients pac ON s.id_pacient = pac.id_pacient
                  ORDER BY p.data_registre DESC
                  LIMIT :limit";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Verificar si una sessió té pagaments
     * 
     * Comprova si una sessió té algun pagament associat.
     * 
     * @param int $id_sessio ID de la sessió
     * @return bool True si té pagaments, false si no
     */
    public function sessionTePagaments($id_sessio) {
        $query = "SELECT COUNT(*) as total FROM " . $this->table . "
                  WHERE id_sessio = :id_sessio";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_sessio', $id_sessio);
        $stmt->execute();
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] > 0;
    }
    
    /**
     * Calcular total pagat per una sessió
     * 
     * Suma tots els imports dels pagaments completats d'una sessió.
     * 
     * @param int $id_sessio ID de la sessió
     * @return float Import total pagat
     */
    public function totalPagatSessio($id_sessio) {
        $query = "SELECT COALESCE(SUM(import), 0) as total
                  FROM " . $this->table . "
                  WHERE id_sessio = :id_sessio 
                    AND estat = 'Completado'";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_sessio', $id_sessio);
        $stmt->execute();
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'];
    }
    
    /**
     * Obtenir sessions sense pagar
     * 
     * Llista totes les sessions realitzades que no tenen cap pagament completat.
     * 
     * @return array Array de sessions sense pagar
     */
    public function sessionsSensePagar() {
        $query = "SELECT s.*, 
                         pac.nom as nom_pacient, 
                         pac.cognoms as cognoms_pacient
                  FROM sessions s
                  INNER JOIN pacients pac ON s.id_pacient = pac.id_pacient
                  LEFT JOIN (
                      SELECT id_sessio, SUM(CASE WHEN estat = 'Completado' THEN 1 ELSE 0 END) as pagaments_completats
                      FROM " . $this->table . "
                      GROUP BY id_sessio
                  ) p ON s.id_sessio = p.id_sessio
                  WHERE s.estat_sessio = 'Realitzada'
                    AND (p.pagaments_completats IS NULL OR p.pagaments_completats = 0)
                  ORDER BY s.data_sessio DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // ============================================
    // MÈTODES D'UTILITAT
    // ============================================
    
    /**
     * Validar dades del pagament
     * 
     * Verifica que les dades del pagament siguin correctes abans de crear/actualitzar.
     * 
     * @return array Array amb errors (buit si tot és correcte)
     */
    public function validar() {
        $errors = [];
        
        // Validar id_sessio
        if (empty($this->id_sessio)) {
            $errors[] = "L'ID de la sessió és obligatori";
        }
        
        // Validar import
        if (empty($this->import) || $this->import <= 0) {
            $errors[] = "L'import ha de ser major que zero";
        }
        
        // Validar mètode de pagament
        $metodes_valids = ['Efectivo', 'Tarjeta', 'Transferencia', 'Bizum'];
        if (!in_array($this->metode_pagament, $metodes_valids)) {
            $errors[] = "El mètode de pagament no és vàlid";
        }
        
        // Validar estat
        $estats_valids = ['Pendiente', 'Completado', 'Anulado'];
        if (!empty($this->estat) && !in_array($this->estat, $estats_valids)) {
            $errors[] = "L'estat del pagament no és vàlid";
        }
        
        // Validar data
        if (!empty($this->data_pagament)) {
            $data = DateTime::createFromFormat('Y-m-d', $this->data_pagament);
            if (!$data || $data->format('Y-m-d') !== $this->data_pagament) {
                $errors[] = "La data del pagament no és vàlida";
            }
        }
        
        return $errors;
    }
    
    /**
     * Comptar pagaments
     * 
     * Compta el nombre total de pagaments amb filtres opcionals.
     * 
     * @param string|null $estat Estat del pagament (opcional)
     * @param bool|null $facturat Si està facturat (opcional)
     * @return int Nombre de pagaments
     */
    public function comptarPagaments($estat = null, $facturat = null) {
        $query = "SELECT COUNT(*) as total FROM " . $this->table . " WHERE 1=1";
        
        if ($estat !== null) {
            $query .= " AND estat = :estat";
        }
        if ($facturat !== null) {
            $query .= " AND facturat = :facturat";
        }
        
        $stmt = $this->conn->prepare($query);
        
        if ($estat !== null) {
            $stmt->bindParam(':estat', $estat);
        }
        if ($facturat !== null) {
            $stmt->bindParam(':facturat', $facturat, PDO::PARAM_BOOL);
        }
        
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result['total'];
    }
}
?>
