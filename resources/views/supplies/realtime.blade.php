<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Dashboard de Consumo en Tiempo Real
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-semibold mb-4">
                        Curva de Carga Horaria para Suministro: {{ $supply->supply_point_identifier }}
                    </h3>
                    <p class="text-sm text-gray-600 mb-6">Mostrando las últimas 72 horas de consumo.</p>
                    
                    <div style="height: 400px;">
                        <canvas id="realtimeChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns/dist/chartjs-adapter-date-fns.bundle.min.js"></script> <!-- Adapter para fechas -->

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const ctx = document.getElementById('realtimeChart');
            if (ctx) {
                const chartData = @json($chartData);
                new Chart(ctx, {
                    type: 'line', // Gráfico de líneas
                    data: {
                        labels: chartData.labels,
                        datasets: [{
                            label: 'Consumo (kWh)',
                            data: chartData.data,
                            borderColor: 'rgba(75, 192, 192, 1)',
                            backgroundColor: 'rgba(75, 192, 192, 0.2)',
                            fill: true,
                            tension: 0.1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            x: {
                                type: 'time',
                                time: {
                                    unit: 'hour',
                                    tooltipFormat: 'dd/MM/yyyy HH:mm',
                                    displayFormats: {
                                        hour: 'HH:mm'
                                    }
                                },
                                title: {
                                    display: true,
                                    text: 'Hora del Día'
                                }
                            },
                            y: {
                                beginAtZero: true,
                                title: {
                                    display: true,
                                    text: 'Consumo (kWh)'
                                }
                            }
                        }
                    }
                });
            }
        });
    </script>
</x-app-layout>