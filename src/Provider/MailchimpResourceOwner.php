<?php
namespace ExpandOnline\OAuth2\Client\Provider;

use League\OAuth2\Client\Provider\ResourceOwnerInterface;

/**
 * Class MailchimpResourceOwner
 * @package ExpandOnline\OAuth2\Client\Provider
 */
class MailchimpResourceOwner implements ResourceOwnerInterface
{

    /**
     * @var array
     */
    protected $response;

    /**
     * MailchimpResourceOwner constructor.
     * @param array $response
     */
    public function __construct(array $response = array())
    {
        $this->response = $response;
    }

    /**
     * @return null
     */
    public function getId()
    {
        return $this->response['user_id'];
    }

    /**
     * @return null
     */
    public function getName()
    {
        return $this->response['accountname'];
    }

    /**
     * @return mixed
     */
    public function getDc()
    {
        return $this->response['dc'];
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return $this->response;
    }
}
