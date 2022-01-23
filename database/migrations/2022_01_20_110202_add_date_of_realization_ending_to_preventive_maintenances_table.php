<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDateOfRealizationEndingToPreventiveMaintenancesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('preventive_maintenances', function (Blueprint $table) {
            $table->datetime('date_of_realization_ending')->after('date_of_realization')->nullable();
            $table->datetime('date_of_realization')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('preventive_maintenances', function (Blueprint $table) {
            $table->dropColumn('date_of_realization_ending');
        });
    }
}
