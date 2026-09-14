<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminAuditAccess
{
    /**
     * Spots incoming requests to verify HTTP Basic Authentication credentials
     * before granting access to sensitive admin audit panels
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Admin credentials with fallback
        $adminUser = env('AUDIT_ADMIN_USER', 'admin');
        $adminPass = env('AUDIT_ADMIN_PASS', 'secret123');

        // Inspect the HTTP Basic Authentication headers sent by the browser
        if ($request->getUser() !== $adminUser || $request->getPassword() !== $adminPass) {
            // It will issue an 401 unauthorized access
            return response('Acces neautorizat. Introduceți credențialele de administrator.', 401, [
                'WWW-Authenticate' => 'Basic realm="RCA Admin Area"'
            ]);
        }

        // Allows request to proceed through the application pipeline if credentials are correct
        return $next($request);
    }
}
