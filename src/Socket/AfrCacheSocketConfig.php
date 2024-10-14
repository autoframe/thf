<?php
declare(strict_types=1);

namespace Autoframe\Core\Socket;

class AfrCacheSocketConfig
{
    public $mSocket = null;
    public array $aErrors = [];
    public float $fFailedToConnect = 0.0;

    public string $sConfigName = 'default';
    public int $socketCreateDomain = AF_INET;
    public int $socketCreateType = SOCK_STREAM;
    public int $socketCreateProtocol = SOL_TCP;
    public array $socketSetOption = [
        [SOL_SOCKET, SO_RCVTIMEO, ['sec' => 1, 'usec' => 0]],
        [SOL_SOCKET, SO_SNDTIMEO, ['sec' => 1, 'usec' => 0]],
    ];
    public string $socketIp = '127.0.0.1';
    public int $socketPort = 11317;

    public int $socketListenBacklogQueue = 1024; //5-10 TCP

    public int $socketReadBuffer = 2048;
    public int $iReadCounter = 0;
    public int $iWriteCounter = 0;
    public int $iConnectedCounter = 0;
    public int $iSocketListenBacklog = 1000;
    public int $iSocketSelectSeconds = 10;
    public int $iSocketSelectUSeconds = 0;

    /**
     * @param string $sConfigName
     */
    public function __construct(string $sConfigName)
    {
        if(strlen($sConfigName)){
            $this->sConfigName = $sConfigName;
        }
    }
}