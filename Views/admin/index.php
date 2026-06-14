<div class="container mt-4">
    <h2 class="mb-4 text-uabc-green fw-bold"><i class="fas fa-users-cog"></i> Gestión de Usuarios</h2>

    <?php if (isset($_SESSION['alerta'])): ?>
        <div class="alert alert-<?php echo $_SESSION['alerta']['tipo']; ?> alert-dismissible fade show shadow-sm">
            <?php echo $_SESSION['alerta']['mensaje']; unset($_SESSION['alerta']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>


    <div class="row">
        <div class="col-lg-4 mb-4">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-uabc-green text-white fw-bold">
                    <i class="fas fa-user-plus"></i> Registrar Nuevo Usuario
                </div>
                <div class="card-body bg-light">
                    <form action="index.php?c=admin&a=crear" method="POST">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Nombre Completo</label>
                            <input type="text" name="nombre" class="form-control" placeholder="Ej. Juan Pérez" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Correo Institucional</label>
                            <input type="email" name="email" class="form-control" placeholder="correo@uabc.edu.mx" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Contraseña Temporal</label>
                            <input type="password" name="password" class="form-control" placeholder="******" required>
                        </div>
                        <div class="mb-4">
                            <label class="form-label fw-bold">Rol del Sistema</label>
                            <select name="rol" class="form-select" required>
                                <option value="estudiante">Estudiante</option>
                                <option value="instructor">Instructor</option>
                                <option value="admin">Administrador</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-warning w-100 fw-bold text-dark">
                            <i class="fas fa-save"></i> Guardar Usuario
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <span class="fw-bold"><i class="fas fa-list"></i> Lista de Usuarios (<?php echo $total_usuarios; ?>)</span>
                    
                    <form method="GET" action="index.php" class="d-flex">
                        <input type="hidden" name="c" value="admin">
                        <input type="hidden" name="a" value="index">
                        <input type="text" name="buscar" class="form-control form-control-sm me-2" placeholder="Buscar nombre o correo..." value="<?php echo htmlspecialchars($buscar); ?>">
                        <button class="btn btn-outline-success btn-sm" type="submit"><i class="fas fa-search"></i></button>
                    </form>
                </div>
                
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped mb-0 align-middle">
                            <thead class="table-dark text-center">
                                <tr>
                                    <th>ID</th>
                                    <th>Nombre</th>
                                    <th>Correo</th>
                                    <th>Rol</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="text-center">
                                <?php if(mysqli_num_rows($usuarios) > 0): ?>
                                    <?php while ($row = mysqli_fetch_assoc($usuarios)): ?>
                                        <tr>
                                            <td class="fw-bold"><?php echo $row['id']; ?></td>
                                            <td><?php echo htmlspecialchars($row['nombre']); ?></td>
                                            <td><?php echo htmlspecialchars($row['email']); ?></td>
                                            <td>
                                                <?php 
                                                    $badge = 'bg-secondary';
                                                    if($row['rol'] == 'admin') $badge = 'bg-danger';
                                                    if($row['rol'] == 'instructor') $badge = 'bg-primary';
                                                    if($row['rol'] == 'estudiante') $badge = 'bg-success';
                                                ?>
                                                <span class="badge <?php echo $badge; ?> text-uppercase"><?php echo $row['rol']; ?></span>
                                            </td>
                                            <td>
                                                <a href="index.php?c=admin&a=editar&id=<?php echo $row['id']; ?>" class="btn btn-sm btn-primary" title="Editar">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <?php if($row['id'] != $_SESSION['usuario_id']): // Evitar que el admin se borre a sí mismo ?>
                                                    <a href="index.php?c=admin&a=borrar&id=<?php echo $row['id']; ?>" class="btn btn-sm btn-danger" title="Eliminar" onclick="return confirm('¿Estás seguro de eliminar permanentemente este usuario?');">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </a>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center py-4 text-muted">No se encontraron usuarios.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <?php if($total_paginas > 1): ?>
                    <div class="card-footer bg-white d-flex justify-content-center pt-3">
                        <nav>
                            <ul class="pagination pagination-sm">
                                <?php for($i = 1; $i <= $total_paginas; $i++): ?>
                                    <li class="page-item <?php echo ($i == $pagina) ? 'active' : ''; ?>">
                                        <a class="page-link" href="index.php?c=admin&a=index&pagina=<?php echo $i; ?>&buscar=<?php echo urlencode($buscar); ?>"><?php echo $i; ?></a>
                                    </li>
                                <?php endfor; ?>
                            </ul>
                        </nav>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>