<?php

namespace App\Http\Middleware;

use App\Http\Requests\UpdateTasksRequest;
use App\Models\Task;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminOrOwnerMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $task = $request->route('task');
        $user = $request->user();
        $project = $task->project;

        $isAdmin = $project->users()->wherePivot('user_id', $user->id)->wherePivot('role', 'admin')->exists();
        $isOwner = $task->user->id === $user->id;

        if ($isOwner || $isAdmin){
           return $next($request);
        }

        abort(403, "Você não é permitido de realizar alterações nessa tarefa!");
    }
}
