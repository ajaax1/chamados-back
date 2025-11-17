<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Mail;
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
     * Apenas database - email é enviado diretamente no controller
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Método toMail() não é usado (email enviado diretamente no controller)
     * Mantido apenas para evitar erros caso seja chamado acidentalmente
     */
    public function toMail(object $notifiable)
    {
        // Email é enviado diretamente no MessageController usando Mail::send()
        // Este método retorna um MailMessage válido caso seja chamado, mas não deve ser usado
        $frontendUrl = env('FRONTEND_URL', 'https://tickets-zap.vercel.app');
        $ticketUrl = $frontendUrl . '/tickets/' . $this->ticket->id;
        
        return (new MailMessage)
                    ->subject('Nova Mensagem no Chamado #' . $this->ticket->id . ' - Sistema de Chamados')
                    ->line('Você recebeu uma nova mensagem no chamado #' . $this->ticket->id)
                    ->line('De: ' . $this->sender->name)
                    ->line('Mensagem: ' . substr($this->ticketMessage->message, 0, 200))
                    ->action('Ver Chamado', $ticketUrl);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        // Verificar anexos de forma segura
        $hasAttachments = false;
        try {
            if ($this->ticketMessage->id) {
                $hasAttachments = $this->ticketMessage->attachments()->count() > 0;
            }
        } catch (\Exception $e) {
            // Se houver erro, assume que não há anexos
            $hasAttachments = false;
        }

        return [
            'ticket_id' => $this->ticket->id,
            'ticket_title' => $this->ticket->title,
            'ticket_status' => $this->ticket->status,
            'ticket_priority' => $this->ticket->priority,
            'message_id' => $this->ticketMessage->id,
            'sender_id' => $this->sender->id,
            'sender_name' => $this->sender->name,
            'sender_email' => $this->sender->email,
            'sender_role' => $this->sender->role,
            'message_preview' => substr($this->ticketMessage->message, 0, 100),
            'message_full' => $this->ticketMessage->message,
            'recipient_role' => $this->recipientRole,
            'has_attachments' => $hasAttachments,
            'message' => $this->recipientRole === 'cliente'
                ? 'Você recebeu uma nova mensagem no chamado #' . $this->ticket->id . ' de ' . $this->sender->name
                : 'Você recebeu uma nova mensagem no chamado #' . $this->ticket->id . ' do cliente ' . $this->ticket->nome_cliente,
        ];
    }
}
