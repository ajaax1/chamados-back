<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MessageAttachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_message_id',
        'nome_arquivo',
        'caminho_arquivo',
        'tipo_mime',
        'tamanho'
    ];

    protected $appends = ['url'];

    public function ticketMessage()
    {
        return $this->belongsTo(TicketMessage::class);
    }

    /**
     * Retorna a URL completa do arquivo para acesso público
     */
    public function getUrlAttribute()
    {
        return url('storage/' . $this->caminho_arquivo);
    }

    /**
     * Retorna o caminho completo do arquivo no storage
     */
    public function getFullPathAttribute()
    {
        return storage_path('app/public/' . $this->caminho_arquivo);
    }
}
