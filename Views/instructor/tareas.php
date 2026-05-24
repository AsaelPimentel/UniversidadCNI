<div class="container mt-4">
    <h2 class="mb-4 text-uabc-green fw-bold"><i class="fas fa-tasks"></i> Revisión de Evidencias</h2>

    <?php if (isset($_GET['msj']) && $_GET['msj'] == 'feedback_ok'): ?>
        <div class="alert alert-success alert-dismissible fade show shadow-sm">
            <i class="fas fa-check-circle"></i> Comentario privado enviado al alumno exitosamente.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm border-0 mb-4 bg-light">
        <div class="card-body py-3">
            <form method="GET" action="index.php" class="row align-items-center g-2">
                <input type="hidden" name="c" value="instructor">
                <input type="hidden" name="a" value="verTareas">

                <div class="col-md-5">
                    <label class="form-label small fw-bold text-secondary mb-1">Filtrar por Curso:</label>
                    <select name="curso_filter" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="todos" <?php echo ($curso_seleccionado == 'todos') ? 'selected' : ''; ?>>--- Todos mis cursos creados ---</option>
                        <?php while ($c = mysqli_fetch_assoc($query_cursos)): ?>
                            <option value="<?php echo $c['id']; ?>" <?php echo ($curso_seleccionado == $c['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($c['titulo']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped mb-0 align-middle">
                    <thead class="table-dark text-center">
                        <tr>
                            <th>Alumno</th>
                            <th>Materia / Curso</th>
                            <th>Lección / Tema</th>
                            <th>Estatus</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="text-center">
                        <?php if (count($tareas) > 0): ?>
                            <?php foreach ($tareas as $tarea): ?>
                                <tr>
                                    <td class="fw-bold text-dark"><?php echo htmlspecialchars($tarea['alumno_nombre']); ?></td>
                                    <td><span class="badge bg-secondary"><?php echo htmlspecialchars($tarea['curso_titulo']); ?></span></td>
                                    <td class="text-start small"><?php echo htmlspecialchars($tarea['leccion_titulo']); ?><br><small class="text-muted"><?php echo date('d/m/Y H:i', strtotime($tarea['fecha_envio'])); ?></small></td>

                                    <td>
                                        <span class="badge <?php echo (isset($tarea['estatus_entrega']) && $tarea['estatus_entrega'] == 'A tiempo') ? 'bg-success' : 'bg-danger'; ?>">
                                            <?php echo htmlspecialchars($tarea['estatus_entrega'] ?? 'A tiempo'); ?>
                                        </span>
                                    </td>

                                    <td>
                                        <div class="d-flex justify-content-center gap-2">
                                            <button type="button" class="btn btn-sm btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#modalPrevisualizar" data-url="<?php echo htmlspecialchars($tarea['archivo_ruta']); ?>" title="Previsualizar PDF">
                                                <i class="fas fa-eye"></i>
                                            </button>

                                            <button type="button" class="btn btn-sm btn-warning shadow-sm position-relative" data-bs-toggle="modal" data-bs-target="#modalFeedback<?php echo $tarea['tarea_id']; ?>" title="Comentarios Privados">
                                                <i class="fas fa-comments text-dark"></i>
                                                <?php if (count($tarea['comentarios']) > 0): ?>
                                                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.65rem;">
                                                        <?php echo count($tarea['comentarios']); ?>
                                                    </span>
                                                <?php endif; ?>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">
                                    <i class="fas fa-folder-open fa-3x mb-2 opacity-25"></i>
                                    <p class="mb-0">No se encontraron tareas entregadas bajo el filtro seleccionado.</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalPrevisualizar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title"><i class="fas fa-file-pdf me-2 text-danger"></i> Previsualización de Evidencia</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0 bg-light">
                <embed id="pdfEmbed" src="" type="application/pdf" width="100%" height="600px" style="border: none;" />
            </div>
        </div>
    </div>
</div>

<?php foreach ($tareas as $tarea): ?>
    <div class="modal fade" id="modalFeedback<?php echo $tarea['tarea_id']; ?>" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title fw-bold"><i class="fas fa-user-graduate me-2"></i> Feedback: <?php echo htmlspecialchars($tarea['alumno_nombre']); ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body bg-light">

                    <div style="max-height: 300px; overflow-y: auto;" class="mb-3 p-2 bg-white rounded border">
                        <?php if (count($tarea['comentarios']) > 0): ?>
                            <?php foreach ($tarea['comentarios'] as $c):
                                $es_yo = ($c['rol'] == 'instructor' || $c['rol'] == 'admin');
                            ?>
                                <div class="mb-2 p-2 rounded small <?php echo $es_yo ? 'bg-light border-start border-warning border-3 ms-4 text-end' : 'bg-light border-start border-primary border-3 me-4 text-start'; ?>">
                                    <strong class="d-block <?php echo $es_yo ? 'text-dark' : 'text-primary'; ?>">
                                        <?php echo $es_yo ? 'Tú (Instructor)' : htmlspecialchars($c['nombre']); ?>
                                        <span class="text-muted fw-normal" style="font-size:0.7rem;"><?php echo date('d/m H:i', strtotime($c['fecha'])); ?></span>
                                    </strong>
                                    <?php echo nl2br(htmlspecialchars($c['comentario'])); ?>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-muted small text-center mb-0 py-3">Aún no hay comentarios privados en esta tarea.</p>
                        <?php endif; ?>
                    </div>

                    <form action="index.php?c=instructor&a=enviarFeedback" method="POST">
                        <input type="hidden" name="tarea_id" value="<?php echo $tarea['tarea_id']; ?>">
                        <input type="hidden" name="curso_filter" value="<?php echo htmlspecialchars($curso_seleccionado); ?>">
                        <div class="input-group">
                            <input type="text" name="comentario" class="form-control" placeholder="Escribe un comentario o retroalimentación..." required>
                            <button class="btn btn-warning fw-bold text-dark" type="submit"><i class="fas fa-paper-plane"></i></button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
<?php endforeach; ?>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        var modal = document.getElementById('modalPrevisualizar');
        modal.addEventListener('show.bs.modal', function(event) {
            var button = event.relatedTarget;
            var urlArchivo = button.getAttribute('data-url');
            modal.querySelector('#pdfEmbed').setAttribute('src', urlArchivo);
        });
        modal.addEventListener('hidden.bs.modal', function() {
            modal.querySelector('#pdfEmbed').setAttribute('src', '');
        });
    });
</script>