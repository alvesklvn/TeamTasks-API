<?php

namespace App\Http\Controllers;

use App\Domain\Services\ProjectServices;
use App\Http\Requests\AddMembersRequest;
use App\Http\Requests\NewProjectRequest;
use App\Http\Resources\ProjectResource;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

use function PHPUnit\Framework\isEmpty;

class ProjectController extends Controller
{
    public function __construct(private ProjectServices $service)
    {
        
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $projects = $user->projects;

        if ($projects->isNotEmpty()){
            return ProjectResource::collection($projects);
        }

        return response()->json(["Você não participa de nenhum projeto."], 404);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(NewProjectRequest $request)
    {
        $data = $request->validated();
        $currentUser = $request->user();
        $project = $this->service->create($data, $currentUser);
        if (!$project){
            return response()->json(["message" => "não foi possível criar o projeto"], 422);
        }
        return new ProjectResource($project);
    }

    public function join(Request $request, Project $project, string $response)
    {
        $user = $request->user();
        $decision = $this->service->joinProject($project, $user, $response);
        if ($decision == "entrou"){
            return response()->json(["message" => "Você entrou no projeto. Seja bem-vindo(a)!"]);
        }

        return response()->json(["message" => "Você recusou a entrada no projeto!"]);
    }

    public function addMembers(AddMembersRequest $request, Project $project)
    {
        $data = $request->validated();
        $insertMembers = $this->service->addMembers($project, $data);
        if ($insertMembers){
            return response()->json(["message" => "Os usuários foram inseridos com sucesso!"]);
        } else {
            return response()->json(["message" => "não foi possível inserir usuários"], 422);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Project $project)
    {
        return new ProjectResource($project);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
