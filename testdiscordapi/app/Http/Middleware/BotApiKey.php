<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class BotApiKey
{
    public function handle(Request $request, Closure $next)
    {
        $provided = $request->bearerToken();
        $expected = config('services.bot.key');

        if (!$provided || !$expected || !hash_equals($expected, $provided)) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        return $next($request);
    }
}
