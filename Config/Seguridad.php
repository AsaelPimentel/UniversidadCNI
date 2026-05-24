<?php
class Seguridad {
    
    // Método para asegurar que la sesión esté iniciada siempre que se requiera
    public static function iniciarSesion() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
    }

    // El guardia principal: verifica que estés logueado y tengas el rol correcto
    public static function verificarAcceso($rol_permitido = null) {
        self::iniciarSesion();

        // 1. Si no hay usuario en sesión, lo pateamos al Login
        if (!isset($_SESSION['usuario_id']) || empty($_SESSION['usuario_id'])) {
            header("Location: index.php?c=auth&a=login");
            exit();
        }

        // 2. Si la ruta exige un rol específico y el usuario no lo tiene, lo redirigimos a SU panel
        if ($rol_permitido !== null && $_SESSION['rol'] !== $rol_permitido) {
            self::redirigirPorRol($_SESSION['rol']);
        }
    }

    // Función que usamos en el Login para que no puedan ver la pantalla de Login si ya entraron
    public static function redirigirSiLogueado() {
        self::iniciarSesion();
        if (isset($_SESSION['usuario_id']) && !empty($_SESSION['rol'])) {
            self::redirigirPorRol($_SESSION['rol']);
        }
    }

    // Enrutador de redirección dependiendo del rol del usuario
    public static function redirigirPorRol($rol) {
        switch ($rol) {
            case 'admin':
                header("Location: index.php?c=admin&a=index");
                break;
            case 'instructor':
                header("Location: index.php?c=instructor&a=index");
                break;
            case 'estudiante':
                header("Location: index.php?c=estudiante&a=index");
                break;
            default:
                // Si el rol no existe o está corrupto, destruimos sesión y lo mandamos al login
                header("Location: index.php?c=auth&a=logout");
                break;
        }
        exit();
    }
}
?>