<?php
declare(strict_types=1);

namespace Autoframe\Core\Socket;

use Exception;

class AfrCacheSocketClient extends AfrCacheSocketAbstract
{

    public float $fRequestTimeMs = 0.0;

    /**
     * @throws Exception
     */
    public function sendRequest(string $sData): array
    {
        $fStart = $this->getMicroTime();
        $aReturn = [];

        if ($this->socketCreate()) {
            $this->socketSetOptions();
            if ($this->socketConnect()) {
                if ($this->socketWrite($sData)) { //Bufferd
                    $sResponse = $this->socketRead();
                    $aReturn[] = $sResponse;
                }
            }
        }
        $this->socketClose();
        $this->fRequestTimeMs = round(($this->getMicroTime() - $fStart) * 1000, 4);

        return $aReturn;

    }


    /**
     * @return bool
     */
    protected function awaitingForTimeoutRecovery(): bool
    {
        $oConfig = $this->getSelectedConfig();
        return ($oConfig->fFailedToConnect && $oConfig->fFailedToConnect > $this->getMicroTime());
    }

    /**
     * @param int $iMsToWait
     * @return void
     */
    protected function setAwaitForTimeoutRecovery(int $iMsToWait = 500): void
    {
        $oConfig = $this->getSelectedConfig();
        $oConfig->fFailedToConnect = $this->getMicroTime() + ($iMsToWait / 1000);
    }
    /**
     * @return bool
     */
    protected function socketCreate(): bool
    {
        $oConfig = $this->getSelectedConfig();
        $this->socketClose();
        if ($this->awaitingForTimeoutRecovery()) {
            return false;
        }
        $oConfig->mSocket = socket_create($oConfig->socketCreateDomain, $oConfig->socketCreateType, $oConfig->socketCreateProtocol);
        if ($oConfig->mSocket === false) {
            $this->socketErrors('socket_create(' . $this->sConfigName . ') failed: ' . socket_strerror(socket_last_error()));
            $this->setAwaitForTimeoutRecovery(60*60*1000); //if the socket could not be created, don't bother to do so
            return false;
        }
        return true;
    }



    /**
     * @return bool
     */
    protected function socketConnect(): bool
    {
        //https://www.techinpost.com/only-one-usage-of-each-socket-address-is-normally-permitted/


        $oConfig = $this->getSelectedConfig();

        if ($this->awaitingForTimeoutRecovery()) { //check for failed attempts
            return false;
        }
        if($oConfig->mSocket){ //already connected
        //    return true;
        }

        $result = socket_connect($oConfig->mSocket, $oConfig->socketIp, $oConfig->socketPort);
        if ($result === false) {
            $sErr = socket_strerror(socket_last_error($oConfig->mSocket));
            $this->socketErrors('socket_connect(' . $this->sConfigName . ') failed: ' .$sErr);
            //if the socket could not connect, we wasted 2 seconds already! retry in one minute
            $this->setAwaitForTimeoutRecovery(60*1000);

            $this->socketClose();
            return false;
        }
        $oConfig->iConnectedCounter++;
        return true;
    }



    /**
     * @param string $sData
     * @return false|int
     */
    protected function socketWrite(string $sData)
    {
        $oConfig = $this->getSelectedConfig();
        if ($this->awaitingForTimeoutRecovery()) {
            return false;
        }
        $result = socket_write($oConfig->mSocket, $sData, strlen($sData));
        if ($result === false) {
            $this->socketErrors('socket_write(' . $this->sConfigName . ') failed: ' .
                socket_strerror(socket_last_error($oConfig->mSocket)));
        }
        socket_shutdown($oConfig->mSocket, 1); //off reading(0);writing(1);both(2)
        $oConfig->iWriteCounter++;
        return $result;
    }

    /**
     * @return string
     */
    protected function socketRead(): string
    {
        $sOut = '';
        $oConfig = $this->getSelectedConfig();
        if ($this->awaitingForTimeoutRecovery()) {
            return $sOut;
        }
        while ($sBuf = socket_read($oConfig->mSocket, $oConfig->socketReadBuffer)) {
            $sOut .= $sBuf;
        }
        if ($oConfig->fFailedToConnect && strlen($sOut) > 25) { //connection recovered
            $oConfig->fFailedToConnect = 0;
        }
        $oConfig->iReadCounter++;
        socket_shutdown($oConfig->mSocket, 0); //off reading(0);writing(1);both(2)
        return $sOut;
    }


}
