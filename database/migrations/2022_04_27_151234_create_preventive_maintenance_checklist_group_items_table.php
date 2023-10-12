<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePreventiveMaintenanceChecklistGroupItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('preventive_checklist_group_items', function (Blueprint $table) {
            $table->id();
            $table->text('description');
            $table->enum('response', ['C', 'NC', 'NA']);
            $table->unsignedBigInteger('prev_checklist_group_id');
            $table->foreign('prev_checklist_group_id')->references('id')->on('preventive_checklist_groups');
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
        Schema::dropIfExists('preventive_checklist_group_items');
    }
}
