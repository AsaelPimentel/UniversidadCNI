<?php
require_once __DIR__ . '/../Models/CursoModel.php';
require_once __DIR__ . '/../Models/LeccionModel.php';
require_once __DIR__ . '/../Models/TareaModel.php';
require_once __DIR__ . '/../Models/ProgresoModel.php';
require_once __DIR__ . '/../Models/ForoModel.php';
require_once __DIR__ . '/../Models/UsuarioModel.php';

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
        $modelCurso = new CursoModel();       // <-- NUEVO: Para buscar los datos del curso
        $modelUsuario = new UsuarioModel();   // <-- NUEVO: Para buscar el correo del maestro

        // 1. Calcular estatus de entrega según la fecha límite
        $leccion = $modelLeccion->obtenerLeccionPorId($_POST['leccion_id']);
        $estatus = "A tiempo";

        if (!empty($leccion['fecha_limite'])) {
            $fecha_actual = new DateTime();
            $fecha_limite = new DateTime($leccion['fecha_limite']);
            if ($fecha_actual > $fecha_limite) {
                $estatus = "Entregada con retraso";
            }
        }

        // 2. Guardar la tarea físicamente y en la Base de Datos
        $modelTarea->guardarTarea($_POST['leccion_id'], $_SESSION['usuario_id'], $_FILES['archivo_tarea'], $estatus);

        // --- 3. LOGICA DINÁMICA DE OBTENCIÓN DE CORREOS DESDE LA BD ---
        // Obtener el ID del instructor a través del curso actual
        $curso = $modelCurso->obtenerCursoPorId($_POST['curso_id']);
        $instructor_id = $curso['instructor_id'];

        // Consultar los datos del instructor en la tabla usuarios
        $instructor = $modelUsuario->obtenerPorId($instructor_id);
        
        // Asignación de variables con datos reales de la Base de Datos y Sesión
        $instructor_email = $instructor['email']; // Correo del maestro guardado en la BD
        $instructor_nombre = $instructor['nombre'];
        $alumno_email = $_SESSION['email'];       // Correo del alumno desde la sesión activa
        $alumno_nombre = $_SESSION['nombre'];
        $materia_nombre = $curso['titulo'];
        $leccion_nombre = $leccion['titulo'];

        // --- 4. CONFIGURACIÓN Y ENVÍO CON PHPMAILER ---
        require_once __DIR__ . '/../Config/MailConfig.php'; 
        require_once __DIR__ . '/../Assets/PHPMailer/src/Exception.php';
        require_once __DIR__ . '/../Assets/PHPMailer/src/PHPMailer.php';
        require_once __DIR__ . '/../Assets/PHPMailer/src/SMTP.php';

        $mail = new \PHPMailer\PHPMailer\PHPMailer(true);

        try {
            // Configuración del servidor (La cuenta de la Universidad CNI)
            $mail->isSMTP();
            $mail->Host       = MailConfig::$host;
            $mail->SMTPAuth   = MailConfig::$smtp_auth;
            $mail->Username   = MailConfig::$username; // El correo del sistema
            $mail->Password   = MailConfig::$password; // La clave del sistema
            $mail->SMTPSecure = (MailConfig::$secure === 'ssl') ? \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS : \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = MailConfig::$port;
            $mail->CharSet    = 'UTF-8';

            // 1. EL REMITENTE SIEMPRE ES EL SISTEMA
            $mail->setFrom(MailConfig::$username, "Plataforma Universidad CNI");

            // --------------------------------------------------------
            // DESTINATARIO: El Instructor
            // --------------------------------------------------------
            $mail->addAddress($instructor_email, $instructor_nombre);
            
            // 2. EL TRUCO: Si el maestro le da a "Responder", el correo se dirigirá al alumno
            $mail->addReplyTo($alumno_email, $alumno_nombre);

            $mail->isHTML(true);
            $mail->Subject = "Nueva tarea recibida - $materia_nombre (Enviado por: $alumno_nombre)";
            
            // 3. En el cuerpo del correo aclaramos de quién viene la tarea
            $mail->Body = "
                <div style='font-family: Arial, sans-serif; color: #333;'>
                    <h3 style='color: #065b3e;'>Nueva Evidencia por Revisar</h3>
                    <p>Estimado instructor <strong>$instructor_nombre</strong>,</p>
                    <p>El sistema te notifica que el alumno <strong>$alumno_nombre</strong> (<em>$alumno_email</em>) ha entregado su evidencia.</p>
                    <ul>
                        <li><strong>Curso:</strong> $materia_nombre</li>
                        <li><strong>Lección:</strong> $leccion_nombre</li>
                        <li><strong>Estatus del envío:</strong> $estatus</li>
                    </ul>
                    <p>Por favor, ingresa al panel de administración para calificar la entrega.</p>
                    <hr>
                    <p style='font-size: 12px; color: #777;'>
                        <em>Nota: Puedes responder directamente a este correo; tu respuesta será enviada automáticamente al alumno.</em>
                    </p>
                </div>
            ";
            
            $mail->send();

        } catch (Exception $e) {
            // Captura silenciosa de errores 
        }

        // 5. Redireccionar al aula virtual con mensaje de éxito
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

    public function miPerfil()
    {
        $modelUsuario = new UsuarioModel();
        $user = $modelUsuario->obtenerPorId($_SESSION['usuario_id']);

        require_once __DIR__ . '/../Views/Layout/header.php';
        require_once __DIR__ . '/../Views/estudiante/perfil.php';
        require_once __DIR__ . '/../Views/Layout/footer.php';
    }

public function actualizarContrasena()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $modelUsuario = new UsuarioModel();
            $resultado = $modelUsuario->actualizarContrasena($_SESSION['usuario_id'], $_POST['password']);
            
            if ($resultado) {
                // Redirecciona al catálogo principal con el parámetro de éxito
                header("Location: index.php?c=estudiante&a=index&msj=pass_ok");
            } else {
                $_SESSION['alerta'] = ['tipo' => 'danger', 'mensaje' => 'Hubo un error al intentar actualizar tu contraseña.'];
                header("Location: index.php?c=estudiante&a=miPerfil");
            }
            exit();
        }
    }
}
