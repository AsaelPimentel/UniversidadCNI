<div class="container mt-4 mb-5">
    <h2 class="mb-4 text-uabc-green fw-bold"><i class="fas fa-chart-bar"></i> Estadísticas Globales del Sistema</h2>

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

    <div class="row mt-3">
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
                    <img src="Assets\Img\Logo CNI.png" alt="Logo" style="width: 100px; " class="mx-auto mb-3">
                    <h5 class="text-secondary fw-bold">Universidad Autónoma de Baja California</h5>
                    <hr>
                    <div class="row text-start px-3">
                        <div class="col-6">
                            <p class="mb-1 small text-muted">Versión DB:</p>
                            <p class="fw-bold small"><?php echo $info_sistema['version']; ?></p>
                        </div>
                        <div class="col-6">
                            <p class="mb-1 small text-muted">Peso de la BD:</p>
                            <p class="fw-bold small"><?php echo $info_sistema['size']; ?> MB</p>
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

<style>
    .hover-shadow:hover {
        transform: translateY(-5px);
        box-shadow: 0 1rem 3rem rgba(0, 0, 0, .175) !important;
    }

    .transition-all {
        transition: all .3s ease;
    }
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Datos inyectados desde PHP
        const labelsRoles = <?php echo json_encode($labels_roles); ?>;
        const dataRoles = <?php echo json_encode($data_roles); ?>;

        // Renderizado de Gráfica Doughnut
        if (document.getElementById('chartRoles')) {
            const ctxRoles = document.getElementById('chartRoles').getContext('2d');
            new Chart(ctxRoles, {
                type: 'doughnut',
                data: {
                    labels: labelsRoles,
                    datasets: [{
                        data: dataRoles,
                        backgroundColor: [
                            'rgba(6, 91, 62, 0.85)', // Verde UABC (Estudiantes/Admin)
                            'rgba(229, 169, 59, 0.85)', // Dorado UABC (Maestros)
                            'rgba(54, 162, 235, 0.85)', // Azul
                            'rgba(201, 203, 207, 0.85)' // Gris extra
                        ],
                        hoverOffset: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    },
                    cutout: '65%' // Hace que el dona sea más delgada y elegante
                }
            });
        }
    });
</script>