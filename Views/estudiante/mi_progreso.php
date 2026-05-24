<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="text-uabc-green fw-bold"><i class="fas fa-chart-line"></i> Mi Rendimiento Escolar</h2>
        <a href="index.php?c=estudiante&a=index" class="btn btn-outline-secondary btn-sm">Explorar más cursos</a>
    </div>

    <div class="row">
        <div class="col-lg-8 mb-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white fw-bold border-bottom-0 pt-4 pb-0">
                    <h5 class="fw-bold mb-0"><i class="fas fa-laptop-code text-primary"></i> Cursos en Progreso</h5>
                </div>
                <div class="card-body">
                    <?php if (mysqli_num_rows($cursos_activos) > 0): ?>
                        <?php while ($curso = mysqli_fetch_assoc($cursos_activos)): 
                            $porcentaje = ($curso['total_lecciones'] > 0) ? round(($curso['completadas'] / $curso['total_lecciones']) * 100) : 0;
                            $ya_graduado = ($porcentaje == 100);
                        ?>
                            <div class="d-flex align-items-center mb-4 p-3 border rounded shadow-sm hover-shadow bg-light">
                                <img src="<?php echo !empty($curso['imagen']) ? htmlspecialchars($curso['imagen']) : 'assets/Img/default.jpg'; ?>" 
                                     class="rounded me-3 object-fit-cover" style="width: 80px; height: 80px;">
                                
                                <div class="flex-grow-1">
                                    <h6 class="fw-bold mb-1"><?php echo htmlspecialchars($curso['titulo']); ?></h6>
                                    <p class="small text-muted mb-2">Has completado <?php echo $curso['completadas']; ?> de <?php echo $curso['total_lecciones']; ?> lecciones.</p>
                                    
                                    <div class="progress" style="height: 10px;">
                                        <div class="progress-bar progress-bar-striped progress-bar-animated <?php echo $ya_graduado ? 'bg-success' : 'bg-uabc-green'; ?>" 
                                             role="progressbar" style="width: <?php echo $porcentaje; ?>%;" 
                                             aria-valuenow="<?php echo $porcentaje; ?>" aria-valuemin="0" aria-valuemax="100">
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="ms-4 text-center">
                                    <span class="fs-4 fw-bold <?php echo $ya_graduado ? 'text-success' : 'text-dark'; ?>"><?php echo $porcentaje; ?>%</span>
                                    <br>
                                    <?php if ($ya_graduado): ?>
                                        <a href="index.php?c=estudiante&a=certificado&curso_id=<?php echo $curso['id']; ?>" class="btn btn-warning btn-sm fw-bold mt-1 text-dark" target="_blank">
                                            <i class="fas fa-award"></i> Diploma
                                        </a>
                                    <?php else: ?>
                                        <a href="index.php?c=estudiante&a=verCurso&id=<?php echo $curso['id']; ?>" class="btn btn-outline-primary btn-sm mt-1">
                                            Continuar
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="fas fa-folder-open fa-3x text-muted opacity-25 mb-3"></i>
                            <p class="text-muted">Aún no has comenzado ningún curso.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-lg-4 mb-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-uabc-green text-white fw-bold">
                    <i class="fas fa-history"></i> Actividad Reciente
                </div>
                <div class="card-body">
                    <div class="timeline">
                        <?php if (mysqli_num_rows($timeline) > 0): ?>
                            <ul class="list-unstyled">
                                <?php while ($act = mysqli_fetch_assoc($timeline)): 
                                    $es_tarea = ($act['tipo'] == 'tarea');
                                    $icono = $es_tarea ? 'fa-file-upload text-warning' : 'fa-check-circle text-success';
                                    $accion = $es_tarea ? 'Entregaste tarea en:' : 'Completaste lección:';
                                ?>
                                    <li class="mb-3 border-start border-3 <?php echo $es_tarea ? 'border-warning' : 'border-success'; ?> ps-3 py-1">
                                        <p class="mb-0 fw-bold" style="font-size: 0.9rem;">
                                            <i class="fas <?php echo $icono; ?> me-1"></i> <?php echo $accion; ?>
                                        </p>
                                        <p class="mb-0 text-muted small text-truncate" title="<?php echo htmlspecialchars($act['detalle']); ?>">
                                            <?php echo htmlspecialchars($act['detalle']); ?>
                                        </p>
                                        <small class="text-secondary" style="font-size: 0.75rem;">
                                            <i class="far fa-clock"></i> <?php echo date('d M, Y - H:i', strtotime($act['fecha'])); ?>
                                        </small>
                                    </li>
                                <?php endwhile; ?>
                            </ul>
                        <?php else: ?>
                            <p class="text-muted text-center small py-4">No hay actividad reciente para mostrar.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>