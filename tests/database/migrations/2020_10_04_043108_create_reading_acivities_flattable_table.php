<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReadingAcivitiesFlattableTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reading_activities_flattable', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('activity_id');
            $table->unsignedInteger('book_id');
            $table->string('book_name');
            $table->unsignedInteger('publisher_id')->nullable();
            $table->string('publisher_first_name')->nullable();
            $table->string('publisher_last_name')->nullable();
            $table->unsignedInteger('publisher_country_id')->nullable();
            $table->string('publisher_country_name')->nullable();
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
        Schema::dropIfExists('reading_activities_flattable');
    }
}
