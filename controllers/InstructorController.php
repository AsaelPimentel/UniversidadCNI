<?php
require_once __DIR__ . '/../Models/CursoModel.php';
require_once __DIR__ . '/../Models/LeccionModel.php';
require_once __DIR__ . '/../Models/TareaModel.php';
require_once __DIR__ . '/../Models/ProgresoModel.php';
require_once __DIR__ . '/../Models/ForoModel.php';

class InstructorController
{

    public function __construct()
    {
        Seguridad::verificarAcceso('instructor');
    }

    public function index()
    {
        $model = new CursoModel();
        $cursos = $model->obtenerPorInstructor($_SESSION['usuario_id']);

        require_once __DIR__ . '/../Views/Layout/header.php';
        require_once __DIR__ . '/../Views/instructor/index.php';
        require_once __DIR__ . '/../Views/Layout/footer.php';
    }

    public function crearCurso()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $model = new CursoModel();
            if ($model->guardar($_POST, $_FILES, $_SESSION['usuario_id'])) {
                header("Location: index.php?c=instructor&a=index");
                exit();
            }
        }
        require_once __DIR__ . '/../Views/Layout/header.php';
        require_once __DIR__ . '/../Views/instructor/crear_curso.php';
        require_once __DIR__ . '/../Views/Layout/footer.php';
    }

    public function nuevaLeccion()
    {
        $modelLeccion = new LeccionModel();
        $curso_id = $_GET['curso_id'];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if ($modelLeccion->guardarLeccion($_POST, $_FILES)) {
                header("Location: index.php?c=instructor&a=nuevaLeccion&curso_id=" . $_POST['curso_id'] . "&msj=ok");
                exit();
            }
        }

        $lecciones = $modelLeccion->obtenerLeccionesPorCurso($curso_id);
        require_once __DIR__ . '/../Views/Layout/header.php';
        require_once __DIR__ . '/../Views/instructor/lecciones.php';
        require_once __DIR__ . '/../Views/Layout/footer.php';
    }

    public function editarLeccion()
    {
        $model = new LeccionModel();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $model->actualizarLeccion($_POST, $_FILES);
            header("Location: index.php?c=instructor&a=nuevaLeccion&curso_id=" . $_POST['curso_id'] . "&msj=ok");
            exit();
        }

        $curso_id = $_GET['curso_id'];
        $leccion = $model->obtenerLeccionPorId($_GET['id']);

        require_once __DIR__ . '/../Views/Layout/header.php';
        require_once __DIR__ . '/../Views/instructor/editar_leccion.php';
        require_once __DIR__ . '/../Views/Layout/footer.php';
    }

    public function eliminarLeccion()
    {
        if (isset($_GET['id']) && isset($_GET['curso_id'])) {
            $model = new LeccionModel();
            $model->eliminarLeccion($_GET['id']);
            header("Location: index.php?c=instructor&a=nuevaLeccion&curso_id=" . $_GET['curso_id']);
            exit();
        }
    }

    public function verTareas()
    {
        $model = new TareaModel();
        $modelCurso = new CursoModel();

        $curso_seleccionado = isset($_GET['curso_filter']) ? $_GET['curso_filter'] : 'todos';
        $query_cursos = $modelCurso->obtenerPorInstructor($_SESSION['usuario_id']);
        $res_tareas = $model->obtenerEntregasPorInstructor($_SESSION['usuario_id'], $curso_seleccionado);

        // EXTRAEMOS LAS TAREAS Y SUS COMENTARIOS PRIVADOS
        $tareas = [];
        while ($t = mysqli_fetch_assoc($res_tareas)) {
            $t['comentarios'] = [];
            $res_com = $model->obtenerComentariosPrivados($t['tarea_id']);
            while ($c = mysqli_fetch_assoc($res_com)) {
                $t['comentarios'][] = $c;
            }
            $tareas[] = $t;
        }

        require_once __DIR__ . '/../Views/Layout/header.php';
        require_once __DIR__ . '/../Views/instructor/tareas.php';
        require_once __DIR__ . '/../Views/Layout/footer.php';
    }

    // NUEVA FUNCION PARA GUARDAR EL COMENTARIO
    public function enviarFeedback()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $model = new TareaModel();
            $model->guardarComentarioPrivado($_POST['tarea_id'], $_SESSION['usuario_id'], $_POST['comentario']);
            header("Location: index.php?c=instructor&a=verTareas&curso_filter=" . ($_POST['curso_filter'] ?? 'todos') . "&msj=feedback_ok");
            exit();
        }
    }

    public function foro()
    {
        $model = new ForoModel();
        $comentarios = $model->obtenerDudasPorInstructor($_SESSION['usuario_id']);

        require_once __DIR__ . '/../Views/Layout/header.php';
        require_once __DIR__ . '/../Views/instructor/foro.php';
        require_once __DIR__ . '/../Views/Layout/footer.php';
    }

    public function responderDuda()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $model = new ForoModel();
            $model->guardarComentario($_POST['leccion_id'], $_SESSION['usuario_id'], $_POST['respuesta']);
            header("Location: index.php?c=instructor&a=foro&msj=respondido");
            exit();
        }
    }

    public function rendimiento()
    {
        $modelCurso = new CursoModel();
        $modelProgreso = new ProgresoModel();

        $cursos_res = $modelCurso->obtenerPorInstructor($_SESSION['usuario_id']);
        $cursos = [];
        while ($c = mysqli_fetch_assoc($cursos_res)) {
            $cursos[] = $c;
        }

        $id_curso_sel = isset($_GET['curso_id']) ? (int)$_GET['curso_id'] : ($cursos[0]['id'] ?? 0);

        if ($id_curso_sel > 0) {
            $curso_actual = $modelCurso->obtenerCursoPorId($id_curso_sel);
            $lecciones_res = $modelProgreso->obtenerEmbudo($id_curso_sel);

            $labels_funnel = [];
            $data_funnel = [];
            while ($f = mysqli_fetch_assoc($lecciones_res)) {
                $labels_funnel[] = $f['titulo'];
                $data_funnel[] = $f['completados'];
            }
            $res_friccion = $modelProgreso->obtenerZonasFriccion($id_curso_sel);
        }

        require_once __DIR__ . '/../Views/Layout/header.php';
        require_once __DIR__ . '/../Views/instructor/rendimiento.php';
        require_once __DIR__ . '/../Views/Layout/footer.php';
    }
}
