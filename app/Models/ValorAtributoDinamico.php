<?php

namespace App\Models;

use App\Models\AtributoDinamico;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ValorAtributoDinamico extends Model implements Auditable
{
    public $timestamps = false;
    use HasFactory;
    use \OwenIt\Auditing\Auditable;

    protected $fillable = ['registoId', 'atributoDinamicoId', 'valor'];

    public function atributo()
    {
        return $this->belongsTo(AtributoDinamico::class, 'atributoDinamicoId');
    }

    public function generateTags(): array
    {
        return [
            'registoId='.$this->registoId
        ];
    }
}
