<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOtherMaintenanceChecklistGroupItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('other_checklist_group_items', function (Blueprint $table) {
            $table->id();
            $table->text('description');
            $table->enum('response', ['C', 'NC', 'NA']);
            $table->unsignedBigInteger('other_checklist_group_id');
            $table->foreign('other_checklist_group_id')->references('id')->on('other_checklist_groups');
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
        Schema::dropIfExists('other_checklist_group_items');
    }
}
