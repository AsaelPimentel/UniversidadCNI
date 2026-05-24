<?php
require_once __DIR__ . '/../Config/Conexion.php';
class ProgresoModel {
    private $db;

    public function __construct() {
        $this->db = ConexionDB::obtenerConexion();
    }

    public function marcarCompletada($usuario_id, $leccion_id) {
        $u = mysqli_real_escape_string($this->db, $usuario_id);
        $l = mysqli_real_escape_string($this->db, $leccion_id);

        $check = mysqli_query($this->db, "SELECT id FROM progreso_lecciones WHERE usuario_id = '$u' AND leccion_id = '$l'");
        if (mysqli_num_rows($check) == 0) {
            return mysqli_query($this->db, "INSERT INTO progreso_lecciones (usuario_id, leccion_id) VALUES ('$u', '$l')");
        }
        return true;
    }

    public function obtenerProgresoUsuario($usuario_id, $curso_id) {
        $u = mysqli_real_escape_string($this->db, $usuario_id);
        $c = mysqli_real_escape_string($this->db, $curso_id);
        
        $sql = "SELECT p.leccion_id FROM progreso_lecciones p INNER JOIN lecciones l ON p.leccion_id = l.id WHERE p.usuario_id = '$u' AND l.curso_id = '$c'";
        $res = mysqli_query($this->db, $sql);
        
        $completadas = [];
        while($row = mysqli_fetch_assoc($res)) { $completadas[] = $row['leccion_id']; }
        return $completadas;
    }

    public function obtenerCursosEnProgreso($usuario_id) {
        $u = mysqli_real_escape_string($this->db, $usuario_id);
        $sql = "SELECT c.id, c.titulo, c.imagen,
                (SELECT COUNT(*) FROM lecciones l WHERE l.curso_id = c.id) as total_lecciones,
                (SELECT COUNT(*) FROM progreso_lecciones pl JOIN lecciones l2 ON pl.leccion_id = l2.id WHERE l2.curso_id = c.id AND pl.usuario_id = $u) as completadas
               FROM cursos c
               WHERE EXISTS (SELECT 1 FROM progreso_lecciones pl2 JOIN lecciones l3 ON pl2.leccion_id = l3.id WHERE l3.curso_id = c.id AND pl2.usuario_id = $u)";
        return mysqli_query($this->db, $sql);
    }

    public function obtenerTimelineEstudiante($usuario_id) {
        $u = mysqli_real_escape_string($this->db, $usuario_id);
        $sql = "(SELECT 'leccion' as tipo, l.titulo as detalle, pl.fecha_completado as fecha 
                  FROM progreso_lecciones pl JOIN lecciones l ON pl.leccion_id = l.id WHERE pl.usuario_id = $u)
                  UNION
                  (SELECT 'tarea' as tipo, l.titulo as detalle, te.fecha_envio as fecha 
                  FROM tareas_entregadas te JOIN lecciones l ON te.leccion_id = l.id WHERE te.usuario_id = $u)
                  ORDER BY fecha DESC LIMIT 8";
        return mysqli_query($this->db, $sql);
    }

    public function obtenerKpiFinalizacion($id_curso, $total_lecciones) {
        $id = mysqli_real_escape_string($this->db, $id_curso);
        if($total_lecciones == 0) return 0;
        $sql = "SELECT AVG(progreso) as promedio FROM (SELECT (COUNT(pl.id) / $total_lecciones) * 100 as progreso FROM progreso_lecciones pl JOIN lecciones l ON pl.leccion_id = l.id WHERE l.curso_id = $id GROUP BY pl.usuario_id) as subquery";
        $res = mysqli_fetch_assoc(mysqli_query($this->db, $sql));
        return round($res['promedio'] ?? 0, 1);
    }

    public function obtenerKpiTareas($id_curso) {
        $id = mysqli_real_escape_string($this->db, $id_curso);
        $q_alumnos = mysqli_fetch_assoc(mysqli_query($this->db, "SELECT COUNT(DISTINCT usuario_id) as total FROM progreso_lecciones pl JOIN lecciones l ON pl.leccion_id = l.id WHERE l.curso_id = $id"));
        $total_alumnos = $q_alumnos['total'] > 0 ? $q_alumnos['total'] : 1;
        $q_tareas = mysqli_fetch_assoc(mysqli_query($this->db, "SELECT COUNT(*) as total FROM tareas_entregadas te JOIN lecciones l ON te.leccion_id = l.id WHERE l.curso_id = $id"));
        return round(($q_tareas['total'] / $total_alumnos) * 100, 1);
    }

    public function obtenerEmbudo($id_curso) {
        $id = mysqli_real_escape_string($this->db, $id_curso);
        return mysqli_query($this->db, "SELECT l.titulo, COUNT(pl.id) as completados FROM lecciones l LEFT JOIN progreso_lecciones pl ON l.id = pl.leccion_id WHERE l.curso_id = $id GROUP BY l.id ORDER BY l.id ASC");
    }

    public function obtenerZonasFriccion($id_curso) {
        $id = mysqli_real_escape_string($this->db, $id_curso);
        return mysqli_query($this->db, "SELECT l.id, l.titulo, COUNT(c.id) as dudas FROM lecciones l LEFT JOIN comentarios c ON l.id = c.leccion_id WHERE l.curso_id = $id GROUP BY l.id HAVING dudas > 0 ORDER BY dudas DESC");
    }
}
?>