<?php

namespace App\Security;

use App\Dto\Me\MeDetailDtoFactory;
use App\Entity\User;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;
use Symfony\Component\Serializer\SerializerInterface;

final class JsonLoginSuccessHandler implements AuthenticationSuccessHandlerInterface
{

    public function __construct(
        private MeDetailDtoFactory $meDetailDtoFactory,
        private SerializerInterface $serializer,
    ) {
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


        $json = $this->serializer->serialize(
            $this->meDetailDtoFactory->fromUser($user),
            'json',
        );

        return new JsonResponse(
            data: $json,
            status: JsonResponse::HTTP_OK,
            json: true,
        );

    }
}
