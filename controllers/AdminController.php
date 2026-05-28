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
}
