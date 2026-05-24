<?php
// 1. Iniciamos el buffer de salida (evita errores de headers) y la sesión
ob_start();
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// 2. Requerimos las configuraciones vitales (AQUÍ ES DONDE SE ARREGLA TU ERROR)
// Al cargar esto aquí, todos los Controladores y Modelos tendrán acceso a la Conexión y Seguridad
require_once 'config/Conexion.php';
require_once 'config/Seguridad.php';

// 3. Sistema de Enrutamiento (Front Controller)
// Capturamos el Controlador (c) y la Acción (a) por la URL
// Si no hay nada en la URL, el valor por defecto es ir al Login (auth / login)
$controller = isset($_GET['c']) ? strtolower(trim($_GET['c'])) : 'auth';
$action     = isset($_GET['a']) ? strtolower(trim($_GET['a'])) : 'login';

// Formateamos el nombre. Ejemplo: "auth" -> "AuthController"
$controllerName = ucfirst($controller) . 'Controller';
$controllerFile = 'controllers/' . $controllerName . '.php';

// 4. Verificamos si el archivo del Controlador existe
if (file_exists($controllerFile)) {
    require_once $controllerFile;
    
    // Instanciamos el controlador
    $objController = new $controllerName();
    
    // Verificamos si la función (acción) existe dentro del controlador
    if (method_exists($objController, $action)) {
        
        // Ejecutamos la función
        $objController->$action();
        
    } else {
        die("Error 404: La acción '{$action}' no existe en el controlador '{$controllerName}'.");
    }
} else {
    die("Error 404: El controlador '{$controllerName}' no existe. Verifica la URL.");
}

// Limpiamos el buffer
ob_end_flush();
?>