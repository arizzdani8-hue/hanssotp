<?php

namespace App\Http\Middleware;

use App\Models\ApiRequestLog;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyHmacSignature
{
    public function handle(Request $request, Closure $next): Response
    {
        $apiKey = $request->header('X-API-Key');
        $signature = $request->header('X-Signature');
        $timestamp = $request->header('X-Timestamp');

        if (!$apiKey || !$signature || !$timestamp) {
            return response()->json(['success' => false, 'message' => 'Missing authentication headers'], 401);
        }

        if (abs(time() - intval($timestamp)) > 300) {
            return response()->json(['success' => false, 'message' => 'Request expired'], 401);
        }

        $user = User::where('api_key', $apiKey)->first();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Invalid API key'], 401);
        }

        if ($user->is_banned) {
            return response()->json(['success' => false, 'message' => 'Account suspended'], 403);
        }

        $payload = $timestamp . $request->method() . $request->path() . $request->getContent();
        $expectedSignature = hash_hmac('sha256', $payload, $apiKey);

        if (!hash_equals($expectedSignature, $signature)) {
            return response()->json(['success' => false, 'message' => 'Invalid signature'], 401);
        }

        $request->setUserResolver(fn() => $user);

        $startTime = microtime(true);
        $response = $next($request);
        $responseTime = (microtime(true) - $startTime) * 1000;

        ApiRequestLog::create([
            'user_id' => $user->id,
            'method' => $request->method(),
            'endpoint' => $request->path(),
            'request_body' => $request->all(),
            'response_code' => $response->getStatusCode(),
            'response_body' => json_decode($response->getContent(), true),
            'ip_address' => $request->ip(),
            'response_time' => $responseTime,
        ]);

        return $response;
    }
}
