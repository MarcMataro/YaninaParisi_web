<?php
/**
 * Classe ProfessionalCertificacions
 *
 * Gestiona les certificacions i màsters dels professionals utilitzant el patró Singleton.
 * Implementa operacions CRUD, validació i gestió de certificacions multiidioma.
 *
 * Estructura esperada de la taula `professional_certificacions`:
 * - id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT
 * - professional_id INT UNSIGNED NOT NULL
 * - certificacions_ca TEXT NOT NULL
 * - certificacions_es TEXT NOT NULL
 * - created_at TIMESTAMP
 * - updated_at TIMESTAMP
 *
 * @author Marc Mataró
 * @version 1.0.0
 * @date 2026-01-01
 */

class ProfessionalCertificacions {

    /** @var ProfessionalCertificacions|null Instància Singleton */
    private static $instancia = null;

    /** @var PDO Instància de connexió a la base de dades */
    private $conn;

    /** @var string Nom de la taula */
    private $table = 'professional_certificacions';

    /* ======== Propietats privades del model ======== */
    private $id;
    private $professional_id;
    private $certificacions_ca;
    private $certificacions_es;
    private $created_at;
    private $updated_at;

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
     * @return ProfessionalCertificacions Instància única
     */
    public static function getInstance(): ProfessionalCertificacions {
        if (self::$instancia === null) {
            self::$instancia = new self();
        }
        return self::$instancia;
    }

    /* ===================== Getters ===================== */
    
    /**
     * @return int|null
     */
    public function getId(): ?int {
        return $this->id;
    }

    /**
     * @return int|null
     */
    public function getProfessionalId(): ?int {
        return $this->professional_id;
    }

    /**
     * @return string|null
     */
    public function getCertificacionsCa(): ?string {
        return $this->certificacions_ca;
    }

    /**
     * @return string|null
     */
    public function getCertificacionsEs(): ?string {
        return $this->certificacions_es;
    }

    /**
     * @return string|null
     */
    public function getCreatedAt(): ?string {
        return $this->created_at;
    }

    /**
     * @return string|null
     */
    public function getUpdatedAt(): ?string {
        return $this->updated_at;
    }

    /* ===================== Setters ===================== */
    
    /**
     * @param int $id
     */
    public function setId(int $id): void {
        $this->id = $id;
    }

    /**
     * @param int $professional_id
     */
    public function setProfessionalId(int $professional_id): void {
        $this->professional_id = $professional_id;
    }

    /**
     * @param string $certificacions_ca
     */
    public function setCertificacionsCa(string $certificacions_ca): void {
        $this->certificacions_ca = $certificacions_ca;
    }

    /**
     * @param string $certificacions_es
     */
    public function setCertificacionsEs(string $certificacions_es): void {
        $this->certificacions_es = $certificacions_es;
    }

    /* ===================== Helpers / Utilitats ===================== */

    /**
     * Sanititza una cadena de text per prevenir XSS
     *
     * @param string|null $text
     * @return string|null
     */
    private function sanitize(?string $text): ?string {
        return $text !== null ? htmlspecialchars(trim($text), ENT_QUOTES, 'UTF-8') : null;
    }

    /**
     * Carrega les dades d'un array associatiu a les propietats de l'objecte
     *
     * @param array $data
     */
    private function carregarDades(array $data): void {
        $this->id = $data['id'] ?? null;
        $this->professional_id = $data['professional_id'] ?? null;
        $this->certificacions_ca = $data['certificacions_ca'] ?? null;
        $this->certificacions_es = $data['certificacions_es'] ?? null;
        $this->created_at = $data['created_at'] ?? null;
        $this->updated_at = $data['updated_at'] ?? null;
    }

    /**
     * Neteja les propietats de l'objecte
     */
    private function netejarPropietats(): void {
        $this->id = null;
        $this->professional_id = null;
        $this->certificacions_ca = null;
        $this->certificacions_es = null;
        $this->created_at = null;
        $this->updated_at = null;
    }

    /* ===================== Validació ===================== */
    
    /**
     * Valida les dades abans d'inserir o actualitzar
     * Retorna array amb errors (buit si no hi ha errors)
     *
     * @return array
     */
    public function validar(): array {
        $errors = [];

        // Validar professional_id
        if (empty($this->professional_id)) {
            $errors[] = 'El professional_id és obligatori';
        }

        // Validar certificacions_ca
        if (empty($this->certificacions_ca)) {
            $errors[] = 'Les certificacions en català són obligatòries';
        }

        // Validar certificacions_es
        if (empty($this->certificacions_es)) {
            $errors[] = 'Les certificacions en espanyol són obligatòries';
        }

        return $errors;
    }

    /* ===================== CRUD - Crear ===================== */
    
    /**
     * Crea un nou registre de certificacions a la base de dades
     *
     * @return int|bool ID del nou registre o false si hi ha error
     * @throws Exception Si hi ha errors de validació o de base de dades
     */
    public function crear() {
        // Validar dades
        $errors = $this->validar();
        if (!empty($errors)) {
            throw new Exception('Errors de validació: ' . implode(', ', $errors));
        }

        $sql = "INSERT INTO {$this->table} (professional_id, certificacions_ca, certificacions_es) 
                VALUES (:professional_id, :certificacions_ca, :certificacions_es)";

        try {
            $stmt = $this->conn->prepare($sql);
            
            $stmt->bindValue(':professional_id', $this->professional_id, PDO::PARAM_INT);
            $stmt->bindValue(':certificacions_ca', $this->certificacions_ca);
            $stmt->bindValue(':certificacions_es', $this->certificacions_es);

            if ($stmt->execute()) {
                $this->id = (int)$this->conn->lastInsertId();
                return $this->id;
            }
            
            return false;
        } catch (PDOException $e) {
            throw new Exception("Error en crear les certificacions: " . $e->getMessage());
        }
    }

    /* ===================== CRUD - Llegir ===================== */
    
    /**
     * Cerca un registre de certificacions per ID i carrega les seves dades
     *
     * @param int $id
     * @return bool True si s'ha trobat, false si no
     * @throws Exception Si hi ha errors de base de dades
     */
    public function llegirPerId(int $id): bool {
        $sql = "SELECT * FROM {$this->table} WHERE id = :id LIMIT 1";

        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($row) {
                $this->carregarDades($row);
                return true;
            }

            $this->netejarPropietats();
            return false;
        } catch (PDOException $e) {
            throw new Exception("Error en llegir les certificacions: " . $e->getMessage());
        }
    }

    /**
     * Cerca certificacions per professional_id i carrega les seves dades
     *
     * @param int $professional_id
     * @return bool True si s'ha trobat, false si no
     * @throws Exception Si hi ha errors de base de dades
     */
    public function llegirPerProfessional(int $professional_id): bool {
        $sql = "SELECT * FROM {$this->table} WHERE professional_id = :professional_id LIMIT 1";

        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':professional_id', $professional_id, PDO::PARAM_INT);
            $stmt->execute();

            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($row) {
                $this->carregarDades($row);
                return true;
            }

            $this->netejarPropietats();
            return false;
        } catch (PDOException $e) {
            throw new Exception("Error en cercar certificacions per professional: " . $e->getMessage());
        }
    }

    /**
     * Obté les certificacions d'un professional per ID sense modificar l'estat de l'objecte
     *
     * @param int $professional_id
     * @return array|null Array associatiu amb les dades o null
     * @throws Exception Si hi ha errors de base de dades
     */
    public function obtenirPerProfessional(int $professional_id): ?array {
        $sql = "SELECT * FROM {$this->table} WHERE professional_id = :professional_id LIMIT 1";

        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':professional_id', $professional_id, PDO::PARAM_INT);
            $stmt->execute();

            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row ?: null;
        } catch (PDOException $e) {
            throw new Exception("Error en obtenir certificacions: " . $e->getMessage());
        }
    }

    /* ===================== CRUD - Actualitzar ===================== */
    
    /**
     * Actualitza les certificacions a la base de dades
     *
     * @return bool True si s'ha actualitzat correctament
     * @throws Exception Si hi ha errors de validació o de base de dades
     */
    public function actualitzar(): bool {
        // Validar que tenim un ID
        if (empty($this->id)) {
            throw new Exception('No es pot actualitzar sense un ID vàlid');
        }

        // Validar dades
        $errors = $this->validar();
        if (!empty($errors)) {
            throw new Exception('Errors de validació: ' . implode(', ', $errors));
        }

        $sql = "UPDATE {$this->table} SET
                    professional_id = :professional_id,
                    certificacions_ca = :certificacions_ca,
                    certificacions_es = :certificacions_es
                WHERE id = :id";

        try {
            $stmt = $this->conn->prepare($sql);
            
            $stmt->bindValue(':professional_id', $this->professional_id, PDO::PARAM_INT);
            $stmt->bindValue(':certificacions_ca', $this->certificacions_ca);
            $stmt->bindValue(':certificacions_es', $this->certificacions_es);
            $stmt->bindValue(':id', $this->id, PDO::PARAM_INT);

            return $stmt->execute();
        } catch (PDOException $e) {
            throw new Exception("Error en actualitzar les certificacions: " . $e->getMessage());
        }
    }

    /* ===================== CRUD - Eliminar ===================== */
    
    /**
     * Elimina un registre de certificacions de la base de dades
     *
     * @param int $id
     * @return bool True si s'ha eliminat correctament
     * @throws Exception Si hi ha errors de base de dades
     */
    public function eliminar(int $id): bool {
        $sql = "DELETE FROM {$this->table} WHERE id = :id";

        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            
            if ($stmt->execute()) {
                // Si s'elimina el propi objecte, netejar les propietats
                if ($this->id === $id) {
                    $this->netejarPropietats();
                }
                return true;
            }
            
            return false;
        } catch (PDOException $e) {
            throw new Exception("Error en eliminar les certificacions: " . $e->getMessage());
        }
    }

    /**
     * Elimina les certificacions d'un professional específic
     *
     * @param int $professional_id
     * @return bool True si s'ha eliminat correctament
     * @throws Exception Si hi ha errors de base de dades
     */
    public function eliminarPerProfessional(int $professional_id): bool {
        $sql = "DELETE FROM {$this->table} WHERE professional_id = :professional_id";

        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':professional_id', $professional_id, PDO::PARAM_INT);
            
            if ($stmt->execute()) {
                // Si s'elimina el propi objecte, netejar les propietats
                if ($this->professional_id === $professional_id) {
                    $this->netejarPropietats();
                }
                return true;
            }
            
            return false;
        } catch (PDOException $e) {
            throw new Exception("Error en eliminar les certificacions del professional: " . $e->getMessage());
        }
    }

    /* ===================== Altres mètodes útils ===================== */
    
    /**
     * Comprova si un professional té certificacions
     *
     * @param int $professional_id
     * @return bool True si té certificacions
     * @throws Exception Si hi ha errors de base de dades
     */
    public function teCertificacions(int $professional_id): bool {
        $sql = "SELECT COUNT(*) as total FROM {$this->table} WHERE professional_id = :professional_id";

        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':professional_id', $professional_id, PDO::PARAM_INT);
            $stmt->execute();
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return (int)$result['total'] > 0;
        } catch (PDOException $e) {
            throw new Exception("Error en comprovar certificacions: " . $e->getMessage());
        }
    }

    /**
     * Crea o actualitza les certificacions d'un professional (upsert)
     *
     * @param int $professional_id
     * @param string $certificacions_ca
     * @param string $certificacions_es
     * @return int|bool ID del registre creat/actualitzat o false si hi ha error
     * @throws Exception Si hi ha errors
     */
    public function guardarCertificacions(int $professional_id, string $certificacions_ca, string $certificacions_es) {
        // Comprovar si ja existeix un registre
        if ($this->llegirPerProfessional($professional_id)) {
            // Actualitzar
            $this->setCertificacionsCa($certificacions_ca);
            $this->setCertificacionsEs($certificacions_es);
            
            if ($this->actualitzar()) {
                return $this->id;
            }
            return false;
        } else {
            // Crear nou
            $this->setProfessionalId($professional_id);
            $this->setCertificacionsCa($certificacions_ca);
            $this->setCertificacionsEs($certificacions_es);
            
            return $this->crear();
        }
    }
}
