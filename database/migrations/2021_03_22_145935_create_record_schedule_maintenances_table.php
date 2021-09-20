<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRecordScheduleMaintenancesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('record_schedule_maintenances', function (Blueprint $table) {
            $table->id();
            $table->date('old_date');
            $table->date('new_date');
            $table->unsignedBigInteger('schedule_maint_detail_id');
            $table->foreign('schedule_maint_detail_id')->references('id')->on('schedule_maintenance_details');
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users');
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
        Schema::dropIfExists('record_schedule_maintenances');
    }
}
