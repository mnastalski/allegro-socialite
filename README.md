
# Allegro Socialite

Allegro OAuth2 Provider for [Laravel Socialite](https://laravel.com/docs/master/socialite).

## Requirements

- PHP >=7.4

## Installation

```shell
composer require mnastalski/allegro-socialite
```

## Usage

### Add configuration to `config/services.php`

```php
'allegro' => [    
    'client_id' => env('ALLEGRO_CLIENT_ID'),
    'client_secret' => env('ALLEGRO_CLIENT_SECRET'),
    'redirect' => env('ALLEGRO_REDIRECT'),
],
```

To make sandbox mode configurable you can add the following to the above configuration:

```php
'sandbox' => env('ALLEGRO_SANDBOX', false),
```

### Example usage:

```php
use Laravel\Socialite\Facades\Socialite;

$provider = Socialite::driver('allegro');

$provider->redirect();
$provider->user();
$provider->getAccessToken();
```

For `$provider->user()` to work the `allegro:api:profile:read` permission must be granted.

### Refreshing the access token:

```php
Socialite::driver('allegro')->getRefreshTokenResponse($refreshToken);
```

Example response:

```php
[
    'access_token' => '...',
    'token_type' => 'bearer',
    'refresh_token' => '...',
    'expires_in' => 43199,
    'scope' => 'allegro:api:profile:read',
    'allegro_api' => true,
    'jti' => '...',
]
```
