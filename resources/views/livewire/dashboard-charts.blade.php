<div class="row" wire:poll.30000ms="actualizar">
    
    {{-- GRÁFICO 1: Salidas vs Vuelos (últimos 7 días) --}}
    <div class="col-md-6">
        <div class="card card-outline card-info">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-chart-line mr-1"></i> Actividad Semanal
                </h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" wire:click="actualizar">
                        <i class="fas fa-sync-alt"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <canvas id="actividadChart" style="min-height: 250px; height: 250px; max-height: 250px;"></canvas>
            </div>
        </div>
    </div>

    {{-- GRÁFICO 2: Conductores por Estado --}}
    <div class="col-md-6">
        <div class="card card-outline card-success">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-chart-pie mr-1"></i> Estado de Conductores
                </h3>
            </div>
            <div class="card-body">
                <canvas id="conductoresChart" style="min-height: 250px; height: 250px; max-height: 250px;"></canvas>
            </div>
        </div>
    </div>

    {{-- GRÁFICO 3: Vehículos Hoy --}}
    <div class="col-md-6">
        <div class="card card-outline card-warning">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-truck mr-1"></i> Vehículos en Ruta (Hoy)
                </h3>
            </div>
            <div class="card-body">
                <canvas id="vehiculosChart" style="min-height: 250px; height: 250px; max-height: 250px;"></canvas>
            </div>
        </div>
    </div>

    {{-- GRÁFICO 4: Novedades por Tipo --}}
    <div class="col-md-6">
        <div class="card card-outline card-danger">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-clipboard-list mr-1"></i> Novedades por Tipo (Mes)
                </h3>
            </div>
            <div class="card-body">
                <canvas id="novedadesChart" style="min-height: 250px; height: 250px; max-height: 250px;"></canvas>
            </div>
        </div>
    </div>

</div>

@push('js')
<script>
document.addEventListener('DOMContentLoaded', function() {
    
    // Datos desde Livewire
    const actividadData = @json($chartData['salidasPorDia']);
    const vuelosData = @json($chartData['vuelosPorDia']);
    const labels7dias = @json($chartData['labels7dias']);
    
    const conductoresData = @json($chartData['conductoresPorEstado']);
    
    const vehiculosData = @json($chartData['vehiculosHoy']);
    
    const novedadesData = @json($chartData['novedadesPorTipo']);
    const novedadesLabels = Object.keys(novedadesData);
    const novedadesValues = Object.values(novedadesData);

    // 1. GRÁFICO DE ACTIVIDAD SEMANAL (Líneas)
    const ctxActividad = document.getElementById('actividadChart');
    if (ctxActividad) {
        new Chart(ctxActividad, {
            type: 'line',
            data: {
                labels: labels7dias,
                datasets: [
                    {
                        label: 'Salidas',
                        data: actividadData,
                        borderColor: 'rgb(54, 162, 235)',
                        backgroundColor: 'rgba(54, 162, 235, 0.1)',
                        tension: 0.3,
                        fill: true
                    },
                    {
                        label: 'Vuelos',
                        data: vuelosData,
                        borderColor: 'rgb(255, 206, 86)',
                        backgroundColor: 'rgba(255, 206, 86, 0.1)',
                        tension: 0.3,
                        fill: true
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });
    }

    // 2. GRÁFICO DE CONDUCTORES (Doughnut)
    const ctxConductores = document.getElementById('conductoresChart');
    if (ctxConductores) {
        new Chart(ctxConductores, {
            type: 'doughnut',
            data: {
                labels: ['Activos', 'Inactivos'],
                datasets: [{
                    data: [conductoresData.activos, conductoresData.inactivos],
                    backgroundColor: [
                        'rgb(75, 192, 192)',
                        'rgb(255, 99, 132)'
                    ],
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                    }
                }
            }
        });
    }

    // 3. GRÁFICO DE VEHÍCULOS HOY (Bar)
    const ctxVehiculos = document.getElementById('vehiculosChart');
    if (ctxVehiculos) {
        new Chart(ctxVehiculos, {
            type: 'bar',
            data: {
                labels: ['En Ruta', 'Finalizados'],
                datasets: [{
                    label: 'Cantidad',
                    data: [vehiculosData.en_ruta, vehiculosData.finalizados],
                    backgroundColor: [
                        'rgba(255, 159, 64, 0.8)',
                        'rgba(75, 192, 192, 0.8)'
                    ],
                    borderColor: [
                        'rgb(255, 159, 64)',
                        'rgb(75, 192, 192)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });
    }

    // 4. GRÁFICO DE NOVEDADES (Horizontal Bar)
    const ctxNovedades = document.getElementById('novedadesChart');
    if (ctxNovedades && novedadesLabels.length > 0) {
        new Chart(ctxNovedades, {
            type: 'bar',
            data: {
                labels: novedadesLabels,
                datasets: [{
                    label: 'Novedades',
                    data: novedadesValues,
                    backgroundColor: 'rgba(255, 99, 132, 0.8)',
                    borderColor: 'rgb(255, 99, 132)',
                    borderWidth: 1
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });
    }
});
</script>
@endpush
