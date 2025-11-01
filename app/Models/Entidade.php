<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Entidade extends Model implements Auditable
{
    use HasFactory;
    use SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    protected $fillable = [
        'nome',
        'codigo',
        'nif',
        'entidadeTipoId',
        'activo',
        'endereco',
        'email',
        'telefone',
        'utilizadorId',
    ];

    public function entidadeTipo() {
        return $this->belongsTo(EntidadeTipo::class, 'entidadeTipoId');
    }

}
