<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePreventiveMaintenancePhotographicAnnexesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('preventive_maintenance_photographic_annexes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('prev_m_id')->nullable();
            $table->foreign('prev_m_id')->references('id')->on('preventive_maintenances');
            $table->string('description');
            $table->string('image');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('preventive_maintenance_photographic_annexes');
    }
}
