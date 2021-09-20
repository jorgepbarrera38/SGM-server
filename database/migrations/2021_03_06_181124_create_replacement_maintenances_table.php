<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReplacementMaintenancesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('replacement_maintenances', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('replacement_id');
            $table->foreign('replacement_id')->references('id')->on('replacements');
            $table->integer('quantity');

            $table->bigInteger('replacement_maintenancable_id');
            $table->string('replacement_maintenancable_type');
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
        Schema::dropIfExists('replacement_maintenances');
    }
}
