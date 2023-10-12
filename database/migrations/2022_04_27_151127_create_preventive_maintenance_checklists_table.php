<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePreventiveMaintenanceChecklistsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('preventive_checklists', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedBigInteger('preventive_maintenance_id');
            $table->foreign('preventive_maintenance_id')->references('id')->on('preventive_maintenances');
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
        Schema::dropIfExists('preventive_maintenance_checklists');
    }
}
