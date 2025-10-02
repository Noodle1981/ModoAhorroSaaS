<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\EquipmentUsageSnapshot;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class HistoricalAnalysisService
{
    /**
     * Genera un informe mensual de consumo real vs. explicado para la compañía del usuario autenticado.
     *
     * @return array
     */
    public function generateMonthlyReport(): array
    {
        $companyId = Auth::user()->company_id;
        $dbDriver = DB::connection()->getDriverName();

        $monthExpression = $this->getMonthExpression('invoice_date', $dbDriver);

        // 1. Obtener el consumo real de las facturas, agrupado por mes.
        $realConsumption = Invoice::whereHas('contract.supply.entity', function ($query) use ($companyId) {
            $query->where('company_id', $companyId);
        })
        ->select(
            DB::raw("$monthExpression as month"),
            DB::raw('sum(total_energy_consumed_kwh) as total_real_kwh')
        )
        ->groupBy('month')
        ->orderBy('month', 'asc')
        ->get()
        ->keyBy('month');

        $monthExpression = $this->getMonthExpression('start_date', $dbDriver);

        // 2. Obtener el consumo explicado de los snapshots, agrupado por mes.
        $explainedConsumption = EquipmentUsageSnapshot::whereHas('invoice.contract.supply.entity', function ($query) use ($companyId) {
            $query->where('company_id', $companyId);
        })
        ->select(
            DB::raw("$monthExpression as month"),
            DB::raw('sum(calculated_kwh_period) as total_explained_kwh')
        )
        ->groupBy('month')
        ->orderBy('month', 'asc')
        ->get()
        ->keyBy('month');

        // 3. Unir y formatear los datos para el gráfico.
        $allMonths = collect(array_keys($realConsumption->toArray()))
            ->merge(array_keys($explainedConsumption->toArray()))
            ->unique()
            ->sort(); 

        $labels = $allMonths->map(function ($monthStr) {
            return Carbon::createFromFormat('Y-m', $monthStr)->format('M Y');
        });

        $realData = $allMonths->map(function ($month) use ($realConsumption) {
            return $realConsumption->get($month)->total_real_kwh ?? 0;
        });

        $explainedData = $allMonths->map(function ($month) use ($explainedConsumption) {
            return $explainedConsumption->get($month)->total_explained_kwh ?? 0;
        });

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Consumo Real (Facturas)',
                    'data' => $realData,
                    'backgroundColor' => 'rgba(255, 99, 132, 0.5)',
                    'borderColor' => 'rgba(255, 99, 132, 1)',
                    'borderWidth' => 1
                ],
                [
                    'label' => 'Consumo Explicado (Inventario)',
                    'data' => $explainedData,
                    'backgroundColor' => 'rgba(54, 162, 235, 0.5)',
                    'borderColor' => 'rgba(54, 162, 235, 1)',
                    'borderWidth' => 1
                ]
            ]
        ];
    }

    /**
     * Devuelve la expresión SQL correcta para extraer el año y el mes de una columna de fecha,
     * dependiendo del motor de base de datos.
     *
     * @param string $column
     * @param string $driver
     * @return string
     */
    private function getMonthExpression(string $column, string $driver): string
    {
        switch ($driver) {
            case 'mysql':
                return "DATE_FORMAT($column, '%Y-%m')";
            case 'pgsql':
                return "TO_CHAR($column, 'YYYY-MM')";
            case 'sqlsrv':
                return "FORMAT($column, 'yyyy-MM')";
            case 'sqlite':
            default:
                return "strftime('%Y-%m', $column)";
        }
    }
}
