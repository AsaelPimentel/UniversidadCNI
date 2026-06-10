<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-warning text-dark fw-bold">
                    <i class="fas fa-edit"></i> Editar Lección
                </div>
                <div class="card-body p-4 bg-light">

                    <form action="index.php?c=instructor&a=editarLeccion" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="id" value="<?php echo htmlspecialchars($leccion['id']); ?>">
                        <input type="hidden" name="curso_id" value="<?php echo htmlspecialchars($curso_id); ?>">

                        <div class="mb-3">
                            <label class="form-label fw-bold">Título de la Lección</label>
                            <input type="text" name="titulo" class="form-control" value="<?php echo htmlspecialchars($leccion['titulo']); ?>" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Enlace de Video (YouTube)</label>
                            <input type="text" name="url" class="form-control" value="<?php echo htmlspecialchars($leccion['contenido_url']); ?>" required>
                            <div class="form-text text-muted small">El sistema extraerá automáticamente el ID del video si pegas el enlace completo.</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Añadir más Material Digital (Opcional)</label>
                            <input type="file" name="pdf_files[]" class="form-control" accept=".pdf" multiple>
                            <div class="form-text small text-secondary">Si subes archivos nuevos, se sumarán a los que ya tiene esta lección.</div>
                        </div>

                        <div class="mb-4 border p-3 rounded bg-white shadow-sm">
                            <div class="form-check form-switch mb-0">
                                <input class="form-check-input" type="checkbox" id="checkTareaEdit" name="tiene_tarea" value="1" <?php echo ($leccion['tiene_tarea'] == 1) ? 'checked' : ''; ?>>
                                <label class="form-check-label fw-bold text-dark" for="checkTareaEdit">
                                    <i class="fas fa-file-upload text-warning me-1"></i> Requerir entrega de tarea para esta lección
                                </label>
                            </div>

                            <div class="mt-2 pl-4">
                                <label class="form-label text-muted small fw-bold mb-1">Fecha y Hora Límite (Opcional)</label>
                                <input type="datetime-local" name="fecha_limite" class="form-control form-control-sm" value="<?php echo !empty($leccion['fecha_limite']) ? date('Y-m-d\TH:i', strtotime($leccion['fecha_limite'])) : ''; ?>">
                            </div>
                        </div>

                        <div class="d-flex justify-content-between mt-4">
                            <a href="index.php?c=instructor&a=nuevaLeccion&curso_id=<?php echo $curso_id; ?>" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancelar
                            </a>
                            <button type="submit" class="btn btn-uabc-green text-white fw-bold px-4" style="background-color: #065b3e;">
                                <i class="fas fa-sync-alt"></i> Actualizar Cambios
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</div>