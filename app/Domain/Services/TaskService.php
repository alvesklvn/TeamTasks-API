<?php

namespace App\Domain\Services;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use JsonException;

class TaskService
{
    public function addTasks(Project $project, array $data)
    {
        DB::transaction(function () use ($project, $data){
            try {
                foreach ($data['tasks'] as $task){

                    $user = User::where('email', $task['email'])->whereNotNull('email_verified_at')->first();

                    if (!$user){
                        continue;
                    }

                    $userIsInProject = $project->users()->where('users.id', $user->id)->exists();

                    if (!$userIsInProject){
                        continue;
                    }

                    $task = Task::create([
                        'name' => $task['name'],
                        'description' => $task['description'],
                        'deadline' => $task['deadline'],
                        'user_id' => $user->id,
                        'project_id' => $project->id,
                        'status_id' => 1
                    ]);
                }
            } catch(\Throwable $e){
                return false;
            }
        });

        return true;
    }

    public function show(Project $project, User $user, ?Task $task)
    {
        $isAdmin = $user->projects()->where('projects.id', $project->id)->wherePivot('role', 'admin')->exists();

        if ($task !== null){
            $taskInProject = $task->project->id === $project->id;
            if (!$taskInProject){
                return false;
            }

            if ($isAdmin){
                return $project->tasks()->where('id', $task->id)->get();
            } 

            return $project->tasks()->where('id', $task->id)->where('user_id', $user->id)->get();
            

        } else {
            if ($isAdmin){
                return $project->tasks;
            }

            return $project->tasks()->where('user_id', $user->id)->get();
        }
    }

    public function update(Task $task, array $data)
    {
        try {
            if (isset($data['title'])) {
                $task->name = $data['title'];
            }

            if (isset($data['description'])) {
                $task->description = $data['description'];
            }

            if (isset($data['deadline'])) {
                $task->deadline = $data['deadline'];
            }

            if (isset($data['status'])) {
                $status_id = DB::table('statuses')->where('name', $data['status'])->value('id');
                $task->status_id = $status_id;
            }

            $task->save();
            return true;
        }catch(\Throwable $e){
            return false;
        }
    }
}