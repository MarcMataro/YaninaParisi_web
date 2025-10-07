<?php
/**
 * Classe Session
 * 
 * Gestiona totes les operacions relacionades amb les sessions terapèutiques del sistema.
 * Aquesta classe implementa el patró Active Record per interactuar amb la taula 'sessions'.
 * 
 * Funcionalitats principals:
 * - Crear i gestionar sessions terapèutiques
 * - Programar, modificar i cancel·lar cites
 * - Registrar assistència i notes de sessió
 * - Gestionar preus i facturació
 * - Generar informes i estadístiques
 * 
 * @author Marc Mataró
 * @version 1.0.0
 * @date 2025-10-06
 */

class Session {
    
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
    private $table = 'sessions';
    
    /**
     * @var int ID únic de la sessió
     */
    public $id_sessio;
    
    /**
     * @var int ID del pacient associat a la sessió
     */
    public $id_pacient;
    
    /**
     * @var string Data de la sessió (format: YYYY-MM-DD)
     */
    public $data_sessio;
    
    /**
     * @var string Hora d'inici de la sessió (format: HH:MM:SS)
     */
    public $hora_inici;
    
    /**
     * @var string Hora de finalització de la sessió (format: HH:MM:SS)
     */
    public $hora_fi;
    
    /**
     * @var string Tipus de sessió ('Individual', 'Pareja', 'Familiar', 'Grupo')
     */
    public $tipus_sessio;
    
    /**
     * @var string Estat de la sessió ('Programada', 'Realizada', 'Cancelada', 'No asistida')
     */
    public $estat_sessio;
    
    /**
     * @var string Ubicació de la sessió ('Presencial', 'Online')
     */
    public $ubicacio;
    
    /**
     * @var float Preu de la sessió
     */
    public $preu_sessio;
    
    /**
     * @var string Notes del terapeuta sobre la sessió
     */
    public $notes_terapeuta;
    
    /**
     * @var string Data i hora de creació del registre
     */
    public $data_creacio;
    
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
     * Crear una nova sessió
     * 
     * Insereix una nova sessió a la base de dades amb totes les dades proporcionades.
     * Valida que els camps obligatoris estiguin emplenats.
     * 
     * @return int|false ID de la sessió creada o false si hi ha error
     */
    public function crear() {
        // Validar dades obligatòries
        if (!$this->validarDadesObligatories()) {
            return false;
        }
        
        // Query SQL per inserir
        $query = "INSERT INTO " . $this->table . " 
                  SET id_pacient = :id_pacient,
                      data_sessio = :data_sessio,
                      hora_inici = :hora_inici,
                      hora_fi = :hora_fi,
                      tipus_sessio = :tipus_sessio,
                      estat_sessio = :estat_sessio,
                      ubicacio = :ubicacio,
                      preu_sessio = :preu_sessio,
                      notes_terapeuta = :notes_terapeuta";
        
        // Preparar la consulta
        $stmt = $this->conn->prepare($query);
        
        // Netejar i vincular les dades
        $this->id_pacient = htmlspecialchars(strip_tags($this->id_pacient));
        $this->data_sessio = htmlspecialchars(strip_tags($this->data_sessio));
        $this->hora_inici = htmlspecialchars(strip_tags($this->hora_inici));
        $this->hora_fi = htmlspecialchars(strip_tags($this->hora_fi));
        $this->tipus_sessio = $this->tipus_sessio ?? 'Individual';
        $this->estat_sessio = $this->estat_sessio ?? 'Programada';
        $this->ubicacio = $this->ubicacio ?? 'Presencial';
        $this->preu_sessio = $this->preu_sessio ?? 0.00;
        
        // Bind dels paràmetres
        $stmt->bindParam(':id_pacient', $this->id_pacient);
        $stmt->bindParam(':data_sessio', $this->data_sessio);
        $stmt->bindParam(':hora_inici', $this->hora_inici);
        $stmt->bindParam(':hora_fi', $this->hora_fi);
        $stmt->bindParam(':tipus_sessio', $this->tipus_sessio);
        $stmt->bindParam(':estat_sessio', $this->estat_sessio);
        $stmt->bindParam(':ubicacio', $this->ubicacio);
        $stmt->bindParam(':preu_sessio', $this->preu_sessio);
        $stmt->bindParam(':notes_terapeuta', $this->notes_terapeuta);
        
        // Executar la consulta
        if ($stmt->execute()) {
            $this->id_sessio = $this->conn->lastInsertId();
            return $this->id_sessio;
        }
        
        return false;
    }
    
    /**
     * Llegir totes les sessions
     * 
     * Obté una llista de sessions amb possibilitat de filtrar per pacient, data o estat.
     * Inclou informació del pacient mitjançant JOIN.
     * 
     * @param int|null $id_pacient Filtrar per pacient específic
     * @param string|null $estat Filtrar per estat de sessió
     * @param string|null $data_inici Data d'inici del rang (YYYY-MM-DD)
     * @param string|null $data_fi Data de fi del rang (YYYY-MM-DD)
     * @param string $ordre Camp per ordenar ('data_sessio', 'hora_inici', 'estat_sessio')
     * @param string $direccio Direcció d'ordenació ('ASC' o 'DESC')
     * @return PDOStatement Resultat de la consulta
     */
    public function llegirTotes($id_pacient = null, $estat = null, $data_inici = null, $data_fi = null, $ordre = 'data_sessio', $direccio = 'DESC') {
        // Query base amb JOIN per obtenir dades del pacient
        $query = "SELECT s.*, 
                         p.nom, 
                         p.cognoms,
                         CONCAT(p.nom, ' ', p.cognoms) as nom_complet_pacient
                  FROM " . $this->table . " s
                  LEFT JOIN pacients p ON s.id_pacient = p.id_pacient
                  WHERE 1=1";
        
        // Afegir filtres opcionals
        if ($id_pacient !== null) {
            $query .= " AND s.id_pacient = :id_pacient";
        }
        
        if ($estat !== null) {
            $query .= " AND s.estat_sessio = :estat";
        }
        
        if ($data_inici !== null) {
            $query .= " AND s.data_sessio >= :data_inici";
        }
        
        if ($data_fi !== null) {
            $query .= " AND s.data_sessio <= :data_fi";
        }
        
        // Afegir ordenació
        $query .= " ORDER BY s." . $ordre . " " . $direccio;
        
        // Preparar la consulta
        $stmt = $this->conn->prepare($query);
        
        // Bind dels paràmetres opcionals
        if ($id_pacient !== null) {
            $stmt->bindParam(':id_pacient', $id_pacient);
        }
        
        if ($estat !== null) {
            $stmt->bindParam(':estat', $estat);
        }
        
        if ($data_inici !== null) {
            $stmt->bindParam(':data_inici', $data_inici);
        }
        
        if ($data_fi !== null) {
            $stmt->bindParam(':data_fi', $data_fi);
        }
        
        // Executar
        $stmt->execute();
        
        return $stmt;
    }
    
    /**
     * Llegir una sessió específica per ID
     * 
     * Obté tota la informació d'una sessió concreta mitjançant el seu ID.
     * Carrega les dades a les propietats de l'objecte.
     * 
     * @return bool True si s'ha trobat la sessió, false si no existeix
     */
    public function llegirUna() {
        // Query SQL amb JOIN per obtenir dades del pacient
        $query = "SELECT s.*, 
                         p.nom, 
                         p.cognoms,
                         CONCAT(p.nom, ' ', p.cognoms) as nom_complet_pacient
                  FROM " . $this->table . " s
                  LEFT JOIN pacients p ON s.id_pacient = p.id_pacient
                  WHERE s.id_sessio = :id_sessio 
                  LIMIT 1";
        
        // Preparar la consulta
        $stmt = $this->conn->prepare($query);
        
        // Bind del paràmetre
        $stmt->bindParam(':id_sessio', $this->id_sessio);
        
        // Executar
        $stmt->execute();
        
        // Obtenir el resultat
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Si s'ha trobat la sessió, carregar les dades
        if ($row) {
            $this->id_pacient = $row['id_pacient'];
            $this->data_sessio = $row['data_sessio'];
            $this->hora_inici = $row['hora_inici'];
            $this->hora_fi = $row['hora_fi'];
            $this->tipus_sessio = $row['tipus_sessio'];
            $this->estat_sessio = $row['estat_sessio'];
            $this->ubicacio = $row['ubicacio'];
            $this->preu_sessio = $row['preu_sessio'];
            $this->notes_terapeuta = $row['notes_terapeuta'];
            $this->data_creacio = $row['data_creacio'];
            
            return true;
        }
        
        return false;
    }
    
    /**
     * Actualitzar una sessió existent
     * 
     * Modifica les dades d'una sessió ja existent a la base de dades.
     * Valida que els camps obligatoris estiguin emplenats.
     * 
     * @return bool True si s'ha actualitzat correctament, false si hi ha error
     */
    public function actualitzar() {
        // Validar dades obligatòries
        if (!$this->validarDadesObligatories()) {
            return false;
        }
        
        // Query SQL
        $query = "UPDATE " . $this->table . "
                  SET id_pacient = :id_pacient,
                      data_sessio = :data_sessio,
                      hora_inici = :hora_inici,
                      hora_fi = :hora_fi,
                      tipus_sessio = :tipus_sessio,
                      estat_sessio = :estat_sessio,
                      ubicacio = :ubicacio,
                      preu_sessio = :preu_sessio,
                      notes_terapeuta = :notes_terapeuta
                  WHERE id_sessio = :id_sessio";
        
        // Preparar la consulta
        $stmt = $this->conn->prepare($query);
        
        // Netejar les dades
        $this->id_pacient = htmlspecialchars(strip_tags($this->id_pacient));
        $this->data_sessio = htmlspecialchars(strip_tags($this->data_sessio));
        $this->hora_inici = htmlspecialchars(strip_tags($this->hora_inici));
        $this->hora_fi = htmlspecialchars(strip_tags($this->hora_fi));
        $this->id_sessio = htmlspecialchars(strip_tags($this->id_sessio));
        
        // Bind dels paràmetres
        $stmt->bindParam(':id_sessio', $this->id_sessio);
        $stmt->bindParam(':id_pacient', $this->id_pacient);
        $stmt->bindParam(':data_sessio', $this->data_sessio);
        $stmt->bindParam(':hora_inici', $this->hora_inici);
        $stmt->bindParam(':hora_fi', $this->hora_fi);
        $stmt->bindParam(':tipus_sessio', $this->tipus_sessio);
        $stmt->bindParam(':estat_sessio', $this->estat_sessio);
        $stmt->bindParam(':ubicacio', $this->ubicacio);
        $stmt->bindParam(':preu_sessio', $this->preu_sessio);
        $stmt->bindParam(':notes_terapeuta', $this->notes_terapeuta);
        
        // Executar
        return $stmt->execute();
    }
    
    /**
     * Eliminar una sessió
     * 
     * Elimina permanentment una sessió de la base de dades.
     * ATENCIÓ: Aquesta acció és irreversible. És millor canviar l'estat a 'Cancel·lada'.
     * 
     * @return bool True si s'ha eliminat correctament, false si hi ha error
     */
    public function eliminar() {
        $query = "DELETE FROM " . $this->table . " WHERE id_sessio = :id_sessio";
        
        $stmt = $this->conn->prepare($query);
        $this->id_sessio = htmlspecialchars(strip_tags($this->id_sessio));
        $stmt->bindParam(':id_sessio', $this->id_sessio);
        
        return $stmt->execute();
    }
    
    // ============================================
    // MÈTODES DE GESTIÓ D'ESTAT
    // ============================================
    
    /**
     * Canviar estat de la sessió
     * 
     * Modifica l'estat de la sessió a un dels estats vàlids.
     * Estats vàlids: 'Programada', 'Realizada', 'Cancelada', 'No asistida'
     * 
     * @param string $nouEstat Nou estat de la sessió
     * @return bool True si s'ha canviat correctament, false si hi ha error
     */
    public function canviarEstat($nouEstat) {
        // Validar que l'estat és vàlid
        $estatsValids = ['Programada', 'Realizada', 'Cancelada', 'No asistida'];
        if (!in_array($nouEstat, $estatsValids)) {
            return false;
        }
        
        $query = "UPDATE " . $this->table . " 
                  SET estat_sessio = :estat 
                  WHERE id_sessio = :id_sessio";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':estat', $nouEstat);
        $stmt->bindParam(':id_sessio', $this->id_sessio);
        
        if ($stmt->execute()) {
            $this->estat_sessio = $nouEstat;
            return true;
        }
        
        return false;
    }
    
    /**
     * Marcar sessió com a realitzada
     * 
     * Canvia l'estat de la sessió a 'Realizada'.
     * Útil per registrar que la sessió s'ha completat amb èxit.
     * 
     * @return bool True si s'ha marcat correctament
     */
    public function marcarRealitzada() {
        return $this->canviarEstat('Realizada');
    }
    
    /**
     * Cancel·lar sessió
     * 
     * Canvia l'estat de la sessió a 'Cancelada'.
     * Útil per registrar cancel·lacions per part del terapeuta o pacient.
     * 
     * @return bool True si s'ha cancel·lat correctament
     */
    public function cancellar() {
        return $this->canviarEstat('Cancelada');
    }
    
    /**
     * Marcar com a no assistida
     * 
     * Canvia l'estat de la sessió a 'No asistida'.
     * Útil per registrar quan el pacient no es presenta a la cita.
     * 
     * @return bool True si s'ha marcat correctament
     */
    public function marcarNoAssistida() {
        return $this->canviarEstat('No asistida');
    }
    
    /**
     * Reprogramar sessió
     * 
     * Torna a marcar la sessió com a 'Programada' després d'haver estat cancel·lada.
     * 
     * @return bool True si s'ha reprogramat correctament
     */
    public function reprogramar() {
        return $this->canviarEstat('Programada');
    }
    
    // ============================================
    // MÈTODES DE CERCA I FILTRATGE
    // ============================================
    
    /**
     * Cercar sessions per data
     * 
     * Obté totes les sessions d'una data concreta.
     * 
     * @param string $data Data a cercar (YYYY-MM-DD)
     * @return PDOStatement Resultat de la consulta
     */
    public function cercarPerData($data) {
        $query = "SELECT s.*, 
                         CONCAT(p.nom, ' ', p.cognoms) as nom_complet_pacient
                  FROM " . $this->table . " s
                  LEFT JOIN pacients p ON s.id_pacient = p.id_pacient
                  WHERE s.data_sessio = :data
                  ORDER BY s.hora_inici ASC";
        
        $stmt = $this->conn->prepare($query);
        $data = htmlspecialchars(strip_tags($data));
        $stmt->bindParam(':data', $data);
        $stmt->execute();
        
        return $stmt;
    }
    
    /**
     * Obtenir sessions d'un pacient
     * 
     * Obté totes les sessions d'un pacient específic.
     * 
     * @param int $id_pacient ID del pacient
     * @param string|null $estat Filtrar per estat (opcional)
     * @return PDOStatement Resultat de la consulta
     */
    public function sessionsPerPacient($id_pacient, $estat = null) {
        $query = "SELECT * FROM " . $this->table . " 
                  WHERE id_pacient = :id_pacient";
        
        if ($estat !== null) {
            $query .= " AND estat_sessio = :estat";
        }
        
        $query .= " ORDER BY data_sessio DESC, hora_inici DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_pacient', $id_pacient);
        
        if ($estat !== null) {
            $stmt->bindParam(':estat', $estat);
        }
        
        $stmt->execute();
        
        return $stmt;
    }
    
    /**
     * Obtenir sessions d'avui
     * 
     * Obté totes les sessions programades per al dia d'avui.
     * 
     * @return PDOStatement Resultat de la consulta
     */
    public function sessionsAvui() {
        $avui = date('Y-m-d');
        return $this->cercarPerData($avui);
    }
    
    /**
     * Obtenir properes sessions
     * 
     * Obté les sessions programades per als propers dies.
     * 
     * @param int $dies Nombre de dies a mirar endavant (per defecte 7)
     * @return PDOStatement Resultat de la consulta
     */
    public function properesSessions($dies = 7) {
        $avui = date('Y-m-d');
        $dataFi = date('Y-m-d', strtotime("+{$dies} days"));
        
        $query = "SELECT s.*, 
                         CONCAT(p.nom, ' ', p.cognoms) as nom_complet_pacient
                  FROM " . $this->table . " s
                  LEFT JOIN pacients p ON s.id_pacient = p.id_pacient
                  WHERE s.data_sessio BETWEEN :data_inici AND :data_fi
                    AND s.estat_sessio = 'Programada'
                  ORDER BY s.data_sessio ASC, s.hora_inici ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':data_inici', $avui);
        $stmt->bindParam(':data_fi', $dataFi);
        $stmt->execute();
        
        return $stmt;
    }
    
    /**
     * Verificar disponibilitat horària
     * 
     * Comprova si hi ha alguna sessió programada en un horari específic.
     * Útil per evitar solapaments de cites.
     * 
     * @param string $data Data a comprovar (YYYY-MM-DD)
     * @param string $hora_inici Hora d'inici (HH:MM:SS)
     * @param string $hora_fi Hora de fi (HH:MM:SS)
     * @param int|null $excloure_id ID de sessió a excloure de la comprovació (per edició)
     * @return bool True si l'horari està disponible, false si està ocupat
     */
    public function verificarDisponibilitat($data, $hora_inici, $hora_fi, $excloure_id = null) {
        $query = "SELECT COUNT(*) as total 
                  FROM " . $this->table . " 
                  WHERE data_sessio = :data
                    AND estat_sessio != 'Cancelada'
                    AND (
                        (hora_inici < :hora_fi AND hora_fi > :hora_inici)
                    )";
        
        if ($excloure_id !== null) {
            $query .= " AND id_sessio != :excloure_id";
        }
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':data', $data);
        $stmt->bindParam(':hora_inici', $hora_inici);
        $stmt->bindParam(':hora_fi', $hora_fi);
        
        if ($excloure_id !== null) {
            $stmt->bindParam(':excloure_id', $excloure_id);
        }
        
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $row['total'] == 0;
    }
    
    // ============================================
    // MÈTODES D'ESTADÍSTIQUES
    // ============================================
    
    /**
     * Comptar sessions
     * 
     * Compta el nombre total de sessions, amb opció de filtrar per estat o pacient.
     * 
     * @param string|null $estat Estat a filtrar (opcional)
     * @param int|null $id_pacient ID del pacient a filtrar (opcional)
     * @return int Nombre de sessions
     */
    public function comptarSessions($estat = null, $id_pacient = null) {
        $query = "SELECT COUNT(*) as total FROM " . $this->table . " WHERE 1=1";
        
        if ($estat !== null) {
            $query .= " AND estat_sessio = :estat";
        }
        
        if ($id_pacient !== null) {
            $query .= " AND id_pacient = :id_pacient";
        }
        
        $stmt = $this->conn->prepare($query);
        
        if ($estat !== null) {
            $stmt->bindParam(':estat', $estat);
        }
        
        if ($id_pacient !== null) {
            $stmt->bindParam(':id_pacient', $id_pacient);
        }
        
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return (int) $row['total'];
    }
    
    /**
     * Obtenir estadístiques generals
     * 
     * Retorna un resum complet amb estadístiques de sessions.
     * 
     * @return array Estadístiques de sessions
     */
    public function obtenirEstadistiques() {
        return [
            'total' => $this->comptarSessions(),
            'programades' => $this->comptarSessions('Programada'),
            'realitzades' => $this->comptarSessions('Realizada'),
            'cancel·lades' => $this->comptarSessions('Cancelada'),
            'no_assistides' => $this->comptarSessions('No asistida')
        ];
    }
    
    /**
     * Calcular ingressos per període
     * 
     * Calcula els ingressos totals de sessions realitzades en un període.
     * 
     * @param string $data_inici Data d'inici (YYYY-MM-DD)
     * @param string $data_fi Data de fi (YYYY-MM-DD)
     * @return float Total d'ingressos
     */
    public function calcularIngressos($data_inici, $data_fi) {
        $query = "SELECT SUM(preu_sessio) as total 
                  FROM " . $this->table . " 
                  WHERE data_sessio BETWEEN :data_inici AND :data_fi
                    AND estat_sessio = 'Realizada'";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':data_inici', $data_inici);
        $stmt->bindParam(':data_fi', $data_fi);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return (float) ($row['total'] ?? 0);
    }
    
    /**
     * Ingressos del mes actual
     * 
     * Calcula els ingressos de sessions realitzades aquest mes.
     * 
     * @return float Total d'ingressos del mes
     */
    public function ingressosAquestMes() {
        $primerDia = date('Y-m-01');
        $ultimDia = date('Y-m-t');
        
        return $this->calcularIngressos($primerDia, $ultimDia);
    }
    
    /**
     * Estadístiques per tipus de sessió
     * 
     * Obté un resum de sessions per cada tipus.
     * 
     * @return array Estadístiques per tipus
     */
    public function estadistiquesPerTipus() {
        $query = "SELECT tipus_sessio, 
                         COUNT(*) as total,
                         SUM(CASE WHEN estat_sessio = 'Realizada' THEN 1 ELSE 0 END) as realitzades,
                         SUM(CASE WHEN estat_sessio = 'Realizada' THEN preu_sessio ELSE 0 END) as ingressos
                  FROM " . $this->table . "
                  GROUP BY tipus_sessio";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Taxa d'assistència
     * 
     * Calcula el percentatge de sessions assistides vs no assistides.
     * 
     * @param string|null $data_inici Data d'inici del període (opcional)
     * @param string|null $data_fi Data de fi del període (opcional)
     * @return array Dades de la taxa d'assistència
     */
    public function taxaAssistencia($data_inici = null, $data_fi = null) {
        $query = "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN estat_sessio = 'Realizada' THEN 1 ELSE 0 END) as assistides,
                    SUM(CASE WHEN estat_sessio = 'No asistida' THEN 1 ELSE 0 END) as no_assistides,
                    SUM(CASE WHEN estat_sessio = 'Cancelada' THEN 1 ELSE 0 END) as cancel·lades
                  FROM " . $this->table . "
                  WHERE estat_sessio != 'Programada'";
        
        if ($data_inici !== null && $data_fi !== null) {
            $query .= " AND data_sessio BETWEEN :data_inici AND :data_fi";
        }
        
        $stmt = $this->conn->prepare($query);
        
        if ($data_inici !== null && $data_fi !== null) {
            $stmt->bindParam(':data_inici', $data_inici);
            $stmt->bindParam(':data_fi', $data_fi);
        }
        
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $total = (int) $row['total'];
        $assistides = (int) $row['assistides'];
        $no_assistides = (int) $row['no_assistides'];
        $cancel·lades = (int) $row['cancel·lades'];
        
        $percentatge = $total > 0 ? round(($assistides / $total) * 100, 2) : 0;
        
        return [
            'total' => $total,
            'assistides' => $assistides,
            'no_assistides' => $no_assistides,
            'cancel·lades' => $cancel·lades,
            'percentatge_assistencia' => $percentatge
        ];
    }
    
    // ============================================
    // MÈTODES DE VALIDACIÓ
    // ============================================
    
    /**
     * Validar dades obligatòries
     * 
     * Comprova que els camps obligatoris estiguin emplenats.
     * 
     * @return bool True si les dades són vàlides, false si no
     */
    private function validarDadesObligatories() {
        if (empty($this->id_pacient) || empty($this->data_sessio) || 
            empty($this->hora_inici) || empty($this->hora_fi) ||
            !isset($this->preu_sessio)) {
            return false;
        }
        return true;
    }
    
    /**
     * Validar format de data
     * 
     * Comprova que la data tingui el format correcte (YYYY-MM-DD).
     * 
     * @param string $data Data a validar
     * @return bool True si la data és vàlida, false si no
     */
    public static function validarData($data) {
        $d = \DateTime::createFromFormat('Y-m-d', $data);
        return $d && $d->format('Y-m-d') === $data;
    }
    
    /**
     * Validar format d'hora
     * 
     * Comprova que l'hora tingui el format correcte (HH:MM o HH:MM:SS).
     * 
     * @param string $hora Hora a validar
     * @return bool True si l'hora és vàlida, false si no
     */
    public static function validarHora($hora) {
        return preg_match('/^([01][0-9]|2[0-3]):[0-5][0-9](:[0-5][0-9])?$/', $hora);
    }
    
    /**
     * Validar rang horari
     * 
     * Comprova que l'hora de fi sigui posterior a l'hora d'inici.
     * 
     * @param string $hora_inici Hora d'inici
     * @param string $hora_fi Hora de fi
     * @return bool True si el rang és vàlid, false si no
     */
    public static function validarRangHorari($hora_inici, $hora_fi) {
        return strtotime($hora_fi) > strtotime($hora_inici);
    }
    
    // ============================================
    // MÈTODES AUXILIARS
    // ============================================
    
    /**
     * Calcular durada de la sessió
     * 
     * Calcula la durada en minuts entre l'hora d'inici i la de fi.
     * 
     * @return int Durada en minuts
     */
    public function calcularDurada() {
        if (empty($this->hora_inici) || empty($this->hora_fi)) {
            return 0;
        }
        
        $inici = strtotime($this->hora_inici);
        $fi = strtotime($this->hora_fi);
        
        return round(($fi - $inici) / 60);
    }
    
    /**
     * Obtenir descripció completa
     * 
     * Genera una descripció de la sessió per mostrar.
     * 
     * @return string Descripció de la sessió
     */
    public function getDescripcio() {
        $durada = $this->calcularDurada();
        return "Sessió {$this->tipus_sessio} - {$durada} minuts - {$this->ubicacio}";
    }
    
    /**
     * És sessió passada
     * 
     * Comprova si la sessió és d'una data passada.
     * 
     * @return bool True si és passada, false si és futura o avui
     */
    public function esPassada() {
        if (empty($this->data_sessio)) {
            return false;
        }
        
        $data_sessio = strtotime($this->data_sessio);
        $avui = strtotime(date('Y-m-d'));
        
        return $data_sessio < $avui;
    }
    
    /**
     * És sessió d'avui
     * 
     * Comprova si la sessió és d'avui.
     * 
     * @return bool True si és avui, false si no
     */
    public function esAvui() {
        return $this->data_sessio === date('Y-m-d');
    }
    
    /**
     * Convertir a array
     * 
     * Converteix les dades de la sessió a un array associatiu.
     * Útil per a exportacions o APIs.
     * 
     * @return array Dades de la sessió
     */
    public function toArray() {
        return [
            'id_sessio' => $this->id_sessio,
            'id_pacient' => $this->id_pacient,
            'data_sessio' => $this->data_sessio,
            'hora_inici' => $this->hora_inici,
            'hora_fi' => $this->hora_fi,
            'durada_minuts' => $this->calcularDurada(),
            'tipus_sessio' => $this->tipus_sessio,
            'estat_sessio' => $this->estat_sessio,
            'ubicacio' => $this->ubicacio,
            'preu_sessio' => $this->preu_sessio,
            'notes_terapeuta' => $this->notes_terapeuta,
            'data_creacio' => $this->data_creacio,
            'descripcio' => $this->getDescripcio(),
            'es_passada' => $this->esPassada(),
            'es_avui' => $this->esAvui()
        ];
    }
    
    /**
     * Convertir a JSON
     * 
     * Converteix les dades de la sessió a format JSON.
     * 
     * @return string JSON amb les dades de la sessió
     */
    public function toJSON() {
        return json_encode($this->toArray(), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }
}
