<?php

namespace App\Integration\Strava;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final readonly class StravaTokenExchangeClient
{
    public function __construct(
        private HttpClientInterface $httpClient,
        #[Autowire(param: 'app.strava.client_id')] private string $clientId,
        #[Autowire(param: 'app.strava.client_secret')]  private string $clientSecret,
    ) {
    }

    /**
     * @return array{
     *     token_type?: string,
     *     expires_at?: int,
     *     expires_in?: int,
     *     refresh_token?: string,
     *     access_token?: string,
     *     athlete?: array<string, mixed>,
     *     scope?: string
     * }
     */
    public function exchangeCode(string $code): array
    {
        $response = $this->httpClient->request('POST', 'https://www.strava.com/oauth/token', [
            'body' => [
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
                'code' => $code,
                'grant_type' => 'authorization_code',
            ],
        ]);

        return $response->toArray();
    }
}
