<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;

final class JsonLoginFailureHandler implements AuthenticationFailureHandlerInterface
{
    public function onAuthenticationFailure(Request                 $request,
                                            AuthenticationException $exception): JsonResponse
    {
        if ($exception instanceof CustomUserMessageAccountStatusException) {
            return new JsonResponse([
                'error' => [
                    'code' => 'ACCOUNT_DISABLED',
                    'message' => 'This account is disabled.',
                ],
            ], JsonResponse::HTTP_UNAUTHORIZED);
        }

        return new JsonResponse([
            'error' => [
                'code' => 'INVALID_CREDENTIALS',
                'message' => 'Invalid credentials.',
            ],
        ], JsonResponse::HTTP_UNAUTHORIZED);
    }
}
