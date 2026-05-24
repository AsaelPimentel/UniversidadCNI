<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-7">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-uabc-green text-white fw-bold">
                    <i class="fas fa-folder-plus"></i> Registrar Materia en Catálogo
                </div>
                <div class="card-body p-4">
                    <form action="index.php?c=instructor&a=crearCurso" method="POST" enctype="multipart/form-data">
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">Título del Curso</label>
                            <input type="text" name="titulo" class="form-control" placeholder="Ej. Introducción a PHP Orientado a Objetos" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">Descripción del Curso</label>
                            <textarea name="descripcion" class="form-control" rows="5" placeholder="Escribe los objetivos del curso, perfil de ingreso, etc..." required></textarea>
                        </div>
                        
                        <div class="mb-4">
                            <label class="form-label fw-bold">Imagen de Portada (Banner)</label>
                            <input type="file" name="imagen_curso" class="form-control" accept="image/*">
                            <div class="form-text text-muted">Formatos recomendados: JPG, PNG. Dimensión sugerida: 800x450px.</div>
                        </div>
                        
                        <div class="d-flex justify-content-between">
                            <a href="index.php?c=instructor&a=index" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Cancelar
                            </a>
                            <button type="submit" class="btn btn-warning fw-bold text-dark shadow-sm">
                                <i class="fas fa-save"></i> Publicar Curso
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>