<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOtherMaintenanceChecklistsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('other_checklists', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedBigInteger('other_maintenance_id');
            $table->foreign('other_maintenance_id')->references('id')->on('other_maintenances');
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
        Schema::dropIfExists('other_checklists');
    }
}
