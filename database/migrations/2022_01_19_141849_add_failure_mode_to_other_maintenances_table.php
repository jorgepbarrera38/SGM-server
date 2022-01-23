<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFailureModeToOtherMaintenancesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('other_maintenances', function (Blueprint $table) {
            $table->unsignedBigInteger('failure_mode_id')->after('conclusion')->nullable();
            $table->foreign('failure_mode_id')->references('id')->on('failure_modes');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('other_maintenances', function (Blueprint $table) {
            $table->dropForeign('failure_mode_id');
            $table->dropColumn('failure_mode_id');
        });
    }
}
