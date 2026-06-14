<?php
class MailConfig {
    // Servidor SMTP (ej. smtp.gmail.com, mail.universidadcni.edu.mx, etc.)
    public static $host = 'smtp.gmail.com';
    
    // Autenticación SMTP
    public static $smtp_auth = true;
    
    // Usuario del correo institucional o emisor de la plataforma
    public static $username = 'notificaciones@universidadcni.edu.mx'; 
    
    // Contraseña de aplicación segura
    public static $password = 'tu_clave_de_16_letras_aqui'; 
    
    // Encriptación y Puerto (ENCRYPTION_SMTPS = 465, ENCRYPTION_STARTTLS = 587)
    public static $secure = 'ssl'; // O 'tls'
    public static $port = 465;
    
    // Nombre visible del sistema
    public static $from_name = 'Universidad CNI';
}
?>