<?php

namespace App\Jobs;

use App\Models\Project;
use App\Models\User;
use App\Notifications\InviteUserToProjectNotification;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Throwable;

class SendProjectInvitationJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 4;
    public function backoff(): array
    {
        return [10, 15, 20];
    }

    /**
     * Create a new job instance.
     */
    public function __construct(private Project $project, private User $member)
    {
        
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        
        Notification::route('mail', $this->member->email)->notify(new InviteUserToProjectNotification($this->project, $this->member));
    }

    public function failed(?Throwable $exception)
    {
        Log::error('Falha ao enviar o convite', [
                    'user_id' => $this->member->id,
                    'user_email' => $this->member->email,
                    'project_id' => $this->project->id,
                    'error' => $exception->getMessage()
                ]);
    }
}
