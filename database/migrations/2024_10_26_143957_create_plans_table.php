<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->decimal('price', 8, 2);
            $table->integer('duration'); // Duration in days
            $table->integer('max_quality')->nullable(); // Maximum quality of video
            $table->integer('max_device')->default(1); // Maximum number of devices
            $table->integer('resolution')->nullable(); // Resolution of video
            $table->integer('support')->default(0); // Support type
            $table->integer('trial_period')->default(0); // Trial period in days
            $table->integer('status')->default(1); // 1: Active, 0: Inactive
            $table->text('description')->nullable();
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
        Schema::dropIfExists('plans');
    }
};
