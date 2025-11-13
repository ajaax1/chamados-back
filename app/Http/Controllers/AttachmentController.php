<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Ticket;
use App\Models\TicketAttachment;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AttachmentController extends Controller
{
    /**
     * Upload de múltiplos arquivos para um ticket
     */
    public function store(Request $request, Ticket $ticket)
    {
        $user = $request->user();

        // Verificar permissão para ver o ticket
        if (!$user->canViewTicket($ticket)) {
            return response()->json(['message' => 'Acesso negado. Você não tem permissão para anexar arquivos neste chamado.'], 403);
        }

        $request->validate([
            'arquivos' => 'required|array|min:1|max:10',
            'arquivos.*' => 'file|mimes:jpeg,jpg,png,gif,pdf,doc,docx|max:10240', // 10MB max por arquivo
        ], [
            'arquivos.required' => 'É necessário enviar pelo menos um arquivo.',
            'arquivos.array' => 'Os arquivos devem ser enviados como array.',
            'arquivos.min' => 'É necessário enviar pelo menos um arquivo.',
            'arquivos.max' => 'Máximo de 10 arquivos por vez.',
            'arquivos.*.file' => 'Cada item deve ser um arquivo válido.',
            'arquivos.*.mimes' => 'Apenas arquivos do tipo: jpeg, jpg, png, gif, pdf, doc, docx são permitidos.',
            'arquivos.*.max' => 'Cada arquivo deve ter no máximo 10MB.',
        ]);

        $uploadedFiles = [];

        foreach ($request->file('arquivos') as $file) {
            // Gerar nome único para o arquivo
            $extension = $file->getClientOriginalExtension();
            $fileName = Str::uuid() . '.' . $extension;
            $originalName = $file->getClientOriginalName();

            // Criar diretório por ticket
            $directory = 'tickets/' . $ticket->id;
            $path = $file->storeAs($directory, $fileName, 'public');

            // Criar registro no banco
            $attachment = TicketAttachment::create([
                'ticket_id' => $ticket->id,
                'nome_arquivo' => $originalName,
                'caminho_arquivo' => $path,
                'tipo_mime' => $file->getMimeType(),
                'tamanho' => $file->getSize(),
            ]);

            $uploadedFiles[] = $attachment;
        }

        return response()->json([
            'message' => 'Arquivos enviados com sucesso',
            'anexos' => $uploadedFiles
        ], 201);
    }

    /**
     * Listar todos os anexos de um ticket
     */
    public function index(Request $request, Ticket $ticket)
    {
        $user = $request->user();

        // Verificar permissão para ver o ticket
        if (!$user->canViewTicket($ticket)) {
            return response()->json(['message' => 'Acesso negado. Você não tem permissão para ver este chamado.'], 403);
        }

        $attachments = $ticket->attachments()->orderBy('created_at', 'desc')->get();

        return response()->json($attachments);
    }

    /**
     * Download de um arquivo específico
     */
    public function download(Request $request, TicketAttachment $attachment)
    {
        $user = $request->user();
        $ticket = $attachment->ticket;

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

    /**
     * Visualizar arquivo (para imagens e PDFs no navegador)
     */
    public function show(Request $request, TicketAttachment $attachment)
    {
        $user = $request->user();
        $ticket = $attachment->ticket;

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

    /**
     * Deletar um anexo
     */
    public function destroy(Request $request, TicketAttachment $attachment)
    {
        $user = $request->user();
        $ticket = $attachment->ticket;

        // Verificar permissão para ver o ticket
        if (!$user->canViewTicket($ticket)) {
            return response()->json(['message' => 'Acesso negado. Você não tem permissão para deletar este arquivo.'], 403);
        }

        // Cliente não pode deletar anexos
        if ($user->isCliente()) {
            return response()->json(['message' => 'Acesso negado. Clientes não podem deletar anexos.'], 403);
        }

        // Deletar arquivo físico
        $filePath = storage_path('app/public/' . $attachment->caminho_arquivo);
        if (file_exists($filePath)) {
            unlink($filePath);
        }

        // Deletar registro
        $attachment->delete();

        return response()->json(['message' => 'Anexo deletado com sucesso']);
    }
}
