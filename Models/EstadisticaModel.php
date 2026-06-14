<?php
require_once __DIR__ . '/../Config/Conexion.php';

class EstadisticaModel
{
    private $db;

    public function __construct()
    {
        $this->db = ConexionDB::obtenerConexion();
    }

    public function getTotalUsuarios()
    {
        $res = mysqli_query($this->db, "SELECT COUNT(*) as total FROM usuarios");
        return mysqli_fetch_assoc($res)['total'];
    }

    public function getTotalCursos()
    {
        $res = mysqli_query($this->db, "SELECT COUNT(*) as total FROM cursos");
        return mysqli_fetch_assoc($res)['total'];
    }

    // Calcula de forma dinámica cuántos alumnos han completado el 100% de un curso
    public function getTotalCertificados()
    {
        $sql = "SELECT COUNT(*) as total FROM (
                    SELECT p.usuario_id, l.curso_id, COUNT(p.id) as completadas, 
                    (SELECT COUNT(id) FROM lecciones WHERE curso_id = l.curso_id) as totales 
                    FROM progreso_lecciones p 
                    JOIN lecciones l ON p.leccion_id = l.id 
                    GROUP BY p.usuario_id, l.curso_id 
                    HAVING completadas = totales AND totales > 0
                ) as graduados";
        $res = mysqli_query($this->db, $sql);
        return mysqli_fetch_assoc($res)['total'];
    }

    // Obtiene los datos para dibujar la gráfica de pastel (Doughnut Chart)
    public function getUsuariosPorRol()
    {
        $sql = "SELECT rol, COUNT(*) as cantidad FROM usuarios GROUP BY rol";
        return mysqli_query($this->db, $sql);
    }

    public function getInfoSistema()
    {
        // Obtiene la versión del servidor de base de datos
        $version = mysqli_get_server_info($this->db);
        // Obtiene el tamaño aproximado de la base de datos en MB
        $sql = "SELECT table_schema AS 'db', ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS 'size' 
                FROM information_schema.TABLES WHERE table_schema = 'universidadcni'";
        $res = mysqli_fetch_assoc(mysqli_query($this->db, $sql));

        return [
            'version' => $version,
            'size' => $res['size'] ?? 0
        ];
    }
    // Obtener los usuarios registrados agrupados por el formato y límite del filtro
    public function getNuevosUsuariosPorRango($formato_fecha, $limite) {
        $sql = "
            SELECT * FROM (
                SELECT DATE_FORMAT(fecha_registro, $formato_fecha) as periodo, COUNT(*) as total 
                FROM usuarios 
                GROUP BY periodo 
                ORDER BY periodo DESC 
                LIMIT $limite
            ) as sub 
            ORDER BY periodo ASC";
        return mysqli_query($this->db, $sql);
    }

    // Construir la matriz de actividad diaria para las barras apiladas
    public function getActividadDiariaPorRango($limite) {
        $actividad_fechas = [];

        // 1. Lecciones completadas
        $sql_progreso = "SELECT DATE(fecha_completado) as fecha, COUNT(*) as total FROM progreso_lecciones GROUP BY fecha ORDER BY fecha DESC LIMIT $limite";
        $res_progreso = mysqli_query($this->db, $sql_progreso);
        while ($row = mysqli_fetch_assoc($res_progreso)) {
            $actividad_fechas[$row['fecha']] = ['lecciones' => $row['total'], 'tareas' => 0, 'comentarios' => 0];
        }

        // 2. Tareas entregadas
        $sql_tareas = "SELECT DATE(fecha_envio) as fecha, COUNT(*) as total FROM tareas_entregadas GROUP BY fecha ORDER BY fecha DESC LIMIT $limite";
        $res_tareas = mysqli_query($this->db, $sql_tareas);
        while ($row = mysqli_fetch_assoc($res_tareas)) {
            if (!isset($actividad_fechas[$row['fecha']])) $actividad_fechas[$row['fecha']] = ['lecciones' => 0, 'tareas' => 0, 'comentarios' => 0];
            $actividad_fechas[$row['fecha']]['tareas'] = $row['total'];
        }

        // 3. Comentarios
        $sql_comentarios = "SELECT DATE(fecha) as fecha, COUNT(*) as total FROM comentarios GROUP BY fecha ORDER BY fecha DESC LIMIT $limite";
        $res_comentarios = mysqli_query($this->db, $sql_comentarios);
        while ($row = mysqli_fetch_assoc($res_comentarios)) {
            if (!isset($actividad_fechas[$row['fecha']])) $actividad_fechas[$row['fecha']] = ['lecciones' => 0, 'tareas' => 0, 'comentarios' => 0];
            $actividad_fechas[$row['fecha']]['comentarios'] = $row['total'];
        }

        // Ordenar fechas cronológicamente de la más antigua a la más reciente
        ksort($actividad_fechas);
        return $actividad_fechas;
    }


}
