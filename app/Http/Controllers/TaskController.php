<?php

namespace App\Http\Controllers;

use App\Domain\Services\TaskService;
use App\Http\Requests\AddTasksRequest;
use App\Http\Resources\TaskResource;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class TaskController extends Controller
{
    public function __construct(Private TaskService $service)
    {
        
    }

    public function addTasks(AddTasksRequest $request, Project $project)
    {
        $data = $request->validated();
        if ($this->service->addTasks($project, $data)){
            return response()->json(["message" => "tarefas criadas com sucesso"]);
        } else {
            return response()->json(["message" => "não foi possível criar as tarefas"], 422);
        }
    }

    public function showTasks(Request $request, Project $project, ?Task $task = null)
    {
        $user = $request->user();

        $result = $this->service->show($project, $user, $task);
        if ($result){
            return $result instanceof Collection ? TaskResource::collection($result) : new TaskResource($result);
        }

        return response()->json(["erro ao encontrar a tarefa!"]);
    }
}
