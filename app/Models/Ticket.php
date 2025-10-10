<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    protected $fillable = [
        'nome_cliente', 'whatsapp_numero', 'user_id', 'descricao', 'status', 'title'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function messages()
    {
        return $this->hasMany(WhatsappMessage::class);
    }

    // filtros de busca
    public function scopeSearch($query, $term)
    {
        if (!$term) return;
        $query->where(function ($s) use ($term) {
            $s->where('id', $term)
              ->orWhere('nome_cliente', 'like', "%{$term}%")
              ->orWhere('descricao', 'like', "%{$term}%");
        });
    }
}

