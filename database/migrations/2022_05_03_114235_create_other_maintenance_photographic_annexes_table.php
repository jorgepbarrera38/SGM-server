<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOtherMaintenancePhotographicAnnexesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('other_maintenance_photographic_annexes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('other_maint_id')->nullable();
            $table->foreign('other_maint_id')->references('id')->on('other_maintenances');
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
        Schema::dropIfExists('other_maintenance_photographic_annexes');
    }
}
