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
    // 1. Asegurar acceso de seguridad corporativo
    Seguridad::verificarAcceso('admin');

    require_once __DIR__ . '/../Assets/fpdf/fpdf.php';
    $db = ConexionDB::obtenerConexion();

    // 2. Capturar parámetros directamente del formulario
    $tipo_reporte = $_GET['tipo_reporte'] ?? 'general';
    $rango_filtro = $_GET['rango_filtro'] ?? 'historico'; // Ahora el filtro viene directamente del Select

    // 3. Inicializar variables de consulta
    $texto_periodo = "Histórico General (Todo el tiempo)";
    $where_fecha_usuarios = "";
    $where_fecha_tareas = "";
    $where_fecha_progreso = "";
    $where_fecha_cursos = ""; 

    // 4. APLICAR LÓGICA DE FECHAS SEGÚN LA OPCIÓN ELEGIDA (7d, 1m, 6m, 1y)
    if ($rango_filtro !== 'historico') {
        $fecha_actual = new DateTime();
        $fecha_inicio = new DateTime();

        if ($rango_filtro === '7d') {
            $fecha_inicio->modify('-7 days');
            $texto_periodo = "Filtro: Últimos 7 Días";
        } elseif ($rango_filtro === '1m') {
            $fecha_inicio->modify('-1 month');
            $texto_periodo = "Filtro: Último Mes";
        } elseif ($rango_filtro === '6m') {
            $fecha_inicio->modify('-6 months');
            $texto_periodo = "Filtro: Últimos 6 Meses";
        } elseif ($rango_filtro === '1y') {
            $fecha_inicio->modify('-1 year');
            $texto_periodo = "Filtro: Último Año";
        }

        // Agregamos las fechas al texto del encabezado del PDF
        $texto_periodo .= " (" . $fecha_inicio->format('d/m/Y') . " al " . $fecha_actual->format('d/m/Y') . ")";

        // Formateamos para MySQL
        $sql_inicio = $fecha_inicio->format('Y-m-d 00:00:00');
        $sql_fin = $fecha_actual->format('Y-m-d 23:59:59');

        // Construimos las sentencias condicionales
        $where_fecha_usuarios = " AND fecha_registro BETWEEN '$sql_inicio' AND '$sql_fin' ";
        $where_fecha_tareas   = " WHERE fecha_envio BETWEEN '$sql_inicio' AND '$sql_fin' ";
        $where_fecha_progreso = " WHERE fecha_completado BETWEEN '$sql_inicio' AND '$sql_fin' ";
        $where_fecha_cursos   = " WHERE fecha_creacion BETWEEN '$sql_inicio' AND '$sql_fin' ";
    }

    // 5. Inicia la creación del PDF
    $pdf = new FPDF();
    $pdf->AddPage();
    $pdf->SetMargins(15, 15, 15);
    
    // ENCABEZADO INSTITUCIONAL CON LOGOTIPOS
    $pdf->Image(__DIR__ . '/../Assets/Img/EscudoUABC.png', 15, 10, 16); 
    $pdf->Image(__DIR__ . '/../Assets/Img/Logo CNI.png', 174, 10, 21); 

    $pdf->SetFont('Arial', 'B', 13);
    $pdf->SetTextColor(6, 91, 62); // Verde UABC
    $pdf->Cell(0, 6, utf8_decode('UNIVERSIDAD AUTÓNOMA DE BAJA CALIFORNIA'), 0, 1, 'C');
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->SetTextColor(100, 100, 100);
    $pdf->Cell(0, 5, utf8_decode('Centro de Negocios e Incubadora FCA - UNIVERSIDAD CNI'), 0, 1, 'C');
    $pdf->SetFont('Arial', 'I', 9);
    $pdf->Cell(0, 5, 'Periodo: ' . utf8_decode($texto_periodo), 0, 1, 'C');
    $pdf->Line(15, 36, 195, 36);
    $pdf->Ln(10);

    // =========================================================================
    // CASO 1: REPORTE GENERAL (TARJETAS GRÁFICAS DE KPI)
    // =========================================================================
    if ($tipo_reporte === 'general') {
        
        $res_u = mysqli_query($db, "SELECT COUNT(*) as total FROM usuarios WHERE 1=1 $where_fecha_usuarios");
        $row_u = mysqli_fetch_assoc($res_u);
        $total_u_kpi = $row_u ? $row_u['total'] : '0';

        $res_c = mysqli_query($db, "SELECT COUNT(*) as total FROM cursos $where_fecha_cursos");
        $row_c = mysqli_fetch_assoc($res_c);
        $total_c_kpi = $row_c ? $row_c['total'] : '0';

        $where_cert = str_replace('fecha_completado', 'p.fecha_completado', $where_fecha_progreso);
        $sql_cert = "
            SELECT COUNT(*) as total FROM (
                SELECT p.usuario_id, c.id as curso_id, COUNT(DISTINCT p.leccion_id) as completadas 
                FROM progreso_lecciones p 
                INNER JOIN lecciones l ON p.leccion_id = l.id 
                INNER JOIN cursos c ON l.curso_id = c.id 
                $where_cert
                GROUP BY p.usuario_id, c.id 
            ) as t
            INNER JOIN (
                SELECT curso_id, COUNT(id) as requeridas FROM lecciones GROUP BY curso_id
            ) as req ON t.curso_id = req.curso_id
            WHERE t.completadas = req.requeridas
        ";
        $res_cert = mysqli_query($db, $sql_cert);
        $row_cert = $res_cert ? mysqli_fetch_assoc($res_cert) : null;
        $total_cert_kpi = $row_cert ? $row_cert['total'] : '0';

        $x_pos = $pdf->GetX(); 
        $y_pos = $pdf->GetY(); 
        
        // Tarjeta Azul: Usuarios
        $pdf->SetXY($x_pos, $y_pos);
        $pdf->SetFillColor(0, 123, 255);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(56, 8, utf8_decode('Usuarios Registrados'), 0, 2, 'C', true);
        $pdf->SetFont('Arial', 'B', 18);
        $pdf->Cell(56, 14, $total_u_kpi, 0, 0, 'C', true);

        // Tarjeta Verde: Cursos
        $pdf->SetXY($x_pos + 62, $y_pos); 
        $pdf->SetFillColor(40, 167, 69);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(56, 8, utf8_decode('Cursos Disponibles'), 0, 2, 'C', true);
        $pdf->SetFont('Arial', 'B', 18);
        $pdf->Cell(56, 14, $total_c_kpi, 0, 0, 'C', true);

        // Tarjeta Dorada: Certificados
        $pdf->SetXY($x_pos + 124, $y_pos); 
        $pdf->SetFillColor(229, 169, 59);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(56, 8, utf8_decode('Certificados Emitidos'), 0, 2, 'C', true);
        $pdf->SetFont('Arial', 'B', 18);
        $pdf->Cell(56, 14, $total_cert_kpi, 0, 0, 'C', true);

        $pdf->SetXY($x_pos, $y_pos + 28);
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->SetTextColor(30, 30, 30);
        $pdf->Cell(0, 8, utf8_decode('Distribución de Usuarios por Rol'), 0, 1, 'L');
        
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->SetFillColor(235, 243, 240);
        $pdf->Cell(90, 8, 'Rol de Usuario', 1, 0, 'C', true);
        $pdf->Cell(90, 8, 'Cantidad Registrada', 1, 1, 'C', true);
        
        $pdf->SetFont('Arial', '', 10);
        $res_roles = mysqli_query($db, "SELECT rol, COUNT(*) as total FROM usuarios WHERE 1=1 $where_fecha_usuarios GROUP BY rol");
        if($res_roles) {
            while($r = mysqli_fetch_assoc($res_roles)) {
                $pdf->Cell(90, 8, '  ' . ucfirst($r['rol']), 1, 0, 'L');
                $pdf->Cell(90, 8, $r['total'], 1, 1, 'C');
            }
        }

        $pdf->Ln(6);
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(0, 8, utf8_decode('Actividad de Interacción en la Plataforma'), 0, 1, 'L');
        $pdf->Ln(2);
        
        $res_tar = mysqli_query($db, "SELECT COUNT(*) as total FROM tareas_entregadas $where_fecha_tareas");
        $val_tareas = $res_tar ? (int)mysqli_fetch_assoc($res_tar)['total'] : 0;

        $res_prog = mysqli_query($db, "SELECT COUNT(*) as total FROM progreso_lecciones $where_fecha_progreso");
        $val_progreso = $res_prog ? (int)mysqli_fetch_assoc($res_prog)['total'] : 0;

        $res_com = mysqli_query($db, "SELECT COUNT(*) as total FROM comentarios_tarea");
        $val_comen = $res_com ? (int)mysqli_fetch_assoc($res_com)['total'] : 0;

        $max_val = max($val_progreso, $val_tareas, $val_comen);
        if ($max_val == 0) $max_val = 1; 
        $ancho_maximo_barra = 90; 

        $pdf->SetFont('Arial', 'B', 9);
        
        $pdf->SetTextColor(80, 80, 80);
        $pdf->Cell(55, 8, utf8_decode('Lecciones Completadas'), 0, 0, 'R');
        $pdf->Cell(5, 8, '', 0, 0); 
        $pdf->SetFillColor(6, 91, 62); 
        if (($w = ($val_progreso / $max_val) * $ancho_maximo_barra) > 0) $pdf->Cell($w, 8, '', 0, 0, 'L', true);
        $pdf->SetTextColor(6, 91, 62);
        $pdf->Cell(20, 8, '  ' . $val_progreso, 0, 1, 'L');
        $pdf->Ln(2);

        $pdf->SetTextColor(80, 80, 80);
        $pdf->Cell(55, 8, utf8_decode('Tareas / Evidencias'), 0, 0, 'R');
        $pdf->Cell(5, 8, '', 0, 0); 
        $pdf->SetFillColor(229, 169, 59); 
        if (($w = ($val_tareas / $max_val) * $ancho_maximo_barra) > 0) $pdf->Cell($w, 8, '', 0, 0, 'L', true);
        $pdf->SetTextColor(229, 169, 59);
        $pdf->Cell(20, 8, '  ' . $val_tareas, 0, 1, 'L');
        $pdf->Ln(2);

        $pdf->SetTextColor(80, 80, 80);
        $pdf->Cell(55, 8, utf8_decode('Comentarios Privados'), 0, 0, 'R');
        $pdf->Cell(5, 8, '', 0, 0); 
        $pdf->SetFillColor(23, 162, 184); 
        if (($w = ($val_comen / $max_val) * $ancho_maximo_barra) > 0) $pdf->Cell($w, 8, '', 0, 0, 'L', true);
        $pdf->SetTextColor(23, 162, 184);
        $pdf->Cell(20, 8, '  ' . $val_comen, 0, 1, 'L');

        $pdf->Ln(8);
        $pdf->SetTextColor(0,0,0);
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(0, 8, utf8_decode('Información Técnica del Servidor'), 0, 1, 'L');
        
        $v_db = mysqli_fetch_assoc(mysqli_query($db, "SELECT VERSION() as version"));
        $size_res = mysqli_fetch_assoc(mysqli_query($db, "SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS size FROM information_schema.TABLES WHERE table_schema = 'universidadcni'"));

        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(60, 8, utf8_decode('Versión Motor Base de Datos'), 1, 0, 'C', true);
        $pdf->Cell(60, 8, 'Peso Total Almacenamiento', 1, 1, 'C', true);
        
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(60, 8, $v_db['version'] ?? 'Desconocida', 1, 0, 'C');
        $pdf->Cell(60, 8, ($size_res['size'] ?? '0.0') . ' MB', 1, 1, 'C');
    } 
    // =========================================================================
    // CASO 2: REPORTE DE NUEVOS USUARIOS
    // =========================================================================
    elseif ($tipo_reporte === 'nuevos_usuarios') {
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(0, 8, utf8_decode('Listado Detallado de Usuarios Registrados'), 0, 1, 'L');
        
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->SetFillColor(6, 91, 62);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->Cell(60, 8, 'Nombre Completo', 1, 0, 'C', true);
        $pdf->Cell(65, 8, 'Correo Institucional', 1, 0, 'C', true);
        $pdf->Cell(25, 8, 'Rol', 1, 0, 'C', true);
        $pdf->Cell(30, 8, 'Fecha Registro', 1, 1, 'C', true);
        
        $pdf->SetFont('Arial', '', 9);
        $pdf->SetTextColor(0, 0, 0);
        
        $res_u = mysqli_query($db, "SELECT nombre, email, rol, fecha_registro FROM usuarios WHERE 1=1 $where_fecha_usuarios ORDER BY id DESC", MYSQLI_USE_RESULT);
        if($res_u) {
            while($user = mysqli_fetch_assoc($res_u)) {
                $pdf->Cell(60, 8, utf8_decode(' ' . $user['nombre']), 1);
                $pdf->Cell(65, 8, ' ' . $user['email'], 1);
                $pdf->Cell(25, 8, ucfirst($user['rol']), 1, 0, 'C');
                $pdf->Cell(30, 8, date('d/m/Y', strtotime($user['fecha_registro'])), 1, 1, 'C');
            }
            mysqli_free_result($res_u);
        }
    } 
    // =========================================================================
    // CASO 3: REPORTE DE ACTIVIDAD (GRÁFICO INDEPENDIENTE)
    // =========================================================================
    elseif ($tipo_reporte === 'actividad_interaccion') {
        $pdf->SetFont('Arial', 'B', 13);
        $pdf->SetTextColor(30, 30, 30);
        $pdf->Cell(0, 10, utf8_decode('Métricas de Rendimiento Académico e Interacción'), 0, 1, 'C');
        $pdf->Ln(5);
        
        $res_tar = mysqli_query($db, "SELECT COUNT(*) as total FROM tareas_entregadas $where_fecha_tareas");
        $val_tareas = $res_tar ? (int)mysqli_fetch_assoc($res_tar)['total'] : 0;

        $res_prog = mysqli_query($db, "SELECT COUNT(*) as total FROM progreso_lecciones $where_fecha_progreso");
        $val_progreso = $res_prog ? (int)mysqli_fetch_assoc($res_prog)['total'] : 0;

        $res_com = mysqli_query($db, "SELECT COUNT(*) as total FROM comentarios_tarea");
        $val_comen = $res_com ? (int)mysqli_fetch_assoc($res_com)['total'] : 0;

        $max_val = max($val_progreso, $val_tareas, $val_comen);
        if ($max_val == 0) $max_val = 1; 
        $ancho_maximo_barra = 90; 

        $pdf->SetFont('Arial', 'B', 10);
        
        $pdf->SetTextColor(80, 80, 80);
        $pdf->Cell(60, 10, utf8_decode('Lecciones Completadas'), 0, 0, 'R');
        $pdf->Cell(5, 10, '', 0, 0); 
        $pdf->SetFillColor(6, 91, 62); 
        if (($w = ($val_progreso / $max_val) * $ancho_maximo_barra) > 0) $pdf->Cell($w, 10, '', 0, 0, 'L', true);
        $pdf->SetTextColor(6, 91, 62);
        $pdf->Cell(20, 10, '  ' . $val_progreso, 0, 1, 'L');
        $pdf->Ln(3);

        $pdf->SetTextColor(80, 80, 80);
        $pdf->Cell(60, 10, utf8_decode('Tareas y Evidencias Subidas'), 0, 0, 'R');
        $pdf->Cell(5, 10, '', 0, 0); 
        $pdf->SetFillColor(229, 169, 59); 
        if (($w = ($val_tareas / $max_val) * $ancho_maximo_barra) > 0) $pdf->Cell($w, 10, '', 0, 0, 'L', true);
        $pdf->SetTextColor(229, 169, 59);
        $pdf->Cell(20, 10, '  ' . $val_tareas, 0, 1, 'L');
        $pdf->Ln(3);

        $pdf->SetTextColor(80, 80, 80);
        $pdf->Cell(60, 10, utf8_decode('Comentarios y Feedback'), 0, 0, 'R');
        $pdf->Cell(5, 10, '', 0, 0); 
        $pdf->SetFillColor(23, 162, 184); 
        if (($w = ($val_comen / $max_val) * $ancho_maximo_barra) > 0) $pdf->Cell($w, 10, '', 0, 0, 'L', true);
        $pdf->SetTextColor(23, 162, 184);
        $pdf->Cell(20, 10, '  ' . $val_comen, 0, 1, 'L');
        $pdf->Ln(15);

        $pdf->SetFont('Arial', '', 11);
        $pdf->SetTextColor(50, 50, 50);
        $pdf->SetDrawColor(200, 200, 200);
        $pdf->SetFillColor(245, 245, 245);
        $resumen = "Durante el periodo establecido, los usuarios registraron un total de " . $val_progreso . " lecciones validadas. Por otra parte, se cargaron " . $val_tareas . " archivos de evidencias al sistema y se intercambiaron " . $val_comen . " mensajes de retroalimentacion en las plataformas de evaluacion.";
        $pdf->SetX(25);
        $pdf->MultiCell(160, 8, utf8_decode($resumen), 1, 'J', true);
    }
    // =========================================================================
    // CASO 4: REPORTE DE CURSOS CREADOS
    // =========================================================================
    elseif ($tipo_reporte === 'cursos_creados') {
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(0, 8, utf8_decode('Reporte Cronológico de Cursos Creados'), 0, 1, 'L');
        $pdf->Ln(2);

        $pdf->SetFont('Arial', 'B', 10);
        $pdf->SetFillColor(6, 91, 62); 
        $pdf->SetTextColor(255, 255, 255);
        $pdf->Cell(95, 8, utf8_decode('Título del Curso'), 1, 0, 'C', true);
        $pdf->Cell(50, 8, 'Instructor Asignado', 1, 0, 'C', true);
        $pdf->Cell(35, 8, 'Fecha', 1, 1, 'C', true);

        $pdf->SetFont('Arial', '', 9);
        $pdf->SetTextColor(0, 0, 0);

        $res_c = mysqli_query($db, "SELECT c.titulo, c.fecha_creacion, u.nombre AS maestro FROM cursos c LEFT JOIN usuarios u ON c.instructor_id = u.id $where_fecha_cursos ORDER BY c.id DESC", MYSQLI_USE_RESULT);
        if ($res_c) {
            while ($curso = mysqli_fetch_assoc($res_c)) {
                $pdf->Cell(95, 8, utf8_decode(' ' . $curso['titulo']), 1);
                $pdf->Cell(50, 8, utf8_decode(' ' . ($curso['maestro'] ?? 'Sin asignar')), 1);
                $pdf->Cell(35, 8, date('d/m/y', strtotime($curso['fecha_creacion'])), 1, 1, 'C');
            }
            mysqli_free_result($res_c);
        }
    }
    // =========================================================================
    // CASO 5: REPORTE DE CERTIFICADOS ACREDITADOS
    // =========================================================================
    elseif ($tipo_reporte === 'certificados_emitidos') {
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(0, 8, utf8_decode('Registro de Certificados Emitidos'), 0, 1, 'L');
        $pdf->Ln(2);

        $pdf->SetFont('Arial', 'B', 10);
        $pdf->SetFillColor(229, 169, 59); 
        $pdf->SetTextColor(255, 255, 255);
        
        $pdf->Cell(70, 8, 'Nombre del Alumno', 1, 0, 'C', true);
        $pdf->Cell(75, 8, utf8_decode('Programa Acreditado (Curso)'), 1, 0, 'C', true);
        $pdf->Cell(35, 8, utf8_decode('Fecha Emisión'), 1, 1, 'C', true);

        $pdf->SetFont('Arial', '', 9);
        $pdf->SetTextColor(0, 0, 0);

        $where_optimizado = str_replace('fecha_completado', 'p.fecha_completado', $where_fecha_progreso);
        $sql_certificados = "
            SELECT t.alumno, t.curso, t.fecha_emision
            FROM (
                SELECT u.nombre AS alumno, c.titulo AS curso, c.id as curso_id, MAX(p.fecha_completado) AS fecha_emision, COUNT(DISTINCT p.leccion_id) as completadas
                FROM progreso_lecciones p 
                INNER JOIN lecciones l ON p.leccion_id = l.id 
                INNER JOIN cursos c ON l.curso_id = c.id 
                INNER JOIN usuarios u ON p.usuario_id = u.id 
                $where_optimizado
                GROUP BY p.usuario_id, c.id, u.nombre, c.titulo
            ) as t
            INNER JOIN (
                SELECT curso_id, COUNT(id) as requeridas FROM lecciones GROUP BY curso_id
            ) as req ON t.curso_id = req.curso_id
            WHERE t.completadas = req.requeridas
            ORDER BY t.fecha_emision DESC
        ";

        $res_cert = mysqli_query($db, $sql_certificados, MYSQLI_USE_RESULT);
        $hubo_datos = false;
        if ($res_cert) {
            while ($cert = mysqli_fetch_assoc($res_cert)) {
                $hubo_datos = true;
                $titulo_curso = strlen($cert['curso']) > 40 ? substr($cert['curso'], 0, 38) . '...' : $cert['curso'];
                $pdf->Cell(70, 8, utf8_decode(' ' . $cert['alumno']), 1);
                $pdf->Cell(75, 8, utf8_decode(' ' . $titulo_curso), 1);
                $pdf->Cell(35, 8, date('d/m/Y', strtotime($cert['fecha_emision'])), 1, 1, 'C');
            }
            mysqli_free_result($res_cert);
        }

        if (!$hubo_datos) {
            $pdf->Cell(180, 10, utf8_decode('No existen registros de certificados emitidos en el periodo seleccionado.'), 1, 1, 'C');
        }
    }

    // PIE DE PÁGINA REGLAMENTARIO DE ALTO RENDIMIENTO
    $pdf->SetY(-25);
    $pdf->SetFont('Arial', 'I', 8);
    $pdf->SetTextColor(120, 120, 120);
    $pdf->Cell(0, 10, utf8_decode('Documento Administrativo Oficial - Universidad CNI. Generado el: ') . date('d/m/Y h:i A'), 0, 0, 'C');

    $pdf->Output('I', 'Reporte_CNI_' . $tipo_reporte . '.pdf');
    exit();
}


}
