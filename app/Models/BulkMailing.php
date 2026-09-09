<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
    'job_batch_id',
    'completed_at',
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
            'completed_at' => 'datetime',
        ];
    }

    public function deliveries(): HasMany
    {
        return $this->hasMany(BulkMailingResult::class);
    }

    public static function finalizeResults(int $bulkMailingId): void
    {
        $mailing = static::find($bulkMailingId);

        if (! $mailing) {
            return;
        }

        $totals = BulkMailingResult::query()
            ->where('bulk_mailing_id', $bulkMailingId)
            ->selectRaw('count(*) as total')
            ->selectRaw("sum(case when status = '".BulkMailingResult::STATUS_SENT."' then 1 else 0 end) as sent")
            ->selectRaw("sum(case when status = '".BulkMailingResult::STATUS_FAILED."' then 1 else 0 end) as failed")
            ->first();

        $sent = (int) ($totals->sent ?? 0);
        $failed = (int) ($totals->failed ?? 0);

        $mailing->update([
            'recipients_count' => (int) ($totals->total ?? count($mailing->recipients ?? [])),
            'sent_count' => $sent,
            'failed_count' => $failed,
            'status' => $failed > 0 && $sent === 0 ? static::STATUS_FAILED : static::STATUS_COMPLETED,
            'completed_at' => now(),
        ]);
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
