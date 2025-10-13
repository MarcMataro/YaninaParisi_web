<?php
/**
 * Classe RelacioEntradesCategories
 *
 * Gestiona les relacions many-to-many entre entrades del blog i categories.
 * Implementa operacions per assignar, eliminar i consultar les relacions entre entrades i categories.
 *
 * Estructura esperada de la taula `blog_entrades_categories`:
 * - id_relacio INT PRIMARY KEY AUTO_INCREMENT
 * - id_entrada INT NOT NULL (FK -> blog_entrades.id_entrada)
 * - id_categoria INT NOT NULL (FK -> categories.id_category)
 * - data_assignacio DATETIME DEFAULT CURRENT_TIMESTAMP
 * - UNIQUE KEY unique_entrada_categoria (id_entrada, id_categoria)
 * - Claus foranes amb CASCADE DELETE per mantenir integritat referencial
 *
 * @author Marc Mataró
 * @version 1.0.0
 * @date 2025-10-11
 */

class RelacioEntradesCategories {

    /** @var PDO Instància de connexió a la base de dades */
    private $conn;

    /** @var string Nom de la taula de relacions */
    private $table = 'blog_entrades_categories';

    /** @var string Nom de la taula d'entrades */
    private $tableEntrades = 'blog_entrades';

    /** @var string Nom de la taula de categories */
    private $tableCategories = 'categories';

    /* ======== Propietats del model ======== */
    public $id_relacio;
    public $id_entrada;
    public $id_categoria;
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
     * Valida que els IDs d'entrada i categoria existeixin i siguin vàlids.
     *
     * @param int $idEntrada
     * @param int $idCategoria
     * @return array Errors trobats (buit si no hi ha errors)
     */
    public function validarRelacio(int $idEntrada, int $idCategoria): array {
        $errors = [];

        // Validar que l'entrada existeixi
        if (!$this->existeixEntrada($idEntrada)) {
            $errors[] = "L'entrada amb ID {$idEntrada} no existeix";
        }

        // Validar que la categoria existeixi
        if (!$this->existeixCategoria($idCategoria)) {
            $errors[] = "La categoria amb ID {$idCategoria} no existeix";
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
     * Comprova si una categoria existeix a la base de dades.
     *
     * @param int $idCategoria
     * @return bool
     */
    private function existeixCategoria(int $idCategoria): bool {
        $sql = "SELECT COUNT(*) FROM {$this->tableCategories} WHERE id_category = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $idCategoria, PDO::PARAM_INT);
        $stmt->execute();
        return (int)$stmt->fetchColumn() > 0;
    }

    /**
     * Comprova si ja existeix una relació entre una entrada i una categoria.
     *
     * @param int $idEntrada
     * @param int $idCategoria
     * @return bool
     */
    public function existeixRelacio(int $idEntrada, int $idCategoria): bool {
        $sql = "SELECT COUNT(*) FROM {$this->table} 
                WHERE id_entrada = :entrada AND id_categoria = :categoria";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':entrada', $idEntrada, PDO::PARAM_INT);
        $stmt->bindParam(':categoria', $idCategoria, PDO::PARAM_INT);
        $stmt->execute();
        return (int)$stmt->fetchColumn() > 0;
    }

    /* ===================== Operacions CRUD ===================== */

    /**
     * Crear una nova relació entre entrada i categoria.
     * Retorna l'ID de la relació creada o false en cas d'error.
     *
     * @param int $idEntrada
     * @param int $idCategoria
     * @return int|false
     */
    public function crearRelacio(int $idEntrada, int $idCategoria) {
        // Validar dades
        $errors = $this->validarRelacio($idEntrada, $idCategoria);
        if (!empty($errors)) {
            return false;
        }

        // Comprovar si la relació ja existeix
        if ($this->existeixRelacio($idEntrada, $idCategoria)) {
            return false; // La relació ja existeix
        }

        $sql = "INSERT INTO {$this->table} (id_entrada, id_categoria) VALUES (:entrada, :categoria)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':entrada', $idEntrada, PDO::PARAM_INT);
        $stmt->bindParam(':categoria', $idCategoria, PDO::PARAM_INT);

        if ($stmt->execute()) {
            return (int)$this->conn->lastInsertId();
        }

        return false;
    }

    /**
     * Eliminar una relació específica entre entrada i categoria.
     *
     * @param int $idEntrada
     * @param int $idCategoria
     * @return bool
     */
    public function eliminarRelacio(int $idEntrada, int $idCategoria): bool {
        $sql = "DELETE FROM {$this->table} 
                WHERE id_entrada = :entrada AND id_categoria = :categoria";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':entrada', $idEntrada, PDO::PARAM_INT);
        $stmt->bindParam(':categoria', $idCategoria, PDO::PARAM_INT);
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
     * Eliminar totes les relacions d'una categoria específica.
     *
     * @param int $idCategoria
     * @return bool
     */
    public function eliminarRelacionsCategoria(int $idCategoria): bool {
        $sql = "DELETE FROM {$this->table} WHERE id_categoria = :categoria";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':categoria', $idCategoria, PDO::PARAM_INT);
        return $stmt->execute();
    }

    /* ===================== Consultes i cerques ===================== */

    /**
     * Obtenir totes les categories d'una entrada específica.
     *
     * @param int $idEntrada
     * @param string $idioma 'ca' o 'es' per obtenir noms en l'idioma especificat
     * @param bool $nomesActives Només categories actives
     * @return array Array de categories amb informació completa
     */
    public function obtenirCategoriesEntrada(int $idEntrada, string $idioma = 'es', bool $nomesActives = true): array {
        $nomCol = $idioma === 'ca' ? 'c.nom_ca' : 'c.nom_es';
        $slugCol = $idioma === 'ca' ? 'c.slug_ca' : 'c.slug_es';
        
        $sql = "SELECT c.id_category, {$nomCol} as nom, {$slugCol} as slug, 
                       c.activa, c.ordre, r.data_assignacio
                FROM {$this->table} r
                INNER JOIN {$this->tableCategories} c ON r.id_categoria = c.id_category
                WHERE r.id_entrada = :entrada";
        
        if ($nomesActives) {
            $sql .= " AND c.activa = 1";
        }
        
        $sql .= " ORDER BY c.ordre ASC, {$nomCol} ASC";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':entrada', $idEntrada, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtenir totes les entrades d'una categoria específica.
     *
     * @param int $idCategoria
     * @param string $idioma 'ca' o 'es' per obtenir títols en l'idioma especificat
     * @param array $filtres Filtres addicionals (estat, visible, etc.)
     * @param int|null $limit Límit de resultats
     * @return array Array d'entrades amb informació bàsica
     */
    public function obtenirEntradesCategoria(int $idCategoria, string $idioma = 'es', array $filtres = [], $limit = null): array {
        $titolCol = $idioma === 'ca' ? 'e.titol_ca' : 'e.titol_es';
        $slugCol = $idioma === 'ca' ? 'e.slug_ca' : 'e.slug_es';
        $resumCol = $idioma === 'ca' ? 'e.resum_ca' : 'e.resum_es';
        
        $sql = "SELECT e.id_entrada, {$titolCol} as titol, {$slugCol} as slug, 
                       {$resumCol} as resum, e.imatge_portada, e.data_publicacio, 
                       e.visualitzacions, e.estat, r.data_assignacio
                FROM {$this->table} r
                INNER JOIN {$this->tableEntrades} e ON r.id_entrada = e.id_entrada
                WHERE r.id_categoria = :categoria";

        $params = [':categoria' => $idCategoria];

        // Aplicar filtres
        if (isset($filtres['estat'])) {
            $sql .= " AND e.estat = :estat";
            $params[':estat'] = $filtres['estat'];
        }

        if (isset($filtres['visible'])) {
            $sql .= " AND e.visible = :visible";
            $params[':visible'] = $filtres['visible'] ? 1 : 0;
        }

        if (isset($filtres['data_desde'])) {
            $sql .= " AND e.data_publicacio >= :data_desde";
            $params[':data_desde'] = $filtres['data_desde'];
        }

        if (isset($filtres['data_fins'])) {
            $sql .= " AND e.data_publicacio <= :data_fins";
            $params[':data_fins'] = $filtres['data_fins'];
        }

        $sql .= " ORDER BY e.data_publicacio DESC";

        if ($limit !== null) {
            $sql .= " LIMIT :limit";
        }

        $stmt = $this->conn->prepare($sql);
        
        foreach ($params as $key => $value) {
            if ($key === ':categoria' || $key === ':visible') {
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
     * Obtenir estadístiques de relacions per categoria.
     *
     * @param int|null $idCategoria Si null, obtenir estadístiques de totes les categories
     * @return array Estadístiques detallades
     */
    public function obtenirEstadistiquesCategoria($idCategoria = null): array {
        $sql = "SELECT 
                    c.id_category,
                    c.nom_es as nom_categoria,
                    COUNT(r.id_entrada) as total_entrades,
                    COUNT(CASE WHEN e.estat = 'publicat' AND e.visible = 1 THEN 1 END) as entrades_publicades,
                    COUNT(CASE WHEN e.estat = 'esborrany' THEN 1 END) as entrades_esborrany,
                    SUM(CASE WHEN e.estat = 'publicat' AND e.visible = 1 THEN e.visualitzacions ELSE 0 END) as total_visualitzacions,
                    MAX(r.data_assignacio) as darrera_assignacio
                FROM {$this->tableCategories} c
                LEFT JOIN {$this->table} r ON c.id_category = r.id_categoria
                LEFT JOIN {$this->tableEntrades} e ON r.id_entrada = e.id_entrada";

        $params = [];
        if ($idCategoria !== null) {
            $sql .= " WHERE c.id_category = :categoria";
            $params[':categoria'] = $idCategoria;
        }

        $sql .= " GROUP BY c.id_category, c.nom_es
                 ORDER BY total_entrades DESC, c.nom_es ASC";

        $stmt = $this->conn->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value, PDO::PARAM_INT);
        }
        $stmt->execute();

        $resultats = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Convertir a enters i processar resultats
        foreach ($resultats as &$categoria) {
            $categoria['total_entrades'] = (int)$categoria['total_entrades'];
            $categoria['entrades_publicades'] = (int)$categoria['entrades_publicades'];
            $categoria['entrades_esborrany'] = (int)$categoria['entrades_esborrany'];
            $categoria['total_visualitzacions'] = (int)$categoria['total_visualitzacions'];
        }

        return $resultats;
    }

    /**
     * Obtenir entrades relacionades basades en categories compartides.
     *
     * @param int $idEntrada Entrada de referència
     * @param int $limit Límit de resultats
     * @param string $idioma Idioma per als títols
     * @return array Entrades relacionades ordenades per rellevància
     */
    public function obtenirEntradesRelacionades(int $idEntrada, int $limit = 5, string $idioma = 'es'): array {
        $titolCol = $idioma === 'ca' ? 'e2.titol_ca' : 'e2.titol_es';
        $slugCol = $idioma === 'ca' ? 'e2.slug_ca' : 'e2.slug_es';
        
        $sql = "SELECT 
                    e2.id_entrada,
                    {$titolCol} as titol,
                    {$slugCol} as slug,
                    e2.imatge_portada,
                    e2.data_publicacio,
                    e2.visualitzacions,
                    COUNT(r2.id_categoria) as categories_compartides
                FROM {$this->table} r1
                INNER JOIN {$this->table} r2 ON r1.id_categoria = r2.id_categoria
                INNER JOIN {$this->tableEntrades} e2 ON r2.id_entrada = e2.id_entrada
                WHERE r1.id_entrada = :entrada_ref
                  AND r2.id_entrada != :entrada_ref2
                  AND e2.estat = 'publicat'
                  AND e2.visible = 1
                GROUP BY e2.id_entrada, {$titolCol}, {$slugCol}, e2.imatge_portada, 
                         e2.data_publicacio, e2.visualitzacions
                ORDER BY categories_compartides DESC, e2.data_publicacio DESC
                LIMIT :limit";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':entrada_ref', $idEntrada, PDO::PARAM_INT);
        $stmt->bindParam(':entrada_ref2', $idEntrada, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /* ===================== Operacions en lot ===================== */

    /**
     * Assignar múltiples categories a una entrada.
     * Elimina les relacions anteriors i crea les noves.
     *
     * @param int $idEntrada
     * @param array $categories Array d'IDs de categories
     * @return bool
     */
    public function assignarCategories(int $idEntrada, array $categories): bool {
        if (!$this->existeixEntrada($idEntrada)) {
            return false;
        }

        // Validar que totes les categories existeixin
        foreach ($categories as $idCategoria) {
            if (!$this->existeixCategoria((int)$idCategoria)) {
                return false;
            }
        }

        $this->conn->beginTransaction();

        try {
            // Eliminar relacions anteriors
            $this->eliminarRelacionsEntrada($idEntrada);

            // Crear noves relacions
            foreach ($categories as $idCategoria) {
                $this->crearRelacio($idEntrada, (int)$idCategoria);
            }

            $this->conn->commit();
            return true;

        } catch (Exception $e) {
            $this->conn->rollBack();
            return false;
        }
    }

    /**
     * Assignar múltiples entrades a una categoria.
     *
     * @param int $idCategoria
     * @param array $entrades Array d'IDs d'entrades
     * @param bool $eliminarAnteriors Si true, elimina relacions anteriors de la categoria
     * @return bool
     */
    public function assignarEntrades(int $idCategoria, array $entrades, bool $eliminarAnteriors = false): bool {
        if (!$this->existeixCategoria($idCategoria)) {
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
                $this->eliminarRelacionsCategoria($idCategoria);
            }

            // Crear noves relacions
            foreach ($entrades as $idEntrada) {
                // Només crear si no existeix ja (en cas de no eliminar anteriors)
                if (!$this->existeixRelacio((int)$idEntrada, $idCategoria)) {
                    $this->crearRelacio((int)$idEntrada, $idCategoria);
                }
            }

            $this->conn->commit();
            return true;

        } catch (Exception $e) {
            $this->conn->rollBack();
            return false;
        }
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
     * Obtenir categories més utilitzades (amb més entrades assignades).
     *
     * @param int $limit
     * @param string $idioma
     * @return array
     */
    public function obtenirCategoriesMesUtilitzades(int $limit = 10, string $idioma = 'es'): array {
        $nomCol = $idioma === 'ca' ? 'c.nom_ca' : 'c.nom_es';
        
        $sql = "SELECT 
                    c.id_category,
                    {$nomCol} as nom,
                    COUNT(r.id_entrada) as total_entrades
                FROM {$this->tableCategories} c
                INNER JOIN {$this->table} r ON c.id_category = r.id_categoria
                WHERE c.activa = 1
                GROUP BY c.id_category, {$nomCol}
                ORDER BY total_entrades DESC, {$nomCol} ASC
                LIMIT :limit";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtenir categories sense entrades assignades.
     *
     * @param string $idioma
     * @return array
     */
    public function obtenirCategoriesBuides(string $idioma = 'es'): array {
        $nomCol = $idioma === 'ca' ? 'c.nom_ca' : 'c.nom_es';
        
        $sql = "SELECT c.id_category, {$nomCol} as nom
                FROM {$this->tableCategories} c
                LEFT JOIN {$this->table} r ON c.id_category = r.id_categoria
                WHERE r.id_categoria IS NULL
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

        // Comprovar relacions amb categories inexistents
        $sql = "SELECT r.id_relacio, r.id_categoria 
                FROM {$this->table} r
                LEFT JOIN {$this->tableCategories} c ON r.id_categoria = c.id_category
                WHERE c.id_category IS NULL";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $categoriesInexistents = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (!empty($categoriesInexistents)) {
            $errors[] = "Trobades " . count($categoriesInexistents) . " relacions amb categories inexistents";
        }

        return $errors;
    }

    /**
     * Netejar relacions òrfenes (amb entrades o categories inexistents).
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

            // Eliminar relacions amb categories inexistents
            $sql = "DELETE r FROM {$this->table} r
                    LEFT JOIN {$this->tableCategories} c ON r.id_categoria = c.id_category
                    WHERE c.id_category IS NULL";
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

}
