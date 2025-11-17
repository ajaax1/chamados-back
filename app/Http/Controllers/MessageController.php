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

        // Carregar relacionamentos
        $message->load(['user:id,name,email,role', 'attachments']);

        // Enviar notificação por email para o outro participante
        $this->notifyOtherParticipant($ticket, $user, $message);

        return response()->json([
            'message' => $message,
            'attachments' => $attachments
        ], 201);
    }

    /**
     * Notifica o outro participante do chamado sobre a nova mensagem
     */
    private function notifyOtherParticipant(Ticket $ticket, $sender, TicketMessage $message)
    {
        try {
            $frontendUrl = env('FRONTEND_URL', 'https://tickets-zap.vercel.app');
            $ticketUrl = $frontendUrl . '/tickets/' . $ticket->id;

            // Se o admin/support enviou, notificar o cliente
            if (!$sender->isCliente() && $ticket->cliente_id && !$message->is_internal) {
                $cliente = \App\Models\User::find($ticket->cliente_id);
                if ($cliente) {
                    Mail::send('emails.ticket-message-notification', [
                        'user' => $cliente,
                        'ticket' => $ticket,
                        'ticketMessage' => $message,
                        'sender' => $sender,
                        'ticketUrl' => $ticketUrl,
                        'role' => 'cliente'
                    ], function ($mailMessage) use ($ticket, $cliente) {
                        $mailMessage->to($cliente->email)
                            ->subject('Nova Mensagem no Chamado #' . $ticket->id . ' - Sistema de Chamados');
                    });
                }
            }
            // Se o cliente enviou, notificar o admin/support atribuído
            elseif ($sender->isCliente() && $ticket->user_id) {
                $assignedUser = \App\Models\User::find($ticket->user_id);
                if ($assignedUser) {
                    Mail::send('emails.ticket-message-notification', [
                        'user' => $assignedUser,
                        'ticket' => $ticket,
                        'ticketMessage' => $message,
                        'sender' => $sender,
                        'ticketUrl' => $ticketUrl,
                        'role' => 'atendente'
                    ], function ($mailMessage) use ($ticket, $assignedUser) {
                        $mailMessage->to($assignedUser->email)
                            ->subject('Nova Mensagem no Chamado #' . $ticket->id . ' - Sistema de Chamados');
                    });
                }
                // Também notificar admin se houver
                $admin = \App\Models\User::where('role', 'admin')->first();
                if ($admin && $admin->id !== $ticket->user_id) {
                    Mail::send('emails.ticket-message-notification', [
                        'user' => $admin,
                        'ticket' => $ticket,
                        'ticketMessage' => $message,
                        'sender' => $sender,
                        'ticketUrl' => $ticketUrl,
                        'role' => 'admin'
                    ], function ($mailMessage) use ($ticket, $admin) {
                        $mailMessage->to($admin->email)
                            ->subject('Nova Mensagem no Chamado #' . $ticket->id . ' - Sistema de Chamados');
                    });
                }
            }
        } catch (\Exception $e) {
            // Log do erro mas não interrompe o processo
            \Log::error('Erro ao enviar notificação de mensagem: ' . $e->getMessage());
        }
    }
}
