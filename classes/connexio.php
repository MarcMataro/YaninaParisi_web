<?php
/**
 * Classe Connexio - Gestió de connexions a la base de dades
 * 
 * Aquesta classe gestiona la connexió a la base de dades utilitzant PDO,
 * amb configuració centralitzada, gestió d'errors i patró Singleton.
 * 
 * @author Marc Mataró
 * @version 1.0
 */

class Connexio {
    private static $instancia = null;
    private $connexio;
    private $config;
    
    // Configuració per defecte de PDO
    private $opcions = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
    ];
    
    /**
     * Constructor privat per implementar el patró Singleton
     * 
     * @throws Exception Si no es pot carregar la configuració o connectar
     */
    private function __construct() {
        $this->carregarConfiguracio();
        $this->connectar();
    }
    
    /**
     * Obtenir la instància única de la connexió (Singleton)
     * 
     * @return Connexio Instància única
     * @throws Exception Si hi ha errors en la connexió
     */
    public static function getInstance() {
        if (self::$instancia === null) {
            self::$instancia = new self();
        }
        return self::$instancia;
    }
    
    /**
     * Obtenir la connexió PDO
     * 
     * @return PDO Objecte de connexió PDO
     */
    public function getConnexio() {
        return $this->connexio;
    }
    
    /**
     * Carregar la configuració des del fitxer connection.inc
     * 
     * @throws Exception Si no es pot carregar el fitxer de configuració
     */
    private function carregarConfiguracio() {
        $rutaConfig = dirname(__DIR__) . '/_data/connection.inc';
        
        if (!file_exists($rutaConfig)) {
            throw new Exception("No s'ha trobat el fitxer de configuració: {$rutaConfig}");
        }
        
        // Carregar la configuració
        include $rutaConfig;
        
        if (!isset($db_config) || !is_array($db_config)) {
            throw new Exception("Configuració de base de dades no vàlida al fitxer: {$rutaConfig}");
        }
        
        // Validar camps obligatoris
        $campsObligatoris = ['h', 'u', 'd', 't'];
        foreach ($campsObligatoris as $camp) {
            if (!isset($db_config[$camp])) {
                throw new Exception("Camp obligatori '{$camp}' no trobat a la configuració");
            }
        }
        
        $this->config = [
            'host' => $db_config['h'],
            'username' => $db_config['u'],
            'password' => $db_config['p'] ?? '',
            'database' => $db_config['d'],
            'port' => $db_config['t']
        ];
    }
    
    /**
     * Establir la connexió amb la base de dades
     * 
     * @throws Exception Si no es pot connectar a la base de dades
     */
    private function connectar() {
        try {
            $dsn = sprintf(
                "mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4",
                $this->config['host'],
                $this->config['port'],
                $this->config['database']
            );
            
            $this->connexio = new PDO(
                $dsn,
                $this->config['username'],
                $this->config['password'],
                $this->opcions
            );
            
        } catch (PDOException $e) {
            error_log("Error de connexió a la base de dades: " . $e->getMessage());
            throw new Exception("No s'ha pogut connectar a la base de dades. Contacti amb l'administrador.");
        }
    }
    
    /**
     * Verificar si la connexió està activa
     * 
     * @return bool True si la connexió està activa
     */
    public function estaConnectat() {
        try {
            return $this->connexio && $this->connexio->query('SELECT 1')->fetchColumn() == 1;
        } catch (PDOException $e) {
            return false;
        }
    }
    
    /**
     * Reconnectar a la base de dades si la connexió s'ha perdut
     * 
     * @return bool True si s'ha reconnectat correctament
     */
    public function reconnectar() {
        try {
            $this->connectar();
            return true;
        } catch (Exception $e) {
            error_log("Error en reconnectar: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Executar una consulta preparada
     * 
     * @param string $sql Consulta SQL
     * @param array $parametres Paràmetres per a la consulta
     * @return PDOStatement|false Resultat de la consulta
     */
    public function query($sql, $parametres = []) {
        try {
            $stmt = $this->connexio->prepare($sql);
            $stmt->execute($parametres);
            return $stmt;
        } catch (PDOException $e) {
            error_log("Error en executar consulta: " . $e->getMessage() . " SQL: " . $sql);
            return false;
        }
    }
    
    /**
     * Executar una consulta i obtenir tots els resultats
     * 
     * @param string $sql Consulta SQL
     * @param array $parametres Paràmetres per a la consulta
     * @return array|false Array amb els resultats o false si hi ha error
     */
    public function fetchAll($sql, $parametres = []) {
        $stmt = $this->query($sql, $parametres);
        return $stmt ? $stmt->fetchAll() : false;
    }
    
    /**
     * Executar una consulta i obtenir un sol resultat
     * 
     * @param string $sql Consulta SQL
     * @param array $parametres Paràmetres per a la consulta
     * @return array|false Array amb el resultat o false si no hi ha resultats
     */
    public function fetch($sql, $parametres = []) {
        $stmt = $this->query($sql, $parametres);
        return $stmt ? $stmt->fetch() : false;
    }
    
    /**
     * Executar una consulta i obtenir una sola columna
     * 
     * @param string $sql Consulta SQL
     * @param array $parametres Paràmetres per a la consulta
     * @param int $columna Índex de la columna (per defecte 0)
     * @return mixed|false Valor de la columna o false si hi ha error
     */
    public function fetchColumn($sql, $parametres = [], $columna = 0) {
        $stmt = $this->query($sql, $parametres);
        return $stmt ? $stmt->fetchColumn($columna) : false;
    }
    
    /**
     * Iniciar una transacció
     * 
     * @return bool True si s'ha iniciat correctament
     */
    public function iniciarTransaccio() {
        try {
            return $this->connexio->beginTransaction();
        } catch (PDOException $e) {
            error_log("Error en iniciar transacció: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Confirmar una transacció
     * 
     * @return bool True si s'ha confirmat correctament
     */
    public function confirmarTransaccio() {
        try {
            return $this->connexio->commit();
        } catch (PDOException $e) {
            error_log("Error en confirmar transacció: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Desfer una transacció
     * 
     * @return bool True si s'ha desfet correctament
     */
    public function desferTransaccio() {
        try {
            return $this->connexio->rollBack();
        } catch (PDOException $e) {
            error_log("Error en desfer transacció: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtenir l'ID de l'últim registre inserit
     * 
     * @return string|false ID de l'últim registre inserit
     */
    public function ultimId() {
        return $this->connexio->lastInsertId();
    }
    
    /**
     * Obtenir informació sobre la base de dades
     * 
     * @return array Informació de la connexió
     */
    public function getInfoConnexio() {
        return [
            'host' => $this->config['host'],
            'database' => $this->config['database'],
            'port' => $this->config['port'],
            'charset' => 'utf8mb4',
            'driver' => $this->connexio->getAttribute(PDO::ATTR_DRIVER_NAME),
            'versio' => $this->connexio->getAttribute(PDO::ATTR_SERVER_VERSION)
        ];
    }
    
    /**
     * Verificar la integritat de la connexió i la base de dades
     * 
     * @return array Resultat de les verificacions
     */
    public function verificarConnexio() {
        $verificacions = [
            'connexio_activa' => $this->estaConnectat(),
            'base_dades_accessible' => false,
            'charset_correcte' => false,
            'motor_innodb' => false
        ];
        
        try {
            // Verificar accés a la base de dades
            $stmt = $this->connexio->query("SELECT DATABASE() as db_name");
            $result = $stmt->fetch();
            $verificacions['base_dades_accessible'] = ($result['db_name'] === $this->config['database']);
            
            // Verificar charset de la connexió
            $stmt = $this->connexio->query("SELECT @@character_set_connection as charset");
            $result = $stmt->fetch();
            $verificacions['charset_correcte'] = (strpos($result['charset'], 'utf8mb4') !== false);
            
            // Verificar suport InnoDB
            $stmt = $this->connexio->query("SELECT ENGINE FROM information_schema.ENGINES WHERE ENGINE = 'InnoDB' AND SUPPORT = 'YES'");
            $verificacions['motor_innodb'] = ($stmt->fetchColumn() === 'InnoDB');
            
        } catch (PDOException $e) {
            error_log("Error en verificar connexió: " . $e->getMessage());
        }
        
        return $verificacions;
    }
    
    /**
     * Executar múltiples consultes en una transacció
     * 
     * @param array $consultes Array de consultes amb format ['sql' => '', 'parametres' => []]
     * @return bool True si totes s'han executat correctament
     */
    public function executarTransaccio($consultes) {
        if (!$this->iniciarTransaccio()) {
            return false;
        }
        
        try {
            foreach ($consultes as $consulta) {
                $sql = $consulta['sql'];
                $parametres = $consulta['parametres'] ?? [];
                
                if (!$this->query($sql, $parametres)) {
                    throw new Exception("Error en executar consulta: " . $sql);
                }
            }
            
            return $this->confirmarTransaccio();
            
        } catch (Exception $e) {
            $this->desferTransaccio();
            error_log("Error en transacció: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Prevenir la clonació de la instància
     */
    private function __clone() {}
    
    /**
     * Prevenir la deserialització de la instància
     */
    public function __wakeup() {
        throw new Exception("No es pot deserialitzar una instància de " . __CLASS__);
    }
    
    /**
     * Tancar la connexió quan es destrueix l'objecte
     */
    public function __destruct() {
        $this->connexio = null;
    }
}
?>
