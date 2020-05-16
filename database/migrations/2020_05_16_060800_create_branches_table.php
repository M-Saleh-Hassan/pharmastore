<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBranchesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('branches', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('store_id')->unsigned();
            $table->bigInteger('area_id')->unsigned();
            $table->string('lng');
            $table->string('lat');
            $table->string('name')->nullable();
            $table->string('mobile')->nullable();
            $table->string('working_hours_from')->nullable();
            $table->string('working_hours_to')->nullable();
            $table->string('working_days_from')->nullable();
            $table->string('working_days_to')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('store_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('area_id')->references('id')->on('areas')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('branches');
    }
}
