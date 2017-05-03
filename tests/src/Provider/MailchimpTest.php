<?php
namespace ExpandOnline\OAuth2\Client\Test\Provider;

use ExpandOnline\OAuth2\Client\Provider\Mailchimp;
use League\OAuth2\Client\Tool\QueryBuilderTrait;
use Mockery as m;

/**
 * Class MailchimpTest
 * @package ExpandOnline\OAuth2\Client\Test\Provider
 */
class MailchimpTest extends \PHPUnit_Framework_TestCase
{
    use QueryBuilderTrait;

    /**
     * @var Mailchimp
     */
    protected $provider;

    /**
     * @var string
     */
    protected $accessTokenResponse;

    /**
     *
     */
    protected function setUp()
    {
        $this->provider = new Mailchimp([
            'clientId' => 'mock_client_id',
            'clientSecret' => 'mock_secret',
            'redirectUri' => 'none',
        ]);
        $this->accessTokenResponse = json_encode([
            'access_token' => 'mock_access_token',
            'expires_in' => 0,
            'scope' => null
        ]);
    }

    /**
     *
     */
    public function tearDown()
    {
        m::close();
        parent::tearDown();
    }

    /**
     *
     */
    public function testAuthorizationUrl()
    {
        $url = $this->provider->getAuthorizationUrl();
        $uri = parse_url($url);
        parse_str($uri['query'], $query);

        $this->assertEquals('/oauth2/authorize', $uri['path']);
        $this->assertArrayHasKey('client_id', $query);
        $this->assertArrayHasKey('redirect_uri', $query);
        $this->assertArrayHasKey('state', $query);
        $this->assertArrayHasKey('scope', $query);
        $this->assertArrayHasKey('response_type', $query);
        $this->assertArrayHasKey('approval_prompt', $query);
        $this->assertNotNull($this->provider->getState());
    }

    /**
     *
     */
    public function testScopes()
    {
        $scopeSeparator = ',';
        $options = ['scope' => [uniqid(), uniqid()]];
        $query = ['scope' => implode($scopeSeparator, $options['scope'])];
        $url = $this->provider->getAuthorizationUrl($options);
        $encodedScope = $this->buildQueryString($query);
        $this->assertContains($encodedScope, $url);
    }

    /**
     *
     */
    public function testGetBaseAccessTokenUrl()
    {
        $params = [];

        $url = $this->provider->getBaseAccessTokenUrl($params);
        $uri = parse_url($url);

        $this->assertEquals('/oauth2/token', $uri['path']);
    }

    /**
     *
     */
    public function testGetAccessToken()
    {
        $response = m::mock('Psr\Http\Message\ResponseInterface');
        $response->shouldReceive('getBody')->andReturn($this->accessTokenResponse);
        $response->shouldReceive('getHeader')->andReturn(['content-type' => 'json']);
        $response->shouldReceive('getStatusCode')->andReturn(200);

        $client = m::mock('GuzzleHttp\ClientInterface');
        $client->shouldReceive('send')->times(1)->andReturn($response);
        $this->provider->setHttpClient($client);

        $token = $this->provider->getAccessToken('authorization_code', ['code' => 'mock_authorization_code']);

        $this->assertEquals('mock_access_token', $token->getToken());
        $this->assertEquals(0, $token->getExpires());
        $this->assertNull($token->getRefreshToken());
    }

    /**
     *
     */
    public function testUserData()
    {
        $name = uniqid();
        $userId = rand(1000,9999);

        $postResponse = m::mock('Psr\Http\Message\ResponseInterface');
        $postResponse->shouldReceive('getBody')->andReturn($this->accessTokenResponse);
        $postResponse->shouldReceive('getHeader')->andReturn(['content-type' => 'json']);
        $postResponse->shouldReceive('getStatusCode')->andReturn(200);

        $userResponse = m::mock('Psr\Http\Message\ResponseInterface');
        $userResponse->shouldReceive('getBody')->andReturn(json_encode([
            'user_id' => $userId,
            'accountname' => $name,
            'dc' => 'us10'
        ]));
        $userResponse->shouldReceive('getHeader')->andReturn(['content-type' => 'json']);
        $userResponse->shouldReceive('getStatusCode')->andReturn(200);

        $client = m::mock('GuzzleHttp\ClientInterface');
        $client->shouldReceive('send')
            ->times(2)
            ->andReturn($postResponse, $userResponse);
        $this->provider->setHttpClient($client);

        $token = $this->provider->getAccessToken('authorization_code', ['code' => 'mock_authorization_code']);
        $user = $this->provider->getResourceOwner($token);

        $this->assertEquals($userId, $user->getId());
        $this->assertEquals($userId, $user->toArray()['user_id']);
        $this->assertEquals($name, $user->getName());
        $this->assertEquals($name, $user->toArray()['accountname']);
        $this->assertEquals('us10', $user->getDc());
        $this->assertEquals('us10', $user->toArray()['dc']);
    }

    /**
     * @expectedException League\OAuth2\Client\Provider\Exception\IdentityProviderException
     **/
    public function testExceptionThrownWhenErrorObjectReceived()
    {
        $message = uniqid();
        $status = rand(400,600);
        $postResponse = m::mock('Psr\Http\Message\ResponseInterface');
        $postResponse->shouldReceive('getBody')->andReturn('{"error": "'.$message.'"}');
        $postResponse->shouldReceive('getHeader')->andReturn(['content-type' => 'json']);
        $postResponse->shouldReceive('getStatusCode')->andReturn($status);

        $client = m::mock('GuzzleHttp\ClientInterface');
        $client->shouldReceive('send')
            ->times(1)
            ->andReturn($postResponse);
        $this->provider->setHttpClient($client);
        $token = $this->provider->getAccessToken('authorization_code', ['code' => 'mock_authorization_code']);
    }
}
