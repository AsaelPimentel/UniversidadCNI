<?php
require_once __DIR__ . '/../Models/UsuarioModel.php';

class AuthController
{

    public function login()
    {
        // Redirige automáticamente si ya hay sesión iniciada
        Seguridad::redirigirSiLogueado();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $model = new UsuarioModel();
            $usuario = $model->login($_POST['email']);

            if ($usuario && ($_POST['password'] === $usuario['password'] || password_verify($_POST['password'], $usuario['password']))) {
                $_SESSION['usuario_id'] = $usuario['id'];
                $_SESSION['nombre']     = $usuario['nombre'];
                $_SESSION['rol']        = $usuario['rol'];

                Seguridad::redirigirPorRol($usuario['rol']);
            } else {
                $error = "El correo institucional o la contraseña son incorrectos.";
                require_once __DIR__ . '/../Views/auth/login.php';
            }
        } else {
            require_once __DIR__ . '/../Views/auth/login.php';
        }
    }

    public function logout()
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        session_unset();
        session_destroy();
        header("Location: index.php?c=auth&a=login");
        exit();
    }

    public function marcarLeidas()
    {
        require_once __DIR__ . '/../Models/NotificacionModel.php';
        $model = new NotificacionModel();
        $model->marcarTodasLeidas($_SESSION['usuario_id']);
        header("Location: " . $_SERVER['HTTP_REFERER']); // Regresa a donde estaba el usuario
        exit();
    }
}
