<?php
/**
 * PsicologaData
 *
 * Model per a la taula `info_psicologa`.
 * Proporciona un mètode centralitzat per llegir i escriure la informació
 * professional/personal de la psicòloga (nom, titulació, contactes i xarxes).
 *
 * Aquesta classe usa PDO per a les operacions amb la base de dades i està
 * dissenyada per ser fàcil d'integrar amb l'estructura existent del projecte.
 *
 * Taula corresponent (resum):
 * - id_info (PK)
 * - nom_complet_ca, nom_complet_es
 * - titulacio_ca, titulacio_es
 * - foto_perfil, alt_foto_ca, alt_foto_es
 * - email_professional, telefon_professional
 * - linkedin_url, instagram_professional
 * - num_collegiat, college_professional
 * - data_creacio, data_actualitzacio
 *
 * Usage example:
 *   $db = Connexio::getInstance()->getConnexio();
 *   $m = new PsicologaData($db);
 *   $m->nom_complet_ca = 'Yanina Parisi';
 *   $m->num_collegiat = '12345';
 *   $m->crear();
 *
 * @package YaninaParisi
 */

/**
 * @property int $id_info
 * @property string $nom_complet_ca
 * @property string $nom_complet_es
 * @property string $titulacio_ca
 * @property string $titulacio_es
 * @property string|null $foto_perfil
 * @property string|null $alt_foto_ca
 * @property string|null $alt_foto_es
 * @property string|null $email_professional
 * @property string|null $telefon_professional
 * @property string|null $linkedin_url
 * @property string|null $instagram_professional
 * @property string $num_collegiat
 * @property string $college_professional
 * @property string|null $data_creacio
 * @property string|null $data_actualitzacio
 */
class PsicologaData {

    /** @var PDO */
    private $conn;

    /** @var string Nom de la taula */
    private $table = 'info_psicologa';

    // =====================
    // Propietats (mapegen la taula)
    // =====================
    public $id_info;
    public $nom_complet_ca;
    public $nom_complet_es;
    public $titulacio_ca;
    public $titulacio_es;
    public $foto_perfil;
    public $alt_foto_ca;
    public $alt_foto_es;
    public $email_professional;
    public $telefon_professional;
    public $linkedin_url;
    public $instagram_professional;
    public $num_collegiat;
    public $college_professional;
    public $data_creacio;
    public $data_actualitzacio;

    /**
     * Constructor
     *
     * @param PDO $db Connexió PDO inicialitzada
     */
    public function __construct(PDO $db) {
        $this->conn = $db;
    }

    /**
     * Neteja bàsica d'entrada.
     * @param mixed $v
     * @return mixed
     */
    private function sanitize($v) {
        if (is_string($v)) return trim($v);
        return $v;
    }

    /**
     * Mapear una fila (PDO::FETCH_ASSOC) a les propietats de l'objecte.
     * @param array $r
     * @return void
     */
    private function mapFromRow(array $r) {
        $this->id_info = isset($r['id_info']) ? (int)$r['id_info'] : null;
        $this->nom_complet_ca = $r['nom_complet_ca'] ?? '';
        $this->nom_complet_es = $r['nom_complet_es'] ?? '';
        $this->titulacio_ca = $r['titulacio_ca'] ?? '';
        $this->titulacio_es = $r['titulacio_es'] ?? '';
        $this->foto_perfil = $r['foto_perfil'] ?? null;
        $this->alt_foto_ca = $r['alt_foto_ca'] ?? null;
        $this->alt_foto_es = $r['alt_foto_es'] ?? null;
        $this->email_professional = $r['email_professional'] ?? null;
        $this->telefon_professional = $r['telefon_professional'] ?? null;
        $this->linkedin_url = $r['linkedin_url'] ?? null;
        $this->instagram_professional = $r['instagram_professional'] ?? null;
        $this->num_collegiat = $r['num_collegiat'] ?? '';
        $this->college_professional = $r['college_professional'] ?? '';
        $this->data_creacio = $r['data_creacio'] ?? null;
        $this->data_actualitzacio = $r['data_actualitzacio'] ?? null;
    }

    /**
     * Convertir objecte a array associatiu (útil per JSON/plantilles)
     * @return array
     */
    public function toArray() {
        return [
            'id_info' => $this->id_info,
            'nom_complet_ca' => $this->nom_complet_ca,
            'nom_complet_es' => $this->nom_complet_es,
            'titulacio_ca' => $this->titulacio_ca,
            'titulacio_es' => $this->titulacio_es,
            'foto_perfil' => $this->foto_perfil,
            'alt_foto_ca' => $this->alt_foto_ca,
            'alt_foto_es' => $this->alt_foto_es,
            'email_professional' => $this->email_professional,
            'telefon_professional' => $this->telefon_professional,
            'linkedin_url' => $this->linkedin_url,
            'instagram_professional' => $this->instagram_professional,
            'num_collegiat' => $this->num_collegiat,
            'college_professional' => $this->college_professional,
            'data_creacio' => $this->data_creacio,
            'data_actualitzacio' => $this->data_actualitzacio,
        ];
    }

    /**
     * Validació bàsica abans de crear/actualitzar.
     * Retorna array d'errors (clau string) o array buit si és vàlid.
     * @return array
     */
    public function validate() {
        $errors = [];

        if (!is_string($this->nom_complet_ca) || trim($this->nom_complet_ca) === '') {
            $errors[] = 'nom_complet_ca_required';
        }
        if (!is_string($this->nom_complet_es) || trim($this->nom_complet_es) === '') {
            $errors[] = 'nom_complet_es_required';
        }
        if (!is_string($this->titulacio_ca) || trim($this->titulacio_ca) === '') {
            $errors[] = 'titulacio_ca_required';
        }
        if (!is_string($this->titulacio_es) || trim($this->titulacio_es) === '') {
            $errors[] = 'titulacio_es_required';
        }
        if (!is_string($this->num_collegiat) || trim($this->num_collegiat) === '') {
            $errors[] = 'num_collegiat_required';
        }
        if (!is_string($this->college_professional) || trim($this->college_professional) === '') {
            $errors[] = 'college_professional_required';
        }

        // Email format check (opcional)
        if (!empty($this->email_professional) && !filter_var($this->email_professional, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'email_invalid';
        }

        return $errors;
    }

    /**
     * Llistar entrades (retorna array de files). Útil en casos d'haver diverses versions.
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function listar($limit = 100, $offset = 0) {
        $sql = 'SELECT * FROM ' . $this->table . ' ORDER BY id_info DESC LIMIT :offset, :limit';
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Llegir per ID
     * @param int $id
     * @return bool True si trobat i mapejat
     */
    public function llegirPerId($id) {
        $sql = 'SELECT * FROM ' . $this->table . ' WHERE id_info = :id LIMIT 1';
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':id' => (int)$id]);
        $r = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$r) return false;
        $this->mapFromRow($r);
        return true;
    }

    /**
     * Cercar per email (retorna fila o false)
     * @param string $email
     * @return array|false
     */
    public function buscarPerEmail($email) {
        $sql = 'SELECT * FROM ' . $this->table . ' WHERE email_professional = :email LIMIT 1';
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':email' => $this->sanitize($email)]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Cercar per número de col·legiat
     * @param string $num
     * @return array|false
     */
    public function buscarPerNumCollegiat($num) {
        $sql = 'SELECT * FROM ' . $this->table . ' WHERE num_collegiat = :num LIMIT 1';
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':num' => $this->sanitize($num)]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Crear nova entrada amb les propietats actuals de l'objecte.
     * Retorna true si la inserció ha tingut èxit.
     * @return bool
     */
    public function crear() {
        $sql = 'INSERT INTO ' . $this->table . ' (
            nom_complet_ca, nom_complet_es, titulacio_ca, titulacio_es,
            foto_perfil, alt_foto_ca, alt_foto_es, email_professional,
            telefon_professional, linkedin_url, instagram_professional,
            num_collegiat, college_professional
        ) VALUES (
            :nom_complet_ca, :nom_complet_es, :titulacio_ca, :titulacio_es,
            :foto_perfil, :alt_foto_ca, :alt_foto_es, :email_professional,
            :telefon_professional, :linkedin_url, :instagram_professional,
            :num_collegiat, :college_professional
        )';

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':nom_complet_ca', $this->sanitize($this->nom_complet_ca));
        $stmt->bindValue(':nom_complet_es', $this->sanitize($this->nom_complet_es));
        $stmt->bindValue(':titulacio_ca', $this->sanitize($this->titulacio_ca));
        $stmt->bindValue(':titulacio_es', $this->sanitize($this->titulacio_es));
        $stmt->bindValue(':foto_perfil', $this->foto_perfil ?: null);
        $stmt->bindValue(':alt_foto_ca', $this->alt_foto_ca ?: null);
        $stmt->bindValue(':alt_foto_es', $this->alt_foto_es ?: null);
        $stmt->bindValue(':email_professional', $this->email_professional ?: null);
        $stmt->bindValue(':telefon_professional', $this->telefon_professional ?: null);
        $stmt->bindValue(':linkedin_url', $this->linkedin_url ?: null);
        $stmt->bindValue(':instagram_professional', $this->instagram_professional ?: null);
        $stmt->bindValue(':num_collegiat', $this->sanitize($this->num_collegiat));
        $stmt->bindValue(':college_professional', $this->sanitize($this->college_professional));

        $res = $stmt->execute();
        if ($res) $this->id_info = (int)$this->conn->lastInsertId();
        return $res;
    }

    /**
     * Actualitzar una entrada existent (basat en $this->id_info)
     * @return bool
     */
    public function actualitzar() {
        if (empty($this->id_info)) return false;
        $sql = 'UPDATE ' . $this->table . ' SET
            nom_complet_ca = :nom_complet_ca,
            nom_complet_es = :nom_complet_es,
            titulacio_ca = :titulacio_ca,
            titulacio_es = :titulacio_es,
            foto_perfil = :foto_perfil,
            alt_foto_ca = :alt_foto_ca,
            alt_foto_es = :alt_foto_es,
            email_professional = :email_professional,
            telefon_professional = :telefon_professional,
            linkedin_url = :linkedin_url,
            instagram_professional = :instagram_professional,
            num_collegiat = :num_collegiat,
            college_professional = :college_professional
            WHERE id_info = :id_info';

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':nom_complet_ca', $this->sanitize($this->nom_complet_ca));
        $stmt->bindValue(':nom_complet_es', $this->sanitize($this->nom_complet_es));
        $stmt->bindValue(':titulacio_ca', $this->sanitize($this->titulacio_ca));
        $stmt->bindValue(':titulacio_es', $this->sanitize($this->titulacio_es));
        $stmt->bindValue(':foto_perfil', $this->foto_perfil ?: null);
        $stmt->bindValue(':alt_foto_ca', $this->alt_foto_ca ?: null);
        $stmt->bindValue(':alt_foto_es', $this->alt_foto_es ?: null);
        $stmt->bindValue(':email_professional', $this->email_professional ?: null);
        $stmt->bindValue(':telefon_professional', $this->telefon_professional ?: null);
        $stmt->bindValue(':linkedin_url', $this->linkedin_url ?: null);
        $stmt->bindValue(':instagram_professional', $this->instagram_professional ?: null);
        $stmt->bindValue(':num_collegiat', $this->sanitize($this->num_collegiat));
        $stmt->bindValue(':college_professional', $this->sanitize($this->college_professional));
        $stmt->bindValue(':id_info', (int)$this->id_info, PDO::PARAM_INT);

        return $stmt->execute();
    }

    /**
     * Eliminar una entrada per ID
     * @param int $id
     * @return bool
     */
    public function eliminar($id) {
        $sql = 'DELETE FROM ' . $this->table . ' WHERE id_info = :id';
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([':id' => (int)$id]);
    }

}

?>
