<?php

namespace App\Exceptions;

use Exception;
use Symfony\Component\HttpFoundation\Response;

class InvalidBipIdException extends Exception {
    public function render($request) {
        if ($request->expectsJson()) {
            return response()->json(
                [
                    'message' => 'Error creating payment, wrong BIP_ID format!',
                ],
                Response::HTTP_UNPROCESSABLE_ENTITY,
            );
        }

        return redirect()->back()->with('error', 'Error creating payment, wrong BIP_ID format!');
    }
}
