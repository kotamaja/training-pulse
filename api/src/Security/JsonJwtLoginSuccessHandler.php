<?php

namespace App\Security;

use App\Dto\Me\MeDetailDtoFactory;
use App\Entity\User;
use App\Security\RefreshToken\RefreshTokenCookieFactory;
use App\Security\RefreshToken\RefreshTokenMode;
use App\Security\RefreshToken\RefreshTokenService;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;
use Symfony\Component\Serializer\SerializerInterface;

final readonly class JsonJwtLoginSuccessHandler implements AuthenticationSuccessHandlerInterface
{
    public function __construct(private JWTTokenManagerInterface  $jwtTokenManager,
                                private MeDetailDtoFactory        $meDetailDtoFactory,
                                private RefreshTokenService       $refreshTokenService,
                                private RefreshTokenCookieFactory $refreshTokenCookieFactory,
                                private EntityManagerInterface    $entityManager,
                                private SerializerInterface       $serializer,
    )
    {
    }

    public function onAuthenticationSuccess(Request        $request,
                                            TokenInterface $token): JsonResponse
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            return new JsonResponse([
                'error' => [
                    'code' => 'INVALID_AUTHENTICATED_USER',
                    'message' => 'Invalid authenticated user.',
                ],
            ], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }

        $mode = $this->resolveRefreshTokenMode($request);

        if ($mode === null) {
            return new JsonResponse([
                'error' => [
                    'code' => 'INVALID_AUTH_MODE',
                    'message' => 'Invalid authentication mode.',
                ],
            ], JsonResponse::HTTP_BAD_REQUEST);
        }

        $issuedRefreshToken = $this->refreshTokenService->issue($user, $mode);

        $payload = [
            'token' => $this->jwtTokenManager->create($user),
            'session' => $this->meDetailDtoFactory->fromUser($user),
        ];

        if ($mode === RefreshTokenMode::Token) {
            $payload['refreshToken'] = $issuedRefreshToken->plainToken;
        }

        $this->entityManager->flush();

        $json = $this->serializer->serialize($payload, 'json');

        $response = new JsonResponse(
            data: $json,
            status: JsonResponse::HTTP_OK,
            json: true,
        );

        if ($mode === RefreshTokenMode::Web) {
            $response->headers->setCookie(
                $this->refreshTokenCookieFactory->create(
                    $issuedRefreshToken->plainToken,
                    $issuedRefreshToken->entity->getExpiresAt(),
                ),
            );
        }

        return $response;
    }

    private function resolveRefreshTokenMode(Request $request): ?RefreshTokenMode
    {
        $data = json_decode($request->getContent(), true);

        if (!is_array($data)) {
            return RefreshTokenMode::Web;
        }

        $mode = $data['mode'] ?? 'web';

        return RefreshTokenMode::tryFrom((string)$mode);
    }
}
