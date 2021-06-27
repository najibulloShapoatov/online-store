<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('category_id');
            $table->date('date');
            $table->string('title')->index();
            $table->string('alias')->unique()->nullable();
            $table->string('description')->nullable();
            $table->text('content')->nullable();
            $table->text('specification')->nullable();
            $table->decimal('price',9,2)->default('0.00');
            $table->integer('sale')->default(0)->nullable();
            $table->integer('new')->unsigned()->default(0)->nullable();
            $table->integer('hit')->unsigned()->default(0)->nullable();
            $table->integer('availability')->unsigned()->default(1)->nullable();
            $table->string('colors')->nullable();
            $table->integer('is_active')->unsigned()->default(1)->nullable();
            $table->string('related')->nullable();
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
        Schema::dropIfExists('products');
    }
}
