<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== USUÁRIOS ===\n";
$users = \App\Models\User::all(['id', 'name', 'email', 'role']);
foreach ($users as $user) {
    echo sprintf("%d | %s | %s | %s\n", $user->id, $user->name, $user->email, $user->role);
}

echo "\n=== TICKETS ===\n";
$tickets = \App\Models\Ticket::all(['id', 'title', 'status', 'priority', 'user_id', 'cliente_id']);
foreach ($tickets as $ticket) {
    echo sprintf("%d | %s | %s | %s | User: %d | Cliente: %s\n", 
        $ticket->id, 
        $ticket->title, 
        $ticket->status, 
        $ticket->priority, 
        $ticket->user_id,
        $ticket->cliente_id ?? 'null'
    );
}

echo "\n=== RESUMO ===\n";
echo "Total de usuários: " . $users->count() . "\n";
echo "Total de tickets: " . $tickets->count() . "\n";




