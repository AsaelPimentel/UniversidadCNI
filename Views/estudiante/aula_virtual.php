<div class="container-fluid mt-3 px-4" id="zona-foro-dudas">

    <div class="mb-3 d-flex justify-content-between align-items-center">
        <h3 class="fw-bold text-uabc-green"><i class="fas fa-laptop-code"></i> <?php echo htmlspecialchars($curso['titulo']); ?></h3>
        <a href="index.php?c=estudiante&a=index" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left"></i> Volver al catálogo</a>
    </div>

    <?php if (isset($_GET['msj']) && $_GET['msj'] == 'tarea_ok'): ?>
        <div class="alert alert-success alert-dismissible fade show shadow-sm">
            <i class="fas fa-check-circle"></i> ¡Tu tarea ha sido enviada correctamente al instructor!
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-lg-3 mb-4">
            <div class="card shadow-sm border-0 sticky-top" style="top: 20px;">
                <div class="card-header bg-dark text-white fw-bold">
                    <i class="fas fa-list-ul"></i> Contenido del Curso
                </div>
                <div class="list-group list-group-flush" style="max-height: 70vh; overflow-y: auto;">
                    <?php if (mysqli_num_rows($lecciones) > 0): ?>
                        <?php while ($lec = mysqli_fetch_assoc($lecciones)):
                            $es_actual = (isset($leccion_actual['id']) && $leccion_actual['id'] == $lec['id']);
                            $esta_completada = in_array($lec['id'], $progreso_array);
                        ?>
                            <a href="index.php?c=estudiante&a=verCurso&id=<?php echo $curso['id']; ?>&lec_id=<?php echo $lec['id']; ?>"
                                class="list-group-item list-group-item-action d-flex justify-content-between align-items-center <?php echo $es_actual ? 'active bg-uabc-green text-white border-0' : ''; ?>">

                                <span style="font-size: 0.9rem;">
                                    <?php if ($esta_completada): ?>
                                        <i class="fas fa-check-circle text-success bg-white rounded-circle me-1"></i>
                                    <?php else: ?>
                                        <i class="fas fa-play-circle opacity-50 me-1"></i>
                                    <?php endif; ?>
                                    <?php echo htmlspecialchars($lec['titulo']); ?>
                                </span>

                                <?php if ($lec['tiene_tarea']): ?>
                                    <i class="fas fa-file-upload small <?php echo $es_actual ? 'text-white' : 'text-warning'; ?>" title="Requiere Tarea"></i>
                                <?php endif; ?>
                            </a>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="p-3 text-muted small text-center">El instructor aún no ha subido lecciones.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-lg-9">
            <?php if ($leccion_actual): ?>

                <div class="card shadow-sm border-0 mb-4">
                    <?php if (!empty($leccion_actual['contenido_url'])): ?>
                        <div class="ratio ratio-16x9 bg-dark rounded-top">
                            <iframe src="https://www.youtube.com/embed/<?php echo $leccion_actual['contenido_url']; ?>?rel=0" allowfullscreen></iframe>
                        </div>
                    <?php else: ?>
                        <div class="bg-dark rounded-top d-flex flex-column align-items-center justify-content-center" style="height: 350px; background: linear-gradient(rgba(6, 91, 62, 0.8), rgba(6, 91, 62, 0.9)), url('assets/Img/FondoCimarron.jpg') center/cover;">
                            <img src="assets/Img/Logo CNI.png" alt="Logo CNI" style="width: 100px; opacity: 0.8;" class="mb-3">
                            <h4 class="text-white fw-bold"><i class="fas fa-book-reader"></i> Material de Lectura / Tarea</h4>
                            <p class="text-light small">Esta lección no contiene video. Por favor lee las instrucciones debajo.</p>
                        </div>
                    <?php endif; ?>

                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h4 class="fw-bold mb-0"><?php echo htmlspecialchars($leccion_actual['titulo']); ?></h4>

                            <?php if (!$completada): ?>
                                <?php if ($leccion_actual['tiene_tarea'] == 1): ?>
                                    <span class="badge bg-warning text-dark fs-6 shadow-sm"><i class="fas fa-clock"></i> Pendiente de validación del instructor</span>
                                <?php else: ?>
                                    <form action="index.php?c=estudiante&a=marcarCompletada" method="POST">
                                        <input type="hidden" name="curso_id" value="<?php echo $curso['id']; ?>">
                                        <input type="hidden" name="leccion_id" value="<?php echo $leccion_actual['id']; ?>">
                                        <button type="submit" class="btn btn-success fw-bold shadow-sm">
                                            <i class="fas fa-check"></i> Marcar como Completada
                                        </button>
                                    </form>
                                <?php endif; ?>
                            <?php else: ?>
                                <span class="badge bg-success fs-6"><i class="fas fa-check-double"></i> Lección Completada</span>
                            <?php endif; ?>
                        </div>

                        <?php if (!empty($leccion_actual['instrucciones'])): ?>
                            <div class="mt-3 p-3 bg-light rounded border-start border-uabc-green border-4 shadow-sm" style="border-left-color: #065b3e !important;">
                                <h6 class="fw-bold text-dark mb-2"><i class="fas fa-info-circle text-uabc-green me-1" style="color: #065b3e;"></i> Instrucciones de la lección</h6>
                                <p class="mb-0 small text-secondary" style="white-space: pre-line;">
                                    <?php
                                    // Esto convierte enlaces de texto (http://...) en links clickeables automáticamente
                                    $texto = htmlspecialchars($leccion_actual['instrucciones']);
                                    $texto_con_links = preg_replace('!(((f|ht)tp(s)?://)[-a-zA-Zа-яА-Я()0-9@:%_+.~#?&;//=]+)!i', '<a href="$1" target="_blank" class="text-primary fw-bold text-decoration-none">$1</a>', $texto);
                                    echo $texto_con_links;
                                    ?>
                                </p>
                            </div>
                        <?php endif; ?>

                        <?php if (mysqli_num_rows($archivos_leccion) > 0): ?>
                            <hr>
                            <h6 class="fw-bold text-secondary"><i class="fas fa-paperclip"></i> Recursos Adicionales</h6>
                            <div class="d-flex flex-wrap gap-2 mt-2">
                                <?php while ($pdf = mysqli_fetch_assoc($archivos_leccion)): ?>
                                    <a href="<?php echo $pdf['ruta_archivo']; ?>" target="_blank" class="btn btn-outline-danger btn-sm">
                                        <i class="fas fa-file-pdf"></i> <?php echo htmlspecialchars($pdf['nombre_original']); ?>
                                    </a>
                                <?php endwhile; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="row">
                    <?php if ($leccion_actual['tiene_tarea']): ?>
                        <div class="col-md-6 mb-4">
                            <div class="card shadow-sm border-warning h-100">
                                <div class="card-header bg-warning text-dark fw-bold">
                                    <i class="fas fa-tasks"></i> Evidencia Requerida
                                </div>
                                <div class="card-body">

                                    <?php if (!empty($leccion_actual['fecha_limite'])):
                                        $fecha_limite_obj = new DateTime($leccion_actual['fecha_limite']);
                                        $hoy_obj = new DateTime();
                                        $esta_retrasada = ($hoy_obj > $fecha_limite_obj);
                                    ?>
                                        <div class="alert <?php echo $esta_retrasada ? 'alert-danger' : 'alert-info'; ?> py-2 small fw-bold shadow-sm mb-3">
                                            <i class="fas fa-clock"></i> Fecha Límite: <?php echo $fecha_limite_obj->format('d/m/Y h:i A'); ?>
                                            <?php if ($esta_retrasada && !$mi_tarea): ?>
                                                <br><span class="text-danger"><i class="fas fa-exclamation-triangle"></i> Si entregas ahora, se marcará con retraso.</span>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>

                                    <?php if ($mi_tarea): ?>
                                        <div class="text-center py-3 border-bottom mb-3">
                                            <i class="fas fa-file-check fa-3x text-success mb-2"></i>
                                            <h5 class="text-success fw-bold">¡Tarea Entregada!</h5>
                                            <p class="small text-muted mb-1">Enviada el: <?php echo date('d/m/Y h:i A', strtotime($mi_tarea['fecha_envio'])); ?></p>

                                            <span class="badge <?php echo (isset($mi_tarea['estatus_entrega']) && $mi_tarea['estatus_entrega'] == 'A tiempo') ? 'bg-success' : 'bg-danger'; ?> mb-3">
                                                <?php echo htmlspecialchars($mi_tarea['estatus_entrega'] ?? 'A tiempo'); ?>
                                            </span>
                                            <br>

                                            <a href="<?php echo $mi_tarea['archivo_ruta']; ?>" target="_blank" class="btn btn-outline-primary btn-sm w-100">
                                                <i class="fas fa-eye"></i> Ver mi archivo entregado
                                            </a>
                                        </div>

                                        <div id="zona-comentarios-tarea" class="mt-3 text-start">
                                            <h6 class="fw-bold text-dark small"><i class="fas fa-lock text-warning"></i> Comentarios Privados (Instructor)</h6>
                                            <div class="bg-white border rounded p-2 mb-2" style="max-height: 200px; overflow-y: auto;">
                                                <?php if (!empty($comentarios_tarea)): ?>
                                                    <?php foreach ($comentarios_tarea as $c):
                                                        $es_yo = ($c['usuario_id'] == $_SESSION['usuario_id']);
                                                    ?>
                                                        <div class="mb-2 p-2 rounded small <?php echo $es_yo ? 'bg-light border-start border-primary border-3 ms-4 text-end' : 'bg-light border-start border-warning border-3 me-4 text-start'; ?>">
                                                            <strong class="d-block <?php echo $es_yo ? 'text-primary' : 'text-warning text-dark'; ?>">
                                                                <?php echo $es_yo ? 'Tú' : htmlspecialchars($c['nombre'] . ' (Instructor)'); ?>
                                                                <span class="text-muted fw-normal" style="font-size:0.7rem;"><?php echo date('d/m H:i', strtotime($c['fecha'])); ?></span>
                                                            </strong>
                                                            <?php echo nl2br(htmlspecialchars($c['comentario'])); ?>
                                                        </div>
                                                    <?php endforeach; ?>
                                                <?php else: ?>
                                                    <p class="text-muted small text-center my-2">No hay comentarios sobre esta tarea.</p>
                                                <?php endif; ?>
                                            </div>

                                            <form action="index.php?c=estudiante&a=enviarFeedbackTarea" method="POST">
                                                <input type="hidden" name="curso_id" value="<?php echo $curso['id']; ?>">
                                                <input type="hidden" name="leccion_id" value="<?php echo $leccion_actual['id']; ?>">
                                                <input type="hidden" name="tarea_id" value="<?php echo $mi_tarea['id']; ?>">
                                                <div class="input-group">
                                                    <input type="text" name="comentario" class="form-control form-control-sm" placeholder="Añadir respuesta..." required>
                                                    <button class="btn btn-warning btn-sm fw-bold text-dark" type="submit"><i class="fas fa-paper-plane"></i></button>
                                                </div>
                                            </form>
                                        </div>

                                    <?php else: ?>
                                        <p class="small text-muted">El instructor requiere que subas un archivo como evidencia de esta lección.</p>
                                        <form action="index.php?c=estudiante&a=subirTarea" method="POST" enctype="multipart/form-data">
                                            <input type="hidden" name="curso_id" value="<?php echo $curso['id']; ?>">
                                            <input type="hidden" name="leccion_id" value="<?php echo $leccion_actual['id']; ?>">

                                            <div class="mb-3">
                                                <input class="form-control" type="file" name="archivo_tarea" required accept=".pdf,.doc,.docx,.jpg,.png,.zip">
                                            </div>
                                            <button type="submit" class="btn btn-warning w-100 fw-bold">
                                                <i class="fas fa-upload"></i> Enviar Tarea
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="col-md-<?php echo $leccion_actual['tiene_tarea'] ? '6' : '12'; ?> mb-4" id="zona-foro-dudas">
                        <div class="card shadow-sm h-100 border-0">
                            <div class="card-header bg-light fw-bold">
                                <i class="fas fa-comments"></i> Foro de Dudas
                            </div>
                            <div class="card-body" style="max-height: 300px; overflow-y: auto; background: #f8f9fa;">
                                <?php if (mysqli_num_rows($lista_comentarios) > 0): ?>
                                    <?php while ($coment = mysqli_fetch_assoc($lista_comentarios)):
                                        $es_maestro = ($coment['rol'] == 'instructor' || $coment['rol'] == 'admin');
                                    ?>
                                        <div class="mb-3 p-2 rounded <?php echo $es_maestro ? 'bg-white border-start border-primary border-4 shadow-sm' : 'bg-white shadow-sm'; ?>">
                                            <div class="d-flex justify-content-between">
                                                <strong class="<?php echo $es_maestro ? 'text-primary' : 'text-dark'; ?>">
                                                    <?php echo htmlspecialchars($coment['nombre']); ?>
                                                    <?php if ($es_maestro): ?><i class="fas fa-check-circle small" title="Instructor"></i><?php endif; ?>
                                                </strong>
                                                <small class="text-muted" style="font-size:0.75rem;"><?php echo date('d/m H:i', strtotime($coment['fecha'])); ?></small>
                                            </div>
                                            <p class="mb-0 mt-1 small"><?php echo nl2br(htmlspecialchars($coment['comentario'])); ?></p>
                                        </div>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <p class="text-center text-muted small py-3">Aún no hay dudas. Sé el primero en comentar.</p>
                                <?php endif; ?>
                            </div>

                            <div class="card-footer bg-white">
                                <form action="index.php?c=estudiante&a=comentar" method="POST">
                                    <input type="hidden" name="curso_id" value="<?php echo $curso['id']; ?>">
                                    <input type="hidden" name="leccion_id" value="<?php echo $leccion_actual['id']; ?>">
                                    <div class="input-group">
                                        <input type="text" name="comentario" class="form-control form-control-sm" placeholder="Escribe una duda..." required>
                                        <button class="btn btn-uabc-green btn-sm text-white" type="submit" style="background-color: #065b3e;">Enviar</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

            <?php else: ?>
                <div class="card shadow-sm border-0 text-center py-5 h-100 d-flex justify-content-center align-items-center bg-light">
                    <img src="<?php echo !empty($curso['imagen']) ? $curso['imagen'] : 'assets/Img/Logo CNI.png'; ?>" style="width: 150px; opacity: 0.5;" class="mb-4 rounded">
                    <h3 class="text-muted fw-bold">Bienvenido al curso</h3>
                    <p class="text-secondary">Selecciona una lección del menú lateral izquierdo para comenzar a aprender.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>