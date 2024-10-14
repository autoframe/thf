<?php


namespace Autoframe\Core\Session;
//TODO FIX!!!!!!!!!!!
//ini_set('session.cookie_samesite', 'None');
//ini_set('session.cookie_secure', 'On');
//ini_set('session.cookie_httponly', 'On');

trait AfrSessionConfig
{
    protected static bool $sessionConfigAfrApplied = false;
    protected static array $sessionConfigAfr = [];

    function sessionConfigAfr(array $options = [], string $mergeWithProfile = ''): array
    {
        if ($options || $mergeWithProfile) {
            if (
                $mergeWithProfile &&
                isset($options[AfrSessionFactory::SELECTED_PROFILE]) &&
                isset($options[$options[AfrSessionFactory::SELECTED_PROFILE]]) &&
                isset(self::$sessionConfigAfr[$mergeWithProfile]) &&
                $mergeWithProfile != $options[AfrSessionFactory::SELECTED_PROFILE]
            ) {
                $options[$options[AfrSessionFactory::SELECTED_PROFILE]] = array_merge(
                    self::$sessionConfigAfr[$mergeWithProfile],
                    $options[$options[AfrSessionFactory::SELECTED_PROFILE]]
                );
            }
            self::$sessionConfigAfr = AfrSessionFactory::mergeSettings(self::$sessionConfigAfr, $options);
        }
        return self::$sessionConfigAfr;
    }

    function session_started(): bool
    {
        return $this->session_status() === PHP_SESSION_ACTIVE;
    }

}