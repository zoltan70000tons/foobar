<?php

namespace App\Traits;

use Illuminate\Http\Request;

trait JsonResponseTrait
{
    /**
     * Return a success response based on the request type.
     *
     * @param mixed $data
     * @param string $message
     * @param int $statusCode
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    protected function successResponse($data, $message = '', $statusCode = 200)
    {
        $response = [
            'status' => 'success',
            'message' => $message,
            'data' => $data,
        ];

        return $this->handleResponse($response, $statusCode);
    }

    /**
     * Return an error response based on the request type.
     *
     * @param string $message
     * @param int $statusCode
     * @param mixed $data
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    protected function errorResponse($message, $statusCode = 400, $data = null)
    {
        $response = [
            'status' => 'error',
            'message' => $message,
            'data' => $data,
        ];

        return $this->handleResponse($response, $statusCode);
    }

    /**
     * Handle the response based on the request type.
     *
     * @param array $response
     * @param int $statusCode
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    protected function handleResponse(array $response, int $statusCode)
    {
        $request = request();
        if ($request instanceof Request && $request->inertia()) {
            return redirect()->back()->with('flash', $response);
        } else {
            return response()->json($response, $statusCode);
        }
    }
}
