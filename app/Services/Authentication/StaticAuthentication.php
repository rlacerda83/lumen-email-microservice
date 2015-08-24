<?php

namespace App\Services\Authentication;

use Illuminate\Http\Request;
use Dingo\Api\Routing\Route;
use Dingo\Api\Auth\Provider\Authorization;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class StaticAuthentication extends Authorization
{
    public function authenticate(Request $request, Route $route)
    {
        $authHeader = $request->headers->get('api-token');
        $key = substr($authHeader, strpos($authHeader, ':') + 1);

        if ($key != env('APP_KEY', rand(0, 1000))) {
            throw new UnauthorizedHttpException('Static', 'Invalid authentication credentials.');
        }

        return true;
    }

    public function getAuthorizationMethod()
    {
        return 'api-token';
    }
}
