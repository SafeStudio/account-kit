<?php

namespace AccountKit;

interface AccountKitInterface
{
    public function getRetrieveAccessTokenUrl($authorizationCode);

    public function getVerifyAccessTokenUrl($authorizationCode);

    public function retrieveAccessToken($authorizationCode);

    public function getAccountKitUserId($authorizationCode);

    public function getRefreshInterval($authorizationCode);

    public function getAccessToken($authorizationCode);

    public function verify($code);

    public function getUserPhone($authorizationCode);

    public function getUserEmail($authorizationCode);

}