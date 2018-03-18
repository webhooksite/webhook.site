<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        AuthorizationException::class,
        HttpException::class,
        ModelNotFoundException::class,
        ValidationException::class,
    ];

    /**
     * @var string|null
     */
    private $sentryId = null;

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Exception  $e
     * @return void
     */
    public function report(Exception $e)
    {
        if (app()->bound('sentry') && $this->shouldReport($e)) {
            $this->sentryId = app('sentry')->captureException($e);
        }

        parent::report($e);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $e
     * @return \Illuminate\Http\Response|Response
     */
    public function render($request, Exception $e)
    {
        if ($request->ajax() || $request->wantsJson() || $request->isJson())
        {
            $json = $this->renderJson($request, $e);

            if ($e instanceof HttpException) {
                return response()->json($json, $e->getStatusCode());
            }

            return response()->json($json, Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return parent::render($request, $e);
    }

    private function renderJson($request, Exception $e)
    {
        $message = 'An internal error occurred';

        if ($e instanceof HttpException) {
            $message = $e->getMessage();
        }

        $response = [
            'success' => false,
            'error' => [
                'message' => $message,
                'id' => $this->sentryId,
            ],
        ];

        if ($e->getCode()) {
            $response['error']['code'] = $e->getCode();
        }

        if (config('app.debug')) {
            $response['error']['exception'] = sprintf('[%s] %s', get_class($e), $e->getMessage());
            $response['error']['trace'] = $e->getTrace();
            $response['error']['file'] = $e->getFile();
            $response['error']['line'] = $e->getLine();
        }

        return $response;
    }
}
