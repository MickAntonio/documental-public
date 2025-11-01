<?php

namespace App\Models;

use App\Models\AtributoDinamico;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tipo extends Model implements Auditable
{
    use HasFactory;
    use \OwenIt\Auditing\Auditable;

    protected $dates    = ['deleted_at',];
    protected $fillable = [
        'id',
        'nome',
        'templateId',
        'descricao',
        'activo',
        'atributosDinamico',
        'type',
        'confidencialidade',
       
    ];

    public function atributosDinamicos()
    {
        return $this->hasMany(AtributoDinamico::class,'tipoId','id');
    }
    public function tipoTemplates()
    {
        return $this->hasMany(TipoTemplate::class,'tipoId','id');
    }
}
