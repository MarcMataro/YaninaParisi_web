<?php
/**
 * Classe Especialitats
 *
 * Gestiona les especialitats mèdiques/psicològiques utilitzant el patró Singleton.
 * Implementa operacions CRUD, validació i cerca per diferents criteris.
 *
 * Estructura esperada de la taula `especialitats`:
 * - id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT
 * - nom VARCHAR(150) NOT NULL
 * - nom_es VARCHAR(150)
 * - descripcio TEXT
 * - descripcio_es TEXT
 * - created_at TIMESTAMP
 * - updated_at TIMESTAMP
 *
 * @author Marc Mataró
 * @version 1.0.0
 * @date 2026-01-01
 */

class Especialitats {

    /** @var Especialitats|null Instància Singleton */
    private static $instancia = null;

    /** @var PDO Instància de connexió a la base de dades */
    private $conn;

    /** @var string Nom de la taula */
    private $table = 'especialitats';

    /* ======== Propietats privades del model ======== */
    private $id;
    private $nom;
    private $nom_es;
    private $descripcio;
    private $descripcio_es;
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
     * @return Especialitats Instància única
     */
    public static function getInstance(): Especialitats {
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
     * @return string|null
     */
    public function getNom(): ?string {
        return $this->nom;
    }

    /**
     * @return string|null
     */
    public function getNomEs(): ?string {
        return $this->nom_es;
    }

    /**
     * @return string|null
     */
    public function getDescripcio(): ?string {
        return $this->descripcio;
    }

    /**
     * @return string|null
     */
    public function getDescripcioEs(): ?string {
        return $this->descripcio_es;
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
     * @param string $nom
     */
    public function setNom(string $nom): void {
        $this->nom = trim($nom);
    }

    /**
     * @param string|null $nom_es
     */
    public function setNomEs(?string $nom_es): void {
        $this->nom_es = $nom_es ? trim($nom_es) : null;
    }

    /**
     * @param string|null $descripcio
     */
    public function setDescripcio(?string $descripcio): void {
        $this->descripcio = $descripcio;
    }

    /**
     * @param string|null $descripcio_es
     */
    public function setDescripcioEs(?string $descripcio_es): void {
        $this->descripcio_es = $descripcio_es;
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
        $this->nom = $data['nom'] ?? null;
        $this->nom_es = $data['nom_es'] ?? null;
        $this->descripcio = $data['descripcio'] ?? null;
        $this->descripcio_es = $data['descripcio_es'] ?? null;
        $this->created_at = $data['created_at'] ?? null;
        $this->updated_at = $data['updated_at'] ?? null;
    }

    /**
     * Neteja les propietats de l'objecte
     */
    private function netejarPropietats(): void {
        $this->id = null;
        $this->nom = null;
        $this->nom_es = null;
        $this->descripcio = null;
        $this->descripcio_es = null;
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

        // Validar nom
        if (empty($this->nom)) {
            $errors[] = 'El nom és obligatori';
        } elseif (strlen($this->nom) > 150) {
            $errors[] = 'El nom no pot superar els 150 caràcters';
        }

        return $errors;
    }

    /* ===================== CRUD - Crear ===================== */
    
    /**
     * Crea una nova especialitat a la base de dades
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

        // Verificar que no existeixi ja el nom
        if ($this->existeixNom($this->nom)) {
            throw new Exception('Ja existeix una especialitat amb aquest nom');
        }

        $sql = "INSERT INTO {$this->table} (nom, nom_es, descripcio, descripcio_es) 
                VALUES (:nom, :nom_es, :descripcio, :descripcio_es)";

        try {
            $stmt = $this->conn->prepare($sql);
            
            $stmt->bindValue(':nom', $this->sanitize($this->nom));
            $stmt->bindValue(':nom_es', $this->nom_es ? $this->sanitize($this->nom_es) : null);
            $stmt->bindValue(':descripcio', $this->descripcio);
            $stmt->bindValue(':descripcio_es', $this->descripcio_es);

            if ($stmt->execute()) {
                $this->id = (int)$this->conn->lastInsertId();
                return $this->id;
            }
            
            return false;
        } catch (PDOException $e) {
            throw new Exception("Error en crear l'especialitat: " . $e->getMessage());
        }
    }

    /* ===================== CRUD - Llegir ===================== */
    
    /**
     * Cerca una especialitat per ID i carrega les seves dades
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
            throw new Exception("Error en llegir l'especialitat: " . $e->getMessage());
        }
    }

    /**
     * Cerca una especialitat per nom i carrega les seves dades
     *
     * @param string $nom
     * @return bool True si s'ha trobat, false si no
     * @throws Exception Si hi ha errors de base de dades
     */
    public function llegirPerNom(string $nom): bool {
        $sql = "SELECT * FROM {$this->table} WHERE nom = :nom LIMIT 1";

        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':nom', trim($nom));
            $stmt->execute();

            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($row) {
                $this->carregarDades($row);
                return true;
            }

            $this->netejarPropietats();
            return false;
        } catch (PDOException $e) {
            throw new Exception("Error en cercar per nom: " . $e->getMessage());
        }
    }

    /**
     * Obté una especialitat per ID sense modificar l'estat de l'objecte
     *
     * @param int $id
     * @return array|null Array associatiu amb les dades o null
     * @throws Exception Si hi ha errors de base de dades
     */
    public function obtenirPerId(int $id): ?array {
        $sql = "SELECT * FROM {$this->table} WHERE id = :id LIMIT 1";

        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row ?: null;
        } catch (PDOException $e) {
            throw new Exception("Error en obtenir l'especialitat: " . $e->getMessage());
        }
    }

    /* ===================== CRUD - Actualitzar ===================== */
    
    /**
     * Actualitza una especialitat a la base de dades
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

        // Verificar que no existeixi el nom en un altre registre
        if ($this->existeixNom($this->nom, $this->id)) {
            throw new Exception('Ja existeix una altra especialitat amb aquest nom');
        }

        $sql = "UPDATE {$this->table} SET
                    nom = :nom,
                    nom_es = :nom_es,
                    descripcio = :descripcio,
                    descripcio_es = :descripcio_es
                WHERE id = :id";

        try {
            $stmt = $this->conn->prepare($sql);
            
            $stmt->bindValue(':nom', $this->sanitize($this->nom));
            $stmt->bindValue(':nom_es', $this->nom_es ? $this->sanitize($this->nom_es) : null);
            $stmt->bindValue(':descripcio', $this->descripcio);
            $stmt->bindValue(':descripcio_es', $this->descripcio_es);
            $stmt->bindValue(':id', $this->id, PDO::PARAM_INT);

            return $stmt->execute();
        } catch (PDOException $e) {
            throw new Exception("Error en actualitzar l'especialitat: " . $e->getMessage());
        }
    }

    /* ===================== CRUD - Eliminar ===================== */
    
    /**
     * Elimina una especialitat de la base de dades
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
            throw new Exception("Error en eliminar l'especialitat: " . $e->getMessage());
        }
    }

    /* ===================== Llistar i cercar ===================== */
    
    /**
     * Llista totes les especialitats amb opcions de filtrat
     *
     * @param array $filtres Opcions: 'limit', 'offset', 'ordre'
     * @return array Array d'especialitats
     * @throws Exception Si hi ha errors de base de dades
     */
    public function llistar(array $filtres = []): array {
        $sql = "SELECT * FROM {$this->table} WHERE 1=1";
        $params = [];

        // Ordenació
        $ordre = $filtres['ordre'] ?? 'nom ASC';
        $sql .= " ORDER BY {$ordre}";

        // Límit i offset
        if (isset($filtres['limit'])) {
            $sql .= " LIMIT :limit";
            if (isset($filtres['offset'])) {
                $sql .= " OFFSET :offset";
            }
        }

        try {
            $stmt = $this->conn->prepare($sql);
            
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value, PDO::PARAM_INT);
            }
            
            if (isset($filtres['limit'])) {
                $stmt->bindValue(':limit', (int)$filtres['limit'], PDO::PARAM_INT);
                if (isset($filtres['offset'])) {
                    $stmt->bindValue(':offset', (int)$filtres['offset'], PDO::PARAM_INT);
                }
            }

            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Error en llistar especialitats: " . $e->getMessage());
        }
    }

    /**
     * Llista totes les especialitats ordenades alfabèticament
     *
     * @return array Array d'especialitats
     * @throws Exception Si hi ha errors de base de dades
     */
    public function llistarTotes(): array {
        return $this->llistar(['ordre' => 'nom ASC']);
    }

    /**
     * Cerca especialitats per nom (cerca parcial)
     *
     * @param string $cerca Text a cercar
     * @return array Array d'especialitats
     * @throws Exception Si hi ha errors de base de dades
     */
    public function cercar(string $cerca): array {
        $sql = "SELECT * FROM {$this->table} 
                WHERE nom LIKE :cerca1 
                OR descripcio LIKE :cerca2
                ORDER BY nom ASC";

        try {
            $stmt = $this->conn->prepare($sql);
            $searchTerm = '%' . $cerca . '%';
            $stmt->bindValue(':cerca1', $searchTerm);
            $stmt->bindValue(':cerca2', $searchTerm);
            
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Error en cercar especialitats: " . $e->getMessage());
        }
    }

    /**
     * Compte el total d'especialitats
     *
     * @return int Total d'especialitats
     * @throws Exception Si hi ha errors de base de dades
     */
    public function comptar(): int {
        $sql = "SELECT COUNT(*) as total FROM {$this->table}";

        try {
            $stmt = $this->conn->query($sql);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return (int)$result['total'];
        } catch (PDOException $e) {
            throw new Exception("Error en comptar especialitats: " . $e->getMessage());
        }
    }

    /**
     * Comprova si existeix un nom a la base de dades
     *
     * @param string $nom
     * @param int|null $excepteId ID a excloure de la cerca
     * @return bool True si existeix
     * @throws Exception Si hi ha errors de base de dades
     */
    public function existeixNom(string $nom, ?int $excepteId = null): bool {
        $sql = "SELECT COUNT(*) as total FROM {$this->table} WHERE nom = :nom";
        
        if ($excepteId !== null) {
            $sql .= " AND id != :id";
        }

        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':nom', trim($nom));
            
            if ($excepteId !== null) {
                $stmt->bindValue(':id', $excepteId, PDO::PARAM_INT);
            }
            
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return (int)$result['total'] > 0;
        } catch (PDOException $e) {
            throw new Exception("Error en verificar nom: " . $e->getMessage());
        }
    }

    /**
     * Obté les especialitats més utilitzades (si hi ha taula de relació)
     * Aquest mètode es pot estendre quan hi hagi relacions amb professionals
     *
     * @param int $limit Nombre d'especialitats a retornar
     * @return array Array d'especialitats
     * @throws Exception Si hi ha errors de base de dades
     */
    public function obtenirMesUtilitzades(int $limit = 5): array {
        return $this->llistar(['limit' => $limit, 'ordre' => 'nom ASC']);
    }
}
