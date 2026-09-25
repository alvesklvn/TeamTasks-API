<?php

namespace App\Http\Middleware;

use App\Models\Project;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class UserInProject
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Project $project, Closure $next): Response
    {
        $user = $request->user();
        $userInProject = $project->users()->where('users.id', $user->id)->exists();
        if (!$userInProject){
            abort(403, "Você não é um usuário permitido de fazer alterações nesse projeto");
        }
        return $next($request);
    }
}
