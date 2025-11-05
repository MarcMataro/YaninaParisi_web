<?php
/**
 * Classe RessenyaTokens
 *
 * Gestiona tokens d'un sol ús per permetre a pacients enviar ressenyes.
 * - createToken(pacient_id, hrs)
 * - getByToken(token)
 * - consumeToken(token)
 *
 * Usa PDO i prepared statements.
 */

class RessenyaTokens {
    protected $pdo;
    protected $table = 'ressenya_tokens';

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Comprova si la taula existeix a la BBDD activa.
     * @return bool
     */
    public function tableExists(): bool {
        try {
            $stmt = $this->pdo->query("SHOW TABLES LIKE '" . $this->table . "'");
            $res = $stmt->fetch(PDO::FETCH_NUM);
            return (bool)$res;
        } catch (PDOException $e) {
            error_log('Error comprovant existència taula ' . $this->table . ': ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Genera un token segur per a un pacient i el desa amb data d'expiració opcional
     * @param int $pacient_id
     * @param int $hours_valid Hora d'expiració des de la creació (0 = sense expiració)
     * @return string|false Token generat o false en error
     */
    public function createToken(int $pacient_id, int $hours_valid = 72) {
        try {
            $token = bin2hex(random_bytes(32)); // 64 chars
        } catch (Exception $e) {
            error_log('Error generant token: ' . $e->getMessage());
            return false;
        }

        $expires_at = null;
        // Use DB time (NOW()) for consistency with table's CURRENT_TIMESTAMP
        // We'll store NULL when $hours_valid == 0, otherwise set expires_at = NOW() + INTERVAL hours_valid HOUR

        if (!$this->tableExists()) {
            error_log("La taula {$this->table} no existeix. Creeu-la amb sql/ressenya_tokens.sql");
            return false;
        }

        try {
            if ($hours_valid > 0) {
                $sql = "INSERT INTO `{$this->table}` (pacient_id, token, expires_at) VALUES (:pid, :token, DATE_ADD(NOW(), INTERVAL :hrs HOUR))";
                $stmt = $this->pdo->prepare($sql);
                $ok = $stmt->execute([':pid' => $pacient_id, ':token' => $token, ':hrs' => $hours_valid]);
            } else {
                $sql = "INSERT INTO `{$this->table}` (pacient_id, token, expires_at) VALUES (:pid, :token, NULL)";
                $stmt = $this->pdo->prepare($sql);
                $ok = $stmt->execute([':pid' => $pacient_id, ':token' => $token]);
            }
            return $ok ? $token : false;
        } catch (PDOException $e) {
            error_log('Error insertant token a ' . $this->table . ': ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtenir informació del token si és vàlid (no usat i no caducat)
     * @param string $token
     * @return array|false
     */
    public function getByToken(string $token) {
        try {
            // Use DB time (NOW()) to compare expires_at and used_at for consistency
            $sql = "SELECT * FROM `{$this->table}` WHERE token = :token AND (used_at IS NULL) AND (expires_at IS NULL OR expires_at > NOW()) LIMIT 1";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':token' => $token]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row ?: false;
        } catch (PDOException $e) {
            error_log('Error llegint token a ' . $this->table . ': ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Marca el token com utilitzat (consumeix-lo). Retorna pacient_id si ok, false en cas contrari.
     * @param string $token
     * @return int|false
     */
    public function consumeToken(string $token) {
        // Fem una revàlida per garantir que encara està disponible
        $row = $this->getByToken($token);
        if (!$row) return false;

        try {
            // Use DB NOW() so used_at matches DB timezone (and created_at)
            $sql = "UPDATE `{$this->table}` SET used_at = NOW() WHERE token = :token AND used_at IS NULL";
            $stmt = $this->pdo->prepare($sql);
            $ok = $stmt->execute([':token' => $token]);
            return $ok ? (int)$row['pacient_id'] : false;
        } catch (PDOException $e) {
            error_log('Error consumint token a ' . $this->table . ': ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Invalida (marca com usada) un token sense retornar el pacient
     * @param string $token
     * @return bool
     */
    public function invalidateToken(string $token) {
        try {
            $sql = "UPDATE `{$this->table}` SET used_at = NOW() WHERE token = :token AND used_at IS NULL";
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([':token' => $token]);
        } catch (PDOException $e) {
            error_log('Error invalidant token a ' . $this->table . ': ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Neteja tokens caducats o utilitzats anciens (opcional)
     * @param int $days
     * @return int nombre de files esborrades
     */
    public function cleanup(int $days = 90) {
        $limitDate = date('Y-m-d H:i:s', strtotime("-{$days} days"));
        try {
            $sql = "DELETE FROM `{$this->table}` WHERE (used_at IS NOT NULL AND used_at < :limit) OR (expires_at IS NOT NULL AND expires_at < :limit)";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':limit' => $limitDate]);
            return $stmt->rowCount();
        } catch (PDOException $e) {
            error_log('Error netejant tokens a ' . $this->table . ': ' . $e->getMessage());
            return 0;
        }
    }
}

// EOF
