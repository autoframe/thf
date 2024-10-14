<?php

namespace Autoframe\Core\Http\breadcrumb;
//TODO SCHEMA_ORG": "https://github.com/spatie/schema-org",

class bbbcr
{
    public static function obtineBreadcrumbJsonLd(array $aNavInfo, string $sSiteUrl = 'https://example.com/')
    {
        $aContent = [];
        $aContent['@context'] = 'https://schema.org';
        $aContent['@type'] = 'BreadcrumbList';
        $aContent['itemListElement'] = [];
        $iPosition = 0;
        foreach ($aNavInfo as $aNavInfoItem) {
            if (empty($aNavInfoItem['text'])) {
                continue;
            }
            $aItem = [];
            $aItem['@type'] = 'ListItem';
            $aItem['position'] = ++$iPosition;
            $aItem['name'] = $aNavInfoItem['text'];
            $aItem['item'] = !empty($aNavInfoItem['link']) ? $aNavInfoItem['link'] : '';

            if (!empty($aNavInfoItem['link'])) {
                if (strpos($aItem['item'], $sSiteUrl) === false) {
                    $aItem['item'] = $sSiteUrl . ltrim($aItem['item'], '/');
                }
            }
            $aContent['itemListElement'][] = (object)$aItem;
        }

        $sJsonLd = '<script type="application/ld+json">';
        $sJsonLd .= json_encode((object)$aContent);
        $sJsonLd .= '</script>';
        return $sJsonLd;
    }
}