<?php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Spatie\Permission\Traits\HasPermissions;

trait HandlePermissions {
    /**
     * Check permissions and execute a callback if authorized.
     *
     * @param array $permissions
     * @param callable $callback
     * @param mixed ...$params Additional parameters to pass to the callback
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response
     */
    protected function withPermission(array $permissions, callable $callback, ...$params) {
        $user = Auth::user();

        // Check if the user has any of the permissions
        if (!$user->hasAnyPermission($permissions)) {
            // Redirect if the user does not have permission
            return Redirect::route('access.denied')->with('error', 'You are not allowed to do that.');
        }

        // If user has permission, execute the callback with additional parameters
        return call_user_func_array($callback, $params);
    }
}
