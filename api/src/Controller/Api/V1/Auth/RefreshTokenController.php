<?php

namespace App\Controller\Api\V1\Auth;

use App\Dto\Auth\RefreshTokenResponseDto;
use App\Security\RefreshToken\InvalidRefreshTokenException;
use App\Security\RefreshToken\RefreshTokenExtractor;
use App\Security\RefreshToken\RefreshTokenService;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

final readonly class RefreshTokenController
{
    public function __construct(private RefreshTokenService      $refreshTokenService,
                                private RefreshTokenExtractor    $refreshTokenExtractor,
                                private JWTTokenManagerInterface $jwtTokenManager,
                                private EntityManagerInterface   $entityManager,
                                private SerializerInterface      $serializer)
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
            $refreshToken = $this->refreshTokenService->consume(
                plainToken: $extracted->plainToken,
                transportMode: $extracted->transportMode,
            );
        } catch (InvalidRefreshTokenException) {
            return $this->invalidRefreshTokenResponse();
        }

        $this->entityManager->flush();

        $dto = new RefreshTokenResponseDto(
            token: $this->jwtTokenManager->create($refreshToken->getUser()),
        );

        $json = $this->serializer->serialize($dto, 'json');

        return new JsonResponse(
            data: $json,
            status: JsonResponse::HTTP_OK,
            json: true,
        );
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
