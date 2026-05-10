<?php

namespace App\Controller\Api\V1\Auth;

use App\Security\RefreshToken\RefreshTokenCookieFactory;
use App\Security\RefreshToken\RefreshTokenExtractor;
use App\Security\RefreshToken\RefreshTokenService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final readonly class LogoutController
{
    public function __construct(private RefreshTokenService       $refreshTokenService,
                                private RefreshTokenExtractor     $refreshTokenExtractor,
                                private RefreshTokenCookieFactory $refreshTokenCookieFactory,
                                private EntityManagerInterface    $entityManager)
    {
    }

    #[Route('/api/v1/auth/logout', name: 'api_v1_auth_logout', methods: ['POST'])]
    public function __invoke(Request $request): JsonResponse
    {
        $plainRefreshToken = $this->refreshTokenExtractor->extractPlainToken($request);

        if ($plainRefreshToken !== null) {
            $this->refreshTokenService->revokePlainToken($plainRefreshToken);
            $this->entityManager->flush();
        }

        $response = new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);

        /*
         * Même si aucun cookie n'était présent, on renvoie quand même
         * un cookie expiré pour nettoyer le navigateur.
         */
        $response->headers->setCookie(
            $this->refreshTokenCookieFactory->clear(),
        );

        return $response;
    }


}
