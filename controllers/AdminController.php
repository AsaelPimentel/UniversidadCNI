<?php
require_once __DIR__ . '/../Models/UsuarioModel.php';

class AdminController
{

    public function __construct()
    {
        Seguridad::verificarAcceso('admin');
    }

    public function index()
    {
        $model = new UsuarioModel();

        $buscar = isset($_GET['buscar']) ? $_GET['buscar'] : '';
        $limite = 5;
        $pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
        $offset = ($pagina - 1) * $limite;

        $total_usuarios = $model->contarTodos();
        $total_filtro = $model->contarTodos($buscar);
        $total_paginas = ceil($total_filtro / $limite);

        $usuarios = $model->obtenerTodos($buscar, $offset, $limite);

        require_once __DIR__ . '/../Views/layout/header.php';
        require_once __DIR__ . '/../Views/admin/index.php';
        require_once __DIR__ . '/../Views/layout/footer.php';
    }

    public function crear()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $model = new UsuarioModel();
            $resultado = $model->crear($_POST);

            if ($resultado === "ok") {
                $_SESSION['alerta'] = ['tipo' => 'success', 'mensaje' => 'Usuario registrado con éxito.'];
            } elseif ($resultado === "duplicado") {
                $_SESSION['alerta'] = ['tipo' => 'warning', 'mensaje' => 'Ese correo ya está registrado en el sistema.'];
            } else {
                $_SESSION['alerta'] = ['tipo' => 'danger', 'mensaje' => 'Error al registrar en la base de datos.'];
            }
        }
        header("Location: index.php?c=admin&a=index");
        exit();
    }

public function editar()
    {
        $model = new UsuarioModel();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // El modelo ahora recibirá el campo 'password' (si fue enviado)
            // dentro del arreglo $_POST automáticamente.
            $resultado = $model->actualizar($_POST);
            
            if ($resultado === "ok") {
                $_SESSION['alerta'] = ['tipo' => 'success', 'mensaje' => '¡Usuario actualizado correctamente!'];
                header("Location: index.php?c=admin&a=index");
            } else if ($resultado === "duplicado") {
                $_SESSION['alerta'] = ['tipo' => 'warning', 'mensaje' => 'El correo ya está registrado en otro usuario.'];
                header("Location: index.php?c=admin&a=editar&id=" . $_POST['usuario_id']);
            } else {
                $_SESSION['alerta'] = ['tipo' => 'danger', 'mensaje' => 'Error al actualizar el usuario.'];
                header("Location: index.php?c=admin&a=editar&id=" . $_POST['usuario_id']);
            }
            exit();
        }

        // Si es un GET, cargamos la vista
        $user = $model->obtenerPorId($_GET['id']);
        if (!$user) {
            header("Location: index.php?c=admin&a=index");
            exit();
        }

        require_once __DIR__ . '/../Views/layout/header.php';
        require_once __DIR__ . '/../Views/admin/editar_usuario.php';
        require_once __DIR__ . '/../Views/layout/footer.php';
    }

    public function borrar()
    {
        if (isset($_GET['id'])) {
            $model = new UsuarioModel();
            $model->eliminar($_GET['id']);
            $_SESSION['alerta'] = ['tipo' => 'success', 'mensaje' => '¡Usuario eliminado permanentemente!'];
        }
        header("Location: index.php?c=admin&a=index");
        exit();
    }

    public function estadisticas()
    {
        require_once __DIR__ . '/../Models/EstadisticaModel.php';
        $model = new EstadisticaModel();

        // 1. KPIs Globales
        $total_usuarios = $model->getTotalUsuarios();
        $total_cursos = $model->getTotalCursos();
        $total_certificados = $model->getTotalCertificados();
        $info_sistema = $model->getInfoSistema();

        // 2. Gráfica Doughnut (Roles)
        $res_roles = $model->getUsuariosPorRol();
        $labels_roles = [];
        $data_roles = [];
        while ($row = mysqli_fetch_assoc($res_roles)) {
            $labels_roles[] = ucfirst($row['rol']);
            $data_roles[] = $row['cantidad'];
        }

        // ==========================================
        // 3. LÓGICA DE FILTRO DE FECHAS (Para los nuevos gráficos)
        // ==========================================
        $rango = isset($_GET['rango']) ? $_GET['rango'] : '6m';

        if ($rango === '7d') {
            $formato_fecha = "'%Y-%m-%d'"; // Agrupar por DÍA
            $limite_linea = 7;
            $limite_barras = 7;
        } elseif ($rango === '1m') {
            $formato_fecha = "'%Y-%m-%d'"; // Agrupar por DÍA
            $limite_linea = 30;
            $limite_barras = 15; // Limitado visualmente
        } elseif ($rango === '1y') {
            $formato_fecha = "'%Y-%m'";    // Agrupar por MES
            $limite_linea = 12;
            $limite_barras = 30;
        } else {
            // Por defecto: 6 Meses ('6m')
            $formato_fecha = "'%Y-%m'";    // Agrupar por MES
            $limite_linea = 6;
            $limite_barras = 14;
        }

        // Obtener datos para la gráfica de línea (Nuevos Usuarios)
        $res_usu_mes = $model->getNuevosUsuariosPorRango($formato_fecha, $limite_linea);
        $labels_meses = [];
        $data_usuarios = [];
        while ($row = mysqli_fetch_assoc($res_usu_mes)) {
            $labels_meses[] = $row['periodo'];
            $data_usuarios[] = $row['total'];
        }

        // Obtener datos para la gráfica de barras apiladas (Actividad)
        $actividad_fechas = $model->getActividadDiariaPorRango($limite_barras);
        
        $labels_actividad = array_keys($actividad_fechas);
        $data_lec = [];
        $data_tar = [];
        $data_com = [];
        foreach ($actividad_fechas as $fecha => $datos) {
            $data_lec[] = $datos['lecciones'];
            $data_tar[] = $datos['tareas'];
            $data_com[] = $datos['comentarios'];
        }

        // Cargamos las vistas inyectando las variables
        require_once __DIR__ . '/../Views/Layout/header.php';
        require_once __DIR__ . '/../Views/admin/estadisticas.php';
        require_once __DIR__ . '/../Views/Layout/footer.php';
    }

public function generarReporteUsuarios() {
    // 1. Incluir la librería FPDF
    require_once __DIR__ . '/../Assets/fpdf/fpdf.php';
    require_once __DIR__ . '/../Models/UsuarioModel.php';

    // 2. Obtener datos
    $model = new UsuarioModel();
    $usuarios = $model->obtenerTodos(); // Asegúrate de tener este método en tu UsuarioModel

    // 3. Configurar PDF
    $pdf = new FPDF();
    $pdf->AddPage();
    $pdf->SetFont('Arial', 'B', 16);
    
    // Título
    $pdf->Cell(0, 10, utf8_decode('Reporte General de Usuarios - Universidad CNI'), 0, 1, 'C');
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(0, 10, 'Fecha de generacion: ' . date('d/m/Y H:i'), 0, 1, 'C');
    $pdf->Ln(10);

    // Encabezados de tabla
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->SetFillColor(200, 220, 255);
    $pdf->Cell(60, 10, 'Nombre', 1, 0, 'C', true);
    $pdf->Cell(80, 10, 'Email', 1, 0, 'C', true);
    $pdf->Cell(40, 10, 'Rol', 1, 1, 'C', true);

    // Datos
    $pdf->SetFont('Arial', '', 12);
    while ($u = mysqli_fetch_assoc($usuarios)) {
        $pdf->Cell(60, 10, utf8_decode($u['nombre']), 1);
        $pdf->Cell(80, 10, $u['email'], 1);
        $pdf->Cell(40, 10, ucfirst($u['rol']), 1, 1);
    }

    // Salida
    $pdf->Output('I', 'Reporte_Usuarios_' . date('Y-m-d') . '.pdf');
    exit();
}

public function generarReporteEstadisticas() {
    // 1. Asegurar acceso de seguridad
    Seguridad::verificarAcceso('admin');

    // 2. Cargar librerías y conexión limpia
    require_once __DIR__ . '/../Assets/fpdf/fpdf.php';
    $db = ConexionDB::obtenerConexion();

    // 3. Capturar parámetros del formulario dinámico
    $tipo_reporte = $_GET['tipo_reporte'] ?? 'general';
    $modo_ambito = $_GET['modo_ambito'] ?? 'historico';
    $rango_filtro = $_GET['rango_filtro'] ?? '7d';

    // 4. Calcular el rango de fechas si se solicitó un reporte filtrado
    $texto_periodo = "Histórico General (Todo el tiempo)";
    $where_fecha_usuarios = "";
    $where_fecha_tareas = "";
    $where_fecha_progreso = "";

    if ($modo_ambito === 'filtrado') {
        $fecha_actual = new DateTime();
        $fecha_inicio = new DateTime();

        if ($rango_filtro === '7d') {
            $fecha_inicio->modify('-7 days');
            $texto_periodo = "Filtro: Últimos 7 Días (" . $fecha_inicio->format('d/m/Y') . " al " . $fecha_actual->format('d/m/Y') . ")";
        } elseif ($rango_filtro === '1m') {
            $fecha_inicio->modify('-1 month');
            $texto_periodo = "Filtro: Último Mes (" . $fecha_inicio->format('d/m/Y') . " al " . $fecha_actual->format('d/m/Y') . ")";
        } elseif ($rango_filtro === '6m') {
            $fecha_inicio->modify('-6 months');
            $texto_periodo = "Filtro: Últimos 6 Meses (" . $fecha_inicio->format('d/m/Y') . " al " . $fecha_actual->format('d/m/Y') . ")";
        } elseif ($rango_filtro === '1y') {
            $fecha_inicio->modify('-1 year');
            $texto_periodo = "Filtro: Último Año (" . $fecha_inicio->format('d/m/Y') . " al " . $fecha_actual->format('d/m/Y') . ")";
        }

        $sql_inicio = $fecha_inicio->format('Y-m-d 00:00:00');
        $sql_fin = $fecha_actual->format('Y-m-d 23:59:59');

        // Construcción de segmentos condicionales SQL
        $where_fecha_usuarios = " AND fecha_registro BETWEEN '$sql_inicio' AND '$sql_fin' ";
        $where_fecha_tareas = " WHERE fecha_envio BETWEEN '$sql_inicio' AND '$sql_fin' ";
        $where_fecha_progreso = " WHERE fecha_completado BETWEEN '$sql_inicio' AND '$sql_fin' ";
    }

    // 5. Inicializar la construcción del documento FPDF
    $pdf = new FPDF();
    $pdf->AddPage();
    $pdf->SetMargins(15, 15, 15);
    
    // Encabezado institucional elegante
    $pdf->SetFont('Arial', 'B', 14);
    $pdf->SetTextColor(6, 91, 62); // Verde UABC oficial
    $pdf->Cell(0, 8, utf8_decode('UNIVERSIDAD AUTÓNOMA DE BAJA CALIFORNIA'), 0, 1, 'C');
    $pdf->SetFont('Arial', 'B', 11);
    $pdf->SetTextColor(100, 100, 100);
    $pdf->Cell(0, 6, utf8_decode('Centro de Negocios e Incubadora FCA - UNIVERSIDAD CNI'), 0, 1, 'C');
    $pdf->SetFont('Arial', 'I', 9);
    $pdf->Cell(0, 6, 'Ámbito del Reporte: ' . utf8_decode($texto_periodo), 0, 1, 'C');
    $pdf->Line(15, 36, 195, 36);
    $pdf->Ln(8);

    // =========================================================================
    // CASO MULTI-OPCIÓN 1: REPORTE GENERAL (TRAE TODO EL DASHBOARD APILADO)
    // =========================================================================
    if ($tipo_reporte === 'general') {
        $pdf->SetFont('Arial', 'B', 13);
        $pdf->SetTextColor(30, 30, 30);
        $pdf->Cell(0, 10, utf8_decode('1. Resumen de Usuarios Registrados'), 0, 1, 'L');
        
        // Tabla de usuarios por rol en el periodo establecido
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->SetFillColor(230, 240, 235);
        $pdf->Cell(90, 8, 'Rol de Usuario', 1, 0, 'C', true);
        $pdf->Cell(90, 8, 'Cantidad Registrada', 1, 1, 'C', true);
        
        $pdf->SetFont('Arial', '', 10);
        $sql_roles = "SELECT rol, COUNT(*) as total FROM usuarios WHERE 1=1 $where_fecha_usuarios GROUP BY rol";
        $res_roles = mysqli_query($db, $sql_roles);
        $total_u = 0;
        while($r = mysqli_fetch_assoc($res_roles)) {
            $pdf->Cell(90, 8, '  ' . ucfirst($r['rol']), 1, 0, 'L');
            $pdf->Cell(90, 8, $r['total'], 1, 1, 'C');
            $total_u += $r['total'];
        }
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(90, 8, '  Total de Usuarios en Periodo', 1, 0, 'L', true);
        $pdf->Cell(90, 8, $total_u, 1, 1, 'C', true);
        
        $pdf->Ln(6);
        $pdf->SetFont('Arial', 'B', 13);
        $pdf->Cell(0, 10, utf8_decode('2. Actividad e Interacción en la Plataforma'), 0, 1, 'L');
        
        // Obtener contadores globales de interacciones
        $q_tareas = mysqli_fetch_assoc(mysqli_query($db, "SELECT COUNT(*) as total FROM tareas_entregadas $where_fecha_tareas"));
        $q_progreso = mysqli_fetch_assoc(mysqli_query($db, "SELECT COUNT(*) as total FROM progreso_lecciones $where_fecha_progreso"));
        $q_comen = mysqli_fetch_assoc(mysqli_query($db, "SELECT COUNT(*) as total FROM comentarios_tarea")); // Histórico de comentarios de feedback

        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(120, 8, utf8_decode('Métrica de Interacción'), 1, 0, 'C', true);
        $pdf->Cell(60, 8, 'Total Acciones', 1, 1, 'C', true);
        
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(120, 8, utf8_decode('  Lecciones marcadas como completadas por Alumnos'), 1, 0, 'L');
        $pdf->Cell(60, 8, $q_progreso['total'], 1, 1, 'C');
        $pdf->Cell(120, 8, utf8_decode('  Evidencias y Tareas subidas al sistema'), 1, 0, 'L');
        $pdf->Cell(60, 8, $q_tareas['total'], 1, 1, 'C');
        $pdf->Cell(120, 8, utf8_decode('  Comentarios privados de retroalimentación (Feedback)'), 1, 0, 'L');
        $pdf->Cell(60, 8, $q_comen['total'], 1, 1, 'C');
    } 
    // =========================================================================
    // CASO MULTI-OPCIÓN 2: REPORTE EXCLUSIVO DE NUEVOS USUARIOS (LISTA DETALLADA)
    // =========================================================================
    elseif ($tipo_reporte === 'nuevos_usuarios') {
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(0, 10, utf8_decode('Listado Detallado de Usuarios Registrados'), 0, 1, 'L');
        
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->SetFillColor(6, 91, 62);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->Cell(60, 8, 'Nombre Completo', 1, 0, 'C', true);
        $pdf->Cell(65, 8, 'Correo Institucional', 1, 0, 'C', true);
        $pdf->Cell(25, 8, 'Rol asignado', 1, 0, 'C', true);
        $pdf->Cell(30, 8, 'Fecha Registro', 1, 1, 'C', true);
        
        $pdf->SetFont('Arial', '', 9);
        $pdf->SetTextColor(0, 0, 0);
        
        $sql_u = "SELECT nombre, email, rol, fecha_registro FROM usuarios WHERE 1=1 $where_fecha_usuarios ORDER BY id DESC";
        $res_u = mysqli_query($db, $sql_u);
        
        if(mysqli_num_rows($res_u) > 0) {
            while($user = mysqli_fetch_assoc($res_u)) {
                $pdf->Cell(60, 8, utf8_decode(' ' . $user['nombre']), 1);
                $pdf->Cell(65, 8, ' ' . $user['email'], 1);
                $pdf->Cell(25, 8, ucfirst($user['rol']), 1, 0, 'C');
                $pdf->Cell(30, 8, date('d/m/Y', strtotime($user['fecha_registro'])), 1, 1, 'C');
            }
        } else {
            $pdf->Cell(180, 10, utf8_decode('No se encontraron registros de usuarios en el rango seleccionado.'), 1, 1, 'C');
        }
    } 
    // =========================================================================
    // CASO MULTI-OPCIÓN 3: REPORTE EXCLUSIVO DE ACTIVIDAD DE INTERACCIÓN
    // =========================================================================
    elseif ($tipo_reporte === 'actividad_interaccion') {
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(0, 10, utf8_decode('Métricas de Rendimiento y Carga Académica'), 0, 1, 'L');
        
        $q_tareas = mysqli_fetch_assoc(mysqli_query($db, "SELECT COUNT(*) as total FROM tareas_entregadas $where_fecha_tareas"));
        $q_progreso = mysqli_fetch_assoc(mysqli_query($db, "SELECT COUNT(*) as total FROM progreso_lecciones $where_fecha_progreso"));
        
        $pdf->SetFont('Arial', '', 11);
        $pdf->MultiCell(0, 7, utf8_decode("Durante el periodo seleccionado, los alumnos registraron un total de " . $q_progreso['total'] . " lecciones completadas académicamente en sus aulas virtuales, interactuando activamente con los contenidos multimedia.\n\nAsimismo, se recibieron " . $q_tareas['total'] . " entregas de tareas formales que se encuentran actualmente en la bandeja de revisión de los respectivos instructores corporativos."));
    }

    // Pie de página reglamentario con fecha de emisión del reporte
    $pdf->SetY(-25);
    $pdf->SetFont('Arial', 'I', 8);
    $pdf->SetTextColor(120, 120, 120);
    $pdf->Cell(0, 10, utf8_decode('Documento oficial generado de forma automática por el Pánel Administrativo de Universidad CNI. Emisión: ') . date('d/m/Y H:i A'), 0, 0, 'C');

    // Lanzar flujo al navegador en una pestaña nueva limpia
    $pdf->Output('I', 'Reporte_CNI_' . $tipo_reporte . '.pdf');
    exit();
}

}
