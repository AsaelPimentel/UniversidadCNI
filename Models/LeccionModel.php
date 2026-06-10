<?php
require_once __DIR__ . '/../Config/Conexion.php';
class LeccionModel
{
    private $db;

    public function __construct()
    {
        $this->db = ConexionDB::obtenerConexion();
    }

    private function procesarMultiplesArchivos($id_leccion, $archivos)
    {
        if (!empty($archivos['pdf_files']['name'][0])) {
            $total = count($archivos['pdf_files']['name']);
            if (!is_dir('assets/Pdfs/')) {
                mkdir('assets/Pdfs/', 0777, true);
            }

            for ($i = 0; $i < $total; $i++) {
                $nombre_original = mysqli_real_escape_string($this->db, $archivos['pdf_files']['name'][$i]);
                $nombre_limpio = time() . "_" . $i . "_" . basename($nombre_original);
                $ruta_fisica = "assets/Pdfs/" . $nombre_limpio;

                if (move_uploaded_file($archivos['pdf_files']['tmp_name'][$i], $ruta_fisica)) {
                    $sql = "INSERT INTO archivos_leccion (leccion_id, nombre_original, ruta_archivo) VALUES ('$id_leccion', '$nombre_original', '$ruta_fisica')";
                    mysqli_query($this->db, $sql);
                }
            }
        }
    }

    public function guardarLeccion($datos, $archivos)
    {
        $curso_id = mysqli_real_escape_string($this->db, $datos['curso_id']);
        $titulo = mysqli_real_escape_string($this->db, $datos['titulo_leccion']);
        $url = $datos['url_video'];
        $tiene_tarea = isset($datos['tiene_tarea']) ? 1 : 0;

        // Capturar fecha límite si la tiene, de lo contrario guardar como NULL
        $fecha_limite = !empty($datos['fecha_limite']) ? "'" . mysqli_real_escape_string($this->db, $datos['fecha_limite']) . "'" : "NULL";

        // NUEVO: Capturar instrucciones y enlace externo, si están vacíos se guardan como NULL
        $instrucciones = !empty($datos['instrucciones']) ? "'" . mysqli_real_escape_string($this->db, $datos['instrucciones']) . "'" : "NULL";


        // Lógica original de extracción de ID de YouTube
        if (preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $url, $match)) {
            $video_id = $match[1];
        } else {
            $video_id = $url;
        }

        // NUEVO: Agregamos instrucciones y enlace_externo al INSERT
        $sql = "INSERT INTO lecciones (curso_id, titulo, instrucciones, contenido_url, tiene_tarea, fecha_limite) 
                VALUES ('$curso_id', '$titulo', $instrucciones, '$video_id', '$tiene_tarea', $fecha_limite)";

        if (mysqli_query($this->db, $sql)) {
            // procesarMultiplesArchivos asume que existe esta función en tu modelo
            if (method_exists($this, 'procesarMultiplesArchivos')) {
                $this->procesarMultiplesArchivos(mysqli_insert_id($this->db), $archivos);
            }
            return true;
        }
        return false;
    }

    public function actualizarLeccion($datos, $archivos)
    {
        $id = mysqli_real_escape_string($this->db, $datos['id']);
        $titulo = mysqli_real_escape_string($this->db, $datos['titulo']);
        $url = mysqli_real_escape_string($this->db, $datos['url']);
        $tiene_tarea = isset($datos['tiene_tarea']) ? 1 : 0;

        $fecha_limite = !empty($datos['fecha_limite']) ? "'" . mysqli_real_escape_string($this->db, $datos['fecha_limite']) . "'" : "NULL";

        // NUEVO: Capturar instrucciones y enlace externo
        $instrucciones = !empty($datos['instrucciones']) ? "'" . mysqli_real_escape_string($this->db, $datos['instrucciones']) . "'" : "NULL";
        
        // Lógica original de extracción de ID de YouTube para la actualización
        if (preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $url, $match)) {
            $video_id = $match[1];
        } else {
            $video_id = $url;
        }

        // NUEVO: Agregamos instrucciones y enlace_externo al UPDATE
        $sql = "UPDATE lecciones SET 
                titulo = '$titulo', 
                instrucciones = $instrucciones,
                contenido_url = '$video_id', 
                tiene_tarea = '$tiene_tarea', 
                fecha_limite = $fecha_limite 
                WHERE id = '$id'";

        if (mysqli_query($this->db, $sql)) {
            if (method_exists($this, 'procesarMultiplesArchivos')) {
                $this->procesarMultiplesArchivos($id, $archivos);
            }
            return true;
        }
        return false;
    }

    public function eliminarLeccion($id_leccion)
    {
        $id_leccion = mysqli_real_escape_string($this->db, $id_leccion);
        $archivos = mysqli_query($this->db, "SELECT ruta_archivo FROM archivos_leccion WHERE leccion_id = '$id_leccion'");
        while ($row = mysqli_fetch_assoc($archivos)) {
            if (file_exists($row['ruta_archivo'])) unlink($row['ruta_archivo']);
        }
        return mysqli_query($this->db, "DELETE FROM lecciones WHERE id = '$id_leccion'");
    }

    public function obtenerLeccionesPorCurso($curso_id)
    {
        $id = mysqli_real_escape_string($this->db, $curso_id);
        return mysqli_query($this->db, "SELECT * FROM lecciones WHERE curso_id = '$id' ORDER BY id ASC");
    }

    public function obtenerLeccionPorId($id)
    {
        $id = mysqli_real_escape_string($this->db, $id);
        return mysqli_fetch_assoc(mysqli_query($this->db, "SELECT * FROM lecciones WHERE id = '$id'"));
    }

    public function obtenerArchivosLeccion($leccion_id)
    {
        $id = mysqli_real_escape_string($this->db, $leccion_id);
        return mysqli_query($this->db, "SELECT * FROM archivos_leccion WHERE leccion_id = '$id'");
    }
}
