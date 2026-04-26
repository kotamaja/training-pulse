<?php

namespace App\Security;

use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\DisabledException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

final class DevAuthenticator extends AbstractAuthenticator
{
    private const DEV_USER_EMAIL = 'dev@trainingpulse.local';

    public function __construct(
        private readonly UserRepository $userRepository,
    ) {
    }

    public function supports(Request $request): ?bool
    {
        return str_starts_with($request->getPathInfo(), '/api');
    }

    public function authenticate(Request $request): Passport
    {
        return new SelfValidatingPassport(
            new UserBadge(
                self::DEV_USER_EMAIL,
                function (string $userIdentifier) {
                    $user = $this->userRepository->findOneBy([
                        'email' => $userIdentifier,
                    ]);

                    if (!$user) {
                        throw new UserNotFoundException(sprintf(
                            'Dev user "%s" was not found.',
                            $userIdentifier,
                        ));
                    }

                    if (!$user->isEnabled()) {
                        throw new DisabledException(sprintf(
                            'Dev user "%s" is disabled.',
                            $userIdentifier,
                        ));
                    }

                    return $user;
                }
            )
        );
    }

    public function onAuthenticationSuccess(
        Request $request,
        TokenInterface $token,
        string $firewallName,
    ): ?Response {
        return null;
    }

    public function onAuthenticationFailure(
        Request $request,
        AuthenticationException $exception,
    ): ?Response {
        return new Response(
            'Dev authentication failed.',
            Response::HTTP_UNAUTHORIZED,
        );
    }
}
