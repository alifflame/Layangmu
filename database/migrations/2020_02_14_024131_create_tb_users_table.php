<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTbUsersTable extends Migration
{

    public function up()
    {
        Schema::create('tb_users', function (Blueprint $table) {
            $table->increments('user_id');
            $table->char('username');
            $table->string('email')->unique()->notNullable();
            $table->string('password');
            $table->string('phone');
            $table->string('position');
        });
    }

    public function down()
    {
        Schema::dropIfExists('tb_users');
    }
}
