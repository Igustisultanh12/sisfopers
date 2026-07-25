<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class LogControllerActivity
{
    /**
     * Memproses dan mencatat seluruh aktivitas pengaksesan Controller ke storage/logs/laravel.log
     */
    public function handle(Request $request, Closure $next): Response
    {
        $startTime = microtime(true);

        $response = $next($request);

        $executionTime = round((microtime(true) - $startTime) * 1000, 2);

        // Abaikan asset statis / build internal
        if ($request->is('_debugbar/*', 'vendor/*', 'build/*', 'storage/*')) {
            return $response;
        }

        $userId = auth()->check() ? auth()->id() : 'GUEST';
        $username = auth()->check() ? auth()->user()->username : 'Guest';
        $role = (auth()->check() && auth()->user()->role) ? auth()->user()->role->name : '-';

        $method = $request->method();
        $url = $request->fullUrl();
        $routeName = $request->route() ? $request->route()->getName() : '-';
        $ip = $request->ip();
        $status = $response->getStatusCode();

        // Sanitasi parameter sensitif
        $input = $request->except(['password', 'password_confirmation', '_token', 'secret', 'code']);

        Log::info("CONTROLLER ACTIVITY: [{$method}] {$url} | Status: {$status} | User: {$username} (ID: {$userId}, Role: {$role}) | Route: {$routeName} | IP: {$ip} | Duration: {$executionTime}ms", [
            'method' => $method,
            'url' => $url,
            'route' => $routeName,
            'user_id' => $userId,
            'username' => $username,
            'role' => $role,
            'status' => $status,
            'ip' => $ip,
            'duration_ms' => $executionTime,
            'payload' => !empty($input) ? $input : null,
        ]);

        return $response;
    }
}
