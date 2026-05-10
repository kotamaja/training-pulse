<?php

namespace App\Controller\Api\V1\Auth;

use App\Dto\Auth\RefreshTokenResponseDto;
use App\Security\RefreshToken\InvalidRefreshTokenException;
use App\Security\RefreshToken\RefreshTokenCookieFactory;
use App\Security\RefreshToken\RefreshTokenExtractor;
use App\Security\RefreshToken\RefreshTokenMode;
use App\Security\RefreshToken\RefreshTokenService;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

final readonly class RefreshTokenController
{
    public function __construct(private RefreshTokenService       $refreshTokenService,
                                private RefreshTokenExtractor     $refreshTokenExtractor,
                                private RefreshTokenCookieFactory $refreshTokenCookieFactory,
                                private JWTTokenManagerInterface  $jwtTokenManager,
                                private EntityManagerInterface    $entityManager,
                                private SerializerInterface       $serializer)
    {
    }

    #[Route('/api/v1/auth/refresh', name: 'api_v1_auth_refresh', methods: ['POST'])]
    public function __invoke(Request $request): JsonResponse
    {
        $extracted = $this->refreshTokenExtractor->extract($request);

        if ($extracted === null) {
            return $this->invalidRefreshTokenResponse();
        }

        try {
            $oldRefreshToken = $this->refreshTokenService->consume(
                plainToken: $extracted->plainToken,
                transportMode: $extracted->transportMode,
            );

            $user = $oldRefreshToken->getUser();
            $mode = $oldRefreshToken->getMode();

            $newRefreshToken = $this->refreshTokenService->rotate($oldRefreshToken);

        } catch (InvalidRefreshTokenException) {
            return $this->invalidRefreshTokenResponse();
        }


        $dto = new RefreshTokenResponseDto(
            token: $this->jwtTokenManager->create($user),
            refreshToken: $mode === RefreshTokenMode::Token ? $newRefreshToken->plainToken : null,
        );


        $this->entityManager->flush();


        $json = $this->serializer->serialize(
            $dto,
            'json',
            [
                'skip_null_values' => true,
            ],
        );


        $response = new JsonResponse(
            data: $json,
            status: JsonResponse::HTTP_OK,
            json: true,
        );

        if ($mode === RefreshTokenMode::Web) {
            $response->headers->setCookie(
                $this->refreshTokenCookieFactory->create($newRefreshToken->plainToken, $newRefreshToken->entity->getExpiresAt()),
            );
        }

        return $response;
    }

    private function invalidRefreshTokenResponse(): JsonResponse
    {
        return new JsonResponse([
            'error' => [
                'code' => 'INVALID_REFRESH_TOKEN',
                'message' => 'Invalid refresh token.',
            ],
        ], JsonResponse::HTTP_UNAUTHORIZED);
    }
}
