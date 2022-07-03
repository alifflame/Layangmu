<?php

namespace App;

use Illuminate\Database\Eloquent\Model;


class Document extends Model
{

    protected $fillable = [
        'from', 'to', 'subject', 'doc_date', 'file_path', 'doc_type', 'doc_number'
    ];

    protected $table = 'tb_documents';
    protected $primaryKey = "doc_id";
}
