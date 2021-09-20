<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDevicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('devices', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('group_id');
            $table->foreign('group_id')->references('id')->on('groups');
            $table->unsignedBigInteger('element_id');
            $table->foreign('element_id')->references('id')->on('elements');
            $table->unsignedBigInteger('location_id');
            $table->foreign('location_id')->references('id')->on('locations');
            $table->unsignedBigInteger('dependence_id');
            $table->foreign('dependence_id')->references('id')->on('dependences');
            $table->unsignedBigInteger('responsible_id');
            $table->foreign('responsible_id')->references('id')->on('responsibles');
            
            $table->string('internal_code')->unique();
            $table->string('serial_number')->unique();
            
            $table->unsignedBigInteger('brand_id');
            $table->foreign('brand_id')->references('id')->on('brands');
            $table->string('model');
            $table->text('description');
            $table->text('observations')->nullable();
            $table->date('start_operation');
            $table->date('date_end_guarantee');
            $table->integer('useful_life');
            $table->text('description_support_preventive_maintenance')->nullable();

            $table->boolean('active')->default(true);
            $table->date('down_date')->nullable();
            $table->string('down_reason')->nullable();
            $table->string('down_evidence')->nullable();
            $table->unsignedBigInteger('down_user_id')->nullable();
            $table->foreign('down_user_id')->references('id')->on('users');

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
        Schema::dropIfExists('devices');
    }
}
