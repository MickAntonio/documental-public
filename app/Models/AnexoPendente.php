<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AnexoPendente extends Model
{
    use HasFactory;

    public function utilizador()
    {
        return $this->belongsTo(User::class, 'utilizadorId');
    }

    public function origem()
    {
        return $this->belongsTo(LocalizacaoScanner::class, 'localizacaoScannerId');
    }
}
