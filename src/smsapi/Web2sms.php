<?php

namespace smsapi;

class Web2sms extends SmsApi
{
    private $sUrl = 'https://www.web2sms.ro/send/message';
    private $aCredentials = [
        'username' => 'imobiliarero',
        'password' => 'H9jVnYKk',
    ];

    private $apiKey = 'ca527774318d634a74001efab38455a31bfba22c';
    private $secret = '9778ba3d02ffc9e198a00d1a19e0b979c44c46f705df556897d6e771e66f40219b0bd03cfacf7830d8b04cb41f567ed14aecd3fcf7d92f07a65e13217666181a';

    public function closeSession() {}

    protected function send($sPhone, $sText, $sSmsId, $sSender)
    {
        $nonce = time();
        $method = "POST";
        $url = "/send/message";
        $string = $this->apiKey . $nonce . $method . $url .
               $sPhone . $sText . $this->secret;

        $sSender == empty($sSender) ? "" : $sSender;

        $signature = hash('sha512', $string);
        $data = json_encode([
            "apiKey" => $this->apiKey,
            "sender" => $sSender,
            "recipient" => $sPhone,
            "message" => $sText,
            "scheduleDatetime" => "",
            "validityDatetime" => "",
            "callbackUrl" => "",
            "userData" => "",
            "visibleMessage" => "",
            "nonce" => $nonce
        ]);

        $ch = curl_init($this->sUrl);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_USERPWD, $this->apiKey . ":" . $signature);
        $header = [
            'Content-type: application/json',
            'Content-length: ' . strlen($data),
        ];

        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FAILONERROR, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_CIPHER_LIST, 'DEFAULT@SECLEVEL=1');

        $response = curl_exec($ch);

        if ($response === false) {
            throw new \Exception($this->getErrorMessage($sPhone, 'Curl error: ' . curl_error($ch)));
        } else {
            $header_size = curl_getinfo($ch,CURLINFO_HEADER_SIZE);
            $result['header'] = substr($response, 0, $header_size);
            $result['body'] = substr( $response, $header_size );
            $result['http_code'] = curl_getinfo($ch,CURLINFO_HTTP_CODE);
            $result['last_url'] = curl_getinfo($ch,CURLINFO_EFFECTIVE_URL);

            $body = json_decode($result['body'], true);

            $error = 'unkown';

            // pe aici intram daca am putut parsa raspunsul
            if (isset($body['error']['code']) ) {

                // error code = 0 inseamna succes
                if ($body['error']['code'] != 0) {
                    if (isset($body['error']['message'])) {
                        $error = $body['error']['code'] . " " . $body['error']['message'];
                    }
                    throw new \Exception($this->getErrorMessage($sPhone, 'Send error: ' . $error));
                }
            } else {
                $error = 'nu am putut parsa raspunsul de la providerul sms.';
                throw new \Exception($this->getErrorMessage($sPhone, 'Send error: ' . $error));
            }
        }

        curl_close($ch);
    }

}
