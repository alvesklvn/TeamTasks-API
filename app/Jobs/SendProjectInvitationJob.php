<?php

namespace App\Jobs;

use App\Models\Project;
use App\Models\User;
use App\Notifications\InviteUserToProjectNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class SendProjectInvitationJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 4;

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
        try {
                Notification::route('mail', $this->member->email)->notify(new InviteUserToProjectNotification($this->project, $this->member));
            } catch (\Throwable $e) {
                Log::error('Falha ao enviar o convite', [
                    'user_id' => $this->member->id,
                    'user_email' => $this->member->email,
                    'project_id' => $this->project->id,
                    'error' => $e->getMessage()
                ]);

                throw $e;
            }
    }
}
