<!doctype html>
<html lang="es">

<head>
    <title>Iniciar Sesión | Universidad CNI</title>
    <link rel="icon" type="image/png" href="Assets/Img/logo.png">
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&family=Playfair+Display:ital,wght@0,600;1,600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="Assets/Css/Login.css">

    <style>
        :root {
            --uabc-green: #065b3e;
            --uabc-gold: #e5a93b;
        }

        body.login-page {
            background: linear-gradient(135deg, rgba(6, 91, 62, 0.9) 0%, rgba(20, 30, 25, 0.95) 100%),
                url("Assets/Img/FondoCimarron.jpg");
            background-size: cover;
            background-attachment: fixed;
            background-position: center;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-card {
            border: none;
            border-radius: 15px;
            overflow: hidden;
            max-width: 900px;
            width: 100%;
        }

        /* Lado Izquierdo (Brand) */
        .brand-side {
            background-color: var(--uabc-green);
            color: white;
            padding: 3rem;
        }

        .logo-container {
            background: white;
            padding: 15px;
            border-radius: 50%;
            width: 140px;
            height: 140px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .cni-logo-main {
            max-width: 100px;
        }

        .uabc-logo-small {
            width: 40px;
        }

        .institution-name {
            font-family: 'Playfair Display', serif;
            font-weight: 600;
        }

        .brand-divider {
            border-top: 2px solid var(--uabc-gold);
            opacity: 1;
        }

        /* Lado Derecho (Formulario) */
        .form-side {
            background: #fff;
            padding: 3rem;
        }

        .custom-form-floating>.input-icon {
            position: absolute;
            right: 15px;
            top: 15px;
            color: #adb5bd;
            z-index: 10;
        }

        .btn-uabc-gold {
            background-color: var(--uabc-gold);
            color: #000;
            font-weight: 700;
            transition: all 0.3s;
        }

        .btn-uabc-gold:hover {
            background-color: #cc922e;
            color: #fff;
        }

        .link-uabc-green {
            color: var(--uabc-green);
        }
    </style>
</head>

<body class="login-page">
    <div class="container p-3">
        <div class="login-card shadow-lg mx-auto">
            <div class="row g-0">

<div class="col-md-5 brand-side d-flex flex-column justify-content-center align-items-center text-center">
    
    <div class="uabc-inline-container d-flex align-items-center justify-content-center mb-1">
        <img src="Assets/Img/EscudoUABC.png" alt="Escudo UABC" class="uabc-logo-small me-3">
        <h5 class="institution-name text-white mb-0">UNIVERSIDAD AUTÓNOMA DE BAJA CALIFORNIA</h5>
    </div>
    <h6 class="faculty-name text-white text-uppercase opacity-75 mb-3">Facultad de Ciencias Administrativas</h6>
    
    <hr class="w-25 mx-auto my-3 brand-divider">

    <div class="logo-container my-4 shadow-sm">
        <img src="Assets/Img/Logo CNI.png" alt="Logo CNI" class="img-fluid cni-logo-main">
    </div>

    <p class="motto fst-italic">"Por la realización plena del ser"</p>
</div>

                <div class="col-md-7 form-side d-flex flex-column justify-content-center">
                    <div class="text-center mb-4">
                        <h3 class="fw-bold text-dark mb-2">Acceso al Sistema</h3>
                        <p class="text-muted small">Plataforma Educativa Exclusiva</p>
                    </div>

                    <?php if (isset($error) && !empty($error)): ?>
                        <div class="alert alert-danger alert-dismissible fade show shadow-sm mb-4" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i> <?php echo htmlspecialchars($error); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <form action="index.php?c=auth&a=login" method="POST">
                        <div class="form-floating mb-4 position-relative custom-form-floating">
                            <input type="email" name="email" class="form-control" id="emailInput" placeholder=" " required>
                            <label for="emailInput">Correo Institucional</label>
                            <i class="fa-solid fa-envelope input-icon"></i>
                        </div>

                        <div class="form-floating mb-4 position-relative custom-form-floating">
                            <input type="password" name="password" class="form-control" id="passwordInput" placeholder=" " required>
                            <label for="passwordInput">Contraseña</label>
                            <i class="fa-solid fa-lock input-icon"></i>
                        </div>

                        <button type="submit" class="btn btn-uabc-gold w-100 fw-bold py-3 mt-3 shadow-sm login-btn text-uppercase">
                            Iniciar Sesión <i class="fa-solid fa-arrow-right ms-2"></i>
                        </button>
                    </form>

                    <div class="text-center mt-4 pt-3 border-top border-light">
                        <small class="text-muted">
                            ¿Problemas para acceder a tu cuenta? <br>
                            <a href="mailto:admin@universidadcni.edu.mx?subject=Soporte%20T%C3%A9cnico%20-%20Universidad%20CNI" class="text-decoration-none fw-bold link-uabc-green">
                                Contacta al administrador del sistema
                            </a>
                        </small>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>