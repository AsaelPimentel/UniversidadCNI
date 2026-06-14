<?php
require_once __DIR__ . '/../Config/Conexion.php';
require_once __DIR__ . '/NotificacionModel.php';
class ForoModel
{
    private $db;

    public function __construct()
    {
        $this->db = ConexionDB::obtenerConexion();
    }

public function guardarComentario($leccion_id, $usuario_id, $texto)
    {
        $leccion = mysqli_real_escape_string($this->db, $leccion_id);
        $usuario = mysqli_real_escape_string($this->db, $usuario_id);
        $texto = mysqli_real_escape_string($this->db, $texto);

        $sql = "INSERT INTO comentarios (leccion_id, usuario_id, comentario) VALUES ('$leccion', '$usuario', '$texto')";
        $ejecutado = mysqli_query($this->db, $sql);

        if ($ejecutado) {
            // Obtenemos información del curso y de quien comenta
            $sql_info = "SELECT c.instructor_id, c.id as curso_id, u.rol 
                         FROM cursos c 
                         JOIN lecciones l ON c.id = l.curso_id 
                         JOIN usuarios u ON u.id = '$usuario'
                         WHERE l.id = '$leccion'";
            $info = mysqli_fetch_assoc(mysqli_query($this->db, $sql_info));

            $notif = new NotificacionModel();

            if ($info['rol'] == 'estudiante') {
                // Si el alumno comenta, se notifica al instructor
                $notif->crearNotificacion($info['instructor_id'], "Nuevo comentario en el foro de dudas.", $leccion, $info['curso_id'], "#zona-foro-dudas");
            } else {
                // Si el instructor responde, se notifica a los estudiantes que han participado en esta lección
                $sql_est = "SELECT DISTINCT usuario_id FROM comentarios WHERE leccion_id = '$leccion' AND usuario_id != '$usuario'";
                $res_est = mysqli_query($this->db, $sql_est);
                
                while ($est = mysqli_fetch_assoc($res_est)) {
                    $notif->crearNotificacion($est['usuario_id'], "El instructor respondió tu duda en el foro.", $leccion, $info['curso_id'], "#zona-foro-dudas");
                }
            }
        }
        return $ejecutado;
    }

    public function obtenerPorLeccion($leccion_id)
    {
        $leccion = mysqli_real_escape_string($this->db, $leccion_id);
        $sql = "SELECT c.*, u.nombre, u.rol 
                FROM comentarios c 
                INNER JOIN usuarios u ON c.usuario_id = u.id 
                WHERE c.leccion_id = '$leccion' ORDER BY c.fecha DESC";
        return mysqli_query($this->db, $sql);
    }

    public function obtenerDudasPorInstructor($instructor_id)
    {
        $id = mysqli_real_escape_string($this->db, $instructor_id);
        $sql = "SELECT c.*, u.nombre as alumno, u.rol as rol_usuario, l.titulo as leccion, cur.titulo as curso 
                FROM comentarios c 
                INNER JOIN usuarios u ON c.usuario_id = u.id 
                INNER JOIN lecciones l ON c.leccion_id = l.id 
                INNER JOIN cursos cur ON l.curso_id = cur.id 
                WHERE cur.instructor_id = '$id' 
                ORDER BY c.fecha DESC";
        return mysqli_query($this->db, $sql);
    }
}
