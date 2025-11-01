<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DocumentoAssociado extends Model implements Auditable
{
    use HasFactory;
    use SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    protected $dates    = ['deleted_at'];
    protected $fillable = [
        'id',
        'utilizador',
        'registoTipo',
        'registoId',
    ];

    public function generateTags(): array
    {
        return [
            'registoId='.$this->registoId
        ];
    }
}
