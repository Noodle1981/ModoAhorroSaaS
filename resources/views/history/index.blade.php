<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Historial de Consumo
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-semibold mb-4">Evolución del Consumo Mensual (Real vs. Explicado)</h3>
                    
                    @if (empty($chartData['labels']))
                        <p>Aún no tienes suficientes datos para generar un historial. Carga algunas facturas y confirma el uso de tu inventario para empezar.</p>
                    @else
                        <!-- El lienzo donde se dibujará el gráfico -->
                        <div style="width: 100%; height: 400px;">
                            <canvas id="consumptionChart"></canvas>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Incluimos la librería Chart.js desde un CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Solo intentamos crear el gráfico si el canvas existe.
            const ctx = document.getElementById('consumptionChart');
            if (ctx) {
                // Pasamos los datos de PHP a JavaScript de forma segura con la directiva @json
                const chartData = @json($chartData);

                new Chart(ctx, {
                    type: 'bar', // Tipo de gráfico: barras
                    data: {
                        labels: chartData.labels,
                        datasets: chartData.datasets
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true,
                                title: {
                                    display: true,
                                    text: 'Consumo (kWh)'
                                }
                            },
                            x: {
                                title: {
                                    display: true,
                                    text: 'Mes'
                                }
                            }
                        }
                    }
                });
            }
        });
    </script>
</x-app-layout>