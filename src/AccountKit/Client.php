<?php

namespace AccountKit;

use GuzzleHttp\Client as GuzzleClient;
use Psr\Log\LoggerInterface;

class Client implements AccountKitInterface
{
    const ACCOUNT_KIT_BASE_URI = 'https://graph.accountkit.com/';

    /**
     * @var Client
     */
    protected $client;

    /**
     * @var string
     */
    protected $accessTokenUrl;

    /**
     * @var string
     */
    protected $verifyTokenUrl;

    /**
     * @var string
     */
    private $appId;

    /**
     * @var string
     */
    private $appSecret;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Client constructor.
     * @param array $accountKitConfig
     * @param LoggerInterface $logger
     */
    public function __construct(array $accountKitConfig, LoggerInterface $logger)
    {
        $this->client = new GuzzleClient();
        $this->appId = $accountKitConfig['app_id'];
        $this->appSecret = $accountKitConfig['app_secret'];
        $accountKitVersion = $accountKitConfig['version'];
        $this->accessTokenUrl = self::ACCOUNT_KIT_BASE_URI.$accountKitVersion.'/access_token';

        $this->verifyTokenUrl = self::ACCOUNT_KIT_BASE_URI.$accountKitVersion.'/me?access_token=%s&appsecret_proof=%s';
        $this->logger = $logger;
    }

    public function getRetrieveAccessTokenUrl($authorizationCode)
    {
        return $this->accessTokenUrl.'?grant_type=authorization_code&code='.$authorizationCode.'&access_token=AA|'.$this->appId.'|'.$this->appSecret;
    }

    public function retrieveAccessToken($authorizationCode)
    {
        $data = $this->client->request('GET', $this->getRetrieveAccessTokenUrl($authorizationCode));
        $this->logger->info("Response from AccountKit\Client::retrieveAccessToken(): ".$data->getBody());

        return json_decode($data->getBody());
    }

    public function getAccountKitUserId($authorizationCode)
    {
        return $this->retrieveAccessToken($authorizationCode)->id;
    }

    public function getAccessToken($authorizationCode)
    {
        return $this->retrieveAccessToken($authorizationCode)->access_token;
    }

    public function getRefreshInterval($authorizationCode)
    {
        return $this->retrieveAccessToken($authorizationCode)->token_refresh_interval_sec;
    }

    public function getVerifyAccessTokenUrl($code, $codeType = CodeType::AUTHORIZATION_CODE)
    {
        $accessToken = null;
        if (CodeType::ACCESS_TOKEN === $codeType) {
            $this->logger->info("Code type is access token");
            $accessToken = $code;
        }
        if (CodeType::AUTHORIZATION_CODE === $codeType) {
            $this->logger->info("Code type is authorization code");
            $accessToken = $this->getAccessToken($code);
        }
        if (is_null($accessToken)) {
            throw new AccountKitException("Could not verify code");
        }
        $appSecretProof = hash_hmac('sha256', $accessToken, $this->appSecret);

        return sprintf($this->verifyTokenUrl, $accessToken, $appSecretProof);
    }

    public function verify($code, $codeType = CodeType::AUTHORIZATION_CODE)
    {
        $data = $this->client->request('GET', $this->getVerifyAccessTokenUrl($code, $codeType));
        $this->logger->info("Response from AccountKit\Client::verify(): ".$data->getBody());

        return json_decode($data->getBody());
    }

    public function getUserPhone($code, $codeType = CodeType::AUTHORIZATION_CODE)
    {
        return $this->verify($code, $codeType)->phone->number;
    }

    public function getUserEmail($code, $codeType = CodeType::AUTHORIZATION_CODE)
    {
        return $this->verify($code, $codeType)->email->address;
    }

}