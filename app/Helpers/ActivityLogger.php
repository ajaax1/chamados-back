<?php

namespace App\Helpers;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class ActivityLogger
{
    /**
     * Registra uma ação no log de atividades
     *
     * @param string $action Ação realizada (created, updated, deleted, viewed, etc.)
     * @param Model|null $model Model relacionado (opcional)
     * @param array|null $oldValues Valores antes da mudança (para updates)
     * @param array|null $newValues Valores depois da mudança (para updates)
     * @param string|null $description Descrição customizada da ação
     * @param array|null $metadata Dados extras (ex: relacionamentos)
     * @return ActivityLog
     */
    public static function log(
        string $action,
        ?Model $model = null,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?string $description = null,
        ?array $metadata = null
    ): ActivityLog {
        $user = Auth::user();
        
        $logData = [
            'user_id' => $user?->id,
            'action' => $action,
            'model_type' => $model ? get_class($model) : null,
            'model_id' => $model?->id,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'description' => $description ?? self::generateDescription($action, $model),
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
            'metadata' => $metadata,
        ];

        return ActivityLog::create($logData);
    }

    /**
     * Registra criação de um model
     */
    public static function created(Model $model, ?string $description = null, ?array $metadata = null): ActivityLog
    {
        return self::log(
            'created',
            $model,
            null,
            $model->getAttributes(),
            $description,
            $metadata
        );
    }

    /**
     * Registra atualização de um model
     */
    public static function updated(Model $model, array $oldValues, ?string $description = null, ?array $metadata = null): ActivityLog
    {
        return self::log(
            'updated',
            $model,
            $oldValues,
            $model->getChanges(),
            $description,
            $metadata
        );
    }

    /**
     * Registra deleção de um model
     */
    public static function deleted(Model $model, ?string $description = null, ?array $metadata = null): ActivityLog
    {
        return self::log(
            'deleted',
            $model,
            $model->getAttributes(),
            null,
            $description,
            $metadata
        );
    }

    /**
     * Registra visualização de um model
     */
    public static function viewed(Model $model, ?string $description = null, ?array $metadata = null): ActivityLog
    {
        return self::log(
            'viewed',
            $model,
            null,
            null,
            $description,
            $metadata
        );
    }

    /**
     * Registra atribuição de ticket
     */
    public static function assigned(Model $model, $assignedToUserId, ?string $description = null): ActivityLog
    {
        return self::log(
            'assigned',
            $model,
            null,
            ['assigned_to' => $assignedToUserId],
            $description ?? "Ticket atribuído ao usuário ID: {$assignedToUserId}",
            ['assigned_to_user_id' => $assignedToUserId]
        );
    }

    /**
     * Registra mudança de status
     */
    public static function statusChanged(Model $model, string $oldStatus, string $newStatus, ?string $description = null): ActivityLog
    {
        return self::log(
            'status_changed',
            $model,
            ['status' => $oldStatus],
            ['status' => $newStatus],
            $description ?? "Status alterado de '{$oldStatus}' para '{$newStatus}'",
            ['old_status' => $oldStatus, 'new_status' => $newStatus]
        );
    }

    /**
     * Gera descrição automática baseada na ação e model
     */
    private static function generateDescription(string $action, ?Model $model): string
    {
        $modelName = $model ? class_basename($model) : 'Item';
        
        $descriptions = [
            'created' => "{$modelName} criado",
            'updated' => "{$modelName} atualizado",
            'deleted' => "{$modelName} deletado",
            'viewed' => "{$modelName} visualizado",
            'assigned' => "{$modelName} atribuído",
            'status_changed' => "Status de {$modelName} alterado",
        ];

        return $descriptions[$action] ?? "Ação '{$action}' realizada em {$modelName}";
    }
}

