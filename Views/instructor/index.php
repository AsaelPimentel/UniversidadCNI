<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="text-uabc-green fw-bold"><i class="fas fa-chalkboard-teacher"></i> Mis Cursos Panel</h2>
        <a href="index.php?c=instructor&a=crearCurso" class="btn btn-warning fw-bold text-dark shadow-sm">
            <i class="fas fa-plus-circle"></i> Crear Nuevo Curso
        </a>
    </div>

    <div class="row">
        <?php if (mysqli_num_rows($cursos) > 0): ?>
            <?php while ($curso = mysqli_fetch_assoc($cursos)): ?>
                <div class="col-md-4 mb-4">
                    <div class="card h-100 shadow-sm border-0">
                        <img src="<?php echo !empty($curso['imagen']) ? htmlspecialchars($curso['imagen']) : 'assets/Img/default.jpg'; ?>" 
                             class="card-img-top" alt="Miniatura del Curso" style="height: 180px; object-fit: cover;">
                        
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title fw-bold text-dark"><?php echo htmlspecialchars($curso['titulo']); ?></h5>
                            <p class="card-text text-secondary small flex-grow-1">
                                <?php echo htmlspecialchars(substr($curso['descripcion'], 0, 110)) . '...'; ?>
                            </p>
                            <span class="text-muted mb-3" style="font-size: 0.8rem;">
                                <i class="far fa-calendar-alt"></i> Creado el: <?php echo date('d/m/Y', strtotime($curso['fecha_creacion'])); ?>
                            </span>
                            
                            <div class="row g-1 pt-2 border-top">
                                <div class="col-6">
                                    <a href="index.php?c=instructor&a=nuevaLeccion&curso_id=<?php echo $curso['id']; ?>" class="btn btn-uabc-green text-white btn-sm w-100 py-2" style="background-color: #065b3e;">
                                        <i class="fas fa-video"></i> Lecciones
                                    </a>
                                </div>
                                <div class="col-6">
                                    <a href="index.php?c=instructor&a=rendimiento&curso_id=<?php echo $curso['id']; ?>" class="btn btn-outline-secondary btn-sm w-100 py-2">
                                        <i class="fas fa-chart-pie"></i> Métricas
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="col-12 text-center py-5 bg-white rounded shadow-sm">
                <i class="fas fa-graduation-cap fa-4x text-muted opacity-25 mb-3"></i>
                <h4 class="text-muted">Aún no has dado de alta ningún curso.</h4>
                <p class="text-secondary">Haz clic en el botón de arriba para registrar tu primera materia.</p>
            </div>
        <?php endif; ?>
    </div>
</div>