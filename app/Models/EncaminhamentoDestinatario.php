<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EncaminhamentoDestinatario extends Model implements Auditable
{
    use HasFactory;
    use \OwenIt\Auditing\Auditable;
    
    public function utilizador()
    {
        return $this->belongsTo(User::class, 'utilizadorId');
    }

    public function encaminhamento()
    {
        return $this->belongsTo(Encaminhamento::class, 'encaminhamentoId');
    }

    public function entidade()
    {
        return $this->belongsTo(Entidade::class, 'entidadeId');
    }

    public function generateTags(): array
    {
        return [
            'registoId='.$this->encaminhamento->registoId
        ];
    }
}
