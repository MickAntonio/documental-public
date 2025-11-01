<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserInterino extends Model implements Auditable
{
    use HasFactory;
    use \OwenIt\Auditing\Auditable;

    protected $fillable = ['utilizadorId', 'utilizadorInterinoId', 'periodoInicio', 'periodoFim'];

    public function utilizador()
    {
        return $this->belongsTo(User::class,'utilizadorId', 'id');
    }

    public function utilizadorInterino()
    {
        return $this->belongsTo(User::class,'utilizadorInterinoId', 'id');
    }
}
