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
    public function toMail(object $notifiable)
    {
        $ticketUrl = url('/tickets/' . $this->ticket->id);
        $role = $this->assignedType === 'cliente' ? 'cliente' : 'atendente';
        
        return (new MailMessage)
                    ->subject('Novo Chamado Atribuído - #' . $this->ticket->id . ' - Sistema de Chamados')
                    ->view('emails.ticket-notification', [
                        'user' => $notifiable,
                        'ticket' => $this->ticket,
                        'ticketUrl' => $ticketUrl,
                        'role' => $role
                    ]);
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
