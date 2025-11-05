<?php
/**
 * Classe Ressenyes
 *
 * Model per a gestionar ressenyes/valoracions de pacients.
 *
 * Implementa CRUD, validació, moderació, likes/reports i funcions de cerca.
 * Està dissenyada per ser compatible amb la següent estructura de taula (MySQL):
 *
 * CREATE TABLE ressenyes (
 *     id_ressenya INT PRIMARY KEY AUTO_INCREMENT,
 *     nom_pacient VARCHAR(100),
 *     inicials VARCHAR(10),
 *     edat TINYINT UNSIGNED,
 *     titol_ca VARCHAR(150) NOT NULL,
 *     titol_es VARCHAR(150) NOT NULL,
 *     text_ressenya_ca TEXT NOT NULL,
 *     text_ressenya_es TEXT NOT NULL,
 *     puntuacio TINYINT UNSIGNED NOT NULL,
 *     data_terapia DATE,
 *     tipus_terapia ENUM('individual','parella','familiar','online','presencial') DEFAULT 'individual',
 *     estat ENUM('pendent','aprovat','rebutjat') DEFAULT 'pendent',
 *     verificada BOOLEAN DEFAULT FALSE,
 *     autoritzacio_publicacio BOOLEAN DEFAULT TRUE,
 *     mostrar_nom BOOLEAN DEFAULT FALSE,
 *     mostrar_inicials BOOLEAN DEFAULT TRUE,
 *     likes INT DEFAULT 0,
 *     reportada BOOLEAN DEFAULT FALSE,
 *     data_creacio DATETIME DEFAULT CURRENT_TIMESTAMP,
 *     data_aprovacio DATETIME NULL,
 *     data_actualitzacio DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
 * ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
 *
 * Notes:
 * - Aquesta classe no executa la creació de la taula; és responsabilitat del desplegament.
 * - Utilitza PDO per seguretat (prepared statements) i facilita la injecció segura.
 *
 * @author Marc
 * @license MIT (actueu segons la llicència del projecte)
 */

class Ressenyes {
    /** @var \PDO Instància PDO per a accés a base de dades */
    protected $pdo;

    /** @var string Nom de la taula */
    protected $table = 'ressenyes';

    /** @var mixed Últim error (string o array) produït per una operació */
    protected $lastError = null;

    /**
     * Constructor
     *
     * @param \PDO $pdo Connexió PDO ja configurada (charset utf8mb4 recomanat)
     */
    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    /* ----------------------------- Helpers / Validadors ----------------------------- */

    /**
     * Validar dades d'entrada per a una ressenya (creació/actualització)
     * Retorna array amb 'ok' boolean i 'errors' array si n'hi ha.
     *
     * @param array $data
     * @return array
     */
    public function validate(array $data) : array {
        $errors = [];

        // Títols
        if (empty($data['titol_ca']) || mb_strlen($data['titol_ca']) > 150) {
            $errors['titol_ca'] = 'Títol (ca) obligatori i màxim 150 caràcters';
        }
        if (empty($data['titol_es']) || mb_strlen($data['titol_es']) > 150) {
            $errors['titol_es'] = 'Títol (es) obligatori i màxim 150 caràcters';
        }

        // Texts
        if (empty($data['text_ressenya_ca'])) {
            $errors['text_ressenya_ca'] = 'Text en català obligatori';
        }
        if (empty($data['text_ressenya_es'])) {
            $errors['text_ressenya_es'] = 'Text en castellà obligatori';
        }

        // Puntuació 1..5
        if (!isset($data['puntuacio']) || !is_numeric($data['puntuacio']) ) {
            $errors['puntuacio'] = 'Puntuació obligatòria (1-5)';
        } else {
            $p = (int)$data['puntuacio'];
            if ($p < 1 || $p > 5) {
                $errors['puntuacio'] = 'Puntuació ha de ser entre 1 i 5';
            }
        }

        // tipus_terapia valid
        $tipus_valids = ['individual','parella','familiar','online','presencial'];
        if (isset($data['tipus_terapia']) && !in_array($data['tipus_terapia'], $tipus_valids, true)) {
            $errors['tipus_terapia'] = 'Tipus de teràpia invàlid';
        }

        // edat
        if (isset($data['edat'])) {
            $e = (int)$data['edat'];
            if ($e < 0 || $e > 120) {
                $errors['edat'] = 'Edat invàlida';
            }
        }

        // data_terapia (opcional) - acceptem format YYYY-MM-DD
        if (!empty($data['data_terapia'])) {
            $d = $data['data_terapia'];
            $dt = \DateTime::createFromFormat('Y-m-d', $d);
            if (!$dt || $dt->format('Y-m-d') !== $d) {
                $errors['data_terapia'] = 'Data de teràpia invàlida (YYYY-MM-DD)';
            }
        }

        return ['ok' => empty($errors), 'errors' => $errors];
    }

    /**
     * Retorna l'últim error registrat per la classe (o null si no n'hi ha)
     * @return mixed
     */
    public function getLastError() {
        return $this->lastError;
    }

    /* ----------------------------- CRUD ----------------------------- */

    /**
     * Crear una nova ressenya
     *
     * @param array $data Associatiu amb claus que corresponen a les columnes
     * @return int|false Retorna ID creat o false en cas d'error
     */
    public function create(array $data) {
        $valid = $this->validate($data);
        if (!$valid['ok']) {
            $this->lastError = ['error' => 'validation', 'details' => $valid['errors']];
            return false;
        }

        $sql = "INSERT INTO `{$this->table}` (
            pacient_id, nom_pacient, inicials, edat,
            titol_ca, titol_es, text_ressenya_ca, text_ressenya_es,
            puntuacio, data_terapia, tipus_terapia,
            estat, verificada, autoritzacio_publicacio, mostrar_nom, mostrar_inicials,
            likes, reportada
        ) VALUES (
            :pacient_id, :nom_pacient, :inicials, :edat,
            :titol_ca, :titol_es, :text_ressenya_ca, :text_ressenya_es,
            :puntuacio, :data_terapia, :tipus_terapia,
            :estat, :verificada, :autoritzacio_publicacio, :mostrar_nom, :mostrar_inicials,
            :likes, :reportada
        )";

        $stmt = $this->pdo->prepare($sql);

        $params = [
            ':pacient_id' => isset($data['pacient_id']) ? (int)$data['pacient_id'] : null,
            ':nom_pacient' => $data['nom_pacient'] ?? null,
            ':inicials' => $data['inicials'] ?? null,
            ':edat' => isset($data['edat']) ? (int)$data['edat'] : null,
            ':titol_ca' => $data['titol_ca'],
            ':titol_es' => $data['titol_es'],
            ':text_ressenya_ca' => $data['text_ressenya_ca'],
            ':text_ressenya_es' => $data['text_ressenya_es'],
            ':puntuacio' => (int)$data['puntuacio'],
            ':data_terapia' => $data['data_terapia'] ?? null,
            ':tipus_terapia' => $data['tipus_terapia'] ?? 'individual',
            ':estat' => $data['estat'] ?? 'pendent',
            ':verificada' => !empty($data['verificada']) ? 1 : 0,
            ':autoritzacio_publicacio' => isset($data['autoritzacio_publicacio']) ? (int)$data['autoritzacio_publicacio'] : 1,
            ':mostrar_nom' => isset($data['mostrar_nom']) ? (int)$data['mostrar_nom'] : 0,
            ':mostrar_inicials' => isset($data['mostrar_inicials']) ? (int)$data['mostrar_inicials'] : 1,
            ':likes' => isset($data['likes']) ? (int)$data['likes'] : 0,
            ':reportada' => !empty($data['reportada']) ? 1 : 0,
        ];

        if ($stmt->execute($params)) {
            return (int)$this->pdo->lastInsertId();
        }

        $this->lastError = $stmt->errorInfo();
        return false;
    }

    /**
     * Obtenir una ressenya per id
     *
     * @param int $id
     * @return array|false
     */
    public function getById(int $id) {
        $sql = "SELECT * FROM `{$this->table}` WHERE id_ressenya = :id LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: false;
    }

    /**
     * Actualitzar una ressenya (partial update)
     * @param int $id
     * @param array $data
     * @return bool|array Retourna true o array error de validació
     */
    public function update(int $id, array $data) {
        // Validem només camp a camp quan sigui necessari
        if (isset($data['titol_ca']) || isset($data['titol_es']) || isset($data['puntuacio']) || isset($data['text_ressenya_ca']) || isset($data['text_ressenya_es'])) {
            $valid = $this->validate(array_merge($this->getById($id) ?: [], $data));
            if (!$valid['ok']) {
                $this->lastError = ['error' => 'validation', 'details' => $valid['errors']];
                return false;
            }
        }

        $fields = [];
        $params = [':id' => $id];

    $allowed = ['pacient_id','nom_pacient','inicials','edat','titol_ca','titol_es','text_ressenya_ca','text_ressenya_es','puntuacio','data_terapia','tipus_terapia','estat','verificada','autoritzacio_publicacio','mostrar_nom','mostrar_inicials','likes','reportada','data_aprovacio'];

        foreach ($allowed as $col) {
            if (array_key_exists($col, $data)) {
                $fields[] = "`$col` = :$col";
                $params[":$col"] = is_bool($data[$col]) ? (int)$data[$col] : $data[$col];
            }
        }

    if (empty($fields)) return false; // res a actualitzar

        $sql = "UPDATE `{$this->table}` SET " . implode(', ', $fields) . " WHERE id_ressenya = :id";
        $stmt = $this->pdo->prepare($sql);
        $ok = $stmt->execute($params);
        if (!$ok) $this->lastError = $stmt->errorInfo();
        return $ok;
    }

    /**
     * Eliminar una ressenya
     * @param int $id
     * @return bool
     */
    public function delete(int $id) {
        $sql = "DELETE FROM `{$this->table}` WHERE id_ressenya = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }

    /* ----------------------------- Moderació / Estat ----------------------------- */

    /**
     * Canvia l'estat d'una ressenya (pendent/aprovat/rebutjat) i registra data_aprovacio si s'aprova
     * @param int $id
     * @param string $estat
     * @return bool
     */
    public function setEstat(int $id, string $estat) {
        $valids = ['pendent','aprovat','rebutjat'];
        if (!in_array($estat, $valids, true)) return false;

        $sql = "UPDATE `{$this->table}` SET estat = :estat";
        $params = [':estat' => $estat, ':id' => $id];

        if ($estat === 'aprovat') {
            $sql .= ", data_aprovacio = :data_aprovacio";
            $params[':data_aprovacio'] = date('Y-m-d H:i:s');
        }

        $sql .= " WHERE id_ressenya = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Marcar o desmarcar com verificada
     */
    public function setVerificada(int $id, bool $verificada) {
        $sql = "UPDATE `{$this->table}` SET verificada = :v WHERE id_ressenya = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([':v' => $verificada ? 1 : 0, ':id' => $id]);
    }

    /* ----------------------------- Engagment ----------------------------- */

    /**
     * Incrementar likes (safe, atomic)
     * @param int $id
     * @param int $by
     * @return bool
     */
    public function addLike(int $id, int $by = 1) {
        $sql = "UPDATE `{$this->table}` SET likes = likes + :by WHERE id_ressenya = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([':by' => max(1, (int)$by), ':id' => $id]);
    }

    /**
     * Marcar com a reportada
     */
    public function setReportada(int $id, bool $reportada = true) {
        $sql = "UPDATE `{$this->table}` SET reportada = :r WHERE id_ressenya = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([':r' => $reportada ? 1 : 0, ':id' => $id]);
    }

    /* ----------------------------- Cerques / Llistats ----------------------------- */

    /**
     * Llistar ressenyes amb filtres bàsics i paginació
     *
     * Opcions possibles a $opts:
     *  - page, per_page, estat, tipus_terapia, verificada, min_puntuacio, max_puntuacio, order_by, order_dir
     *
     * @param array $opts
     * @return array [data => [...], total => int]
     */
    public function list(array $opts = []) : array {
        $page = max(1, (int)($opts['page'] ?? 1));
        $per_page = max(1, min(200, (int)($opts['per_page'] ?? 20)));
        $offset = ($page - 1) * $per_page;

        $where = [];
        $params = [];

        if (!empty($opts['estat'])) { $where[] = 'estat = :estat'; $params[':estat'] = $opts['estat']; }
        if (!empty($opts['tipus_terapia'])) { $where[] = 'tipus_terapia = :tt'; $params[':tt'] = $opts['tipus_terapia']; }
        if (isset($opts['verificada'])) { $where[] = 'verificada = :v'; $params[':v'] = $opts['verificada'] ? 1 : 0; }
        if (!empty($opts['min_puntuacio'])) { $where[] = 'puntuacio >= :minp'; $params[':minp'] = (int)$opts['min_puntuacio']; }
        if (!empty($opts['max_puntuacio'])) { $where[] = 'puntuacio <= :maxp'; $params[':maxp'] = (int)$opts['max_puntuacio']; }

        $where_sql = $where ? 'WHERE '.implode(' AND ', $where) : '';

        $order_by = 'data_creacio';
        $order_dir = 'DESC';
        if (!empty($opts['order_by'])) $order_by = preg_replace('/[^a-zA-Z0-9_]/', '', $opts['order_by']);
        if (!empty($opts['order_dir']) && in_array(strtoupper($opts['order_dir']), ['ASC','DESC'])) $order_dir = strtoupper($opts['order_dir']);

        $sql = "SELECT SQL_CALC_FOUND_ROWS * FROM `{$this->table}` $where_sql ORDER BY {$order_by} {$order_dir} LIMIT :limit OFFSET :offset";
        $stmt = $this->pdo->prepare($sql);
        foreach ($params as $k => $v) $stmt->bindValue($k, $v);
        $stmt->bindValue(':limit', (int)$per_page, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $total = (int)$this->pdo->query('SELECT FOUND_ROWS()')->fetchColumn();

        return ['data' => $data, 'total' => $total, 'page' => $page, 'per_page' => $per_page];
    }

    /**
     * Cerca de text simple (FULLTEXT) en català o castellà
     * @param string $q
     * @param string $lang 'ca'|'es'
     * @param array $opts
     * @return array
     */
    public function searchFulltext(string $q, string $lang = 'ca', array $opts = []) : array {
        $field = $lang === 'es' ? 'text_ressenya_es' : 'text_ressenya_ca';
        $sql = "SELECT *, MATCH($field) AGAINST(:q IN NATURAL LANGUAGE MODE) AS score
                FROM `{$this->table}`
                WHERE MATCH($field) AGAINST(:q IN NATURAL LANGUAGE MODE)
                AND estat = 'aprovat'
                ORDER BY score DESC
                LIMIT :limit";

        $limit = (int)($opts['limit'] ?? 20);
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':q', $q);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /* ----------------------------- Útils ----------------------------- */

    /**
     * Comptar ressenyes per estat o altres filtres senzills
     * @param array $filters
     * @return int
     */
    public function count(array $filters = []) : int {
        $where = [];
        $params = [];
        if (!empty($filters['estat'])) { $where[] = 'estat = :estat'; $params[':estat'] = $filters['estat']; }
        if (isset($filters['verificada'])) { $where[] = 'verificada = :v'; $params[':v'] = $filters['verificada'] ? 1 : 0; }
        $where_sql = $where ? 'WHERE '.implode(' AND ', $where) : '';
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM `{$this->table}` $where_sql");
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }

}

// EOF
