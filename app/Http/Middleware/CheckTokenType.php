<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckTokenType
{
    protected $allowedName;

    public function __construct($allowedName = null)
    {
        $this->allowedName = $allowedName;
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, $tokenName)
    {
        $user = $request->user(); // Passport sets auth()->user() via auth:api

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $accessToken = $user->token();

        if (!$accessToken || $accessToken->name !== $tokenName) {
            return response()->json(['message' => 'Invalid token type.'], 401);
        }

        return $next($request);
    }
}
