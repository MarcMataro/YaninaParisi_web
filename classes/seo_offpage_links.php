<?php
/**
 * Classe SEO_OffPage_Links
 * 
 * Gestiona els backlinks i enllaços externs (Off-Page SEO) del lloc web.
 * Aquesta classe proporciona una interfície completa per treballar amb la taula
 * `seo_offpage` que emmagatzema tots els enllaços entrants (backlinks), les seves
 * mètriques de qualitat, estat i seguiment.
 * 
 * Funcionalitats principals:
 * - Registre i seguiment de backlinks
 * - Càlcul de mètriques d'autoritat i qualitat
 * - Gestió d'estats (actiu, perdut, trencat)
 * - Anàlisi de campanyes i estratègies SEO
 * - Monitorització de rendiment
 * 
 * @package     YaninaParisi
 * @subpackage  Classes
 * @category    SEO
 * @author      Marc Mataró
 * @version     1.0.0
 * @since       2025-10-07
 */

require_once __DIR__ . '/connexio.php';

class SEO_OffPage_Links {
    
    // ============================================
    // PROPIETATS PRIVADES
    // ============================================
    
    /**
     * @var int|null ID del backlink
     */
    private $id_offpage;
    
    /**
     * @var Connexio Instància de la connexió a la base de dades
     */
    private $conn;
    
    /**
     * @var PDO Objecte PDO per a consultes
     */
    private $pdo;
    
    // ============================================
    // 1. IDENTIFICACIÓ DEL BACKLINK
    // ============================================
    
    /**
     * @var string URL que enllaça al nostre lloc
     */
    private $url_origen;
    
    /**
     * @var string URL del nostre lloc que rep l'enllaç
     */
    private $url_destino;
    
    /**
     * @var string Text visible de l'enllaç (anchor text)
     */
    private $anchor_text;
    
    // ============================================
    // 2. METADADES DEL DOMINI ORIGEN
    // ============================================
    
    /**
     * @var string Domini que genera el backlink
     */
    private $dominio_origen;
    
    /**
     * @var int|null Domain Authority (Moz) de 0 a 100
     */
    private $da_origen;
    
    /**
     * @var int|null Domain Rating (Ahrefs) de 0 a 100
     */
    private $dr_origen;
    
    /**
     * @var int|null Trust Flow (Majestic) de 0 a 100
     */
    private $tf_origen;
    
    /**
     * @var int|null Citation Flow (Majestic) de 0 a 100
     */
    private $cf_origen;
    
    // ============================================
    // 3. METADADES DE LA PÀGINA ORIGEN
    // ============================================
    
    /**
     * @var string|null Títol de la pàgina que conté el backlink
     */
    private $titulo_pagina_origen;
    
    /**
     * @var int|null Page Authority de la pàgina origen
     */
    private $da_pagina_origen;
    
    /**
     * @var int|null Tràfic mensual estimat de la pàgina origen
     */
    private $traffic_origen;
    
    /**
     * @var string Idioma de la pàgina origen
     */
    private $idioma_origen;
    
    // ============================================
    // 4. TIPUS I CONTEXT DEL BACKLINK
    // ============================================
    
    /**
     * @var string Tipus de backlink (guest_post, directorio, prensa, etc.)
     */
    private $tipo_backlink;
    
    /**
     * @var string|null Text que envolta l'enllaç (context)
     */
    private $contexto_backlink;
    
    /**
     * @var string Posició de l'enllaç a la pàgina
     */
    private $posicion_enlace;
    
    /**
     * @var bool Si l'enllaç té atribut rel="nofollow"
     */
    private $nofollow;
    
    /**
     * @var bool Si l'enllaç és patrocinador (rel="sponsored")
     */
    private $sponsored;
    
    /**
     * @var bool Si és contingut generat per usuaris (rel="ugc")
     */
    private $ugc;
    
    // ============================================
    // 5. QUALITAT I RELEVÀNCIA
    // ============================================
    
    /**
     * @var string Rellevància temàtica (alta, media, baja)
     */
    private $relevancia_tematica;
    
    /**
     * @var string Qualitat percebuda (excelente, buena, regular, mala)
     */
    private $calidad_percibida;
    
    /**
     * @var string|null Temàtica principal del domini origen
     */
    private $autoridad_tematica;
    
    // ============================================
    // 6. ESTAT I VIGÈNCIA
    // ============================================
    
    /**
     * @var string Data de descobriment del backlink
     */
    private $fecha_descubrimiento;
    
    /**
     * @var string|null Data de l'última verificació
     */
    private $fecha_ultima_verificacion;
    
    /**
     * @var string Estat del backlink (activo, perdido, roto, en_revision)
     */
    private $estado;
    
    /**
     * @var string|null Data quan es va perdre el backlink
     */
    private $fecha_perdida;
    
    // ============================================
    // 7. MÈTRIQUES DE RENDIMENT
    // ============================================
    
    /**
     * @var int Clics mensuals rebuts des d'aquest backlink
     */
    private $clicks_mensuales;
    
    /**
     * @var int Tràfic estimat que genera el backlink
     */
    private $traffic_estimado;
    
    /**
     * @var float|null Valor monetari estimat del backlink
     */
    private $valor_estimado;
    
    // ============================================
    // 8. ACCIONS I SEGUIMENT
    // ============================================
    
    /**
     * @var bool Si s'ha contactat amb el propietari
     */
    private $contacto_realizado;
    
    /**
     * @var string|null Data del contacte
     */
    private $fecha_contacto;
    
    /**
     * @var bool Si s'ha rebut resposta
     */
    private $respuesta_recibida;
    
    /**
     * @var string|null Notes del contacte realitzat
     */
    private $notas_contacto;
    
    // ============================================
    // 9. ESTRATÈGIA I CLASSIFICACIÓ
    // ============================================
    
    /**
     * @var string|null Campanya SEO a la que pertany
     */
    private $campana_seo;
    
    /**
     * @var string Objectiu SEO (branding, trafico, autoridad, conversiones)
     */
    private $objetivo_seo;
    
    /**
     * @var string Prioritat (alta, media, baja)
     */
    private $prioridad;
    
    // ============================================
    // 10. DADES TÈCNIQUES
    // ============================================
    
    /**
     * @var string|null IP del servidor origen
     */
    private $ip_origen;
    
    /**
     * @var string|null País del servidor origen
     */
    private $pais_origen;
    
    /**
     * @var string|null TLD del domini origen (.com, .es, etc.)
     */
    private $tld_origen;
    
    /**
     * @var string Estat d'indexació a Google
     */
    private $indexacion_origen;
    
    // ============================================
    // 11. HERRAMIENTAS EXTERNES
    // ============================================
    
    /**
     * @var string|null ID de SEMrush
     */
    private $semrush_id;
    
    /**
     * @var string|null ID d'Ahrefs
     */
    private $ahrefs_id;
    
    /**
     * @var string|null ID de Majestic
     */
    private $majestic_id;
    
    // ============================================
    // 12. CONTROL INTERN
    // ============================================
    
    /**
     * @var string|null Notes internes sobre el backlink
     */
    private $notas_internas;
    
    /**
     * @var string Data de creació del registre
     */
    private $fecha_creacion;
    
    /**
     * @var string Data de l'última actualització
     */
    private $fecha_actualizacion;
    
    
    // ============================================
    // CONSTRUCTOR I INICIALITZACIÓ
    // ============================================
    
    /**
     * Constructor de la classe
     * 
     * Si es passa un ID, carrega el backlink de la base de dades.
     * Si no, inicialitza un nou backlink buit.
     * 
     * @param int|null $id_offpage ID del backlink a carregar
     * @throws Exception Si hi ha error de connexió o el backlink no existeix
     */
    public function __construct($id_offpage = null) {
        $this->conn = Connexio::getInstance();
        $this->pdo = $this->conn->getConnexio();
        
        if ($id_offpage) {
            $this->id_offpage = $id_offpage;
            $this->carregarDades();
        } else {
            // Valors per defecte per a un nou backlink
            $this->estado = 'activo';
            $this->idioma_origen = 'es';
            $this->posicion_enlace = 'contenido';
            $this->nofollow = false;
            $this->sponsored = false;
            $this->ugc = false;
            $this->relevancia_tematica = 'media';
            $this->calidad_percibida = 'regular';
            $this->clicks_mensuales = 0;
            $this->traffic_estimado = 0;
            $this->contacto_realizado = false;
            $this->respuesta_recibida = false;
            $this->objetivo_seo = 'autoridad';
            $this->prioridad = 'media';
            $this->indexacion_origen = 'desconocido';
            $this->fecha_descubrimiento = date('Y-m-d');
        }
    }
    
    /**
     * Destructor de la classe
     * 
     * La connexió es gestiona automàticament pel Singleton,
     * no cal tancar-la manualment.
     */
    public function __destruct() {
        // Connexio és Singleton i es tanca automàticament
    }
    
    /**
     * Carrega les dades del backlink des de la base de dades
     * 
     * @throws Exception Si el backlink no existeix o hi ha error de consulta
     */
    private function carregarDades() {
        $sql = "SELECT * FROM seo_offpage WHERE id_offpage = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':id', $this->id_offpage, PDO::PARAM_INT);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$row) {
            throw new Exception("Backlink amb ID {$this->id_offpage} no trobat");
        }
        
        // Assignar totes les propietats
        foreach ($row as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
        
        // Convertir booleans
        $this->nofollow = (bool)$this->nofollow;
        $this->sponsored = (bool)$this->sponsored;
        $this->ugc = (bool)$this->ugc;
        $this->contacto_realizado = (bool)$this->contacto_realizado;
        $this->respuesta_recibida = (bool)$this->respuesta_recibida;
    }
    
    
    // ============================================
    // MÈTODES DE CREACIÓ I ACTUALITZACIÓ
    // ============================================
    
    /**
     * Crea un nou backlink a la base de dades
     * 
     * @param array $data Array associatiu amb les dades del backlink
     * @return SEO_OffPage_Links Nova instància del backlink creat
     * @throws Exception Si falten camps obligatoris o hi ha error
     */
    public static function crear($data) {
        // Validar camps obligatoris
        $required = ['url_origen', 'url_destino', 'anchor_text', 'dominio_origen', 'tipo_backlink'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                throw new Exception("El camp $field és obligatori");
            }
        }
        
        $conn = Connexio::getInstance();
        $pdo = $conn->getConnexio();
        
        // Preparar la consulta INSERT
        $fields = [];
        $placeholders = [];
        $values = [];
        
        foreach ($data as $key => $value) {
            $fields[] = $key;
            $placeholders[] = ":$key";
            $values[":$key"] = $value;
        }
        
        $sql = "INSERT INTO seo_offpage (" . implode(', ', $fields) . ") 
                VALUES (" . implode(', ', $placeholders) . ")";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($values);
        
        $id = $pdo->lastInsertId();
        
        return new self($id);
    }
    
    /**
     * Actualitza un camp específic del backlink
     * 
     * @param string $camp Nom del camp a actualitzar
     * @param mixed $valor Valor a assignar
     * @return bool True si s'ha actualitzat correctament
     * @throws Exception Si el camp no existeix o hi ha error
     */
    public function actualitzarCamp($camp, $valor) {
        if (!property_exists($this, $camp)) {
            throw new Exception("El camp $camp no existeix");
        }
        
        $sql = "UPDATE seo_offpage SET $camp = :valor WHERE id_offpage = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':valor', $valor);
        $stmt->bindParam(':id', $this->id_offpage, PDO::PARAM_INT);
        $stmt->execute();
        
        $this->$camp = $valor;
        
        return true;
    }
    
    /**
     * Actualitza múltiples camps del backlink
     * 
     * @param array $data Array associatiu amb els camps a actualitzar
     * @return bool True si s'ha actualitzat correctament
     * @throws Exception Si hi ha error a la consulta
     */
    public function actualitzarMultiplesCamps($data) {
        $sets = [];
        $values = [];
        
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $sets[] = "$key = :$key";
                $values[":$key"] = $value;
            }
        }
        
        if (empty($sets)) {
            throw new Exception("No hi ha camps vàlids per actualitzar");
        }
        
        $values[':id'] = $this->id_offpage;
        
        $sql = "UPDATE seo_offpage SET " . implode(', ', $sets) . " WHERE id_offpage = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($values);
        
        // Actualitzar les propietats de l'objecte
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
        
        return true;
    }
    
    /**
     * Elimina el backlink de la base de dades
     * 
     * @return bool True si s'ha eliminat correctament
     * @throws Exception Si hi ha error
     */
    public function eliminar() {
        $sql = "DELETE FROM seo_offpage WHERE id_offpage = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':id', $this->id_offpage, PDO::PARAM_INT);
        $stmt->execute();
        
        return true;
    }
    
    
    // ============================================
    // MÈTODES DE VERIFICACIÓ I ESTAT
    // ============================================
    
    /**
     * Verifica si el backlink encara existeix i està actiu
     * 
     * Comprova si la URL origen encara conté l'enllaç cap a la nostra URL.
     * Actualitza l'estat i la data de verificació.
     * 
     * @return array Resultat de la verificació amb estat i detalls
     */
    public function verificarBacklink() {
        try {
            // Obtenir contingut de la pàgina origen
            $context = stream_context_create([
                'http' => [
                    'timeout' => 10,
                    'user_agent' => 'Mozilla/5.0 (compatible; SEOBot/1.0)'
                ]
            ]);
            
            $contenido = @file_get_contents($this->url_origen, false, $context);
            
            if ($contenido === false) {
                // Error al accedir a la pàgina
                $this->actualitzarMultiplesCamps([
                    'estado' => 'roto',
                    'fecha_ultima_verificacion' => date('Y-m-d'),
                    'fecha_perdida' => date('Y-m-d')
                ]);
                
                return [
                    'estado' => 'roto',
                    'mensaje' => 'No es pot accedir a la URL origen'
                ];
            }
            
            // Comprovar si l'enllaç existeix
            $enlace_existe = strpos($contenido, $this->url_destino) !== false;
            
            if ($enlace_existe) {
                // El backlink està actiu
                $this->actualitzarMultiplesCamps([
                    'estado' => 'activo',
                    'fecha_ultima_verificacion' => date('Y-m-d')
                ]);
                
                return [
                    'estado' => 'activo',
                    'mensaje' => 'Backlink verificat i actiu'
                ];
            } else {
                // El backlink s'ha perdut
                $this->actualitzarMultiplesCamps([
                    'estado' => 'perdido',
                    'fecha_ultima_verificacion' => date('Y-m-d'),
                    'fecha_perdida' => date('Y-m-d')
                ]);
                
                return [
                    'estado' => 'perdido',
                    'mensaje' => 'L\'enllaç ja no existeix a la pàgina'
                ];
            }
            
        } catch (Exception $e) {
            error_log("Error verificant backlink {$this->id_offpage}: " . $e->getMessage());
            
            return [
                'estado' => 'error',
                'mensaje' => 'Error durant la verificació: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Marca el backlink com a perdut
     * 
     * @param string|null $notas Notes sobre per què s'ha perdut
     * @return bool True si s'ha actualitzat correctament
     */
    public function marcarComPerdut($notas = null) {
        $data = [
            'estado' => 'perdido',
            'fecha_perdida' => date('Y-m-d'),
            'fecha_ultima_verificacion' => date('Y-m-d')
        ];
        
        if ($notas) {
            $data['notas_internas'] = ($this->notas_internas ? $this->notas_internas . "\n\n" : '') . 
                                       date('Y-m-d H:i:s') . " - Perdut: " . $notas;
        }
        
        return $this->actualitzarMultiplesCamps($data);
    }
    
    /**
     * Marca el backlink com a recuperat (torna a actiu)
     * 
     * @param string|null $notas Notes sobre la recuperació
     * @return bool True si s'ha actualitzat correctament
     */
    public function marcarComRecuperat($notas = null) {
        $data = [
            'estado' => 'activo',
            'fecha_perdida' => null,
            'fecha_ultima_verificacion' => date('Y-m-d')
        ];
        
        if ($notas) {
            $data['notas_internas'] = ($this->notas_internas ? $this->notas_internas . "\n\n" : '') . 
                                       date('Y-m-d H:i:s') . " - Recuperat: " . $notas;
        }
        
        return $this->actualitzarMultiplesCamps($data);
    }
    
    
    // ============================================
    // MÈTODES DE CÀLCUL I ANÀLISI
    // ============================================
    
    /**
     * Calcula la puntuació de qualitat del backlink (0-100)
     * 
     * Factors considerats:
     * - Domain Authority / Domain Rating (30%)
     * - Trust Flow / Citation Flow (20%)
     * - Relevància temàtica (20%)
     * - Posició de l'enllaç (10%)
     * - Atributs nofollow/sponsored (10%)
     * - Tràfic del domini origen (10%)
     * 
     * @return int Puntuació de 0 a 100
     */
    public function calcularQualityScore() {
        $score = 0;
        
        // 1. Domain Authority (30 punts)
        if ($this->da_origen !== null) {
            $score += round(($this->da_origen / 100) * 30);
        } elseif ($this->dr_origen !== null) {
            $score += round(($this->dr_origen / 100) * 30);
        } else {
            $score += 10; // Puntuació per defecte si no hi ha dades
        }
        
        // 2. Trust Flow / Citation Flow (20 punts)
        if ($this->tf_origen !== null && $this->cf_origen !== null) {
            $trust_ratio = $this->cf_origen > 0 ? ($this->tf_origen / $this->cf_origen) : 0;
            if ($trust_ratio >= 1) {
                $score += 20; // Excel·lent ratio
            } elseif ($trust_ratio >= 0.7) {
                $score += 15; // Bon ratio
            } elseif ($trust_ratio >= 0.5) {
                $score += 10; // Ratio acceptable
            } else {
                $score += 5; // Ratio baix
            }
        } else {
            $score += 10; // Puntuació per defecte
        }
        
        // 3. Relevància temàtica (20 punts)
        switch ($this->relevancia_tematica) {
            case 'alta':
                $score += 20;
                break;
            case 'media':
                $score += 12;
                break;
            case 'baja':
                $score += 5;
                break;
        }
        
        // 4. Posició de l'enllaç (10 punts)
        switch ($this->posicion_enlace) {
            case 'contenido':
                $score += 10;
                break;
            case 'navegacion':
            case 'header':
                $score += 7;
                break;
            case 'sidebar':
                $score += 5;
                break;
            case 'footer':
            case 'comentarios':
                $score += 3;
                break;
        }
        
        // 5. Atributs de l'enllaç (10 punts)
        if (!$this->nofollow && !$this->sponsored) {
            $score += 10; // DoFollow i no patrocinador
        } elseif (!$this->nofollow) {
            $score += 7; // DoFollow però patrocinador
        } elseif (!$this->sponsored) {
            $score += 5; // NoFollow però no patrocinador
        } else {
            $score += 2; // NoFollow i patrocinador
        }
        
        // 6. Tràfic origen (10 punts)
        if ($this->traffic_origen !== null) {
            if ($this->traffic_origen >= 10000) {
                $score += 10;
            } elseif ($this->traffic_origen >= 5000) {
                $score += 7;
            } elseif ($this->traffic_origen >= 1000) {
                $score += 5;
            } elseif ($this->traffic_origen >= 100) {
                $score += 3;
            } else {
                $score += 1;
            }
        } else {
            $score += 5; // Puntuació per defecte
        }
        
        return min(100, $score);
    }
    
    /**
     * Calcula el valor estimat del backlink en euros
     * 
     * Basat en la qualitat, tràfic i autoritat del domini origen.
     * Fórmula: (Quality Score * Traffic * DA) / 10000
     * 
     * @return float Valor estimat en euros
     */
    public function calcularValorEstimado() {
        $quality_score = $this->calcularQualityScore();
        $traffic = $this->traffic_origen ?? 0;
        $da = $this->da_origen ?? $this->dr_origen ?? 30;
        
        // Multiplicador segons tipus de backlink
        $multiplicador = 1;
        switch ($this->tipo_backlink) {
            case 'prensa':
            case 'recursos_util':
                $multiplicador = 1.5;
                break;
            case 'guest_post':
            case 'colaboracion':
                $multiplicador = 1.3;
                break;
            case 'directorio':
            case 'foro':
                $multiplicador = 0.8;
                break;
            case 'blog_comentario':
            case 'social_media':
                $multiplicador = 0.5;
                break;
        }
        
        $valor = (($quality_score * $traffic * $da) / 10000) * $multiplicador;
        
        return round($valor, 2);
    }
    
    /**
     * Analitza l'anchor text del backlink
     * 
     * @return array Informació sobre l'anchor text (tipus, longitud, keywords)
     */
    public function analitzarAnchorText() {
        $anchor = strtolower(trim($this->anchor_text));
        $longitud = mb_strlen($anchor);
        
        // Determinar tipus d'anchor text
        $tipo = 'descriptivo';
        
        if (empty($anchor)) {
            $tipo = 'vacio';
        } elseif (preg_match('/^https?:\/\//', $anchor)) {
            $tipo = 'url_completa';
        } elseif (strpos($anchor, 'www.') === 0 || preg_match('/\.\w{2,}$/', $anchor)) {
            $tipo = 'dominio';
        } elseif (in_array($anchor, ['aquí', 'aqui', 'clic aquí', 'haz clic', 'click here', 'more', 'leer más'])) {
            $tipo = 'generico';
        } elseif (strpos($anchor, 'yanina') !== false || strpos($anchor, 'parisi') !== false) {
            $tipo = 'marca';
        }
        
        // Comptar paraules
        $palabras = str_word_count($anchor, 0, 'áéíóúàèìòùäëïöüñç');
        
        return [
            'tipo' => $tipo,
            'longitud' => $longitud,
            'palabras' => $palabras,
            'texto' => $this->anchor_text,
            'recomendacion' => $this->getRecomendacionAnchor($tipo, $longitud, $palabras)
        ];
    }
    
    /**
     * Proporciona recomanacions sobre l'anchor text
     * 
     * @param string $tipo Tipus d'anchor detectat
     * @param int $longitud Longitud en caràcters
     * @param int $palabras Nombre de paraules
     * @return string Recomanació
     */
    private function getRecomendacionAnchor($tipo, $longitud, $palabras) {
        switch ($tipo) {
            case 'vacio':
                return 'Anchor text buit - Intentar negociar un anchor descriptiu';
            case 'generico':
                return 'Anchor genèric - Millor utilitzar keywords o marca';
            case 'url_completa':
                return 'URL completa - Acceptable però millorable amb text descriptiu';
            case 'dominio':
                return 'Domini com anchor - Bon per branding';
            case 'marca':
                return 'Anchor de marca - Excel·lent per branding i seguretat';
            case 'descriptivo':
                if ($palabras > 5) {
                    return 'Anchor massa llarg - Ideal seria 2-4 paraules';
                } elseif ($palabras >= 2 && $palabras <= 4) {
                    return 'Anchor óptim - Longitud i descriptivitat adequades';
                } else {
                    return 'Anchor d\'una paraula - Podria ser més descriptiu';
                }
            default:
                return 'Analitzar manualment aquest anchor';
        }
    }
    
    
    // ============================================
    // MÈTODES ESTÀTICS DE CONSULTA
    // ============================================
    
    /**
     * Llista backlinks amb filtres opcionals
     * 
     * @param array $filtros Array associatiu amb filtres (estado, tipo_backlink, campana_seo, etc.)
     * @param string $order_by Camp per ordenar
     * @param string $order Direcció (ASC o DESC)
     * @param int|null $limit Límit de resultats
     * @return array Array d'objectes SEO_OffPage_Links
     */
    public static function llistarBacklinks($filtros = [], $order_by = 'fecha_descubrimiento', $order = 'DESC', $limit = null) {
        try {
            $conn = Connexio::getInstance();
            $pdo = $conn->getConnexio();
            
            $sql = "SELECT id_offpage FROM seo_offpage WHERE 1=1";
            $params = [];
            
            // Aplicar filtres
            if (!empty($filtros['estado'])) {
                $sql .= " AND estado = :estado";
                $params[':estado'] = $filtros['estado'];
            }
            
            if (!empty($filtros['tipo_backlink'])) {
                $sql .= " AND tipo_backlink = :tipo";
                $params[':tipo'] = $filtros['tipo_backlink'];
            }
            
            if (!empty($filtros['campana_seo'])) {
                $sql .= " AND campana_seo = :campana";
                $params[':campana'] = $filtros['campana_seo'];
            }
            
            if (!empty($filtros['relevancia_tematica'])) {
                $sql .= " AND relevancia_tematica = :relevancia";
                $params[':relevancia'] = $filtros['relevancia_tematica'];
            }
            
            if (!empty($filtros['prioridad'])) {
                $sql .= " AND prioridad = :prioridad";
                $params[':prioridad'] = $filtros['prioridad'];
            }
            
            if (!empty($filtros['dominio_origen'])) {
                $sql .= " AND dominio_origen LIKE :dominio";
                $params[':dominio'] = '%' . $filtros['dominio_origen'] . '%';
            }
            
            if (isset($filtros['da_min'])) {
                $sql .= " AND da_origen >= :da_min";
                $params[':da_min'] = $filtros['da_min'];
            }
            
            if (!empty($filtros['url_destino'])) {
                $sql .= " AND url_destino LIKE :url_destino";
                $params[':url_destino'] = '%' . $filtros['url_destino'] . '%';
            }
            
            // Ordenació
            $sql .= " ORDER BY $order_by $order";
            
            // Límit
            if ($limit) {
                $sql .= " LIMIT :limit";
            }
            
            $stmt = $pdo->prepare($sql);
            
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            
            if ($limit) {
                $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            }
            
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $backlinks = [];
            foreach ($rows as $row) {
                $backlinks[] = new self($row['id_offpage']);
            }
            
            return $backlinks;
            
        } catch (Exception $e) {
            error_log("Error en llistarBacklinks: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obté estadístiques globals dels backlinks
     * 
     * @return array Estadístiques detallades dels backlinks
     */
    public static function obtenirEstadistiquesGlobals() {
        try {
            $conn = Connexio::getInstance();
            $pdo = $conn->getConnexio();
            
            // Estadístiques bàsiques
            $sql = "SELECT 
                        COUNT(*) as total,
                        SUM(CASE WHEN estado = 'activo' THEN 1 ELSE 0 END) as activos,
                        SUM(CASE WHEN estado = 'perdido' THEN 1 ELSE 0 END) as perdidos,
                        SUM(CASE WHEN estado = 'roto' THEN 1 ELSE 0 END) as rotos,
                        AVG(da_origen) as da_promedio,
                        AVG(dr_origen) as dr_promedio,
                        SUM(CASE WHEN nofollow = 0 THEN 1 ELSE 0 END) as dofollow,
                        SUM(CASE WHEN nofollow = 1 THEN 1 ELSE 0 END) as nofollow,
                        SUM(clicks_mensuales) as clicks_totales,
                        SUM(valor_estimado) as valor_total
                    FROM seo_offpage";
            
            $stmt = $pdo->query($sql);
            $stats = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Estadístiques per tipus
            $sql_tipos = "SELECT tipo_backlink, COUNT(*) as cantidad 
                          FROM seo_offpage 
                          WHERE estado = 'activo'
                          GROUP BY tipo_backlink 
                          ORDER BY cantidad DESC";
            $stmt_tipos = $pdo->query($sql_tipos);
            $stats['por_tipo'] = $stmt_tipos->fetchAll(PDO::FETCH_ASSOC);
            
            // Estadístiques per rellevància
            $sql_rel = "SELECT relevancia_tematica, COUNT(*) as cantidad 
                        FROM seo_offpage 
                        WHERE estado = 'activo'
                        GROUP BY relevancia_tematica";
            $stmt_rel = $pdo->query($sql_rel);
            $stats['por_relevancia'] = $stmt_rel->fetchAll(PDO::FETCH_ASSOC);
            
            // Top 10 dominis
            $sql_top = "SELECT dominio_origen, COUNT(*) as backlinks, AVG(da_origen) as da 
                        FROM seo_offpage 
                        WHERE estado = 'activo'
                        GROUP BY dominio_origen 
                        ORDER BY backlinks DESC 
                        LIMIT 10";
            $stmt_top = $pdo->query($sql_top);
            $stats['top_dominios'] = $stmt_top->fetchAll(PDO::FETCH_ASSOC);
            
            // Calcular puntuació global
            $score = 0;
            if ($stats['total'] > 0) {
                $score += min(30, ($stats['activos'] / max(1, $stats['total'])) * 30); // 30 punts per % actius
                $score += min(25, ($stats['da_promedio'] / 100) * 25); // 25 punts per DA mitjà
                $score += min(20, ($stats['dofollow'] / max(1, $stats['total'])) * 20); // 20 punts per % dofollow
                $score += min(15, count($stats['top_dominios']) * 1.5); // 15 punts per diversitat
                $score += min(10, ($stats['clicks_totales'] / 1000)); // 10 punts per tràfic
            }
            
            $stats['score_global'] = round($score);
            
            // Determinar estat
            if ($stats['score_global'] >= 80) {
                $stats['estado_global'] = 'Excelente';
            } elseif ($stats['score_global'] >= 60) {
                $stats['estado_global'] = 'Bueno';
            } elseif ($stats['score_global'] >= 40) {
                $stats['estado_global'] = 'Regular';
            } else {
                $stats['estado_global'] = 'Necesita mejora';
            }
            
            return $stats;
            
        } catch (Exception $e) {
            error_log("Error en obtenirEstadistiquesGlobals: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Obté els backlinks que necessiten verificació
     * 
     * Retorna backlinks que no s'han verificat recentment (més de 30 dies)
     * 
     * @param int $dies Dies des de l'última verificació (per defecte 30)
     * @return array Array d'objectes SEO_OffPage_Links
     */
    public static function obtenirBacklinksPerVerificar($dies = 30) {
        try {
            $conn = Connexio::getInstance();
            $pdo = $conn->getConnexio();
            
            $fecha_limite = date('Y-m-d', strtotime("-$dies days"));
            
            $sql = "SELECT id_offpage FROM seo_offpage 
                    WHERE estado = 'activo' 
                    AND (fecha_ultima_verificacion IS NULL OR fecha_ultima_verificacion < :fecha)
                    ORDER BY fecha_ultima_verificacion ASC, da_origen DESC
                    LIMIT 50";
            
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':fecha', $fecha_limite);
            $stmt->execute();
            
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $backlinks = [];
            foreach ($rows as $row) {
                $backlinks[] = new self($row['id_offpage']);
            }
            
            return $backlinks;
            
        } catch (Exception $e) {
            error_log("Error en obtenirBacklinksPerVerificar: " . $e->getMessage());
            return [];
        }
    }
    
    
    // ============================================
    // GETTERS
    // ============================================
    
    public function getId() { return $this->id_offpage; }
    public function getUrlOrigen() { return $this->url_origen; }
    public function getUrlDestino() { return $this->url_destino; }
    public function getAnchorText() { return $this->anchor_text; }
    public function getDominioOrigen() { return $this->dominio_origen; }
    public function getDaOrigen() { return $this->da_origen; }
    public function getDrOrigen() { return $this->dr_origen; }
    public function getTfOrigen() { return $this->tf_origen; }
    public function getCfOrigen() { return $this->cf_origen; }
    public function getTituloPaginaOrigen() { return $this->titulo_pagina_origen; }
    public function getDaPaginaOrigen() { return $this->da_pagina_origen; }
    public function getTrafficOrigen() { return $this->traffic_origen; }
    public function getIdiomaOrigen() { return $this->idioma_origen; }
    public function getTipoBacklink() { return $this->tipo_backlink; }
    public function getContextoBacklink() { return $this->contexto_backlink; }
    public function getPosicionEnlace() { return $this->posicion_enlace; }
    public function isNofollow() { return $this->nofollow; }
    public function isSponsored() { return $this->sponsored; }
    public function isUgc() { return $this->ugc; }
    public function getRelevanciaTematica() { return $this->relevancia_tematica; }
    public function getCalidadPercibida() { return $this->calidad_percibida; }
    public function getAutoridadTematica() { return $this->autoridad_tematica; }
    public function getFechaDescubrimiento() { return $this->fecha_descubrimiento; }
    public function getFechaUltimaVerificacion() { return $this->fecha_ultima_verificacion; }
    public function getEstado() { return $this->estado; }
    public function getFechaPerdida() { return $this->fecha_perdida; }
    public function getClicksMensuales() { return $this->clicks_mensuales; }
    public function getTrafficEstimado() { return $this->traffic_estimado; }
    public function getValorEstimado() { return $this->valor_estimado; }
    public function isContactoRealizado() { return $this->contacto_realizado; }
    public function getFechaContacto() { return $this->fecha_contacto; }
    public function isRespuestaRecibida() { return $this->respuesta_recibida; }
    public function getNotasContacto() { return $this->notas_contacto; }
    public function getCampanaSeo() { return $this->campana_seo; }
    public function getObjetivoSeo() { return $this->objetivo_seo; }
    public function getPrioridad() { return $this->prioridad; }
    public function getIpOrigen() { return $this->ip_origen; }
    public function getPaisOrigen() { return $this->pais_origen; }
    public function getTldOrigen() { return $this->tld_origen; }
    public function getIndexacionOrigen() { return $this->indexacion_origen; }
    public function getSemrushId() { return $this->semrush_id; }
    public function getAhrefsId() { return $this->ahrefs_id; }
    public function getMajesticId() { return $this->majestic_id; }
    public function getNotasInternas() { return $this->notas_internas; }
    public function getFechaCreacion() { return $this->fecha_creacion; }
    public function getFechaActualizacion() { return $this->fecha_actualizacion; }
    
    
    // ============================================
    // SETTERS
    // ============================================
    
    public function setUrlOrigen($url) { return $this->actualitzarCamp('url_origen', $url); }
    public function setUrlDestino($url) { return $this->actualitzarCamp('url_destino', $url); }
    public function setAnchorText($text) { return $this->actualitzarCamp('anchor_text', $text); }
    public function setDominioOrigen($dominio) { return $this->actualitzarCamp('dominio_origen', $dominio); }
    public function setDaOrigen($da) { return $this->actualitzarCamp('da_origen', $da); }
    public function setDrOrigen($dr) { return $this->actualitzarCamp('dr_origen', $dr); }
    public function setTfOrigen($tf) { return $this->actualitzarCamp('tf_origen', $tf); }
    public function setCfOrigen($cf) { return $this->actualitzarCamp('cf_origen', $cf); }
    public function setTituloPaginaOrigen($titulo) { return $this->actualitzarCamp('titulo_pagina_origen', $titulo); }
    public function setDaPaginaOrigen($da) { return $this->actualitzarCamp('da_pagina_origen', $da); }
    public function setTrafficOrigen($traffic) { return $this->actualitzarCamp('traffic_origen', $traffic); }
    public function setIdiomaOrigen($idioma) { return $this->actualitzarCamp('idioma_origen', $idioma); }
    public function setTipoBacklink($tipo) { return $this->actualitzarCamp('tipo_backlink', $tipo); }
    public function setContextoBacklink($contexto) { return $this->actualitzarCamp('contexto_backlink', $contexto); }
    public function setPosicionEnlace($posicion) { return $this->actualitzarCamp('posicion_enlace', $posicion); }
    public function setNofollow($nofollow) { return $this->actualitzarCamp('nofollow', $nofollow ? 1 : 0); }
    public function setSponsored($sponsored) { return $this->actualitzarCamp('sponsored', $sponsored ? 1 : 0); }
    public function setUgc($ugc) { return $this->actualitzarCamp('ugc', $ugc ? 1 : 0); }
    public function setRelevanciaTematica($relevancia) { return $this->actualitzarCamp('relevancia_tematica', $relevancia); }
    public function setCalidadPercibida($calidad) { return $this->actualitzarCamp('calidad_percibida', $calidad); }
    public function setAutoridadTematica($autoridad) { return $this->actualitzarCamp('autoridad_tematica', $autoridad); }
    public function setEstado($estado) { return $this->actualitzarCamp('estado', $estado); }
    public function setClicksMensuales($clicks) { return $this->actualitzarCamp('clicks_mensuales', $clicks); }
    public function setTrafficEstimado($traffic) { return $this->actualitzarCamp('traffic_estimado', $traffic); }
    public function setValorEstimado($valor) { return $this->actualitzarCamp('valor_estimado', $valor); }
    public function setContactoRealizado($contacto) { return $this->actualitzarCamp('contacto_realizado', $contacto ? 1 : 0); }
    public function setFechaContacto($fecha) { return $this->actualitzarCamp('fecha_contacto', $fecha); }
    public function setRespuestaRecibida($respuesta) { return $this->actualitzarCamp('respuesta_recibida', $respuesta ? 1 : 0); }
    public function setNotasContacto($notas) { return $this->actualitzarCamp('notas_contacto', $notas); }
    public function setCampanaSeo($campana) { return $this->actualitzarCamp('campana_seo', $campana); }
    public function setObjetivoSeo($objetivo) { return $this->actualitzarCamp('objetivo_seo', $objetivo); }
    public function setPrioridad($prioridad) { return $this->actualitzarCamp('prioridad', $prioridad); }
    public function setIpOrigen($ip) { return $this->actualitzarCamp('ip_origen', $ip); }
    public function setPaisOrigen($pais) { return $this->actualitzarCamp('pais_origen', $pais); }
    public function setTldOrigen($tld) { return $this->actualitzarCamp('tld_origen', $tld); }
    public function setIndexacionOrigen($indexacion) { return $this->actualitzarCamp('indexacion_origen', $indexacion); }
    public function setSemrushId($id) { return $this->actualitzarCamp('semrush_id', $id); }
    public function setAhrefsId($id) { return $this->actualitzarCamp('ahrefs_id', $id); }
    public function setMajesticId($id) { return $this->actualitzarCamp('majestic_id', $id); }
    public function setNotasInternas($notas) { return $this->actualitzarCamp('notas_internas', $notas); }
    
    
    // ============================================
    // MÈTODE AUXILIAR: ARRAY COMPLET
    // ============================================
    
    /**
     * Retorna totes les dades del backlink com array associatiu
     * 
     * @return array Array amb totes les propietats del backlink
     */
    public function toArray() {
        return [
            'id_offpage' => $this->id_offpage,
            'url_origen' => $this->url_origen,
            'url_destino' => $this->url_destino,
            'anchor_text' => $this->anchor_text,
            'dominio_origen' => $this->dominio_origen,
            'da_origen' => $this->da_origen,
            'dr_origen' => $this->dr_origen,
            'tf_origen' => $this->tf_origen,
            'cf_origen' => $this->cf_origen,
            'titulo_pagina_origen' => $this->titulo_pagina_origen,
            'da_pagina_origen' => $this->da_pagina_origen,
            'traffic_origen' => $this->traffic_origen,
            'idioma_origen' => $this->idioma_origen,
            'tipo_backlink' => $this->tipo_backlink,
            'contexto_backlink' => $this->contexto_backlink,
            'posicion_enlace' => $this->posicion_enlace,
            'nofollow' => $this->nofollow,
            'sponsored' => $this->sponsored,
            'ugc' => $this->ugc,
            'relevancia_tematica' => $this->relevancia_tematica,
            'calidad_percibida' => $this->calidad_percibida,
            'autoridad_tematica' => $this->autoridad_tematica,
            'fecha_descubrimiento' => $this->fecha_descubrimiento,
            'fecha_ultima_verificacion' => $this->fecha_ultima_verificacion,
            'estado' => $this->estado,
            'fecha_perdida' => $this->fecha_perdida,
            'clicks_mensuales' => $this->clicks_mensuales,
            'traffic_estimado' => $this->traffic_estimado,
            'valor_estimado' => $this->valor_estimado,
            'contacto_realizado' => $this->contacto_realizado,
            'fecha_contacto' => $this->fecha_contacto,
            'respuesta_recibida' => $this->respuesta_recibida,
            'notas_contacto' => $this->notas_contacto,
            'campana_seo' => $this->campana_seo,
            'objetivo_seo' => $this->objetivo_seo,
            'prioridad' => $this->prioridad,
            'ip_origen' => $this->ip_origen,
            'pais_origen' => $this->pais_origen,
            'tld_origen' => $this->tld_origen,
            'indexacion_origen' => $this->indexacion_origen,
            'semrush_id' => $this->semrush_id,
            'ahrefs_id' => $this->ahrefs_id,
            'majestic_id' => $this->majestic_id,
            'notas_internas' => $this->notas_internas,
            'fecha_creacion' => $this->fecha_creacion,
            'fecha_actualizacion' => $this->fecha_actualizacion,
            'quality_score' => $this->calcularQualityScore(),
            'anchor_analysis' => $this->analitzarAnchorText()
        ];
    }
}
