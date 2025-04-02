<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Http\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Configuration\Middleware;
use App\Exceptions\CsvImportException;

function extractResourceName(Request $request): string {
    $segments = $request->segments();
    
    if (!empty($segments)) {
        $resource = Str::singular(str_replace('-', ' ', $segments[1]));
        return $resource;
    }

    return 'resource';
}

function determineAction(string $method): string {
    return match ($method) {
        'GET'    => 'index',
        'POST'   => 'store',
        'PUT', 'PATCH' => 'update',
        'DELETE' => 'destroy',
        default  => 'operation'
    };
}

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->statefulApi();
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (NotFoundHttpException $e, Request $request) {
            return response()->json([
                'success' => false,
                'message' => __('errors.not_found', ['resource' => extractResourceName($request)]),
                'errors' => $e->getMessage(),
            ], Response::HTTP_NOT_FOUND);
        });

        $exceptions->render(function (QueryException $e, Request $request) {
            $resource = extractResourceName($request);
            $action = determineAction($request->method());
            $logMessage = "Failed to {$action} the {$resource}: " . $e->getMessage();

            Log::error($logMessage . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => __("messages.{$action}.error", ['attribute' => extractResourceName($request)]),
                'errors' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        });

        $exceptions->render(function (CsvImportException $e, Request $request) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'errors' => $e->getMessage(),
            ], $e->statusCode);
        });
    })->create();
