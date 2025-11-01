<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RegistoPermissoes extends Model
{
    use HasFactory;

    public function entidade()
    {
        return $this->belongsTo(Entidade::class,'entidadeId');
    }

    public function utilizador()
    {
        return $this->belongsTo(User::class,'utilizadorId');
    }
}
