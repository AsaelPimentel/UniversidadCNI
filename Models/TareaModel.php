<?php
require_once __DIR__ . '/../Config/Conexion.php';
require_once __DIR__ . '/NotificacionModel.php';
class TareaModel
{
    private $db;

    public function __construct()
    {
        $this->db = ConexionDB::obtenerConexion();
    }

    public function guardarTarea($leccion_id, $usuario_id, $archivo, $estatus)
    {
        $leccion_id = mysqli_real_escape_string($this->db, $leccion_id);
        $usuario_id = mysqli_real_escape_string($this->db, $usuario_id);
        $estatus = mysqli_real_escape_string($this->db, $estatus);

        if (!is_dir('Assets/Tareas/')) {
            mkdir('Assets/Tareas/', 0777, true);
        }

        $nombre_limpio = preg_replace("/[^a-zA-Z0-9.]/", "_", basename($archivo['name']));
        $ruta_db = "Assets/Tareas/tarea_u" . $usuario_id . "_l" . $leccion_id . "_" . time() . "_" . $nombre_limpio;

        if (move_uploaded_file($archivo['tmp_name'], $ruta_db)) {
            $sql = "INSERT INTO tareas_entregadas (leccion_id, usuario_id, archivo_ruta, estatus_entrega) VALUES ('$leccion_id', '$usuario_id', '$ruta_db', '$estatus')";
            return mysqli_query($this->db, $sql);
        }
        return false;
    }

    public function obtenerMiTarea($usuario_id, $leccion_id)
    {
        $usuario = mysqli_real_escape_string($this->db, $usuario_id);
        $leccion = mysqli_real_escape_string($this->db, $leccion_id);
        $res = mysqli_query($this->db, "SELECT * FROM tareas_entregadas WHERE leccion_id = '$leccion' AND usuario_id = '$usuario'");
        return mysqli_fetch_assoc($res);
    }

    public function obtenerEntregasPorInstructor($id_instructor, $id_curso = 'todos')
    {
        $id_instructor = mysqli_real_escape_string($this->db, $id_instructor);
        $filtro = ($id_curso !== 'todos') ? " AND c.id = '" . mysqli_real_escape_string($this->db, $id_curso) . "' " : "";

        // NOTA: Se agregó t.id AS tarea_id para poder identificar cada tarea en los comentarios
        $sql = "SELECT t.id AS tarea_id, t.archivo_ruta, t.fecha_envio, t.estatus_entrega, u.nombre AS alumno_nombre, c.titulo AS curso_titulo, l.titulo AS leccion_titulo
                FROM tareas_entregadas t
                INNER JOIN usuarios u ON t.usuario_id = u.id
                INNER JOIN lecciones l ON t.leccion_id = l.id
                INNER JOIN cursos c ON l.curso_id = c.id
                WHERE c.instructor_id = '$id_instructor' $filtro ORDER BY t.fecha_envio DESC";
        return mysqli_query($this->db, $sql);
    }

    // --- NUEVAS FUNCIONES PARA COMENTARIOS PRIVADOS ---
    public function guardarComentarioPrivado($tarea_id, $usuario_id, $comentario)
    {
        $tarea = mysqli_real_escape_string($this->db, $tarea_id);
        $usuario = mysqli_real_escape_string($this->db, $usuario_id);
        $texto = mysqli_real_escape_string($this->db, $comentario);

        $sql = "INSERT INTO comentarios_tarea (tarea_id, usuario_id, comentario) VALUES ('$tarea', '$usuario', '$texto')";
        $ejecutado = mysqli_query($this->db, $sql);

        if ($ejecutado) {
            $sql_alumno = "SELECT t.usuario_id, l.curso_id, l.id as leccion_id 
                           FROM tareas_entregadas t
                           JOIN lecciones l ON t.leccion_id = l.id 
                           WHERE t.id = '$tarea'";
            $res = mysqli_fetch_assoc(mysqli_query($this->db, $sql_alumno));

            if ($res) {
                $notif = new NotificacionModel();
                // AQUÍ AGREGAMOS EL ANCLA AL FINAL: "#zona-comentarios-tarea"
                $notif->crearNotificacion($res['usuario_id'], "El instructor ha dejado un comentario en tu tarea.", $res['leccion_id'], $res['curso_id'], "#zona-comentarios-tarea");
            }
        }

        return $ejecutado;
    }

    public function obtenerComentariosPrivados($tarea_id)
    {
        $tarea = mysqli_real_escape_string($this->db, $tarea_id);
        $sql = "SELECT ct.*, u.nombre, u.rol FROM comentarios_tarea ct INNER JOIN usuarios u ON ct.usuario_id = u.id WHERE ct.tarea_id = '$tarea' ORDER BY ct.fecha ASC";
        return mysqli_query($this->db, $sql);
    }

public function obtenerTareaPorId($id)
    {
        $id = mysqli_real_escape_string($this->db, $id);
        $query = "SELECT * FROM tareas_entregadas WHERE id = '$id'";
        $resultado = mysqli_query($this->db, $query);
        
        return mysqli_fetch_assoc($resultado);
    }
}
