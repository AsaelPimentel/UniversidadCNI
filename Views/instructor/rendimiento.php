<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="text-uabc-green fw-bold"><i class="fas fa-chart-line"></i> Rendimiento Analítico Escolar</h2>
        
        <form method="GET" action="index.php" class="d-flex align-items-center">
            <input type="hidden" name="c" value="instructor">
            <input type="hidden" name="a" value="rendimiento">
            <select name="curso_id" class="form-select shadow-sm me-2" onchange="this.form.submit()">
                <?php foreach($cursos as $c): ?>
                    <option value="<?php echo $c['id']; ?>" <?php echo ($id_curso_sel == $c['id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($c['titulo']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </form>
    </div>

    <?php if ($id_curso_sel > 0 && isset($curso_actual)): ?>
        <div class="row">
            <div class="col-lg-8 mb-4">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header bg-white fw-bold">
                        <i class="fas fa-filter text-primary"></i> Gráfica: Embudo de Retención de Alumnos
                    </div>
                    <div class="card-body p-4 d-flex flex-column justify-content-center">
                        <?php if(!empty($labels_funnel)): ?>
                            <div style="position: relative; height:320px; width:100%;">
                                <canvas id="chartEmbudo"></canvas>
                            </div>
                        <?php else: ?>
                            <p class="text-muted text-center py-5">No hay lecciones dadas de alta en esta materia para graficar.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 mb-4">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header bg-uabc-green text-white fw-bold">
                        <i class="fas fa-exclamation-triangle"></i> Alertas: Zonas de Fricción
                    </div>
                    <div class="card-body">
                        <p class="small text-muted mb-3">Lecciones ordenadas de mayor a menor según el número de dudas/comentarios registrados por los alumnos en el foro lateral.</p>
                        
                        <div class="list-group list-group-flush">
                            <?php if (isset($res_friccion) && mysqli_num_rows($res_friccion) > 0): ?>
                                <?php while ($fricc = mysqli_fetch_assoc($res_friccion)): ?>
                                    <div class="list-group-item d-flex justify-content-between align-items-center bg-light mb-2 rounded border shadow-sm">
                                        <div class="text-truncate me-2" style="max-width: 75%;">
                                            <span class="fw-bold text-dark small d-block text-truncate"><?php echo htmlspecialchars($fricc['titulo']); ?></span>
                                            <small class="text-muted">Revisar explicaciones</small>
                                        </div>
                                        <span class="badge bg-danger p-2 fs-6" title="Comentarios en foro">
                                            <?php echo $fricc['dudas']; ?> <i class="far fa-comments small"></i>
                                        </span>
                                    </div>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <div class="text-center py-5 text-muted small">
                                    <i class="fas fa-smile fa-3x mb-2 text-success opacity-50"></i>
                                    <p class="mb-0">¡Excelente! No hay lecciones con dudas acumuladas o sin responder.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php if(!empty($labels_funnel)): ?>
            <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
            <script>
                document.addEventListener("DOMContentLoaded", function() {
                    const ctx = document.getElementById('chartEmbudo').getContext('2d');
                    new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: <?php echo json_encode($labels_funnel); ?>,
                            datasets: [{
                                label: 'Alumnos que completaron la lección',
                                data: <?php echo json_encode($data_funnel); ?>,
                                backgroundColor: 'rgba(6, 91, 62, 0.85)',
                                borderColor: 'rgba(6, 91, 62, 1)',
                                borderWidth: 1,
                                borderRadius: 5,
                                barPercentage: 0.6
                            }]
                        },
                        options: {
                            indexAxis: 'y', // Hace que la gráfica de barras sea horizontal simulando el embudo
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: { display: false }
                            },
                            scales: {
                                x: { beginAtZero: true, ticks: { stepSize: 1 } },
                                y: { grid: { display: false } }
                            }
                        }
                    });
                });
            </script>
        <?php endif; ?>

    <?php else: ?>
        <div class="alert alert-info text-center py-4">
            <i class="fas fa-info-circle fa-2x mb-2"></i>
            <p class="mb-0">No se encontraron cursos activos asignados a tu cuenta para auditar métricas.</p>
        </div>
    <?php endif; ?>
</div>