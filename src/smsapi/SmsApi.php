<?php

namespace smsapi;

abstract class SmsApi
{
    protected $bDoNotAllowSendSms = false;
    protected $mLastResponse = null;

    public final function sendSms($sPhone, $sText, $sSmsId = 0, $sSender = false)
    {
        $sNrTelefon = $this->getCleanPhone($sPhone);

        if (empty($sNrTelefon)) {
            throw new \Exception('Numar de telefon invalid:' . $sPhone);
        }

        if (strlen($sText) > 640) {
            throw new \Exception('Numar de caractere din mesaj > 640:' . $sText . ' -= ' . strlen($sText) . ' =- caractere');
        }

        if ($this->bDoNotAllowSendSms) {
            throw new \Exception('Restrictie trimitere SMS-uri!');
        }

        $this->send($sPhone, $sText, $sSmsId, $sSender);
    }

    public final function setSmsSendRestriction($bool)
    {
        $this->bDoNotAllowSendSms = $bool;
    }

    public final function getLastResponse()
    {
        return $this->mLastResponse;
    }

    public function closeSession() {}

    protected abstract function send($sPhone, $sText, $sSmsId, $sSender);

    private final function getCleanPhone($sPhone)
    {
        $sPhone = str_replace('+', '', $sPhone);
        $sPhone = str_replace('-', '', $sPhone);
        $sPhone = str_replace('.', '', $sPhone);
        $sPhone = str_replace(';', '', $sPhone);
        $sPhone = str_replace('+', '', $sPhone);

        //validari telefon web2sms
        if (substr($sPhone, 0, 2) == '07') {
            //07PPXXXXXX
            $sPhone = substr($sPhone, 0, 10);
        } elseif (substr($sPhone, 0, 3) == '407') {
            //407PPXXXXXX
            $sPhone = substr($sPhone, 0, 11);
        } elseif (substr($sPhone, 0, 1) == '7') {
            //7PPXXXXXX
            $sPhone = substr($sPhone, 0, 9);
        } else {
            $sPhone = '';
        }

        return $sPhone;
    }

    protected final function getErrorMessage($sPhone, $sError)
    {
        return 'Eroare trimitere Sms la numarul: ' . $sPhone . ' prin "'.get_class($this).'" pt ca: ' . $sError;
    }
}