<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class Compte extends BaseModel
{
    use HasFactory, SoftDeletes;

    protected $table = 'comptes';


    protected $fillable = [
        'id',
        'numero_compte',
        'user_id',
        'titulaire',
        'type',
        'devise',
        'statut',
        'derniere_modification',
        'version'
    ];

    protected function numeroCompte(): Attribute
    {
        return Attribute::make(
            set: fn($value) => $value ?: 'ACC-' . strtoupper(Str::random(10))
        );
    }

    public function scopeNumero($query, $numero)
    {
        return $query->where('numero_compte', $numero);
    }

   public function scopeFiltrerComptes(Builder $query, $filters = [], $user = null)
{
    $isAdmin = $user && method_exists($user, 'isAdmin') ? $user->isAdmin() : false;

    $query->whereIn('type', ['epargne', 'cheque'])
          ->where('statut', 'actif');

    if (!$isAdmin && $user) {
        $query->where('user_id', $user->id);
    }

    if (!empty($filters['type'])) {
        $query->where('type', $filters['type']);
    }

    if (!empty($filters['statut'])) {
        $query->where('statut', $filters['statut']);
    }

    if (!empty($filters['search'])) {
        $search = $filters['search'];
        $query->where(function ($q) use ($search) {
            $q->where('titulaire', 'like', "%{$search}%")
              ->orWhere('numero_compte', 'like', "%{$search}%");
        });
    }

    $sortField = match ($filters['sort'] ?? null) {
        'dateCreation' => 'created_at',
        'solde' => 'solde',
        'titulaire' => 'titulaire',
        default => 'created_at',
    };

    $order = in_array($filters['order'] ?? '', ['asc', 'desc'])
        ? $filters['order']
        : 'desc';

    $query->orderBy($sortField, $order);

    $query->withSum(['transactions as depot_sum' => fn($q) =>
        $q->where('type', 'depot')->where('status', 'validee')
    ], 'montant')
    ->withSum(['transactions as retrait_sum' => fn($q) =>
        $q->where('type', 'retrait')->where('status', 'validee')
    ], 'montant');

    return $query;
}

public function scopeClient($query, $phone)
{
    return $query->whereHas('user', fn($q) => $q->where('telephone', $phone));
}



   

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id',  'id');
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'compte_id', 'id');
    }
}
