<?php

namespace App\Jobs;

use App\Models\Project;
use App\Models\User;
use App\Notifications\InviteUserToProjectNotification;
use Exception;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Middleware\RateLimited;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Throwable;

class SendProjectInvitationJob implements ShouldQueue, ShouldBeUnique
{
    use Queueable;

    public int $tries = 4;

    public int $timeout = 8;

    public function backoff(): array
    {
        return [10, 15, 20];
    }


    /**
     * Create a new job instance.
     */
    public function __construct(public Project $project, public User $member)
    {
        
    }

    public function uniqueId(): string
    {
        return "projectinvitation:project-{$this->project->id}-user0{$this->member->id}";
    }

    public function middleware(): array
    {
        return [
            new WithoutOverlapping("project-invitation:project-{$this->project->id}-user-{$this->member->id}"),
            new RateLimited('project-invitations')
        ];
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $project_user = $this->project->users()->where('users.id', $this->member->id)->first();
        if (!$project_user){
            return;
        }

        $status = $project_user->pivot->status;
        if($status !== 'pending'){
            return;
        }

        Notification::route('mail', $this->member->email)->notify(new InviteUserToProjectNotification($this->project, $this->member));
        $this->project->users()->updateExistingPivot($this->member->id, ['status' => 'invited']);
    }

    public function failed(Throwable $exception)
    {
        Log::error('Falha ao enviar o convite', [
                    'user_id' => $this->member->id,
                    'user_email' => $this->member->email,
                    'project_id' => $this->project->id,
                    'error' => $exception->getMessage()
                ]);
    }
}
