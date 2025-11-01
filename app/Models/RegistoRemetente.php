<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RegistoRemetente extends Model implements Auditable
{
    use HasFactory;
    use \OwenIt\Auditing\Auditable;

    protected $fillable = [
        'id',
        'tipo',
        'entidadeId',
        'registoId',
        'utilizadorId',
        'grupoId',

    ];

    public function entidade()
    {
        return $this->belongsTo(Entidade::class,'entidadeId');
    }

    public function utilizador()
    {
        return $this->belongsTo(User::class,'utilizadorId');
    }

    public function generateTags(): array
    {
        return [
            'registoId='.$this->id
        ];
    }
}
