<?php
/**
 * Classe RelacioEntradesEtiquetes
 *
 * Gestiona les relacions many-to-many entre entrades del blog i etiquetes.
 * Implementa operacions per assignar, eliminar i consultar les relacions entre entrades i etiquetes,
 * incloent funcionalitats avançades per anàlisi de contingut i descobriment de contingut relacionat.
 *
 * Estructura esperada de la taula `blog_entrades_etiquetes`:
 * - id_relacio INT PRIMARY KEY AUTO_INCREMENT
 * - id_entrada INT NOT NULL (FK -> blog_entrades.id_entrada)
 * - id_etiqueta INT NOT NULL (FK -> etiquetes.id_etiqueta)
 * - data_assignacio DATETIME DEFAULT CURRENT_TIMESTAMP
 * - UNIQUE KEY unique_entrada_etiqueta (id_entrada, id_etiqueta)
 * - Claus foranes amb CASCADE DELETE per mantenir integritat referencial
 *
 * @author Marc Mataró
 * @version 1.0.0
 * @date 2025-10-11
 */

class RelacioEntradesEtiquetes {

    /** @var PDO Instància de connexió a la base de dades */
    private $conn;

    /** @var string Nom de la taula de relacions */
    private $table = 'blog_entrades_etiquetes';

    /** @var string Nom de la taula d'entrades */
    private $tableEntrades = 'blog_entrades';

    /** @var string Nom de la taula d'etiquetes */
    private $tableEtiquetes = 'etiquetes';

    /* ======== Propietats del model ======== */
    public $id_relacio;
    public $id_entrada;
    public $id_etiqueta;
    public $data_assignacio;

    /* ===================== Constructor ===================== */
    public function __construct($db) {
        if (!$db instanceof PDO) {
            throw new InvalidArgumentException('La connexió ha de ser una instància de PDO');
        }
        $this->conn = $db;
    }

    /* ===================== Validació ===================== */
    /**
     * Valida que els IDs d'entrada i etiqueta existeixin i siguin vàlids.
     *
     * @param int $idEntrada
     * @param int $idEtiqueta
     * @return array Errors trobats (buit si no hi ha errors)
     */
    public function validarRelacio(int $idEntrada, int $idEtiqueta): array {
        $errors = [];

        // Validar que l'entrada existeixi
        if (!$this->existeixEntrada($idEntrada)) {
            $errors[] = "L'entrada amb ID {$idEntrada} no existeix";
        }

        // Validar que l'etiqueta existeixi
        if (!$this->existeixEtiqueta($idEtiqueta)) {
            $errors[] = "L'etiqueta amb ID {$idEtiqueta} no existeix";
        }

        return $errors;
    }

    /**
     * Comprova si una entrada existeix a la base de dades.
     *
     * @param int $idEntrada
     * @return bool
     */
    private function existeixEntrada(int $idEntrada): bool {
        $sql = "SELECT COUNT(*) FROM {$this->tableEntrades} WHERE id_entrada = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $idEntrada, PDO::PARAM_INT);
        $stmt->execute();
        return (int)$stmt->fetchColumn() > 0;
    }

    /**
     * Comprova si una etiqueta existeix a la base de dades.
     *
     * @param int $idEtiqueta
     * @return bool
     */
    private function existeixEtiqueta(int $idEtiqueta): bool {
        $sql = "SELECT COUNT(*) FROM {$this->tableEtiquetes} WHERE id_etiqueta = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $idEtiqueta, PDO::PARAM_INT);
        $stmt->execute();
        return (int)$stmt->fetchColumn() > 0;
    }

    /**
     * Comprova si ja existeix una relació entre una entrada i una etiqueta.
     *
     * @param int $idEntrada
     * @param int $idEtiqueta
     * @return bool
     */
    public function existeixRelacio(int $idEntrada, int $idEtiqueta): bool {
        $sql = "SELECT COUNT(*) FROM {$this->table} 
                WHERE id_entrada = :entrada AND id_etiqueta = :etiqueta";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':entrada', $idEntrada, PDO::PARAM_INT);
        $stmt->bindParam(':etiqueta', $idEtiqueta, PDO::PARAM_INT);
        $stmt->execute();
        return (int)$stmt->fetchColumn() > 0;
    }

    /* ===================== Operacions CRUD ===================== */

    /**
     * Crear una nova relació entre entrada i etiqueta.
     * Retorna l'ID de la relació creada o false en cas d'error.
     *
     * @param int $idEntrada
     * @param int $idEtiqueta
     * @return int|false
     */
    public function crearRelacio(int $idEntrada, int $idEtiqueta) {
        // Validar dades
        $errors = $this->validarRelacio($idEntrada, $idEtiqueta);
        if (!empty($errors)) {
            return false;
        }

        // Comprovar si la relació ja existeix
        if ($this->existeixRelacio($idEntrada, $idEtiqueta)) {
            return false; // La relació ja existeix
        }

        $sql = "INSERT INTO {$this->table} (id_entrada, id_etiqueta) VALUES (:entrada, :etiqueta)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':entrada', $idEntrada, PDO::PARAM_INT);
        $stmt->bindParam(':etiqueta', $idEtiqueta, PDO::PARAM_INT);

        if ($stmt->execute()) {
            return (int)$this->conn->lastInsertId();
        }

        return false;
    }

    /**
     * Eliminar una relació específica entre entrada i etiqueta.
     *
     * @param int $idEntrada
     * @param int $idEtiqueta
     * @return bool
     */
    public function eliminarRelacio(int $idEntrada, int $idEtiqueta): bool {
        $sql = "DELETE FROM {$this->table} 
                WHERE id_entrada = :entrada AND id_etiqueta = :etiqueta";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':entrada', $idEntrada, PDO::PARAM_INT);
        $stmt->bindParam(':etiqueta', $idEtiqueta, PDO::PARAM_INT);
        return $stmt->execute();
    }

    /**
     * Eliminar totes les relacions d'una entrada específica.
     *
     * @param int $idEntrada
     * @return bool
     */
    public function eliminarRelacionsEntrada(int $idEntrada): bool {
        $sql = "DELETE FROM {$this->table} WHERE id_entrada = :entrada";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':entrada', $idEntrada, PDO::PARAM_INT);
        return $stmt->execute();
    }

    /**
     * Eliminar totes les relacions d'una etiqueta específica.
     *
     * @param int $idEtiqueta
     * @return bool
     */
    public function eliminarRelacionsEtiqueta(int $idEtiqueta): bool {
        $sql = "DELETE FROM {$this->table} WHERE id_etiqueta = :etiqueta";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':etiqueta', $idEtiqueta, PDO::PARAM_INT);
        return $stmt->execute();
    }

    /* ===================== Consultes i cerques ===================== */

    /**
     * Obtenir totes les etiquetes d'una entrada específica.
     *
     * @param int $idEntrada
     * @param string $idioma 'ca' o 'es' per obtenir noms en l'idioma especificat
     * @param bool $nomesActives Només etiquetes actives
     * @return array Array d'etiquetes amb informació completa
     */
    public function obtenirEtiquetesEntrada(int $idEntrada, string $idioma = 'es', bool $nomesActives = true): array {
        $nomCol = $idioma === 'ca' ? 'e.nom_ca' : 'e.nom_es';
        $slugCol = $idioma === 'ca' ? 'e.slug_ca' : 'e.slug_es';
        $descCol = $idioma === 'ca' ? 'e.descripcio_ca' : 'e.descripcion_es';
        
        $sql = "SELECT e.id_etiqueta, {$nomCol} as nom, {$slugCol} as slug, 
                       {$descCol} as descripcio, e.activa, e.ordre, r.data_assignacio
                FROM {$this->table} r
                INNER JOIN {$this->tableEtiquetes} e ON r.id_etiqueta = e.id_etiqueta
                WHERE r.id_entrada = :entrada";
        
        if ($nomesActives) {
            $sql .= " AND e.activa = 1";
        }
        
        $sql .= " ORDER BY e.ordre ASC, {$nomCol} ASC";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':entrada', $idEntrada, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtenir totes les entrades d'una etiqueta específica.
     *
     * @param int $idEtiqueta
     * @param string $idioma 'ca' o 'es' per obtenir títols en l'idioma especificat
     * @param array $filtres Filtres addicionals (estat, visible, etc.)
     * @param int|null $limit Límit de resultats
     * @return array Array d'entrades amb informació bàsica
     */
    public function obtenirEntradesEtiqueta(int $idEtiqueta, string $idioma = 'es', array $filtres = [], $limit = null): array {
        $titolCol = $idioma === 'ca' ? 'ent.titol_ca' : 'ent.titol_es';
        $slugCol = $idioma === 'ca' ? 'ent.slug_ca' : 'ent.slug_es';
        $resumCol = $idioma === 'ca' ? 'ent.resum_ca' : 'ent.resum_es';
        
        $sql = "SELECT ent.id_entrada, {$titolCol} as titol, {$slugCol} as slug, 
                       {$resumCol} as resum, ent.imatge_portada, ent.data_publicacio, 
                       ent.visualitzacions, ent.estat, ent.temps_lectura_es, r.data_assignacio
                FROM {$this->table} r
                INNER JOIN {$this->tableEntrades} ent ON r.id_entrada = ent.id_entrada
                WHERE r.id_etiqueta = :etiqueta";

        $params = [':etiqueta' => $idEtiqueta];

        // Aplicar filtres
        if (isset($filtres['estat'])) {
            $sql .= " AND ent.estat = :estat";
            $params[':estat'] = $filtres['estat'];
        }

        if (isset($filtres['visible'])) {
            $sql .= " AND ent.visible = :visible";
            $params[':visible'] = $filtres['visible'] ? 1 : 0;
        }

        if (isset($filtres['data_desde'])) {
            $sql .= " AND ent.data_publicacio >= :data_desde";
            $params[':data_desde'] = $filtres['data_desde'];
        }

        if (isset($filtres['data_fins'])) {
            $sql .= " AND ent.data_publicacio <= :data_fins";
            $params[':data_fins'] = $filtres['data_fins'];
        }

        $sql .= " ORDER BY ent.data_publicacio DESC";

        if ($limit !== null) {
            $sql .= " LIMIT :limit";
        }

        $stmt = $this->conn->prepare($sql);
        
        foreach ($params as $key => $value) {
            if ($key === ':etiqueta' || $key === ':visible') {
                $stmt->bindValue($key, $value, PDO::PARAM_INT);
            } else {
                $stmt->bindValue($key, $value);
            }
        }
        
        if ($limit !== null) {
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        }

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtenir estadístiques de relacions per etiqueta.
     *
     * @param int|null $idEtiqueta Si null, obtenir estadístiques de totes les etiquetes
     * @return array Estadístiques detallades
     */
    public function obtenirEstadistiquesEtiqueta($idEtiqueta = null): array {
        $sql = "SELECT 
                    e.id_etiqueta,
                    e.nom_es as nom_etiqueta,
                    COUNT(r.id_entrada) as total_entrades,
                    COUNT(CASE WHEN ent.estat = 'publicat' AND ent.visible = 1 THEN 1 END) as entrades_publicades,
                    COUNT(CASE WHEN ent.estat = 'esborrany' THEN 1 END) as entrades_esborrany,
                    SUM(CASE WHEN ent.estat = 'publicat' AND ent.visible = 1 THEN ent.visualitzacions ELSE 0 END) as total_visualitzacions,
                    AVG(CASE WHEN ent.estat = 'publicat' AND ent.visible = 1 THEN ent.temps_lectura_es ELSE NULL END) as temps_mitjà_lectura,
                    MAX(r.data_assignacio) as darrera_assignacio
                FROM {$this->tableEtiquetes} e
                LEFT JOIN {$this->table} r ON e.id_etiqueta = r.id_etiqueta
                LEFT JOIN {$this->tableEntrades} ent ON r.id_entrada = ent.id_entrada";

        $params = [];
        if ($idEtiqueta !== null) {
            $sql .= " WHERE e.id_etiqueta = :etiqueta";
            $params[':etiqueta'] = $idEtiqueta;
        }

        $sql .= " GROUP BY e.id_etiqueta, e.nom_es
                 ORDER BY total_entrades DESC, e.nom_es ASC";

        $stmt = $this->conn->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value, PDO::PARAM_INT);
        }
        $stmt->execute();

        $resultats = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Convertir a enters i processar resultats
        foreach ($resultats as &$etiqueta) {
            $etiqueta['total_entrades'] = (int)$etiqueta['total_entrades'];
            $etiqueta['entrades_publicades'] = (int)$etiqueta['entrades_publicades'];
            $etiqueta['entrades_esborrany'] = (int)$etiqueta['entrades_esborrany'];
            $etiqueta['total_visualitzacions'] = (int)$etiqueta['total_visualitzacions'];
            $etiqueta['temps_mitjà_lectura'] = $etiqueta['temps_mitjà_lectura'] ? round((float)$etiqueta['temps_mitjà_lectura'], 1) : null;
        }

        return $resultats;
    }

    /**
     * Obtenir entrades relacionades basades en etiquetes compartides.
     * Utilitza un algoritme de similaritat basat en etiquetes compartides.
     *
     * @param int $idEntrada Entrada de referència
     * @param int $limit Límit de resultats
     * @param string $idioma Idioma per als títols
     * @param float $llindarSimilaritat Llindar mínim de similaritat (0.1 a 1.0)
     * @return array Entrades relacionades ordenades per rellevància
     */
    public function obtenirEntradesRelacionades(int $idEntrada, int $limit = 5, string $idioma = 'es', float $llindarSimilaritat = 0.2): array {
        $titolCol = $idioma === 'ca' ? 'e2.titol_ca' : 'e2.titol_es';
        $slugCol = $idioma === 'ca' ? 'e2.slug_ca' : 'e2.slug_es';
        
        $sql = "SELECT 
                    e2.id_entrada,
                    {$titolCol} as titol,
                    {$slugCol} as slug,
                    e2.imatge_portada,
                    e2.data_publicacio,
                    e2.visualitzacions,
                    COUNT(r2.id_etiqueta) as etiquetes_compartides,
                    (COUNT(r2.id_etiqueta) / total_etiquetes_ref.total) as similaritat
                FROM {$this->table} r1
                INNER JOIN {$this->table} r2 ON r1.id_etiqueta = r2.id_etiqueta
                INNER JOIN {$this->tableEntrades} e2 ON r2.id_entrada = e2.id_entrada
                CROSS JOIN (
                    SELECT COUNT(*) as total 
                    FROM {$this->table} 
                    WHERE id_entrada = :entrada_ref3
                ) total_etiquetes_ref
                WHERE r1.id_entrada = :entrada_ref
                  AND r2.id_entrada != :entrada_ref2
                  AND e2.estat = 'publicat'
                  AND e2.visible = 1
                GROUP BY e2.id_entrada, {$titolCol}, {$slugCol}, e2.imatge_portada, 
                         e2.data_publicacio, e2.visualitzacions, total_etiquetes_ref.total
                HAVING similaritat >= :llindar
                ORDER BY similaritat DESC, etiquetes_compartides DESC, e2.data_publicacio DESC
                LIMIT :limit";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':entrada_ref', $idEntrada, PDO::PARAM_INT);
        $stmt->bindParam(':entrada_ref2', $idEntrada, PDO::PARAM_INT);
        $stmt->bindParam(':entrada_ref3', $idEntrada, PDO::PARAM_INT);
        $stmt->bindParam(':llindar', $llindarSimilaritat, PDO::PARAM_STR);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Cercar entrades per múltiples etiquetes amb mode AND o OR.
     *
     * @param array $etiquetes Array d'IDs d'etiquetes
     * @param string $mode 'AND' per entrades que tinguin totes les etiquetes, 'OR' per alguna
     * @param string $idioma Idioma dels resultats
     * @param array $filtres Filtres addicionals
     * @param int $limit Límit de resultats
     * @return array
     */
    public function cercarPerEtiquetes(array $etiquetes, string $mode = 'OR', string $idioma = 'es', array $filtres = [], int $limit = 20): array {
        if (empty($etiquetes)) {
            return [];
        }

        $titolCol = $idioma === 'ca' ? 'e.titol_ca' : 'e.titol_es';
        $slugCol = $idioma === 'ca' ? 'e.slug_ca' : 'e.slug_es';
        $resumCol = $idioma === 'ca' ? 'e.resum_ca' : 'e.resum_es';

        $placeholders = implode(',', array_fill(0, count($etiquetes), '?'));
        
        if ($mode === 'AND') {
            // Entrades que tenen TOTES les etiquetes especificades
            $sql = "SELECT e.id_entrada, {$titolCol} as titol, {$slugCol} as slug, 
                           {$resumCol} as resum, e.imatge_portada, e.data_publicacio, 
                           e.visualitzacions, COUNT(r.id_etiqueta) as etiquetes_coincidents
                    FROM {$this->tableEntrades} e
                    INNER JOIN {$this->table} r ON e.id_entrada = r.id_entrada
                    WHERE r.id_etiqueta IN ({$placeholders})
                      AND e.estat = 'publicat' AND e.visible = 1";
        } else {
            // Entrades que tenen ALGUNA de les etiquetes especificades
            $sql = "SELECT DISTINCT e.id_entrada, {$titolCol} as titol, {$slugCol} as slug, 
                           {$resumCol} as resum, e.imatge_portada, e.data_publicacio, 
                           e.visualitzacions
                    FROM {$this->tableEntrades} e
                    INNER JOIN {$this->table} r ON e.id_entrada = r.id_entrada
                    WHERE r.id_etiqueta IN ({$placeholders})
                      AND e.estat = 'publicat' AND e.visible = 1";
        }

        // Aplicar filtres addicionals
        if (isset($filtres['data_desde'])) {
            $sql .= " AND e.data_publicacio >= ?";
            $etiquetes[] = $filtres['data_desde'];
        }

        if (isset($filtres['data_fins'])) {
            $sql .= " AND e.data_publicacio <= ?";
            $etiquetes[] = $filtres['data_fins'];
        }

        if ($mode === 'AND') {
            $sql .= " GROUP BY e.id_entrada, {$titolCol}, {$slugCol}, {$resumCol}, 
                             e.imatge_portada, e.data_publicacio, e.visualitzacions
                     HAVING COUNT(r.id_etiqueta) = ?";
            $etiquetes[] = count($etiquetes) - (isset($filtres['data_desde']) ? 1 : 0) - (isset($filtres['data_fins']) ? 1 : 0);
            $sql .= " ORDER BY e.data_publicacio DESC";
        } else {
            $sql .= " ORDER BY e.data_publicacio DESC";
        }

        $sql .= " LIMIT ?";
        $etiquetes[] = $limit;

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($etiquetes);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /* ===================== Operacions en lot ===================== */

    /**
     * Assignar múltiples etiquetes a una entrada.
     * Elimina les relacions anteriors i crea les noves.
     *
     * @param int $idEntrada
     * @param array $etiquetes Array d'IDs d'etiquetes
     * @return bool
     */
    public function assignarEtiquetes(int $idEntrada, array $etiquetes): bool {
        if (!$this->existeixEntrada($idEntrada)) {
            return false;
        }

        // Validar que totes les etiquetes existeixin
        foreach ($etiquetes as $idEtiqueta) {
            if (!$this->existeixEtiqueta((int)$idEtiqueta)) {
                return false;
            }
        }

        $this->conn->beginTransaction();

        try {
            // Eliminar relacions anteriors
            $this->eliminarRelacionsEntrada($idEntrada);

            // Crear noves relacions
            foreach ($etiquetes as $idEtiqueta) {
                $this->crearRelacio($idEntrada, (int)$idEtiqueta);
            }

            $this->conn->commit();
            return true;

        } catch (Exception $e) {
            $this->conn->rollBack();
            return false;
        }
    }

    /**
     * Assignar múltiples entrades a una etiqueta.
     *
     * @param int $idEtiqueta
     * @param array $entrades Array d'IDs d'entrades
     * @param bool $eliminarAnteriors Si true, elimina relacions anteriors de l'etiqueta
     * @return bool
     */
    public function assignarEntrades(int $idEtiqueta, array $entrades, bool $eliminarAnteriors = false): bool {
        if (!$this->existeixEtiqueta($idEtiqueta)) {
            return false;
        }

        // Validar que totes les entrades existeixin
        foreach ($entrades as $idEntrada) {
            if (!$this->existeixEntrada((int)$idEntrada)) {
                return false;
            }
        }

        $this->conn->beginTransaction();

        try {
            // Eliminar relacions anteriors si s'especifica
            if ($eliminarAnteriors) {
                $this->eliminarRelacionsEtiqueta($idEtiqueta);
            }

            // Crear noves relacions
            foreach ($entrades as $idEntrada) {
                // Només crear si no existeix ja (en cas de no eliminar anteriors)
                if (!$this->existeixRelacio((int)$idEntrada, $idEtiqueta)) {
                    $this->crearRelacio((int)$idEntrada, $idEtiqueta);
                }
            }

            $this->conn->commit();
            return true;

        } catch (Exception $e) {
            $this->conn->rollBack();
            return false;
        }
    }

    /* ===================== Anàlisi i descobriment de contingut ===================== */

    /**
     * Obtenir núvol d'etiquetes amb freqüències.
     * Retorna etiquetes ordenades per popularitat amb comptadors d'ús.
     *
     * @param string $idioma
     * @param int $limit
     * @param bool $nomesPublicades Només comptar entrades publicades
     * @return array
     */
    public function obtenirNuvolEtiquetes(string $idioma = 'es', int $limit = 50, bool $nomesPublicades = true): array {
        $nomCol = $idioma === 'ca' ? 'e.nom_ca' : 'e.nom_es';
        $slugCol = $idioma === 'ca' ? 'e.slug_ca' : 'e.slug_es';
        
        $sql = "SELECT 
                    e.id_etiqueta,
                    {$nomCol} as nom,
                    {$slugCol} as slug,
                    COUNT(r.id_entrada) as frequencia,
                    MAX(r.data_assignacio) as darrer_us
                FROM {$this->tableEtiquetes} e
                INNER JOIN {$this->table} r ON e.id_etiqueta = r.id_etiqueta";
        
        if ($nomesPublicades) {
            $sql .= " INNER JOIN {$this->tableEntrades} ent ON r.id_entrada = ent.id_entrada
                     WHERE ent.estat = 'publicat' AND ent.visible = 1 AND e.activa = 1";
        } else {
            $sql .= " WHERE e.activa = 1";
        }
        
        $sql .= " GROUP BY e.id_etiqueta, {$nomCol}, {$slugCol}
                 ORDER BY frequencia DESC, {$nomCol} ASC
                 LIMIT :limit";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtenir etiquetes tendència (més utilitzades recentment).
     *
     * @param string $idioma
     * @param int $dies Nombre de dies a considerar
     * @param int $limit
     * @return array
     */
    public function obtenirEtiquetesTendencia(string $idioma = 'es', int $dies = 30, int $limit = 20): array {
        $nomCol = $idioma === 'ca' ? 'e.nom_ca' : 'e.nom_es';
        $slugCol = $idioma === 'ca' ? 'e.slug_ca' : 'e.slug_es';
        
        $sql = "SELECT 
                    e.id_etiqueta,
                    {$nomCol} as nom,
                    {$slugCol} as slug,
                    COUNT(r.id_entrada) as usos_recents,
                    AVG(ent.visualitzacions) as mitjana_visualitzacions
                FROM {$this->tableEtiquetes} e
                INNER JOIN {$this->table} r ON e.id_etiqueta = r.id_etiqueta
                INNER JOIN {$this->tableEntrades} ent ON r.id_entrada = ent.id_entrada
                WHERE r.data_assignacio >= DATE_SUB(NOW(), INTERVAL :dies DAY)
                  AND ent.estat = 'publicat' 
                  AND ent.visible = 1 
                  AND e.activa = 1
                GROUP BY e.id_etiqueta, {$nomCol}, {$slugCol}
                ORDER BY usos_recents DESC, mitjana_visualitzacions DESC
                LIMIT :limit";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':dies', $dies, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Suggerir etiquetes per a una entrada basant-se en el contingut.
     * Utilitza similaritat de text per suggerir etiquetes existents.
     *
     * @param string $contingut Contingut de l'entrada
     * @param string $idioma
     * @param int $limit
     * @return array
     */
    public function suggerirEtiquetes(string $contingut, string $idioma = 'es', int $limit = 10): array {
        $nomCol = $idioma === 'ca' ? 'nom_ca' : 'nom_es';
        $descCol = $idioma === 'ca' ? 'descripcio_ca' : 'descripcion_es';
        
        // Netejar contingut i convertir a minúscules
        $contingutNet = strtolower(strip_tags($contingut));
        $paraules = explode(' ', $contingutNet);
        $paraulesUniques = array_unique(array_filter($paraules, function($p) {
            return strlen($p) > 3; // Només paraules de més de 3 caràcters
        }));
        
        if (empty($paraulesUniques)) {
            return [];
        }
        
        // Crear condicions LIKE per a cada paraula
        $conditions = [];
        $params = [];
        foreach (array_slice($paraulesUniques, 0, 10) as $index => $paraula) { // Màxim 10 paraules
            $paramName = ":paraula{$index}";
            $conditions[] = "({$nomCol} LIKE {$paramName} OR {$descCol} LIKE {$paramName})";
            $params[$paramName] = '%' . $paraula . '%';
        }
        
        if (empty($conditions)) {
            return [];
        }
        
        $sql = "SELECT id_etiqueta, {$nomCol} as nom, 
                       COUNT(*) as coincidencies
                FROM {$this->tableEtiquetes}
                WHERE activa = 1 AND (" . implode(' OR ', $conditions) . ")
                GROUP BY id_etiqueta, {$nomCol}
                ORDER BY coincidencies DESC, {$nomCol} ASC
                LIMIT :limit";

        $stmt = $this->conn->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /* ===================== Utilitats i helpers ===================== */

    /**
     * Obtenir el nombre total de relacions al sistema.
     *
     * @return int
     */
    public function comptarRelacions(): int {
        $sql = "SELECT COUNT(*) FROM {$this->table}";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return (int)$stmt->fetchColumn();
    }

    /**
     * Obtenir etiquetes més utilitzades (amb més entrades assignades).
     *
     * @param int $limit
     * @param string $idioma
     * @return array
     */
    public function obtenirEtiquetesMesUtilitzades(int $limit = 10, string $idioma = 'es'): array {
        $nomCol = $idioma === 'ca' ? 'e.nom_ca' : 'e.nom_es';
        
        $sql = "SELECT 
                    e.id_etiqueta,
                    {$nomCol} as nom,
                    COUNT(r.id_entrada) as total_entrades
                FROM {$this->tableEtiquetes} e
                INNER JOIN {$this->table} r ON e.id_etiqueta = r.id_etiqueta
                WHERE e.activa = 1
                GROUP BY e.id_etiqueta, {$nomCol}
                ORDER BY total_entrades DESC, {$nomCol} ASC
                LIMIT :limit";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtenir etiquetes sense entrades assignades.
     *
     * @param string $idioma
     * @return array
     */
    public function obtenirEtiquetesBuides(string $idioma = 'es'): array {
        $nomCol = $idioma === 'ca' ? 'e.nom_ca' : 'e.nom_es';
        
        $sql = "SELECT e.id_etiqueta, {$nomCol} as nom
                FROM {$this->tableEtiquetes} e
                LEFT JOIN {$this->table} r ON e.id_etiqueta = r.id_etiqueta
                WHERE r.id_etiqueta IS NULL
                ORDER BY {$nomCol} ASC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Validar integritat referencial de les relacions.
     * Retorna array amb errors trobats.
     *
     * @return array
     */
    public function validarIntegritatReferencial(): array {
        $errors = [];

        // Comprovar relacions amb entrades inexistents
        $sql = "SELECT r.id_relacio, r.id_entrada 
                FROM {$this->table} r
                LEFT JOIN {$this->tableEntrades} e ON r.id_entrada = e.id_entrada
                WHERE e.id_entrada IS NULL";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $entradesInexistents = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (!empty($entradesInexistents)) {
            $errors[] = "Trobades " . count($entradesInexistents) . " relacions amb entrades inexistents";
        }

        // Comprovar relacions amb etiquetes inexistents
        $sql = "SELECT r.id_relacio, r.id_etiqueta 
                FROM {$this->table} r
                LEFT JOIN {$this->tableEtiquetes} e ON r.id_etiqueta = e.id_etiqueta
                WHERE e.id_etiqueta IS NULL";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $etiquetesInexistents = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (!empty($etiquetesInexistents)) {
            $errors[] = "Trobades " . count($etiquetesInexistents) . " relacions amb etiquetes inexistents";
        }

        return $errors;
    }

    /**
     * Netejar relacions òrfenes (amb entrades o etiquetes inexistents).
     *
     * @return int Nombre de relacions eliminades
     */
    public function netejarRelacionsOrfenes(): int {
        $eliminades = 0;

        $this->conn->beginTransaction();

        try {
            // Eliminar relacions amb entrades inexistents
            $sql = "DELETE r FROM {$this->table} r
                    LEFT JOIN {$this->tableEntrades} e ON r.id_entrada = e.id_entrada
                    WHERE e.id_entrada IS NULL";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            $eliminades += $stmt->rowCount();

            // Eliminar relacions amb etiquetes inexistents
            $sql = "DELETE r FROM {$this->table} r
                    LEFT JOIN {$this->tableEtiquetes} e ON r.id_etiqueta = e.id_etiqueta
                    WHERE e.id_etiqueta IS NULL";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            $eliminades += $stmt->rowCount();

            $this->conn->commit();
            return $eliminades;

        } catch (Exception $e) {
            $this->conn->rollBack();
            return 0;
        }
    }

    /**
     * Obtenir estadístiques globals del sistema d'etiquetatge.
     *
     * @return array
     */
    public function obtenirEstadistiquesGlobals(): array {
        $sql = "SELECT 
                    COUNT(DISTINCT e.id_etiqueta) as total_etiquetes,
                    COUNT(DISTINCT r.id_entrada) as entrades_amb_etiquetes,
                    COUNT(r.id_relacio) as total_relacions,
                    AVG(etiquetes_per_entrada.num_etiquetes) as mitjana_etiquetes_per_entrada,
                    MAX(etiquetes_per_entrada.num_etiquetes) as max_etiquetes_entrada
                FROM {$this->tableEtiquetes} e
                LEFT JOIN {$this->table} r ON e.id_etiqueta = r.id_etiqueta
                LEFT JOIN (
                    SELECT id_entrada, COUNT(*) as num_etiquetes
                    FROM {$this->table}
                    GROUP BY id_entrada
                ) etiquetes_per_entrada ON r.id_entrada = etiquetes_per_entrada.id_entrada";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return [
            'total_etiquetes' => (int)$result['total_etiquetes'],
            'entrades_amb_etiquetes' => (int)$result['entrades_amb_etiquetes'],
            'total_relacions' => (int)$result['total_relacions'],
            'mitjana_etiquetes_per_entrada' => round((float)$result['mitjana_etiquetes_per_entrada'], 2),
            'max_etiquetes_entrada' => (int)$result['max_etiquetes_entrada']
        ];
    }

}
