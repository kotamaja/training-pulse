<?php

namespace App\Integration\Strava\Api;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final readonly class StravaTokenRefreshClient
{
    public function __construct(private HttpClientInterface                                   $httpClient,
                                #[Autowire(param: 'app.strava.client_id')] private string     $clientId,
                                #[Autowire(param: 'app.strava.client_secret')] private string $clientSecret)
    {

    }

    /**
     * @return array{
     *     token_type?: string,
     *     access_token?: string,
     *     refresh_token?: string,
     *     expires_at?: int,
     *     expires_in?: int
     * }
     */
    public function refreshAccessToken(string $refreshToken): array
    {
        $response = $this->httpClient->request('POST', 'https://www.strava.com/oauth/token', [
            'body' => [
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
                'grant_type' => 'refresh_token',
                'refresh_token' => $refreshToken,
            ],
        ]);

        $statusCode = $response->getStatusCode();
        $content = $response->getContent(false);

        if ($statusCode >= 400) {
            throw new \RuntimeException(sprintf(
                'Strava token refresh failed with HTTP %d: %s',
                $statusCode,
                $content,
            ));
        }

        return $response->toArray();
    }
}
