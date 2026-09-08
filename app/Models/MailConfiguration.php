<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'tenant_id',
    'smtp_host',
    'smtp_port',
    'username',
    'password',
    'encryption',
    'from_name',
    'from_email',
    'reply_to_email',
    'is_default',
])]
#[Hidden(['password'])]
class MailConfiguration extends Model
{
    public const ENCRYPTIONS = ['none', 'tls', 'ssl'];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'smtp_port' => 'integer',
            'password' => 'encrypted',
            'is_default' => 'boolean',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
