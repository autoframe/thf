<?php

namespace smsapi;

class Factory
{
    private static function sendSmsRestricted()
    {
        return defined('RMFW_RESTRICTIE_TRIMITERE_SMS') && RMFW_RESTRICTIE_TRIMITERE_SMS;
    }
    /**
     * @param $name
     * @return SmsApi
     * @throws \Exception
     */
    public static function get($name)
    {
        $obj = null;
        switch ($name) {
            case 'sms4pay':
                $obj = new Sms4pay();
                break;
            case 'web2sms':
                $obj = new Web2sms();
                break;
            case 'smslink':
                $obj = new Smslink();
                break;
            default:
                throw new \Exception('Nu exista nicio implementare pentru "' . $name . '"');
        }

        $obj->setSmsSendRestriction(self::sendSmsRestricted());

        return $obj;
    }
}