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

    public function editarCurso() {
    $model = new CursoModel();
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Debes crear el método actualizarCurso en tu CursoModel
        $model->actualizarCurso($_POST, $_FILES); 
        header("Location: index.php?c=instructor&a=index&msj=curso_actualizado");
        exit();
    }
    // Cargar la vista de edición del curso
    $curso = $model->obtenerCursoPorId($_GET['id']);
    require_once __DIR__ . '/../Views/Layout/header.php';
    require_once __DIR__ . '/../Views/instructor/editar_curso.php';
    require_once __DIR__ . '/../Views/Layout/footer.php';
}

    public function nuevaLeccion()
    {
        $modelLeccion = new LeccionModel();
        // Validación segura por si viene por POST en lugar de GET
        $curso_id = $_GET['curso_id'] ?? $_POST['curso_id'] ?? 0;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if ($modelLeccion->guardarLeccion($_POST, $_FILES)) {
                
                if (isset($_POST['tiene_tarea']) && $_POST['tiene_tarea'] == 1) {
                    
                    require_once __DIR__ . '/../Models/CursoModel.php';
                    $modelCurso = new CursoModel();
                    
                    $curso = $modelCurso->obtenerCursoPorId($_POST['curso_id']);
                    $materia_nombre = $curso['titulo'];
                    $leccion_titulo = $_POST['titulo']; 
                    
                    $db = ConexionDB::obtenerConexion();
                    $sql_alumnos = "SELECT email, nombre FROM usuarios WHERE rol = 'estudiante'"; 
                    $res_alumnos = mysqli_query($db, $sql_alumnos);

                    require_once __DIR__ . '/../Config/MailConfig.php'; 
                    require_once __DIR__ . '/../Assets/PHPMailer/src/Exception.php';
                    require_once __DIR__ . '/../Assets/PHPMailer/src/PHPMailer.php';
                    require_once __DIR__ . '/../Assets/PHPMailer/src/SMTP.php';

                    $mail = new \PHPMailer\PHPMailer\PHPMailer(true);

                    try {
                        $mail->isSMTP();
                        $mail->Host       = MailConfig::$host;
                        $mail->SMTPAuth   = MailConfig::$smtp_auth;
                        $mail->Username   = MailConfig::$username;
                        $mail->Password   = MailConfig::$password;
                        $mail->SMTPSecure = (MailConfig::$secure === 'ssl') ? \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS : \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
                        $mail->Port       = MailConfig::$port;
                        $mail->CharSet    = 'UTF-8';

                        $mail->setFrom(MailConfig::$username, 'Universidad CNI');
                        $mail->addAddress(MailConfig::$username, 'Universidad CNI - Notificaciones');

                        // Agregamos a todos los alumnos en Copia Oculta (BCC)
                        while ($alumno = mysqli_fetch_assoc($res_alumnos)) {
                            if (!empty(trim($alumno['email']))) {
                                $mail->addBCC($alumno['email'], $alumno['nombre']);
                            }
                        }

                        $mail->isHTML(true);
                        $mail->Subject = "Nueva Tarea Asignada - $materia_nombre";
                        
                        $mail->Body = "
                            <div style='font-family: Arial, sans-serif; color: #333; max-width: 600px; margin: 0 auto; border: 1px solid #ddd; border-radius: 8px; padding: 20px;'>
                                <div style='text-align: center; margin-bottom: 20px;'>
                                    <h2 style='color: #065b3e; margin: 0;'>¡Nueva actividad disponible!</h2>
                                </div>
                                <p>Estimado estudiante,</p>
                                <p>Te notificamos que tu instructor ha publicado una nueva lección que requiere que subas una evidencia a la plataforma.</p>
                                
                                <div style='background-color: #f8f9fa; padding: 15px; border-left: 4px solid #e5a93b; border-radius: 4px; margin-bottom: 20px;'>
                                    <p style='margin: 0 0 10px 0;'><strong>Curso:</strong> $materia_nombre</p>
                                    <p style='margin: 0;'><strong>Lección / Tema:</strong> $leccion_titulo</p>
                                </div>
                                
                                <p>Por favor, ingresa a tu Aula Virtual para revisar las instrucciones detalladas del instructor, descargar los materiales adjuntos si los hay, y subir tu archivo antes de que venza la fecha límite establecida.</p>
                                
                                <hr style='border: none; border-top: 1px solid #eee; margin: 25px 0;'>
                                <p style='font-size: 11px; color: #999; text-align: center; margin: 0;'>
                                    <em>Este es un correo automatizado generado por el Aula Virtual. Por favor no respondas a este mensaje.</em>
                                </p>
                            </div>
                        ";
                        
                        $mail->send();
                    } catch (Exception $e) {
                        // Captura silenciosa para que no interrumpa al instructor
                    }
                }
                // -------------------------------------------------------------------

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
            require_once __DIR__ . '/../Models/TareaModel.php';
            $model = new TareaModel();
            
            // 1. Si el maestro escribió un comentario, lo guardamos y notificamos
            if (!empty(trim($_POST['comentario']))) {
                $model->guardarComentarioPrivado($_POST['tarea_id'], $_SESSION['usuario_id'], $_POST['comentario']);
            }
            
            // 2. Si el maestro marcó la casilla de "Aprobar evidencia"
            if (isset($_POST['marcar_completada']) && $_POST['marcar_completada'] == '1') {
                require_once __DIR__ . '/../Models/ProgresoModel.php';
                $progresoModel = new ProgresoModel();
                
                // Obtenemos los datos de la tarea para saber a qué alumno y lección corresponde
                $tarea = $model->obtenerTareaPorId($_POST['tarea_id']);
                if ($tarea) {
                    // Marcamos la lección como completada para el estudiante
                    $progresoModel->marcarCompletada($tarea['usuario_id'], $tarea['leccion_id']);
                }
            }

            // Redirigimos de vuelta a las tareas
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
