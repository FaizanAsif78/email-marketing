<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable([
    'company_name',
    'company_logo',
    'company_website',
    'timezone',
    'country',
    'subscription_plan',
    'status',
])]
#[Hidden([])]
class Tenant extends Model
{
    public const SUBSCRIPTION_PLANS = ['basic', 'pro', 'enterprise'];

    public const STATUSES = ['active', 'inactive', 'suspended', 'pending'];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function admin(): HasOne
    {
        return $this->hasOne(User::class)->whereHas('roles', fn ($query) => $query->where('name', 'admin'));
    }
}
