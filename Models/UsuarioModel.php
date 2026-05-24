<?php
require_once __DIR__ . '/../Config/Conexion.php';
class UsuarioModel {
    private $db;

    public function __construct() {
        $this->db = ConexionDB::obtenerConexion();
    }

    public function login($email) {
        $email = mysqli_real_escape_string($this->db, $email);
        $sql = "SELECT * FROM usuarios WHERE email = '$email'";
        $res = mysqli_query($this->db, $sql);
        return mysqli_fetch_assoc($res);
    }

    public function crear($datos) {
        $nombre = mysqli_real_escape_string($this->db, $datos['nombre']);
        $email  = mysqli_real_escape_string($this->db, $datos['email']);
        $rol    = mysqli_real_escape_string($this->db, $datos['rol']);
        $pass   = mysqli_real_escape_string($this->db, $datos['password']);

        // Evitar duplicados
        $verificar = mysqli_query($this->db, "SELECT id FROM usuarios WHERE email = '$email'");
        if (mysqli_num_rows($verificar) > 0) return "duplicado";

        $sql = "INSERT INTO usuarios (nombre, email, password, rol) VALUES ('$nombre', '$email', '$pass', '$rol')";
        return mysqli_query($this->db, $sql) ? "ok" : "error";
    }

    public function actualizar($datos) {
        $id     = mysqli_real_escape_string($this->db, $datos['usuario_id']);
        $nombre = mysqli_real_escape_string($this->db, $datos['nombre']);
        $email  = mysqli_real_escape_string($this->db, $datos['email']);
        $rol    = mysqli_real_escape_string($this->db, $datos['rol']);

        $verificar = mysqli_query($this->db, "SELECT id FROM usuarios WHERE email = '$email' AND id != '$id'");
        if (mysqli_num_rows($verificar) > 0) return "duplicado";

        $sql = "UPDATE usuarios SET nombre='$nombre', email='$email', rol='$rol' WHERE id='$id'";
        return mysqli_query($this->db, $sql) ? "ok" : "error";
    }

    public function eliminar($id) {
        $id = mysqli_real_escape_string($this->db, $id);
        return mysqli_query($this->db, "DELETE FROM usuarios WHERE id = '$id'");
    }

    public function obtenerTodos($buscar = '', $offset = 0, $limite = 5) {
        $where = "";
        if (!empty($buscar)) {
            $b = mysqli_real_escape_string($this->db, $buscar);
            $where = " WHERE nombre LIKE '%$b%' OR email LIKE '%$b%' ";
        }
        $sql = "SELECT * FROM usuarios $where ORDER BY id DESC LIMIT $offset, $limite";
        return mysqli_query($this->db, $sql);
    }

    public function contarTodos($buscar = '') {
        $where = "";
        if (!empty($buscar)) {
            $b = mysqli_real_escape_string($this->db, $buscar);
            $where = " WHERE nombre LIKE '%$b%' OR email LIKE '%$b%' ";
        }
        $sql = "SELECT COUNT(*) as total FROM usuarios $where";
        $res = mysqli_fetch_assoc(mysqli_query($this->db, $sql));
        return $res['total'];
    }

    public function obtenerPorId($id) {
        $id = mysqli_real_escape_string($this->db, $id);
        $res = mysqli_query($this->db, "SELECT * FROM usuarios WHERE id = '$id'");
        return mysqli_fetch_assoc($res);
    }
}
?>