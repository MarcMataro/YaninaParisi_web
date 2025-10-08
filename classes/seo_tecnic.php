<?php
/**
 * Classe SEO_Tecnico
 *
 * Gestiona les dades d'auditories tècniques del lloc i proporciona
 * operacions bàsiques (CRUD) i utilitats per obtenir resums i una
 * puntuació tècnica (0-100) basada en diverses mètriques.
 *
 * Aquesta classe està escrita de manera coherent amb la resta de
 * classes del projecte i utilitza la classe Connexio (Singleton PDO)
 * per executar les consultes sobre la taula `seo_tecnico`.
 *
 * Notes: els noms de propietat corresponen exactament als camps de la
 * taula SQL proporcionada. Els mètodes implementats són els més bàsics
 * necessaris per integrar aquesta classe en el panell: crear, carregar,
 * actualitzar, eliminar, resum i càlcul de puntuació tècnica.
 * @package     YaninaParisi
 * @subpackage  Classes
 * @category    SEO
 * @author      Marc Mataró
 * @version     1.0.0
 * @since       2025-10-07
 */

require_once __DIR__ . '/connexio.php';

/**
 * Classe SEO_Tecnico
 *
 * Gestor complet per la taula `seo_tecnico` amb mètodes bàsics i útils
 * per a integració amb panells d'administració: CRUD, llistats amb
 * filtres/paginació, validacions, càlcul de puntuació i recomanacions
 * bàsiques basades en els valors detectats.
 *
 * Està pensada per ser coherent amb la resta de classes del projecte
 * (utilitza Connexio singleton) i inclou comentaris professionals
 * explicant decisions i lògica.
 */

class SEO_Tecnico {
    // PK
    private $id_tecnico = null;

    // 1. Rendiment i velocitat
    private $velocidad_carga_ms = null;
    private $velocidad_primera_pintura = null;
    private $velocidad_pintura_contenido = null;
    private $velocidad_interactividad = null;
    private $puntuacion_lighthouse = null;
    private $core_web_vitals = null; // JSON

    // 2. Indexació i rastreig
    private $estado_indexacion = 'completa';
    private $paginas_indexadas = 0;
    private $paginas_no_indexadas = 0;
    private $ultimo_rastreo_google = null;
    private $frecuencia_rastreo = 'semanal';

    // 3. Estructura URL i canonicals
    private $estructura_url = 'amigable';
    private $urls_canonicas_incorrectas = 0;
    private $urls_duplicadas = 0;
    private $parametros_url_problematicos = null;

    // 4. Estat del sitemap
    private $sitemap_existe = true;
    private $sitemap_url = '/sitemap.xml';
    private $sitemap_ultima_actualizacion = null;
    private $sitemap_urls_total = 0;
    private $sitemap_urls_indexables = 0;

    // 5. robots.txt i accés
    private $robots_txt_existe = true;
    private $robots_txt_configurado = false;
    private $bloqueos_inecesarios = 0;

    // 6. HTTPS i SSL
    private $ssl_activo = true;
    private $ssl_valido = true;
    private $ssl_tipo = 'dv';
    private $ssl_caducidad = null;
    private $http2_activo = true;

    // 7. Responsive
    private $mobile_friendly = true;
    private $viewport_configurado = true;
    private $tap_targets_adecuados = true;
    private $font_sizes_legibles = true;

    // 8. Estructura i jerarquia
    private $profundidad_maxima = 3;
    private $enlaces_rotos = 0;
    private $enlaces_internos_total = 0;
    private $enlaces_salientes_total = 0;
    private $arquitectura_optimizada = true;

    // 9. Seguretat
    private $headers_seguridad = null; // JSON
    private $vulnerabilidades_detectadas = 0;
    private $proteccion_malware = true;

    // 10. Internacional i hreflang
    private $hreflang_implementado = true;
    private $hreflang_errores = 0;
    private $geotargeting_configurado = false;
    private $ccTLDs_implementados = false;

    // 11. Dades estructurades
    private $schema_implementado = true;
    private $schema_errores = 0;
    private $rich_results_activos = false;
    private $tipo_rich_results = null; // JSON

    // 12. Imatges i multimèdia
    private $imagenes_optimizadas = true;
    private $imagenes_sin_alt = 0;
    private $lazy_loading_activo = true;
    private $webp_soportado = true;

    // 13. Cache i CDN
    private $cache_implementado = true;
    private $cdn_activo = false;
    private $tiempo_cache_browser = 86400;
    private $compresion_activa = true;

    // 14. Monitorització i errors
    private $uptime_30d = 99.99;
    private $errores_404 = 0;
    private $errores_500 = 0;
    private $redirects_encadenados = 0;

    // 15. Integracions eines
    private $google_search_console_configurado = true;
    private $google_analytics_configurado = true;
    private $google_tag_manager_configurado = true;
    private $google_business_profile_configurado = false;

    // 16. Control i auditoria
    private $ultima_auditoria_completa = null;
    private $puntuacion_seo_tecnico = 0;
    private $criticidad_issues = 'media';
    private $fecha_creacion = null;
    private $fecha_actualizacion = null;

    // Connexio PDO helper
    private $db;

    /**
     * Constructor
     * Si s'indica $id carreguem l'entrada de la base de dades.
     *
     * @param int|null $id
     */
    public function __construct($id = null) {
        $this->db = Connexio::getInstance();
        if ($id !== null) {
            $this->carregar($id);
        }
    }

    /* -----------------------------------------------------------------
     * Validació i utilitats
     * ----------------------------------------------------------------- */

    /**
     * Validar un conjunt de dades abans d'inserir/actualitzar.
     * Retorna un array amb "valid" (bool) i "errors" (array).
     *
     * @param array $dades
     * @return array
     */
    public static function validar(array $dades) {
        $errors = [];

        // Validar rangs numèrics simples
        if (isset($dades['puntuacion_lighthouse']) && (!is_numeric($dades['puntuacion_lighthouse']) || $dades['puntuacion_lighthouse'] < 0 || $dades['puntuacion_lighthouse'] > 100)) {
            $errors[] = 'puntuacion_lighthouse ha de ser un enter entre 0 i 100';
        }
        if (isset($dades['puntuacion_seo_tecnico']) && (!is_numeric($dades['puntuacion_seo_tecnico']) || $dades['puntuacion_seo_tecnico'] < 0 || $dades['puntuacion_seo_tecnico'] > 100)) {
            $errors[] = 'puntuacion_seo_tecnico ha de ser un enter entre 0 i 100';
        }

        // Enumerats
        $estado_vals = ['completa','parcial','limitada','bloqueada'];
        if (isset($dades['estado_indexacion']) && !in_array($dades['estado_indexacion'], $estado_vals, true)) {
            $errors[] = 'estado_indexacion no és vàlid';
        }

        $freq_vals = ['diaria','semanal','mensual','poco_frecuente'];
        if (isset($dades['frecuencia_rastreo']) && !in_array($dades['frecuencia_rastreo'], $freq_vals, true)) {
            $errors[] = 'frecuencia_rastreo no és vàlid';
        }

        $estructura_vals = ['amigable','parametrica','mixta'];
        if (isset($dades['estructura_url']) && !in_array($dades['estructura_url'], $estructura_vals, true)) {
            $errors[] = 'estructura_url no és vàlid';
        }

        $ssl_tipo_vals = ['dv','ov','ev'];
        if (isset($dades['ssl_tipo']) && !in_array($dades['ssl_tipo'], $ssl_tipo_vals, true)) {
            $errors[] = 'ssl_tipo no és vàlid';
        }

        $critic_vals = ['critica','alta','media','baja'];
        if (isset($dades['criticidad_issues']) && !in_array($dades['criticidad_issues'], $critic_vals, true)) {
            $errors[] = 'criticidad_issues no és vàlid';
        }

        return ['valid' => empty($errors), 'errors' => $errors];
    }

    /**
     * Sanititzar entrades abans d'inserir a BD (bàsic)
     * Aquest mètode normalitza booleans i JSONs.
     */
    private function sanitizeForDB(array $in) {
        $out = [];
        foreach ($in as $k => $v) {
            // Normalitzar booleans
            if (in_array($k, ['sitemap_existe','robots_txt_existe','robots_txt_configurado','ssl_activo','ssl_valido','http2_activo',
                'mobile_friendly','viewport_configurado','tap_targets_adecuados','font_sizes_legibles','arquitectura_optimizada',
                'proteccion_malware','hreflang_implementado','schema_implementado','rich_results_activos','imagenes_optimizadas',
                'lazy_loading_activo','webp_soportado','cache_implementado','cdn_activo','google_search_console_configurado',
                'google_analytics_configurado','google_tag_manager_configurado','google_business_profile_configurado'])) {
                $out[$k] = (int)(bool)$v;
                continue;
            }

            // JSON fields
            if (in_array($k, ['core_web_vitals','headers_seguridad','tipo_rich_results'])) {
                if (is_array($v) || is_object($v)) $out[$k] = json_encode($v, JSON_UNESCAPED_UNICODE);
                else $out[$k] = $v !== null ? $v : null;
                continue;
            }

            $out[$k] = $v;
        }
        return $out;
    }

    /* -----------------------------------------------------------------
     * CRUD: save (create/update), crear (static) i actualitzar
     * ----------------------------------------------------------------- */

    /**
     * Guardar l'objecte a la base de dades: si id_tecnico existeix -> UPDATE,
     * si no -> INSERT.
     * Retorna true en cas d'èxit, o array d'errors si la validació falla.
     *
     * @param array|null $dades Opcional: camp=>valor per sobreescriure abans de salvar
     * @return bool|array
     */
    public function save(array $dades = null) {
        if ($dades) {
            $this->actualitzarPropietatsLocals($dades);
        }

        // Validar
        $valid = self::validar($this->toArray());
        if (!$valid['valid']) return $valid;

        // Preparar dades per BD
        $dbData = $this->sanitizeForDB($this->toArray());

        // Eliminar propietats no mapejables
        unset($dbData['db']);
        unset($dbData['id_tecnico']);

        if ($this->id_tecnico) {
            // UPDATE
            $sets = [];
            $params = [];
            foreach ($dbData as $k => $v) {
                $sets[] = "`$k` = :$k";
                $params[":$k"] = $v;
            }
            $params[':id'] = $this->id_tecnico;
            $sql = 'UPDATE seo_tecnico SET ' . implode(', ', $sets) . ', fecha_actualizacion = NOW() WHERE id_tecnico = :id';
            $res = $this->db->query($sql, $params);
            return $res !== false;
        } else {
            // INSERT
            $cols = array_keys($dbData);
            $placeholders = array_map(function($c){ return ':' . $c; }, $cols);
            $params = [];
            foreach ($cols as $c) $params[':' . $c] = $dbData[$c];

            $sql = 'INSERT INTO seo_tecnico (`' . implode('`,`', $cols) . '`) VALUES (' . implode(',', $placeholders) . ')';
            $res = $this->db->query($sql, $params);
            if ($res === false) return false;
            $this->id_tecnico = $this->db->ultimId();
            return true;
        }
    }

    /**
     * Actualitzar propietats locals de l'objecte sense escriure a BD
     * (helper intern)
     *
     * @param array $dades
     */
    private function actualitzarPropietatsLocals(array $dades) {
        foreach ($dades as $k => $v) {
            if (property_exists($this, $k)) $this->$k = $v;
        }
    }

    /* -----------------------------------------------------------------
     * Llistat amb filtres i paginació
     * ----------------------------------------------------------------- */

    /**
     * Llistar registres amb filtres opcionals i paginació.
     * Filters acceptats: estado_indexacion, criticidad_issues, min_lighthouse,
     * max_velocidad_ms, mobile_friendly (bool), limit, offset.
     *
     * @param array $filters
     * @param int $limit
     * @param int $offset
     * @return array|false
     */
    public static function listar(array $filters = [], $limit = 20, $offset = 0) {
        $db = Connexio::getInstance();
        $where = [];
        $params = [];

        if (!empty($filters['estado_indexacion'])) {
            $where[] = 'estado_indexacion = :estado_indexacion';
            $params[':estado_indexacion'] = $filters['estado_indexacion'];
        }
        if (!empty($filters['criticidad_issues'])) {
            $where[] = 'criticidad_issues = :criticidad_issues';
            $params[':criticidad_issues'] = $filters['criticidad_issues'];
        }
        if (isset($filters['min_lighthouse'])) {
            $where[] = 'puntuacion_lighthouse >= :min_lh';
            $params[':min_lh'] = $filters['min_lighthouse'];
        }
        if (isset($filters['max_velocidad_ms'])) {
            $where[] = 'velocidad_carga_ms <= :max_vel';
            $params[':max_vel'] = $filters['max_velocidad_ms'];
        }
        if (isset($filters['mobile_friendly'])) {
            $where[] = 'mobile_friendly = :mobile_friendly';
            $params[':mobile_friendly'] = (int)(bool)$filters['mobile_friendly'];
        }

        $sql = 'SELECT * FROM seo_tecnico';
        if (!empty($where)) $sql .= ' WHERE ' . implode(' AND ', $where);
        $sql .= ' ORDER BY fecha_actualizacion DESC';
        $sql .= ' LIMIT ' . (int)$limit . ' OFFSET ' . (int)$offset;

        return $db->fetchAll($sql, $params);
    }

    /* -----------------------------------------------------------------
     * Helpers i petits operadors que poden ser útils al panell
     * ----------------------------------------------------------------- */

    /**
     * Marcar una auditoria com a feta (actualitza ultima_auditoria_completa)
     *
     * @param string|null $datetime Datetime en format MySQL, per defecte NOW()
     * @return bool
     */
    public function marcarAuditada($datetime = null) {
        if (!$this->id_tecnico) return false;
        $dt = $datetime ? $datetime : date('Y-m-d H:i:s');
        $res = $this->db->query('UPDATE seo_tecnico SET ultima_auditoria_completa = ? WHERE id_tecnico = ?', [$dt, $this->id_tecnico]);
        if ($res !== false) $this->ultima_auditoria_completa = $dt;
        return $res !== false;
    }

    /**
     * Incrementar comptadors d'errors 404/500
     *
     * @param string $tipus '404'|'500'
     * @param int $by
     * @return bool
     */
    public function incrementarError($tipus = '404', $by = 1) {
        if (!$this->id_tecnico) return false;
        $camp = $tipus === '500' ? 'errores_500' : 'errores_404';
        $res = $this->db->query("UPDATE seo_tecnico SET $camp = $camp + ? WHERE id_tecnico = ?", [$by, $this->id_tecnico]);
        if ($res !== false) {
            if ($camp === 'errores_404') $this->errores_404 += $by; else $this->errores_500 += $by;
        }
        return $res !== false;
    }

    /* -----------------------------------------------------------------
     * Estadístiques globals i recomanacions
     * ----------------------------------------------------------------- */

    /**
     * Obtenir estadístiques globals ràpides per al panell
     *
     * @return array|false
     */
    public static function obtenerEstadisticasGlobals() {
        $db = Connexio::getInstance();
        $res = [];

        $avgUptime = $db->fetchColumn('SELECT AVG(uptime_30d) FROM seo_tecnico');
        $avgLighthouse = $db->fetchColumn('SELECT AVG(puntuacion_lighthouse) FROM seo_tecnico');
        $total = $db->fetchColumn('SELECT COUNT(*) FROM seo_tecnico');

        $res['total'] = (int)$total;
        $res['avg_uptime_30d'] = $avgUptime !== false ? round((float)$avgUptime,2) : null;
        $res['avg_lighthouse'] = $avgLighthouse !== false ? round((float)$avgLighthouse,1) : null;

        return $res;
    }

    /**
     * Generar recomanacions bàsiques a partir de l'estat actual
     * Retorna array de strings amb les recomanacions prioritzades.
     *
     * @return array
     */
    public function recomendacionesBasicas() {
        $out = [];

        if ($this->velocidad_carga_ms === null || $this->velocidad_carga_ms > 3000) {
            $out[] = 'Optimitzar temps de càrrega: comprimir imatges, habilitar cache i revisar fonts crítiques.';
        }
        if ($this->puntuacion_lighthouse === null || $this->puntuacion_lighthouse < 60) {
            $out[] = 'Millorar criteris Lighthouse: revisar accessibilitat, millorar render-blocking i optimitzar recursos.';
        }
        if (!$this->ssl_activo || !$this->ssl_valido) {
            $out[] = 'Assegurar HTTPS amb certificat vàlid i revisar caducitat.';
        }
        if (!$this->sitemap_existe) {
            $out[] = 'Generar i publicar sitemap.xml, assegurar que conté URLs indexables.';
        }
        if ($this->imagenes_sin_alt > 0) {
            $out[] = "Afegir atributs alt a les imatges (sin alt: {$this->imagenes_sin_alt}).";
        }
        if ($this->enlaces_rotos > 10) {
            $out[] = "Corregir enllaços trencats (enllaços_rotos: {$this->enlaces_rotos}).";
        }
        if ($this->hreflang_errores > 0) {
            $out[] = "Revisar etiquetes hreflang (errors: {$this->hreflang_errores}).";
        }
        if ($this->uptime_30d < 99.5) {
            $out[] = 'Investigar disponibilitat i monitorització; uptime < 99.5%.';
        }

        // Afegir recomanacions per problemes d'indexació
        if ($this->estado_indexacion === 'bloqueada') {
            $out[] = 'Comprovar robots.txt i meta robots; la web sembla bloquejada per al rastreig.';
        } elseif ($this->estado_indexacion === 'limitada') {
            $out[] = 'Revisar restriccions de cicle, canonical i paràmetres URL per millorar indexació.';
        }

        return $out;
    }

    /* -----------------------------------------------------------------
     * Representació / utilitats finals
     * ----------------------------------------------------------------- */

    /**
     * Crear un nou registre a la base de dades
     * Accepta un array associatiu amb les claus coincidents amb els camps.
     * Retorna la instància carregada del nou registre o false en cas d'error.
     *
     * @param array $dades
     * @return SEO_Tecnico|false
     */
    public static function crear($dades = []) {
        $inst = new self();
        $db = Connexio::getInstance();

        // Llista blanca de camps que acceptem per inserir
        $campos_acceptats = [
            'velocidad_carga_ms','velocidad_primera_pintura','velocidad_pintura_contenido','velocidad_interactividad',
            'puntuacion_lighthouse','core_web_vitals','estado_indexacion','paginas_indexadas','paginas_no_indexadas',
            'ultimo_rastreo_google','frecuencia_rastreo','estructura_url','urls_canonicas_incorrectas','urls_duplicadas',
            'parametros_url_problematicos','sitemap_existe','sitemap_url','sitemap_ultima_actualizacion','sitemap_urls_total',
            'sitemap_urls_indexables','robots_txt_existe','robots_txt_configurado','bloqueos_inecesarios','ssl_activo','ssl_valido',
            'ssl_tipo','ssl_caducidad','http2_activo','mobile_friendly','viewport_configurado','tap_targets_adecuados',
            'font_sizes_legibles','profundidad_maxima','enlaces_rotos','enlaces_internos_total','enlaces_salientes_total',
            'arquitectura_optimizada','headers_seguridad','vulnerabilidades_detectadas','proteccion_malware',
            'hreflang_implementado','hreflang_errores','geotargeting_configurado','ccTLDs_implementados','schema_implementado',
            'schema_errores','rich_results_activos','tipo_rich_results','imagenes_optimizadas','imagenes_sin_alt',
            'lazy_loading_activo','webp_soportado','cache_implementado','cdn_activo','tiempo_cache_browser','compresion_activa',
            'uptime_30d','errores_404','errores_500','redirects_encadenados','google_search_console_configurado',
            'google_analytics_configurado','google_tag_manager_configurado','google_business_profile_configurado',
            'ultima_auditoria_completa','puntuacion_seo_tecnico','criticidad_issues'
        ];

        $pares = [];
        $placeholders = [];
        $valors = [];

        foreach ($campos_acceptats as $camp) {
            if (array_key_exists($camp, $dades)) {
                $pares[] = "`$camp`";
                $placeholders[] = ':' . $camp;
                $valors[':' . $camp] = $dades[$camp];
            }
        }

        if (empty($pares)) {
            return false; // no hi ha dades per inserir
        }

        $sql = 'INSERT INTO seo_tecnico (' . implode(', ', $pares) . ') VALUES (' . implode(', ', $placeholders) . ')';

        $stmt = $db->query($sql, $valors);
        if ($stmt === false) {
            return false;
        }

        $nouId = $db->ultimId();
        return new self($nouId);
    }

    /**
     * Carregar la fila des de la base de dades segons id
     *
     * @param int $id
     * @return bool True si s'ha carregat correctament
     */
    public function carregar($id) {
        $row = $this->db->fetch('SELECT * FROM seo_tecnico WHERE id_tecnico = ?', [$id]);
        if (!$row) {
            return false;
        }

        // Mapejar columnes a propietats
        foreach ($row as $k => $v) {
            if (property_exists($this, $k)) {
                $this->$k = $v;
            }
        }

        $this->id_tecnico = $row['id_tecnico'];
        return true;
    }

    /**
     * Actualitzar múltiples camps d'un registre
     *
     * @param array $dades Associatiu camp => valor
     * @return bool
     */
    public function actualitzarMultiplesCamps($dades = []) {
        if (!$this->id_tecnico) {
            return false;
        }

        $sets = [];
        $valors = [':id' => $this->id_tecnico];

        foreach ($dades as $camp => $valor) {
            if (!property_exists($this, $camp)) continue;
            $sets[] = "`$camp` = :$camp";
            $valors[':' . $camp] = $valor;
            $this->$camp = $valor; // actualitzar cache objecte
        }

        if (empty($sets)) return false;

        $sql = 'UPDATE seo_tecnico SET ' . implode(', ', $sets) . ', fecha_actualizacion = NOW() WHERE id_tecnico = :id';
        $stmt = $this->db->query($sql, $valors);
        return $stmt !== false;
    }

    /**
     * Eliminar el registre de la base de dades
     *
     * @return bool
     */
    public function eliminar() {
        if (!$this->id_tecnico) return false;
        $stmt = $this->db->query('DELETE FROM seo_tecnico WHERE id_tecnico = ?', [$this->id_tecnico]);
        return $stmt !== false;
    }

    /**
     * Obtenir un resum lleuger amb les mètriques més rellevants
     * Utilitzat per panells i llistats ràpids.
     *
     * @return array
     */
    public function obtenirResum() {
        return [
            'id_tecnico' => $this->id_tecnico,
            'velocidad_carga_ms' => (int) $this->velocidad_carga_ms,
            'puntuacion_lighthouse' => (int) $this->puntuacion_lighthouse,
            'core_web_vitals' => $this->core_web_vitals ? json_decode($this->core_web_vitals, true) : null,
            'estado_indexacion' => $this->estado_indexacion,
            'paginas_indexadas' => (int) $this->paginas_indexadas,
            'sitemap_existe' => (bool) $this->sitemap_existe,
            'ssl_activo' => (bool) $this->ssl_activo,
            'mobile_friendly' => (bool) $this->mobile_friendly,
            'uptime_30d' => (float) $this->uptime_30d,
            'puntuacion_seo_tecnico' => (int) $this->puntuacion_seo_tecnico,
            'criticidad_issues' => $this->criticidad_issues,
            'fecha_actualizacion' => $this->fecha_actualizacion
        ];
    }

    /**
     * Calcular una puntuació tècnica agregada 0-100 basada en diverses mètriques.
     * Pesos (exemple):
     *  - Velocitat i rendiment: 30%
     *  - Lighthouse: 25%
     *  - Core Web Vitals: 20%
     *  - SSL/HTTPS: 8%
     *  - Mobile/Responsive: 7%
     *  - Indexació i Sitemap: 10%
     *
     * El càlcul normalitza cada subfactor i combina amb pesos. Si falten dades
     * s'ignoren o es consideren valors neutres.
     *
     * @return int Puntuació 0-100
     */
    public function calcularPuntuacioTecnica() {
        $scores = [];

        // 1) Velocitat: normalitzem a una escala 0-100 on 0ms => 100 i 6000ms => 0
        if ($this->velocidad_carga_ms !== null && $this->velocidad_carga_ms >= 0) {
            $t = (int) $this->velocidad_carga_ms;
            $velScore = max(0, min(100, intval(100 - ($t / 6000) * 100)));
        } else {
            $velScore = 60; // valor neutre si no hi ha dades
        }
        $scores['velocitat'] = $velScore; // 0-100

        // 2) Lighthouse: ja està en 0-100
        $lh = is_numeric($this->puntuacion_lighthouse) ? (int)$this->puntuacion_lighthouse : 60;
        $scores['lighthouse'] = max(0, min(100, $lh));

        // 3) Core Web Vitals: tractem JSON amb possibles claus lcp, fid/inp, cls
        $cwvs = $this->core_web_vitals ? json_decode($this->core_web_vitals, true) : null;
        $cwScore = 60;
        if (is_array($cwvs)) {
            $sub = [];
            // LCP: <2500ms excel·lent
            if (isset($cwvs['lcp'])) {
                $lcp = (float)$cwvs['lcp'];
                $sub[] = max(0, min(100, intval(100 - (($lcp - 2500) / 5000) * 100)));
            }
            // FID / INP: <100ms excel·lent
            if (isset($cwvs['fid'])) {
                $fid = (float)$cwvs['fid'];
                $sub[] = max(0, min(100, intval(100 - (($fid - 100) / 1000) * 100)));
            } elseif (isset($cwvs['inp'])) {
                $inp = (float)$cwvs['inp'];
                $sub[] = max(0, min(100, intval(100 - (($inp - 100) / 1000) * 100)));
            }
            // CLS: <0.1 excel·lent
            if (isset($cwvs['cls'])) {
                $cls = (float)$cwvs['cls'];
                // cls és petit, invertim la regla
                $clsScore = $cls <= 0.1 ? 100 : max(0, min(100, intval(100 - (($cls - 0.1) / 1.0) * 100)));
                $sub[] = $clsScore;
            }
            if (!empty($sub)) {
                $cwScore = intval(array_sum($sub) / count($sub));
            }
        }
        $scores['core_web_vitals'] = $cwScore;

        // 4) SSL/HTTPS
        $sslScore = 0;
        if ($this->ssl_activo) {
            $sslScore = $this->ssl_valido ? 100 : 40;
            // Rebaixem si caduca aviat
            if ($this->ssl_caducidad) {
                $cad = strtotime($this->ssl_caducidad);
                $dies = ($cad - time()) / 86400;
                if ($dies < 30) $sslScore = max(0, $sslScore - 30);
                if ($dies < 7) $sslScore = max(0, $sslScore - 30);
            }
        }
        $scores['ssl'] = $sslScore;

        // 5) Mobile / Responsive
        $mobileScore = 100;
        $checks = 0;
        $pass = 0;
        foreach (['mobile_friendly','viewport_configurado','tap_targets_adecuados','font_sizes_legibles'] as $c) {
            if (property_exists($this, $c)) {
                $checks++;
                if ($this->$c) $pass++;
            }
        }
        if ($checks > 0) {
            $mobileScore = intval(($pass / $checks) * 100);
        }
        $scores['mobile'] = $mobileScore;

        // 6) Indexació i sitemap
        $indexScore = 100;
        if ($this->estado_indexacion === 'bloqueada') $indexScore = 10;
        elseif ($this->estado_indexacion === 'limitada') $indexScore = 40;
        elseif ($this->estado_indexacion === 'parcial') $indexScore = 75;
        // penalitzem errors en sitemap o moltes no indexades
        if (!$this->sitemap_existe) $indexScore = intval($indexScore * 0.6);
        if ($this->paginas_no_indexadas > ($this->paginas_indexadas / 2 + 1)) $indexScore = intval($indexScore * 0.7);
        $scores['indexacion'] = $indexScore;

        // Pesos finals
        $pesos = [
            'velocitat' => 0.30,
            'lighthouse' => 0.25,
            'core_web_vitals' => 0.20,
            'ssl' => 0.08,
            'mobile' => 0.07,
            'indexacion' => 0.10
        ];

        // Ajust: normalitzar si algun component falta
        $total = 0.0;
        $valor = 0.0;
        foreach ($pesos as $k => $w) {
            if (!isset($scores[$k])) continue;
            $total += $w;
            $valor += $scores[$k] * $w;
        }

        if ($total <= 0) return 0;
        $final = intval(round($valor / $total));

        // Guardem la puntuació en la propietat i tornem el valor
        $this->puntuacion_seo_tecnico = max(0, min(100, $final));
        return $this->puntuacion_seo_tecnico;
    }

    /**
     * Retorna una representació completa de l'objecte en array
     *
     * @return array
     */
    public function toArray() {
        $vars = get_object_vars($this);
        // Eliminar propietats internes no necessàries (db)
        unset($vars['db']);
        return $vars;
    }

    /**
     * Mètode getter genèric per camps públics/protegits definits
     *
     * @param string $nom
     * @return mixed|null
     */
    public function get($nom) {
        return property_exists($this, $nom) ? $this->$nom : null;
    }

    /**
     * Mètode setter genèric per actualitzar una propietat en memòria (no a BD)
     *
     * @param string $nom
     * @param mixed $valor
     */
    public function set($nom, $valor) {
        if (property_exists($this, $nom)) {
            $this->$nom = $valor;
            return true;
        }
        return false;
    }

}

?>
