<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOtherMaintenancesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('other_maintenances', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['Digital', 'Físico']);
            $table->unsignedBigInteger('device_id')->nullable();
            $table->foreign('device_id')->references('id')->on('devices');
            $table->unsignedBigInteger('group_id')->nullable();
            $table->foreign('group_id')->references('id')->on('groups');
            $table->date('date_of_realization');
            $table->unsignedBigInteger('type_other_maintenance_id');
            $table->foreign('type_other_maintenance_id')->references('id')->on('type_other_maintenances');
            $table->text('reason')->nullable();
            $table->text('support_description')->nullable();
            $table->string('evidence')->nullable();
            $table->unsignedBigInteger('location_id');
            $table->foreign('location_id')->references('id')->on('locations');
            $table->unsignedBigInteger('dependence_id');
            $table->foreign('dependence_id')->references('id')->on('dependences');
            $table->string('official_names')->nullable();
            $table->string('official_position')->nullable();
            $table->text('signature')->nullable();
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
        Schema::dropIfExists('other_maintenances');
    }
}
