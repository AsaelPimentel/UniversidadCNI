<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
    .hover-shadow:hover {
        transform: translateY(-5px);
        box-shadow: 0 1rem 3rem rgba(0, 0, 0, .175) !important;
    }
    .transition-all {
        transition: all .3s ease;
    }

    /* ESTILOS DEL FILTRO DE FECHAS (Premium UI) */
    .date-filter-form {
        background: #ffffff;
        border: 1px solid #cbd5e1;
        padding: 0.4rem 0.8rem;
        border-radius: 10px;
        display: flex;
        align-items: center;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.02);
        transition: all 0.3s ease;
        cursor: pointer;
    }
    .date-filter-form:hover {
        border-color: #065b3e; /* UABC Green */
        box-shadow: 0 4px 12px rgba(6, 91, 62, 0.1);
    }
    .date-filter-form .fa-calendar {
        color: #065b3e !important;
        font-size: 1.05rem;
        transition: transform 0.3s ease;
    }
    .date-filter-form:hover .fa-calendar {
        transform: scale(1.1);
    }
    .date-filter-form select {
        border: none; background: transparent; color: #475569; font-weight: 600; font-size: 0.9rem; cursor: pointer; outline: none; box-shadow: none;
    }
    .custom-option { font-weight: 500; color: #334155; background-color: #ffffff; padding: 10px; }
    .custom-option:checked { background-color: #065b3e !important; color: #ffffff !important; font-weight: 700; }
</style>

<div class="container mt-4 mb-5">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="text-uabc-green fw-bold m-0"><i class="fas fa-chart-bar"></i> Estadísticas Globales del Sistema</h2>
        
        <form id="filtroDashboard" method="GET" action="index.php" class="m-0">
            <input type="hidden" name="c" value="admin">
            <input type="hidden" name="a" value="estadisticas">
            <div class="date-filter-form">
                <i class="fa-regular fa-calendar text-muted ms-2"></i>
                <select name="rango" class="form-select form-select-sm" onchange="document.getElementById('filtroDashboard').submit();">
                    <option value="7d" class="custom-option" <?php echo ($rango == '7d') ? 'selected' : ''; ?>>Últimos 7 Días</option>
                    <option value="1m" class="custom-option" <?php echo ($rango == '1m') ? 'selected' : ''; ?>>Último Mes</option>
                    <option value="6m" class="custom-option" <?php echo ($rango == '6m') ? 'selected' : ''; ?>>Últimos 6 Meses</option>
                    <option value="1y" class="custom-option" <?php echo ($rango == '1y') ? 'selected' : ''; ?>>Último Año</option>
                </select>
            </div>
        </form>
    </div>

    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="card border-0 shadow-sm bg-primary text-white h-100 transition-all hover-shadow">
                <div class="card-body text-center d-flex flex-column justify-content-center py-4">
                    <i class="fas fa-users fa-3x mb-3 opacity-50"></i>
                    <h5 class="card-title fw-bold">Usuarios en el Sistema</h5>
                    <p class="card-text fw-bold mb-0" style="font-size: 3rem;"><?php echo $total_usuarios; ?></p>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-4">
            <div class="card border-0 shadow-sm bg-success text-white h-100 transition-all hover-shadow">
                <div class="card-body text-center d-flex flex-column justify-content-center py-4">
                    <i class="fas fa-book fa-3x mb-3 opacity-50"></i>
                    <h5 class="card-title fw-bold">Cursos Disponibles</h5>
                    <p class="card-text fw-bold mb-0" style="font-size: 3rem;"><?php echo $total_cursos; ?></p>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-4">
            <div class="card border-0 shadow-sm bg-warning text-dark h-100 transition-all hover-shadow">
                <div class="card-body text-center d-flex flex-column justify-content-center py-4">
                    <i class="fas fa-award fa-3x mb-3 opacity-50"></i>
                    <h5 class="card-title fw-bold">Certificados Emitidos</h5>
                    <p class="card-text fw-bold mb-0" style="font-size: 3rem;"><?php echo $total_certificados; ?></p>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-2">
        <div class="col-lg-12 mb-4">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                    <h6 class="fw-bold m-0 text-dark"><i class="fa-solid fa-chart-line text-success me-2"></i> Nuevos Usuarios (Registros)</h6>
                    <span class="badge bg-light text-dark border fw-normal">
                        <?php
                        if ($rango == '7d') echo "Viendo: Por Día (Últimos 7 Días)";
                        elseif ($rango == '1m') echo "Viendo: Por Día (Último mes)";
                        else echo "Viendo: Por Meses";
                        ?>
                    </span>
                </div>
                <div class="card-body">
                    <div style="height: 300px; width: 100%;">
                        <canvas id="lineChartUsuarios"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-12 mb-4">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3">
                    <h6 class="fw-bold m-0 text-dark"><i class="fa-solid fa-cubes-stacked text-warning me-2"></i> Actividad de Interacción</h6>
                </div>
                <div class="card-body">
                    <div style="height: 350px; width: 100%;">
                        <canvas id="barChartActividad"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-2">
        <div class="col-md-6 mb-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white fw-bold py-3">
                    <i class="fas fa-chart-pie text-uabc-green me-2"></i> Distribución de Usuarios por Rol
                </div>
                <div class="card-body d-flex justify-content-center align-items-center" style="min-height: 350px;">
                    <?php if (!empty($data_roles)): ?>
                        <div style="position: relative; height:300px; width:100%;">
                            <canvas id="chartRoles"></canvas>
                        </div>
                    <?php else: ?>
                        <p class="text-muted small">No hay datos suficientes para graficar.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-md-6 mb-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white fw-bold py-3">
                    <i class="fas fa-info-circle text-primary me-2"></i> Información del Sistema
                </div>
                <div class="card-body d-flex flex-column justify-content-center text-center">
                    <img src="Assets/Img/Logo CNI.png" alt="Logo" style="width: 100px;" class="mx-auto mb-3">
                    <h5 class="text-secondary fw-bold">Universidad Autónoma de Baja California</h5>
                    <hr>
                    <div class="row text-start px-3">
                        <div class="col-6">
                            <p class="mb-1 small text-muted">Versión DB:</p>
                            <p class="fw-bold small"><?php echo htmlspecialchars($info_sistema['version']); ?></p>
                        </div>
                        <div class="col-6">
                            <p class="mb-1 small text-muted">Peso de la BD:</p>
                            <p class="fw-bold small"><?php echo htmlspecialchars($info_sistema['size']); ?> MB</p>
                        </div>
                    </div>
                    <p class="text-muted small mt-3">
                        <i class="fas fa-check-circle text-success"></i> Sistema conectado correctamente.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        Chart.defaults.font.family = "'Inter', sans-serif";

        // ==========================================
        // 1. GRÁFICO DE LÍNEA: Nuevos Usuarios
        // ==========================================
        const ctxLine = document.getElementById('lineChartUsuarios');
        if (ctxLine) {
            let contextLine = ctxLine.getContext('2d');
            let gradientGreen = contextLine.createLinearGradient(0, 0, 0, 400);
            gradientGreen.addColorStop(0, 'rgba(6, 91, 62, 0.5)'); // Verde UABC
            gradientGreen.addColorStop(1, 'rgba(6, 91, 62, 0.0)');

            new Chart(contextLine, {
                type: 'line',
                data: {
                    labels: <?php echo json_encode($labels_meses); ?>,
                    datasets: [{
                        label: 'Usuarios Registrados',
                        data: <?php echo json_encode($data_usuarios); ?>,
                        borderColor: '#065b3e',
                        backgroundColor: gradientGreen,
                        borderWidth: 3,
                        pointBackgroundColor: '#ffffff',
                        pointBorderColor: '#065b3e',
                        pointBorderWidth: 2,
                        pointRadius: 4,
                        fill: true,
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        x: { grid: { display: false } },
                        y: {
                            grid: { borderDash: [5, 5], color: '#e2e8f0' },
                            beginAtZero: true,
                            ticks: { stepSize: 1 }
                        }
                    }
                }
            });
        }

        // ==========================================
        // 2. GRÁFICO BARRAS APILADAS: Actividad Diaria
        // ==========================================
        const ctxBar = document.getElementById('barChartActividad');
        if (ctxBar) {
            new Chart(ctxBar.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: <?php echo json_encode($labels_actividad); ?>,
                    datasets: [
                        { label: 'Lecciones Completadas', data: <?php echo json_encode($data_lec); ?>, backgroundColor: '#065b3e', borderRadius: 4 },
                        { label: 'Tareas Enviadas', data: <?php echo json_encode($data_tar); ?>, backgroundColor: '#e5a93b', borderRadius: 4 }, // Dorado UABC
                        { label: 'Comentarios', data: <?php echo json_encode($data_com); ?>, backgroundColor: '#17a2b8', borderRadius: 4 }
                    ]
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    plugins: {
                        tooltip: { mode: 'index', intersect: false }
                    },
                    scales: {
                        x: { stacked: true, grid: { display: false } },
                        y: {
                            stacked: true,
                            grid: { borderDash: [5, 5], color: '#e2e8f0' },
                            beginAtZero: true,
                            ticks: { stepSize: 1 }
                        }
                    }
                }
            });
        }

        // ==========================================
        // 3. GRÁFICO DOUGHNUT: Distribución de Roles
        // ==========================================
        const ctxRoles = document.getElementById('chartRoles');
        if (ctxRoles) {
            new Chart(ctxRoles.getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: <?php echo json_encode($labels_roles); ?>,
                    datasets: [{
                        data: <?php echo json_encode($data_roles); ?>,
                        backgroundColor: [
                            'rgba(6, 91, 62, 0.85)',   // Verde UABC
                            'rgba(229, 169, 59, 0.85)', // Dorado UABC
                            'rgba(54, 162, 235, 0.85)', // Azul
                            'rgba(201, 203, 207, 0.85)' // Gris
                        ],
                        hoverOffset: 4
                    }]
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    plugins: { legend: { position: 'bottom' } },
                    cutout: '65%'
                }
            });
        }
    });
</script>