<?php
/**
 * Classe UsuarisPanell
 *
 * Model per a la taula `usuarios_panel`.
 * Implementa operacions CRUD, autenticació bàsica, gestió d'intents de login,
 * tokens per restabliment, 2FA i utilitats per a permisos emmagatzemats en JSON.
 *
 * Comentaris i estil adaptats a la resta de classes del projecte (Connexio, Session, ...).
 *
 * Taula esperada (resum): usuarios_panel
 * - id_usuario, email, password_hash, nombre, apellidos, telefono, avatar
 * - rol, permisos (JSON)
 * - activo, ultimo_acceso, fecha_expiracion, intentos_login, bloqueado
 * - idioma, zona_horiana, notificaciones_email, notificaciones_push
 * - token_restablecimiento, token_expiracion, two_factor_auth, two_factor_secret
 * - fecha_creacion, fecha_actualizacion, creado_por
 *
 * @author Marc
 * @version 1.0
 * @date 2025-10-08
 */

class UsuarisPanell {

	/** @var PDO Connexió a la base de dades */
	private $conn;

	/** @var string Nom de la taula */
	private $table = 'usuarios_panel';

	// ==============================
	// Propietats que mapegen la taula
	// ==============================
	public $id_usuario;
	public $email;
	public $password_hash;
	public $nombre;
	public $apellidos;
	public $telefono;
	public $avatar;
	public $rol;
	public $permisos; // array o JSON
	public $activo;
	public $ultimo_acceso;
	public $fecha_expiracion;
	public $intentos_login;
	public $bloqueado;
	public $idioma;
	public $zona_horiana;
	public $notificaciones_email;
	public $notificaciones_push;
	public $token_restablecimiento;
	public $token_expiracion;
	public $two_factor_auth;
	public $two_factor_secret;
	public $fecha_creacion;
	public $fecha_actualizacion;
	public $creado_por;

	// ==============================
	// Constructor
	// ==============================
	/**
	 * Constructor
	 *
	 * @param PDO $db Connexió PDO (o objecte Connexio->getConnexio())
	 */
	public function __construct($db) {
		$this->conn = $db;
	}

	// ==============================
	// Helpers interns
	// ==============================
	/**
	 * Neteja una cadena per inserir a la base (prevenció XSS bàsica). No substituirà
	 * la validació/filtrat a nivell d'aplicació.
	 *
	 * @param mixed $valor
	 * @return mixed
	 */
	private function sanitize($valor) {
		if (is_string($valor)) {
			return htmlspecialchars(strip_tags($valor));
		}
		return $valor;
	}

	/**
	 * Converteix permisos (JSON en BD) a array PHP
	 *
	 * @param string|null $json
	 * @return array
	 */
	public static function permisosJsonAArray($json) {
		if (!$json) return [];
		$decoded = json_decode($json, true);
		return is_array($decoded) ? $decoded : [];
	}

	/**
	 * Converteix array a JSON segur per guardar a la BD
	 *
	 * @param array $arr
	 * @return string
	 */
	public static function permisosArrayAJson($arr) {
		return json_encode((object)$arr, JSON_UNESCAPED_UNICODE);
	}

	// ==============================
	// Operacions CRUD bàsics
	// ==============================
	/**
	 * Crear un usuari nou
	 *
	 * @param string $plainPassword Contrasenya en text pla (es xifra aquí)
	 * @return int|false ID creat o false en cas d'error
	 */
	public function crear($plainPassword = null) {
		// Validacions bàsiques
		if (empty($this->email) || empty($this->nombre) || empty($this->apellidos)) {
			return false;
		}

		if ($plainPassword !== null) {
			$this->password_hash = password_hash($plainPassword, PASSWORD_DEFAULT);
		}

		// Convertir permisos a JSON si cal
		$permisosJson = is_array($this->permisos) ? self::permisosArrayAJson($this->permisos) : ($this->permisos ?? null);

		$sql = "INSERT INTO {$this->table} 
				(email, password_hash, nombre, apellidos, telefono, avatar, rol, permisos, activo, ultimo_acceso, fecha_expiracion, intentos_login, bloqueado, idioma, zona_horiana, notificaciones_email, notificaciones_push, token_restablecimiento, token_expiracion, two_factor_auth, two_factor_secret, creado_por)
				VALUES
				(:email, :password_hash, :nombre, :apellidos, :telefono, :avatar, :rol, :permisos, :activo, :ultimo_acceso, :fecha_expiracion, :intentos_login, :bloqueado, :idioma, :zona_horiana, :notificaciones_email, :notificaciones_push, :token_restablecimiento, :token_expiracion, :two_factor_auth, :two_factor_secret, :creado_por)
				";

		$stmt = $this->conn->prepare($sql);

		// Netejar i bind
		$stmt->bindValue(':email', $this->sanitize($this->email));
		$stmt->bindValue(':password_hash', $this->password_hash ?? null);
		$stmt->bindValue(':nombre', $this->sanitize($this->nombre));
		$stmt->bindValue(':apellidos', $this->sanitize($this->apellidos));
		$stmt->bindValue(':telefono', $this->sanitize($this->telefono));
		$stmt->bindValue(':avatar', $this->sanitize($this->avatar));
		$stmt->bindValue(':rol', $this->rol ?? 'editor');
		$stmt->bindValue(':permisos', $permisosJson);
		$stmt->bindValue(':activo', isset($this->activo) ? (bool)$this->activo : true, PDO::PARAM_BOOL);
		$stmt->bindValue(':ultimo_acceso', $this->ultimo_acceso ?? null);
		$stmt->bindValue(':fecha_expiracion', $this->fecha_expiracion ?? null);
		$stmt->bindValue(':intentos_login', $this->intentos_login ?? 0, PDO::PARAM_INT);
		$stmt->bindValue(':bloqueado', isset($this->bloqueado) ? (bool)$this->bloqueado : false, PDO::PARAM_BOOL);
		$stmt->bindValue(':idioma', $this->idioma ?? 'ca');
		$stmt->bindValue(':zona_horiana', $this->zona_horiana ?? 'Europe/Madrid');
		$stmt->bindValue(':notificaciones_email', isset($this->notificaciones_email) ? (bool)$this->notificaciones_email : true, PDO::PARAM_BOOL);
		$stmt->bindValue(':notificaciones_push', isset($this->notificaciones_push) ? (bool)$this->notificaciones_push : true, PDO::PARAM_BOOL);
		$stmt->bindValue(':token_restablecimiento', $this->token_restablecimiento ?? null);
		$stmt->bindValue(':token_expiracion', $this->token_expiracion ?? null);
		$stmt->bindValue(':two_factor_auth', isset($this->two_factor_auth) ? (bool)$this->two_factor_auth : false, PDO::PARAM_BOOL);
		$stmt->bindValue(':two_factor_secret', $this->two_factor_secret ?? null);
		$stmt->bindValue(':creado_por', $this->creado_por ?? null, PDO::PARAM_INT);

		if ($stmt->execute()) {
			$this->id_usuario = $this->conn->lastInsertId();
			return $this->id_usuario;
		}

		return false;
	}

	/**
	 * Llegir un usuari per ID
	 * Carrega les propietats de l'objecte amb les dades trobades.
	 *
	 * @return bool True si trobat, false si no
	 */
	public function llegirPerId() {
		$sql = "SELECT * FROM {$this->table} WHERE id_usuario = :id_usuario LIMIT 1";
		$stmt = $this->conn->prepare($sql);
		$stmt->bindValue(':id_usuario', $this->id_usuario, PDO::PARAM_INT);
		$stmt->execute();
		$row = $stmt->fetch(PDO::FETCH_ASSOC);
		if (!$row) return false;

		$this->mapFromRow($row);
		return true;
	}

	/**
	 * Llegir usuari per email (útil per autenticació)
	 *
	 * @param string $email
	 * @return array|false Retorna fila associative o false
	 */
	public function buscarPerEmail($email) {
		$sql = "SELECT * FROM {$this->table} WHERE email = :email LIMIT 1";
		$stmt = $this->conn->prepare($sql);
		$stmt->bindValue(':email', $email);
		$stmt->execute();
		$row = $stmt->fetch(PDO::FETCH_ASSOC);
		return $row ?: false;
	}

	/**
	 * Actualitzar usuari existent
	 *
	 * @return bool True si actualitzat
	 */
	public function actualitzar() {
		if (empty($this->id_usuario)) return false;

		$permisosJson = is_array($this->permisos) ? self::permisosArrayAJson($this->permisos) : ($this->permisos ?? null);

		$sql = "UPDATE {$this->table} SET
					email = :email,
					password_hash = :password_hash,
					nombre = :nombre,
					apellidos = :apellidos,
					telefono = :telefono,
					avatar = :avatar,
					rol = :rol,
					permisos = :permisos,
					activo = :activo,
					ultimo_acceso = :ultimo_acceso,
					fecha_expiracion = :fecha_expiracion,
					intentos_login = :intentos_login,
					bloqueado = :bloqueado,
					idioma = :idioma,
					zona_horiana = :zona_horiana,
					notificaciones_email = :notificaciones_email,
					notificaciones_push = :notificaciones_push,
					token_restablecimiento = :token_restablecimiento,
					token_expiracion = :token_expiracion,
					two_factor_auth = :two_factor_auth,
					two_factor_secret = :two_factor_secret,
					creado_por = :creado_por
				 WHERE id_usuario = :id_usuario";

		$stmt = $this->conn->prepare($sql);

		$stmt->bindValue(':email', $this->sanitize($this->email));
		$stmt->bindValue(':password_hash', $this->password_hash ?? null);
		$stmt->bindValue(':nombre', $this->sanitize($this->nombre));
		$stmt->bindValue(':apellidos', $this->sanitize($this->apellidos));
		$stmt->bindValue(':telefono', $this->sanitize($this->telefono));
		$stmt->bindValue(':avatar', $this->sanitize($this->avatar));
		$stmt->bindValue(':rol', $this->rol ?? 'editor');
		$stmt->bindValue(':permisos', $permisosJson);
		$stmt->bindValue(':activo', isset($this->activo) ? (bool)$this->activo : true, PDO::PARAM_BOOL);
		$stmt->bindValue(':ultimo_acceso', $this->ultimo_acceso ?? null);
		$stmt->bindValue(':fecha_expiracion', $this->fecha_expiracion ?? null);
		$stmt->bindValue(':intentos_login', $this->intentos_login ?? 0, PDO::PARAM_INT);
		$stmt->bindValue(':bloqueado', isset($this->bloqueado) ? (bool)$this->bloqueado : false, PDO::PARAM_BOOL);
		$stmt->bindValue(':idioma', $this->idioma ?? 'ca');
		$stmt->bindValue(':zona_horiana', $this->zona_horiana ?? 'Europe/Madrid');
		$stmt->bindValue(':notificaciones_email', isset($this->notificaciones_email) ? (bool)$this->notificaciones_email : true, PDO::PARAM_BOOL);
		$stmt->bindValue(':notificaciones_push', isset($this->notificaciones_push) ? (bool)$this->notificaciones_push : true, PDO::PARAM_BOOL);
		$stmt->bindValue(':token_restablecimiento', $this->token_restablecimiento ?? null);
		$stmt->bindValue(':token_expiracion', $this->token_expiracion ?? null);
		$stmt->bindValue(':two_factor_auth', isset($this->two_factor_auth) ? (bool)$this->two_factor_auth : false, PDO::PARAM_BOOL);
		$stmt->bindValue(':two_factor_secret', $this->two_factor_secret ?? null);
		$stmt->bindValue(':creado_por', $this->creado_por ?? null, PDO::PARAM_INT);
		$stmt->bindValue(':id_usuario', $this->id_usuario, PDO::PARAM_INT);

		return $stmt->execute();
	}

	/**
	 * Desactivar (marcar inactiu) o eliminar usuari
	 * Recomanat utilitzar desactivació en comptes d'eliminar físicament.
	 *
	 * @param bool $hard Si true, s'elimina físicament (DELETE). Si false, es marca `activo` = false.
	 * @return bool
	 */
	public function eliminar($hard = false) {
		if (empty($this->id_usuario)) return false;

		if ($hard) {
			$sql = "DELETE FROM {$this->table} WHERE id_usuario = :id_usuario";
			$stmt = $this->conn->prepare($sql);
			$stmt->bindValue(':id_usuario', $this->id_usuario, PDO::PARAM_INT);
			return $stmt->execute();
		}

		$sql = "UPDATE {$this->table} SET activo = 0 WHERE id_usuario = :id_usuario";
		$stmt = $this->conn->prepare($sql);
		$stmt->bindValue(':id_usuario', $this->id_usuario, PDO::PARAM_INT);
		return $stmt->execute();
	}

	// ==============================
	// Autenticació i gestió d'intents
	// ==============================
	/**
	 * Verificar credencials d'un usuari per email i contrasenya (text pla)
	 * Si la comprovació és correcta, carrega l'objecte i retorna true.
	 * També actualitza `ultimo_acceso`, reinicia intents i retorna l'usuari.
	 *
	 * @param string $email
	 * @param string $plainPassword
	 * @return bool True si autenticat
	 */
	public function autenticar($email, $plainPassword) {
		$row = $this->buscarPerEmail($email);
		if (!$row) return false;

		// Si l'usuari està bloquejat o inactiu, negar
		if (!$row['activo'] || $row['bloqueado']) {
			return false;
		}

		if (!isset($row['password_hash']) || empty($row['password_hash'])) {
			return false;
		}

		if (password_verify($plainPassword, $row['password_hash'])) {
			// Login correcte: carregar dades a l'objecte
			$this->mapFromRow($row);
			$this->intentos_login = 0;
			$this->ultimo_acceso = date('Y-m-d H:i:s');
			$this->actualitzar();
			return true;
		}

		// Login incorrecte: incrementar intents
		$this->id_usuario = $row['id_usuario'];
		$this->intentos_login = $row['intentos_login'] + 1;
		$this->actualitzarIntent();

		return false;
	}

	/**
	 * Actualitza el camp `intentos_login` i, si supera el llindar, marca `bloqueado`.
	 *
	 * @param int $llindar Nombre d'intents per bloquejar (per defecte 5)
	 * @return bool
	 */
	public function actualitzarIntent($llindar = 5) {
		if (empty($this->id_usuario)) return false;

		$bloquejat = ($this->intentos_login >= $llindar) ? 1 : 0;
		$sql = "UPDATE {$this->table} SET intentos_login = :intentos_login, bloqueado = :bloqueado WHERE id_usuario = :id_usuario";
		$stmt = $this->conn->prepare($sql);
		$stmt->bindValue(':intentos_login', $this->intentos_login, PDO::PARAM_INT);
		$stmt->bindValue(':bloqueado', $bloquejat, PDO::PARAM_BOOL);
		$stmt->bindValue(':id_usuario', $this->id_usuario, PDO::PARAM_INT);
		return $stmt->execute();
	}

	/**
	 * Reinicia intents i desbloqueja l'usuari
	 *
	 * @return bool
	 */
	public function resetIntentsIBloqueig() {
		if (empty($this->id_usuario)) return false;
		$sql = "UPDATE {$this->table} SET intentos_login = 0, bloqueado = 0 WHERE id_usuario = :id_usuario";
		$stmt = $this->conn->prepare($sql);
		$stmt->bindValue(':id_usuario', $this->id_usuario, PDO::PARAM_INT);
		return $stmt->execute();
	}

	// ==============================
	// Tokens de restabliment i 2FA
	// ==============================
	/**
	 * Genera un token segur per a restabliment i l'emmagatzema amb expiració
	 *
	 * @param int $segons Vida del token en segons (per defecte 3600 = 1h)
	 * @return string|false Token generat o false
	 */
	public function generarTokenRestablecimiento($segons = 3600) {
		if (empty($this->id_usuario)) return false;
		$token = bin2hex(random_bytes(32));
		$exp = date('Y-m-d H:i:s', time() + $segons);

		$sql = "UPDATE {$this->table} SET token_restablecimiento = :token, token_expiracion = :exp WHERE id_usuario = :id_usuario";
		$stmt = $this->conn->prepare($sql);
		$stmt->bindValue(':token', $token);
		$stmt->bindValue(':exp', $exp);
		$stmt->bindValue(':id_usuario', $this->id_usuario, PDO::PARAM_INT);

		if ($stmt->execute()) {
			$this->token_restablecimiento = $token;
			$this->token_expiracion = $exp;
			return $token;
		}

		return false;
	}

	/**
	 * Verifica si un token és vàlid (i no expirat)
	 *
	 * @param string $token
	 * @return bool
	 */
	public function verificarTokenRestablecimiento($token) {
		$sql = "SELECT * FROM {$this->table} WHERE token_restablecimiento = :token LIMIT 1";
		$stmt = $this->conn->prepare($sql);
		$stmt->bindValue(':token', $token);
		$stmt->execute();
		$row = $stmt->fetch(PDO::FETCH_ASSOC);
		if (!$row) return false;

		if (empty($row['token_expiracion'])) return false;
		if (strtotime($row['token_expiracion']) < time()) return false;

		$this->mapFromRow($row);
		return true;
	}

	/**
	 * Activa o desactiva 2FA per l'usuari i guarda el secret
	 *
	 * @param bool $activar
	 * @param string|null $secret
	 * @return bool
	 */
	public function setTwoFactor($activar, $secret = null) {
		if (empty($this->id_usuario)) return false;
		$sql = "UPDATE {$this->table} SET two_factor_auth = :tfa, two_factor_secret = :secret WHERE id_usuario = :id_usuario";
		$stmt = $this->conn->prepare($sql);
		$stmt->bindValue(':tfa', $activar ? 1 : 0, PDO::PARAM_BOOL);
		$stmt->bindValue(':secret', $secret);
		$stmt->bindValue(':id_usuario', $this->id_usuario, PDO::PARAM_INT);
		$ok = $stmt->execute();
		if ($ok) {
			$this->two_factor_auth = $activar ? 1 : 0;
			$this->two_factor_secret = $secret;
		}
		return $ok;
	}

	// ==============================
	// Utilitats i llistats
	// ==============================
	/**
	 * Llistar usuaris amb filtres senzills i paginació
	 *
	 * @param array $filtres ['rol' => '', 'activo' => 1, 'q' => 'nom o email']
	 * @param int $limit
	 * @param int $offset
	 * @return array|false
	 */
	public function llistar($filtres = [], $limit = 50, $offset = 0) {
		$wheres = [];
		$params = [];

		if (!empty($filtres['rol'])) {
			$wheres[] = "rol = :rol";
			$params[':rol'] = $filtres['rol'];
		}

		if (isset($filtres['activo'])) {
			$wheres[] = "activo = :activo";
			$params[':activo'] = $filtres['activo'] ? 1 : 0;
		}

		if (!empty($filtres['q'])) {
			$wheres[] = "(email LIKE :q OR nombre LIKE :q OR apellidos LIKE :q)";
			$params[':q'] = '%' . $filtres['q'] . '%';
		}

		$sql = "SELECT * FROM {$this->table}";
		if (!empty($wheres)) $sql .= ' WHERE ' . implode(' AND ', $wheres);
		$sql .= ' ORDER BY fecha_creacion DESC';
		$sql .= ' LIMIT :limit OFFSET :offset';

		$stmt = $this->conn->prepare($sql);
		foreach ($params as $k => $v) {
			$stmt->bindValue($k, $v);
		}
		$stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
		$stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
		$stmt->execute();
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}

	/**
	 * Comprova si existeix un email registrat
	 *
	 * @param string $email
	 * @return bool
	 */
	public function existeixEmail($email) {
		$sql = "SELECT COUNT(1) FROM {$this->table} WHERE email = :email";
		$stmt = $this->conn->prepare($sql);
		$stmt->bindValue(':email', $email);
		$stmt->execute();
		return (bool)$stmt->fetchColumn();
	}

	// ==============================
	// Map i utilitats auxiliars
	// ==============================
	/**
	 * Mapa un array de BD a les propietats de l'objecte
	 *
	 * @param array $row
	 */
	private function mapFromRow(array $row) {
		$this->id_usuario = $row['id_usuario'] ?? null;
		$this->email = $row['email'] ?? null;
		$this->password_hash = $row['password_hash'] ?? null;
		$this->nombre = $row['nombre'] ?? null;
		$this->apellidos = $row['apellidos'] ?? null;
		$this->telefono = $row['telefono'] ?? null;
		$this->avatar = $row['avatar'] ?? null;
		$this->rol = $row['rol'] ?? null;
		$this->permisos = self::permisosJsonAArray($row['permisos'] ?? null);
		$this->activo = isset($row['activo']) ? (bool)$row['activo'] : null;
		$this->ultimo_acceso = $row['ultimo_acceso'] ?? null;
		$this->fecha_expiracion = $row['fecha_expiracion'] ?? null;
		$this->intentos_login = isset($row['intentos_login']) ? (int)$row['intentos_login'] : 0;
		$this->bloqueado = isset($row['bloqueado']) ? (bool)$row['bloqueado'] : false;
		$this->idioma = $row['idioma'] ?? null;
		$this->zona_horiana = $row['zona_horiana'] ?? null;
		$this->notificaciones_email = isset($row['notificaciones_email']) ? (bool)$row['notificaciones_email'] : null;
		$this->notificaciones_push = isset($row['notificaciones_push']) ? (bool)$row['notificaciones_push'] : null;
		$this->token_restablecimiento = $row['token_restablecimiento'] ?? null;
		$this->token_expiracion = $row['token_expiracion'] ?? null;
		$this->two_factor_auth = isset($row['two_factor_auth']) ? (bool)$row['two_factor_auth'] : false;
		$this->two_factor_secret = $row['two_factor_secret'] ?? null;
		$this->fecha_creacion = $row['fecha_creacion'] ?? null;
		$this->fecha_actualizacion = $row['fecha_actualizacion'] ?? null;
		$this->creado_por = $row['creado_por'] ?? null;
	}

	/**
	 * Comprova si l'usuari té un permís específic (procurar usar claus senzilles)
	 *
	 * @param string $clau
	 * @return bool
	 */
	public function hasPermiso($clau) {
		if (empty($this->permisos)) return false;
		return isset($this->permisos[$clau]) && $this->permisos[$clau];
	}

}

?>

