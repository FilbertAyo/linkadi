<?php

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\MessageBag;
use Illuminate\Validation\ValidationException;

if (!function_exists('api_success')) {
    /**
     * Return a success API response.
     *
     * @param mixed $data
     * @param string $message
     * @param int $statusCode
     * @return JsonResponse
     */
    function api_success($data = null, string $message = 'Operation successful', int $statusCode = 200): JsonResponse
    {
        $response = [
            'success' => true,
            'message' => $message,
        ];

        if ($data !== null) {
            $response['data'] = $data;
        }

        return response()->json($response, $statusCode);
    }
}

if (!function_exists('api_error')) {
    /**
     * Return an error API response.
     *
     * @param string $message
     * @param int $statusCode
     * @param array|null $errors
     * @return JsonResponse
     */
    function api_error(string $message = 'Operation failed', int $statusCode = 400, ?array $errors = null): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $statusCode);
    }
}

if (!function_exists('api_validation_error')) {
    /**
     * Return a validation error API response.
     *
     * @param array|MessageBag|ValidationException $errors
     * @param string $message
     * @return JsonResponse
     */
    function api_validation_error($errors, string $message = 'Validation failed'): JsonResponse
    {
        if ($errors instanceof ValidationException) {
            $errors = $errors->errors();
        } elseif ($errors instanceof MessageBag) {
            $errors = $errors->toArray();
        }

        return api_error($message, 422, $errors);
    }
}

if (!function_exists('api_created')) {
    /**
     * Return a created resource API response.
     *
     * @param mixed $data
     * @param string $message
     * @return JsonResponse
     */
    function api_created($data = null, string $message = 'Resource created successfully'): JsonResponse
    {
        return api_success($data, $message, 201);
    }
}

if (!function_exists('api_updated')) {
    /**
     * Return an updated resource API response.
     *
     * @param mixed $data
     * @param string $message
     * @return JsonResponse
     */
    function api_updated($data = null, string $message = 'Resource updated successfully'): JsonResponse
    {
        return api_success($data, $message, 200);
    }
}

if (!function_exists('api_deleted')) {
    /**
     * Return a deleted resource API response.
     *
     * @param string $message
     * @return JsonResponse
     */
    function api_deleted(string $message = 'Resource deleted successfully'): JsonResponse
    {
        return api_success(null, $message, 200);
    }
}

if (!function_exists('api_not_found')) {
    /**
     * Return a not found API response.
     *
     * @param string $message
     * @return JsonResponse
     */
    function api_not_found(string $message = 'Resource not found'): JsonResponse
    {
        return api_error($message, 404);
    }
}

if (!function_exists('api_unauthorized')) {
    /**
     * Return an unauthorized API response.
     *
     * @param string $message
     * @return JsonResponse
     */
    function api_unauthorized(string $message = 'Unauthorized'): JsonResponse
    {
        return api_error($message, 401);
    }
}

if (!function_exists('api_forbidden')) {
    /**
     * Return a forbidden API response.
     *
     * @param string $message
     * @return JsonResponse
     */
    function api_forbidden(string $message = 'Forbidden'): JsonResponse
    {
        return api_error($message, 403);
    }
}

if (!function_exists('api_server_error')) {
    /**
     * Return a server error API response.
     *
     * @param string $message
     * @param \Throwable|null $exception
     * @return JsonResponse
     */
    function api_server_error(string $message = 'Internal server error', ?\Throwable $exception = null): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        if ($exception && config('app.debug')) {
            $response['debug'] = [
                'exception' => get_class($exception),
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
            ];
        }

        return response()->json($response, 500);
    }
}

if (!function_exists('redirect_success')) {
    /**
     * Redirect back with a success message.
     *
     * @param string $message
     * @param array $data
     * @return RedirectResponse
     */
    function redirect_success(string $message, array $data = []): RedirectResponse
    {
        $redirect = redirect()->back()->with('success', $message);

        if (!empty($data)) {
            $redirect->with($data);
        }

        return $redirect;
    }
}

if (!function_exists('redirect_error')) {
    /**
     * Redirect back with an error message.
     *
     * @param string $message
     * @param array $data
     * @return RedirectResponse
     */
    function redirect_error(string $message, array $data = []): RedirectResponse
    {
        $redirect = redirect()->back()->with('error', $message);

        if (!empty($data)) {
            $redirect->with($data);
        }

        return $redirect;
    }
}

if (!function_exists('redirect_warning')) {
    /**
     * Redirect back with a warning message.
     *
     * @param string $message
     * @param array $data
     * @return RedirectResponse
     */
    function redirect_warning(string $message, array $data = []): RedirectResponse
    {
        $redirect = redirect()->back()->with('warning', $message);

        if (!empty($data)) {
            $redirect->with($data);
        }

        return $redirect;
    }
}

if (!function_exists('redirect_info')) {
    /**
     * Redirect back with an info message.
     *
     * @param string $message
     * @param array $data
     * @return RedirectResponse
     */
    function redirect_info(string $message, array $data = []): RedirectResponse
    {
        $redirect = redirect()->back()->with('info', $message);

        if (!empty($data)) {
            $redirect->with($data);
        }

        return $redirect;
    }
}

if (!function_exists('redirect_route_success')) {
    /**
     * Redirect to a route with a success message.
     *
     * @param string $route
     * @param string $message
     * @param array $routeParams
     * @param array $data
     * @return RedirectResponse
     */
    function redirect_route_success(string $route, string $message, array $routeParams = [], array $data = []): RedirectResponse
    {
        $redirect = redirect()->route($route, $routeParams)->with('success', $message);

        if (!empty($data)) {
            $redirect->with($data);
        }

        return $redirect;
    }
}

if (!function_exists('redirect_route_error')) {
    /**
     * Redirect to a route with an error message.
     *
     * @param string $route
     * @param string $message
     * @param array $routeParams
     * @param array $data
     * @return RedirectResponse
     */
    function redirect_route_error(string $route, string $message, array $routeParams = [], array $data = []): RedirectResponse
    {
        $redirect = redirect()->route($route, $routeParams)->with('error', $message);

        if (!empty($data)) {
            $redirect->with($data);
        }

        return $redirect;
    }
}

