<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use App\Models\EncaminhamentoDestinatario;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Encaminhamento extends Model  implements Auditable
{
    use HasFactory;
    use \OwenIt\Auditing\Auditable;

    public function destinatarios()
    {
        return $this->hasMany(EncaminhamentoDestinatario::class, 'encaminhamentoId', 'id');
    }

    public function utilizador()
    {
        return $this->belongsTo(User::class, 'utilizadorId');
    }

    public function registo()
    {
        return $this->belongsTo(Registo::class, 'registoId');
    }

    public function generateTags(): array
    {
        return [
            'registoId='.$this->registoId
        ];
    }
}
