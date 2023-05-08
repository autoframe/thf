<?php
declare(strict_types=1);

namespace Autoframe\Core\Socket;

use Exception;

class AfrCacheSocketServer extends AfrCacheSocketAbstract
{

    private string $sHearthBeatFile = __DIR__ . DIRECTORY_SEPARATOR . 'hearthBeat';
    protected array $aClients = [];




    /**
     * @return bool
     */
    protected function socketCreateBindListen(): bool
    {
        $oConfig = $this->getSelectedConfig();
        $this->socketClose();

        $oConfig->mSocket = socket_create($oConfig->socketCreateDomain, $oConfig->socketCreateType, $oConfig->socketCreateProtocol);
        if ($oConfig->mSocket === false) {
            $this->socketErrors('socket_create(' . $this->sConfigName . ') failed: ' . socket_strerror(socket_last_error()));
            return false;
        }

        if (socket_bind($oConfig->mSocket, $oConfig->socketIp, $oConfig->socketPort) === false) {
            $this->socketErrors('socket_bind(' . $this->sConfigName . ') failed: ' . socket_strerror(socket_last_error()));
            return false;
        }

        if (socket_listen($oConfig->mSocket, $oConfig->iSocketListenBacklog) === false) {
            $this->socketErrors('socket_listen(' . $this->sConfigName . ') failed: ' . socket_strerror(socket_last_error()));
            return false;
        }
        $this->aClients = [];
        $this->doHearthBeat(0);
        return true;
    }


    /**
     * @param string $sData
     * @param $key
     * @return false|int|mixed
     */
    protected function socketWrite(string $sData, $key)
    {
        $oConfig = $this->getSelectedConfig();

        $result = socket_write($this->aClients[$key], $sData, strlen($sData));
        if ($result === false) {
            $this->socketErrors('socket_write(' . $this->sConfigName . ') failed: ' .
                socket_strerror(socket_last_error($this->aClients[$key])));
        }
        //off reading(0);writing(1);both(2)
        socket_shutdown($this->aClients[$key], 1);
        socket_close($this->aClients[$key]);
        unset($this->aClients[$key]);
        $oConfig->iWriteCounter++;
        return $result;
    }

    /**
     * @param $key
     * @return string
     */
    protected function socketRead($key):string
    {
        $oConfig = $this->getSelectedConfig();
        $sRead = '';
        while ($sBuf = socket_read($this->aClients[$key], $oConfig->socketReadBuffer)) {
            $sRead .= $sBuf;
        }
        if (strlen($sRead) > 0) {
            $oConfig->iReadCounter++;
            socket_shutdown($this->aClients[$key], 0); //off reading(0);writing(1);both(2)
        }
        return $sRead;
    }

    /**
     * @param $iFlag
     * @return void
     */
    private function doHearthBeat($iFlag): void
    {
        file_put_contents(
            $this->sHearthBeatFile,
            $this->getMicroTime(true) . PHP_EOL,
            $iFlag
        );
    }


    /**
     * @param $iTimeLimit
     * @param $errorReporting
     * @param $bEcho
     * @return void
     * @throws Exception
     */
    public function run($iTimeLimit = 0, $errorReporting = E_ALL, $bEcho = true)
    {
        error_reporting($errorReporting);
        set_time_limit($iTimeLimit);

        if ($bEcho && (strpos(strtolower(php_sapi_name()), 'cli') === false)) {
            ob_end_flush();
            // Turn on implicit output dump, so we'll see what we're getting as it arrives.
            ob_implicit_flush();
        }
        if ($bEcho) {
            echo "Starting:<br>\n";
        }
        $oConfig = $this->getSelectedConfig();
        if (!$this->socketCreateBindListen()) {
            throw new Exception('Unable to initiate the server: ' . implode('; ', $oConfig->aErrors));
        }

        do {
            $this->doHearthBeat(FILE_APPEND);
            $aRead = array_merge([$oConfig->mSocket], $this->aClients);

            // Set up a blocking call to socket_select
            $write = $except = null;
            if (socket_select(
                    $aRead,
                    $write,
                    $except,
                    $oConfig->iSocketSelectSeconds,
                    $oConfig->iSocketSelectUSeconds
                ) < 1) {
                //    SocketServer::debug("Problem blocking socket_select?");
                continue;
            }

            // Handle new Connections
            if (in_array($oConfig->mSocket, $aRead)) {
                if (!$this->socketAccept()) {
                    //continue;
                    //TODO if the connection is not accepted, then continue or break?
                    break;
                }
            }

            // Handle Input
            foreach ($this->aClients as $key => $oClient) { // for each oClient
                if (!in_array($oClient, $aRead)) {
                    continue;
                }

                $sRead = $this->socketRead($key);
                if (strlen($sRead) < 1) {
                    continue; //just connected without any send
                }

                if ($sRead != 'shutdown') {
                    $talkback = $this->prepareTalkback($sRead, $key);
                    $this->socketWrite($talkback,$key);
                    $this->talkbackWrite($key, $sRead, $talkback);
                    break;
                }
                if ($sRead == 'shutdown') {
                    $this->socketWrite($sRead,$key);
                    $this->listMemoryStats('end');
                    print_r($GLOBALS);
                    break 2;
                }
            }
        } while (true);
        $this->socketClose();
    }

    /**
     * @return bool
     */
    private function socketAccept(): bool
    {
        $oConfig = $this->getSelectedConfig();
        if (($msgSock = socket_accept($oConfig->mSocket)) === false) {
            $this->socketErrors('socket_accept(' . $this->sConfigName . ') failed: ' .
                socket_strerror(socket_last_error($oConfig->mSocket)));
            return false;
        }
        $this->aClients[] = $msgSock;
        return true;
    }

    /**
     * @param $key
     * @param string $sRead
     * @param string $talkback
     * @return void
     */
    private function talkbackWrite($key, string $sRead, string $talkback): void
    {
        if ($key % 100 == 0) {
            echo "\n";
            $this->listMemoryStats($key);
            echo "\n<hr>\n";
        }
        echo "#$key CL: " . count($this->aClients) . "; RECV_SV: " . round(strlen($sRead) / 1024, 2) . ' KB; ';
        echo 'SENT_SV: ' . round(strlen($talkback) / 1024, 2) . ' KB; ';
        echo substr($talkback, 0, 51) . "...<br>\n";
    }



    function listMemoryStats($key)
    {
        $peak_memory = number_format(memory_get_peak_usage() / 1024, 0, '.', ',') . " kb";
        $end_memory = number_format(memory_get_usage() / 1024, 0, '.', ',') . " kb";
        echo "#$key; peak: $peak_memory; end: $end_memory <br>\n";
    }

    function addSvResponseLengthCheckup(string &$sReply, int $iContainerLen = 32)
    {

        $sTotalLen = (string)(strlen($sReply) + $iContainerLen);
        $sContainer = str_repeat('@', $iContainerLen - strlen($sTotalLen)) . $sTotalLen;
        $sReply .= $sContainer;
    }

    /**
     * @param string $sRead
     * @param $key
     * @return string
     */
    private function prepareTalkback(string $sRead, $key): string
    {
        $talkback = '';
        for ($i = 0; $i < 10; $i++) {
            for ($j = 0; $j < 10; $j++) {
                $talkback .= str_repeat((string)$i, 102) . "\n";
            }
        }

        $md5 = substr($sRead, 0, 32);
        $talkback = "#{$key}~~$md5~~" . strlen($sRead) . "~~\n" . str_repeat($talkback, rand(1, 25)) . '~~' . $sRead;
        $this->addSvResponseLengthCheckup($talkback);
        return $talkback;
    }

}