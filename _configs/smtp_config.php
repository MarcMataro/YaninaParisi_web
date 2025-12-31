<?php
/**
 * Configuració SMTP per a l'enviament de correus
 * 
 * INSTRUCCIONS:
 * 1. Omple els valors amb les credencials del teu compte de correu
 * 2. El servidor SMTP normalment és: mail.yaninaparisi.com o el que et proporcioni el teu hosting
 * 3. El port normalment és 587 (STARTTLS) o 465 (SSL)
 */

return [
    'smtp_host' => 'smtp.ionos.es',  // Canvia pel teu servidor SMTP
    'smtp_port' => 465,                       // 587 per TLS o 465 per SSL
    'smtp_secure' => 'ssl',                   // 'tls' o 'ssl'
    'smtp_username' => 'info@yaninaparisi.com',
    'smtp_password' => 'Yaninapsicologa2025',                    // AFEGIR LA CONTRASENYA AQUÍ
    'from_email' => 'info@yaninaparisi.com',
    'from_name' => 'Yanina Parisi',
];
