<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePublishersFlattable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('publishers_flattable', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('publisher_id');
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->longText('books')->nullable();
            $table->longText('recent_books')->nullable();
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
