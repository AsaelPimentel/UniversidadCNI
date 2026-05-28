<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow border-0">
                <div class="card-header bg-uabc-green text-white fw-bold">
                    <i class="fas fa-user-circle"></i> Mi Perfil de Usuario
                </div>
                <div class="card-body p-4">
                    
                    <?php if (isset($_SESSION['alerta'])): ?>
                        <div class="alert alert-<?php echo $_SESSION['alerta']['tipo']; ?> alert-dismissible fade show shadow-sm">
                            <?php echo $_SESSION['alerta']['mensaje']; unset($_SESSION['alerta']); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <form action="index.php?c=estudiante&a=actualizarContrasena" method="POST">
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold text-muted">Nombre Completo</label>
                            <input type="text" class="form-control bg-light" value="<?php echo htmlspecialchars($user['nombre']); ?>" disabled>
                            <div class="form-text">Para correcciones en tu nombre, contacta al administrador del CNI.</div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold text-muted">Correo Institucional</label>
                            <input type="email" class="form-control bg-light" value="<?php echo htmlspecialchars($user['email']); ?>" disabled>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold">Contraseña del Sistema</label>
                            <div class="input-group">
                                <input type="password" name="password" class="form-control" id="passwordField" value="<?php echo htmlspecialchars($user['password']); ?>" required>
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePassword()">
                                    <i class="fas fa-eye" id="eyeIcon"></i>
                                </button>
                            </div>
                            <div class="form-text text-success">
                                <i class="fas fa-info-circle"></i> Puedes modificar tu contraseña directamente o usar el botón para visualizarla.
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-between align-items-center border-top pt-3">
                            <a href="index.php?c=estudiante&a=index" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Volver al Catálogo</a>
                            <button type="submit" class="btn btn-warning fw-bold text-dark shadow-sm"><i class="fas fa-save"></i> Guardar Cambios</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function togglePassword() {
    var field = document.getElementById("passwordField");
    var icon = document.getElementById("eyeIcon");
    if (field.type === "password") {
        field.type = "text";
        icon.classList.remove("fa-eye");
        icon.classList.add("fa-eye-slash");
    } else {
        field.type = "password";
        icon.classList.remove("fa-eye-slash");
        icon.classList.add("fa-eye");
    }
}
</script>