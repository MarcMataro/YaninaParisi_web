<?php
/**
 * Classe ProfessionalEspecialitat
 *
 * Gestiona la relació molts a molts entre professionals i especialitats utilitzant el patró Singleton.
 * Implementa operacions per assignar, eliminar i consultar les relacions.
 *
 * Estructura esperada de la taula `professional_especialitat`:
 * - professional_id INT UNSIGNED NOT NULL
 * - especialitat_id INT UNSIGNED NOT NULL
 * - PRIMARY KEY (professional_id, especialitat_id)
 * - FOREIGN KEY professional_id -> professionals(id) ON DELETE CASCADE
 * - FOREIGN KEY especialitat_id -> especialitats(id) ON DELETE CASCADE
 *
 * @author Marc Mataró
 * @version 1.0.0
 * @date 2026-01-01
 */

class ProfessionalEspecialitat {

    /** @var ProfessionalEspecialitat|null Instància Singleton */
    private static $instancia = null;

    /** @var PDO Instància de connexió a la base de dades */
    private $conn;

    /** @var string Nom de la taula */
    private $table = 'professional_especialitat';

    /* ======== Propietats privades del model ======== */
    private $professional_id;
    private $especialitat_id;

    /* ===================== Constructor privat (Singleton) ===================== */
    
    /**
     * Constructor privat per implementar el patró Singleton
     *
     * @throws Exception Si no es pot obtenir la connexió
     */
    private function __construct() {
        try {
            $this->conn = Connexio::getInstance()->getConnexio();
        } catch (Exception $e) {
            throw new Exception("Error al connectar amb la base de dades: " . $e->getMessage());
        }
    }

    /**
     * Evitar la clonació de la instància
     */
    private function __clone() {}

    /**
     * Evitar la deserialització de la instància
     */
    public function __wakeup() {
        throw new Exception("No es pot deserialitzar un Singleton");
    }

    /* ===================== Singleton ===================== */
    
    /**
     * Obtenir la instància única de la classe (Singleton)
     *
     * @return ProfessionalEspecialitat Instància única
     */
    public static function getInstance(): ProfessionalEspecialitat {
        if (self::$instancia === null) {
            self::$instancia = new self();
        }
        return self::$instancia;
    }

    /* ===================== Getters ===================== */
    
    /**
     * @return int|null
     */
    public function getProfessionalId(): ?int {
        return $this->professional_id;
    }

    /**
     * @return int|null
     */
    public function getEspecialitat_id(): ?int {
        return $this->especialitat_id;
    }

    /* ===================== Setters ===================== */
    
    /**
     * @param int $professional_id
     */
    public function setProfessionalId(int $professional_id): void {
        $this->professional_id = $professional_id;
    }

    /**
     * @param int $especialitat_id
     */
    public function setEspecialitatId(int $especialitat_id): void {
        $this->especialitat_id = $especialitat_id;
    }

    /* ===================== Helpers / Utilitats ===================== */

    /**
     * Neteja les propietats de l'objecte
     */
    private function netejarPropietats(): void {
        $this->professional_id = null;
        $this->especialitat_id = null;
    }

    /* ===================== Validació ===================== */
    
    /**
     * Valida les dades abans d'inserir
     * Retorna array amb errors (buit si no hi ha errors)
     *
     * @return array
     */
    public function validar(): array {
        $errors = [];

        // Validar professional_id
        if (empty($this->professional_id)) {
            $errors[] = 'El ID del professional és obligatori';
        } elseif ($this->professional_id <= 0) {
            $errors[] = 'El ID del professional ha de ser un valor positiu';
        }

        // Validar especialitat_id
        if (empty($this->especialitat_id)) {
            $errors[] = 'El ID de l\'especialitat és obligatori';
        } elseif ($this->especialitat_id <= 0) {
            $errors[] = 'El ID de l\'especialitat ha de ser un valor positiu';
        }

        return $errors;
    }

    /* ===================== Operacions de relació ===================== */
    
    /**
     * Assigna una especialitat a un professional
     *
     * @param int $professional_id
     * @param int $especialitat_id
     * @return bool True si s'ha assignat correctament
     * @throws Exception Si hi ha errors de validació o de base de dades
     */
    public function assignar(int $professional_id, int $especialitat_id): bool {
        $this->professional_id = $professional_id;
        $this->especialitat_id = $especialitat_id;

        // Validar dades
        $errors = $this->validar();
        if (!empty($errors)) {
            throw new Exception('Errors de validació: ' . implode(', ', $errors));
        }

        // Verificar que no existeixi ja la relació
        if ($this->existeixRelacio($professional_id, $especialitat_id)) {
            return true; // Ja existeix, no fem res
        }

        $sql = "INSERT INTO {$this->table} (professional_id, especialitat_id) 
                VALUES (:professional_id, :especialitat_id)";

        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':professional_id', $professional_id, PDO::PARAM_INT);
            $stmt->bindValue(':especialitat_id', $especialitat_id, PDO::PARAM_INT);

            return $stmt->execute();
        } catch (PDOException $e) {
            throw new Exception("Error en assignar especialitat: " . $e->getMessage());
        }
    }

    /**
     * Elimina una especialitat d'un professional
     *
     * @param int $professional_id
     * @param int $especialitat_id
     * @return bool True si s'ha eliminat correctament
     * @throws Exception Si hi ha errors de base de dades
     */
    public function eliminar(int $professional_id, int $especialitat_id): bool {
        $sql = "DELETE FROM {$this->table} 
                WHERE professional_id = :professional_id 
                AND especialitat_id = :especialitat_id";

        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':professional_id', $professional_id, PDO::PARAM_INT);
            $stmt->bindValue(':especialitat_id', $especialitat_id, PDO::PARAM_INT);

            return $stmt->execute();
        } catch (PDOException $e) {
            throw new Exception("Error en eliminar especialitat: " . $e->getMessage());
        }
    }

    /**
     * Elimina totes les especialitats d'un professional
     *
     * @param int $professional_id
     * @return bool True si s'ha eliminat correctament
     * @throws Exception Si hi ha errors de base de dades
     */
    public function eliminarTotesProfessional(int $professional_id): bool {
        $sql = "DELETE FROM {$this->table} WHERE professional_id = :professional_id";

        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':professional_id', $professional_id, PDO::PARAM_INT);

            return $stmt->execute();
        } catch (PDOException $e) {
            throw new Exception("Error en eliminar especialitats del professional: " . $e->getMessage());
        }
    }

    /**
     * Elimina tots els professionals d'una especialitat
     *
     * @param int $especialitat_id
     * @return bool True si s'ha eliminat correctament
     * @throws Exception Si hi ha errors de base de dades
     */
    public function eliminarTotsEspecialitat(int $especialitat_id): bool {
        $sql = "DELETE FROM {$this->table} WHERE especialitat_id = :especialitat_id";

        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':especialitat_id', $especialitat_id, PDO::PARAM_INT);

            return $stmt->execute();
        } catch (PDOException $e) {
            throw new Exception("Error en eliminar professionals de l'especialitat: " . $e->getMessage());
        }
    }

    /* ===================== Consultes ===================== */
    
    /**
     * Comprova si existeix la relació entre un professional i una especialitat
     *
     * @param int $professional_id
     * @param int $especialitat_id
     * @return bool True si existeix la relació
     * @throws Exception Si hi ha errors de base de dades
     */
    public function existeixRelacio(int $professional_id, int $especialitat_id): bool {
        $sql = "SELECT COUNT(*) as total FROM {$this->table} 
                WHERE professional_id = :professional_id 
                AND especialitat_id = :especialitat_id";

        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':professional_id', $professional_id, PDO::PARAM_INT);
            $stmt->bindValue(':especialitat_id', $especialitat_id, PDO::PARAM_INT);
            $stmt->execute();

            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return (int)$result['total'] > 0;
        } catch (PDOException $e) {
            throw new Exception("Error en verificar relació: " . $e->getMessage());
        }
    }

    /**
     * Obté totes les especialitats d'un professional
     *
     * @param int $professional_id
     * @return array Array d'especialitats amb totes les seves dades
     * @throws Exception Si hi ha errors de base de dades
     */
    public function obtenirEspecialitatsProfessional(int $professional_id): array {
        $sql = "SELECT e.* 
                FROM especialitats e
                INNER JOIN {$this->table} pe ON e.id = pe.especialitat_id
                WHERE pe.professional_id = :professional_id
                ORDER BY e.nom ASC";

        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':professional_id', $professional_id, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Error en obtenir especialitats del professional: " . $e->getMessage());
        }
    }

    /**
     * Obté només els IDs de les especialitats d'un professional
     *
     * @param int $professional_id
     * @return array Array d'IDs
     * @throws Exception Si hi ha errors de base de dades
     */
    public function obtenirIdsEspecialitatsProfessional(int $professional_id): array {
        $sql = "SELECT especialitat_id 
                FROM {$this->table} 
                WHERE professional_id = :professional_id
                ORDER BY especialitat_id ASC";

        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':professional_id', $professional_id, PDO::PARAM_INT);
            $stmt->execute();

            return array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'especialitat_id');
        } catch (PDOException $e) {
            throw new Exception("Error en obtenir IDs d'especialitats: " . $e->getMessage());
        }
    }

    /**
     * Obté tots els professionals d'una especialitat
     *
     * @param int $especialitat_id
     * @param bool $nomes_actius Si true, només retorna professionals actius
     * @return array Array de professionals amb totes les seves dades
     * @throws Exception Si hi ha errors de base de dades
     */
    public function obtenirProfessionalsEspecialitat(int $especialitat_id, bool $nomes_actius = false): array {
        $sql = "SELECT p.* 
                FROM professionals p
                INNER JOIN {$this->table} pe ON p.id = pe.professional_id
                WHERE pe.especialitat_id = :especialitat_id";

        if ($nomes_actius) {
            $sql .= " AND p.actiu = 1";
        }

        $sql .= " ORDER BY p.cognoms ASC, p.nom ASC";

        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':especialitat_id', $especialitat_id, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Error en obtenir professionals de l'especialitat: " . $e->getMessage());
        }
    }

    /**
     * Obté només els IDs dels professionals d'una especialitat
     *
     * @param int $especialitat_id
     * @return array Array d'IDs
     * @throws Exception Si hi ha errors de base de dades
     */
    public function obtenirIdsProfessionalsEspecialitat(int $especialitat_id): array {
        $sql = "SELECT professional_id 
                FROM {$this->table} 
                WHERE especialitat_id = :especialitat_id
                ORDER BY professional_id ASC";

        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':especialitat_id', $especialitat_id, PDO::PARAM_INT);
            $stmt->execute();

            return array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'professional_id');
        } catch (PDOException $e) {
            throw new Exception("Error en obtenir IDs de professionals: " . $e->getMessage());
        }
    }

    /**
     * Compte quantes especialitats té un professional
     *
     * @param int $professional_id
     * @return int Nombre d'especialitats
     * @throws Exception Si hi ha errors de base de dades
     */
    public function comptarEspecialitatsProfessional(int $professional_id): int {
        $sql = "SELECT COUNT(*) as total 
                FROM {$this->table} 
                WHERE professional_id = :professional_id";

        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':professional_id', $professional_id, PDO::PARAM_INT);
            $stmt->execute();

            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return (int)$result['total'];
        } catch (PDOException $e) {
            throw new Exception("Error en comptar especialitats: " . $e->getMessage());
        }
    }

    /**
     * Compte quantos professionals tenen una especialitat
     *
     * @param int $especialitat_id
     * @return int Nombre de professionals
     * @throws Exception Si hi ha errors de base de dades
     */
    public function comptarProfessionalsEspecialitat(int $especialitat_id): int {
        $sql = "SELECT COUNT(*) as total 
                FROM {$this->table} 
                WHERE especialitat_id = :especialitat_id";

        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':especialitat_id', $especialitat_id, PDO::PARAM_INT);
            $stmt->execute();

            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return (int)$result['total'];
        } catch (PDOException $e) {
            throw new Exception("Error en comptar professionals: " . $e->getMessage());
        }
    }

    /* ===================== Operacions en bloc ===================== */
    
    /**
     * Sincronitza les especialitats d'un professional
     * Elimina les existents i assigna les noves
     *
     * @param int $professional_id
     * @param array $especialitat_ids Array d'IDs d'especialitats
     * @return bool True si s'ha sincronitzat correctament
     * @throws Exception Si hi ha errors de base de dades
     */
    public function sincronitzarEspecialitats(int $professional_id, array $especialitat_ids): bool {
        try {
            // Iniciar transacció
            $this->conn->beginTransaction();

            // Eliminar totes les especialitats actuals
            $this->eliminarTotesProfessional($professional_id);

            // Assignar les noves especialitats
            foreach ($especialitat_ids as $especialitat_id) {
                if (!empty($especialitat_id)) {
                    $this->assignar($professional_id, (int)$especialitat_id);
                }
            }

            // Confirmar transacció
            $this->conn->commit();
            return true;

        } catch (Exception $e) {
            // Revertir transacció en cas d'error
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            throw new Exception("Error en sincronitzar especialitats: " . $e->getMessage());
        }
    }

    /**
     * Assigna múltiples especialitats a un professional
     * No elimina les especialitats existents
     *
     * @param int $professional_id
     * @param array $especialitat_ids Array d'IDs d'especialitats
     * @return bool True si s'ha assignat correctament
     * @throws Exception Si hi ha errors de base de dades
     */
    public function assignarMultiples(int $professional_id, array $especialitat_ids): bool {
        try {
            // Iniciar transacció
            $this->conn->beginTransaction();

            // Assignar cada especialitat
            foreach ($especialitat_ids as $especialitat_id) {
                if (!empty($especialitat_id)) {
                    $this->assignar($professional_id, (int)$especialitat_id);
                }
            }

            // Confirmar transacció
            $this->conn->commit();
            return true;

        } catch (Exception $e) {
            // Revertir transacció en cas d'error
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            throw new Exception("Error en assignar múltiples especialitats: " . $e->getMessage());
        }
    }

    /* ===================== Estadístiques ===================== */
    
    /**
     * Obté les especialitats més populars
     *
     * @param int $limit Nombre d'especialitats a retornar
     * @return array Array amb especialitats i compte de professionals
     * @throws Exception Si hi ha errors de base de dades
     */
    public function obtenirEspecialitatsPopulars(int $limit = 10): array {
        $sql = "SELECT e.*, COUNT(pe.professional_id) as num_professionals
                FROM especialitats e
                LEFT JOIN {$this->table} pe ON e.id = pe.especialitat_id
                GROUP BY e.id
                ORDER BY num_professionals DESC, e.nom ASC
                LIMIT :limit";

        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Error en obtenir especialitats populars: " . $e->getMessage());
        }
    }

    /**
     * Obté professionals amb múltiples especialitats
     *
     * @param int $min_especialitats Mínim d'especialitats
     * @return array Array de professionals amb compte d'especialitats
     * @throws Exception Si hi ha errors de base de dades
     */
    public function obtenirProfessionalsMultiespecialitat(int $min_especialitats = 2): array {
        $sql = "SELECT p.*, COUNT(pe.especialitat_id) as num_especialitats
                FROM professionals p
                INNER JOIN {$this->table} pe ON p.id = pe.professional_id
                WHERE p.actiu = 1
                GROUP BY p.id
                HAVING num_especialitats >= :min
                ORDER BY num_especialitats DESC, p.cognoms ASC";

        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':min', $min_especialitats, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Error en obtenir professionals multiespecialitat: " . $e->getMessage());
        }
    }
}
