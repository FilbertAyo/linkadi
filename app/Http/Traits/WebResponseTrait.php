<?php

namespace App\Http\Traits;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\MessageBag;
use Illuminate\Validation\ValidationException;

trait WebResponseTrait
{
    /**
     * Redirect back with a success message.
     *
     * @param string $message
     * @param array $data
     * @return RedirectResponse
     */
    protected function redirectWithSuccess(string $message, array $data = []): RedirectResponse
    {
        $redirect = redirect()->back()->with('success', $message);

        if (!empty($data)) {
            $redirect->with($data);
        }

        return $redirect;
    }

    /**
     * Redirect back with an error message.
     *
     * @param string $message
     * @param array $data
     * @return RedirectResponse
     */
    protected function redirectWithError(string $message, array $data = []): RedirectResponse
    {
        $redirect = redirect()->back()->with('error', $message);

        if (!empty($data)) {
            $redirect->with($data);
        }

        return $redirect;
    }

    /**
     * Redirect back with a warning message.
     *
     * @param string $message
     * @param array $data
     * @return RedirectResponse
     */
    protected function redirectWithWarning(string $message, array $data = []): RedirectResponse
    {
        $redirect = redirect()->back()->with('warning', $message);

        if (!empty($data)) {
            $redirect->with($data);
        }

        return $redirect;
    }

    /**
     * Redirect back with an info message.
     *
     * @param string $message
     * @param array $data
     * @return RedirectResponse
     */
    protected function redirectWithInfo(string $message, array $data = []): RedirectResponse
    {
        $redirect = redirect()->back()->with('info', $message);

        if (!empty($data)) {
            $redirect->with($data);
        }

        return $redirect;
    }

    /**
     * Redirect back with validation errors.
     *
     * @param array|MessageBag|ValidationException $errors
     * @param string|null $message
     * @return RedirectResponse
     */
    protected function redirectWithValidationErrors($errors, ?string $message = null): RedirectResponse
    {
        if ($errors instanceof ValidationException) {
            return redirect()->back()
                ->withErrors($errors->errors())
                ->withInput();
        }

        if ($errors instanceof MessageBag) {
            $errors = $errors->toArray();
        }

        $redirect = redirect()->back()
            ->withErrors($errors)
            ->withInput();

        if ($message) {
            $redirect->with('error', $message);
        }

        return $redirect;
    }

    /**
     * Redirect to a route with a success message.
     *
     * @param string $route
     * @param string $message
     * @param array $routeParams
     * @param array $data
     * @return RedirectResponse
     */
    protected function redirectToRouteWithSuccess(string $route, string $message, array $routeParams = [], array $data = []): RedirectResponse
    {
        $redirect = redirect()->route($route, $routeParams)->with('success', $message);

        if (!empty($data)) {
            $redirect->with($data);
        }

        return $redirect;
    }

    /**
     * Redirect to a route with an error message.
     *
     * @param string $route
     * @param string $message
     * @param array $routeParams
     * @param array $data
     * @return RedirectResponse
     */
    protected function redirectToRouteWithError(string $route, string $message, array $routeParams = [], array $data = []): RedirectResponse
    {
        $redirect = redirect()->route($route, $routeParams)->with('error', $message);

        if (!empty($data)) {
            $redirect->with($data);
        }

        return $redirect;
    }

    /**
     * Redirect to a route with a warning message.
     *
     * @param string $route
     * @param string $message
     * @param array $routeParams
     * @param array $data
     * @return RedirectResponse
     */
    protected function redirectToRouteWithWarning(string $route, string $message, array $routeParams = [], array $data = []): RedirectResponse
    {
        $redirect = redirect()->route($route, $routeParams)->with('warning', $message);

        if (!empty($data)) {
            $redirect->with($data);
        }

        return $redirect;
    }

    /**
     * Redirect to a route with an info message.
     *
     * @param string $route
     * @param string $message
     * @param array $routeParams
     * @param array $data
     * @return RedirectResponse
     */
    protected function redirectToRouteWithInfo(string $route, string $message, array $routeParams = [], array $data = []): RedirectResponse
    {
        $redirect = redirect()->route($route, $routeParams)->with('info', $message);

        if (!empty($data)) {
            $redirect->with($data);
        }

        return $redirect;
    }

    /**
     * Redirect back with input and errors preserved.
     *
     * @param string $message
     * @return RedirectResponse
     */
    protected function redirectBackWithInput(string $message): RedirectResponse
    {
        return redirect()->back()
            ->withInput()
            ->with('error', $message);
    }

    /**
     * Handle exception and redirect with error.
     *
     * @param \Throwable $exception
     * @param string|null $customMessage
     * @param string|null $route
     * @return RedirectResponse
     */
    protected function handleExceptionRedirect(\Throwable $exception, ?string $customMessage = null, ?string $route = null): RedirectResponse
    {
        $message = $customMessage ?? 'An error occurred: ' . $exception->getMessage();

        // Log the exception
        logger()->error($message, [
            'exception' => get_class($exception),
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
        ]);

        if ($route) {
            return redirect()->route($route)->with('error', $message);
        }

        return redirect()->back()->with('error', $message);
    }
}

