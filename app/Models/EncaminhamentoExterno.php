<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EncaminhamentoExterno extends Model implements Auditable
{
    use HasFactory;
    use \OwenIt\Auditing\Auditable;

    public function utilizador()
    {
        return $this->belongsTo(User::class, 'utilizadorId');
    }

    public function registo()
    {
        return $this->belongsTo(Registo::class, 'registoId');
    }
}
