<?php
require_once __DIR__ . '/../Config/Conexion.php';
class NotificacionModel {
    private $db;

    public function __construct() {
        $this->db = ConexionDB::obtenerConexion();
    }

public function crearNotificacion($usuario_id, $mensaje, $leccion_id, $curso_id, $ancla = '') {
        $u = mysqli_real_escape_string($this->db, $usuario_id);
        $m = mysqli_real_escape_string($this->db, $mensaje);
        $l = mysqli_real_escape_string($this->db, $leccion_id);
        $c = mysqli_real_escape_string($this->db, $curso_id);
        $a = mysqli_real_escape_string($this->db, $ancla);

        $sql = "INSERT INTO notificaciones (usuario_id, mensaje, leccion_id, curso_id, ancla) VALUES ('$u', '$m', '$l', '$c', '$a')";
        return mysqli_query($this->db, $sql);
    }

    public function obtenerNoLeidas($usuario_id) {
        $u = mysqli_real_escape_string($this->db, $usuario_id);
        $sql = "SELECT * FROM notificaciones WHERE usuario_id = '$u' AND leida = 0 ORDER BY fecha DESC";
        return mysqli_query($this->db, $sql);
    }

    public function marcarLeida($notif_id) {
        $id = mysqli_real_escape_string($this->db, $notif_id);
        return mysqli_query($this->db, "UPDATE notificaciones SET leida = 1 WHERE id = '$id'");
    }

    public function marcarTodasLeidas($usuario_id) {
        $u = mysqli_real_escape_string($this->db, $usuario_id);
        return mysqli_query($this->db, "UPDATE notificaciones SET leida = 1 WHERE usuario_id = '$u'");
    }
}
?>