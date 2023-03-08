<?php
declare(strict_types=1);

namespace Autoframe\Core\Date\Month\Language;

class AfrDateMonthLanguageFactory implements AfrDateMonthLanguageInterface
{
    private string $sLanguageClass;

    /**
     * @param string $sLanguage
     */
    public function __construct(string $sLanguage = '')
    {
        $sNsClassBase = __NAMESPACE__ . '\\AfrDateMonthLanguage';
        if (!$sLanguage) {
            //TODO get from framework config
        }

        if ($sLanguage) {
            $sLanguageClass = $sNsClassBase . ucwords(strtolower(substr($sLanguage, 0, 2)));
            if (class_exists($sLanguageClass)) {
                $this->sLanguageClass = $sLanguageClass;
                return;
            }
        }
        $this->sLanguageClass = $sNsClassBase . 'En';

    }

    /**
     * @return string[]
     */
    public function getMonthNames(): array
    {
        return (new $this->sLanguageClass())->getMonthNames();
    }

    /**
     * @return string[]
     */
    public function getMonthNamesShort(): array
    {
        return (new $this->sLanguageClass())->getMonthNamesShort();
    }
}