<?php
require_once __DIR__ . '/../Config/Conexion.php';
class CursoModel {
    private $db;

    public function __construct() {
        $this->db = ConexionDB::obtenerConexion();
    }

    public function guardar($datos, $archivo, $instructor_id) {
        $titulo = mysqli_real_escape_string($this->db, $datos['titulo']);
        $desc   = mysqli_real_escape_string($this->db, $datos['descripcion']);
        $ruta_db = "assets/img/default.jpg";

        if (isset($archivo['imagen_curso']) && $archivo['imagen_curso']['error'] == 0) {
            $extension = pathinfo($archivo['imagen_curso']['name'], PATHINFO_EXTENSION);
            $nombre_archivo = "curso_" . time() . "." . $extension;
            $ruta_destino = "assets/img/" . $nombre_archivo; // Ruta relativa al index.php

            if (move_uploaded_file($archivo['imagen_curso']['tmp_name'], $ruta_destino)) {
                $ruta_db = "assets/img/" . $nombre_archivo;
            }
        }

        $sql = "INSERT INTO cursos (titulo, descripcion, instructor_id, imagen) VALUES ('$titulo', '$desc', '$instructor_id', '$ruta_db')";
        return mysqli_query($this->db, $sql);
    }

    public function eliminarCurso($id_curso, $instructor_id) {
        $id_curso = mysqli_real_escape_string($this->db, $id_curso);
        $instructor_id = mysqli_real_escape_string($this->db, $instructor_id);

        $query_img = mysqli_query($this->db, "SELECT imagen FROM cursos WHERE id = '$id_curso' AND instructor_id = '$instructor_id'");
        if ($row = mysqli_fetch_assoc($query_img)) {
            if (!empty($row['imagen']) && $row['imagen'] != 'assets/img/default.jpg' && file_exists($row['imagen'])) {
                unlink($row['imagen']);
            }
        }
        return mysqli_query($this->db, "DELETE FROM cursos WHERE id = '$id_curso' AND instructor_id = '$instructor_id'");
    }

    public function obtenerPorInstructor($instructor_id) {
        $id = mysqli_real_escape_string($this->db, $instructor_id);
        return mysqli_query($this->db, "SELECT * FROM cursos WHERE instructor_id = '$id' ORDER BY fecha_creacion DESC");
    }

    public function obtenerCatalogo() {
        $sql = "SELECT c.*, u.nombre AS nombre_instructor FROM cursos c INNER JOIN usuarios u ON c.instructor_id = u.id ORDER BY c.fecha_creacion DESC";
        return mysqli_query($this->db, $sql);
    }

    public function obtenerCursoPorId($id_curso) {
        $id = mysqli_real_escape_string($this->db, $id_curso);
        $res = mysqli_query($this->db, "SELECT * FROM cursos WHERE id = '$id'");
        return mysqli_fetch_assoc($res);
    }
}
?>