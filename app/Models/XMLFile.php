<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class XMLFile extends Model
{
    use SoftDeletes;

    protected $table = 'xml_files';

    protected $fillable = [
        'file_name',
        'content',
        'file_id',
    ];
}
