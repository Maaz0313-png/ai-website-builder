<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectVersion extends Model
{
    public const SOURCE_GENERATION = 'generation';

    public const SOURCE_VISUAL_EDITOR = 'visual_editor';

    public const SOURCE_CODE_EDITOR = 'code_editor';

    protected $fillable = [
        'project_id',
        'generation_id',
        'version',
        'source',
        'spec',
        'code',
        'build_path',
    ];

    protected function casts(): array
    {
        return [
            'spec' => 'array',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function generation(): BelongsTo
    {
        return $this->belongsTo(Generation::class);
    }
}
