<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Ticket;

class TicketAssignedNotification extends Notification
{
    use Queueable;

    protected $ticket;
    protected $assignedType; // 'user' ou 'cliente'

    /**
     * Create a new notification instance.
     */
    public function __construct(Ticket $ticket, string $assignedType = 'user')
    {
        $this->ticket = $ticket;
        $this->assignedType = $assignedType;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $ticketUrl = url('/tickets/' . $this->ticket->id);
        $role = $this->assignedType === 'cliente' ? 'cliente' : 'atendente';
        
        return (new MailMessage)
                    ->subject('Novo Chamado Atribuído - #' . $this->ticket->id)
                    ->greeting('Olá, ' . $notifiable->name . '!')
                    ->line('Um novo chamado foi atribuído a você como ' . $role . '.')
                    ->line('**Título:** ' . $this->ticket->title)
                    ->line('**Cliente:** ' . $this->ticket->nome_cliente)
                    ->line('**Status:** ' . ucfirst($this->ticket->status))
                    ->line('**Prioridade:** ' . ucfirst($this->ticket->priority))
                    ->action('Ver Chamado', $ticketUrl)
                    ->line('Obrigado por usar nosso sistema!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'ticket_id' => $this->ticket->id,
            'ticket_title' => $this->ticket->title,
            'ticket_status' => $this->ticket->status,
            'ticket_priority' => $this->ticket->priority,
            'assigned_type' => $this->assignedType,
            'message' => $this->assignedType === 'cliente' 
                ? 'Um novo chamado foi criado para você: ' . $this->ticket->title
                : 'Um novo chamado foi atribuído a você: ' . $this->ticket->title,
        ];
    }
}
