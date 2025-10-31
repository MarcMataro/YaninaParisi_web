<?php
/**
 * Tarifes - Model de dades per a la gestió de tarifes/serveis
 *
 * Aquesta classe encapsula l'accés a la taula `tarifes` i exposa mètodes
 * per crear, llegir, actualitzar, eliminar i llistar tarifes. També inclou
 * alguns helpers orientats a l'ús en l'aplicació (promocions, comptadors).
 *
 * Diseny i assumpcions:
 * - Requereix un objecte PDO connectat a la base de dades amb la taula
 *   `tarifes` amb l'estructura proporcionada per l'usuari.
 * - Les funcions retornen arrays associatius (per a llistar) o booleans
 *   per a operacions d'escriptura. En cas d'error en una operació PDO es
 *   retorna `false` i cal revisar `$this->conn->errorInfo()` per al diagnostico.
 *
 * Exemple d'ús:
 *   $db = Connexio::getInstance()->getConnexio();
 *   $m = new Tarifes($db);
 *   $m->nom_servei_ca = 'Sessió individual';
 *   $m->preu_base = '60.00';
 *   $m->crear();
 *
 * @author Marc
 * @license MIT
 */

/**
 * @property int $id_tarifa
 * @property string $nom_servei_ca
 * @property string $nom_servei_es
 * @property string $tipus_servei
 * @property string $descripcio_ca
 * @property string $descripcio_es
 * @property int $durada_minuts
 * @property string $preu_base
 * @property string|null $preu_promocio
 * @property string $iva_percentatge
 * @property string $moneda
 * @property int $disponible
 * @property int $visible_web
 * @property int $destacat
 * @property string $modalitat
 * @property int $sessions_pack
 * @property int|null $validesa_dies
 * @property string $requisits
 * @property string $beneficios_ca
 * @property string $beneficios_es
 * @property int $ordre_visualitzacio
 * @property string $color_etiqueta
 * @property int $vegades_contractat
 * @property string|null $data_creacio
 * @property string|null $data_actualitzacio
 * @property string|null $data_inici_promocio
 * @property string|null $data_fi_promocio
 */
class Tarifes {

	/** @var PDO Instància de connexió PDO */
	private $conn;

	/** @var string Nom de la taula */
	private $table = 'tarifes';

	// =====================
	// Propietats que mapegen la taula
	// =====================
	public $id_tarifa;
	public $nom_servei_ca;
	public $nom_servei_es;
	public $tipus_servei;
	public $descripcio_ca;
	public $descripcio_es;
	public $durada_minuts;
	public $preu_base;
	public $preu_promocio;
	public $iva_percentatge;
	public $moneda;
	public $disponible;
	public $visible_web;
	public $destacat;
	public $modalitat;
	public $sessions_pack;
	public $validesa_dies;
	public $requisits;
	public $beneficios_ca;
	public $beneficios_es;
	public $ordre_visualitzacio;
	public $color_etiqueta;
	public $vegades_contractat;
	public $data_creacio;
	public $data_actualitzacio;
	public $data_inici_promocio;
	public $data_fi_promocio;

	// Enumeracions i valors permesos (ajuda per validació i formularis)
	public static $TIPUS_SERVEI = [
		'individual','pareja','familiar','grupo','evaluación','urgente','pack'
	];

	public static $MODALITATS = [
		'presencial','online','híbrida','telefónica'
	];

	/**
	 * Constructor
	 *
	 * @param PDO $db Connexió PDO ja inicialitzada
	 */
	public function __construct($db) {
		$this->conn = $db;
	}

	/**
	 * Neteja bàsica d'un valor (trim per strings)
	 *
	 * @param mixed $v
	 * @return mixed
	 */
	private function sanitize($v) {
		if (is_string($v)) return trim($v);
		return $v;
	}

	/**
	 * Mapea una fila de la base de dades a les propietats de l'objecte.
	 *
	 * @param array $r Fila retornada per PDO::FETCH_ASSOC
	 * @return void
	 */
	private function mapFromRow(array $r) {
		$this->id_tarifa = $r['id_tarifa'] ?? null;
		$this->nom_servei_ca = $r['nom_servei_ca'] ?? '';
		$this->nom_servei_es = $r['nom_servei_es'] ?? '';
		$this->tipus_servei = $r['tipus_servei'] ?? '';
		$this->descripcio_ca = $r['descripcio_ca'] ?? '';
		$this->descripcio_es = $r['descripcio_es'] ?? '';
		$this->durada_minuts = isset($r['durada_minuts']) ? (int)$r['durada_minuts'] : 0;
		$this->preu_base = isset($r['preu_base']) ? (string)$r['preu_base'] : '0.00';
		$this->preu_promocio = isset($r['preu_promocio']) ? (string)$r['preu_promocio'] : null;
		$this->iva_percentatge = isset($r['iva_percentatge']) ? (string)$r['iva_percentatge'] : '21.00';
		$this->moneda = $r['moneda'] ?? 'EUR';
		$this->disponible = !empty($r['disponible']) ? 1 : 0;
		$this->visible_web = !empty($r['visible_web']) ? 1 : 0;
		$this->destacat = !empty($r['destacat']) ? 1 : 0;
		$this->modalitat = $r['modalitat'] ?? 'presencial';
		$this->sessions_pack = isset($r['sessions_pack']) ? (int)$r['sessions_pack'] : 1;
		$this->validesa_dies = isset($r['validesa_dies']) ? (int)$r['validesa_dies'] : null;
		$this->requisits = $r['requisits'] ?? '';
		$this->beneficios_ca = $r['beneficios_ca'] ?? '';
		$this->beneficios_es = $r['beneficios_es'] ?? '';
		$this->ordre_visualitzacio = isset($r['ordre_visualitzacio']) ? (int)$r['ordre_visualitzacio'] : 0;
		$this->color_etiqueta = $r['color_etiqueta'] ?? '#3B82F6';
		$this->vegades_contractat = isset($r['vegades_contractat']) ? (int)$r['vegades_contractat'] : 0;
		$this->data_creacio = $r['data_creacio'] ?? null;
		$this->data_actualitzacio = $r['data_actualitzacio'] ?? null;
		$this->data_inici_promocio = $r['data_inici_promocio'] ?? null;
		$this->data_fi_promocio = $r['data_fi_promocio'] ?? null;
	}

	/**
	 * Converteix l'objecte a array associatiu (útil per JSON o templates)
	 *
	 * @return array
	 */
	public function toArray() {
		return [
			'id_tarifa' => $this->id_tarifa,
			'nom_servei_ca' => $this->nom_servei_ca,
			'nom_servei_es' => $this->nom_servei_es,
			'tipus_servei' => $this->tipus_servei,
			'descripcio_ca' => $this->descripcio_ca,
			'descripcio_es' => $this->descripcio_es,
			'durada_minuts' => $this->durada_minuts,
			'preu_base' => $this->preu_base,
			'preu_promocio' => $this->preu_promocio,
			'iva_percentatge' => $this->iva_percentatge,
			'moneda' => $this->moneda,
			'disponible' => $this->disponible,
			'visible_web' => $this->visible_web,
			'destacat' => $this->destacat,
			'modalitat' => $this->modalitat,
			'sessions_pack' => $this->sessions_pack,
			'validesa_dies' => $this->validesa_dies,
			'requisits' => $this->requisits,
			'beneficios_ca' => $this->beneficios_ca,
			'beneficios_es' => $this->beneficios_es,
			'ordre_visualitzacio' => $this->ordre_visualitzacio,
			'color_etiqueta' => $this->color_etiqueta,
			'vegades_contractat' => $this->vegades_contractat,
			'data_creacio' => $this->data_creacio,
			'data_actualitzacio' => $this->data_actualitzacio,
			'data_inici_promocio' => $this->data_inici_promocio,
			'data_fi_promocio' => $this->data_fi_promocio,
		];
	}

	/**
	 * Validació bàsica dels camps mínims abans de crear/actualitzar.
	 * Retorna un array amb errors (string) o un array buit si és vàlid.
	 *
	 * @return array
	 */
	public function validate() {
		$errors = [];

		if (!is_string($this->nom_servei_ca) || trim($this->nom_servei_ca) === '') {
			$errors[] = 'nom_servei_ca_required';
		}
		if (!is_string($this->nom_servei_es) || trim($this->nom_servei_es) === '') {
			$errors[] = 'nom_servei_es_required';
		}
		if ($this->preu_base === null || $this->preu_base === '') {
			$errors[] = 'preu_base_required';
		} elseif (!is_numeric(str_replace(',', '.', $this->preu_base))) {
			$errors[] = 'preu_base_invalid';
		}
		if ($this->tipus_servei && !in_array($this->tipus_servei, self::$TIPUS_SERVEI, true)) {
			$errors[] = 'tipus_servei_invalid';
		}
		if ($this->modalitat && !in_array($this->modalitat, self::$MODALITATS, true)) {
			$errors[] = 'modalitat_invalid';
		}

		return $errors;
	}

	/**
	 * Llistar tarifes amb filtres simples
	 *
	 * @param array $filters Filtres (disponible, visible_web, tipus_servei, destacat, modalitat)
	 * @param int $limit
	 * @param int $offset
	 * @param string $order
	 * @return array Array de files (assoc)
	 */
	public function listar(array $filters = [], $limit = 100, $offset = 0, $order = 'ordre_visualitzacio ASC, preu_base ASC') {
		$where = [];
		$params = [];

		if (isset($filters['disponible'])) {
			$where[] = 'disponible = :disponible';
			$params[':disponible'] = $filters['disponible'] ? 1 : 0;
		}
		if (isset($filters['visible_web'])) {
			$where[] = 'visible_web = :visible_web';
			$params[':visible_web'] = $filters['visible_web'] ? 1 : 0;
		}
		if (!empty($filters['tipus_servei'])) {
			$where[] = 'tipus_servei = :tipus_servei';
			$params[':tipus_servei'] = $filters['tipus_servei'];
		}
		if (isset($filters['destacat'])) {
			$where[] = 'destacat = :destacat';
			$params[':destacat'] = $filters['destacat'] ? 1 : 0;
		}
		if (!empty($filters['modalitat'])) {
			$where[] = 'modalitat = :modalitat';
			$params[':modalitat'] = $filters['modalitat'];
		}

		$sql = 'SELECT * FROM ' . $this->table;
		if ($where) $sql .= ' WHERE ' . implode(' AND ', $where);
		$sql .= ' ORDER BY ' . $order . ' LIMIT :offset, :limit';

		$stmt = $this->conn->prepare($sql);
		foreach ($params as $k => $v) $stmt->bindValue($k, $v);
		$stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
		$stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
		$stmt->execute();
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}

	/**
	 * Llegir una tarifa per ID i mapear-la a les propietats de l'objecte.
	 *
	 * @param int $id
	 * @return bool True si trobat, false si no
	 */
	public function llegirPerId($id) {
		$sql = 'SELECT * FROM ' . $this->table . ' WHERE id_tarifa = :id LIMIT 1';
		$stmt = $this->conn->prepare($sql);
		$stmt->execute([':id' => (int)$id]);
		$r = $stmt->fetch(PDO::FETCH_ASSOC);
		if (!$r) return false;
		$this->mapFromRow($r);
		return true;
	}

	/**
	 * Crear una nova tarifa amb les propietats actuals de l'objecte.
	 * Abans de cridar-ho, es recomana executar $this->validate() per validar camps.
	 *
	 * @return bool True si la inserció ha tingut èxit, false en cas contrari
	 */
	public function crear() {
		$sql = 'INSERT INTO ' . $this->table . ' (
				nom_servei_ca, nom_servei_es, tipus_servei, descripcio_ca, descripcio_es,
				durada_minuts, preu_base, preu_promocio, iva_percentatge, moneda,
				disponible, visible_web, destacat, modalitat, sessions_pack, validesa_dies,
				requisits, beneficios_ca, beneficios_es, ordre_visualitzacio, color_etiqueta,
				data_inici_promocio, data_fi_promocio
			) VALUES (
				:nom_servei_ca, :nom_servei_es, :tipus_servei, :descripcio_ca, :descripcio_es,
				:durada_minuts, :preu_base, :preu_promocio, :iva_percentatge, :moneda,
				:disponible, :visible_web, :destacat, :modalitat, :sessions_pack, :validesa_dies,
				:requisits, :beneficios_ca, :beneficios_es, :ordre_visualitzacio, :color_etiqueta,
				:data_inici_promocio, :data_fi_promocio
			)';

		$stmt = $this->conn->prepare($sql);
		$stmt->bindValue(':nom_servei_ca', $this->sanitize($this->nom_servei_ca));
		$stmt->bindValue(':nom_servei_es', $this->sanitize($this->nom_servei_es));
		$stmt->bindValue(':tipus_servei', $this->sanitize($this->tipus_servei));
		$stmt->bindValue(':descripcio_ca', $this->descripcio_ca ?? '');
		$stmt->bindValue(':descripcio_es', $this->descripcio_es ?? '');
		$stmt->bindValue(':durada_minuts', (int)($this->durada_minuts ?? 0), PDO::PARAM_INT);
		$stmt->bindValue(':preu_base', $this->preu_base);
		$stmt->bindValue(':preu_promocio', $this->preu_promocio !== null ? $this->preu_promocio : null);
		$stmt->bindValue(':iva_percentatge', $this->iva_percentatge ?? '21.00');
		$stmt->bindValue(':moneda', $this->moneda ?? 'EUR');
		$stmt->bindValue(':disponible', isset($this->disponible) && $this->disponible ? 1 : 0, PDO::PARAM_INT);
		$stmt->bindValue(':visible_web', isset($this->visible_web) && $this->visible_web ? 1 : 0, PDO::PARAM_INT);
		$stmt->bindValue(':destacat', isset($this->destacat) && $this->destacat ? 1 : 0, PDO::PARAM_INT);
		$stmt->bindValue(':modalitat', $this->modalitat ?? 'presencial');
		$stmt->bindValue(':sessions_pack', isset($this->sessions_pack) ? (int)$this->sessions_pack : 1, PDO::PARAM_INT);
		$stmt->bindValue(':validesa_dies', $this->validesa_dies !== null ? (int)$this->validesa_dies : null, PDO::PARAM_INT);
		$stmt->bindValue(':requisits', $this->requisits ?? '');
		$stmt->bindValue(':beneficios_ca', $this->beneficios_ca ?? '');
		$stmt->bindValue(':beneficios_es', $this->beneficios_es ?? '');
		$stmt->bindValue(':ordre_visualitzacio', isset($this->ordre_visualitzacio) ? (int)$this->ordre_visualitzacio : 0, PDO::PARAM_INT);
		$stmt->bindValue(':color_etiqueta', $this->color_etiqueta ?? '#3B82F6');
		$stmt->bindValue(':data_inici_promocio', $this->data_inici_promocio ?: null);
		$stmt->bindValue(':data_fi_promocio', $this->data_fi_promocio ?: null);

		$res = $stmt->execute();
		if ($res) $this->id_tarifa = $this->conn->lastInsertId();
		return $res;
	}

	/**
	 * Actualitza la tarifa actual (basat en $this->id_tarifa)
	 *
	 * @return bool True si l'actualització s'ha executat amb èxit
	 */
	public function actualitzar() {
		if (empty($this->id_tarifa)) return false;
		$sql = 'UPDATE ' . $this->table . ' SET
			nom_servei_ca = :nom_servei_ca,
			nom_servei_es = :nom_servei_es,
			tipus_servei = :tipus_servei,
			descripcio_ca = :descripcio_ca,
			descripcio_es = :descripcio_es,
			durada_minuts = :durada_minuts,
			preu_base = :preu_base,
			preu_promocio = :preu_promocio,
			iva_percentatge = :iva_percentatge,
			moneda = :moneda,
			disponible = :disponible,
			visible_web = :visible_web,
			destacat = :destacat,
			modalitat = :modalitat,
			sessions_pack = :sessions_pack,
			validesa_dies = :validesa_dies,
			requisits = :requisits,
			beneficios_ca = :beneficios_ca,
			beneficios_es = :beneficios_es,
			ordre_visualitzacio = :ordre_visualitzacio,
			color_etiqueta = :color_etiqueta,
			data_inici_promocio = :data_inici_promocio,
			data_fi_promocio = :data_fi_promocio
			WHERE id_tarifa = :id_tarifa';

		$stmt = $this->conn->prepare($sql);
		$stmt->bindValue(':nom_servei_ca', $this->sanitize($this->nom_servei_ca));
		$stmt->bindValue(':nom_servei_es', $this->sanitize($this->nom_servei_es));
		$stmt->bindValue(':tipus_servei', $this->sanitize($this->tipus_servei));
		$stmt->bindValue(':descripcio_ca', $this->descripcio_ca ?? '');
		$stmt->bindValue(':descripcio_es', $this->descripcio_es ?? '');
		$stmt->bindValue(':durada_minuts', (int)($this->durada_minuts ?? 0), PDO::PARAM_INT);
		$stmt->bindValue(':preu_base', $this->preu_base);
		$stmt->bindValue(':preu_promocio', $this->preu_promocio !== null ? $this->preu_promocio : null);
		$stmt->bindValue(':iva_percentatge', $this->iva_percentatge ?? '21.00');
		$stmt->bindValue(':moneda', $this->moneda ?? 'EUR');
		$stmt->bindValue(':disponible', isset($this->disponible) && $this->disponible ? 1 : 0, PDO::PARAM_INT);
		$stmt->bindValue(':visible_web', isset($this->visible_web) && $this->visible_web ? 1 : 0, PDO::PARAM_INT);
		$stmt->bindValue(':destacat', isset($this->destacat) && $this->destacat ? 1 : 0, PDO::PARAM_INT);
		$stmt->bindValue(':modalitat', $this->modalitat ?? 'presencial');
		$stmt->bindValue(':sessions_pack', isset($this->sessions_pack) ? (int)$this->sessions_pack : 1, PDO::PARAM_INT);
		$stmt->bindValue(':validesa_dies', $this->validesa_dies !== null ? (int)$this->validesa_dies : null, PDO::PARAM_INT);
		$stmt->bindValue(':requisits', $this->requisits ?? '');
		$stmt->bindValue(':beneficios_ca', $this->beneficios_ca ?? '');
		$stmt->bindValue(':beneficios_es', $this->beneficios_es ?? '');
		$stmt->bindValue(':ordre_visualitzacio', isset($this->ordre_visualitzacio) ? (int)$this->ordre_visualitzacio : 0, PDO::PARAM_INT);
		$stmt->bindValue(':color_etiqueta', $this->color_etiqueta ?? '#3B82F6');
		$stmt->bindValue(':data_inici_promocio', $this->data_inici_promocio ?: null);
		$stmt->bindValue(':data_fi_promocio', $this->data_fi_promocio ?: null);
		$stmt->bindValue(':id_tarifa', (int)$this->id_tarifa, PDO::PARAM_INT);

		return $stmt->execute();
	}

	/**
	 * Eliminar una tarifa per ID (esborrat físic)
	 *
	 * @param int $id
	 * @return bool
	 */
	public function eliminar($id) {
		$sql = 'DELETE FROM ' . $this->table . ' WHERE id_tarifa = :id';
		$stmt = $this->conn->prepare($sql);
		return $stmt->execute([':id' => (int)$id]);
	}

	/**
	 * Incrementa el comptador 'vegades_contractat' d'una tarifa.
	 *
	 * @param int $id
	 * @param int $by Quantitat a incrementar (per defecte 1)
	 * @return bool
	 */
	public function incrementarVegadesContractat($id, $by = 1) {
		$sql = 'UPDATE ' . $this->table . ' SET vegades_contractat = vegades_contractat + :by WHERE id_tarifa = :id';
		$stmt = $this->conn->prepare($sql);
		return $stmt->execute([':by' => (int)$by, ':id' => (int)$id]);
	}

	/**
	 * Llista les tarifes amb promoció actives en una data determinada.
	 *
	 * @param string|null $date Data en format YYYY-MM-DD (per defecte avui)
	 * @param int $limit
	 * @return array
	 */
	public function listarPromocionsActives($date = null, $limit = 100) {
		$d = $date ?: date('Y-m-d');
		$sql = 'SELECT * FROM ' . $this->table . ' WHERE preu_promocio IS NOT NULL AND (data_inici_promocio IS NULL OR data_inici_promocio <= :d) AND (data_fi_promocio IS NULL OR data_fi_promocio >= :d) ORDER BY ordre_visualitzacio ASC LIMIT :limit';
		$stmt = $this->conn->prepare($sql);
		$stmt->bindValue(':d', $d);
		$stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
		$stmt->execute();
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}

}

?>
