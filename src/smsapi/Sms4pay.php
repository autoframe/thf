<?php

namespace smsapi;

class Sms4pay extends SmsApi
{
    private $sUrl = 'http://sms.4pay.ro/smscust/api.send_sms';
    private $aCredentials = [
        'servID' => 1052,
        'password' => 'nf6Q6YGzaB'
    ];

    protected function send($sPhone, $sText, $sSmsId, $sSender)
    {
        $this->mLastResponse = $this->getWithCurl($this->getUrl($sPhone, $sText, $sSmsId));
        if (empty($this->mLastResponse) || strpos($this->mLastResponse, 'OK network') === false) {
            throw new \Exception($this->getErrorMessage($sPhone, $this->mLastResponse));
        }
    }

    private function getUrl($sPhone, $sText, $sSmsId)
    {
        $sUrl = $this->sUrl .
            "?servID=" . $this->aCredentials['servID'] . "&password=" . $this->aCredentials["password"] .
            "&msg_dst=" . urlencode($sPhone) . "&msg_text=" . urlencode($sText);
        if (!empty($sSmsId)) {
            $sUrl .= "&external_messageID=" . urlencode($sSmsId);
        }

        return $sUrl;
    }

    private function getWithCurl($sUrl, $iTimeout = 10)
    {
        $rCh = curl_init($sUrl);
        curl_setopt($rCh, CURLOPT_HEADER, 0);
        curl_setopt($rCh, CURLOPT_NOBODY, 0);
        curl_setopt($rCh, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($rCh, CURLOPT_CONNECTTIMEOUT, $iTimeout);
        $sResponse = curl_exec($rCh);
        curl_close($rCh);

        return $sResponse;
    }
}