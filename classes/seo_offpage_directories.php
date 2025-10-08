<?php
/**
 * Classe SEO_OffPage_Directories
 * 
 * Gestiona els directoris empresarials i de negocis on s'inscriu el lloc web
 * per millorar la presència Off-Page SEO, autoritat local i visibilitat online.
 * 
 * Els directoris són plataformes externes (Google Business, Yelp, Páginas Amarillas,
 * directoris de psicòlegs, etc.) on es registra el negoci amb informació de contacte,
 * categoria i enllaç al lloc web.
 * 
 * Funcionalitats principals:
 * - Gestió de registres en directoris
 * - Seguiment d'estats (pendent, enviat, aprovat, actiu)
 * - Control de costos i ROI
 * - Anàlisi de directoris per categoria
 * - Estadístiques de cobertura en directoris
 * 
 * @package     YaninaParisi
 * @subpackage  Classes
 * @category    SEO
 * @author      Marc Mataró
 * @version     1.0.0
 * @since       2025-10-07
 */

require_once __DIR__ . '/connexio.php';

class SEO_OffPage_Directories {
    
    // ============================================
    // PROPIETATS PRIVADES
    // ============================================
    
    /**
     * @var int|null ID del directori
     */
    private $id_directorio;
    
    /**
     * @var Connexio Instància de la connexió a la base de dades
     */
    private $conn;
    
    /**
     * @var PDO Objecte PDO per a consultes
     */
    private $pdo;
    
    /**
     * @var string Nom del directori
     */
    private $nombre;
    
    /**
     * @var string URL del directori
     */
    private $url;
    
    /**
     * @var string Categoria del directori (salud, psicologia, negocios, locales, generico)
     */
    private $categoria;
    
    /**
     * @var int|null Domain Authority del directori (0-100)
     */
    private $da_directorio;
    
    /**
     * @var float Cost d'inscripció/manteniment anual
     */
    private $costo;
    
    /**
     * @var string Idioma principal del directori (ca, es, en, other)
     */
    private $idioma;
    
    /**
     * @var bool Si l'enllaç des del directori és nofollow
     */
    private $nofollow;
    
    /**
     * @var bool Si el directori permet personalitzar l'anchor text
     */
    private $permite_anchor_personalizado;
    
    /**
     * @var string Estat del registre (pendiente, enviado, aprobado, rechazado, activo)
     */
    private $estado;
    
    /**
     * @var string|null Data d'enviament de la sol·licitud
     */
    private $fecha_envio;
    
    /**
     * @var string|null Data d'aprovació del registre
     */
    private $fecha_aprobacion;
    
    /**
     * @var string|null Notes i observacions sobre el directori
     */
    private $notas;
    
    /**
     * @var string Data de creació del registre
     */
    private $fecha_creacion;
    
    
    // ============================================
    // CONSTRUCTOR I INICIALITZACIÓ
    // ============================================
    
    /**
     * Constructor de la classe
     * 
     * Si es passa un ID, carrega el directori de la base de dades.
     * Si no, inicialitza un nou directori buit amb valors per defecte.
     * 
     * @param int|null $id_directorio ID del directori a carregar
     * @throws Exception Si hi ha error de connexió o el directori no existeix
     */
    public function __construct($id_directorio = null) {
        $this->conn = Connexio::getInstance();
        $this->pdo = $this->conn->getConnexio();
        
        if ($id_directorio) {
            $this->id_directorio = $id_directorio;
            $this->carregarDades();
        } else {
            // Valors per defecte per a un nou directori
            $this->categoria = 'psicologia';
            $this->costo = 0.00;
            $this->idioma = 'es';
            $this->nofollow = false;
            $this->permite_anchor_personalizado = true;
            $this->estado = 'pendiente';
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
     * Carrega les dades del directori des de la base de dades
     * 
     * @throws Exception Si el directori no existeix o hi ha error de consulta
     */
    private function carregarDades() {
        $sql = "SELECT * FROM seo_offpage_directorios WHERE id_directorio = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':id', $this->id_directorio, PDO::PARAM_INT);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$row) {
            throw new Exception("Directori amb ID {$this->id_directorio} no trobat");
        }
        
        // Assignar totes les propietats
        foreach ($row as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
        
        // Convertir booleans
        $this->nofollow = (bool)$this->nofollow;
        $this->permite_anchor_personalizado = (bool)$this->permite_anchor_personalizado;
    }
    
    
    // ============================================
    // MÈTODES DE CREACIÓ I ACTUALITZACIÓ
    // ============================================
    
    /**
     * Crea un nou directori a la base de dades
     * 
     * @param array $data Array associatiu amb les dades del directori
     * @return SEO_OffPage_Directories Nova instància del directori creat
     * @throws Exception Si falten camps obligatoris o hi ha error
     */
    public static function crear($data) {
        // Validar camps obligatoris
        $required = ['nombre', 'url'];
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
        
        $sql = "INSERT INTO seo_offpage_directorios (" . implode(', ', $fields) . ") 
                VALUES (" . implode(', ', $placeholders) . ")";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($values);
        
        $id = $pdo->lastInsertId();
        
        return new self($id);
    }
    
    /**
     * Actualitza un camp específic del directori
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
        
        $sql = "UPDATE seo_offpage_directorios SET $camp = :valor WHERE id_directorio = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':valor', $valor);
        $stmt->bindParam(':id', $this->id_directorio, PDO::PARAM_INT);
        $stmt->execute();
        
        $this->$camp = $valor;
        
        return true;
    }
    
    /**
     * Actualitza múltiples camps del directori
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
        
        $values[':id'] = $this->id_directorio;
        
        $sql = "UPDATE seo_offpage_directorios SET " . implode(', ', $sets) . " WHERE id_directorio = :id";
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
     * Elimina el directori de la base de dades
     * 
     * @return bool True si s'ha eliminat correctament
     * @throws Exception Si hi ha error
     */
    public function eliminar() {
        $sql = "DELETE FROM seo_offpage_directorios WHERE id_directorio = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':id', $this->id_directorio, PDO::PARAM_INT);
        $stmt->execute();
        
        return true;
    }
    
    
    // ============================================
    // MÈTODES DE GESTIÓ D'ESTAT
    // ============================================
    
    /**
     * Marca el directori com enviat
     * 
     * Actualitza l'estat a 'enviado' i estableix la data d'enviament.
     * 
     * @param string|null $fecha Data d'enviament (per defecte avui)
     * @param string|null $notas Notes sobre l'enviament
     * @return bool True si s'ha actualitzat correctament
     */
    public function marcarComEnviat($fecha = null, $notas = null) {
        $fecha = $fecha ?: date('Y-m-d');
        
        $data = [
            'estado' => 'enviado',
            'fecha_envio' => $fecha
        ];
        
        if ($notas) {
            $data['notas'] = ($this->notas ? $this->notas . "\n\n" : '') . 
                             date('Y-m-d H:i:s') . " - Enviat: " . $notas;
        }
        
        return $this->actualitzarMultiplesCamps($data);
    }
    
    /**
     * Marca el directori com aprovat
     * 
     * Actualitza l'estat a 'aprobado' i estableix la data d'aprovació.
     * 
     * @param string|null $fecha Data d'aprovació (per defecte avui)
     * @param string|null $notas Notes sobre l'aprovació
     * @return bool True si s'ha actualitzat correctament
     */
    public function marcarComAprovat($fecha = null, $notas = null) {
        $fecha = $fecha ?: date('Y-m-d');
        
        $data = [
            'estado' => 'aprobado',
            'fecha_aprobacion' => $fecha
        ];
        
        if ($notas) {
            $data['notas'] = ($this->notas ? $this->notas . "\n\n" : '') . 
                             date('Y-m-d H:i:s') . " - Aprovat: " . $notas;
        }
        
        return $this->actualitzarMultiplesCamps($data);
    }
    
    /**
     * Marca el directori com actiu
     * 
     * Actualitza l'estat a 'activo', indicant que el perfil està operatiu
     * i visible al directori.
     * 
     * @param string|null $notas Notes sobre l'activació
     * @return bool True si s'ha actualitzat correctament
     */
    public function marcarComActiu($notas = null) {
        $data = [
            'estado' => 'activo'
        ];
        
        if ($notas) {
            $data['notas'] = ($this->notas ? $this->notas . "\n\n" : '') . 
                             date('Y-m-d H:i:s') . " - Actiu: " . $notas;
        }
        
        return $this->actualitzarMultiplesCamps($data);
    }
    
    /**
     * Marca el directori com rebutjat
     * 
     * Actualitza l'estat a 'rechazado', indicant que la sol·licitud
     * no ha estat acceptada.
     * 
     * @param string $motivo Motiu del rebuig
     * @return bool True si s'ha actualitzat correctament
     */
    public function marcarComRebutjat($motivo) {
        $data = [
            'estado' => 'rechazado',
            'notas' => ($this->notas ? $this->notas . "\n\n" : '') . 
                       date('Y-m-d H:i:s') . " - Rebutjat: " . $motivo
        ];
        
        return $this->actualitzarMultiplesCamps($data);
    }
    
    
    // ============================================
    // MÈTODES DE CÀLCUL I ANÀLISI
    // ============================================
    
    /**
     * Calcula la puntuació de qualitat del directori (0-100)
     * 
     * Factors considerats:
     * - Domain Authority (40%)
     * - Categoria específica (20%)
     * - DoFollow vs NoFollow (15%)
     * - Permet anchor personalitzat (15%)
     * - Cost vs valor (10%)
     * 
     * @return int Puntuació de 0 a 100
     */
    public function calcularQualityScore() {
        $score = 0;
        
        // 1. Domain Authority (40 punts)
        if ($this->da_directorio !== null) {
            $score += round(($this->da_directorio / 100) * 40);
        } else {
            $score += 15; // Puntuació per defecte si no hi ha DA
        }
        
        // 2. Categoria específica (20 punts)
        switch ($this->categoria) {
            case 'psicologia':
                $score += 20; // Màxima rellevància
                break;
            case 'salud':
                $score += 15; // Alta rellevància
                break;
            case 'locales':
                $score += 12; // Bona rellevància (SEO local)
                break;
            case 'negocios':
                $score += 8; // Rellevància mitjana
                break;
            case 'generico':
                $score += 5; // Baixa rellevància
                break;
        }
        
        // 3. DoFollow vs NoFollow (15 punts)
        if (!$this->nofollow) {
            $score += 15; // DoFollow aporta valor SEO
        } else {
            $score += 5; // NoFollow té menys valor però segueix sent útil
        }
        
        // 4. Permet anchor personalitzat (15 punts)
        if ($this->permite_anchor_personalizado) {
            $score += 15; // Permet optimització de keywords
        } else {
            $score += 7; // Menys flexibilitat
        }
        
        // 5. Cost vs valor (10 punts)
        if ($this->costo == 0) {
            $score += 10; // Gratuït és sempre bo
        } elseif ($this->costo <= 50) {
            $score += 8; // Cost baix acceptable
        } elseif ($this->costo <= 150) {
            $score += 5; // Cost mitjà
        } else {
            $score += 2; // Cost alt (ha de justificar-se amb DA alt)
        }
        
        return min(100, $score);
    }
    
    /**
     * Calcula el ROI (Return on Investment) del directori
     * 
     * Basat en el quality score, DA i cost anual.
     * Retorna un valor estimat de retorn per cada euro invertit.
     * 
     * @return float Ratio ROI (valor_generat / cost)
     */
    public function calcularROI() {
        if ($this->costo <= 0) {
            return 999; // ROI infinit per directoris gratuïts
        }
        
        $quality_score = $this->calcularQualityScore();
        $da = $this->da_directorio ?? 30;
        
        // Valor estimat generat pel directori
        $valor_generado = ($quality_score * $da) / 10;
        
        // ROI = Valor generat / Cost
        $roi = $valor_generado / $this->costo;
        
        return round($roi, 2);
    }
    
    /**
     * Calcula els dies des de l'enviament
     * 
     * @return int|null Nombre de dies o null si no s'ha enviat
     */
    public function getDiesDesDeEnviament() {
        if (!$this->fecha_envio) {
            return null;
        }
        
        $fecha_envio = new DateTime($this->fecha_envio);
        $fecha_actual = new DateTime();
        $diferencia = $fecha_actual->diff($fecha_envio);
        
        return $diferencia->days;
    }
    
    /**
     * Calcula els dies des de l'aprovació
     * 
     * @return int|null Nombre de dies o null si no s'ha aprovat
     */
    public function getDiesDesDeAprovacio() {
        if (!$this->fecha_aprobacion) {
            return null;
        }
        
        $fecha_aprobacion = new DateTime($this->fecha_aprobacion);
        $fecha_actual = new DateTime();
        $diferencia = $fecha_actual->diff($fecha_aprobacion);
        
        return $diferencia->days;
    }
    
    /**
     * Determina si el directori està pendent de revisió
     * 
     * Considera pendent si ha passat més de 15 dies des de l'enviament
     * i encara no ha estat aprovat o rebutjat.
     * 
     * @return bool True si està pendent de revisió
     */
    public function isPendienteRevision() {
        if ($this->estado !== 'enviado') {
            return false;
        }
        
        $dies = $this->getDiesDesDeEnviament();
        
        return $dies !== null && $dies > 15;
    }
    
    
    // ============================================
    // MÈTODES ESTÀTICS DE CONSULTA
    // ============================================
    
    /**
     * Llista directoris amb filtres opcionals
     * 
     * @param array $filtros Array associatiu amb filtres (estado, categoria, idioma, etc.)
     * @param string $order_by Camp per ordenar
     * @param string $order Direcció (ASC o DESC)
     * @param int|null $limit Límit de resultats
     * @return array Array d'objectes SEO_OffPage_Directories
     */
    public static function llistarDirectoris($filtros = [], $order_by = 'nombre', $order = 'ASC', $limit = null) {
        try {
            $conn = Connexio::getInstance();
            $pdo = $conn->getConnexio();
            
            $sql = "SELECT id_directorio FROM seo_offpage_directorios WHERE 1=1";
            $params = [];
            
            // Aplicar filtres
            if (!empty($filtros['estado'])) {
                $sql .= " AND estado = :estado";
                $params[':estado'] = $filtros['estado'];
            }
            
            if (!empty($filtros['categoria'])) {
                $sql .= " AND categoria = :categoria";
                $params[':categoria'] = $filtros['categoria'];
            }
            
            if (!empty($filtros['idioma'])) {
                $sql .= " AND idioma = :idioma";
                $params[':idioma'] = $filtros['idioma'];
            }
            
            if (isset($filtros['nofollow'])) {
                $sql .= " AND nofollow = :nofollow";
                $params[':nofollow'] = $filtros['nofollow'] ? 1 : 0;
            }
            
            if (isset($filtros['da_min'])) {
                $sql .= " AND da_directorio >= :da_min";
                $params[':da_min'] = $filtros['da_min'];
            }
            
            if (isset($filtros['costo_max'])) {
                $sql .= " AND costo <= :costo_max";
                $params[':costo_max'] = $filtros['costo_max'];
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
            
            $directorios = [];
            foreach ($rows as $row) {
                $directorios[] = new self($row['id_directorio']);
            }
            
            return $directorios;
            
        } catch (Exception $e) {
            error_log("Error en llistarDirectoris: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obté estadístiques globals dels directoris
     * 
     * @return array Estadístiques detallades dels directoris
     */
    public static function obtenirEstadistiquesGlobals() {
        try {
            $conn = Connexio::getInstance();
            $pdo = $conn->getConnexio();
            
            // Estadístiques bàsiques
            $sql = "SELECT 
                        COUNT(*) as total,
                        SUM(CASE WHEN estado = 'pendiente' THEN 1 ELSE 0 END) as pendientes,
                        SUM(CASE WHEN estado = 'enviado' THEN 1 ELSE 0 END) as enviados,
                        SUM(CASE WHEN estado = 'aprobado' THEN 1 ELSE 0 END) as aprobados,
                        SUM(CASE WHEN estado = 'rechazado' THEN 1 ELSE 0 END) as rechazados,
                        SUM(CASE WHEN estado = 'activo' THEN 1 ELSE 0 END) as activos,
                        AVG(da_directorio) as da_promedio,
                        SUM(CASE WHEN nofollow = 0 THEN 1 ELSE 0 END) as dofollow,
                        SUM(CASE WHEN nofollow = 1 THEN 1 ELSE 0 END) as nofollow,
                        SUM(costo) as costo_total_anual
                    FROM seo_offpage_directorios";
            
            $stmt = $pdo->query($sql);
            $stats = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Estadístiques per categoria
            $sql_categorias = "SELECT categoria, COUNT(*) as cantidad 
                               FROM seo_offpage_directorios 
                               GROUP BY categoria 
                               ORDER BY cantidad DESC";
            $stmt_categorias = $pdo->query($sql_categorias);
            $stats['por_categoria'] = $stmt_categorias->fetchAll(PDO::FETCH_ASSOC);
            
            // Estadístiques per idioma
            $sql_idiomas = "SELECT idioma, COUNT(*) as cantidad 
                            FROM seo_offpage_directorios 
                            GROUP BY idioma 
                            ORDER BY cantidad DESC";
            $stmt_idiomas = $pdo->query($sql_idiomas);
            $stats['por_idioma'] = $stmt_idiomas->fetchAll(PDO::FETCH_ASSOC);
            
            // Top 10 directoris per DA
            $sql_top = "SELECT id_directorio 
                        FROM seo_offpage_directorios 
                        WHERE da_directorio IS NOT NULL 
                        ORDER BY da_directorio DESC 
                        LIMIT 10";
            $stmt_top = $pdo->query($sql_top);
            $rows_top = $stmt_top->fetchAll(PDO::FETCH_ASSOC);
            
            $stats['top_directorios'] = [];
            foreach ($rows_top as $row) {
                $dir = new self($row['id_directorio']);
                $stats['top_directorios'][] = [
                    'id' => $dir->getId(),
                    'nombre' => $dir->getNombre(),
                    'da' => $dir->getDaDirectorio(),
                    'categoria' => $dir->getCategoria(),
                    'estado' => $dir->getEstado()
                ];
            }
            
            // Calcular score global
            $score = 0;
            if ($stats['total'] > 0) {
                // Percentatge d'actius (30 punts)
                $score += min(30, ($stats['activos'] / $stats['total']) * 30);
                
                // DA promig (30 punts)
                $score += min(30, ($stats['da_promedio'] / 100) * 30);
                
                // Percentatge DoFollow (20 punts)
                $score += min(20, ($stats['dofollow'] / max(1, $stats['total'])) * 20);
                
                // Nombre total de directoris (10 punts)
                $score += min(10, ($stats['total'] / 20) * 10);
                
                // Diversitat de categories (10 punts)
                $score += min(10, count($stats['por_categoria']) * 2);
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
     * Obté els directoris pendents d'enviament
     * 
     * @return array Array d'objectes SEO_OffPage_Directories
     */
    public static function obtenirPendentsEnviament() {
        return self::llistarDirectoris(['estado' => 'pendiente'], 'nombre', 'ASC');
    }
    
    /**
     * Obté els directoris pendents de revisió (enviats fa més de 15 dies)
     * 
     * @return array Array d'objectes SEO_OffPage_Directories
     */
    public static function obtenirPendentsRevisio() {
        try {
            $conn = Connexio::getInstance();
            $pdo = $conn->getConnexio();
            
            $fecha_limite = date('Y-m-d', strtotime('-15 days'));
            
            $sql = "SELECT id_directorio FROM seo_offpage_directorios 
                    WHERE estado = 'enviado' 
                    AND fecha_envio <= :fecha_limite
                    ORDER BY fecha_envio ASC";
            
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':fecha_limite', $fecha_limite);
            $stmt->execute();
            
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $directorios = [];
            foreach ($rows as $row) {
                $directorios[] = new self($row['id_directorio']);
            }
            
            return $directorios;
            
        } catch (Exception $e) {
            error_log("Error en obtenirPendentsRevisio: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obté els millors directoris per registrar-se
     * 
     * Retorna directoris gratuïts o de baix cost amb alta autoritat
     * 
     * @param int $limit Nombre màxim de resultats
     * @return array Array d'objectes SEO_OffPage_Directories
     */
    public static function obtenirMillorsOpcions($limit = 10) {
        try {
            $conn = Connexio::getInstance();
            $pdo = $conn->getConnexio();
            
            $sql = "SELECT id_directorio FROM seo_offpage_directorios 
                    WHERE estado = 'pendiente' 
                    AND costo <= 50 
                    AND da_directorio >= 30
                    ORDER BY da_directorio DESC, costo ASC 
                    LIMIT :limit";
            
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $directorios = [];
            foreach ($rows as $row) {
                $directorios[] = new self($row['id_directorio']);
            }
            
            return $directorios;
            
        } catch (Exception $e) {
            error_log("Error en obtenirMillorsOpcions: " . $e->getMessage());
            return [];
        }
    }
    
    
    // ============================================
    // GETTERS
    // ============================================
    
    public function getId() { return $this->id_directorio; }
    public function getNombre() { return $this->nombre; }
    public function getUrl() { return $this->url; }
    public function getCategoria() { return $this->categoria; }
    public function getDaDirectorio() { return $this->da_directorio; }
    public function getCosto() { return $this->costo; }
    public function getIdioma() { return $this->idioma; }
    public function isNofollow() { return $this->nofollow; }
    public function permiteAnchorPersonalizado() { return $this->permite_anchor_personalizado; }
    public function getEstado() { return $this->estado; }
    public function getFechaEnvio() { return $this->fecha_envio; }
    public function getFechaAprobacion() { return $this->fecha_aprobacion; }
    public function getNotas() { return $this->notas; }
    public function getFechaCreacion() { return $this->fecha_creacion; }
    
    
    // ============================================
    // SETTERS
    // ============================================
    
    public function setNombre($nombre) { return $this->actualitzarCamp('nombre', $nombre); }
    public function setUrl($url) { return $this->actualitzarCamp('url', $url); }
    public function setCategoria($categoria) { return $this->actualitzarCamp('categoria', $categoria); }
    public function setDaDirectorio($da) { return $this->actualitzarCamp('da_directorio', $da); }
    public function setCosto($costo) { return $this->actualitzarCamp('costo', $costo); }
    public function setIdioma($idioma) { return $this->actualitzarCamp('idioma', $idioma); }
    public function setNofollow($nofollow) { return $this->actualitzarCamp('nofollow', $nofollow ? 1 : 0); }
    public function setPermiteAnchorPersonalizado($permite) { return $this->actualitzarCamp('permite_anchor_personalizado', $permet ? 1 : 0); }
    public function setEstado($estado) { return $this->actualitzarCamp('estado', $estado); }
    public function setFechaEnvio($fecha) { return $this->actualitzarCamp('fecha_envio', $fecha); }
    public function setFechaAprobacion($fecha) { return $this->actualitzarCamp('fecha_aprobacion', $fecha); }
    public function setNotas($notas) { return $this->actualitzarCamp('notas', $notas); }
    
    
    // ============================================
    // MÈTODE AUXILIAR: ARRAY COMPLET
    // ============================================
    
    /**
     * Retorna totes les dades del directori com array associatiu
     * 
     * @return array Array amb totes les propietats del directori
     */
    public function toArray() {
        return [
            'id_directorio' => $this->id_directorio,
            'nombre' => $this->nombre,
            'url' => $this->url,
            'categoria' => $this->categoria,
            'da_directorio' => $this->da_directorio,
            'costo' => $this->costo,
            'idioma' => $this->idioma,
            'nofollow' => $this->nofollow,
            'permite_anchor_personalizado' => $this->permite_anchor_personalizado,
            'estado' => $this->estado,
            'fecha_envio' => $this->fecha_envio,
            'fecha_aprobacion' => $this->fecha_aprobacion,
            'notas' => $this->notas,
            'fecha_creacion' => $this->fecha_creacion,
            'quality_score' => $this->calcularQualityScore(),
            'roi' => $this->calcularROI(),
            'dies_des_enviament' => $this->getDiesDesDeEnviament(),
            'dies_des_aprovacio' => $this->getDiesDesDeAprovacio(),
            'pendent_revisio' => $this->isPendienteRevision()
        ];
    }
}
