<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TableTbDocuments extends Migration
{

    public function up()
    {
        Schema::create('tb_documents', function (Blueprint $table) {
            $table->bigIncrements('doc_id');
            $table->string('from');
            $table->string('to');
            $table->string('subject');
            $table->date('doc_date');
            $table->string('file_path');
            $table->integer('doc_type');
            $table->integer('doc_number');
            $table->boolean('archived')->default(false);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('documents');
    }
}
