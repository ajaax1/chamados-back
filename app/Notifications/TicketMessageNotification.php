<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\View;
use App\Models\Ticket;
use App\Models\TicketMessage;
use App\Models\User;

class TicketMessageNotification extends Notification
{
    use Queueable;

    protected $ticket;
    protected $ticketMessage;
    protected $sender;
    protected $recipientRole;

    /**
     * Create a new notification instance.
     */
    public function __construct(Ticket $ticket, TicketMessage $ticketMessage, User $sender, string $recipientRole = 'atendente')
    {
        $this->ticket = $ticket;
        $this->ticketMessage = $ticketMessage;
        $this->sender = $sender;
        $this->recipientRole = $recipientRole;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable)
    {
        $frontendUrl = env('FRONTEND_URL', 'https://tickets-zap.vercel.app');
        $ticketUrl = $frontendUrl . '/tickets/' . $this->ticket->id;

        $html = View::make('emails.ticket-message-notification', [
            'user' => $notifiable,
            'ticket' => $this->ticket,
            'ticketMessage' => $this->ticketMessage,
            'sender' => $this->sender,
            'ticketUrl' => $ticketUrl,
            'role' => $this->recipientRole
        ])->render();

        return (new MailMessage)
                    ->subject('Nova Mensagem no Chamado #' . $this->ticket->id . ' - Sistema de Chamados')
                    ->html($html);
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
            'message_id' => $this->ticketMessage->id,
            'sender_id' => $this->sender->id,
            'sender_name' => $this->sender->name,
            'message_preview' => substr($this->ticketMessage->message, 0, 100),
        ];
    }
}
