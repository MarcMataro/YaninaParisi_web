<?php
/**
 * Classe Faq
 *
 * Gestiona les operacions sobre la taula `faqs` segons l'estructura proporcionada.
 * Implementa operacions CRUD, cerca filtrada, increment d'estadístiques i helpers
 * per a la generació de slugs i validacions bàsiques.
 *
 * Comentaris professionals:
 * - Aquest model assumeix que la connexió PDO està configurada amb utf8mb4.
 * - Les operacions modifiquen exclusivament camps previstos a la taula per prevenir
 *   injeccions i garantir coherència amb l'esquema SQL proporcionat.
 * - Les operacions que escriuen dades retornen l'ID o boolean segons sigui adequat.
 *
 * @author Marc Mataró
 * @version 1.0
 * @date 2025-11-01
 */

class Faq {

    /** @var PDO Instància de connexió PDO */
    private $conn;

    /** @var string Nom de la taula */
    private $table = 'faqs';

    // ==========================
    // Propietats que reflecteixen la taula
    // ==========================
    public $id_faq;

    // 1. Pregunta i resposta bilingüe
    public $pregunta_ca;
    public $pregunta_es;
    public $resposta_ca;
    public $resposta_es;

    // 2. Categoria i organització
    public $categoria = 'general';
    public $ordre = 0;

    // 3. Estat i visibilitat
    public $activa = true;
    public $destacada = false;

    // 4. SEO per a cada FAQ
    public $meta_title_ca;
    public $meta_title_es;
    public $meta_description_ca;
    public $meta_description_es;
    public $slug_ca;
    public $slug_es;

    // 5. Estadístiques
    public $vegades_visualitzada = 0;
    public $vegades_util = 0;

    // 6. Dates (s'omplen des de la BD automàticament si no s'envien)
    public $data_creacio;
    public $data_actualitzacio;

    // 7. Usuari creador
    public $id_usuario;


    // ==========================
    // Constructor
    // ==========================
    /**
     * Constructor
     *
     * @param PDO $db Connexió PDO ja inicialitzada (s'ha de passar Connexio::getInstance()->getConnexio() o similar)
     * @throws InvalidArgumentException
     */
    public function __construct($db) {
        if (!$db instanceof PDO) {
            throw new InvalidArgumentException("La connexió a la base de dades ha de ser una instància de PDO");
        }
        $this->conn = $db;
    }


    // ==========================
    // Mètodes helpers
    // ==========================

    /**
     * Neteja text per a emmagatzematge
     *
     * @param string|null $text
     * @return string|null
     */
    private function sanitize($text) {
        if ($text === null) return null;
        return trim($text);
    }

    /**
     * Genera un slug amistós (latinitzat) i limitat a la longitud de la BD
     * Manté caràcters segurs per URL.
     *
     * @param string $text
     * @param int $maxLen
     * @return string
     */
    public function generarSlug($text, $maxLen = 120) {
        $text = html_entity_decode($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        // substituir espais i caràcters no alfanumèrics
        $slug = preg_replace('~[^\r\na-zA-Z0-9À-ÿ]+~u', '-', $text);
        // treure guions duplicats
        $slug = preg_replace('~-+~', '-', $slug);
        $slug = trim($slug, '-');
        $slug = mb_substr($slug, 0, $maxLen, 'UTF-8');
        $slug = strtolower($slug);
        // fallback si queda buit
        if ($slug === '') $slug = 'faq-' . time();
        return $slug;
    }

    /**
     * Assegura que el slug és únic (afegeix sufix numèric si cal)
     *
     * @param string $slugBase
     * @param string $campo ('slug_ca'|'slug_es')
     * @param int|null $excludeId ID a excloure (per a actualitzacions)
     * @return string Slug únic
     */
    private function ensureUniqueSlug($slugBase, $campo = 'slug_es', $excludeId = null) {
        $slug = $slugBase;
        $i = 1;
        while (true) {
            $sql = "SELECT COUNT(*) FROM {$this->table} WHERE {$campo} = :slug" . ($excludeId ? " AND id_faq != :id" : '');
            $params = [':slug' => $slug];
            if ($excludeId) $params[':id'] = $excludeId;
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($params);
            $count = (int) $stmt->fetchColumn();
            if ($count === 0) break;
            $slug = mb_substr($slugBase, 0, 110, 'UTF-8') . '-' . $i; // deixar marge per al sufix
            $i++;
        }
        return $slug;
    }


    // ==========================
    // Validació
    // ==========================
    /**
     * Validar camps mínims abans d'insertar/actualitzar
     *
     * @return bool True si és vàlid
     */
    public function validar() {
        // Exigir preguntes i respostes en ambdues llengües
        if (empty($this->pregunta_ca) || empty($this->pregunta_es) || empty($this->resposta_ca) || empty($this->resposta_es)) {
            return false;
        }
        // Categoria valida
        $validCats = ['general', 'terapia', 'tarifes', 'tecnica', 'primera_visita', 'urgencies'];
        if (!in_array($this->categoria, $validCats, true)) {
            $this->categoria = 'general';
        }
        // Ordre ha de ser int
        $this->ordre = (int) $this->ordre;
        $this->activa = (bool) $this->activa;
        $this->destacada = (bool) $this->destacada;

        return true;
    }


    // ==========================
    // CRUD
    // ==========================

    /**
     * Crear una FAQ nova
     *
     * @return int|false ID creat o false en cas d'error
     */
    public function crear() {
        if (!$this->validar()) return false;

        // Sanitització bàsica
        $this->pregunta_ca = $this->sanitize($this->pregunta_ca);
        $this->pregunta_es = $this->sanitize($this->pregunta_es);
        $this->resposta_ca = $this->sanitize($this->resposta_ca);
        $this->resposta_es = $this->sanitize($this->resposta_es);

        // Generar slugs si no hi són
        if (empty($this->slug_ca)) {
            $this->slug_ca = $this->generarSlug($this->pregunta_ca);
        }
        if (empty($this->slug_es)) {
            $this->slug_es = $this->generarSlug($this->pregunta_es);
        }

        // Assegurar unicitat
        $this->slug_ca = $this->ensureUniqueSlug($this->slug_ca, 'slug_ca');
        $this->slug_es = $this->ensureUniqueSlug($this->slug_es, 'slug_es');

        $sql = "INSERT INTO {$this->table} (
                    pregunta_ca, pregunta_es, resposta_ca, resposta_es,
                    categoria, ordre, activa, destacada,
                    meta_title_ca, meta_title_es, meta_description_ca, meta_description_es,
                    slug_ca, slug_es, vegades_visualitzada, vegades_util, id_usuario
                ) VALUES (
                    :pregunta_ca, :pregunta_es, :resposta_ca, :resposta_es,
                    :categoria, :ordre, :activa, :destacada,
                    :meta_title_ca, :meta_title_es, :meta_description_ca, :meta_description_es,
                    :slug_ca, :slug_es, :vegades_visualitzada, :vegades_util, :id_usuario
                )";

        $params = [
            ':pregunta_ca' => $this->pregunta_ca,
            ':pregunta_es' => $this->pregunta_es,
            ':resposta_ca' => $this->resposta_ca,
            ':resposta_es' => $this->resposta_es,
            ':categoria' => $this->categoria,
            ':ordre' => $this->ordre,
            ':activa' => $this->activa ? 1 : 0,
            ':destacada' => $this->destacada ? 1 : 0,
            ':meta_title_ca' => $this->meta_title_ca,
            ':meta_title_es' => $this->meta_title_es,
            ':meta_description_ca' => $this->meta_description_ca,
            ':meta_description_es' => $this->meta_description_es,
            ':slug_ca' => $this->slug_ca,
            ':slug_es' => $this->slug_es,
            ':vegades_visualitzada' => (int)$this->vegades_visualitzada,
            ':vegades_util' => (int)$this->vegades_util,
            ':id_usuario' => $this->id_usuario
        ];

        $stmt = $this->conn->prepare($sql);
        if ($stmt->execute($params)) {
            $this->id_faq = $this->conn->lastInsertId();
            return $this->id_faq;
        }

        return false;
    }

    /**
     * Actualitzar una FAQ existent
     *
     * @return bool True si l'actualització té èxit
     */
    public function actualitzar() {
        if (empty($this->id_faq)) return false;
        if (!$this->validar()) return false;

        // Regenerar i assegurar slugs (sense sobrescriure slugs manuals si ja existeixen)
        if (empty($this->slug_ca)) $this->slug_ca = $this->generarSlug($this->pregunta_ca);
        if (empty($this->slug_es)) $this->slug_es = $this->generarSlug($this->pregunta_es);
        $this->slug_ca = $this->ensureUniqueSlug($this->slug_ca, 'slug_ca', $this->id_faq);
        $this->slug_es = $this->ensureUniqueSlug($this->slug_es, 'slug_es', $this->id_faq);

        $sql = "UPDATE {$this->table} SET
                    pregunta_ca = :pregunta_ca,
                    pregunta_es = :pregunta_es,
                    resposta_ca = :resposta_ca,
                    resposta_es = :resposta_es,
                    categoria = :categoria,
                    ordre = :ordre,
                    activa = :activa,
                    destacada = :destacada,
                    meta_title_ca = :meta_title_ca,
                    meta_title_es = :meta_title_es,
                    meta_description_ca = :meta_description_ca,
                    meta_description_es = :meta_description_es,
                    slug_ca = :slug_ca,
                    slug_es = :slug_es
                WHERE id_faq = :id_faq";

        $params = [
            ':pregunta_ca' => $this->pregunta_ca,
            ':pregunta_es' => $this->pregunta_es,
            ':resposta_ca' => $this->resposta_ca,
            ':resposta_es' => $this->resposta_es,
            ':categoria' => $this->categoria,
            ':ordre' => $this->ordre,
            ':activa' => $this->activa ? 1 : 0,
            ':destacada' => $this->destacada ? 1 : 0,
            ':meta_title_ca' => $this->meta_title_ca,
            ':meta_title_es' => $this->meta_title_es,
            ':meta_description_ca' => $this->meta_description_ca,
            ':meta_description_es' => $this->meta_description_es,
            ':slug_ca' => $this->slug_ca,
            ':slug_es' => $this->slug_es,
            ':id_faq' => $this->id_faq
        ];

        $stmt = $this->conn->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Eliminar una FAQ
     *
     * @param int|null $id ID de la FAQ (si no es passa, usa $this->id_faq)
     * @return bool True si s'ha eliminat
     */
    public function eliminar($id = null) {
        $id = $id ?? $this->id_faq;
        if (empty($id)) return false;
        $sql = "DELETE FROM {$this->table} WHERE id_faq = :id";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }


    // ==========================
    // Consultes i cerques
    // ==========================

    /**
     * Obtenir una FAQ per ID
     *
     * @param int $id
     * @return array|false Associatiu amb camps o false
     */
    public function obtenirPerId($id) {
        $sql = "SELECT * FROM {$this->table} WHERE id_faq = :id LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        if ($stmt->execute([':id' => $id])) {
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }
        return false;
    }

    /**
     * Obtenir una FAQ per slug (idioma específic)
     *
     * @param string $slug
     * @param string $idioma 'ca'|'es'
     * @return array|false
     */
    public function obtenirPerSlug($slug, $idioma = 'es') {
        $campo = $idioma === 'ca' ? 'slug_ca' : 'slug_es';
        $sql = "SELECT * FROM {$this->table} WHERE {$campo} = :slug LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        if ($stmt->execute([':slug' => $slug])) {
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }
        return false;
    }

    /**
     * Llistar FAQs amb filtres senzills
     *
     * @param array $opts Opcions: categoria, activa (bool|null), destacada (bool|null), id_usuario, limit, offset
     * @return array|false Array de resultats o false
     */
    public function llistar($opts = []) {
        $where = [];
        $params = [];

        if (!empty($opts['categoria'])) {
            $where[] = 'categoria = :categoria';
            $params[':categoria'] = $opts['categoria'];
        }
        if (isset($opts['activa'])) {
            $where[] = 'activa = :activa';
            $params[':activa'] = $opts['activa'] ? 1 : 0;
        }
        if (isset($opts['destacada'])) {
            $where[] = 'destacada = :destacada';
            $params[':destacada'] = $opts['destacada'] ? 1 : 0;
        }
        if (!empty($opts['id_usuario'])) {
            $where[] = 'id_usuario = :id_usuario';
            $params[':id_usuario'] = $opts['id_usuario'];
        }

        $whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        // Orden per categoria -> ordre -> id
        $sql = "SELECT * FROM {$this->table} {$whereSql} ORDER BY categoria ASC, ordre ASC, id_faq ASC";
        if (!empty($opts['limit'])) {
            $limit = (int)$opts['limit'];
            $offset = (int)($opts['offset'] ?? 0);
            $sql .= " LIMIT {$offset}, {$limit}";
        }

        $stmt = $this->conn->prepare($sql);
        if ($stmt->execute($params)) {
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        return false;
    }


    // ==========================
    // Estadístiques helpers
    // ==========================

    /**
     * Incrementar el comptador de visualitzacions
     *
     * @param int|null $id
     * @return bool
     */
    public function incrementarVisualitzacions($id = null) {
        $id = $id ?? $this->id_faq;
        if (empty($id)) return false;
    $sql = "UPDATE {$this->table} SET vegades_visualitzada = vegades_visualitzada + 1 WHERE id_faq = :id";
    $stmt = $this->conn->prepare($sql);
    return (bool) $stmt->execute([':id' => $id]);
    }

    /**
     * Incrementar el comptador de "útil"
     *
     * @param int|null $id
     * @return bool
     */
    public function incrementarUtil($id = null) {
        $id = $id ?? $this->id_faq;
        if (empty($id)) return false;
    $sql = "UPDATE {$this->table} SET vegades_util = vegades_util + 1 WHERE id_faq = :id";
    $stmt = $this->conn->prepare($sql);
    return (bool) $stmt->execute([':id' => $id]);
    }


    // ==========================
    // Operacions d'estat
    // ==========================

    /**
     * Canviar l'estat 'activa'
     *
     * @param int|null $id
     * @param bool|null $valor Si es passa null, fa toggle
     * @return bool
     */
    public function toggleActiva($id = null, $valor = null) {
        $id = $id ?? $this->id_faq;
        if (empty($id)) return false;
        if ($valor === null) {
            // Obtenir valor actual
            $fila = $this->obtenirPerId($id);
            $valor = empty($fila) ? 0 : (int)!((bool)$fila['activa']);
        } else {
            $valor = $valor ? 1 : 0;
        }
    $sql = "UPDATE {$this->table} SET activa = :valor WHERE id_faq = :id";
    $stmt = $this->conn->prepare($sql);
    return (bool) $stmt->execute([':valor' => $valor, ':id' => $id]);
    }

    /**
     * Canviar l'estat 'destacada'
     *
     * @param int|null $id
     * @param bool|null $valor
     * @return bool
     */
    public function toggleDestacada($id = null, $valor = null) {
        $id = $id ?? $this->id_faq;
        if (empty($id)) return false;
        if ($valor === null) {
            $fila = $this->obtenirPerId($id);
            $valor = empty($fila) ? 0 : (int)!((bool)$fila['destacada']);
        } else {
            $valor = $valor ? 1 : 0;
        }
    $sql = "UPDATE {$this->table} SET destacada = :valor WHERE id_faq = :id";
    $stmt = $this->conn->prepare($sql);
    return (bool) $stmt->execute([':valor' => $valor, ':id' => $id]);
    }

}

?>
