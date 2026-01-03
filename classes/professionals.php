<?php
/**
 * Classe Professionals
 *
 * Gestiona els professionals de la clínica utilitzant el patró Singleton.
 * Implementa operacions CRUD, validació i cerca per diferents criteris.
 *
 * Estructura esperada de la taula `professionals`:
 * - id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT
 * - nom VARCHAR(100) NOT NULL
 * - cognoms VARCHAR(150) NOT NULL
 * - subtitol_ca VARCHAR(50)
 * - subtitol_es VARCHAR(50)
 * - email VARCHAR(150) NOT NULL UNIQUE
 * - telefon VARCHAR(30)
 * - descripcio TEXT
 * - descripcio_es TEXT
 * - num_collegiat VARCHAR(50)
 * - anys_experiencia INT UNSIGNED
 * - foto VARCHAR(255)
 * - actiu TINYINT(1) DEFAULT 1
 * - visible_web TINYINT(1) DEFAULT 1
 * - created_at TIMESTAMP
 * - updated_at TIMESTAMP
 *
 * @author Marc Mataró
 * @version 1.0.0
 * @date 2026-01-01
 */

class Professionals {

    /** @var Professionals|null Instància Singleton */
    private static $instancia = null;

    /** @var PDO Instància de connexió a la base de dades */
    private $conn;

    /** @var string Nom de la taula */
    private $table = 'professionals';

    /* ======== Propietats privades del model ======== */
    private $id;
    private $nom;
    private $cognoms;
    private $subtitol_ca;
    private $subtitol_es;
    private $email;
    private $telefon;
    private $descripcio;
    private $descripcio_es;
    private $num_collegiat;
    private $anys_experiencia;
    private $foto;
    private $actiu;
    private $visible_web;
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
     * @return Professionals Instància única
     */
    public static function getInstance(): Professionals {
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
    public function getCognoms(): ?string {
        return $this->cognoms;
    }

    /**
     * @return string|null
     */
    public function getSubtitolCa(): ?string {
        return $this->subtitol_ca;
    }

    /**
     * @return string|null
     */
    public function getSubtitolEs(): ?string {
        return $this->subtitol_es;
    }

    /**
     * @return string|null
     */
    public function getEmail(): ?string {
        return $this->email;
    }

    /**
     * @return string|null
     */
    public function getTelefon(): ?string {
        return $this->telefon;
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
    public function getNumCollegiat(): ?string {
        return $this->num_collegiat;
    }

    /**
     * @return int|null
     */
    public function getAnysExperiencia(): ?int {
        return $this->anys_experiencia;
    }

    /**
     * @return string|null
     */
    public function getFoto(): ?string {
        return $this->foto;
    }

    /**
     * @return bool
     */
    public function getActiu(): bool {
        return (bool)$this->actiu;
    }

    /**
     * @return bool
     */
    public function getVisibleWeb(): bool {
        return (bool)$this->visible_web;
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

    /**
     * Retorna el nom complet del professional
     *
     * @return string
     */
    public function getNomComplet(): string {
        return trim(($this->nom ?? '') . ' ' . ($this->cognoms ?? ''));
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
     * @param string $cognoms
     */
    public function setCognoms(string $cognoms): void {
        $this->cognoms = trim($cognoms);
    }

    /**
     * @param string|null $subtitol_ca
     */
    public function setSubtitolCa(?string $subtitol_ca): void {
        $this->subtitol_ca = $subtitol_ca ? trim($subtitol_ca) : null;
    }

    /**
     * @param string|null $subtitol_es
     */
    public function setSubtitolEs(?string $subtitol_es): void {
        $this->subtitol_es = $subtitol_es ? trim($subtitol_es) : null;
    }

    /**
     * @param string $email
     */
    public function setEmail(string $email): void {
        $this->email = trim(strtolower($email));
    }

    /**
     * @param string|null $telefon
     */
    public function setTelefon(?string $telefon): void {
        $this->telefon = $telefon ? trim($telefon) : null;
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

    /**
     * @param string|null $num_collegiat
     */
    public function setNumCollegiat(?string $num_collegiat): void {
        $this->num_collegiat = $num_collegiat ? trim($num_collegiat) : null;
    }

    /**
     * @param int|null $anys_experiencia
     */
    public function setAnysExperiencia(?int $anys_experiencia): void {
        $this->anys_experiencia = $anys_experiencia;
    }

    /**
     * @param string|null $foto
     */
    public function setFoto(?string $foto): void {
        $this->foto = $foto ? trim($foto) : null;
    }

    /**
     * @param bool $actiu
     */
    public function setActiu(bool $actiu): void {
        $this->actiu = $actiu ? 1 : 0;
    }

    /**
     * @param bool $visible_web
     */
    public function setVisibleWeb(bool $visible_web): void {
        $this->visible_web = $visible_web ? 1 : 0;
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
     * Valida una adreça de correu electrònic
     *
     * @param string $email
     * @return bool
     */
    private function validarEmail(string $email): bool {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Carrega les dades d'un array associatiu a les propietats de l'objecte
     *
     * @param array $data
     */
    private function carregarDades(array $data): void {
        $this->id = $data['id'] ?? null;
        $this->nom = $data['nom'] ?? null;
        $this->cognoms = $data['cognoms'] ?? null;
        $this->subtitol_ca = $data['subtitol_ca'] ?? null;
        $this->subtitol_es = $data['subtitol_es'] ?? null;
        $this->email = $data['email'] ?? null;
        $this->telefon = $data['telefon'] ?? null;
        $this->descripcio = $data['descripcio'] ?? null;
        $this->descripcio_es = $data['descripcio_es'] ?? null;
        $this->num_collegiat = $data['num_collegiat'] ?? null;
        $this->anys_experiencia = $data['anys_experiencia'] ?? null;
        $this->foto = $data['foto'] ?? null;
        $this->actiu = $data['actiu'] ?? 1;
        $this->visible_web = $data['visible_web'] ?? 1;
        $this->created_at = $data['created_at'] ?? null;
        $this->updated_at = $data['updated_at'] ?? null;
    }

    /**
     * Neteja les propietats de l'objecte
     */
    private function netejarPropietats(): void {
        $this->id = null;
        $this->nom = null;
        $this->cognoms = null;
        $this->subtitol_ca = null;
        $this->subtitol_es = null;
        $this->email = null;
        $this->telefon = null;
        $this->descripcio = null;
        $this->descripcio_es = null;
        $this->num_collegiat = null;
        $this->anys_experiencia = null;
        $this->foto = null;
        $this->actiu = 1;
        $this->visible_web = 1;
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
        } elseif (strlen($this->nom) > 100) {
            $errors[] = 'El nom no pot superar els 100 caràcters';
        }

        // Validar cognoms
        if (empty($this->cognoms)) {
            $errors[] = 'Els cognoms són obligatoris';
        } elseif (strlen($this->cognoms) > 150) {
            $errors[] = 'Els cognoms no poden superar els 150 caràcters';
        }

        // Validar email
        if (empty($this->email)) {
            $errors[] = 'L\'email és obligatori';
        } elseif (!$this->validarEmail($this->email)) {
            $errors[] = 'L\'email no té un format vàlid';
        } elseif (strlen($this->email) > 150) {
            $errors[] = 'L\'email no pot superar els 150 caràcters';
        }

        // Validar telèfon (si existeix)
        if (!empty($this->telefon) && strlen($this->telefon) > 30) {
            $errors[] = 'El telèfon no pot superar els 30 caràcters';
        }

        // Validar número de col·legiat (si existeix)
        if (!empty($this->num_collegiat) && strlen($this->num_collegiat) > 50) {
            $errors[] = 'El número de col·legiat no pot superar els 50 caràcters';
        }

        // Validar anys d'experiència (si existeix)
        if ($this->anys_experiencia !== null && $this->anys_experiencia < 0) {
            $errors[] = 'Els anys d\'experiència no poden ser negatius';
        }

        // Validar foto (si existeix)
        if (!empty($this->foto) && strlen($this->foto) > 255) {
            $errors[] = 'La ruta de la foto no pot superar els 255 caràcters';
        }

        return $errors;
    }

    /* ===================== CRUD - Crear ===================== */
    
    /**
     * Crea un nou professional a la base de dades
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

        // Verificar que no existeixi ja l'email
        if ($this->existeixEmail($this->email)) {
            throw new Exception('Ja existeix un professional amb aquest email');
        }

        $sql = "INSERT INTO {$this->table} (
                    nom, cognoms, subtitol_ca, subtitol_es, email, telefon, descripcio, descripcio_es,
                    num_collegiat, anys_experiencia, foto, actiu, visible_web
                ) VALUES (
                    :nom, :cognoms, :subtitol_ca, :subtitol_es, :email, :telefon, :descripcio, :descripcio_es,
                    :num_collegiat, :anys_experiencia, :foto, :actiu, :visible_web
                )";

        try {
            $stmt = $this->conn->prepare($sql);
            
            $stmt->bindValue(':nom', $this->sanitize($this->nom));
            $stmt->bindValue(':cognoms', $this->sanitize($this->cognoms));
            $stmt->bindValue(':subtitol_ca', $this->subtitol_ca);
            $stmt->bindValue(':subtitol_es', $this->subtitol_es);
            $stmt->bindValue(':email', $this->email);
            $stmt->bindValue(':telefon', $this->telefon);
            $stmt->bindValue(':descripcio', $this->descripcio);
            $stmt->bindValue(':descripcio_es', $this->descripcio_es);
            $stmt->bindValue(':num_collegiat', $this->num_collegiat);
            $stmt->bindValue(':anys_experiencia', $this->anys_experiencia, PDO::PARAM_INT);
            $stmt->bindValue(':foto', $this->foto);
            $stmt->bindValue(':actiu', $this->actiu, PDO::PARAM_INT);
            $stmt->bindValue(':visible_web', $this->visible_web, PDO::PARAM_INT);

            if ($stmt->execute()) {
                $this->id = (int)$this->conn->lastInsertId();
                return $this->id;
            }
            
            return false;
        } catch (PDOException $e) {
            throw new Exception("Error en crear el professional: " . $e->getMessage());
        }
    }

    /* ===================== CRUD - Llegir ===================== */
    
    /**
     * Cerca un professional per ID i carrega les seves dades
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
            throw new Exception("Error en llegir el professional: " . $e->getMessage());
        }
    }

    /**
     * Cerca un professional per email i carrega les seves dades
     *
     * @param string $email
     * @return bool True si s'ha trobat, false si no
     * @throws Exception Si hi ha errors de base de dades
     */
    public function llegirPerEmail(string $email): bool {
        $sql = "SELECT * FROM {$this->table} WHERE email = :email LIMIT 1";

        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':email', trim(strtolower($email)));
            $stmt->execute();

            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($row) {
                $this->carregarDades($row);
                return true;
            }

            $this->netejarPropietats();
            return false;
        } catch (PDOException $e) {
            throw new Exception("Error en cercar per email: " . $e->getMessage());
        }
    }

    /**
     * Obté un professional per ID sense modificar l'estat de l'objecte
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
            throw new Exception("Error en obtenir el professional: " . $e->getMessage());
        }
    }

    /* ===================== CRUD - Actualitzar ===================== */
    
    /**
     * Actualitza un professional a la base de dades
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

        // Verificar que no existeixi l'email en un altre registre
        if ($this->existeixEmail($this->email, $this->id)) {
            throw new Exception('Ja existeix un altre professional amb aquest email');
        }

        $sql = "UPDATE {$this->table} SET
                    nom = :nom,
                    cognoms = :cognoms,
                    subtitol_ca = :subtitol_ca,
                    subtitol_es = :subtitol_es,
                    email = :email,
                    telefon = :telefon,
                    descripcio = :descripcio,
                    descripcio_es = :descripcio_es,
                    num_collegiat = :num_collegiat,
                    anys_experiencia = :anys_experiencia,
                    foto = :foto,
                    actiu = :actiu,
                    visible_web = :visible_web
                WHERE id = :id";

        try {
            $stmt = $this->conn->prepare($sql);
            
            $stmt->bindValue(':nom', $this->sanitize($this->nom));
            $stmt->bindValue(':cognoms', $this->sanitize($this->cognoms));
            $stmt->bindValue(':subtitol_ca', $this->subtitol_ca);
            $stmt->bindValue(':subtitol_es', $this->subtitol_es);
            $stmt->bindValue(':email', $this->email);
            $stmt->bindValue(':telefon', $this->telefon);
            $stmt->bindValue(':descripcio', $this->descripcio);
            $stmt->bindValue(':descripcio_es', $this->descripcio_es);
            $stmt->bindValue(':num_collegiat', $this->num_collegiat);
            $stmt->bindValue(':anys_experiencia', $this->anys_experiencia, PDO::PARAM_INT);
            $stmt->bindValue(':foto', $this->foto);
            $stmt->bindValue(':actiu', $this->actiu, PDO::PARAM_INT);
            $stmt->bindValue(':visible_web', $this->visible_web, PDO::PARAM_INT);
            $stmt->bindValue(':id', $this->id, PDO::PARAM_INT);

            return $stmt->execute();
        } catch (PDOException $e) {
            throw new Exception("Error en actualitzar el professional: " . $e->getMessage());
        }
    }

    /* ===================== CRUD - Eliminar ===================== */
    
    /**
     * Elimina un professional de la base de dades
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
            throw new Exception("Error en eliminar el professional: " . $e->getMessage());
        }
    }

    /**
     * Desactiva un professional (soft delete)
     *
     * @param int $id
     * @return bool True si s'ha desactivat correctament
     * @throws Exception Si hi ha errors de base de dades
     */
    public function desactivar(int $id): bool {
        $sql = "UPDATE {$this->table} SET actiu = 0 WHERE id = :id";

        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            
            if ($stmt->execute()) {
                if ($this->id === $id) {
                    $this->actiu = 0;
                }
                return true;
            }
            
            return false;
        } catch (PDOException $e) {
            throw new Exception("Error en desactivar el professional: " . $e->getMessage());
        }
    }

    /**
     * Activa un professional
     *
     * @param int $id
     * @return bool True si s'ha activat correctament
     * @throws Exception Si hi ha errors de base de dades
     */
    public function activar(int $id): bool {
        $sql = "UPDATE {$this->table} SET actiu = 1 WHERE id = :id";

        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            
            if ($stmt->execute()) {
                if ($this->id === $id) {
                    $this->actiu = 1;
                }
                return true;
            }
            
            return false;
        } catch (PDOException $e) {
            throw new Exception("Error en activar el professional: " . $e->getMessage());
        }
    }

    /* ===================== Llistar i cercar ===================== */
    
    /**
     * Llista tots els professionals amb opcions de filtrat
     *
     * @param array $filtres Opcions: 'actiu', 'visible_web', 'limit', 'offset', 'ordre'
     * @return array Array de professionals
     * @throws Exception Si hi ha errors de base de dades
     */
    public function llistar(array $filtres = []): array {
        $sql = "SELECT * FROM {$this->table} WHERE 1=1";
        $params = [];

        // Filtrar per actiu
        if (isset($filtres['actiu'])) {
            $sql .= " AND actiu = :actiu";
            $params[':actiu'] = (int)$filtres['actiu'];
        }

        // Filtrar per visible_web
        if (isset($filtres['visible_web'])) {
            $sql .= " AND visible_web = :visible_web";
            $params[':visible_web'] = (int)$filtres['visible_web'];
        }

        // Ordenació
        $ordre = $filtres['ordre'] ?? 'cognoms ASC, nom ASC';
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
            throw new Exception("Error en llistar professionals: " . $e->getMessage());
        }
    }

    /**
     * Llista professionals visibles a la web
     *
     * @return array Array de professionals
     * @throws Exception Si hi ha errors de base de dades
     */
    public function llistarVisiblesWeb(): array {
        return $this->llistar([
            'actiu' => 1,
            'visible_web' => 1,
            'ordre' => 'cognoms ASC, nom ASC'
        ]);
    }

    /**
     * Cerca professionals per nom o cognoms
     *
     * @param string $cerca Text a cercar
     * @return array Array de professionals
     * @throws Exception Si hi ha errors de base de dades
     */
    public function cercar(string $cerca): array {
        $sql = "SELECT * FROM {$this->table} 
                WHERE nom LIKE :cerca1 
                OR cognoms LIKE :cerca2 
                OR email LIKE :cerca3
                ORDER BY cognoms ASC, nom ASC";

        try {
            $stmt = $this->conn->prepare($sql);
            $searchTerm = '%' . $cerca . '%';
            $stmt->bindValue(':cerca1', $searchTerm);
            $stmt->bindValue(':cerca2', $searchTerm);
            $stmt->bindValue(':cerca3', $searchTerm);
            
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Error en cercar professionals: " . $e->getMessage());
        }
    }

    /**
     * Compte el total de professionals amb filtres opcionals
     *
     * @param array $filtres Opcions: 'actiu', 'visible_web'
     * @return int Total de professionals
     * @throws Exception Si hi ha errors de base de dades
     */
    public function comptar(array $filtres = []): int {
        $sql = "SELECT COUNT(*) as total FROM {$this->table} WHERE 1=1";
        $params = [];

        if (isset($filtres['actiu'])) {
            $sql .= " AND actiu = :actiu";
            $params[':actiu'] = (int)$filtres['actiu'];
        }

        if (isset($filtres['visible_web'])) {
            $sql .= " AND visible_web = :visible_web";
            $params[':visible_web'] = (int)$filtres['visible_web'];
        }

        try {
            $stmt = $this->conn->prepare($sql);
            
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value, PDO::PARAM_INT);
            }
            
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return (int)$result['total'];
        } catch (PDOException $e) {
            throw new Exception("Error en comptar professionals: " . $e->getMessage());
        }
    }

    /**
     * Comprova si existeix un email a la base de dades
     *
     * @param string $email
     * @param int|null $excepteId ID a excloure de la cerca
     * @return bool True si existeix
     * @throws Exception Si hi ha errors de base de dades
     */
    public function existeixEmail(string $email, ?int $excepteId = null): bool {
        $sql = "SELECT COUNT(*) as total FROM {$this->table} WHERE email = :email";
        
        if ($excepteId !== null) {
            $sql .= " AND id != :id";
        }

        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':email', trim(strtolower($email)));
            
            if ($excepteId !== null) {
                $stmt->bindValue(':id', $excepteId, PDO::PARAM_INT);
            }
            
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return (int)$result['total'] > 0;
        } catch (PDOException $e) {
            throw new Exception("Error en verificar email: " . $e->getMessage());
        }
    }

    /**
     * Comprova si existeix un número de col·legiat a la base de dades
     *
     * @param string $num_collegiat
     * @param int|null $excepteId ID a excloure de la cerca
     * @return bool True si existeix
     * @throws Exception Si hi ha errors de base de dades
     */
    public function existeixNumCollegiat(string $num_collegiat, ?int $excepteId = null): bool {
        $sql = "SELECT COUNT(*) as total FROM {$this->table} 
                WHERE num_collegiat = :num_collegiat AND num_collegiat IS NOT NULL";
        
        if ($excepteId !== null) {
            $sql .= " AND id != :id";
        }

        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':num_collegiat', trim($num_collegiat));
            
            if ($excepteId !== null) {
                $stmt->bindValue(':id', $excepteId, PDO::PARAM_INT);
            }
            
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return (int)$result['total'] > 0;
        } catch (PDOException $e) {
            throw new Exception("Error en verificar número de col·legiat: " . $e->getMessage());
        }
    }
}
