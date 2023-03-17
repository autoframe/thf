<?php


namespace Autoframe\Core\Session;

use Autoframe\Core\Object\AfrObjectAbstractSingletonFactory;
/*
 * Choose Class Model:
 *
 * define('AFR_SESSION_CLASS_DEFAULT_NAMESPACE','Autoframe\Core\Session');
 * define('AFR_SESSION_CLASS_DEFAULT_CLASS_NAME','AfrSessionPhp');
 *
 * Session Config Model:
 * define('AFR_SESSION_CLASS_DEFAULT_PROFILE','afr&nocache&cookie&subdomainsSession&iMinutes=302400&samesite=strict');
 * define('AFR_SESSION_CLASS_DEFAULT_PROFILES_ARR',[AFR_SESSION_CLASS_DEFAULT_PROFILE=>['name' => 'AFRSSIDZ',]]);
 *
 * Custom Captcha Model:
 * define('AFR_SESSION_CLASS_DEFAULT_CAPTCHA_PROFILE','captcha');
 * define('AFR_SESSION_CLASS_DEFAULT_CAPTCHA_PROFILES_ARR', [AFR_SESSION_CLASS_DEFAULT_CAPTCHA_PROFILE => ['name' => 'AFRSSIDY',]]);
 *
 * Usage:
 * $session = AfrSessionFactory::getInstance();
 * $session->session_start();
 *
 * Others:
 * $session->session_start(array $aSessionOptions = [], string $mergeWithProfile = '');
 * print_r($session->sessionConfigAfr());
 *
 * */


class AfrSessionFactory extends AfrObjectAbstractSingletonFactory
{

    protected static string $sDefaultNamespace = __NAMESPACE__;
    public const DEFAULT_NAMESPACE = 'AFR_SESSION_CLASS_DEFAULT_NAMESPACE';       // Autoframe\Core\Session

    protected static string $sDefaultClassName = 'AfrSessionPhp';
    public const DEFAULT_CLASS_NAME = 'AFR_SESSION_CLASS_DEFAULT_CLASS_NAME';     // AfrSessionPhp

    public const DEFAULT_PROFILE = 'AFR_SESSION_CLASS_DEFAULT_PROFILE';           // afr&nocache&cookie&subdomainsSession&iMinutes=302400&samesite=strict
    public const DEFAULT_PROFILES_ARR = 'AFR_SESSION_CLASS_DEFAULT_PROFILES_ARR'; // []

    public const DEFAULT_CAPTCHA_PROFILE = 'AFR_SESSION_CLASS_DEFAULT_CAPTCHA_PROFILE';           // captcha
    public const DEFAULT_CAPTCHA_PROFILES_ARR = 'AFR_SESSION_CLASS_DEFAULT_CAPTCHA_PROFILES_ARR'; // []


    public const SELECTED_PROFILE = 'sSelectedProfile';

    protected static function customConstruct()
    {

    }

    /**
     * @return object
     * @throws \Autoframe\Core\Exception\AfrException
     */
    final public static function getInstance(): object
    {
        /** @var AfrSessionPhp $oInstance */
        $oInstance = parent::getInstance();

        if (!$oInstance->sessionConfigAfr()) {
            $sProfile = '';
            if (
                defined(static::DEFAULT_PROFILE) &&
                is_string(constant(static::DEFAULT_PROFILE))) {
                $sProfile = constant(static::DEFAULT_PROFILE);
            }
            $aFrameworkProfiles = static::loadFrameworkProfiles($sProfile, true);
            if (
                defined(static::DEFAULT_CAPTCHA_PROFILE) &&
                is_string(constant(static::DEFAULT_CAPTCHA_PROFILE))) {
                $sProfile = $aFrameworkProfiles[self::SELECTED_PROFILE] . '&' . constant(static::DEFAULT_CAPTCHA_PROFILE);
                $aFrameworkProfiles = static::loadFrameworkProfiles($sProfile, true);
            }

            $oInstance->sessionConfigAfr($aFrameworkProfiles);
        }
        return $oInstance;
    }

    /**
     * @param string $sProfile
     * @param bool $bSetAsSelected
     * @return array
     */
    private static function loadFrameworkProfiles(string $sProfile = '', bool $bSetAsSelected = false): array
    {
        $aFrameworkProfiles = $aProfile = [];

        if (!$sProfile) {
            $sProfile = 'afr&nocache&cookie&subdomainsSession&iMinutes=302400&samesite=strict';
        }
        if (isset($_SERVER['AFR_ENV']) && strtolower($_SERVER['AFR_ENV']) === 'dev') {
            if (strpos($sProfile, 'cookie') !== false && strpos($sProfile, 'cookie_dev') === false) {
                $sProfile = str_replace('cookie', 'cookie_dev', $sProfile);
            }
        }
        if ($bSetAsSelected) {
            $aFrameworkProfiles[self::SELECTED_PROFILE] = $sProfile;
        }
        $aFrameworkProfiles[$sProfile] = [];

        $aTmp = parse_url('?' . $sProfile);
        if ($aTmp['query']) {
            parse_str($aTmp['query'], $aProfile);
        }

        $iMinutes = 60 * 24 * 7 * 30; //in minute
        if (isset($aProfile['iMinutes']) && is_numeric($aProfile['iMinutes'])) {
            $iMinutes = abs(intval($aProfile['iMinutes']));
        }
        $sSamesite = 'lax';//  strict|lax|none
        if (isset($aProfile['samesite'])) {
            $sSamesite = $aProfile['samesite'];
        }
        $sHostname = $_SERVER['SERVER_NAME'];
        if (substr_count($sProfile, 'subdomainsSession') && !filter_var($sHostname, FILTER_VALIDATE_IP)) {
            $sHostname = explode('.', $sHostname);
            $sHostname = '.' . $sHostname[count($sHostname) - 1]; //add all subdomains
        }

        $aFrameworkProfiles['rcf'] = [
            'read_and_close' => false,
        ];

        $aFrameworkProfiles['rct'] = [
            'read_and_close' => true,
        ];

        $aFrameworkProfiles['nocache'] = [
            'cache_limiter' => 'nocache',
            //session.cookie_lifetime specifies the lifetime of the cookie in seconds which is sent to the browser. The value 0 means "until the browser is closed." Defaults to 0.
        ];

        $aFrameworkProfiles['captcha'] = [
            'cache_limiter' => 'nocache',
        ];

        $aFrameworkProfiles['public'] = [
            'cache_expire' => $iMinutes, //minutes
            'cache_limiter' => 'public',
        ];

        $aFrameworkProfiles['private_no_expire'] = [
            'cache_expire' => $iMinutes, //minutes
            'cache_limiter' => 'private_no_expire',
        ];

        $aFrameworkProfiles['private'] = [
            'cache_expire' => $iMinutes, //minutes
            'cache_limiter' => 'private',
        ];

        $aFrameworkProfiles['gc'] = [
            'gc_maxlifetime' => $iMinutes * 60,
            'gc_divisor' => 1000,
            'gc_probability' => 1,
        ];

        $aFrameworkProfiles['afr'] =
            $aFrameworkProfiles['gc'] + [
                'sid_bits_per_character' => 6,
                'name' => 'AFRSSID',
            ];

        $aFrameworkProfiles['cookie'] = [
            //session.cookie_lifetime specifies the lifetime of the cookie in seconds which is sent to the browser. The value 0 means "until the browser is closed." Defaults to 0.
            'cookie_lifetime' => $iMinutes * 60,
            'cookie_path' => '/',
            'cookie_domain' => $sHostname,  //  $sHostname = '.example.com';
            'cookie_secure' => true,
            'cookie_httponly' => true, // prevent JavaScript access to session cookie
            'cookie_samesite' => $sSamesite,//  strict|lax|none
        ];

        $aFrameworkProfiles['cookie_dev'] = array_merge(
            $aFrameworkProfiles['cookie'], [
            'cookie_secure' => false,
            'cookie_samesite' => '',//  strict|lax|''
        ]);

        if (defined(static::DEFAULT_PROFILES_ARR) && is_array(constant(static::DEFAULT_PROFILES_ARR))) {
            $aFrameworkProfiles = self::mergeSettings($aFrameworkProfiles, constant(static::DEFAULT_PROFILES_ARR));
        }
        if (defined(static::DEFAULT_CAPTCHA_PROFILES_ARR) && is_array(constant(static::DEFAULT_CAPTCHA_PROFILES_ARR))) {
            $aFrameworkProfiles = self::mergeSettings($aFrameworkProfiles, constant(static::DEFAULT_CAPTCHA_PROFILES_ARR));
        }

        foreach ($aProfile as $key => $val) {
            if (isset($aFrameworkProfiles[$key])) {
                $aFrameworkProfiles[$sProfile] = array_merge(
                    $aFrameworkProfiles[$sProfile],
                    $aFrameworkProfiles[$key]
                );
            }
        }
        return $aFrameworkProfiles;
    }

    /**
     * @param array $aOriginal
     * @param array $aNew
     * @return array
     */
    public static function mergeSettings(array $aOriginal, array $aNew): array
    {
        foreach ($aNew as $sNewKey => $mNewProfile) {
            if (isset($aOriginal[$sNewKey]) && is_array($aOriginal[$sNewKey])&& is_array($mNewProfile)) {
                $aOriginal[$sNewKey] = array_merge($aOriginal[$sNewKey], $mNewProfile);
            } else {
                $aOriginal[$sNewKey] = $mNewProfile;
            }
        }
        return $aOriginal;
    }

}