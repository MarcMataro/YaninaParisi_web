<?php
require_once __DIR__ . '/connexio.php';

/**
 * Class VideoReviews
 * 
 * Gestiona les ressenyes de vídeo (testimonis de YouTube).
 * Implementa el patró Singleton per garantir una única instància de gestió.
 * 
 * Estructura de la taula `video_reviews`:
 * - id: INT UNSIGNED AI
 * - youtube_url: VARCHAR(255)
 * - title_ca: VARCHAR(150)
 * - title_es: VARCHAR(150)
 * - position: INT UNSIGNED (default 0)
 * - is_public: TINYINT(1) (default 1)
 * - created_at, updated_at: TIMESTAMP
 */
class VideoReviews {
    
    /**
     * @var VideoReviews|null Instància única de la classe
     */
    private static $instance = null;

    /**
     * @var PDO Connexió a la base de dades
     */
    private $pdo;

    /**
     * @var string Nom de la taula 
     */
    private $table = 'video_reviews';

    /**
     * Constructor privat per evitar instanciació directa (Pattern Singleton).
     * Obté automàticament la connexió a la BD a través de la classe Connexio.
     */
    private function __construct() {
        try {
            $connexio = Connexio::getInstance();
            $this->pdo = $connexio->getConnexio();
        } catch (Exception $e) {
            die('Error connectant a la BD des de VideoReviews: ' . $e->getMessage());
        }
    }

    /**
     * Retorna la instància única de la classe (Singleton).
     * 
     * @return VideoReviews
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Crea una nova ressenya de vídeo.
     * 
     * @param string $youtube_url URL completa o ID del vídeo.
     * @param string $title_ca Títol en català.
     * @param string $title_es Títol en castellà.
     * @param int|null $position Ordre (opcional). Si és null, es posa al final.
     * @param bool $is_public Visible públicament (per defecte true).
     * @return int|false ID de la nova inserció o false en cas d'error.
     */
    public function create(string $youtube_url, string $title_ca, string $title_es, ?int $position = null, bool $is_public = true) {
        if ($position === null) {
            $position = $this->getNextPosition();
        }

        $sql = "INSERT INTO {$this->table} (youtube_url, title_ca, title_es, position, is_public, created_at) 
                VALUES (:url, :t_ca, :t_es, :pos, :public, NOW())";
        
        try {
            $stmt = $this->pdo->prepare($sql);
            $res = $stmt->execute([
                ':url' => $youtube_url,
                ':t_ca' => $title_ca,
                ':t_es' => $title_es,
                ':pos' => $position,
                ':public' => $is_public ? 1 : 0
            ]);
            
            if ($res) {
                return (int)$this->pdo->lastInsertId();
            }
            return false;
        } catch (PDOException $e) {
            error_log("VideoReviews::create Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obté una entrada per ID.
     * 
     * @param int $id
     * @return array|false Dades del vídeo o false si no existeix.
     */
    public function getById(int $id) {
        $sql = "SELECT * FROM {$this->table} WHERE id = :id LIMIT 1";
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':id' => $id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("VideoReviews::getById Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Llista tots els vídeos, ordenats per posició.
     * 
     * @param bool $onlyPublic Si és true, només retorna els marcats com a públics.
     * @return array Llista de vídeos.
     */
    public function getAll(bool $onlyPublic = false): array {
        $sql = "SELECT * FROM {$this->table}";
        if ($onlyPublic) {
            $sql .= " WHERE is_public = 1";
        }
        $sql .= " ORDER BY position ASC, created_at DESC";

        try {
            $stmt = $this->pdo->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("VideoReviews::getAll Error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Actualitza les dades bàsiques d'un vídeo.
     * 
     * @param int $id ID del vídeo
     * @param array $data Array associatiu amb les claus a modificar (youtube_url, title_ca, title_es, position, is_public).
     * @return bool True si s'ha actualitzat correctament.
     */
    public function update(int $id, array $data): bool {
        $fields = [];
        $params = [':id' => $id];

        if (isset($data['youtube_url'])) {
            $fields[] = 'youtube_url = :url';
            $params[':url'] = $data['youtube_url'];
        }
        if (isset($data['title_ca'])) {
            $fields[] = 'title_ca = :t_ca';
            $params[':t_ca'] = $data['title_ca'];
        }
        if (isset($data['title_es'])) {
            $fields[] = 'title_es = :t_es';
            $params[':t_es'] = $data['title_es'];
        }
        if (isset($data['position'])) {
            $fields[] = 'position = :pos';
            $params[':pos'] = $data['position'];
        }
        if (isset($data['is_public'])) {
            $fields[] = 'is_public = :public';
            $params[':public'] = $data['is_public'] ? 1 : 0;
        }

        if (empty($fields)) {
            return false;
        }

        $sql = "UPDATE {$this->table} SET " . implode(', ', $fields) . " WHERE id = :id";

        try {
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            error_log("VideoReviews::update Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Esborra un vídeo per ID.
     * 
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool {
        $sql = "DELETE FROM {$this->table} WHERE id = :id";
        try {
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([':id' => $id]);
        } catch (PDOException $e) {
            error_log("VideoReviews::delete Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Canvia la visibilitat d'un vídeo (toggle).
     * 
     * @param int $id
     * @return bool Nou estat o false en error.
     */
    public function toggleVisibility(int $id) {
        // Obtenir estat actual
        $current = $this->getById($id);
        if (!$current) return false;

        $newState = $current['is_public'] == 1 ? 0 : 1;
        if ($this->update($id, ['is_public' => $newState])) {
            return (bool)$newState;
        }
        return false;
    }

    /**
     * Reordena posicions. Pot rebre un array [id => posicio, id => posicio].
     * 
     * @param array $positions Array associatiu id => nova_posicio
     * @return bool
     */
    public function reorder(array $positions): bool {
        try {
            $this->pdo->beginTransaction();
            $sql = "UPDATE {$this->table} SET position = :pos WHERE id = :id";
            $stmt = $this->pdo->prepare($sql);

            foreach ($positions as $id => $pos) {
                $stmt->execute([':pos' => $pos, ':id' => $id]);
            }

            $this->pdo->commit();
            return true;
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            error_log("VideoReviews::reorder Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Helper: Calcula la següent posició disponible (final de la llista).
     * 
     * @return int
     */
    private function getNextPosition(): int {
        $sql = "SELECT MAX(position) as max_pos FROM {$this->table}";
        try {
            $stmt = $this->pdo->query($sql);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return ($row['max_pos'] !== null) ? (int)$row['max_pos'] + 1 : 0;
        } catch (PDOException $e) {
            return 0;
        }
    }

    /**
     * Helper: Extreu l'ID del vídeo de YouTube des de diverses formes d'URL.
     * Útil pe guardar només l'ID o per generar thumbnails.
     * 
     * @param string $url
     * @return string|null ID de YouTube o null si no es troba.
     */
    public function extractYoutubeId(string $url): ?string {
        $pattern = '/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/i';
        if (preg_match($pattern, $url, $matches)) {
            return $matches[1];
        }
        // Si l'string ja té 11 caràcters i no és URL, assumim que és l'ID
        if (strlen($url) === 11 && !filter_var($url, FILTER_VALIDATE_URL)) {
            return $url;
        }
        return null;
    }
}
