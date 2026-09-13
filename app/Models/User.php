<?php

namespace App\Models;

use App\Notifications\VerifyEmailNotification;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Override;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens;

    use Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password_hash'
    ];

    protected $hidden = [
        'password_hash'
    ];

    #[Override]
    protected function casts()
    {
        return [
            'id' => 'integer',
            'name' => 'string',
            'email' => 'string',
            'password_hash' => 'string',
            'email_verified_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime'
        ];      
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function projects(): BelongsToMany
    {
        return $this->belongsToMany(Project::class)->withPivot('role', 'joined_at');
    }

    #[Override]
    public function sendEmailVerificationNotification()
    {
        $this->notify(new VerifyEmailNotification());
    }
}
