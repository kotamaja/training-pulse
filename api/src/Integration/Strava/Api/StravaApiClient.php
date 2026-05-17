<?php

namespace App\Integration\Strava\Api;

use DateTimeImmutable;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

final readonly class StravaApiClient
{
    private const BASE_URL = 'https://www.strava.com/api/v3';

    public function __construct(private HttpClientInterface $httpClient)
    {
    }

    /**
     * @return array<string, mixed>
     */
    public function getAthlete(string $accessToken): array
    {
        $response = $this->httpClient->request('GET', self::BASE_URL . '/athlete', [
            'headers' => [
                'Authorization' => 'Bearer ' . $accessToken,
                'Accept' => 'application/json',
            ],
        ]);

        return $this->toArrayOrFail($response, 'Strava athlete request failed');
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function listAthleteActivities(string             $accessToken,
                                          int                $page = 1,
                                          int                $perPage = 10,
                                          ?DateTimeImmutable $after = null,
                                          ?DateTimeImmutable $before = null,): array
    {
        $query = [
            'page' => $page,
            'per_page' => $perPage,
        ];

        if ($after !== null) {
            $query['after'] = $after->getTimestamp();
        }

        if ($before !== null) {
            $query['before'] = $before->getTimestamp();
        }

        $response = $this->httpClient->request('GET', self::BASE_URL . '/athlete/activities', [
            'headers' => [
                'Authorization' => 'Bearer ' . $accessToken,
                'Accept' => 'application/json',
            ],
            'query' => $query,
        ]);

        $data = $this->toArrayOrFail($response, 'Strava activities request failed');

        if (!array_is_list($data)) {
            throw new \RuntimeException('Expected Strava activities response to be a list.');
        }

        return $data;
    }

    /**
     * @param list<string> $keys
     *
     * @return array<string, mixed>
     */
    public function getActivityStreams(string $accessToken,
                                       string $activityId,
                                       array  $keys = ['latlng']): array
    {
        $response = $this->httpClient->request(
            'GET',
            sprintf('%s/activities/%s/streams', self::BASE_URL, rawurlencode($activityId)),
            [
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken,
                    'Accept' => 'application/json',
                ],
                'query' => [
                    // Symfony HttpClient encode correctement le tableau en query string.
                    // Strava attend une liste de keys et key_by_type=true.
                    'keys' => implode(',', $keys),
                    'key_by_type' => 'true',
                ],
            ],
        );

        return $this->toArrayOrFail($response, 'Strava activity streams request failed');
    }

    /**
     * @return array<mixed>
     */
    private function toArrayOrFail(ResponseInterface $response,
                                   string            $message): array
    {
        $statusCode = $response->getStatusCode();
        $content = $response->getContent(false);

        if ($statusCode >= 400) {
            throw new \RuntimeException(sprintf(
                '%s with HTTP %d: %s',
                $message,
                $statusCode,
                $content,
            ));
        }

        return $response->toArray();
    }
}
