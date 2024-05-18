<?php

namespace App\Http\Middleware;

use App\Exceptions\Middleware\CurrentUserException;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class CurrentUserMiddleware
 *
 * This middleware checks if the belongs to the current logged-in user
 * It throws an exception if the user is not authorized.
 * If the model is not there it ignores the check, maybe improve or just
 * use authorize from the request
 */
class CurrentUserMiddleware
{
    public function handle(Request $request, Closure $next, ...$modelNames): Response
    {
        if (!$modelNames) {
            throw new CurrentUserException('The check.user middleware needs at least one model to check');
        }

        foreach ($modelNames as $modelName) {
            $model = $request->route()->parameter($modelName);

            if ($model && ($model->user_id !== auth()->user()->id)) {
                throw new NotFoundHttpException();
            }
        }

        return $next($request);
    }
}
