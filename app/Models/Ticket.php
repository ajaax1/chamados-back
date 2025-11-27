<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    protected $fillable = [
        'nome_cliente', 'whatsapp_numero', 'user_id', 'cliente_id', 'descricao', 'status', 'title', 'priority', 'tempo_resolucao', 'prazo_resolucao', 'origem'
    ];

    protected $casts = [
        'prazo_resolucao' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function cliente()
    {
        return $this->belongsTo(User::class, 'cliente_id');
    }

    public function messages()
    {
        return $this->hasMany(WhatsappMessage::class);
    }

    public function ticketMessages()
    {
        return $this->hasMany(TicketMessage::class);
    }

    public function attachments()
    {
        return $this->hasMany(TicketAttachment::class);
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

