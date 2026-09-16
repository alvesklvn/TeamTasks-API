<?php

namespace App\Http\Middleware;

use App\Models\Project;
use Closure;
use Illuminate\Http\Request;
use JsonException;
use Symfony\Component\HttpFoundation\Response;

class IsAdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $project = $request->route('project');
        $isAdmin = $project->users()->where('users.id', $user->id)->wherePivot('role', 'admin')->exists();
        if (!$isAdmin) {
            abort(403, "Você não é um usuário permitido de fazer alterações nesse projeto");
        }

        return $next($request);
    }
}
