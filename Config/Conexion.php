<?php
class ConexionDB {
    private static $conexion = null;

    public static function obtenerConexion() {
        // Si no hay una conexión activa, la creamos
        if (self::$conexion === null) {
            $host = "localhost";
            $usuario = "root";
            $password = "";
            $base_datos = "universidadcni";

            self::$conexion = mysqli_connect($host, $usuario, $password, $base_datos);

            // Verificar si hubo un error fatal de conexión
            if (!self::$conexion) {
                die("Error crítico: No se pudo conectar a la base de datos. " . mysqli_connect_error());
            }

            // Forzar UTF-8 para que los acentos y las "ñ" en español se guarden y lean correctamente
            mysqli_set_charset(self::$conexion, "utf8mb4");
        }
        
        // Devolvemos la conexión activa
        return self::$conexion;
    }
}
?>