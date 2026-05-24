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
            $resultado = $model->actualizar($_POST);
            if ($resultado === "ok") {
                $_SESSION['alerta'] = ['tipo' => 'success', 'mensaje' => '¡Usuario actualizado correctamente!'];
                header("Location: index.php?c=admin&a=index");
            } else {
                $_SESSION['alerta'] = ['tipo' => 'danger', 'mensaje' => 'Error o correo duplicado al actualizar.'];
                header("Location: index.php?c=admin&a=editar&id=" . $_POST['usuario_id']);
            }
            exit();
        }

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

        // Obtenemos los KPIs desde la base de datos
        $total_usuarios = $model->getTotalUsuarios();
        $total_cursos = $model->getTotalCursos();
        $total_certificados = $model->getTotalCertificados();

        // AGREGAR ESTA LÍNEA:
        $info_sistema = $model->getInfoSistema();

        // Obtenemos los datos para la gráfica de Chart.js
        $res_roles = $model->getUsuariosPorRol();
        $labels_roles = [];
        $data_roles = [];

        while ($row = mysqli_fetch_assoc($res_roles)) {
            $labels_roles[] = ucfirst($row['rol']);
            $data_roles[] = $row['cantidad'];
        }

        // Cargamos las vistas inyectando las variables
        require_once __DIR__ . '/../Views/Layout/header.php';
        require_once __DIR__ . '/../Views/admin/estadisticas.php';
        require_once __DIR__ . '/../Views/Layout/footer.php';
    }
}
