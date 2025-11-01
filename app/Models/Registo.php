<?php

namespace App\Models;

use App\Models\Encaminhamento;
use App\Models\ValorAtributoDinamico;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Registo extends Model implements Auditable
{

    use HasFactory;
    use SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    protected $dates    = ['deleted_at', 'data'];
    protected $fillable = [
        'id',
        'referencia',
        'assunto',
        'criadoPor',
        'localizacaoFisica',
        'data',
        'confidencial',
        'tipoId',
        'estadoId',
        'utilizadorId',
        'registoTipo',
    ];

    public function estado()
    {
        return $this->belongsTo(Estado::class, 'estadoId');
    }

    public function tipo()
    {
        return $this->belongsTo(Tipo::class, 'tipoId');
    }

    public function documento()
    {
        return $this->hasOne(Documento::class, 'registoId');
    }

    public function documentoAssociado()
    {
        return $this->hasOne(DocumentoAssociado::class, 'registoId');
    }

    public function registoRemetente()
    {
        return $this->hasOne(RegistoRemetente::class, 'registoId');
    }

    public function registoDestinatario()
    {
        return $this->hasMany(RegistoDestinatario::class, 'registoId', 'id');
    }

    public function registoPermissoes()
    {
        return $this->hasMany(RegistoPermissoes::class, 'registoId', 'id');
    }

    public function utilizador()
    {
        return $this->belongsTo(User::class, 'utilizadorId');
    }

    public function valorAtributoDinamicos()
    {
        return $this->hasMany(ValorAtributoDinamico::class, 'registoId', 'id');
    }

    public function anexos()
    {
        return $this->hasMany(Anexo::class, 'registoId', 'id');
    }

    public function anexosHistorico()
    {
        return $this->hasMany(HistoricoAnexo::class, 'registoId', 'id');
    }

    public function encaminhamentos()
    {
        return $this->hasMany(Encaminhamento::class, 'registoId', 'id');
    }

    public function generateTags(): array
    {
        return [
            'registoId='.$this->id
        ];
    }
}
