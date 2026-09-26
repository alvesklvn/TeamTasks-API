<?php

namespace App\Domain\Services;

use App\Jobs\SendProjectInvitationJob;
use App\Models\Project;
use App\Models\Task;
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
                        'status' => 'pending'
                    ]);

                    foreach($members as $member) {
                        SendProjectInvitationJob::dispatch($project, $member)->onQueue('email')->afterCommit();
                    }
                }

                $project->refresh();
                return $project; 
            });
            return $project;
        } catch (\Throwable $e) {
            return false;
        }
    }

    public function addMembers(Project $project, array $data)
    {
        DB::transaction(function () use ($data, $project){
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
                        'role' => ($member['is_admin'] ?? false) ? 'admin' : 'user',
                        'status' => 'pending'
                    ]);
                
                } catch (\Throwable $e) {
                    return false;
                }

                SendProjectInvitationJob::dispatch($project, $user)->onQueue('email')->afterCommit();
                
            }
        });
        return true;
    }

    public function joinProject(Project $project, User $user, string $response)
    {
        $memberInvited = $project->users()->where('users.id', $user->id)->wherePivot('status', 'invited')->first();

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

    public function leave(Project $project, User $user)
    {
        $projectAdmins = $project->users()->wherePivot('role', 'admin')->get();
        $isAdmin = $projectAdmins->contains($user);
        
        if ($projectAdmins->count() === 1 && $isAdmin){
            return false;
        }

        $project->users()->detach($user->id);
        $user->tasks()->where('project_id', $project->id)->delete();
        return true;
    }

    public function delete(Project $project)
    {
        try {
            $project->tasks()->delete();
            $project->delete();
            return true;
        } catch (\Throwable $e){
            return false;
        }
    }
}