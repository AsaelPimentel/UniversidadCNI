<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
// Evita errores de "Undefined array key" si no se han pasado parámetros por la URL
$current_c = isset($_GET['c']) ? strtolower(trim($_GET['c'])) : 'auth';
$current_a = isset($_GET['a']) ? strtolower(trim($_GET['a'])) : 'login';
?>
<!DOCTYPEdoctype html>
    <html lang="es">

    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <title>Universidad CNI - Aula Virtual</title>
        <link rel="icon" type="image/png" href="Assets\Img\logo.png">

        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />
        <link rel="stylesheet" href="assets/Css/Login.css">

        <style>
            :root {
                --uabc-green: #065b3e;
                --uabc-gold: #e5a93b;
            }

            body {
                background: linear-gradient(135deg, rgba(240, 244, 243, 0.9) 0%, rgba(216, 226, 223, 0.9) 100%),
                    url("assets/Img/FondoCimarron.jpg");
                background-size: cover;
                background-attachment: fixed;
                background-position: center;
                min-height: 100vh;
                display: flex;
                flex-direction: column;
            }

            .bg-uabc-green {
                background-color: var(--uabc-green) !important;
            }

            .text-uabc-gold {
                color: var(--uabc-gold) !important;
            }

            .navbar-dark .navbar-nav .nav-link.active {
                color: var(--uabc-gold) !important;
                font-weight: bold;
            }

            main {
                flex: 1 0 auto;
            }
        </style>
    </head>

    <body>

        <header>
            <nav class="navbar navbar-expand-lg navbar-dark bg-uabc-green shadow">
                <div class="container">
                    <a class="navbar-brand fw-bold d-flex align-items-center" href="index.php">
                        <img src="assets/Img/Logo CNI.png" alt="Logo CNI" style="width: 45px; margin-right: 10px;">
                        <span>Universidad CNI</span>
                    </a>

                    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarPrincipal">
                        <span class="navbar-toggler-icon"></span>
                    </button>

                    <div class="collapse navbar-collapse" id="navbarPrincipal">
                        <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                            <?php if (isset($_SESSION['rol'])): ?>

                                <?php if ($_SESSION['rol'] === 'admin'): ?>
                                    <li class="nav-item">
                                        <a class="nav-link <?php echo ($current_c == 'admin' && $current_a == 'index') ? 'active' : ''; ?>" href="index.php?c=admin&a=index">
                                            <i class="fas fa-users-cog me-1"></i> Gestión de Usuarios
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link <?php echo ($current_c == 'admin' && $current_a == 'estadisticas') ? 'active' : ''; ?>" href="index.php?c=admin&a=estadisticas">
                                            <i class="fas fa-chart-bar me-1"></i> Estadísticas Globales
                                        </a>
                                    </li>

                                <?php elseif ($_SESSION['rol'] === 'instructor'): ?>
                                    <li class="nav-item">
                                        <a class="nav-link <?php echo ($current_c == 'instructor' && $current_a == 'index') ? 'active' : ''; ?>" href="index.php?c=instructor&a=index">
                                            <i class="fas fa-chalkboard-teacher me-1"></i> Mis Cursos
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link <?php echo ($current_c == 'instructor' && $current_a == 'verTareas') ? 'active' : ''; ?>" href="index.php?c=instructor&a=verTareas">
                                            <i class="fas fa-tasks me-1"></i> Revisar Tareas
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link <?php echo ($current_c == 'instructor' && $current_a == 'foro') ? 'active' : ''; ?>" href="index.php?c=instructor&a=foro">
                                            <i class="fas fa-comments me-1"></i> Foro de Dudas
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link <?php echo ($current_c == 'instructor' && $current_a == 'rendimiento') ? 'active' : ''; ?>" href="index.php?c=instructor&a=rendimiento">
                                            <i class="fas fa-chart-pie"></i> Métricas
                                        </a>
                                    </li>

                                <?php elseif ($_SESSION['rol'] === 'estudiante'): ?>
                                    <li class="nav-item">
                                        <a class="nav-link <?php echo ($current_c == 'estudiante' && $current_a == 'index') ? 'active' : ''; ?>" href="index.php?c=estudiante&a=index">
                                            <i class="fas fa-book-open me-1"></i> Catálogo de Cursos
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link <?php echo ($current_c == 'estudiante' && $current_a == 'miprogreso') ? 'active' : ''; ?>" href="index.php?c=estudiante&a=miProgreso">
                                            <i class="fas fa-chart-line me-1"></i> Mi Progreso
                                        </a>
                                    </li>
                                <?php endif; ?>

                            <?php endif; ?>
                        </ul>

                        <?php if (isset($_SESSION['usuario_id'])):
                            require_once __DIR__ . '/../../Models/NotificacionModel.php';
                            $notifModel = new NotificacionModel();
                            $notificaciones = $notifModel->obtenerNoLeidas($_SESSION['usuario_id']);
                            $total = mysqli_num_rows($notificaciones);
                        ?>
                            <div class="dropdown me-3">
                                <a class="nav-link text-white position-relative" href="#" role="button" data-bs-toggle="dropdown">
                                    <i class="fas fa-bell"></i>
                                    <?php if ($total > 0): ?>
                                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                            <?php echo $total; ?>
                                        </span>
                                    <?php endif; ?>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end shadow" style="width: 300px;">
                                    <li class="dropdown-header fw-bold">Notificaciones</li>
                                    <?php if ($total > 0): ?>
                                        <?php while ($n = mysqli_fetch_assoc($notificaciones)): ?>
                                            <li>
                                                <a class="dropdown-item text-wrap small" href="index.php?c=estudiante&a=verCurso&id=<?php echo $n['curso_id']; ?>&lec_id=<?php echo $n['leccion_id']; ?>">
                                                    <?php echo $n['mensaje']; ?>
                                                </a>
                                            </li>
                                        <?php endwhile; ?>
                                        <li>
                                            <hr class="dropdown-divider">
                                        </li>
                                        <li><a class="dropdown-item text-center small text-primary" href="index.php?c=auth&a=marcarLeidas">Marcar todas como leídas</a></li>
                                    <?php else: ?>
                                        <li><span class="dropdown-item text-muted small">No hay notificaciones nuevas.</span></li>
                                    <?php endif; ?>
                                </ul>
                            </div>
                        <?php endif; ?>

                        <?php if (isset($_SESSION['usuario_id'])): ?>
                            <div class="d-flex align-items-center">
                                <div class="text-white me-3 text-end d-none d-sm-block">
                                    <span class="fw-bold d-block"><?php echo htmlspecialchars($_SESSION['nombre']); ?></span>
                                    <span class="badge bg-warning text-dark text-uppercase" style="font-size: 0.7rem;"><?php echo htmlspecialchars($_SESSION['role'] ?? $_SESSION['rol']); ?></span>
                                </div>
                                <a href="index.php?c=auth&a=logout" class="btn btn-outline-light btn-sm fw-bold"><i class="fas fa-sign-out-alt"></i> Salir</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </nav>
        </header>

        <main class="py-4">