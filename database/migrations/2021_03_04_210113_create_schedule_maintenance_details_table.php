<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateScheduleMaintenanceDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('schedule_maintenance_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('schedule_maintenance_id');
            $table->foreign('schedule_maintenance_id')->references('id')->on('schedule_maintenances');
            $table->unsignedBigInteger('device_id');
            $table->foreign('device_id')->references('id')->on('devices');
            $table->date('date');
            $table->boolean('made')->default(false);
            $table->unsignedBigInteger('preventive_maintenance_id')->nullable();
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
        Schema::dropIfExists('schedule_maintenance_details');
    }
}
