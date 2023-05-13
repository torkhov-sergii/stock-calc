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
        Schema::create('time_series', function (Blueprint $table) {
            $table->id();
            $table->string('symbol')->index();
            $table->date('date')->index();
            $table->decimal('open');
            $table->decimal('high');
            $table->decimal('low');
            $table->decimal('close');
            $table->decimal('adjusted_close');
            $table->unsignedBigInteger('volume');
            $table->integer('split_coefficient')->nullable();

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
        Schema::dropIfExists('time_series');
    }
};
