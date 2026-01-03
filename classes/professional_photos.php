<?php
/**
 * Classe ProfessionalPhotos
 *
 * Gestiona les fotos dels professionals utilitzant el patró Singleton.
 * Implementa operacions CRUD, validació i cerca per professional_id.
 *
 * Estructura esperada de la taula `professional_photos`:
 * - id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT
 * - professional_id INT UNSIGNED NOT NULL
 * - image_path VARCHAR(255) NOT NULL
 * - title_ca VARCHAR(150) NOT NULL
 * - title_es VARCHAR(150) NOT NULL
 * - description_ca TEXT NULL
 * - description_es TEXT NULL
 * - alt_ca VARCHAR(255) NOT NULL
 * - alt_es VARCHAR(255) NOT NULL
 * - created_at TIMESTAMP
 * - updated_at TIMESTAMP
 *
 * @author Marc Mataró
 * @version 1.0.0
 * @date 2026-01-03
 */

class ProfessionalPhotos {

    /** @var ProfessionalPhotos|null Instància Singleton */
    private static $instancia = null;

    /** @var PDO Instància de connexió a la base de dades */
    private $conn;

    /** @var string Nom de la taula */
    private $table = 'professional_photos';

    /* ======== Propietats privades del model ======== */
    private $id;
    private $professional_id;
    private $image_path;
    private $title_ca;
    private $title_es;
    private $description_ca;
    private $description_es;
    private $alt_ca;
    private $alt_es;
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
     * @return ProfessionalPhotos Instància única
     */
    public static function getInstance(): ProfessionalPhotos {
        if (self::$instancia === null) {
            self::$instancia = new self();
        }
        return self::$instancia;
    }

    /* ===================== Getters ===================== */
    
    public function getId(): ?int {
        return $this->id;
    }

    public function getProfessionalId(): ?int {
        return $this->professional_id;
    }

    public function getImagePath(): ?string {
        return $this->image_path;
    }

    public function getTitleCa(): ?string {
        return $this->title_ca;
    }

    public function getTitleEs(): ?string {
        return $this->title_es;
    }

    public function getDescriptionCa(): ?string {
        return $this->description_ca;
    }

    public function getDescriptionEs(): ?string {
        return $this->description_es;
    }

    public function getAltCa(): ?string {
        return $this->alt_ca;
    }

    public function getAltEs(): ?string {
        return $this->alt_es;
    }

    public function getCreatedAt(): ?string {
        return $this->created_at;
    }

    public function getUpdatedAt(): ?string {
        return $this->updated_at;
    }

    /* ===================== Setters ===================== */
    
    public function setId(int $id): void {
        $this->id = $id;
    }

    public function setProfessionalId(int $professional_id): void {
        $this->professional_id = $professional_id;
    }

    public function setImagePath(string $image_path): void {
        $this->image_path = trim($image_path);
    }

    public function setTitleCa(string $title_ca): void {
        $this->title_ca = trim($title_ca);
    }

    public function setTitleEs(string $title_es): void {
        $this->title_es = trim($title_es);
    }

    public function setDescriptionCa(?string $description_ca): void {
        $this->description_ca = $description_ca ? trim($description_ca) : null;
    }

    public function setDescriptionEs(?string $description_es): void {
        $this->description_es = $description_es ? trim($description_es) : null;
    }

    public function setAltCa(string $alt_ca): void {
        $this->alt_ca = trim($alt_ca);
    }

    public function setAltEs(string $alt_es): void {
        $this->alt_es = trim($alt_es);
    }

    /* ===================== Validacions ===================== */
    
    /**
     * Valida les dades abans de guardar
     *
     * @return array Array d'errors (buit si tot és correcte)
     */
    private function validar(): array {
        $errors = [];

        if (empty($this->professional_id)) {
            $errors[] = "L'ID del professional és obligatori";
        }

        if (empty($this->image_path)) {
            $errors[] = "La ruta de la imatge és obligatòria";
        } elseif (strlen($this->image_path) > 255) {
            $errors[] = "La ruta de la imatge no pot superar els 255 caràcters";
        }

        if (empty($this->title_ca)) {
            $errors[] = "El títol en català és obligatori";
        } elseif (strlen($this->title_ca) > 150) {
            $errors[] = "El títol en català no pot superar els 150 caràcters";
        }

        if (empty($this->title_es)) {
            $errors[] = "El títol en espanyol és obligatori";
        } elseif (strlen($this->title_es) > 150) {
            $errors[] = "El títol en espanyol no pot superar els 150 caràcters";
        }

        if (empty($this->alt_ca)) {
            $errors[] = "El text alternatiu en català és obligatori";
        } elseif (strlen($this->alt_ca) > 255) {
            $errors[] = "El text alternatiu en català no pot superar els 255 caràcters";
        }

        if (empty($this->alt_es)) {
            $errors[] = "El text alternatiu en espanyol és obligatori";
        } elseif (strlen($this->alt_es) > 255) {
            $errors[] = "El text alternatiu en espanyol no pot superar els 255 caràcters";
        }

        return $errors;
    }

    /* ===================== CRUD ===================== */
    
    /**
     * Crear una nova foto
     *
     * @return int ID de la foto creada
     * @throws Exception Si hi ha errors de validació o de base de dades
     */
    public function crear(): int {
        $errors = $this->validar();
        if (!empty($errors)) {
            throw new Exception("Errors de validació: " . implode(", ", $errors));
        }

        $sql = "INSERT INTO {$this->table} 
                (professional_id, image_path, title_ca, title_es, description_ca, description_es, alt_ca, alt_es) 
                VALUES 
                (:professional_id, :image_path, :title_ca, :title_es, :description_ca, :description_es, :alt_ca, :alt_es)";

        try {
            $stmt = $this->conn->prepare($sql);
            
            $stmt->bindValue(':professional_id', $this->professional_id, PDO::PARAM_INT);
            $stmt->bindValue(':image_path', $this->image_path);
            $stmt->bindValue(':title_ca', $this->title_ca);
            $stmt->bindValue(':title_es', $this->title_es);
            $stmt->bindValue(':description_ca', $this->description_ca);
            $stmt->bindValue(':description_es', $this->description_es);
            $stmt->bindValue(':alt_ca', $this->alt_ca);
            $stmt->bindValue(':alt_es', $this->alt_es);
            
            $stmt->execute();
            $this->id = (int)$this->conn->lastInsertId();
            
            return $this->id;
        } catch (PDOException $e) {
            throw new Exception("Error en crear la foto: " . $e->getMessage());
        }
    }

    /**
     * Llegir una foto per ID
     *
     * @param int $id ID de la foto
     * @return array|null Dades de la foto o null si no existeix
     * @throws Exception Si hi ha errors de base de dades
     */
    public function llegir(int $id): ?array {
        $sql = "SELECT * FROM {$this->table} WHERE id = :id";

        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ?: null;
        } catch (PDOException $e) {
            throw new Exception("Error en llegir la foto: " . $e->getMessage());
        }
    }

    /**
     * Actualitzar una foto existent
     *
     * @return bool True si s'ha actualitzat correctament
     * @throws Exception Si hi ha errors de validació o de base de dades
     */
    public function actualitzar(): bool {
        if (empty($this->id)) {
            throw new Exception("ID no especificat per actualitzar");
        }

        $errors = $this->validar();
        if (!empty($errors)) {
            throw new Exception("Errors de validació: " . implode(", ", $errors));
        }

        $sql = "UPDATE {$this->table} 
                SET professional_id = :professional_id,
                    image_path = :image_path,
                    title_ca = :title_ca,
                    title_es = :title_es,
                    description_ca = :description_ca,
                    description_es = :description_es,
                    alt_ca = :alt_ca,
                    alt_es = :alt_es
                WHERE id = :id";

        try {
            $stmt = $this->conn->prepare($sql);
            
            $stmt->bindValue(':id', $this->id, PDO::PARAM_INT);
            $stmt->bindValue(':professional_id', $this->professional_id, PDO::PARAM_INT);
            $stmt->bindValue(':image_path', $this->image_path);
            $stmt->bindValue(':title_ca', $this->title_ca);
            $stmt->bindValue(':title_es', $this->title_es);
            $stmt->bindValue(':description_ca', $this->description_ca);
            $stmt->bindValue(':description_es', $this->description_es);
            $stmt->bindValue(':alt_ca', $this->alt_ca);
            $stmt->bindValue(':alt_es', $this->alt_es);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            throw new Exception("Error en actualitzar la foto: " . $e->getMessage());
        }
    }

    /**
     * Eliminar una foto per ID
     *
     * @param int $id ID de la foto
     * @return bool True si s'ha eliminat correctament
     * @throws Exception Si hi ha errors de base de dades
     */
    public function eliminar(int $id): bool {
        $sql = "DELETE FROM {$this->table} WHERE id = :id";

        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            throw new Exception("Error en eliminar la foto: " . $e->getMessage());
        }
    }

    /* ===================== Consultes específiques ===================== */
    
    /**
     * Llista totes les fotos d'un professional
     *
     * @param int $professional_id ID del professional
     * @return array Array de fotos
     * @throws Exception Si hi ha errors de base de dades
     */
    public function llistarPerProfessional(int $professional_id): array {
        $sql = "SELECT * FROM {$this->table} 
                WHERE professional_id = :professional_id 
                ORDER BY created_at ASC";

        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':professional_id', $professional_id, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Error en llistar fotos del professional: " . $e->getMessage());
        }
    }

    /**
     * Compta el total de fotos d'un professional
     *
     * @param int $professional_id ID del professional
     * @return int Total de fotos
     * @throws Exception Si hi ha errors de base de dades
     */
    public function comptarPerProfessional(int $professional_id): int {
        $sql = "SELECT COUNT(*) as total FROM {$this->table} 
                WHERE professional_id = :professional_id";

        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':professional_id', $professional_id, PDO::PARAM_INT);
            $stmt->execute();
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return (int)($result['total'] ?? 0);
        } catch (PDOException $e) {
            throw new Exception("Error en comptar fotos: " . $e->getMessage());
        }
    }

    /**
     * Eliminar totes les fotos d'un professional
     *
     * @param int $professional_id ID del professional
     * @return bool True si s'ha eliminat correctament
     * @throws Exception Si hi ha errors de base de dades
     */
    public function eliminarPerProfessional(int $professional_id): bool {
        $sql = "DELETE FROM {$this->table} WHERE professional_id = :professional_id";

        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':professional_id', $professional_id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            throw new Exception("Error en eliminar fotos del professional: " . $e->getMessage());
        }
    }

    /**
     * Carregar dades en les propietats de la classe des d'un array
     *
     * @param array $data Array associatiu amb les dades
     */
    public function carregarDades(array $data): void {
        if (isset($data['id'])) $this->id = (int)$data['id'];
        if (isset($data['professional_id'])) $this->professional_id = (int)$data['professional_id'];
        if (isset($data['image_path'])) $this->image_path = $data['image_path'];
        if (isset($data['title_ca'])) $this->title_ca = $data['title_ca'];
        if (isset($data['title_es'])) $this->title_es = $data['title_es'];
        if (isset($data['description_ca'])) $this->description_ca = $data['description_ca'];
        if (isset($data['description_es'])) $this->description_es = $data['description_es'];
        if (isset($data['alt_ca'])) $this->alt_ca = $data['alt_ca'];
        if (isset($data['alt_es'])) $this->alt_es = $data['alt_es'];
        if (isset($data['created_at'])) $this->created_at = $data['created_at'];
        if (isset($data['updated_at'])) $this->updated_at = $data['updated_at'];
    }

    /**
     * Neteja les propietats de la classe
     */
    public function netejar(): void {
        $this->id = null;
        $this->professional_id = null;
        $this->image_path = null;
        $this->title_ca = null;
        $this->title_es = null;
        $this->description_ca = null;
        $this->description_es = null;
        $this->alt_ca = null;
        $this->alt_es = null;
        $this->created_at = null;
        $this->updated_at = null;
    }
}
