<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\Ticket;
use App\Models\WhatsappMessage;
use App\Models\TicketMessage;
use App\Models\MessageAttachment;
use App\Notifications\TicketMessageNotification;

class MessageController extends Controller
{
    // LISTAR MENSAGENS DO WHATSAPP DE UM CHAMADO (mantido para compatibilidade)
    public function index(Request $request, Ticket $ticket)
    {
        $user = $request->user();

        // Verificar permissão para ver o ticket
        if (!$user->canViewTicket($ticket)) {
            return response()->json(['message' => 'Acesso negado. Você não tem permissão para ver este chamado.'], 403);
        }

        return $ticket->messages()->orderBy('criado_em')->get();
    }

    // ENVIAR RESPOSTA PELO WHATSAPP (mantido para compatibilidade)
    public function store(Request $request, Ticket $ticket)
    {
        $user = $request->user();

        // Verificar permissão para ver o ticket
        if (!$user->canViewTicket($ticket)) {
            return response()->json(['message' => 'Acesso negado. Você não tem permissão para enviar mensagens neste chamado.'], 403);
        }

        $data = $request->validate([
            'mensagem' => 'required|string'
        ]);

        $msg = $ticket->messages()->create([
            'mensagem' => $data['mensagem'],
            'tipo' => 'enviado'
        ]);

        // Enfileira envio real via WhatsAppService
        dispatch(function() use ($ticket, $msg){
            app('App\Services\WhatsappService')
                ->sendMessage($ticket->whatsapp_numero, $msg->mensagem);
        });

        return $msg;
    }

    // LISTAR MENSAGENS INTERNAS DO SISTEMA DE UM CHAMADO
    public function indexInternal(Request $request, Ticket $ticket)
    {
        $user = $request->user();

        // Verificar permissão para ver o ticket
        if (!$user->canViewTicket($ticket)) {
            return response()->json(['message' => 'Acesso negado. Você não tem permissão para ver este chamado.'], 403);
        }

        $messages = $ticket->ticketMessages()
            ->with(['user:id,name,email,role', 'attachments'])
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json($messages);
    }

    // DOWNLOAD DE ANEXO DE MENSAGEM
    public function downloadAttachment(Request $request, MessageAttachment $attachment)
    {
        $user = $request->user();
        $message = $attachment->ticketMessage;
        $ticket = $message->ticket;

        // Verificar permissão para ver o ticket
        if (!$user->canViewTicket($ticket)) {
            return response()->json(['message' => 'Acesso negado. Você não tem permissão para baixar este arquivo.'], 403);
        }

        $filePath = storage_path('app/public/' . $attachment->caminho_arquivo);

        if (!file_exists($filePath)) {
            return response()->json(['message' => 'Arquivo não encontrado'], 404);
        }

        return response()->download($filePath, $attachment->nome_arquivo);
    }

    // VISUALIZAR ANEXO DE MENSAGEM (para imagens e PDFs no navegador)
    public function showAttachment(Request $request, MessageAttachment $attachment)
    {
        $user = $request->user();
        $message = $attachment->ticketMessage;
        $ticket = $message->ticket;

        // Verificar permissão para ver o ticket
        if (!$user->canViewTicket($ticket)) {
            return response()->json(['message' => 'Acesso negado. Você não tem permissão para ver este arquivo.'], 403);
        }

        $filePath = storage_path('app/public/' . $attachment->caminho_arquivo);

        if (!file_exists($filePath)) {
            return response()->json(['message' => 'Arquivo não encontrado'], 404);
        }

        return response()->file($filePath, [
            'Content-Type' => $attachment->tipo_mime,
            'Content-Disposition' => 'inline; filename="' . $attachment->nome_arquivo . '"',
        ]);
    }

    // ENVIAR MENSAGEM INTERNA NO SISTEMA
    public function storeInternal(Request $request, Ticket $ticket)
    {
        $user = $request->user();

        // Verificar permissão para ver o ticket
        if (!$user->canViewTicket($ticket)) {
            return response()->json(['message' => 'Acesso negado. Você não tem permissão para enviar mensagens neste chamado.'], 403);
        }

        $data = $request->validate([
            'message' => 'required|string|max:5000',
            'is_internal' => 'sometimes|boolean',
            'anexos' => 'sometimes|array|max:10',
            'anexos.*' => 'file|mimes:jpeg,jpg,png,gif,webp,pdf,doc,docx,xls,xlsx,txt|max:10240', // 10MB max por arquivo
        ], [
            'message.required' => 'A mensagem é obrigatória.',
            'message.max' => 'A mensagem não pode ter mais de 5000 caracteres.',
            'anexos.array' => 'Os anexos devem ser enviados como array.',
            'anexos.max' => 'Máximo de 10 anexos por mensagem.',
            'anexos.*.file' => 'Cada item deve ser um arquivo válido.',
            'anexos.*.mimes' => 'Apenas arquivos do tipo: jpeg, jpg, png, gif, webp, pdf, doc, docx, xls, xlsx, txt são permitidos.',
            'anexos.*.max' => 'Cada arquivo deve ter no máximo 10MB.',
        ]);

        // Cliente não pode enviar mensagens internas
        if ($user->isCliente() && ($data['is_internal'] ?? false)) {
            return response()->json(['message' => 'Clientes não podem enviar mensagens internas.'], 403);
        }

        // Criar mensagem
        $message = $ticket->ticketMessages()->create([
            'user_id' => $user->id,
            'message' => $data['message'],
            'is_internal' => $data['is_internal'] ?? false
        ]);

        // Processar anexos se houver
        $attachments = [];
        if ($request->hasFile('anexos')) {
            foreach ($request->file('anexos') as $file) {
                // Gerar nome único para o arquivo
                $extension = $file->getClientOriginalExtension();
                $fileName = Str::uuid() . '.' . $extension;
                $originalName = $file->getClientOriginalName();

                // Criar diretório por ticket/mensagem
                $directory = 'messages/' . $ticket->id . '/' . $message->id;
                $path = $file->storeAs($directory, $fileName, 'public');

                // Criar registro no banco
                $attachment = MessageAttachment::create([
                    'ticket_message_id' => $message->id,
                    'nome_arquivo' => $originalName,
                    'caminho_arquivo' => $path,
                    'tipo_mime' => $file->getMimeType(),
                    'tamanho' => $file->getSize(),
                ]);

                $attachments[] = $attachment;
            }
        }

        // Recarregar mensagem com relacionamentos após salvar anexos
        $message->refresh();
        $message->load(['user:id,name,email,role', 'attachments']);

        // Enviar notificação por email para o outro participante (em background para não bloquear resposta)
        try {
            $this->notifyOtherParticipant($ticket, $user, $message);
        } catch (\Exception $e) {
            // Log do erro mas não interrompe o processo
            \Log::error('Erro ao enviar notificação: ' . $e->getMessage());
        }

        return response()->json([
            'message' => $message,
            'attachments' => $attachments
        ], 201);
    }

    /**
     * Notifica o outro participante do chamado sobre a nova mensagem
     * Salva notificação no banco de dados e envia email
     * NÃO envia email para quem enviou a mensagem
     */
    private function notifyOtherParticipant(Ticket $ticket, $sender, TicketMessage $message)
    {
        try {
            // Não notificar se a mensagem for interna
            if ($message->is_internal) {
                return;
            }

            $frontendUrl = env('FRONTEND_URL', 'https://tickets-zap.vercel.app');
            $ticketUrl = $frontendUrl . '/tickets/' . $ticket->id;

            // Se o admin/support/assistant enviou, SEMPRE notificar o cliente
            if (!$sender->isCliente() && $ticket->cliente_id) {
                $cliente = \App\Models\User::find($ticket->cliente_id);
                // Verificar se cliente existe e não é o próprio remetente
                if ($cliente && $cliente->id !== $sender->id) {
                    // Salva notificação no banco
                    $cliente->notify(new TicketMessageNotification($ticket, $message, $sender, 'cliente'));
                    
                    // Envia email com template customizado
                    $this->sendEmailNotification($cliente, $ticket, $message, $sender, $ticketUrl, 'cliente');
                }
            }
            
            // Se o cliente enviou, SEMPRE notificar admin/support
            if ($sender->isCliente()) {
                // Notificar o usuário atribuído ao chamado (se houver e não for o próprio cliente)
                if ($ticket->user_id) {
                    $assignedUser = \App\Models\User::find($ticket->user_id);
                    if ($assignedUser && $assignedUser->id !== $sender->id) {
                        // Salva notificação no banco
                        $assignedUser->notify(new TicketMessageNotification($ticket, $message, $sender, 'atendente'));
                        
                        // Envia email com template customizado
                        $this->sendEmailNotification($assignedUser, $ticket, $message, $sender, $ticketUrl, 'atendente');
                    }
                }

                // SEMPRE notificar TODOS os admins quando cliente envia mensagem
                // (exceto se o admin já foi notificado acima como usuário atribuído)
                $admins = \App\Models\User::where('role', 'admin')->get();
                foreach ($admins as $admin) {
                    // Não notificar se for o próprio cliente ou se já foi notificado como usuário atribuído
                    if ($admin->id !== $sender->id && $admin->id !== $ticket->user_id) {
                        // Salva notificação no banco
                        $admin->notify(new TicketMessageNotification($ticket, $message, $sender, 'admin'));
                        
                        // Envia email com template customizado
                        $this->sendEmailNotification($admin, $ticket, $message, $sender, $ticketUrl, 'admin');
                    }
                }
            }
        } catch (\Exception $e) {
            // Log do erro mas não interrompe o processo
            \Log::error('Erro ao enviar notificação de mensagem: ' . $e->getMessage(), [
                'ticket_id' => $ticket->id,
                'message_id' => $message->id,
                'sender_id' => $sender->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Envia email de notificação usando template customizado
     */
    private function sendEmailNotification($recipient, Ticket $ticket, TicketMessage $message, $sender, $ticketUrl, $role)
    {
        try {
            Mail::send('emails.ticket-message-notification', [
                'user' => $recipient,
                'ticket' => $ticket,
                'ticketMessage' => $message,
                'sender' => $sender,
                'ticketUrl' => $ticketUrl,
                'role' => $role
            ], function ($mailMessage) use ($recipient, $ticket) {
                $mailMessage->to($recipient->email)
                    ->subject('Nova Mensagem no Chamado #' . $ticket->id . ' - Sistema de Chamados');
            });
        } catch (\Exception $e) {
            \Log::error('Erro ao enviar email de notificação: ' . $e->getMessage(), [
                'recipient_id' => $recipient->id,
                'ticket_id' => $ticket->id,
                'message_id' => $message->id
            ]);
        }
    }
}
