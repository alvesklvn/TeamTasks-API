<?php

namespace App\Domain\Services;

use App\Jobs\SendProjectInvitationJob;
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

                    foreach($members as $member) {
                        SendProjectInvitationJob::dispatch($project, $member)->afterCommit();
                    }
                }

                $project->refresh();
                return $project; 
            });
            return $project;
        } catch (\Throwable $e) {
            abort(500, $e->getMessage());
        }
    }

    public function addMembers(Project $project, array $data)
    {
        foreach ($data['members'] as $member){
            try {
                $user = User::where('email', $member['email'])->whereNotNull('email_verified_at')->first();
                
                if (!$user){
                    continue;
                }

                $userIsInProject = $project->users()->where('users.id', $user->id)->exists();
                
                if ($userIsInProject){
                    continue;
                }

            
                $project->users()->attach($user->id, [
                    'role' => ($member['is_admin'] ?? false) ? 'admin' : 'user'
                ]);
            
            } catch (\Throwable $e) {
                abort(500, "Não foi possível convidar o usuário ".$member['email']." ao projeto");
            }

            SendProjectInvitationJob::dispatch($project, $user);
            
        }
        return true;
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