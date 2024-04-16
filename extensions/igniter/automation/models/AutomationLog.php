<?php

namespace Igniter\Automation\Models;

use Igniter\Flame\Database\Model;
use Igniter\Flame\Database\Traits\Validation;
use Throwable;

class AutomationLog extends Model
{
    use Validation;

    /**
     * @var string The database table name
     */
    protected $table = 'igniter_automation_logs';

    public $timestamps = true;

    public $relation = [
        'belongsTo' => [
            'rule' => [AutomationRule::class, 'key' => 'automation_rule_id'],
            'action' => [RuleAction::class, 'foreignKey' => 'rule_action_id'],
        ],
    ];

    public $rules = [
        'automation_rule_id' => 'integer',
        'rule_action_id' => 'nullable|integer',
        'is_success' => 'boolean',
        'message' => 'string',
        'params' => 'array',
        'exception' => 'array',
    ];

    protected $casts = [
        'automation_rule_id' => 'integer',
        'rule_action_id' => 'integer',
        'is_success' => 'boolean',
        'params' => 'array',
        'exception' => 'array',
    ];

    protected $appends = ['action_name', 'status_name', 'created_since'];

    public static function createLog($rule, string $message, bool $isSuccess, array $params = [], ?Throwable $exception = null)
    {
        $record = new static;
        if ($rule instanceof RuleAction) {
            $record->automation_rule_id = $rule->automation_rule_id;
            $record->rule_action_id = $rule->getKey();
        } else {
            $record->automation_rule_id = $rule->getKey();
            $record->rule_action_id = null;
        }

        $record->is_success = $isSuccess;
        $record->message = $message;
        $record->params = $params;
        $record->exception = $exception ? [
            'message' => $exception->getMessage(),
            'code' => $exception->getCode(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString(),
        ] : null;

        $record->save();

        return $record;
    }

    public function getStatusNameAttribute($value)
    {
        return lang($this->is_success
            ? 'igniter.automation::default.text_success'
            : 'igniter.automation::default.text_failed'
        );
    }

    public function getActionNameAttribute($value)
    {
        return optional($this->action)->name ?? '--';
    }

    public function getCreatedSinceAttribute($value)
    {
        return $this->created_at ? time_elapsed($this->created_at) : null;
    }
}
