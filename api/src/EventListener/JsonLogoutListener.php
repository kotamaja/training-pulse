<?php

namespace App\EventListener;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Http\Event\LogoutEvent;

#[AsEventListener(
    event: LogoutEvent::class,
    method: '__invoke',
    dispatcher: 'security.event_dispatcher.main',
)]
final class JsonLogoutListener
{
    public function __invoke(LogoutEvent $event): void
    {
        $event->setResponse(new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT));
    }
}
