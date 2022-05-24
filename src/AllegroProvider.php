<?php

namespace Allegro\Socialite;

use GuzzleHttp\RequestOptions;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;
use Laravel\Socialite\Two\AbstractProvider;
use Laravel\Socialite\Two\User;

class AllegroProvider extends AbstractProvider
{
    public const AUTH_HOST_LIVE = 'https://allegro.pl/';

    public const AUTH_HOST_SANDBOX = 'https://allegro.pl.allegrosandbox.pl/';

    public const API_HOST_LIVE = 'https://api.allegro.pl/';

    public const API_HOST_SANDBOX = 'https://api.allegro.pl.allegrosandbox.pl/';

    /**
     * @inheritDoc
     */
    protected $scopeSeparator = ' ';

    /**
     * @inheritDoc
     */
    protected $usesPKCE = true;

    /**
     * Cached access token response.
     *
     * @var array|null
     */
    private ?array $accessTokenResponse = null;

    /**
     * @return array
     */
    public function getAccessToken(): array
    {
        $response = $this->getAccessTokenResponse($this->getCode());

        return [
            'access_token' => Arr::get($response, 'access_token'),
            'refresh_token' => Arr::get($response, 'refresh_token'),
            'expires_in' => Arr::get($response, 'expires_in'),
            'scopes' => explode($this->scopeSeparator, Arr::get($response, 'scope', '')),
        ];
    }

    /**
     * @inheritDoc
     */
    public function getAccessTokenResponse($code)
    {
        if ($this->accessTokenResponse) {
            return $this->accessTokenResponse;
        }

        return $this->accessTokenResponse = parent::getAccessTokenResponse($code);
    }

    /**
     * Get the refresh token response for the given token.
     *
     * @param string $refreshToken
     * @return array
     */
    public function getRefreshTokenResponse(string $refreshToken): array
    {
        $response = $this->getHttpClient()->post($this->getTokenUrl(), [
            RequestOptions::HEADERS => [
                'Accept' => 'application/vnd.allegro.public.v1+json',
                'Authorization' => 'Basic ' . base64_encode("{$this->clientId}:{$this->clientSecret}"),
            ],
            RequestOptions::QUERY => $this->getRefreshTokenFields($refreshToken),
        ]);

        return json_decode($response->getBody(), true);
    }

    /**
     * @inheritDoc
     */
    protected function getAuthUrl($state)
    {
        return $this->buildAuthUrlFromBase(
            $this->authUrl() . 'auth/oauth/authorize',
            $state
        );
    }

    /**
     * @inheritDoc
     */
    protected function getTokenUrl()
    {
        return $this->authUrl() . 'auth/oauth/token';
    }

    /**
     * @inheritDoc
     */
    protected function getUserByToken($token)
    {
        $response = $this->getHttpClient()->get(
            $this->apiUrl() . 'me',
            [
                RequestOptions::HEADERS => [
                    'Accept' => 'application/vnd.allegro.public.v1+json',
                    'Authorization' => 'Bearer ' . $token,
                ],
            ]
        );

        return json_decode((string) $response->getBody(), true);
    }

    /**
     * @inheritDoc
     */
    protected function mapUserToObject(array $user)
    {
        return (new User())->setRaw($user)->map([
            'id' => $user['id'],
            'nickname' => $user['login'],
            'name' => "{$user['firstName']} {$user['lastName']}",
            'email' => $user['email'],
            'avatar' => null,
        ]);
    }

    /**
     * @param string $token
     * @return array
     */
    protected function getRefreshTokenFields(string $token): array
    {
        return [
            'grant_type' => 'refresh_token',
            'refresh_token' => $token,
            'redirect_uri' => $this->redirectUrl,
        ];
    }

    /**
     * @return bool
     */
    private function isLive(): bool
    {
        return Config::get('services.allegro.sandbox', false) === false;
    }

    /**
     * @return string
     */
    private function authUrl(): string
    {
        return $this->isLive() ? self::AUTH_HOST_LIVE : self::AUTH_HOST_SANDBOX;
    }

    /**
     * @return string
     */
    private function apiUrl(): string
    {
        return $this->isLive() ? self::API_HOST_LIVE : self::API_HOST_SANDBOX;
    }
}
