<?php
declare(strict_types=1);

namespace Autoframe\Core\Socket;

use Exception;

abstract class AfrCacheSocketAbstract
{
    //https://www.techinpost.com/only-one-usage-of-each-socket-address-is-normally-permitted/


    protected static array $aInstances = [];

    public const DEFAULT_CONFIG_NAME = 'default';
    protected string $sConfigName = '';

    /**
     * @var AfrCacheSocketConfig[]
     */
    protected array $aConfigs = [];

    protected function __construct(){}

    /**
     * @throws Exception
     */
    protected function __clone()
    {
        throw new Exception('Cannot clone a singleton');
    }

    public function __wakeup()
    {
        throw new Exception("Cannot unserialize singleton");
    }

    /**
     * @return self
     */
    public static function getInstance(): object
    {
        if (!isset(self::$aInstances[static::class])) {
            return self::$aInstances[static::class] = new static();
        }
        return self::$aInstances[static::class];
    }

    /**
     * @return AfrCacheSocketConfig
     */
    public function getSelectedConfig(): AfrCacheSocketConfig
    {
        return $this->aConfigs[$this->sConfigName];
    }

    /**
     * @param AfrCacheSocketConfig $oConfig
     * @return void
     */
    public function defineSocketClientConfig(AfrCacheSocketConfig $oConfig): void
    {
        $this->socketClose();
        $this->aConfigs[$oConfig->sConfigName] = $oConfig;
    }

    /**
     * @param string $sConfigName
     * @return bool
     */
    public function isDefinedSocketConfig(string $sConfigName): bool
    {
        return !empty($this->aConfigs[$sConfigName]);
    }

    /**
     * @return array
     */
    public function getDefinedSocketConfigs(): array
    {
        return $this->aConfigs;
    }

    /**
     * @param string $sConfigName
     * @return AfrCacheSocketConfig
     * @throws Exception
     */
    public function selectSocketConfig(string $sConfigName): AfrCacheSocketConfig
    {
        if (!$this->isDefinedSocketConfig($sConfigName)) {
            if ($sConfigName === self::DEFAULT_CONFIG_NAME) {
                if ($this->addDefaultConfigToServerListIfMissing() && $this->isDefinedSocketConfig($sConfigName)) {
                    $this->sConfigName = $sConfigName;
                    return $this->aConfigs[$sConfigName];
                }
            }
            throw new Exception('Invalid Cache Sock config name: ' . $sConfigName);
        }
        $this->sConfigName = $sConfigName;
        return $this->aConfigs[$sConfigName];
    }


    /**
     * @return bool
     */
    private function addDefaultConfigToServerListIfMissing(): bool
    {
        if (!$this->isDefinedSocketConfig(self::DEFAULT_CONFIG_NAME)) {
            $this->defineSocketClientConfig(new AfrCacheSocketConfig(self::DEFAULT_CONFIG_NAME));
            return true;
        }
        return false;
    }

    /**
     * @return void
     */
    protected function socketSetOptions(): void
    {
        $oConfig = $this->getSelectedConfig();
        foreach ($oConfig->socketSetOption as $aOptions) {
            socket_set_option($oConfig->mSocket, $aOptions[0], $aOptions[1], $aOptions[2]);
        }
    }

    /**
     * @return void
     */
    protected function socketClose(): void
    {
        if ($this->sConfigName && !empty($this->aConfigs[$this->sConfigName])) {
            $oConfig = $this->getSelectedConfig();
            if ($oConfig->mSocket) {
                @socket_shutdown($oConfig->mSocket, 2);
                socket_close($oConfig->mSocket);
                $oConfig->mSocket = null;
            }
        }
    }

    /**
     * @param string $sErr
     * @return void
     */
    protected function socketErrors(string $sErr): void
    {
        $oConfig = $this->getSelectedConfig();
        $oConfig->aErrors[] = $sErr;
    }


    /**
     * @param bool $bAsFloat
     * @return float
     */
    protected function getMicroTime(bool $bAsFloat = true): float
    {
        $tmp = explode(' ', (string)microtime($bAsFloat));
        return floatval(isset($tmp[1]) ? floatval($tmp[1]) + floatval($tmp[0]) : $tmp[0]);
    }

    /**
     * @param int $iKb
     * @param string $sOneKbEll
     * @return string
     */
    public static function generateRandomText(int $iKb = 10, string $sOneKbEll = "\n"): string
    {
        $talkback = '';
        for ($i = 0; $i < 10; $i++) {
            for ($j = 0; $j < 10; $j++) {
                if ($j % 2) {
                    $talkback .= str_repeat((string)$i, 11);
                } else {
                    $talkback .= str_repeat(chr(rand(64, 90)), 10);
                }
            }
        }
        $talkback = substr($talkback, 0, 1024 - strlen($sOneKbEll)) . $sOneKbEll;
        return str_repeat($talkback, $iKb);
    }
}