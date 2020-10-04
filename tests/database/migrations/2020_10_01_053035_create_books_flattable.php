<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBooksFlattable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('books_flattable', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('book_id');
            $table->string('name')->nullable();
            $table->date('published_at')->nullable();
            $table->unsignedBigInteger('publisher_id')->nullable();

            $table->string('publisher_first_name')->nullable();
            $table->string('publisher_last_name')->nullable();
            $table->string('publisher_country_id')->nullable();
            $table->string('publisher_country_name')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('books_flattable');
    }
}
