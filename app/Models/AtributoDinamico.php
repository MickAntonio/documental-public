<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AtributoDinamico extends Model implements Auditable
{
    use HasFactory;
    use SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    protected $dates    = ['deleted_at',];
    protected $fillable = [
        'id',
        'key',
        'value',
        'label',
        'required',
        'order',
        'controlType',
        'type',
        'options',
        'tipoId'
    ];
}
