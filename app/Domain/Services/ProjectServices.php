<?php

namespace App\Domain\Services;

use App\Models\Project;
use App\Models\User;
use App\Notifications\InviteUserToProjectNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use JsonException;

class ProjectServices
{
    public function create(array $data, User $user)
    {
        $members = collect();
        if (isset($data['members'])){
            $members = User::whereIn('email', $data['members'])->whereNotNull('email_verified_at')->get();
        }
                    
        try {
            $project = DB::transaction(function () use ($data, $user, $members) {
                $project = Project::create([
                    'title' => $data['title'],
                    'description' => $data['description']
                ]);
                
                $project->users()->attach($user, [
                    'role' => 'admin',
                    'joined_at' => now(),
                    'status' => 'joined'
                ]);

                if ($members->isNotEmpty()){
                    $project->users()->attach($members->pluck('id'), [
                        'role' => 'user',
                        'joined_at' => null,
                        'status' => 'invited'
                    ]);
                }
                $project->refresh();
                return $project; 
            });
        } catch (\Throwable $e) {
            throw new JsonException($e->getMessage());
        }

        try {
            if ($members->isNotEmpty()){
                    foreach($members as $member){
                        Notification::route('mail', $member->email)->notify(new InviteUserToProjectNotification($project, $member));
                    }
                }
            return $project;
        } catch (\Throwable $e) {
            throw new JsonException("erro ao enviar o email: ".$e->getMessage());
        }
    }

    public function joinProject(Project $project, User $user, string $response)
    {
        $memberInvited = $project->users()->wherePivot('user_id', $user->id)->wherePivot('status', 'invited')->first();

        if (!$memberInvited){
            throw new JsonException("Você não tem invites pendentes nesse projeto", 404);
        }

        if ($response === 'accept'){
            $project->users()->updateExistingPivotOrFail($user->id, [
                'status' => 'joined',
                'joined_at' => now(),
            ]);

            return "entrou";
        }

        $project->users()->detach($user->id);
        return "recusou";
    }
}