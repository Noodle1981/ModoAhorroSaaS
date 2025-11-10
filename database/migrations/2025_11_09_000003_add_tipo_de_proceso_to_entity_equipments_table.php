<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTipoDeProcesoToEntityEquipmentsTable extends Migration
{
    public function up()
    {
        Schema::table('entity_equipment', function (Blueprint $table) {
            $table->string('tipo_de_proceso')->nullable()->after('custom_name');
        });
    }

    public function down()
    {
        Schema::table('entity_equipment', function (Blueprint $table) {
            $table->dropColumn('tipo_de_proceso');
        });
    }
}
