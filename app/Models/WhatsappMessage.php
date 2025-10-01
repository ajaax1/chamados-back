<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WhatsappMessage extends Model
{
    protected $table = 'whatsapp_messages';

    public $timestamps = false; // usamos campo criado_em

    protected $fillable = ['ticket_id', 'mensagem', 'tipo', 'criado_em'];

    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }
}
