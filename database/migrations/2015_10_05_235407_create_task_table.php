<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTaskTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('project_id')->unsigned();
            $table->foreign('project_id')->references('id')->on('projects');    // 1
            $table->string('name', 20);    //2
            $table->text('description')->nullable();    //3
            $table->datetime('due_date')->nullable();    //4
            $table->enum('priority', ['낮음','보통', '높음'])->default('보통');    //5
            $table->enum('status', ['등록', '진행', '완료'])->default('등록');    //6
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
        Schema::drop('tasks');
    }
}
