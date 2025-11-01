<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Empresa extends Model implements Auditable
{
    use HasFactory;
    use SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    protected $dates    = ['deleted_at','data_abertura'];
    protected $fillable = [
        'id',
        'nome',
        'nif',
        'dataAbertura',
        'endereco',
        'email',
        'telefone',
        'naturezaJuridica',
        'actividadesEconomica',
    ];
}
