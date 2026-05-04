<?php

namespace App\Actions\Fortify;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Fortify;

class EnsureUserIsNotDisabled
{
    public function __invoke(Request $request, Closure $next): mixed
    {
        $user = User::query()
            ->where(Fortify::username(), $request->input(Fortify::username()))
            ->first();

        if ($user && $user->is_disabled) {
            throw ValidationException::withMessages([
                Fortify::username() => [__('This account has been disabled.')],
            ]);
        }

        return $next($request);
    }
}
