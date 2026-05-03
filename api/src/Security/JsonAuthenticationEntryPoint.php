<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;

final class JsonAuthenticationEntryPoint implements AuthenticationEntryPointInterface
{
    public function start(
        Request $request,
        ?AuthenticationException $authException = null,
    ): JsonResponse {
        return new JsonResponse([
            'error' => [
                'code' => 'AUTHENTICATION_REQUIRED',
                'message' => 'Authentication is required.',
            ],
        ], JsonResponse::HTTP_UNAUTHORIZED);
    }
}
