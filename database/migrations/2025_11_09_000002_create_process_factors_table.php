<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProcessFactorsTable extends Migration
{
    public function up()
    {
        Schema::create('process_factors', function (Blueprint $table) {
            $table->id();
            $table->string('tipo_de_proceso')->unique();
            $table->float('factor_carga');
            $table->float('eficiencia');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('process_factors');
    }
}
