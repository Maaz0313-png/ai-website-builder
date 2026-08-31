<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'prompt',
        'status',
        'spec',
        'current_version_id',
    ];

    protected function casts(): array
    {
        return [
            'spec' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function versions(): HasMany
    {
        return $this->hasMany(ProjectVersion::class);
    }

    public function currentVersion(): BelongsTo
    {
        return $this->belongsTo(ProjectVersion::class, 'current_version_id');
    }

    public function generations(): HasMany
    {
        return $this->hasMany(Generation::class);
    }
}
