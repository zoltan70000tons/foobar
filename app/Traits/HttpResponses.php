<?php

namespace App\Traits;

trait HttpResponses {
    /**
     * Return a new JSON response for a successful request.
     *
     * @param  mixed  $data
     * @param  int  $status
     * @return \Illuminate\Http\JsonResponse
     */
    public function successResponse($data, $status = 200) {
        return response()->json($data, $status);
    }

    /**
     * Return a new JSON response for a failed request.
     *
     * @param  string  $message
     * @param  int  $status
     * @return \Illuminate\Http\JsonResponse
     */
    public function errorResponse($message, $status) {
        return response()->json(['error' => $message, 'status' => $status], $status);
    }
}
