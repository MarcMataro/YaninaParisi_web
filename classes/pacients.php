<?php
/**
 * Classe Pacient
 * 
 * Gestiona totes les operacions relacionades amb els pacients del sistema.
 * Aquesta classe implementa el patró Active Record per interactuar amb la taula 'pacients'.
 * 
 * @author Marc Mataró
 * @version 1.0.0
 * @date 2025-10-06
 */

class Pacient {
    
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
    private $table = 'pacients';
    
    // Propietats d'identificació
    
    /**
     * @var int|null ID únic del pacient (clau primària)
     */
    public $id_pacient;
    
    /**
     * @var string Nom del pacient
     */
    public $nom;
    
    /**
     * @var string Cognoms del pacient
     */
    public $cognoms;
    
    /**
     * @var string|null Data de naixement (format YYYY-MM-DD)
     */
    public $data_naixement;
    
    /**
     * @var string|null Sexe del pacient ('Home', 'Dona', 'Altre', 'No especificat')
     */
    public $sexe;
    
    // Propietats de contacte
    
    /**
     * @var string|null Telèfon de contacte principal
     */
    public $telefon;
    
    /**
     * @var string|null Correu electrònic
     */
    public $email;
    
    /**
     * @var string|null Adreça completa del domicili
     */
    public $adreca;
    
    /**
     * @var string|null Ciutat de residència
     */
    public $ciutat;
    
    /**
     * @var string|null Codi postal
     */
    public $codi_postal;
    
    // Informació mèdica rellevant
    
    /**
     * @var string|null Antecedents mèdics rellevants per la teràpia
     */
    public $antecedents_medics;
    
    /**
     * @var string|null Medicació actual que pren el pacient
     */
    public $medicacio_actual;
    
    /**
     * @var string|null Al·lèrgies conegudes
     */
    public $alergies;
    
    // Informació de contacte d'emergència
    
    /**
     * @var string|null Nom complet del contacte d'emergència
     */
    public $contacte_emergencia_nom;
    
    /**
     * @var string|null Telèfon del contacte d'emergència
     */
    public $contacte_emergencia_telefon;
    
    /**
     * @var string|null Relació amb el pacient (familiar, amic, etc.)
     */
    public $contacte_emergencia_relacio;
    
    // Dates del sistema
    
    /**
     * @var string|null Data i hora de registre al sistema
     */
    public $data_registre;
    
    /**
     * @var string|null Data i hora de l'última actualització
     */
    public $data_ultima_actualitzacio;
    
    // Estat del pacient
    
    /**
     * @var string Estat actual del pacient ('Actiu', 'Inactiu', 'Alta', 'Seguiment')
     */
    public $estat;
    
    // Observacions addicionals
    
    /**
     * @var string|null Observacions o notes generals sobre el pacient
     */
    public $observacions;
    
    
    // ============================================
    // CONSTRUCTOR
    // ============================================
    
    /**
     * Constructor de la classe Pacient
     * 
     * Inicialitza la connexió a la base de dades.
     * 
     * @param PDO $db Objecte de connexió PDO a la base de dades
     * @throws InvalidArgumentException Si la connexió no és vàlida
     */
    public function __construct($db) {
        if (!$db instanceof PDO) {
            throw new InvalidArgumentException("La connexió a la base de dades ha de ser una instància de PDO");
        }
        $this->conn = $db;
    }
    
    
    // ============================================
    // MÈTODES CRUD (Create, Read, Update, Delete)
    // ============================================
    
    /**
     * Crear un nou pacient a la base de dades
     * 
     * Insereix un nou registre de pacient amb totes les dades proporcionades.
     * Valida les dades abans d'inserir i retorna el nou ID generat.
     * 
     * @return int|false Retorna l'ID del nou pacient o false si falla
     * @throws PDOException Si hi ha un error en la consulta SQL
     */
    public function crear() {
        // Validar dades obligatòries
        if (!$this->validarDadesObligatories()) {
            return false;
        }
        
        // Query SQL per inserir
        $query = "INSERT INTO " . $this->table . " 
                  SET nom = :nom,
                      cognoms = :cognoms,
                      data_naixement = :data_naixement,
                      sexe = :sexe,
                      telefon = :telefon,
                      email = :email,
                      adreca = :adreca,
                      ciutat = :ciutat,
                      codi_postal = :codi_postal,
                      antecedents_medics = :antecedents_medics,
                      medicacio_actual = :medicacio_actual,
                      alergies = :alergies,
                      contacte_emergencia_nom = :contacte_emergencia_nom,
                      contacte_emergencia_telefon = :contacte_emergencia_telefon,
                      contacte_emergencia_relacio = :contacte_emergencia_relacio,
                      estat = :estat,
                      observacions = :observacions";
        
        // Preparar la consulta
        $stmt = $this->conn->prepare($query);
        
        // Netejar i vincular les dades
        $this->nom = htmlspecialchars(strip_tags($this->nom));
        $this->cognoms = htmlspecialchars(strip_tags($this->cognoms));
        $this->email = filter_var($this->email, FILTER_SANITIZE_EMAIL);
        $this->estat = $this->estat ?? 'Activo';
        
        // Bind dels paràmetres
        $stmt->bindParam(':nom', $this->nom);
        $stmt->bindParam(':cognoms', $this->cognoms);
        $stmt->bindParam(':data_naixement', $this->data_naixement);
        $stmt->bindParam(':sexe', $this->sexe);
        $stmt->bindParam(':telefon', $this->telefon);
        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':adreca', $this->adreca);
        $stmt->bindParam(':ciutat', $this->ciutat);
        $stmt->bindParam(':codi_postal', $this->codi_postal);
        $stmt->bindParam(':antecedents_medics', $this->antecedents_medics);
        $stmt->bindParam(':medicacio_actual', $this->medicacio_actual);
        $stmt->bindParam(':alergies', $this->alergies);
        $stmt->bindParam(':contacte_emergencia_nom', $this->contacte_emergencia_nom);
        $stmt->bindParam(':contacte_emergencia_telefon', $this->contacte_emergencia_telefon);
        $stmt->bindParam(':contacte_emergencia_relacio', $this->contacte_emergencia_relacio);
        $stmt->bindParam(':estat', $this->estat);
        $stmt->bindParam(':observacions', $this->observacions);
        
        // Executar la consulta
        if ($stmt->execute()) {
            $this->id_pacient = $this->conn->lastInsertId();
            return $this->id_pacient;
        }
        
        return false;
    }
    
    /**
     * Llegir/obtenir tots els pacients
     * 
     * Obté un llistat de tots els pacients de la base de dades.
     * Pot filtrar per estat si es proporciona.
     * 
     * @param string|null $estat Filtrar per estat ('Actiu', 'Inactiu', 'Alta', 'Seguiment')
     * @param string $ordre Camp pel qual ordenar (per defecte: cognoms)
     * @param string $direccio Direcció de l'ordre (ASC o DESC)
     * @return PDOStatement Resultat de la consulta
     */
    public function llegirTots($estat = null, $ordre = 'cognoms', $direccio = 'ASC') {
        // Query base
        $query = "SELECT * FROM " . $this->table;
        
        // Afegir filtre per estat si s'especifica
        if ($estat !== null) {
            $query .= " WHERE estat = :estat";
        }
        
        // Afegir ordenació
        $query .= " ORDER BY " . $ordre . " " . $direccio;
        
        // Preparar la consulta
        $stmt = $this->conn->prepare($query);
        
        // Bind del paràmetre estat si cal
        if ($estat !== null) {
            $stmt->bindParam(':estat', $estat);
        }
        
        // Executar
        $stmt->execute();
        
        return $stmt;
    }
    
    /**
     * Llegir un pacient específic per ID
     * 
     * Obté tota la informació d'un pacient concret mitjançant el seu ID.
     * Carrega les dades a les propietats de l'objecte.
     * 
     * @return bool True si s'ha trobat el pacient, false si no existeix
     */
    public function llegirUn() {
        // Query SQL
        $query = "SELECT * FROM " . $this->table . " WHERE id_pacient = :id_pacient LIMIT 1";
        
        // Preparar la consulta
        $stmt = $this->conn->prepare($query);
        
        // Bind del paràmetre
        $stmt->bindParam(':id_pacient', $this->id_pacient);
        
        // Executar
        $stmt->execute();
        
        // Obtenir el resultat
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Si s'ha trobat el pacient, carregar les dades
        if ($row) {
            $this->nom = $row['nom'];
            $this->cognoms = $row['cognoms'];
            $this->data_naixement = $row['data_naixement'];
            $this->sexe = $row['sexe'];
            $this->telefon = $row['telefon'];
            $this->email = $row['email'];
            $this->adreca = $row['adreca'];
            $this->ciutat = $row['ciutat'];
            $this->codi_postal = $row['codi_postal'];
            $this->antecedents_medics = $row['antecedents_medics'];
            $this->medicacio_actual = $row['medicacio_actual'];
            $this->alergies = $row['alergies'];
            $this->contacte_emergencia_nom = $row['contacte_emergencia_nom'];
            $this->contacte_emergencia_telefon = $row['contacte_emergencia_telefon'];
            $this->contacte_emergencia_relacio = $row['contacte_emergencia_relacio'];
            $this->data_registre = $row['data_registre'];
            $this->data_ultima_actualitzacio = $row['data_ultima_actualitzacio'];
            $this->estat = $row['estat'];
            $this->observacions = $row['observacions'];
            
            return true;
        }
        
        return false;
    }
    
    /**
     * Actualitzar un pacient existent
     * 
     * Actualitza totes les dades d'un pacient a la base de dades.
     * La data d'última actualització s'actualitza automàticament.
     * 
     * @return bool True si s'ha actualitzat correctament, false si ha fallat
     */
    public function actualitzar() {
        // Validar que existeix l'ID
        if (!$this->id_pacient) {
            return false;
        }
        
        // Validar dades obligatòries
        if (!$this->validarDadesObligatories()) {
            return false;
        }
        
        // Query SQL
        $query = "UPDATE " . $this->table . "
                  SET nom = :nom,
                      cognoms = :cognoms,
                      data_naixement = :data_naixement,
                      sexe = :sexe,
                      telefon = :telefon,
                      email = :email,
                      adreca = :adreca,
                      ciutat = :ciutat,
                      codi_postal = :codi_postal,
                      antecedents_medics = :antecedents_medics,
                      medicacio_actual = :medicacio_actual,
                      alergies = :alergies,
                      contacte_emergencia_nom = :contacte_emergencia_nom,
                      contacte_emergencia_telefon = :contacte_emergencia_telefon,
                      contacte_emergencia_relacio = :contacte_emergencia_relacio,
                      estat = :estat,
                      observacions = :observacions
                  WHERE id_pacient = :id_pacient";
        
        // Preparar la consulta
        $stmt = $this->conn->prepare($query);
        
        // Netejar les dades
        $this->nom = htmlspecialchars(strip_tags($this->nom));
        $this->cognoms = htmlspecialchars(strip_tags($this->cognoms));
        $this->email = filter_var($this->email, FILTER_SANITIZE_EMAIL);
        
        // Bind dels paràmetres
        $stmt->bindParam(':id_pacient', $this->id_pacient);
        $stmt->bindParam(':nom', $this->nom);
        $stmt->bindParam(':cognoms', $this->cognoms);
        $stmt->bindParam(':data_naixement', $this->data_naixement);
        $stmt->bindParam(':sexe', $this->sexe);
        $stmt->bindParam(':telefon', $this->telefon);
        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':adreca', $this->adreca);
        $stmt->bindParam(':ciutat', $this->ciutat);
        $stmt->bindParam(':codi_postal', $this->codi_postal);
        $stmt->bindParam(':antecedents_medics', $this->antecedents_medics);
        $stmt->bindParam(':medicacio_actual', $this->medicacio_actual);
        $stmt->bindParam(':alergies', $this->alergies);
        $stmt->bindParam(':contacte_emergencia_nom', $this->contacte_emergencia_nom);
        $stmt->bindParam(':contacte_emergencia_telefon', $this->contacte_emergencia_telefon);
        $stmt->bindParam(':contacte_emergencia_relacio', $this->contacte_emergencia_relacio);
        $stmt->bindParam(':estat', $this->estat);
        $stmt->bindParam(':observacions', $this->observacions);
        
        // Executar
        return $stmt->execute();
    }
    
    /**
     * Eliminar un pacient (NO RECOMANAT)
     * 
     * Elimina definitivament un pacient de la base de dades.
     * ATENCIÓ: Aquesta acció és irreversible. És millor canviar l'estat a 'Inactiu'.
     * 
     * @return bool True si s'ha eliminat correctament, false si ha fallat
     */
    public function eliminar() {
        // Query SQL
        $query = "DELETE FROM " . $this->table . " WHERE id_pacient = :id_pacient";
        
        // Preparar la consulta
        $stmt = $this->conn->prepare($query);
        
        // Bind del paràmetre
        $stmt->bindParam(':id_pacient', $this->id_pacient);
        
        // Executar
        return $stmt->execute();
    }
    
    
    // ============================================
    // MÈTODES DE CERCA I FILTRATGE
    // ============================================
    
    /**
     * Cercar pacients per nom o cognoms
     * 
     * Cerca pacients que continguin el text proporcionat al nom o cognoms.
     * Utilitza LIKE per fer cerques parcials (case-insensitive).
     * 
     * @param string $text Text a cercar
     * @return PDOStatement Resultat de la consulta
     */
    public function cercarPerNom($text) {
        $query = "SELECT * FROM " . $this->table . " 
                  WHERE nom LIKE :text OR cognoms LIKE :text 
                  ORDER BY cognoms ASC";
        
        $stmt = $this->conn->prepare($query);
        $text = '%' . htmlspecialchars(strip_tags($text)) . '%';
        $stmt->bindParam(':text', $text);
        $stmt->execute();
        
        return $stmt;
    }
    
    /**
     * Cercar pacients per email
     * 
     * @param string $email Email a cercar
     * @return array|false Dades del pacient o false si no es troba
     */
    public function cercarPerEmail($email) {
        $query = "SELECT * FROM " . $this->table . " WHERE email = :email LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $email = filter_var($email, FILTER_SANITIZE_EMAIL);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Filtrar pacients per ciutat
     * 
     * @param string $ciutat Ciutat per filtrar
     * @return PDOStatement Resultat de la consulta
     */
    public function filtrarPerCiutat($ciutat) {
        $query = "SELECT * FROM " . $this->table . " 
                  WHERE ciutat = :ciutat 
                  ORDER BY cognoms ASC";
        
        $stmt = $this->conn->prepare($query);
        $ciutat = htmlspecialchars(strip_tags($ciutat));
        $stmt->bindParam(':ciutat', $ciutat);
        $stmt->execute();
        
        return $stmt;
    }
    
    /**
     * Obtenir pacients per rang d'edat
     * 
     * Filtra pacients segons la seva edat (calculada a partir de la data de naixement).
     * 
     * @param int $edatMin Edat mínima
     * @param int $edatMax Edat màxima
     * @return PDOStatement Resultat de la consulta
     */
    public function filtrarPerEdat($edatMin, $edatMax) {
        $query = "SELECT *, 
                  TIMESTAMPDIFF(YEAR, data_naixement, CURDATE()) AS edat 
                  FROM " . $this->table . " 
                  HAVING edat BETWEEN :edatMin AND :edatMax 
                  ORDER BY cognoms ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':edatMin', $edatMin, PDO::PARAM_INT);
        $stmt->bindParam(':edatMax', $edatMax, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt;
    }
    
    
    // ============================================
    // MÈTODES D'ESTAT
    // ============================================
    
    /**
     * Canviar l'estat d'un pacient
     * 
     * Actualitza l'estat del pacient a la base de dades.
     * Estats vàlids: 'Actiu', 'Inactiu', 'Alta', 'Seguiment'
     * 
     * @param string $nouEstat Nou estat del pacient
     * @return bool True si s'ha actualitzat correctament, false si ha fallat
     */
    public function canviarEstat($nouEstat) {
        // Validar que l'estat és vàlid
        $estatsValids = ['Activo', 'Inactivo', 'Alta', 'Seguimiento'];
        if (!in_array($nouEstat, $estatsValids)) {
            return false;
        }
        
        $query = "UPDATE " . $this->table . " 
                  SET estat = :estat 
                  WHERE id_pacient = :id_pacient";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':estat', $nouEstat);
        $stmt->bindParam(':id_pacient', $this->id_pacient);
        
        if ($stmt->execute()) {
            $this->estat = $nouEstat;
            return true;
        }
        
        return false;
    }
    
    /**
     * Activar un pacient
     * 
     * Canvia l'estat del pacient a 'Activo'.
     * 
     * @return bool True si s'ha activat correctament
     */
    public function activar() {
        return $this->canviarEstat('Activo');
    }
    
    /**
     * Desactivar un pacient
     * 
     * Canvia l'estat del pacient a 'Inactivo'.
     * 
     * @return bool True si s'ha desactivat correctament
     */
    public function desactivar() {
        return $this->canviarEstat('Inactivo');
    }
    
    /**
     * Donar l'alta a un pacient
     * 
     * Canvia l'estat del pacient a 'Alta' (ja no necessita seguiment).
     * 
     * @return bool True si s'ha donat l'alta correctament
     */
    public function donarAlta() {
        return $this->canviarEstat('Alta');
    }
    
    
    // ============================================
    // MÈTODES D'ESTADÍSTIQUES
    // ============================================
    
    /**
     * Comptar total de pacients
     * 
     * @param string|null $estat Filtrar per estat (opcional)
     * @return int Nombre total de pacients
     */
    public function comptarTotal($estat = null) {
        $query = "SELECT COUNT(*) as total FROM " . $this->table;
        
        if ($estat !== null) {
            $query .= " WHERE estat = :estat";
        }
        
        $stmt = $this->conn->prepare($query);
        
        if ($estat !== null) {
            $stmt->bindParam(':estat', $estat);
        }
        
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return (int) $row['total'];
    }
    
    /**
     * Obtenir estadístiques generals de pacients
     * 
     * Retorna un array amb estadístiques: total, actius, inactius, alta, seguiment.
     * 
     * @return array Estadístiques de pacients
     */
    public function obtenirEstadistiques() {
        return [
            'total' => $this->comptarTotal(),
            'actius' => $this->comptarTotal('Activo'),
            'inactius' => $this->comptarTotal('Inactivo'),
            'alta' => $this->comptarTotal('Alta'),
            'seguiment' => $this->comptarTotal('Seguimiento')
        ];
    }
    
    /**
     * Obtenir pacients nous aquest mes
     * 
     * @return PDOStatement Pacients registrats aquest mes
     */
    public function pacientsDaquestMes() {
        $query = "SELECT * FROM " . $this->table . " 
                  WHERE MONTH(data_registre) = MONTH(CURRENT_DATE()) 
                  AND YEAR(data_registre) = YEAR(CURRENT_DATE()) 
                  ORDER BY data_registre DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt;
    }
    
    
    // ============================================
    // MÈTODES DE VALIDACIÓ
    // ============================================
    
    /**
     * Validar dades obligatòries
     * 
     * Comprova que els camps obligatoris (nom, cognoms) estiguin emplenats.
     * 
     * @return bool True si les dades són vàlides, false si no
     */
    private function validarDadesObligatories() {
        if (empty($this->nom) || empty($this->cognoms)) {
            return false;
        }
        return true;
    }
    
    /**
     * Validar format d'email
     * 
     * @param string $email Email a validar
     * @return bool True si l'email és vàlid, false si no
     */
    public static function validarEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
    
    /**
     * Validar format de telèfon espanyol
     * 
     * Accepta telèfons fixos (9 dígits que comencen per 8 o 9) i mòbils (9 dígits que comencen per 6 o 7).
     * També accepta el prefix internacional +34.
     * 
     * @param string $telefon Telèfon a validar
     * @return bool True si el telèfon és vàlid, false si no
     */
    public static function validarTelefon($telefon) {
        // Eliminar espais, guions i parèntesis
        $telefon = preg_replace('/[\s\-\(\)]/', '', $telefon);
        
        // Acceptar formats: 666666666, +34666666666, 0034666666666
        return preg_match('/^(\+34|0034)?[6-9][0-9]{8}$/', $telefon);
    }
    
    /**
     * Validar que el pacient sigui major d'edat
     * 
     * @param string $dataNaixement Data de naixement (format YYYY-MM-DD)
     * @return bool True si és major d'edat (>=18 anys), false si no
     */
    public static function esMajorEdat($dataNaixement) {
        $data = new DateTime($dataNaixement);
        $ara = new DateTime();
        $edat = $ara->diff($data)->y;
        
        return $edat >= 18;
    }
    
    
    // ============================================
    // MÈTODES AUXILIARS
    // ============================================
    
    /**
     * Calcular l'edat del pacient
     * 
     * Calcula l'edat actual del pacient a partir de la seva data de naixement.
     * 
     * @return int|null Edat en anys, o null si no hi ha data de naixement
     */
    public function calcularEdat() {
        if (!$this->data_naixement) {
            return null;
        }
        
        $dataNaixement = new DateTime($this->data_naixement);
        $ara = new DateTime();
        
        return $ara->diff($dataNaixement)->y;
    }
    
    /**
     * Obtenir nom complet
     * 
     * Retorna el nom i cognoms concatenats.
     * 
     * @return string Nom complet del pacient
     */
    public function getNomComplet() {
        return trim($this->nom . ' ' . $this->cognoms);
    }
    
    /**
     * Formatar telèfon per mostrar
     * 
     * Formata el telèfon amb espais per millorar la llegibilitat.
     * Format: +34 666 66 66 66
     * 
     * @return string|null Telèfon formatat
     */
    public function formatarTelefon() {
        if (!$this->telefon) {
            return null;
        }
        
        // Eliminar tot excepte números i +
        $telefon = preg_replace('/[^0-9+]/', '', $this->telefon);
        
        // Si té prefix +34
        if (strpos($telefon, '+34') === 0) {
            $numero = substr($telefon, 3);
            return '+34 ' . substr($numero, 0, 3) . ' ' . 
                   substr($numero, 3, 2) . ' ' . 
                   substr($numero, 5, 2) . ' ' . 
                   substr($numero, 7, 2);
        }
        
        // Si són 9 dígits sense prefix
        if (strlen($telefon) === 9) {
            return substr($telefon, 0, 3) . ' ' . 
                   substr($telefon, 3, 2) . ' ' . 
                   substr($telefon, 5, 2) . ' ' . 
                   substr($telefon, 7, 2);
        }
        
        return $this->telefon;
    }
    
    /**
     * Obtenir temps des del registre
     * 
     * Calcula quant temps fa que el pacient està registrat al sistema.
     * 
     * @return string Temps en format llegible (ex: "3 mesos", "1 any")
     */
    public function getTempsRegistre() {
        if (!$this->data_registre) {
            return 'Desconegut';
        }
        
        $dataRegistre = new DateTime($this->data_registre);
        $ara = new DateTime();
        $interval = $ara->diff($dataRegistre);
        
        if ($interval->y > 0) {
            return $interval->y . ($interval->y === 1 ? ' any' : ' anys');
        } elseif ($interval->m > 0) {
            return $interval->m . ($interval->m === 1 ? ' mes' : ' mesos');
        } elseif ($interval->d > 0) {
            return $interval->d . ($interval->d === 1 ? ' dia' : ' dies');
        } else {
            return 'Avui';
        }
    }
    
    /**
     * Exportar dades a array
     * 
     * Retorna totes les dades del pacient en format array.
     * Útil per a exportacions o APIs.
     * 
     * @return array Dades del pacient
     */
    public function toArray() {
        return [
            'id_pacient' => $this->id_pacient,
            'nom' => $this->nom,
            'cognoms' => $this->cognoms,
            'nom_complet' => $this->getNomComplet(),
            'data_naixement' => $this->data_naixement,
            'edat' => $this->calcularEdat(),
            'sexe' => $this->sexe,
            'telefon' => $this->telefon,
            'telefon_formatat' => $this->formatarTelefon(),
            'email' => $this->email,
            'adreca' => $this->adreca,
            'ciutat' => $this->ciutat,
            'codi_postal' => $this->codi_postal,
            'antecedents_medics' => $this->antecedents_medics,
            'medicacio_actual' => $this->medicacio_actual,
            'alergies' => $this->alergies,
            'contacte_emergencia_nom' => $this->contacte_emergencia_nom,
            'contacte_emergencia_telefon' => $this->contacte_emergencia_telefon,
            'contacte_emergencia_relacio' => $this->contacte_emergencia_relacio,
            'data_registre' => $this->data_registre,
            'temps_registre' => $this->getTempsRegistre(),
            'data_ultima_actualitzacio' => $this->data_ultima_actualitzacio,
            'estat' => $this->estat,
            'observacions' => $this->observacions
        ];
    }
    
    /**
     * Exportar dades a JSON
     * 
     * @return string JSON amb les dades del pacient
     */
    public function toJSON() {
        return json_encode($this->toArray(), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }
    
    /**
     * Comprovar si el pacient té informació mèdica
     * 
     * @return bool True si té antecedents, medicació o al·lèrgies registrades
     */
    public function teInformacioMedica() {
        return !empty($this->antecedents_medics) || 
               !empty($this->medicacio_actual) || 
               !empty($this->alergies);
    }
    
    /**
     * Comprovar si el pacient té contacte d'emergència
     * 
     * @return bool True si té contacte d'emergència registrat
     */
    public function teContacteEmergencia() {
        return !empty($this->contacte_emergencia_nom) && 
               !empty($this->contacte_emergencia_telefon);
    }
    
    /**
     * Obtenir inicials del pacient
     * 
     * @return string Inicials (ex: "JP" per Joan Pons)
     */
    public function getInicials() {
        $inicialNom = !empty($this->nom) ? strtoupper(substr($this->nom, 0, 1)) : '';
        $inicialCognom = !empty($this->cognoms) ? strtoupper(substr($this->cognoms, 0, 1)) : '';
        
        return $inicialNom . $inicialCognom;
    }

    // ============================================
    // MÈTODES D'INSTÀNCIA
    // ============================================
    /**
     * Crear instància amb connexió automàtica
     * 
     * @return Pacient Nova instància amb connexió establerta
     */
    public static function getInstance() {
        $connexio = Connexio::getInstance();
        return new self($connexio->getConnexio());
    }
}
?>
