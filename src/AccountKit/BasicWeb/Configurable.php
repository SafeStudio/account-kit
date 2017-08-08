<?php

namespace AccountKit\BasicWeb;

trait Configurable
{
    /**
     * @var string
     */
    private $verifyPhoneUrl;

    private function configAccountKit(array $accountKitConfig)
    {
        $accountKitAppId = $accountKitConfig['app_id'];
        $accountKitVersion = $accountKitConfig['version'];
        $this->verifyPhoneUrl = 'https://www.accountkit.com/'.$accountKitVersion.'/basic/dialog/sms_login/?app_id='.$accountKitAppId;
    }
}