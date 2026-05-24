<?php
require_once __DIR__ . '/../Models/CursoModel.php';
require_once __DIR__ . '/../Models/LeccionModel.php';
require_once __DIR__ . '/../Models/TareaModel.php';
require_once __DIR__ . '/../Models/ProgresoModel.php';
require_once __DIR__ . '/../Models/ForoModel.php';

class EstudianteController
{

    public function __construct()
    {
        Seguridad::verificarAcceso('estudiante');
    }

    public function index()
    {
        $model = new CursoModel();
        $res_cursos = $model->obtenerCatalogo();

        require_once __DIR__ . '/../Views/Layout/header.php';
        require_once __DIR__ . '/../Views/estudiante/catalogo.php';
        require_once __DIR__ . '/../Views/Layout/footer.php';
    }

    public function verCurso()
    {
        $modelCurso = new CursoModel();
        $modelLeccion = new LeccionModel();
        $modelProgreso = new ProgresoModel();
        $modelTarea = new TareaModel();
        $modelForo = new ForoModel();

        $id_curso = $_GET['id'];
        $curso = $modelCurso->obtenerCursoPorId($id_curso);

        $lecciones = $modelLeccion->obtenerLeccionesPorCurso($id_curso);
        $progreso_array = $modelProgreso->obtenerProgresoUsuario($_SESSION['usuario_id'], $id_curso);

        $leccion_actual = null;
        $mi_tarea = null;
        $completada = false;
        $archivos_leccion = null;
        $lista_comentarios = null;
        $comentarios_tarea = []; // Inicializamos el arreglo vacío por defecto

        if (isset($_GET['lec_id'])) {
            $leccion_actual = $modelLeccion->obtenerLeccionPorId($_GET['lec_id']);
            if ($leccion_actual) {
                $completada = in_array($leccion_actual['id'], $progreso_array);
                $archivos_leccion = $modelLeccion->obtenerArchivosLeccion($leccion_actual['id']);
                $lista_comentarios = $modelForo->obtenerPorLeccion($leccion_actual['id']);

                // LÓGICA DE TAREA Y COMENTARIOS PRIVADOS
                if ($leccion_actual['tiene_tarea']) {
                    $mi_tarea = $modelTarea->obtenerMiTarea($_SESSION['usuario_id'], $leccion_actual['id']);
                    if ($mi_tarea) {
                        $res_com = $modelTarea->obtenerComentariosPrivados($mi_tarea['id']);
                        while ($c = mysqli_fetch_assoc($res_com)) {
                            $comentarios_tarea[] = $c;
                        }
                    }
                }
            }
        }

        // ESTO ES LO QUE TE FALTABA AL FINAL: Cargar las vistas
        require_once __DIR__ . '/../Views/Layout/header.php';
        require_once __DIR__ . '/../Views/estudiante/aula_virtual.php';
        require_once __DIR__ . '/../Views/Layout/footer.php';
    }

    public function marcarCompletada()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $model = new ProgresoModel();
            $model->marcarCompletada($_SESSION['usuario_id'], $_POST['leccion_id']);
            header("Location: index.php?c=estudiante&a=verCurso&id=" . $_POST['curso_id'] . "&lec_id=" . $_POST['leccion_id']);
            exit();
        }
    }

    public function subirTarea()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $modelTarea = new TareaModel();
            $modelLeccion = new LeccionModel();

            // 1. Obtenemos los datos de la lección para saber la fecha límite
            $leccion = $modelLeccion->obtenerLeccionPorId($_POST['leccion_id']);
            $estatus = "A tiempo"; // Estatus por defecto

            // 2. Verificamos si existe fecha límite y comparamos
            if (!empty($leccion['fecha_limite'])) {
                $fecha_actual = new DateTime(); // Hora exacta en que sube el archivo
                $fecha_limite = new DateTime($leccion['fecha_limite']);

                if ($fecha_actual > $fecha_limite) {
                    $estatus = "Entregada con retraso";
                }
            }

            // 3. Guardamos la tarea pasando el estatus calculado
            $modelTarea->guardarTarea($_POST['leccion_id'], $_SESSION['usuario_id'], $_FILES['archivo_tarea'], $estatus);

            header("Location: index.php?c=estudiante&a=verCurso&id=" . $_POST['curso_id'] . "&lec_id=" . $_POST['leccion_id'] . "&msj=tarea_ok");
            exit();
        }
    }

    public function comentar()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $modelForo = new ForoModel();
            $modelForo->guardarComentario($_POST['leccion_id'], $_SESSION['usuario_id'], $_POST['comentario']);
            header("Location: index.php?c=estudiante&a=verCurso&id=" . $_POST['curso_id'] . "&lec_id=" . $_POST['leccion_id']);
            exit();
        }
    }

    public function miProgreso()
    {
        $model = new ProgresoModel();
        $cursos_activos = $model->obtenerCursosEnProgreso($_SESSION['usuario_id']);
        $timeline = $model->obtenerTimelineEstudiante($_SESSION['usuario_id']);

        require_once __DIR__ . '/../Views/Layout/header.php';
        require_once __DIR__ . '/../Views/estudiante/mi_progreso.php';
        require_once __DIR__ . '/../Views/Layout/footer.php';
    }

    public function certificado()
    {
        if (!isset($_GET['curso_id'])) {
            header("Location: index.php?c=estudiante&a=miProgreso");
            exit();
        }
        $modelCurso = new CursoModel();
        $curso = $modelCurso->obtenerCursoPorId($_GET['curso_id']);
        require_once __DIR__ . '/../Views/estudiante/certificado.php';
    }

    public function enviarFeedbackTarea()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $model = new TareaModel();
            $model->guardarComentarioPrivado($_POST['tarea_id'], $_SESSION['usuario_id'], $_POST['comentario']);
            header("Location: index.php?c=estudiante&a=verCurso&id=" . $_POST['curso_id'] . "&lec_id=" . $_POST['leccion_id']);
            exit();
        }
    }
}
