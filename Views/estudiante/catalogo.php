<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="text-uabc-green fw-bold"><i class="fas fa-book-reader"></i> Catálogo de Cursos</h2>
    </div>

    <div class="row">
        <?php if (mysqli_num_rows($res_cursos) > 0): ?>
            <?php while ($curso = mysqli_fetch_assoc($res_cursos)): ?>
                <div class="col-md-4 mb-4">
                    <div class="card h-100 shadow-sm border-0 hover-shadow transition-all">
                        <img src="<?php echo !empty($curso['imagen']) ? htmlspecialchars($curso['imagen']) : 'assets/Img/default.jpg'; ?>" 
                             class="card-img-top" 
                             alt="Portada del curso" 
                             style="height: 200px; object-fit: cover;">
                        
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title fw-bold text-dark"><?php echo htmlspecialchars($curso['titulo']); ?></h5>
                            <p class="text-muted small mb-2">
                                <i class="fas fa-chalkboard-teacher text-uabc-gold"></i> Instructor: <?php echo htmlspecialchars($curso['nombre_instructor']); ?>
                            </p>
                            <p class="card-text text-secondary" style="font-size: 0.9rem;">
                                <?php echo htmlspecialchars(substr($curso['descripcion'], 0, 100)) . '...'; ?>
                            </p>
                            
                            <div class="mt-auto pt-3">
                                <a href="index.php?c=estudiante&a=verCurso&id=<?php echo $curso['id']; ?>" class="btn btn-uabc-green text-white w-100 fw-bold shadow-sm" style="background-color: #065b3e;">
                                    <i class="fas fa-sign-in-alt"></i> Entrar al Curso
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="col-12 text-center py-5">
                <i class="fas fa-box-open fa-4x text-muted opacity-25 mb-3"></i>
                <h4 class="text-muted">Aún no hay cursos disponibles.</h4>
                <p>Vuelve más tarde para descubrir nuevo contenido.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
    .hover-shadow:hover {
        transform: translateY(-5px);
        box-shadow: 0 .5rem 1rem rgba(0,0,0,.15)!important;
    }
    .transition-all {
        transition: all .3s ease-in-out;
    }
</style>