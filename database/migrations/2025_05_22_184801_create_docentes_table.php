<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDocentesTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('docentes', function (Blueprint $table) {
            $table->bigIncrements('id');

            // Identificación
            $table->string('dni', 20)->unique();
            $table->string('apellido_paterno', 100);
            $table->string('apellido_materno', 100);
            $table->string('nombres', 150);

            // Datos académicos y administrativos
            $table->string('mayor_grado_academico', 100)->nullable();
            $table->string('categoria', 50)->nullable();
            $table->string('regimen_dedicacion', 50)->nullable();
            $table->string('regimen_pensionario', 50)->nullable();
            $table->integer('anio_ingreso_unprg')->nullable();
            $table->string('facultad', 100)->nullable();

            // Datos personales de contacto
            $table->date('fecha_nacimiento')->nullable();
            $table->string('direccion')->nullable();
            $table->string('numero_celular', 20)->nullable();
            $table->string('correo_institucional')->nullable();

            // Primer nombramiento
            $table->string('categoria_regimen_001', 50)->nullable();
            $table->string('resolucion_nombramiento', 100)->nullable();
            $table->date('fecha_nombramiento')->nullable();

            // Ascenso a asociado
            $table->string('categoria_regimen_002', 50)->nullable();
            $table->string('resolucion_ascenso_asociado', 100)->nullable();
            $table->date('fecha_ascenso_asociado')->nullable();

            // Ascenso a principal
            $table->string('categoria_regimen_003', 50)->nullable();
            $table->string('resolucion_ascenso_principal', 100)->nullable();
            $table->date('fecha_ascenso_principal')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('docentes');
    }
}
