<?php
/**
 * Classe Etiqueta
 *
 * Gestiona les etiquetes del blog segons l'estructura de dades proporcionada.
 * Implementa operacions CRUD, validació, cerca per slug i utilitats de reordenació.
 *
 * Estructura esperada de la taula `etiquetes`:
 * - id_etiqueta INT PRIMARY KEY AUTO_INCREMENT
 * - nom_ca, nom_es VARCHAR(100)
 * - slug_ca, slug_es VARCHAR(120) UNIQUE
 * - descripcio_ca TEXT, descripcion_es TEXT
 * - ordre INT DEFAULT 0
 * - activa BOOLEAN DEFAULT TRUE
 * - data_creacio DATETIME, data_actualitzacio DATETIME
 *
 * @author Marc Mataró
 * @version 1.0.0
 * @date 2025-10-11
 */

class Etiqueta {

    /** @var PDO Instància de connexió a la base de dades */
    private $conn;

    /** @var string Nom de la taula */
    private $table = 'etiquetes';

    /* ======== Propietats del model (mapegen camp a propietat) ======== */
    public $id_etiqueta;
    public $nom_ca;
    public $nom_es;
    public $slug_ca;
    public $slug_es;
    public $descripcio_ca;
    public $descripcion_es;
    public $ordre = 0;
    public $activa = 1;
    public $data_creacio;
    public $data_actualitzacio;

    /* ===================== Constructor ===================== */
    public function __construct($db) {
        if (!$db instanceof PDO) {
            throw new InvalidArgumentException('La connexió ha de ser una instància de PDO');
        }
        $this->conn = $db;
    }

    /* ===================== Helpers / Utilitats ===================== */

    /**
     * Normalitza i genera un slug segur a partir d'un text.
     * Manté només lletres, números i guions, elimina accents i caràcters especials.
     *
     * @param string $text
     * @return string
     */
    public function generarSlug(string $text): string {
        // Convertir a minuscules
        $text = mb_strtolower(trim($text), 'UTF-8');

        // Substituir caràcters accentuats per equivalents sense accent
        $replacements = [
            'à'=>'a','á'=>'a','â'=>'a','ä'=>'a','ã'=>'a','å'=>'a','æ'=>'ae',
            'ç'=>'c','è'=>'e','é'=>'e','ê'=>'e','ë'=>'e','ì'=>'i','í'=>'i','î'=>'i','ï'=>'i',
            'ñ'=>'n','ò'=>'o','ó'=>'o','ô'=>'o','ö'=>'o','õ'=>'o','œ'=>'oe',
            'ù'=>'u','ú'=>'u','û'=>'u','ü'=>'u','ý'=>'y','ÿ'=>'y'
        ];
        $text = strtr($text, $replacements);

        // Substituir qualsevol cosa no alfanumèrica per guió
        $text = preg_replace('/[^a-z0-9]+/u', '-', $text);

        // Treure guions inicials/ finals i duplicates
        $text = trim($text, '-');
        $text = preg_replace('/-+/', '-', $text);

        return $text === '' ? 'etiqueta' : $text;
    }

    /**
     * Assegura que un slug sigui únic per la llengua donada afegint sufix numèric si cal.
     *
     * @param string $slug
     * @param string $lang 'ca' o 'es'
     * @param int|null $excludeId ID per excloure (útil a l'hora d'actualitzar)
     * @return string slug únic
     */
    public function makeUniqueSlug(string $slug, string $lang = 'ca', $excludeId = null): string {
        $base = $slug;
        $i = 1;
        while ($this->existeixSlug($slug, $lang, $excludeId)) {
            $slug = $base . '-' . $i;
            $i++;
        }
        return $slug;
    }

    /**
     * Comprova si un slug existeix ja a la base de dades per una llengua determinada.
     *
     * @param string $slug
     * @param string $lang 'ca' o 'es'
     * @param int|null $excludeId
     * @return bool
     */
    public function existeixSlug(string $slug, string $lang = 'ca', $excludeId = null): bool {
        $col = $lang === 'es' ? 'slug_es' : 'slug_ca';
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE {$col} = :slug";
        if ($excludeId) {
            $sql .= " AND id_etiqueta != :exclude";
        }
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':slug', $slug);
        if ($excludeId) $stmt->bindParam(':exclude', $excludeId, PDO::PARAM_INT);
        $stmt->execute();
        return (int)$stmt->fetchColumn() > 0;
    }

    /* ===================== Validació ===================== */
    /**
     * Valida les dades mínimes abans d'inserir/actualitzar.
     * Retorna array amb errors (buit si no hi ha errors).
     *
     * @return array
     */
    public function validar(): array {
        $errors = [];

        if (empty(trim((string)$this->nom_ca))) {
            $errors[] = 'El nom en català és obligatori';
        }
        if (empty(trim((string)$this->nom_es))) {
            $errors[] = 'El nom en castellà és obligatori';
        }

        // Validar longitud dels noms
        if (strlen(trim((string)$this->nom_ca)) > 100) {
            $errors[] = 'El nom en català no pot superar els 100 caràcters';
        }
        if (strlen(trim((string)$this->nom_es)) > 100) {
            $errors[] = 'El nom en castellà no pot superar els 100 caràcters';
        }

        // Slugs: si es proporcionen, validar format; si no, els generarem.
        if (!empty($this->slug_ca) && !preg_match('/^[a-z0-9\-]+$/', $this->slug_ca)) {
            $errors[] = 'El slug (ca) té un format no vàlid';
        }
        if (!empty($this->slug_es) && !preg_match('/^[a-z0-9\-]+$/', $this->slug_es)) {
            $errors[] = 'El slug (es) té un format no vàlid';
        }

        // Validar longitud dels slugs
        if (!empty($this->slug_ca) && strlen($this->slug_ca) > 120) {
            $errors[] = 'El slug (ca) no pot superar els 120 caràcters';
        }
        if (!empty($this->slug_es) && strlen($this->slug_es) > 120) {
            $errors[] = 'El slug (es) no pot superar els 120 caràcters';
        }

        return $errors;
    }

    /* ===================== CRUD ===================== */
    /**
     * Crear una nova etiqueta.
     * Retorna l'id creat o false en cas de fallada.
     *
     * @return int|false
     */
    public function crear() {
        $errs = $this->validar();
        if (!empty($errs)) {
            return false;
        }

        // Netejar entrades
        $this->nom_ca = htmlspecialchars(strip_tags(trim((string)$this->nom_ca)));
        $this->nom_es = htmlspecialchars(strip_tags(trim((string)$this->nom_es)));
        $this->descripcio_ca = $this->descripcio_ca ?? null;
        $this->descripcion_es = $this->descripcion_es ?? null;
        $this->ordre = (int)($this->ordre ?? 0);
        $this->activa = $this->activa ? 1 : 0;

        // Generar slugs si no especificats
        if (empty($this->slug_ca)) {
            $this->slug_ca = $this->generarSlug($this->nom_ca);
        }
        if (empty($this->slug_es)) {
            $this->slug_es = $this->generarSlug($this->nom_es);
        }

        // Assegurar unicitat dels slugs
        $this->slug_ca = $this->makeUniqueSlug($this->slug_ca, 'ca');
        $this->slug_es = $this->makeUniqueSlug($this->slug_es, 'es');

        $sql = "INSERT INTO {$this->table}
                (nom_ca, nom_es, slug_ca, slug_es, descripcio_ca, descripcion_es, ordre, activa)
                VALUES
                (:nom_ca, :nom_es, :slug_ca, :slug_es, :descripcio_ca, :descripcion_es, :ordre, :activa)";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':nom_ca', $this->nom_ca);
        $stmt->bindParam(':nom_es', $this->nom_es);
        $stmt->bindParam(':slug_ca', $this->slug_ca);
        $stmt->bindParam(':slug_es', $this->slug_es);
        $stmt->bindParam(':descripcio_ca', $this->descripcio_ca);
        $stmt->bindParam(':descripcion_es', $this->descripcion_es);
        $stmt->bindParam(':ordre', $this->ordre, PDO::PARAM_INT);
        $stmt->bindParam(':activa', $this->activa, PDO::PARAM_INT);

        if ($stmt->execute()) {
            $this->id_etiqueta = (int)$this->conn->lastInsertId();
            return $this->id_etiqueta;
        }

        return false;
    }

    /**
     * Llegir una etiqueta per ID.
     * Retorna array associatiu o false.
     *
     * @param int $id
     * @return array|false
     */
    public function llegirUn(int $id) {
        $sql = "SELECT * FROM {$this->table} WHERE id_etiqueta = :id LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            // Mapear propietats
            foreach ($row as $k => $v) {
                if (property_exists($this, $k)) $this->{$k} = $v;
            }
            return $row;
        }
        return false;
    }

    /**
     * Llegir totes/varies etiquetes.
     * Retorna PDOStatement per permetre paginació i fetch flexible.
     *
     * @param bool|null $actives null = tots, true = només actives, false = només inactives
     * @param int|null $limit
     * @param int|null $offset
     * @param string $orderBy
     * @param string $direction
     * @return PDOStatement
     */
    public function llegirTots($actives = null, $limit = null, $offset = null, $orderBy = 'ordre', $direction = 'ASC') {
        $allowedOrder = ['ordre','nom_ca','nom_es','data_creacio','data_actualitzacio','id_etiqueta'];
        if (!in_array($orderBy, $allowedOrder)) $orderBy = 'ordre';
        $direction = strtoupper($direction) === 'DESC' ? 'DESC' : 'ASC';

        $sql = "SELECT * FROM {$this->table}";
        $params = [];
        if ($actives !== null) {
            $sql .= " WHERE activa = :activa";
            $params[':activa'] = $actives ? 1 : 0;
        }

        $sql .= " ORDER BY {$orderBy} {$direction}";

        if ($limit !== null) {
            $sql .= " LIMIT :limit";
            if ($offset !== null) $sql .= " OFFSET :offset";
        }

        $stmt = $this->conn->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v, PDO::PARAM_INT);
        }
        if ($limit !== null) {
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
            if ($offset !== null) $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        }

        $stmt->execute();
        return $stmt;
    }

    /**
     * Actualitzar una etiqueta existent.
     * Retorna true/false.
     *
     * @return bool
     */
    public function actualitzar() {
        if (empty($this->id_etiqueta)) return false;

        $errs = $this->validar();
        if (!empty($errs)) return false;

        $this->nom_ca = htmlspecialchars(strip_tags(trim((string)$this->nom_ca)));
        $this->nom_es = htmlspecialchars(strip_tags(trim((string)$this->nom_es)));
        $this->descripcio_ca = $this->descripcio_ca ?? null;
        $this->descripcion_es = $this->descripcion_es ?? null;
        $this->ordre = (int)($this->ordre ?? 0);
        $this->activa = $this->activa ? 1 : 0;

        // Slugs: si buits generar, si donats assegurar unicitat excloent el propi id
        if (empty($this->slug_ca)) {
            $this->slug_ca = $this->generarSlug($this->nom_ca);
        }
        if (empty($this->slug_es)) {
            $this->slug_es = $this->generarSlug($this->nom_es);
        }
        $this->slug_ca = $this->makeUniqueSlug($this->slug_ca, 'ca', $this->id_etiqueta);
        $this->slug_es = $this->makeUniqueSlug($this->slug_es, 'es', $this->id_etiqueta);

        $sql = "UPDATE {$this->table} SET
                    nom_ca = :nom_ca,
                    nom_es = :nom_es,
                    slug_ca = :slug_ca,
                    slug_es = :slug_es,
                    descripcio_ca = :descripcio_ca,
                    descripcion_es = :descripcion_es,
                    ordre = :ordre,
                    activa = :activa
                WHERE id_etiqueta = :id";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':nom_ca', $this->nom_ca);
        $stmt->bindParam(':nom_es', $this->nom_es);
        $stmt->bindParam(':slug_ca', $this->slug_ca);
        $stmt->bindParam(':slug_es', $this->slug_es);
        $stmt->bindParam(':descripcio_ca', $this->descripcio_ca);
        $stmt->bindParam(':descripcion_es', $this->descripcion_es);
        $stmt->bindParam(':ordre', $this->ordre, PDO::PARAM_INT);
        $stmt->bindParam(':activa', $this->activa, PDO::PARAM_INT);
        $stmt->bindParam(':id', $this->id_etiqueta, PDO::PARAM_INT);

        return $stmt->execute();
    }

    /**
     * Eliminar una etiqueta (borrat definitiu).
     * Retorna true/false.
     *
     * @param int $id
     * @return bool
     */
    public function eliminar(int $id) {
        $sql = "DELETE FROM {$this->table} WHERE id_etiqueta = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    /* ===================== Cerca per slug i utilitats ===================== */
    /**
     * Buscar etiqueta per slug (retorna fila o false)
     *
     * @param string $slug
     * @param string $lang 'ca' o 'es'
     * @return array|false
     */
    public function buscarPerSlug(string $slug, string $lang = 'ca') {
        $col = $lang === 'es' ? 'slug_es' : 'slug_ca';
        $sql = "SELECT * FROM {$this->table} WHERE {$col} = :slug LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':slug', $slug);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) return $row;
        return false;
    }

    /**
     * Activar/desactivar una etiqueta.
     *
     * @param int $id
     * @param bool $state
     * @return bool
     */
    public function activarDesactivar(int $id, bool $state): bool {
        $sql = "UPDATE {$this->table} SET activa = :activa WHERE id_etiqueta = :id";
        $stmt = $this->conn->prepare($sql);
        $val = $state ? 1 : 0;
        $stmt->bindParam(':activa', $val, PDO::PARAM_INT);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    /**
     * Comptar etiquetes (opcionalment per estat)
     * @param bool|null $actives
     * @return int
     */
    public function comptar($actives = null): int {
        $sql = "SELECT COUNT(*) FROM {$this->table}";
        $params = [];
        if ($actives !== null) {
            $sql .= " WHERE activa = :activa";
            $params[':activa'] = $actives ? 1 : 0;
        }
        $stmt = $this->conn->prepare($sql);
        if (isset($params[':activa'])) $stmt->bindValue(':activa', $params[':activa'], PDO::PARAM_INT);
        $stmt->execute();
        return (int)$stmt->fetchColumn();
    }

    /**
     * Reordenar etiquetes segons un array associatiu [id => ordre]
     * Retorna true si totes les actualitzacions es realitzen correctament.
     *
     * @param array $orders
     * @return bool
     */
    public function reordenar(array $orders): bool {
        $this->conn->beginTransaction();
        try {
            $sql = "UPDATE {$this->table} SET ordre = :ordre WHERE id_etiqueta = :id";
            $stmt = $this->conn->prepare($sql);
            foreach ($orders as $id => $ordre) {
                $stmt->bindValue(':ordre', (int)$ordre, PDO::PARAM_INT);
                $stmt->bindValue(':id', (int)$id, PDO::PARAM_INT);
                $stmt->execute();
            }
            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollBack();
            return false;
        }
    }

    /**
     * Obtenir llistat simplificat per selects (id i nom segons idioma)
     * @param string $lang 'ca' o 'es'
     * @param bool $onlyActive
     * @return array
     */
    public function getForSelect(string $lang = 'ca', bool $onlyActive = true): array {
        $col = $lang === 'es' ? 'nom_es' : 'nom_ca';
        $sql = "SELECT id_etiqueta, {$col} AS nom FROM {$this->table}";
        if ($onlyActive) $sql .= " WHERE activa = 1";
        $sql .= " ORDER BY ordre ASC, nom ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Cerca etiquetes per nom (cerca parcial, útil per autocompletar)
     * @param string $terme Terme de cerca
     * @param string $lang Idioma de cerca ('ca' o 'es')
     * @param bool $onlyActive Només etiquetes actives
     * @param int $limit Límit de resultats
     * @return array
     */
    public function cercarPerNom(string $terme, string $lang = 'es', bool $onlyActive = true, int $limit = 10): array {
        $col = $lang === 'es' ? 'nom_es' : 'nom_ca';
        $sql = "SELECT id_etiqueta, {$col} AS nom, slug_ca, slug_es FROM {$this->table} 
                WHERE {$col} LIKE :terme";
        
        if ($onlyActive) {
            $sql .= " AND activa = 1";
        }
        
        $sql .= " ORDER BY {$col} ASC LIMIT :limit";
        
        $stmt = $this->conn->prepare($sql);
        $termeCerca = '%' . $terme . '%';
        $stmt->bindParam(':terme', $termeCerca);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtenir etiquetes més populars (per implementar en el futur amb estadístiques d'ús)
     * @param string $lang
     * @param int $limit
     * @return array
     */
    public function getMesPopulars(string $lang = 'es', int $limit = 20): array {
        $col = $lang === 'es' ? 'nom_es' : 'nom_ca';
        // De moment només retorna les etiquetes ordenades per ordre i nom
        // En el futur es podria afegir una taula de relacions article-etiqueta per comptar usos
        $sql = "SELECT id_etiqueta, {$col} AS nom, slug_ca, slug_es FROM {$this->table} 
                WHERE activa = 1
                ORDER BY ordre ASC, {$col} ASC 
                LIMIT :limit";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtenir estadístiques bàsiques de les etiquetes
     * @return array
     */
    public function obtenirEstadistiques(): array {
        $sql = "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN activa = 1 THEN 1 ELSE 0 END) as actives,
                    SUM(CASE WHEN activa = 0 THEN 1 ELSE 0 END) as inactives
                FROM {$this->table}";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return [
            'total' => (int)$result['total'],
            'actives' => (int)$result['actives'],
            'inactives' => (int)$result['inactives']
        ];
    }

}
