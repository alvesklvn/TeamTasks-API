<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Task;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Override;

class Project extends Model
{
    protected $fillable = [
        'title',
        'description'
    ];

    #[Override]
    protected function casts()
    {
        return [
            'id' => 'integer',
            'title' => 'string',
            'description' => 'string',
            'created_at' => 'datetime',
            'updated_at' => 'datetime'
        ];
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withPivot('role', 'joined_at', 'status');
    }
}
