<?php
/**
 * Classe Entrada
 *
 * Gestiona les entrades del blog segons l'estructura de dades proporcionada.
 * Implementa operacions CRUD completes, gestió multilingüe, SEO, estadístiques i cerca avançada.
 *
 * Estructura esperada de la taula `blog_entrades`:
 * - Contingut multilingüe (CA/ES) amb títols, slugs, contingut i resums
 * - Sistema d'estats: esborrany, revisio, publicat, programat, arxivat
 * - Gestió d'imatges i multimèdia amb suport per galeries
 * - SEO complet per cada idioma (meta títol, descripció, keywords)
 * - Estadístiques (visualitzacions, compartits, temps de lectura)
 * - Sistema de dates avançat amb programació de publicació
 * - Índexs optimitzats per cerca i rendiment
 *
 * @author Marc Mataró
 * @version 1.0.0
 * @date 2025-10-11
 */

class Entrada {

    /** @var PDO Instància de connexió a la base de dades */
    private $conn;

    /** @var string Nom de la taula */
    private $table = 'blog_entrades';

    /* ======== Propietats del model (mapegen camp a propietat) ======== */
    
    // Identificador únic
    public $id_entrada;
    
    // Títols i contingut multilingüe
    public $titol_ca;
    public $titol_es;
    public $slug_ca;
    public $slug_es;
    public $contingut_ca;
    public $contingut_es;
    public $resum_ca;
    public $resum_es;
    
    // Estat i visibilitat
    public $estat = 'esborrany';
    public $data_publicacio;
    public $data_arxivat;
    public $visible = 1;
    
    // Imatges i multimèdia
    public $imatge_portada;
    public $alt_imatge_ca;
    public $alt_imatge_es;
    public $galeria_imatges;
    
    // SEO per a cada idioma
    public $meta_title_ca;
    public $meta_title_es;
    public $meta_description_ca;
    public $meta_description_es;
    public $meta_keywords_ca;
    public $meta_keywords_es;
    
    // Autoria i propietat
    public $id_autor;
    public $temps_lectura_ca;
    public $temps_lectura_es;
    
    // Estadístiques i engagement
    public $visualitzacions = 0;
    public $compartits = 0;
    public $comentaris_actius = 1;
    
    // Dates del sistema
    public $data_creacio;
    public $data_actualitzacio;
    public $data_modificacio;

    /* ===================== Constants per estats ===================== */
    const ESTAT_ESBORRANY = 'esborrany';
    const ESTAT_REVISIO = 'revisio';
    const ESTAT_PUBLICAT = 'publicat';
    const ESTAT_PROGRAMAT = 'programat';
    const ESTAT_ARXIVAT = 'arxivat';

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
        // Convertir a minúscules
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

        // Treure guions inicials/finals i duplicats
        $text = trim($text, '-');
        $text = preg_replace('/-+/', '-', $text);

        return $text === '' ? 'entrada' : $text;
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
            $sql .= " AND id_entrada != :exclude";
        }
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':slug', $slug);
        if ($excludeId) $stmt->bindParam(':exclude', $excludeId, PDO::PARAM_INT);
        $stmt->execute();
        return (int)$stmt->fetchColumn() > 0;
    }

    /**
     * Calcula el temps de lectura estimat basant-se en el nombre de paraules.
     * Assumeix una velocitat de lectura de 200 paraules per minut.
     *
     * @param string $contingut
     * @return int temps en minuts
     */
    public function calcularTempsLectura(string $contingut): int {
        $paraules = str_word_count(strip_tags($contingut));
        $minuts = ceil($paraules / 200);
        return max(1, $minuts); // Mínim 1 minut
    }

    /**
     * Valida si un estat és vàlid segons les constants definides.
     *
     * @param string $estat
     * @return bool
     */
    public function estatValid(string $estat): bool {
        $estatsValids = [
            self::ESTAT_ESBORRANY,
            self::ESTAT_REVISIO,
            self::ESTAT_PUBLICAT,
            self::ESTAT_PROGRAMAT,
            self::ESTAT_ARXIVAT
        ];
        return in_array($estat, $estatsValids);
    }

    /**
     * Processa i valida la galeria d'imatges (JSON).
     *
     * @param array|string|null $galeria
     * @return string|null JSON vàlid o null
     */
    public function processarGaleriaImatges($galeria): ?string {
        if (is_null($galeria) || empty($galeria)) {
            return null;
        }
        
        if (is_string($galeria)) {
            // Validar que sigui JSON vàlid
            $decoded = json_decode($galeria, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return null;
            }
            return $galeria;
        }
        
        if (is_array($galeria)) {
            return json_encode($galeria, JSON_UNESCAPED_UNICODE);
        }
        
        return null;
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

        // Títols obligatoris
        if (empty(trim((string)$this->titol_ca))) {
            $errors[] = 'El títol en català és obligatori';
        }
        if (empty(trim((string)$this->titol_es))) {
            $errors[] = 'El títol en castellà és obligatori';
        }

        // Contingut obligatori
        if (empty(trim((string)$this->contingut_ca))) {
            $errors[] = 'El contingut en català és obligatori';
        }
        if (empty(trim((string)$this->contingut_es))) {
            $errors[] = 'El contingut en castellà és obligatori';
        }

        // Autor obligatori
        if (empty($this->id_autor)) {
            $errors[] = 'L\'autor és obligatori';
        }

        // Validar longitud dels títols
        if (strlen(trim((string)$this->titol_ca)) > 255) {
            $errors[] = 'El títol en català no pot superar els 255 caràcters';
        }
        if (strlen(trim((string)$this->titol_es)) > 255) {
            $errors[] = 'El títol en castellà no pot superar els 255 caràcters';
        }

        // Validar longitud dels meta títols SEO
        if (!empty($this->meta_title_ca) && strlen($this->meta_title_ca) > 60) {
            $errors[] = 'El meta títol (CA) no pot superar els 60 caràcters';
        }
        if (!empty($this->meta_title_es) && strlen($this->meta_title_es) > 60) {
            $errors[] = 'El meta títol (ES) no pot superar els 60 caràcters';
        }

        // Validar longitud de les meta descripcions SEO
        if (!empty($this->meta_description_ca) && strlen($this->meta_description_ca) > 160) {
            $errors[] = 'La meta descripció (CA) no pot superar els 160 caràcters';
        }
        if (!empty($this->meta_description_es) && strlen($this->meta_description_es) > 160) {
            $errors[] = 'La meta descripció (ES) no pot superar els 160 caràcters';
        }

        // Validar longitud dels alt d'imatge
        if (!empty($this->alt_imatge_ca) && strlen($this->alt_imatge_ca) > 125) {
            $errors[] = 'El text alternatiu (CA) no pot superar els 125 caràcters';
        }
        if (!empty($this->alt_imatge_es) && strlen($this->alt_imatge_es) > 125) {
            $errors[] = 'El text alternatiu (ES) no pot superar els 125 caràcters';
        }

        // Validar estat
        if (!$this->estatValid($this->estat)) {
            $errors[] = 'L\'estat de l\'entrada no és vàlid';
        }

        // Validar slugs si es proporcionen
        if (!empty($this->slug_ca) && !preg_match('/^[a-z0-9\-]+$/', $this->slug_ca)) {
            $errors[] = 'El slug (CA) té un format no vàlid';
        }
        if (!empty($this->slug_es) && !preg_match('/^[a-z0-9\-]+$/', $this->slug_es)) {
            $errors[] = 'El slug (ES) té un format no vàlid';
        }

        // Validar longitud dels slugs
        if (!empty($this->slug_ca) && strlen($this->slug_ca) > 120) {
            $errors[] = 'El slug (CA) no pot superar els 120 caràcters';
        }
        if (!empty($this->slug_es) && strlen($this->slug_es) > 120) {
            $errors[] = 'El slug (ES) no pot superar els 120 caràcters';
        }

        // Validar temps de lectura
        if (!empty($this->temps_lectura_ca) && ($this->temps_lectura_ca < 1 || $this->temps_lectura_ca > 255)) {
            $errors[] = 'El temps de lectura (CA) ha de ser entre 1 i 255 minuts';
        }
        if (!empty($this->temps_lectura_es) && ($this->temps_lectura_es < 1 || $this->temps_lectura_es > 255)) {
            $errors[] = 'El temps de lectura (ES) ha de ser entre 1 i 255 minuts';
        }

        return $errors;
    }

    /* ===================== CRUD ===================== */
    /**
     * Crear una nova entrada.
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
        $this->titol_ca = htmlspecialchars(strip_tags(trim((string)$this->titol_ca)));
        $this->titol_es = htmlspecialchars(strip_tags(trim((string)$this->titol_es)));
        $this->resum_ca = $this->resum_ca ? htmlspecialchars(strip_tags($this->resum_ca)) : null;
        $this->resum_es = $this->resum_es ? htmlspecialchars(strip_tags($this->resum_es)) : null;
        
        // El contingut pot contenir HTML, però sanititzem etiquetes perilloses
        $this->contingut_ca = $this->sanititzarContingut($this->contingut_ca);
        $this->contingut_es = $this->sanititzarContingut($this->contingut_es);

        // Processar metadades SEO
        $this->meta_title_ca = $this->meta_title_ca ? htmlspecialchars(strip_tags($this->meta_title_ca)) : null;
        $this->meta_title_es = $this->meta_title_es ? htmlspecialchars(strip_tags($this->meta_title_es)) : null;
        $this->meta_description_ca = $this->meta_description_ca ? htmlspecialchars(strip_tags($this->meta_description_ca)) : null;
        $this->meta_description_es = $this->meta_description_es ? htmlspecialchars(strip_tags($this->meta_description_es)) : null;
        $this->meta_keywords_ca = $this->meta_keywords_ca ? htmlspecialchars(strip_tags($this->meta_keywords_ca)) : null;
        $this->meta_keywords_es = $this->meta_keywords_es ? htmlspecialchars(strip_tags($this->meta_keywords_es)) : null;

        // Processar imatges
        $this->alt_imatge_ca = $this->alt_imatge_ca ? htmlspecialchars(strip_tags($this->alt_imatge_ca)) : null;
        $this->alt_imatge_es = $this->alt_imatge_es ? htmlspecialchars(strip_tags($this->alt_imatge_es)) : null;
        $this->galeria_imatges = $this->processarGaleriaImatges($this->galeria_imatges);

        // Generar slugs si no especificats
        if (empty($this->slug_ca)) {
            $this->slug_ca = $this->generarSlug($this->titol_ca);
        }
        if (empty($this->slug_es)) {
            $this->slug_es = $this->generarSlug($this->titol_es);
        }

        // Assegurar unicitat dels slugs
        $this->slug_ca = $this->makeUniqueSlug($this->slug_ca, 'ca');
        $this->slug_es = $this->makeUniqueSlug($this->slug_es, 'es');

        // Calcular temps de lectura si no especificat
        if (empty($this->temps_lectura_ca)) {
            $this->temps_lectura_ca = $this->calcularTempsLectura($this->contingut_ca);
        }
        if (empty($this->temps_lectura_es)) {
            $this->temps_lectura_es = $this->calcularTempsLectura($this->contingut_es);
        }

        // Validar i ajustar dates
        if ($this->estat === self::ESTAT_PUBLICAT && empty($this->data_publicacio)) {
            $this->data_publicacio = date('Y-m-d H:i:s');
        }

        // Assegurar valors per defecte
        $this->estat = $this->estat ?: self::ESTAT_ESBORRANY;
        $this->visible = $this->visible ? 1 : 0;
        $this->comentaris_actius = $this->comentaris_actius ? 1 : 0;
        $this->visualitzacions = (int)($this->visualitzacions ?? 0);
        $this->compartits = (int)($this->compartits ?? 0);

        $sql = "INSERT INTO {$this->table}
                (titol_ca, titol_es, slug_ca, slug_es, contingut_ca, contingut_es, resum_ca, resum_es,
                 estat, data_publicacio, data_arxivat, visible,
                 imatge_portada, alt_imatge_ca, alt_imatge_es, galeria_imatges,
                 meta_title_ca, meta_title_es, meta_description_ca, meta_description_es, 
                 meta_keywords_ca, meta_keywords_es,
                 id_autor, temps_lectura_ca, temps_lectura_es,
                 visualitzacions, compartits, comentaris_actius, data_modificacio)
                VALUES
                (:titol_ca, :titol_es, :slug_ca, :slug_es, :contingut_ca, :contingut_es, :resum_ca, :resum_es,
                 :estat, :data_publicacio, :data_arxivat, :visible,
                 :imatge_portada, :alt_imatge_ca, :alt_imatge_es, :galeria_imatges,
                 :meta_title_ca, :meta_title_es, :meta_description_ca, :meta_description_es,
                 :meta_keywords_ca, :meta_keywords_es,
                 :id_autor, :temps_lectura_ca, :temps_lectura_es,
                 :visualitzacions, :compartits, :comentaris_actius, :data_modificacio)";

        $stmt = $this->conn->prepare($sql);
        
        // Bind parameters
        $stmt->bindParam(':titol_ca', $this->titol_ca);
        $stmt->bindParam(':titol_es', $this->titol_es);
        $stmt->bindParam(':slug_ca', $this->slug_ca);
        $stmt->bindParam(':slug_es', $this->slug_es);
        $stmt->bindParam(':contingut_ca', $this->contingut_ca);
        $stmt->bindParam(':contingut_es', $this->contingut_es);
        $stmt->bindParam(':resum_ca', $this->resum_ca);
        $stmt->bindParam(':resum_es', $this->resum_es);
        $stmt->bindParam(':estat', $this->estat);
        $stmt->bindParam(':data_publicacio', $this->data_publicacio);
        $stmt->bindParam(':data_arxivat', $this->data_arxivat);
        $stmt->bindParam(':visible', $this->visible, PDO::PARAM_INT);
        $stmt->bindParam(':imatge_portada', $this->imatge_portada);
        $stmt->bindParam(':alt_imatge_ca', $this->alt_imatge_ca);
        $stmt->bindParam(':alt_imatge_es', $this->alt_imatge_es);
        $stmt->bindParam(':galeria_imatges', $this->galeria_imatges);
        $stmt->bindParam(':meta_title_ca', $this->meta_title_ca);
        $stmt->bindParam(':meta_title_es', $this->meta_title_es);
        $stmt->bindParam(':meta_description_ca', $this->meta_description_ca);
        $stmt->bindParam(':meta_description_es', $this->meta_description_es);
        $stmt->bindParam(':meta_keywords_ca', $this->meta_keywords_ca);
        $stmt->bindParam(':meta_keywords_es', $this->meta_keywords_es);
        $stmt->bindParam(':id_autor', $this->id_autor, PDO::PARAM_INT);
        $stmt->bindParam(':temps_lectura_ca', $this->temps_lectura_ca, PDO::PARAM_INT);
        $stmt->bindParam(':temps_lectura_es', $this->temps_lectura_es, PDO::PARAM_INT);
        $stmt->bindParam(':visualitzacions', $this->visualitzacions, PDO::PARAM_INT);
        $stmt->bindParam(':compartits', $this->compartits, PDO::PARAM_INT);
        $stmt->bindParam(':comentaris_actius', $this->comentaris_actius, PDO::PARAM_INT);
        $stmt->bindParam(':data_modificacio', $this->data_modificacio);

        if ($stmt->execute()) {
            $this->id_entrada = (int)$this->conn->lastInsertId();
            return $this->id_entrada;
        }

        return false;
    }

    /**
     * Llegir una entrada per ID.
     * Retorna array associatiu o false.
     *
     * @param int $id
     * @return array|false
     */
    public function llegirUn(int $id) {
        $sql = "SELECT * FROM {$this->table} WHERE id_entrada = :id LIMIT 1";
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
     * Llegir múltiples entrades amb filtres avançats.
     * Retorna PDOStatement per permetre paginació i fetch flexible.
     *
     * @param array $filters Filtres aplicables
     * @param int|null $limit
     * @param int|null $offset
     * @param string $orderBy
     * @param string $direction
     * @return PDOStatement
     */
    public function llegirTots($filters = [], $limit = null, $offset = null, $orderBy = 'data_creacio', $direction = 'DESC') {
        $allowedOrder = [
            'data_creacio', 'data_publicacio', 'data_actualitzacio', 
            'titol_ca', 'titol_es', 'visualitzacions', 'compartits', 'estat'
        ];
        if (!in_array($orderBy, $allowedOrder)) $orderBy = 'data_creacio';
        $direction = strtoupper($direction) === 'ASC' ? 'ASC' : 'DESC';

        $sql = "SELECT * FROM {$this->table}";
        $conditions = [];
        $params = [];

        // Aplicar filtres
        if (isset($filters['estat']) && $this->estatValid($filters['estat'])) {
            $conditions[] = "estat = :estat";
            $params[':estat'] = $filters['estat'];
        }

        if (isset($filters['visible'])) {
            $conditions[] = "visible = :visible";
            $params[':visible'] = $filters['visible'] ? 1 : 0;
        }

        if (isset($filters['id_autor'])) {
            $conditions[] = "id_autor = :id_autor";
            $params[':id_autor'] = (int)$filters['id_autor'];
        }

        if (isset($filters['data_desde'])) {
            $conditions[] = "data_publicacio >= :data_desde";
            $params[':data_desde'] = $filters['data_desde'];
        }

        if (isset($filters['data_fins'])) {
            $conditions[] = "data_publicacio <= :data_fins";
            $params[':data_fins'] = $filters['data_fins'];
        }

        if (isset($filters['cerca']) && !empty($filters['cerca'])) {
            $conditions[] = "(MATCH(titol_ca, titol_es) AGAINST(:cerca IN NATURAL LANGUAGE MODE) 
                           OR MATCH(contingut_ca, contingut_es) AGAINST(:cerca IN NATURAL LANGUAGE MODE))";
            $params[':cerca'] = $filters['cerca'];
        }

        // Afegir condicions WHERE
        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }

        $sql .= " ORDER BY {$orderBy} {$direction}";

        if ($limit !== null) {
            $sql .= " LIMIT :limit";
            if ($offset !== null) $sql .= " OFFSET :offset";
        }

        $stmt = $this->conn->prepare($sql);
        
        // Bind filtres
        foreach ($params as $k => $v) {
            if ($k === ':id_autor') {
                $stmt->bindValue($k, $v, PDO::PARAM_INT);
            } else {
                $stmt->bindValue($k, $v);
            }
        }
        
        // Bind limit/offset
        if ($limit !== null) {
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
            if ($offset !== null) $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        }

        $stmt->execute();
        return $stmt;
    }

    /**
     * Actualitzar una entrada existent.
     * Retorna true/false.
     *
     * @return bool
     */
    public function actualitzar() {
        if (empty($this->id_entrada)) return false;

        $errs = $this->validar();
        if (!empty($errs)) return false;

        // Aplicar les mateixes netejades que en crear()
        $this->titol_ca = htmlspecialchars(strip_tags(trim((string)$this->titol_ca)));
        $this->titol_es = htmlspecialchars(strip_tags(trim((string)$this->titol_es)));
        $this->resum_ca = $this->resum_ca ? htmlspecialchars(strip_tags($this->resum_ca)) : null;
        $this->resum_es = $this->resum_es ? htmlspecialchars(strip_tags($this->resum_es)) : null;
        
        $this->contingut_ca = $this->sanititzarContingut($this->contingut_ca);
        $this->contingut_es = $this->sanititzarContingut($this->contingut_es);

        // Processar metadades i imatges
        $this->meta_title_ca = $this->meta_title_ca ? htmlspecialchars(strip_tags($this->meta_title_ca)) : null;
        $this->meta_title_es = $this->meta_title_es ? htmlspecialchars(strip_tags($this->meta_title_es)) : null;
        $this->meta_description_ca = $this->meta_description_ca ? htmlspecialchars(strip_tags($this->meta_description_ca)) : null;
        $this->meta_description_es = $this->meta_description_es ? htmlspecialchars(strip_tags($this->meta_description_es)) : null;
        $this->meta_keywords_ca = $this->meta_keywords_ca ? htmlspecialchars(strip_tags($this->meta_keywords_ca)) : null;
        $this->meta_keywords_es = $this->meta_keywords_es ? htmlspecialchars(strip_tags($this->meta_keywords_es)) : null;

        $this->alt_imatge_ca = $this->alt_imatge_ca ? htmlspecialchars(strip_tags($this->alt_imatge_ca)) : null;
        $this->alt_imatge_es = $this->alt_imatge_es ? htmlspecialchars(strip_tags($this->alt_imatge_es)) : null;
        $this->galeria_imatges = $this->processarGaleriaImatges($this->galeria_imatges);

        // Slugs: si buits generar, si donats assegurar unicitat excloent el propi id
        if (empty($this->slug_ca)) {
            $this->slug_ca = $this->generarSlug($this->titol_ca);
        }
        if (empty($this->slug_es)) {
            $this->slug_es = $this->generarSlug($this->titol_es);
        }
        $this->slug_ca = $this->makeUniqueSlug($this->slug_ca, 'ca', $this->id_entrada);
        $this->slug_es = $this->makeUniqueSlug($this->slug_es, 'es', $this->id_entrada);

        // Actualitzar temps de lectura si no especificat
        if (empty($this->temps_lectura_ca)) {
            $this->temps_lectura_ca = $this->calcularTempsLectura($this->contingut_ca);
        }
        if (empty($this->temps_lectura_es)) {
            $this->temps_lectura_es = $this->calcularTempsLectura($this->contingut_es);
        }

        // Gestionar data de publicació
        if ($this->estat === self::ESTAT_PUBLICAT && empty($this->data_publicacio)) {
            $this->data_publicacio = date('Y-m-d H:i:s');
        }

        // Assegurar valors
        $this->visible = $this->visible ? 1 : 0;
        $this->comentaris_actius = $this->comentaris_actius ? 1 : 0;
        $this->data_modificacio = date('Y-m-d H:i:s');

        $sql = "UPDATE {$this->table} SET
                    titol_ca = :titol_ca,
                    titol_es = :titol_es,
                    slug_ca = :slug_ca,
                    slug_es = :slug_es,
                    contingut_ca = :contingut_ca,
                    contingut_es = :contingut_es,
                    resum_ca = :resum_ca,
                    resum_es = :resum_es,
                    estat = :estat,
                    data_publicacio = :data_publicacio,
                    data_arxivat = :data_arxivat,
                    visible = :visible,
                    imatge_portada = :imatge_portada,
                    alt_imatge_ca = :alt_imatge_ca,
                    alt_imatge_es = :alt_imatge_es,
                    galeria_imatges = :galeria_imatges,
                    meta_title_ca = :meta_title_ca,
                    meta_title_es = :meta_title_es,
                    meta_description_ca = :meta_description_ca,
                    meta_description_es = :meta_description_es,
                    meta_keywords_ca = :meta_keywords_ca,
                    meta_keywords_es = :meta_keywords_es,
                    temps_lectura_ca = :temps_lectura_ca,
                    temps_lectura_es = :temps_lectura_es,
                    visible = :visible2,
                    comentaris_actius = :comentaris_actius,
                    data_modificacio = :data_modificacio
                WHERE id_entrada = :id";

        $stmt = $this->conn->prepare($sql);
        
        // Bind tots els paràmetres
        $stmt->bindParam(':titol_ca', $this->titol_ca);
        $stmt->bindParam(':titol_es', $this->titol_es);
        $stmt->bindParam(':slug_ca', $this->slug_ca);
        $stmt->bindParam(':slug_es', $this->slug_es);
        $stmt->bindParam(':contingut_ca', $this->contingut_ca);
        $stmt->bindParam(':contingut_es', $this->contingut_es);
        $stmt->bindParam(':resum_ca', $this->resum_ca);
        $stmt->bindParam(':resum_es', $this->resum_es);
        $stmt->bindParam(':estat', $this->estat);
        $stmt->bindParam(':data_publicacio', $this->data_publicacio);
        $stmt->bindParam(':data_arxivat', $this->data_arxivat);
        $stmt->bindParam(':visible', $this->visible, PDO::PARAM_INT);
        $stmt->bindParam(':imatge_portada', $this->imatge_portada);
        $stmt->bindParam(':alt_imatge_ca', $this->alt_imatge_ca);
        $stmt->bindParam(':alt_imatge_es', $this->alt_imatge_es);
        $stmt->bindParam(':galeria_imatges', $this->galeria_imatges);
        $stmt->bindParam(':meta_title_ca', $this->meta_title_ca);
        $stmt->bindParam(':meta_title_es', $this->meta_title_es);
        $stmt->bindParam(':meta_description_ca', $this->meta_description_ca);
        $stmt->bindParam(':meta_description_es', $this->meta_description_es);
        $stmt->bindParam(':meta_keywords_ca', $this->meta_keywords_ca);
        $stmt->bindParam(':meta_keywords_es', $this->meta_keywords_es);
        $stmt->bindParam(':temps_lectura_ca', $this->temps_lectura_ca, PDO::PARAM_INT);
        $stmt->bindParam(':temps_lectura_es', $this->temps_lectura_es, PDO::PARAM_INT);
        $stmt->bindParam(':visible2', $this->visible, PDO::PARAM_INT);
        $stmt->bindParam(':comentaris_actius', $this->comentaris_actius, PDO::PARAM_INT);
        $stmt->bindParam(':data_modificacio', $this->data_modificacio);
        $stmt->bindParam(':id', $this->id_entrada, PDO::PARAM_INT);

        return $stmt->execute();
    }

    /**
     * Eliminar una entrada (borrat definitiu).
     * Retorna true/false.
     *
     * @param int $id
     * @return bool
     */
    public function eliminar(int $id) {
        $sql = "DELETE FROM {$this->table} WHERE id_entrada = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    /* ===================== Funcions específiques del blog ===================== */

    /**
     * Buscar entrada per slug i idioma.
     *
     * @param string $slug
     * @param string $lang 'ca' o 'es'
     * @param bool $nomesPubplicades Només entrades publicades i visibles
     * @return array|false
     */
    public function buscarPerSlug(string $slug, string $lang = 'es', bool $nomesPubplicades = true) {
        $col = $lang === 'ca' ? 'slug_ca' : 'slug_es';
        $sql = "SELECT * FROM {$this->table} WHERE {$col} = :slug";
        
        if ($nomesPubplicades) {
            $sql .= " AND estat = :estat AND visible = 1";
        }
        
        $sql .= " LIMIT 1";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':slug', $slug);
        if ($nomesPubplicades) {
            $publicat = self::ESTAT_PUBLICAT;
            $stmt->bindParam(':estat', $publicat);
        }
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: false;
    }

    /**
     * Incrementar visualitzacions d'una entrada.
     *
     * @param int $id
     * @return bool
     */
    public function incrementarVisualitzacions(int $id): bool {
        $sql = "UPDATE {$this->table} SET visualitzacions = visualitzacions + 1 WHERE id_entrada = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    /**
     * Incrementar compartits d'una entrada.
     *
     * @param int $id
     * @return bool
     */
    public function incrementarCompartits(int $id): bool {
        $sql = "UPDATE {$this->table} SET compartits = compartits + 1 WHERE id_entrada = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    /**
     * Canviar estat d'una entrada.
     *
     * @param int $id
     * @param string $nouEstat
     * @return bool
     */
    public function canviarEstat(int $id, string $nouEstat): bool {
        if (!$this->estatValid($nouEstat)) return false;
        
        $dataPublicacio = null;
        if ($nouEstat === self::ESTAT_PUBLICAT) {
            $dataPublicacio = date('Y-m-d H:i:s');
        }
        
        $sql = "UPDATE {$this->table} SET estat = :estat";
        if ($dataPublicacio) {
            $sql .= ", data_publicacio = :data_publicacio";
        }
        $sql .= " WHERE id_entrada = :id";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':estat', $nouEstat);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        if ($dataPublicacio) {
            $stmt->bindParam(':data_publicacio', $dataPublicacio);
        }
        
        return $stmt->execute();
    }

    /**
     * Obtenir entrades relacionades basades en etiquetes/categories.
     *
     * @param int $id ID de l'entrada actual
     * @param int $limit Nombre d'entrades a retornar
     * @param string $lang Idioma
     * @return array
     */
    public function obtenirEntradesRelacionades(int $id, int $limit = 5, string $lang = 'es'): array {
        // Aquesta funció serà ampliada quan implementem les relacions amb categories i etiquetes
        $titolCol = $lang === 'ca' ? 'titol_ca' : 'titol_es';
        $slugCol = $lang === 'ca' ? 'slug_ca' : 'slug_es';
        
        $sql = "SELECT id_entrada, {$titolCol} as titol, {$slugCol} as slug, 
                       imatge_portada, data_publicacio, visualitzacions
                FROM {$this->table} 
                WHERE id_entrada != :id 
                  AND estat = :estat 
                  AND visible = 1 
                ORDER BY data_publicacio DESC 
                LIMIT :limit";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $publicat = self::ESTAT_PUBLICAT;
        $stmt->bindParam(':estat', $publicat);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtenir estadístiques de les entrades.
     *
     * @param int|null $idAutor Filtrar per autor específic
     * @return array
     */
    public function obtenirEstadistiques($idAutor = null): array {
        $sql = "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN estat = :publicat THEN 1 ELSE 0 END) as publicades,
                    SUM(CASE WHEN estat = :esborrany THEN 1 ELSE 0 END) as esborranys,
                    SUM(CASE WHEN estat = :revisio THEN 1 ELSE 0 END) as en_revisio,
                    SUM(CASE WHEN estat = :programat THEN 1 ELSE 0 END) as programades,
                    SUM(visualitzacions) as total_visualitzacions,
                    SUM(compartits) as total_compartits,
                    AVG(visualitzacions) as mitjana_visualitzacions
                FROM {$this->table}";
        
        $params = [
            ':publicat' => self::ESTAT_PUBLICAT,
            ':esborrany' => self::ESTAT_ESBORRANY,
            ':revisio' => self::ESTAT_REVISIO,
            ':programat' => self::ESTAT_PROGRAMAT
        ];
        
        if ($idAutor) {
            $sql .= " WHERE id_autor = :id_autor";
            $params[':id_autor'] = $idAutor;
        }
        
        $stmt = $this->conn->prepare($sql);
        foreach ($params as $k => $v) {
            if ($k === ':id_autor') {
                $stmt->bindValue($k, $v, PDO::PARAM_INT);
            } else {
                $stmt->bindValue($k, $v);
            }
        }
        $stmt->execute();
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Convertir a enters i arrodonir mitjanes
        return [
            'total' => (int)$result['total'],
            'publicades' => (int)$result['publicades'],
            'esborranys' => (int)$result['esborranys'],
            'en_revisio' => (int)$result['en_revisio'],
            'programades' => (int)$result['programades'],
            'total_visualitzacions' => (int)$result['total_visualitzacions'],
            'total_compartits' => (int)$result['total_compartits'],
            'mitjana_visualitzacions' => round((float)$result['mitjana_visualitzacions'], 2)
        ];
    }

    /**
     * Obtenir les entrades més populars.
     *
     * @param int $limit
     * @param string $lang
     * @param int $dies Nombre de dies a considerar (0 = tots)
     * @return array
     */
    public function obtenirMesPopulars(int $limit = 10, string $lang = 'es', int $dies = 0): array {
        $titolCol = $lang === 'ca' ? 'titol_ca' : 'titol_es';
        $slugCol = $lang === 'ca' ? 'slug_ca' : 'slug_es';
        
        $sql = "SELECT id_entrada, {$titolCol} as titol, {$slugCol} as slug, 
                       imatge_portada, data_publicacio, visualitzacions, compartits
                FROM {$this->table} 
                WHERE estat = :estat AND visible = 1";
        
        if ($dies > 0) {
            $sql .= " AND data_publicacio >= DATE_SUB(NOW(), INTERVAL :dies DAY)";
        }
        
        $sql .= " ORDER BY visualitzacions DESC, compartits DESC LIMIT :limit";
        
        $stmt = $this->conn->prepare($sql);
        $publicat = self::ESTAT_PUBLICAT;
        $stmt->bindParam(':estat', $publicat);
        if ($dies > 0) {
            $stmt->bindValue(':dies', $dies, PDO::PARAM_INT);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Cerca avançada amb múltiples criteris.
     *
     * @param string $terme Terme de cerca
     * @param array $filtres Filtres addicionals
     * @param string $lang Idioma de cerca
     * @param int $limit Límit de resultats
     * @return array
     */
    public function cercaAvancada(string $terme, array $filtres = [], string $lang = 'es', int $limit = 20): array {
        $titolCol = $lang === 'ca' ? 'titol_ca' : 'titol_es';
        $contingutCol = $lang === 'ca' ? 'contingut_ca' : 'contingut_es';
        $resumCol = $lang === 'ca' ? 'resum_ca' : 'resum_es';
        $slugCol = $lang === 'ca' ? 'slug_ca' : 'slug_es';
        
        $sql = "SELECT id_entrada, {$titolCol} as titol, {$slugCol} as slug, 
                       {$resumCol} as resum, imatge_portada, data_publicacio, visualitzacions,
                       MATCH({$titolCol}) AGAINST(:terme IN NATURAL LANGUAGE MODE) as relevancia_titol,
                       MATCH({$contingutCol}) AGAINST(:terme IN NATURAL LANGUAGE MODE) as relevancia_contingut
                FROM {$this->table} 
                WHERE (MATCH({$titolCol}) AGAINST(:terme2 IN NATURAL LANGUAGE MODE)
                   OR MATCH({$contingutCol}) AGAINST(:terme3 IN NATURAL LANGUAGE MODE)
                   OR {$titolCol} LIKE :terme_like
                   OR {$resumCol} LIKE :terme_like2)
                  AND estat = :estat AND visible = 1";
        
        $params = [
            ':terme' => $terme,
            ':terme2' => $terme,
            ':terme3' => $terme,
            ':terme_like' => '%' . $terme . '%',
            ':terme_like2' => '%' . $terme . '%',
            ':estat' => self::ESTAT_PUBLICAT
        ];
        
        // Aplicar filtres addicionals
        if (isset($filtres['data_desde'])) {
            $sql .= " AND data_publicacio >= :data_desde";
            $params[':data_desde'] = $filtres['data_desde'];
        }
        
        if (isset($filtres['data_fins'])) {
            $sql .= " AND data_publicacio <= :data_fins";
            $params[':data_fins'] = $filtres['data_fins'];
        }
        
        $sql .= " ORDER BY relevancia_titol DESC, relevancia_contingut DESC, data_publicacio DESC
                 LIMIT :limit";
        
        $stmt = $this->conn->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /* ===================== Utilitats i helpers ===================== */

    /**
     * Sanititzar contingut HTML permetent etiquetes segures.
     *
     * @param string $contingut
     * @return string
     */
    private function sanititzarContingut(string $contingut): string {
        // Etiquetes permeses per al contingut del blog
        $etiquetesPermeses = '<p><br><strong><b><em><i><u><h1><h2><h3><h4><h5><h6><ul><ol><li><a><img><blockquote><code><pre>';
        
        // Neteja bàsica mantenint etiquetes permeses
        $contingut = strip_tags($contingut, $etiquetesPermeses);
        
        // Eliminar atributs perillosos dels links i imatges
        $contingut = preg_replace('/(<a[^>]*?)(?:onclick|onload|onerror|javascript:)[^>]*?(>)/i', '$1$2', $contingut);
        $contingut = preg_replace('/(<img[^>]*?)(?:onclick|onload|onerror|javascript:)[^>]*?(>)/i', '$1$2', $contingut);
        
        return $contingut;
    }

    /**
     * Obtenir un resum automàtic del contingut.
     *
     * @param string $contingut
     * @param int $longitudMaxima
     * @return string
     */
    public function generarResum(string $contingut, int $longitudMaxima = 200): string {
        // Eliminar etiquetes HTML
        $text = strip_tags($contingut);
        
        // Eliminar espais extra
        $text = preg_replace('/\s+/', ' ', trim($text));
        
        if (strlen($text) <= $longitudMaxima) {
            return $text;
        }
        
        // Tallar pel darrer espai abans del límit
        $resum = substr($text, 0, $longitudMaxima);
        $ultimEspai = strrpos($resum, ' ');
        
        if ($ultimEspai !== false) {
            $resum = substr($resum, 0, $ultimEspai);
        }
        
        return $resum . '...';
    }

    /**
     * Validar i processar data de publicació programada.
     *
     * @param string $data
     * @return string|null Data vàlida o null
     */
    public function validarDataPublicacio(string $data): ?string {
        $timestamp = strtotime($data);
        
        if ($timestamp === false) {
            return null;
        }
        
        // No permetre dates en el passat per entrades programades
        if ($timestamp < time()) {
            return null;
        }
        
        return date('Y-m-d H:i:s', $timestamp);
    }

    /**
     * Comptar paraules en un text.
     *
     * @param string $text
     * @return int
     */
    public function comptarParaules(string $text): int {
        return str_word_count(strip_tags($text));
    }

    /**
     * Obtenir entrades programades per publicar.
     *
     * @return array
     */
    public function obtenirEntradesProgramades(): array {
        $sql = "SELECT id_entrada, titol_es, data_publicacio 
                FROM {$this->table} 
                WHERE estat = :estat 
                  AND data_publicacio <= NOW() 
                  AND visible = 1
                ORDER BY data_publicacio ASC";
        
        $stmt = $this->conn->prepare($sql);
        $programat = self::ESTAT_PROGRAMAT;
        $stmt->bindParam(':estat', $programat);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Publicar entrades programades que han arribat a la seva data.
     *
     * @return int Nombre d'entrades publicades
     */
    public function publicarEntradesProgramades(): int {
        $sql = "UPDATE {$this->table} 
                SET estat = :estat_nou 
                WHERE estat = :estat_programat 
                  AND data_publicacio <= NOW() 
                  AND visible = 1";
        
        $stmt = $this->conn->prepare($sql);
        $publicat = self::ESTAT_PUBLICAT;
        $programat = self::ESTAT_PROGRAMAT;
        $stmt->bindParam(':estat_nou', $publicat);
        $stmt->bindParam(':estat_programat', $programat);
        $stmt->execute();
        
        return $stmt->rowCount();
    }

}
