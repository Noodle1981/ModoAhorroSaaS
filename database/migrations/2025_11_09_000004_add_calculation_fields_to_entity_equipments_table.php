<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCalculationFieldsToEntityEquipmentsTable extends Migration
{
    public function up()
    {
        Schema::table('entity_equipment', function (Blueprint $table) {
            $table->float('factor_carga')->nullable()->after('tipo_de_proceso');
            $table->float('eficiencia')->nullable()->after('factor_carga');
            $table->float('energia_consumida_wh')->nullable()->after('eficiencia');
            $table->float('energia_util_consumida_wh')->nullable()->after('energia_consumida_wh');
            $table->float('energia_consumida_wh_periodo')->nullable()->after('energia_util_consumida_wh');
            $table->float('costo_monetario_periodo')->nullable()->after('energia_consumida_wh_periodo');
        });
    }

    public function down()
    {
        Schema::table('entity_equipment', function (Blueprint $table) {
            $table->dropColumn(['factor_carga', 'eficiencia', 'energia_consumida_wh', 'energia_util_consumida_wh', 'energia_consumida_wh_periodo', 'costo_monetario_periodo']);
        });
    }
}
