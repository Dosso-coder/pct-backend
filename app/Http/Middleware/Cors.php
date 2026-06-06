<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class Cors
{
    public function handle(Request $request, Closure $next)
    {
        $allowedOrigins = [
            'http://localhost:5173',
            'http://localhost:5174',
            'http://localhost:5175',
            'https://pct-frontend-7s4q.vercel.app'
        ];
        $origin = $request->header('Origin');

        if (in_array($origin, $allowedOrigins)) {
            $corsOrigin = $origin;
        } else {
            $corsOrigin = 'http://localhost:5173'; // Default
        }

        if ($request->isMethod('OPTIONS')) {
            return response('', 200)
                ->header('Access-Control-Allow-Origin', $corsOrigin)
                ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS')
                ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With, Accept, Origin, X-Auth-Token')
                ->header('Access-Control-Allow-Credentials', 'true');
        }

        return $next($request)
            ->header('Access-Control-Allow-Origin', $corsOrigin)
            ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS')
            ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With, Accept, Origin, X-Auth-Token')
            ->header('Access-Control-Allow-Credentials', 'true');
    }
}
