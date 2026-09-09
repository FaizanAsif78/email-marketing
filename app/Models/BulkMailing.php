<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'user_id',
    'tenant_id',
    'mail_configuration_id',
    'email_template_id',
    'subject',
    'recipients',
    'results',
    'recipients_count',
    'sent_count',
    'failed_count',
    'status',
    'error',
])]
class BulkMailing extends Model
{
    public const STATUS_PROCESSING = 'processing';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_FAILED = 'failed';

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'recipients_count' => 'integer',
            'sent_count' => 'integer',
            'failed_count' => 'integer',
            'recipients' => 'array',
            'results' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function mailConfiguration(): BelongsTo
    {
        return $this->belongsTo(MailConfiguration::class);
    }

    public function emailTemplate(): BelongsTo
    {
        return $this->belongsTo(EmailTemplate::class);
    }
}
