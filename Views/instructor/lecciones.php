<div class="container mt-4">
    <div class="mb-4">
        <a href="index.php?c=instructor&a=index" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left"></i> Volver a Mis Cursos</a>
    </div>

    <?php if (isset($_GET['msj']) && $_GET['msj'] == 'ok'): ?>
        <div class="alert alert-success alert-dismissible fade show shadow-sm">
            <i class="fas fa-check-circle"></i> ¡Estructura de la lección actualizada de forma correcta!
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-lg-5 mb-4">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-dark text-white fw-bold">
                    <i class="fas fa-plus"></i> Agregar Nueva Lección / Clase
                </div>
                <div class="card-body bg-light p-4">
                    <form action="index.php?c=instructor&a=nuevaLeccion&curso_id=<?php echo $curso_id; ?>" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="curso_id" value="<?php echo $curso_id; ?>">

                        <div class="mb-3">
                            <label class="form-label fw-bold">Título de la Lección</label>
                            <input type="text" name="titulo_leccion" class="form-control" placeholder="Ej. Clase 1: Fundamentos Básicos" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Enlace de Video (YouTube)</label>
                            <input type="url" name="url_video" class="form-control" placeholder="https://www.youtube.com/watch?v=..." required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Material Digital (Múltiples PDFs)</label>
                            <input type="file" name="pdf_files[]" class="form-control" accept=".pdf" multiple>
                        </div>

                        <div class="mb-4 border p-2 rounded bg-white">
                            <div class="form-check form-switch mb-0">
                                <input class="form-check-input" type="checkbox" id="checkTarea" name="tiene_tarea" value="1">
                                <label class="form-check-label fw-bold text-dark" for="checkTarea">
                                    <i class="fas fa-file-upload text-warning me-1"></i> Requerir entrega de tarea al alumno
                                </label>
                            </div>

                            <div class="mt-2 pl-4">
                                <label class="form-label text-muted small fw-bold mb-1">Fecha y Hora Límite (Opcional)</label>
                                <input type="datetime-local" name="fecha_limite" class="form-control form-control-sm">
                                <div class="form-text" style="font-size:0.75rem;">Si dejas este campo vacío, la tarea no tendrá caducidad.</div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-uabc-green text-white w-100 fw-bold shadow-sm" style="background-color: #065b3e;">
                            <i class="fas fa-cloud-upload-alt"></i> Subir Lección
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white fw-bold">
                    <i class="fas fa-stream text-uabc-green"></i> Estructura de Clases Actuales
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        <?php if (mysqli_num_rows($lecciones) > 0): ?>
                            <?php $num = 1;
                            while ($lec = mysqli_fetch_assoc($lecciones)): ?>
                                <div class="list-group-item d-flex justify-content-between align-items-center p-3">
                                    <div>
                                        <h6 class="mb-1 fw-bold text-dark"><?php echo $num . ". " . htmlspecialchars($lec['titulo']); ?></h6>
                                        <small class="text-muted">ID de Video: <code><?php echo $lec['contenido_url']; ?></code></small>
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <?php if ($lec['tiene_tarea']): ?>
                                            <span class="badge bg-warning text-dark me-2" title="Requiere entregable"><i class="fas fa-file-alt"></i> Evidencia</span>
                                        <?php endif; ?>
                                        <a href="index.php?c=instructor&a=editarLeccion&id=<?php echo $lec['id']; ?>&curso_id=<?php echo $curso_id; ?>" class="btn btn-sm btn-primary me-1" title="Editar Lección">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="index.php?c=instructor&a=eliminarLeccion&id=<?php echo $lec['id']; ?>&curso_id=<?php echo $curso_id; ?>" class="btn btn-sm btn-danger" title="Eliminar Lección" onclick="return confirm('¿Estás seguro de eliminar esta lección?');">
                                            <i class="fas fa-trash-alt"></i>
                                        </a>
                                    </div>
                                </div>
                            <?php $num++;
                            endwhile; ?>
                        <?php else: ?>
                            <div class="p-4 text-center text-muted">
                                <p class="mb-0">Este curso aún no tiene lecciones creadas.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>