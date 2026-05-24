<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow border-0">
                <div class="card-header bg-uabc-green text-white fw-bold">
                    <i class="fas fa-user-edit"></i> Editar Información del Usuario
                </div>
                <div class="card-body">
                    
                    <?php if (isset($_SESSION['alerta'])): ?>
                        <div class="alert alert-<?php echo $_SESSION['alerta']['tipo']; ?>">
                            <?php echo $_SESSION['alerta']['mensaje']; unset($_SESSION['alerta']); ?>
                        </div>
                    <?php endif; ?>

                    <form action="index.php?c=admin&a=editar" method="POST">
                        <input type="hidden" name="usuario_id" value="<?php echo $user['id']; ?>">

                        <div class="mb-3">
                            <label class="form-label fw-bold">Nombre Completo</label>
                            <input type="text" name="nombre" class="form-control" value="<?php echo htmlspecialchars($user['nombre']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Correo Institucional</label>
                            <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                        </div>
                        <div class="mb-4">
                            <label class="form-label fw-bold">Rol del Sistema</label>
                            <select name="rol" class="form-select" required>
                                <option value="estudiante" <?php echo ($user['rol'] == 'estudiante') ? 'selected' : ''; ?>>Estudiante</option>
                                <option value="instructor" <?php echo ($user['rol'] == 'instructor') ? 'selected' : ''; ?>>Instructor</option>
                                <option value="admin" <?php echo ($user['rol'] == 'admin') ? 'selected' : ''; ?>>Administrador</option>
                            </select>
                        </div>
                        
                        <div class="d-flex justify-content-between">
                            <a href="index.php?c=admin&a=index" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Volver</a>
                            <button type="submit" class="btn btn-warning fw-bold text-dark"><i class="fas fa-sync-alt"></i> Actualizar Datos</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>