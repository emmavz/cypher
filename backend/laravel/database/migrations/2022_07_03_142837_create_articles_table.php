<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateArticlesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('articles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id');
            $table->string('title');
            $table->text('description')->nullable();
            $table->text('content')->nullable();
            $table->bigInteger('liquidation_days')->default(0);
            $table->float('price')->default(0);
            $table->bigInteger('category')->default(0);
            $table->string('image_url')->nullable();
            $table->bigInteger('article_total_reads')->default(0);
            $table->bigInteger('article_total_shares')->default(0);
            $table->boolean('is_published')->default(0);
            $table->dateTime('date_posted')->nullable();
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
        Schema::dropIfExists('articles');
    }
}
