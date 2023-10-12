<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOtherMaintenanceReplacementsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('other_maintenance_replacements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('other_maintenance_id')->nullable();
            $table->foreign('other_maintenance_id')->references('id')->on('other_maintenances');
            $table->unsignedBigInteger('replacement_id');
            $table->foreign('replacement_id')->references('id')->on('replacements');
            $table->integer('quantity');
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
        Schema::dropIfExists('other_maintenance_replacements');
    }
}
