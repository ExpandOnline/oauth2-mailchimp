<?php
namespace ExpandOnline\OAuth2\Client\Provider;

use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Tool\BearerAuthorizationTrait;
use Psr\Http\Message\ResponseInterface;

/**
 * Class Mailchimp
 * @package ExpandOnline\OAuth2\Client\Provider
 */
class Mailchimp extends AbstractProvider
{

    use BearerAuthorizationTrait;

    /**
     * @return string
     */
    public function getBaseAuthorizationUrl()
    {
        return 'https://login.mailchimp.com/oauth2/authorize';
    }

    /**
     * @param array $params
     * @return string
     */
    public function getBaseAccessTokenUrl(array $params)
    {
        return 'https://login.mailchimp.com/oauth2/token';
    }

    /**
     * @param AccessToken $token
     * @return string
     */
    public function getResourceOwnerDetailsUrl(AccessToken $token)
    {
        return 'https://login.mailchimp.com/oauth2/metadata';
    }

    /**
     * @return array
     */
    protected function getDefaultScopes()
    {
        return [];
    }

    /**
     * @param ResponseInterface $response
     * @param array|string $data
     * @throws IdentityProviderException
     */
    protected function checkResponse(ResponseInterface $response, $data)
    {
        if ($response->getStatusCode() >= 400) {
            throw new IdentityProviderException(
                $data['error'] ?: $response->getReasonPhrase(),
                $response->getStatusCode(),
                $response->getBody()
            );
        }
    }

    /**
     * @param array $response
     * @param AccessToken $token
     * @return MailchimpResourceOwner
     */
    protected function createResourceOwner(array $response, AccessToken $token)
    {
        return new MailchimpResourceOwner($response);
    }

}
