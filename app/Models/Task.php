<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Override;

class Task extends Model
{
    protected $fillable = [
        'name',
        'description',
        'deadline',
        'user_id',
        'project_id',
        'status_id'
    ];

    #[Override]
    protected function casts()
    {
        return [
            'id' => 'integer',
            'project_id' => 'integer',
            'user_id' => 'integer',
            'status_id' => 'integer',
            'name' => 'string',
            'description' => 'string',
            'deadline' => 'date'
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(Status::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
