<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'bulk_mailing_id',
    'email',
    'status',
    'error',
])]
class BulkMailingResult extends Model
{
    public const STATUS_SENT = 'sent';

    public const STATUS_FAILED = 'failed';

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'error' => 'string',
        ];
    }

    public function bulkMailing(): BelongsTo
    {
        return $this->belongsTo(BulkMailing::class);
    }
}
