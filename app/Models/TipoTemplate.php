<?php

namespace App\Models;

use App\Models\Template;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TipoTemplate extends Model implements Auditable
{
    use HasFactory;
    use \OwenIt\Auditing\Auditable;

    protected $dates    = ['deleted_at',];
    protected $fillable = [
        'id',
        'templateId',
        'tipoId',
    ];

    public function template()
    {
        return $this->belongsTo(Template::class, 'templateId');
    }
}
