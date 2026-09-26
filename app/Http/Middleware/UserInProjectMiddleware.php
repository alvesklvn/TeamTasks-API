<?php

namespace App\Http\Middleware;

use App\Models\Project;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class UserInProjectMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $project = $request->route('project');
        $user = $request->user();
        $userInProject = $project->users()->where('users.id', $user->id)->wherePivot('status', 'joined')->exists();
        if (!$userInProject){
            abort(403, "Você não é um usuário permitido de fazer alterações ou visualizar este projeto");
        }
        return $next($request);
    }
}
