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
    protected $assignmentType; // 'user' ou 'cliente'

    /**
     * Create a new notification instance.
     */
    public function __construct(Ticket $ticket, string $assignmentType = 'user')
    {
        $this->ticket = $ticket;
        $this->assignmentType = $assignmentType;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $message = $this->assignmentType === 'cliente' 
            ? "Um novo chamado foi criado para você: {$this->ticket->title}"
            : "Um chamado foi atribuído a você: {$this->ticket->title}";

        return (new MailMessage)
                    ->subject('Novo Chamado Atribuído')
                    ->line($message)
                    ->line("Prioridade: {$this->ticket->priority}")
                    ->line("Status: {$this->ticket->status}")
                    ->action('Ver Chamado', url("/tickets/{$this->ticket->id}"));
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $message = $this->assignmentType === 'cliente' 
            ? "Um novo chamado foi criado para você: {$this->ticket->title}"
            : "Um chamado foi atribuído a você: {$this->ticket->title}";

        return [
            'ticket_id' => $this->ticket->id,
            'ticket_title' => $this->ticket->title,
            'ticket_status' => $this->ticket->status,
            'ticket_priority' => $this->ticket->priority,
            'assignment_type' => $this->assignmentType,
            'message' => $message,
            'created_at' => now()->toDateTimeString(),
        ];
    }
}
