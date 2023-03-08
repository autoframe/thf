<?php
declare(strict_types=1);

namespace Autoframe\Core\Date\Hours\Language;

class AfrDateHoursLanguageFactory implements AfrDateHoursLanguageInterface
{
    private string $sLanguageClass;

    /**
     * @param string $sLanguage
     */
    public function __construct(string $sLanguage = '')
    {
        $sNsClassBase = __NAMESPACE__ . '\\AfrDateHoursLanguage';
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
    public function getHoursNames(): array
    {
        return (new $this->sLanguageClass())->getHoursNames();
    }

    /**
     * @return string[]
     */
    public function getHoursNamesShort(): array
    {
        return (new $this->sLanguageClass())->getHoursNamesShort();
    }
}