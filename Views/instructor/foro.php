<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="text-uabc-green fw-bold"><i class="fas fa-comments"></i> Foro de Dudas Generales</h2>
        <a href="index.php?c=instructor&a=index" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left"></i> Volver a Mis Cursos</a>
    </div>

    <?php if(isset($_GET['msj']) && $_GET['msj'] == 'respondido'): ?>
        <div class="alert alert-success alert-dismissible fade show shadow-sm">
            <i class="fas fa-check-circle"></i> Respuesta publicada correctamente en el foro.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm border-0 bg-light">
        <div class="card-body p-4">
            <?php if(mysqli_num_rows($comentarios) > 0): ?>
                <div class="row">
                    <?php while($coment = mysqli_fetch_assoc($comentarios)): 
                        // Diferenciar si es un comentario del maestro o la duda de un alumno
                        $es_maestro = ($coment['rol_usuario'] == 'instructor' || $coment['rol_usuario'] == 'admin');
                    ?>
                        <div class="col-12 mb-3">
                            <div class="card border-0 shadow-sm <?php echo $es_maestro ? 'ms-5 border-start border-primary border-4' : 'me-5 border-start border-warning border-4'; ?>">
                                <div class="card-header bg-white d-flex justify-content-between align-items-center py-2">
                                    <div>
                                        <strong class="<?php echo $es_maestro ? 'text-primary' : 'text-dark'; ?>">
                                            <i class="fas <?php echo $es_maestro ? 'fa-chalkboard-teacher' : 'fa-user-graduate'; ?>"></i> 
                                            <?php echo htmlspecialchars($coment['alumno']); ?>
                                        </strong>
                                        <span class="badge bg-secondary ms-2"><?php echo htmlspecialchars($coment['curso']); ?></span>
                                        <small class="text-muted ms-2">Lección: <?php echo htmlspecialchars($coment['leccion']); ?></small>
                                    </div>
                                    <small class="text-muted"><i class="far fa-clock"></i> <?php echo date('d M, H:i', strtotime($coment['fecha'])); ?></small>
                                </div>
                                <div class="card-body py-3">
                                    <p class="mb-0"><?php echo nl2br(htmlspecialchars($coment['comentario'])); ?></p>
                                </div>
                                
                                <?php if(!$es_maestro): ?>
                                    <div class="card-footer bg-white border-top-0 pt-0">
                                        <form action="index.php?c=instructor&a=responderDuda" method="POST" class="mt-2">
                                            <input type="hidden" name="leccion_id" value="<?php echo $coment['leccion_id']; ?>">
                                            <div class="input-group">
                                                <input type="text" name="respuesta" class="form-control form-control-sm" placeholder="Escribe tu respuesta como instructor..." required>
                                                <button type="submit" class="btn btn-primary btn-sm fw-bold">Responder</button>
                                            </div>
                                        </form>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <i class="far fa-smile-beam fa-4x text-success opacity-50 mb-3"></i>
                    <h4 class="text-muted">No hay dudas en tus cursos.</h4>
                    <p class="text-secondary">Todo parece estar muy claro para tus alumnos.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>