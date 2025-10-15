<?php
/**
 * Classe Tarifes - Gestió de tarifes i serveis
 * 
 * Aquesta classe representa una tarifa/servei i permet la gestió completa
 * (CRUD) de les tarifes a la base de dades. Les propietats corresponen als
 * camps de la taula 'tarifes'.
 * 
 * @author Marc Mataró
 * @version 1.0
 */

require_once dirname(__FILE__) . '/connexio.php';

class Tarifa {
	// 1. IDENTIFICACIÓ DEL SERVEI
	public $id_tarifa;
	public $nom_servei_ca;
	public $nom_servei_es;
	public $tipus_servei;

	// 2. DESCRIPCIÓ DEL SERVEI
	public $descripcio_ca;
	public $descripcio_es;
	public $durada_minuts;

	// 3. PREUS I TARIFES
	public $preu_base;
	public $preu_promocio;
	public $iva_percentatge;
	public $moneda;

	// 4. DISPONIBILITAT I VISIBILITAT
	public $disponible;
	public $visible_web;
	public $destacat;

	// 5. MODALITAT DE LA SESSIÓ
	public $modalitat;

	// 6. OPCIONS DE PACK I FREQÜÈNCIA
	public $sessions_pack;
	public $validesa_dies;

	// 7. INFORMACIÓ ADDICIONAL
	public $requisits;
	public $beneficios_ca;
	public $beneficios_es;

	// 8. ORDRE I CATEGORITZACIÓ
	public $ordre_visualitzacio;
	public $color_etiqueta;

	// 9. ESTADÍSTIQUES
	public $vegades_contractat;

	// 10. DATES DEL SISTEMA
	public $data_creacio;
	public $data_actualitzacio;
	public $data_inici_promocio;
	public $data_fi_promocio;

	/**
	 * Constructor
	 * @param array $dades Opcional. Array associatiu amb les dades de la tarifa
	 */
	public function __construct($dades = []) {
		foreach ($dades as $clau => $valor) {
			if (property_exists($this, $clau)) {
				$this->$clau = $valor;
			}
		}
	}

	/**
	 * Crear una nova tarifa a la base de dades
	 * @return bool True si s'ha creat correctament
	 */
	public function crear() {
		$conn = Connexio::getInstance()->getConnexio();
		$sql = "INSERT INTO tarifes (
			nom_servei_ca, nom_servei_es, tipus_servei,
			descripcio_ca, descripcio_es, durada_minuts,
			preu_base, preu_promocio, iva_percentatge, moneda,
			disponible, visible_web, destacat,
			modalitat, sessions_pack, validesa_dies,
			requisits, beneficios_ca, beneficios_es,
			ordre_visualitzacio, color_etiqueta,
			vegades_contractat, data_inici_promocio, data_fi_promocio
		) VALUES (
			:nom_servei_ca, :nom_servei_es, :tipus_servei,
			:descripcio_ca, :descripcio_es, :durada_minuts,
			:preu_base, :preu_promocio, :iva_percentatge, :moneda,
			:disponible, :visible_web, :destacat,
			:modalitat, :sessions_pack, :validesa_dies,
			:requisits, :beneficios_ca, :beneficios_es,
			:ordre_visualitzacio, :color_etiqueta,
			:vegades_contractat, :data_inici_promocio, :data_fi_promocio
		)";
		$stmt = $conn->prepare($sql);
		return $stmt->execute([
			':nom_servei_ca' => $this->nom_servei_ca,
			':nom_servei_es' => $this->nom_servei_es,
			':tipus_servei' => $this->tipus_servei,
			':descripcio_ca' => $this->descripcio_ca,
			':descripcio_es' => $this->descripcio_es,
			':durada_minuts' => $this->durada_minuts,
			':preu_base' => $this->preu_base,
			':preu_promocio' => $this->preu_promocio,
			':iva_percentatge' => $this->iva_percentatge,
			':moneda' => $this->moneda,
			':disponible' => $this->disponible,
			':visible_web' => $this->visible_web,
			':destacat' => $this->destacat,
			':modalitat' => $this->modalitat,
			':sessions_pack' => $this->sessions_pack,
			':validesa_dies' => $this->validesa_dies,
			':requisits' => $this->requisits,
			':beneficios_ca' => $this->beneficios_ca,
			':beneficios_es' => $this->beneficios_es,
			':ordre_visualitzacio' => $this->ordre_visualitzacio,
			':color_etiqueta' => $this->color_etiqueta,
			':vegades_contractat' => $this->vegades_contractat,
			':data_inici_promocio' => $this->data_inici_promocio,
			':data_fi_promocio' => $this->data_fi_promocio
		]);
	}

	/**
	 * Actualitzar una tarifa existent
	 * @return bool True si s'ha actualitzat correctament
	 */
	public function actualitzar() {
		if (!$this->id_tarifa) return false;
		$conn = Connexio::getInstance()->getConnexio();
		$sql = "UPDATE tarifes SET
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
			vegades_contractat = :vegades_contractat,
			data_inici_promocio = :data_inici_promocio,
			data_fi_promocio = :data_fi_promocio
		WHERE id_tarifa = :id_tarifa";
		$stmt = $conn->prepare($sql);
		return $stmt->execute([
			':nom_servei_ca' => $this->nom_servei_ca,
			':nom_servei_es' => $this->nom_servei_es,
			':tipus_servei' => $this->tipus_servei,
			':descripcio_ca' => $this->descripcio_ca,
			':descripcio_es' => $this->descripcio_es,
			':durada_minuts' => $this->durada_minuts,
			':preu_base' => $this->preu_base,
			':preu_promocio' => $this->preu_promocio,
			':iva_percentatge' => $this->iva_percentatge,
			':moneda' => $this->moneda,
			':disponible' => $this->disponible,
			':visible_web' => $this->visible_web,
			':destacat' => $this->destacat,
			':modalitat' => $this->modalitat,
			':sessions_pack' => $this->sessions_pack,
			':validesa_dies' => $this->validesa_dies,
			':requisits' => $this->requisits,
			':beneficios_ca' => $this->beneficios_ca,
			':beneficios_es' => $this->beneficios_es,
			':ordre_visualitzacio' => $this->ordre_visualitzacio,
			':color_etiqueta' => $this->color_etiqueta,
			':vegades_contractat' => $this->vegades_contractat,
			':data_inici_promocio' => $this->data_inici_promocio,
			':data_fi_promocio' => $this->data_fi_promocio,
			':id_tarifa' => $this->id_tarifa
		]);
	}

	/**
	 * Eliminar una tarifa
	 * @return bool True si s'ha eliminat correctament
	 */
	public function eliminar() {
		if (!$this->id_tarifa) return false;
		$conn = Connexio::getInstance()->getConnexio();
		$sql = "DELETE FROM tarifes WHERE id_tarifa = :id_tarifa";
		$stmt = $conn->prepare($sql);
		return $stmt->execute([':id_tarifa' => $this->id_tarifa]);
	}

	/**
	 * Obtenir una tarifa per ID
	 * @param int $id_tarifa
	 * @return Tarifa|null
	 */
	public static function obtenirPerId($id_tarifa) {
		$conn = Connexio::getInstance()->getConnexio();
		$sql = "SELECT * FROM tarifes WHERE id_tarifa = :id_tarifa";
		$stmt = $conn->prepare($sql);
		$stmt->execute([':id_tarifa' => $id_tarifa]);
		$dades = $stmt->fetch(PDO::FETCH_ASSOC);
		return $dades ? new self($dades) : null;
	}

	/**
	 * Obtenir totes les tarifes (opcionalment filtrades)
	 * @param array $filtre Array associatiu de filtres
	 * @return array Array de Tarifa
	 */
	public static function obtenirTotes($filtre = []) {
		$conn = Connexio::getInstance()->getConnexio();
		$sql = "SELECT * FROM tarifes";
		$params = [];
		if (!empty($filtre)) {
			$condicions = [];
			foreach ($filtre as $clau => $valor) {
				$condicions[] = "$clau = :$clau";
				$params[":$clau"] = $valor;
			}
			$sql .= " WHERE " . implode(' AND ', $condicions);
		}
		$sql .= " ORDER BY ordre_visualitzacio ASC, id_tarifa ASC";
		$stmt = $conn->prepare($sql);
		$stmt->execute($params);
		$resultat = [];
		while ($dades = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$resultat[] = new self($dades);
		}
		return $resultat;
	}

	// Aquí podries afegir més getters/setters específics si cal
}
