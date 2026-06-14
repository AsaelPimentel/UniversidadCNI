<div class="container mt-4 mb-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3 d-flex align-items-center">
                    <h4 class="text-uabc-green fw-bold m-0"><i class="fas fa-edit me-2"></i> Editar Curso</h4>
                </div>
                <div class="card-body p-4">
                    <form action="index.php?c=instructor&a=editarCurso" method="POST" enctype="multipart/form-data">
                        
                        <input type="hidden" name="id" value="<?php echo $curso['id']; ?>">
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold text-secondary">Título del Curso</label>
                            <input type="text" name="titulo" class="form-control" value="<?php echo htmlspecialchars($curso['titulo']); ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold text-secondary">Descripción</label>
                            <textarea name="descripcion" class="form-control" rows="4" required><?php echo htmlspecialchars($curso['descripcion']); ?></textarea>
                        </div>
                        
                        <div class="mb-4">
                            <label class="form-label fw-bold text-secondary">Imagen de Portada (Opcional)</label>
                            <div class="d-flex align-items-center mb-3">
                                <div class="me-3">
                                    <span class="d-block small text-muted mb-1">Portada Actual:</span>
                                    <img src="<?php echo !empty($curso['imagen']) ? htmlspecialchars($curso['imagen']) : 'Assets/Img/default.jpg'; ?>" 
                                         alt="Portada Actual" class="img-thumbnail shadow-sm" style="height: 80px; width: 120px; object-fit: cover;">
                                </div>
                                <div class="flex-grow-1">
                                    <input type="file" name="imagen_curso" class="form-control form-control-sm" accept="image/*">
                                    <div class="form-text mt-1 text-primary"><i class="fas fa-info-circle"></i> Si no deseas cambiar la imagen actual, deja este campo vacío.</div>
                                </div>
                            </div>
                        </div>
                        
                        <hr class="text-muted">
                        
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <a href="index.php?c=instructor&a=index" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left"></i> Volver
                            </a>
                            <button type="submit" class="btn btn-warning fw-bold text-dark px-4">
                                <i class="fas fa-save"></i> Guardar Cambios
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>