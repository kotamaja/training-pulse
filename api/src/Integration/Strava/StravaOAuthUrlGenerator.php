<?php

namespace App\Integration\Strava;

use Symfony\Component\DependencyInjection\Attribute\Autowire;

final readonly class StravaOAuthUrlGenerator
{
    public function __construct(#[Autowire(param: 'app.strava.client_id')] private string    $clientId,
                                #[Autowire(param: 'app.strava.redirect_url')] private string $redirectUri)
    {
    }


    public function generateAuthorizeUrl(): string
    {
        $query = http_build_query([
            'client_id' => $this->clientId,
            'redirect_uri' => $this->redirectUri,
            'response_type' => 'code',
            'approval_prompt' => 'auto',
            'scope' => 'read,activity:read_all',
        ], '', '&', PHP_QUERY_RFC3986);

        return 'https://www.strava.com/oauth/authorize?' . $query;
    }
}
