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
}
