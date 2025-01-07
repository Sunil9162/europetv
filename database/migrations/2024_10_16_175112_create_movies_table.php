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
        Schema::create('movies', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('director')->nullable();
            $table->string('producer')->nullable();
            $table->integer('release_year')->nullable();
            $table->string('rating')->nullable();
            $table->string('poster')->nullable();  // URL to movie poster
            $table->string('trailer_url')->nullable();  // URL to movie trailer
            $table->string('movie_url')->nullable();    // URL to full movie
            $table->integer('view_count')->default(0);
            $table->boolean('status')->default(true);  // 1 for active, 0 for inactive
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
        Schema::dropIfExists('movies');
    }
};
