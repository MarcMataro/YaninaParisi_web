<?php
/**
 * PHPMailer simplificat per a l'enviament de correus via SMTP
 */

class PHPMailer {
    public $Host;
    public $Port;
    public $SMTPSecure;
    public $SMTPAuth = true;
    public $Username;
    public $Password;
    public $From;
    public $FromName;
    public $CharSet = 'UTF-8';
    public $Subject;
    public $Body;
    public $ErrorInfo = '';
    
    private $to = [];
    private $reply_to = [];
    
    public function __construct($config_file = null) {
        if ($config_file && file_exists($config_file)) {
            $config = include $config_file;
            $this->Host = $config['smtp_host'] ?? '';
            $this->Port = $config['smtp_port'] ?? 587;
            $this->SMTPSecure = $config['smtp_secure'] ?? 'tls';
            $this->Username = $config['smtp_username'] ?? '';
            $this->Password = $config['smtp_password'] ?? '';
            $this->From = $config['from_email'] ?? '';
            $this->FromName = $config['from_name'] ?? '';
        }
    }
    
    public function isSMTP() {
        // Només per compatibilitat
    }
    
    public function setFrom($email, $name = '') {
        $this->From = $email;
        $this->FromName = $name;
    }
    
    public function addAddress($email, $name = '') {
        $this->to[] = ['email' => $email, 'name' => $name];
    }
    
    public function addReplyTo($email, $name = '') {
        $this->reply_to[] = ['email' => $email, 'name' => $name];
    }
    
    public function send() {
        try {
            // Connexió SMTP
            $host = ($this->SMTPSecure === 'ssl' ? 'ssl://' : '') . $this->Host;
            $smtp = @fsockopen($host, $this->Port, $errno, $errstr, 30);
            
            if (!$smtp) {
                $this->ErrorInfo = "Connexió SMTP fallida al servidor {$this->Host}:{$this->Port} - $errstr ($errno)";
                return false;
            }
            
            $greeting = $this->getResponse($smtp);
            if (strpos($greeting, '220') === false) {
                $this->ErrorInfo = "Servidor SMTP no respon correctament: $greeting";
                fclose($smtp);
                return false;
            }
            
            // EHLO
            fputs($smtp, "EHLO " . ($_SERVER['SERVER_NAME'] ?? 'localhost') . "\r\n");
            $ehlo = $this->getResponse($smtp);
            if (strpos($ehlo, '250') === false) {
                $this->ErrorInfo = "EHLO fallat: $ehlo";
                fclose($smtp);
                return false;
            }
            
            // STARTTLS
            if ($this->SMTPSecure === 'tls') {
                fputs($smtp, "STARTTLS\r\n");
                $tls = $this->getResponse($smtp);
                if (strpos($tls, '220') === false) {
                    $this->ErrorInfo = "STARTTLS fallat (comprova smtp_secure='tls'): $tls";
                    fclose($smtp);
                    return false;
                }
                stream_socket_enable_crypto($smtp, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
                fputs($smtp, "EHLO " . ($_SERVER['SERVER_NAME'] ?? 'localhost') . "\r\n");
                $this->getResponse($smtp);
            }
            
            // AUTH
            fputs($smtp, "AUTH LOGIN\r\n");
            $auth_start = $this->getResponse($smtp);
            if (strpos($auth_start, '334') === false) {
                $this->ErrorInfo = "AUTH LOGIN no suportat pel servidor: $auth_start";
                fclose($smtp);
                return false;
            }
            
            fputs($smtp, base64_encode($this->Username) . "\r\n");
            $user_response = $this->getResponse($smtp);
            if (strpos($user_response, '334') === false) {
                $this->ErrorInfo = "Usuari SMTP incorrecte (smtp_username: {$this->Username}): $user_response";
                fclose($smtp);
                return false;
            }
            
            fputs($smtp, base64_encode($this->Password) . "\r\n");
            $auth = $this->getResponse($smtp);
            
            if (strpos($auth, '235') === false) {
                $this->ErrorInfo = "Contrasenya SMTP incorrecta (comprova smtp_password): $auth";
                fclose($smtp);
                return false;
            }
            
            // MAIL FROM
            fputs($smtp, "MAIL FROM: <{$this->From}>\r\n");
            $this->getResponse($smtp);
            
            // RCPT TO
            foreach ($this->to as $recipient) {
                fputs($smtp, "RCPT TO: <{$recipient['email']}>\r\n");
                $this->getResponse($smtp);
            }
            
            // DATA
            fputs($smtp, "DATA\r\n");
            $this->getResponse($smtp);
            
            // Headers
            $msg = $this->buildHeaders() . "\r\n\r\n" . $this->Body . "\r\n.\r\n";
            fputs($smtp, $msg);
            $this->getResponse($smtp);
            
            // QUIT
            fputs($smtp, "QUIT\r\n");
            fclose($smtp);
            
            return true;
            
        } catch (Exception $e) {
            $this->ErrorInfo = $e->getMessage();
            return false;
        }
    }
    
    private function getResponse($smtp) {
        $response = '';
        while ($line = fgets($smtp, 515)) {
            $response .= $line;
            if (substr($line, 3, 1) == ' ') break;
        }
        return $response;
    }
    
    private function buildHeaders() {
        $h = [];
        $h[] = "From: " . ($this->FromName ? "{$this->FromName} <{$this->From}>" : $this->From);
        
        foreach ($this->to as $r) {
            $h[] = "To: " . ($r['name'] ? "{$r['name']} <{$r['email']}>" : $r['email']);
        }
        
        foreach ($this->reply_to as $r) {
            $h[] = "Reply-To: " . ($r['name'] ? "{$r['name']} <{$r['email']}>" : $r['email']);
        }
        
        $h[] = "Subject: =?{$this->CharSet}?B?" . base64_encode($this->Subject) . "?=";
        $h[] = "MIME-Version: 1.0";
        $h[] = "Content-Type: text/plain; charset={$this->CharSet}";
        $h[] = "X-Mailer: PHP/" . phpversion();
        
        return implode("\r\n", $h);
    }
}
