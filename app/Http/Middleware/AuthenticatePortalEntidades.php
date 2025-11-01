<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticatePortalEntidades
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {

        $user = $request->getUser();
        $password = $request->getPassword();

        if (!$this->validateCredentials($user, $password)) {
            return response('Invalid credentials', 401)->header('WWW-Authenticate', 'Basic');
        }

        return $next($request);
    }

    private function validateCredentials($username, $password)
    {
        $validUsername = env('PORTAL_ENTIDADE_USERNAME');
        $validPassword = env('PORTAL_ENTIDADE_PASSWORD');

        if ($username==$validUsername && $password==$validPassword) {
            return true;
        }

        return false;
    }
}
