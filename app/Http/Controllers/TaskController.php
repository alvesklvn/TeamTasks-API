<?php

namespace App\Http\Controllers;

use App\Domain\Services\TaskService;
use App\Http\Requests\AddTasksRequest;
use App\Http\Requests\UpdateTasksRequest;
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

    public function create(AddTasksRequest $request, Project $project)
    {
        $data = $request->validated();
        if ($this->service->addTasks($project, $data)){
            return response()->json(["message" => "tarefas criadas com sucesso"]);
        } else {
            return response()->json(["message" => "não foi possível criar as tarefas"], 422);
        }
    }

    public function update(UpdateTasksRequest $request, Task $task)
    {
        $data = $request->validated();
        $result = $this->service->update($task, $data);
        if ($result) {
            return response()->json(["tarefa atualizada com sucesso!"]);
        }

        return response()->json(["erro ao atualizar tarefa!"], 422);
    }

    public function index(Request $request, Project $project, ?Task $task = null)
    {
        $user = $request->user();

        $result = $this->service->show($project, $user, $task);
        if ($result && $result->isNotEmpty()){
            return TaskResource::collection($result);
        }

        return response()->json(["erro ao encontrar a tarefa!"], 404);
    }
}
