# Mailchimp Provider for OAuth 2.0 Client

[![Latest Version](https://img.shields.io/github/release/expandonline/oauth2-mailchimp.svg?style=flat-square)](https://github.com/expandonline/oauth2-mailchimp/releases)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![Total Downloads](https://img.shields.io/packagist/dt/expandonline/oauth2-mailchimp.svg?style=flat-square)](https://packagist.org/packages/expandonline/oauth2-mailchimp)

This package provides Mailchimp OAuth 2.0 support for the PHP League's [OAuth 2.0 Client](https://github.com/thephpleague/oauth2-client).

## Installation

To install, use composer:

```
composer require expandonline/oauth2-mailchimp
```

## Usage

Usage is the same as The League's OAuth client, using `\ExpandOnline\OAuth2\Client\Provider\Mailchimp` as the provider.

### Authorization Code Flow

```php
$provider = new ExpandOnline\OAuth2\Client\Provider\Mailchimp([
    'clientId'          => '{mailchimp-client-id}',
    'clientSecret'      => '{mailchimp-client-secret}',
    'redirectUri'       => 'https://example.com/callback-url'
]);

if (!isset($_GET['code'])) {

    // If we don't have an authorization code then get one
    $authUrl = $provider->getAuthorizationUrl();
    $_SESSION['oauth2state'] = $provider->getState();
    header('Location: '.$authUrl);
    exit;

// Check given state against previously stored one to mitigate CSRF attack
} elseif (empty($_GET['state']) || ($_GET['state'] !== $_SESSION['oauth2state'])) {

    unset($_SESSION['oauth2state']);
    exit('Invalid state');

} else {

    // Try to get an access token (using the authorization code grant)
    $token = $provider->getAccessToken('authorization_code', [
        'code' => $_GET['code']
    ]);

    // Optional: Now you have a token you can look up a users profile data
    try {

        // We got an access token, let's now get the user's details
        $user = $provider->getResourceOwner($token);

        // Use these details to create a new profile
        printf('Hello %s!', $user->getId());

    } catch (Exception $e) {

        // Failed to get user details
        exit('Oh dear...');
    }

    // Use this to interact with an API on the users behalf
    echo $token->getToken();
}
```

## Refreshing a Token
Mailchimp's OAuth implementation does not use refresh tokens. Access tokens are valid until a user revokes access manually, or until an app deauthorizes itself.