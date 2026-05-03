<?php

namespace App\Controller\Api\V1\Auth;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final class LogoutController
{
    #[Route('/api/v1/auth/logout', name: 'api_v1_auth_logout', methods: ['POST'])]
    public function __invoke(): JsonResponse
    {
        return new JsonResponse(null, 204);
    }
}
