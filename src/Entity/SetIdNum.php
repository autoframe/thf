<?php


define('RM_AGENTIE_CALLCENTER', 'X7J9');
define('RMFW_DATASOURCE_ONLINE', 1);
define('RMFW_DATASOURCE_PORTAL', 2);
define('RMFW_DATASOURCE_JUNKYARD', 3);
define('RMFW_DATASOURCE_INDICE', 4);
define('RMFW_DATASOURCE_PLATFORMA', 5);
define('RMFW_DATASOURCE_SMP', 6);
define('RMFW_DATASOURCE_IMORADAR', 7);
define('RMFW_DATASOURCE_INTERNATIONAL', 8);

define( 'OfferInterface_APARTAMENT', 1 );
define( 'OfferInterface_VILA',       2 );
define( 'OfferInterface_SPATIU',     5 );
define( 'OfferInterface_TEREN',      4 );



class OfferInterface
{
    public string $sId = '';
    public string $sIdUnic = '';
    public string $tDataAdaugare = '2023-01-01 00:00:00';


}

class SetIdNum
{
    public const MAX_OFFERS = 32767;
    public array $aWildcards = [];
    public bool $bInternational = false;

    //TODO Adapt the code for redis/ memcahed/ real time cache
    //TODO Implement methods: read(string $key) and write(string $key, mixed $value, int tTl = 240 seconds)
    protected $oCacheIdOferta;
    protected int $iRecentCacheTtl = 240; //seconds

    public function __construct()
    {
        //TODO Adapt the code for redis/ memcahed/ real time cache
        //TODO Implement methods: read(string $key) and write(string $key, mixed $value, int tTl = 240 seconds)
        if (class_exists('CI_Cache')) {
            $oCacheIdOferta = CI_Cache::instance('idoferta');
        } else {
            $oCacheIdOferta = new CacheReadWrite();
        }
        $this->mockRandomWildcards();
    }

    /**
     * @return void
     */
    public function mockRandomWildcards(): void
    {
        $aTestDataSet = [
            [], //none
            [ //agency and agent
                'sIdStr' => 'X6EQ',
                'iIdAgent' => rand(1,50),
            ],
            [RM_AGENTIE_CALLCENTER], //callcenter agency


        ];
        $this->aWildcards = $aTestDataSet[rand(0, count($aTestDataSet) - 1)];
    }

    
    private function initConfig($iCategory = 0, $aDatasource = ['src' => RMFW_DATASOURCE_ONLINE])
    {
        if (empty($aDatasource['src'])) {
            throw new Exception('Are you serious ??? aDataSource e array.....');
        }

        if ($aDatasource['src'] === RMFW_DATASOURCE_INTERNATIONAL &&
            //$this->bInternationalAllow &&
            in_array($iCategory, [1, 2])
        ) {
            $this->bInternational = true;
        }

        if (!($iCategory == 0
            || $iCategory == OfferInterface_APARTAMENT || $iCategory == OfferInterface_VILA
            || $iCategory == OfferInterface_SPATIU || $iCategory == OfferInterface_TEREN)) {
            throw new \Exception(__CLASS__ . '::' . __FUNCTION__ . '::' . __LINE__ . '() > Invalid offer type!');
        }
        //TODO: implement according to CUBE
    }


    /**
     * @param OfferInterface $oOferta
     * @param $sMarkerOnline
     * @return void
     *
     * Generates numeric and string IDS codes for the offer to be added to the system
     *     and properly populate the members of the offer.
     * ============================================================================================
     * NEW ID OFFER 2007
     * ============================================================================================
     * ID FIRM & DB CODE            |    Information server IA + offer
     * --------------------------------------------------------------------------------------------
     * X    00000    00000    00000    |    00000    00000    00000    00000    00000
     *                                    \___/    \___/    \___________________/
     *                                     \         \         \_15 bits for the offer (max. 32767 offers)
     *                                      \       \
     *                                       \       \_ 5 bits for category (00=apartments, 01=villas, 10=lands, 11=spaces)
     *                                        \         + 3 bits reserved for the category in clear)
     *                                         \
     *                                        \_ 5 bits for server (00000=online, the rest server IA:
     *                                                              supports max. 30 de servers)
     * ============================================================================================
     */
    public function attachNewUniqueOfferId(OfferInterface &$oOferta, $sMarkerOnline = '0'): void
    {
        if ($this->isPrivateSellerOffer($oOferta)) {
            $this->attachPrivateSellerUniqueOfferIdNum($oOferta);
            return;
        }

        list($sIdAgentie, $sCateg, $sTable) = $this->getOfferCategoryAndTable($oOferta);
        $iNew = $this->getNextNewId($oOferta, $sCateg, $sTable, $sIdAgentie, $sMarkerOnline);
        list($sIdTemp, $iNew) = $this->checkForParallelSaveRequests($iNew, $sIdAgentie, $sMarkerOnline, $sCateg);


        list($aInternationalOffers, $aHistoryOffers, $aFirmOffers, $iNew) = $this->getDbOffersAndNextId($sCateg, $iNew, $sIdAgentie, $sTable, $sMarkerOnline);


        // have whe reached the maximum number of recordings? Whe search for a free id, without checking the junkyard
        if ($iNew < self::MAX_OFFERS) {
            $this->freeIdLookup(
                $iNew,
                [],
                $sIdAgentie,
                $sMarkerOnline,
                $sCateg,
                $sIdTemp,
                $iNew
            );
        } elseif ($iNew >= self::MAX_OFFERS) {
            if (count($aHistoryOffers) >= self::MAX_OFFERS - 1) {
                $this->freeIdLookup(
                    0,
                    [$aFirmOffers,$aInternationalOffers],
                    $sIdAgentie,
                    $sMarkerOnline,
                    $sCateg,
                    $sIdTemp,
                    $iNew
                );
            } elseif (count($aInternationalOffers) >= self::MAX_OFFERS - 1) {
                $this->freeIdLookup(
                    0,
                    [$aFirmOffers,$aHistoryOffers],
                    $sIdAgentie,
                    $sMarkerOnline,
                    $sCateg,
                    $sIdTemp,
                    $iNew
                );
            } else {
                $this->freeIdLookup(
                    0,
                    [$aFirmOffers,$aHistoryOffers,$aInternationalOffers],
                    $sIdAgentie,
                    $sMarkerOnline,
                    $sCateg,
                    $sIdTemp,
                    $iNew
                );
                if ($iNew >= self::MAX_OFFERS) {
                    $this->freeIdLookup(
                        0,
                        [$aFirmOffers],
                        $sIdAgentie,
                        $sMarkerOnline,
                        $sCateg,
                        $sIdTemp,
                        $iNew
                    );
                }
            }
        }

        // I have reached the maximum number of registrations. We cannot generate another id
        if ($iNew >= self::MAX_OFFERS) {
            throw new \Exception('Reached the maximum number of registrations on the agency ' . $sIdAgentie . '!');
        }
        // We reserve the offer id for x minutes
        if (!empty($sIdTemp)) {
            $this->setInRecentCache($sIdTemp);
        }

        $oOferta->sId = $this->generateIdString($iNew, $sIdAgentie, $sMarkerOnline, $sCateg);
        $oOferta->sIdUnic = $this->checkDbForUniqueId($oOferta, $sCateg);;


    }

    /**
     * @param int $iNumber
     * @param string $sIdAgentie
     * @param string $sMarkerOnline
     * @param string $sCateg
     * @param int $iMinLength
     * @return string
     */
    private function generateIdString(
        int    $iNumber,
        string $sIdAgentie,
        string $sMarkerOnline,
        string $sCateg,
        int    $iMinLength = 3
    ): string
    {
        $sFinal = $this->to32Char($iNumber);
        for ($iIndex = strlen($sFinal); $iIndex < $iMinLength; $iIndex++) {
            $sFinal = '0' . $sFinal;
        }
        return $sIdAgentie . $sMarkerOnline . $sCateg . $sFinal;
    }

    /**
     * @param int $n
     * @return string
     */
    private function to32Char(int $n = 0): string
    {
        $s = '';
        while ($n != 0) {
            $n1 = $n & 31;
            //echo $n1."<br>";
            if ($n1 < 10) {
                $ch = $n1;
            } else {
                $ch = chr(ord("A") + $n1 - 10);
            }
            //echo $ch."<br>";
            $s = $ch . $s;
            $n = $n >> 5;
        }
        return $s;
    }

    /**
     * @param string $sChar
     * @return int
     */
    protected function Val32Char(string $sChar): int
    {
        if ($sChar < 'A') {
            return ord($sChar) - ord('0');
        } else {
            return ord($sChar) - ord('A') + 10;
        }
    }


    /**
     * @param OfferInterface $oOferta
     * @return bool
     */
    protected function isPrivateSellerOffer(OfferInterface $oOferta): bool
    {
        return property_exists($oOferta, 'sId') && substr($oOferta->sId, 0, 2) == 'XV';
    }

    protected function attachPrivateSellerUniqueOfferIdNum(OfferInterface &$oOferta): void
    {
        // if we are the "private seller" offer, we only calculate the unique ID and exit quickly
        $iOfferTiming = strtotime($oOferta->tDataAdaugare . ' GMT') - strtotime('1999-01-01 00:00:00 GMT');

        $sIdUnic = substr($oOferta->sId, 1, 5) .
            base_convert(
                str_replace('.000000', '', sprintf('%f', $iOfferTiming)),
                10,
                32
            );
        $oOferta->sIdUnic = strtoupper($sIdUnic);
    }


    /**
     * @param $sIdNum
     * @return bool
     */
    private function isFoundInRecentCache($sIdNum): bool
    {
        return $this->oCacheIdOferta->read('ADD_' . $sIdNum) == 1;
    }

    /**
     * @param string $sIdNum
     * @return void
     */
    private function setInRecentCache(string $sIdNum): void
    {
        $this->oCacheIdOferta->write('ADD_' . $sIdNum, 1, $this->iRecentCacheTtl);
    }

    /**
     * @param OfferInterface $oOferta
     * @return array
     */
    private function getOfferCategoryAndTable(OfferInterface $oOferta): array
    {
        
        $aWildC = array_values($this->aWildcards);
        $sIdAgentie = strtoupper(array_shift($aWildC));

        //TODO adapt the class naming and db+table mapping
        $sTipOferta = strtolower(get_class($oOferta));
        $sTipOferta = str_replace('OfferInterface_', '', $sTipOferta);
        $sTipOferta = str_replace('_international', '', $sTipOferta);
        $sTipOferta = str_replace('_full', '', $sTipOferta);
        switch ($sTipOferta) {
            case 'apartament':
                $sCateg = '0';
                $sTable = 'apartamente';
                break;
            case 'casavila':
                $sCateg = '1';
                $sTable = 'casevile';
                break;
            case 'spatiu':
                $sCateg = '4';
                $sTable = 'comercial';
                break;
            case 'teren':
                $sCateg = '3';
                $sTable = 'terenuri';
                break;
            default:
                break;
        }
        if (empty($sTable)) {
            throw new \Exception('Categoria nu este valida!');
        }
        return [$sIdAgentie, $sCateg, $sTable];
    }

    /**
     * @param $oOferta
     * @param $sCateg
     * @param $sTable
     * @param $sIdAgentie
     * @param $sMarkerOnline
     * @return int
     */
    private function getNextNewId(OfferInterface $oOferta, $sCateg, $sTable, $sIdAgentie, $sMarkerOnline): int
    {
        if ($this->isInternationalOffer($oOferta)) {
            $this->initConfig((int)$sCateg + 1, ['src' => RMFW_DATASOURCE_ONLINE]);
        }

        $sSql = "SELECT `" . $sTable . "`.`id_num_oferta`,`" . $sTable . "`.`id_str_oferta`, SUBSTR(id_str_oferta, 7, 3) AS maxid FROM `firma_" .
            $sIdAgentie . "`.`" . $sTable . "` WHERE  ( ( ( `id_str_oferta` LIKE '" . $sIdAgentie . $sMarkerOnline . "%' ) AND ( `rank` > '-9999' ) ) )  ORDER BY maxid DESC LIMIT 1 ;";

        $oRs = $this->oDl->oConn->Execute($sSql);
        $sIdFirma = '';
        if (!$oRs) {
            throw new \Exception('Eroare obtinere oferte ' . __FUNCTION__ . '::' . __CLASS__ . '::' . $sSql);
        } else {
            while (!$oRs->EOF) {
                $sIdFirma = $oRs->fields['id_str_oferta'];
                $oRs->MoveNext();
            }
        }

        $iIdMaxFirma = $iIdMaxInt = $iIdMaxJunk = -1;
        if (!empty($sIdFirma)) {
            $iIdMaxFirma = ($this->Val32Char($sIdFirma[6])) * 32 * 32 +
                ($this->Val32Char($sIdFirma[7])) * 32 +
                $this->Val32Char($sIdFirma[8]);
        }


        if (in_array((int)$sCateg + 1, [1, 2])) {
            //internationl
            $this->initConfig((int)$sCateg + 1, ['src' => RMFW_DATASOURCE_INTERNATIONAL]);

            $sSql = "SELECT `" . $sTable . "_international`.`id_num_oferta`,`" . $sTable . "_international`.`id_str_oferta`, SUBSTR(id_str_oferta, 7, 3) AS maxid FROM `imobiliare_ro_public`.`" . $sTable . "_international` WHERE  ( ( ( `id_str_oferta` LIKE '" . $sIdAgentie . $sMarkerOnline . "%' ) AND ( `rank` > '-9999' ) ) )  ORDER BY maxid DESC LIMIT 1 ;";

            $oRs = $this->oDl->oConn->Execute($sSql);
            $sIdStr = '';
            if (!$oRs) {
                throw new \Exception('Eroare obtinere oferte ' . __FUNCTION__ . '::' . __CLASS__ . '::' . $sSql);
            } else {
                while (!$oRs->EOF) {
                    $sIdStr = $oRs->fields['id_str_oferta'];
                    $oRs->MoveNext();
                }
            }

            if (!empty($sIdStr)) {
                $iIdMaxInt = ($this->Val32Char($sIdStr[6])) * 32 * 32 +
                    ($this->Val32Char($sIdStr[7])) * 32 +
                    $this->Val32Char($sIdStr[8]);
            }
        }

        $this->initConfig((int)$sCateg + 1, ['src' => RMFW_DATASOURCE_JUNKYARD]);
        $sSql = "SELECT `oferte_sterse_" . $sTable . "`.`id_num_oferta`,`oferte_sterse_" . $sTable . "`.`id_str_oferta`, SUBSTR(id_str_oferta, 7, 3) AS maxid FROM `istoric_oferte`.`oferte_sterse_" .
            $sTable . "` WHERE  ( ( ( `id_agentie` = '" . $sIdAgentie . "' AND `id_str_oferta` LIKE '" . $sIdAgentie . $sMarkerOnline . "%' )) )  ORDER BY maxid DESC LIMIT 1 ;";

        $oRs = $this->oDl->oConn->Execute($sSql);
        $sIdStr = '';
        if (!$oRs) {
            throw new \Exception('Eroare obtinere oferte ' . __FUNCTION__ . '::' . __CLASS__ . '::' . $sSql);
        } else {
            while (!$oRs->EOF) {
                $sIdStr = $oRs->fields['id_str_oferta'];
                $oRs->MoveNext();
            }
        }

        if (!empty($sIdStr)) {
            $iIdMaxJunk = ($this->Val32Char($sIdStr[6])) * 32 * 32 +
                ($this->Val32Char($sIdStr[7])) * 32 +
                $this->Val32Char($sIdStr[8]);
        }

        return (int)max($iIdMaxFirma, $iIdMaxJunk, $iIdMaxInt) + 1;
    }

    /**
     * @param OfferInterface $oOferta
     * @return bool
     */
    private function isInternationalOffer(OfferInterface $oOferta): bool
    {
        if($this->bInternational){
            return $this->bInternational;
        }
        return (bool)substr_count(strtolower(get_class($oOferta)), '_international');
    }

    /**
     * @param int $iNew
     * @param $sIdAgentie
     * @param $sMarkerOnline
     * @param $sCateg
     * @return array
     */
    private function checkForParallelSaveRequests(int $iNew, $sIdAgentie, $sMarkerOnline, $sCateg): array
    {
        //we are checking that there is not already another offer that has asked for it
        $bContinue = true;
        while ($bContinue) {
            $sIdTemp = $this->generateIdString($iNew, $sIdAgentie, $sMarkerOnline, $sCateg);
            $bFoundInRecentCache = $this->isFoundInRecentCache($sIdTemp);
            if ($bFoundInRecentCache) {
                $iNew++;
            } else {
                $bContinue = false;
            }
        }
        return [$sIdTemp, $iNew];
    }

    /**
     * @param int $iFrom
     * @param int $iTo
     * @param array $aTocheckIn
     * @param string $sIdAgentie
     * @param string $sMarkerOnline
     * @param string $sCateg
     * @param $sIdTemp
     * @param $iNew
     */
    private function freeIdLookup(
        int    $iFrom,
        array  $aTocheckIn,
        string $sIdAgentie,
        string $sMarkerOnline,
        string $sCateg,
        &$sIdTemp,
        &$iNew
    )
    {
        for ($i = $iFrom; $i <= self::MAX_OFFERS-1; $i++) {
            $bEmpty = true;
            foreach ($aTocheckIn as $aDbData) {
                if (!empty($aDbData[$i])) {
                    $bEmpty = false;
                    break;
                }
            }

            if ($bEmpty) {
                $sIdTemp = $this->generateIdString($i, $sIdAgentie, $sMarkerOnline, $sCateg);
                $bFoundInRecentCache = $this->isFoundInRecentCache($sIdTemp);
                if (empty($bFoundInRecentCache)) {
                    $iNew = $i;
                    break;
                }
            }
        }
    }

    /**
     * @param $sCateg
     * @param $iNew
     * @param $sIdAgentie
     * @param $sTable
     * @param $sMarkerOnline
     * @return array
     * @throws \Exception
     */
    private function getDbOffersAndNextId($sCateg, $iNew, $sIdAgentie, $sTable, $sMarkerOnline): array
    {
        //TODO adapt the code:
        $aFirmOffers = $aHistoryOffers = $aInternationalOffers = [];
        $this->initConfig((int)$sCateg + 1, ['src' => RMFW_DATASOURCE_ONLINE]);
        if ($iNew >= self::MAX_OFFERS) {
            // I have reached the maximum number of registrants. we are looking for a free ID among them, with verification in the junkyard
            $sSql = "SELECT id_str_oferta, tabela FROM (" .
                "SELECT id_str_oferta, 'firma' AS tabela FROM `firma_" . $sIdAgentie . "`.`" . $sTable . "` UNION " .
                "SELECT DISTINCT(id_str_oferta), 'istoric' AS tabela FROM `istoric_oferte`.`oferte_sterse_" . $sTable . "` WHERE `id_agentie` = '" . $sIdAgentie . "'" .
                ") t ORDER BY id_str_oferta ASC;";
            $oRs = $this->oDl->oConn->Execute($sSql);
            if (!$oRs) {
                throw new \Exception('Eroare obtinere oferte 2 ' . __FUNCTION__ . '::' . __CLASS__ . '::' . $sSql);
            } else {
                $iMaxDB = 0;
                while (!$oRs->EOF) {
                    $sIdAnalizat = $oRs->fields['id_str_oferta'];
                    if (substr($sIdAnalizat, 4, 1) != $sMarkerOnline) {
                        $oRs->MoveNext();
                        continue;
                    }
                    $iIdBase10 = ($this->Val32Char($sIdAnalizat[6])) * 32 * 32 +
                        ($this->Val32Char($sIdAnalizat[7])) * 32 +
                        $this->Val32Char($sIdAnalizat[8]);
                    if ($oRs->fields['tabela'] == 'firma') {
                        $aFirmOffers[$iIdBase10] = $sIdAnalizat;
                    } else {
                        $aHistoryOffers[$iIdBase10] = $sIdAnalizat;
                    }
                    $iMaxDB = $iIdBase10;
                    $oRs->MoveNext();
                }
            }

            if (in_array((int)$sCateg + 1, [1, 2])) {
                $this->initConfig((int)$sCateg + 1, ['src' => RMFW_DATASOURCE_INTERNATIONAL]);
                $sSql = "SELECT id_str_oferta FROM `imobiliare_ro_public`.`" . $sTable . "_international` WHERE `id_agentie` = '" . $sIdAgentie . "' ORDER BY id_str_oferta ASC;";
                $oRs = $this->oDl->oConn->Execute($sSql);
                if (!$oRs) {
                    throw new \Exception('Error getting offers 2 ' . __FUNCTION__ . '::' . __CLASS__ . '::' . $sSql);
                } else {
                    while (!$oRs->EOF) {
                        $sIdAnalizat = $oRs->fields['id_str_oferta'];
                        if (substr($sIdAnalizat, 4, 1) != $sMarkerOnline) {
                            $oRs->MoveNext();
                            continue;
                        }

                        $iIdBase10 = ($this->Val32Char($sIdAnalizat[6])) * 32 * 32 +
                            ($this->Val32Char($sIdAnalizat[7])) * 32 +
                            $this->Val32Char($sIdAnalizat[8]);

                        $aInternationalOffers[$iIdBase10] = $sIdAnalizat;

                        $iMaxDB += $iIdBase10;
                        $oRs->MoveNext();
                    }
                }

                $this->initConfig((int)$sCateg + 1, ['src' => RMFW_DATASOURCE_ONLINE]);
            }

            $iNew = $iMaxDB + 1;
        }
        return array($aInternationalOffers, $aHistoryOffers, $aFirmOffers, $iNew);
    }

    /**
     * @param OfferInterface $oOferta
     * @param string $sCateg
     * @return string
     * @throws \Exception
     */
    private function checkDbForUniqueId(OfferInterface $oOferta, $sCateg): string
    {
        $bAllOkay = false;
        $iExtraMarker = 0;
        $iOfferTiming = strtotime($oOferta->tDataAdaugare . ' GMT') - strtotime('1999-01-01 00:00:00 GMT');
        do {
            $iOfferTiming += $iExtraMarker;

            $sIdUnic = substr($oOferta->sId, 1, 5) .
                base_convert(
                    str_replace('.000000', '', sprintf('%f', $iOfferTiming)),
                    10,
                    32
                );
            $sIdUnic = strtoupper($sIdUnic);

            // -- VERIFICARE UNICITATE IN DB
            $oOrg = $this->construiesteOrganizator();
            $oOrg->iLimit = RMFW_LIMIT_NONE;
            $oOrg->aAllowed = array('sIdUnic', 'sId');
            $oOrg->oFiltru->adaugaCriteriu('sIdUnic', $sIdUnic);
            $oRez = $this->obtineLista($oOrg);

            // -- VERIFICARE UNICITATE IN DB INTERNATIONAL
            if (in_array((int)$sCateg + 1, [1, 2])) {
                $this->initConfig((int)$sCateg + 1, ['src' => RMFW_DATASOURCE_INTERNATIONAL]);
                $oOrg = $this->construiesteOrganizator();
                $oOrg->iLimit = RMFW_LIMIT_NONE;
                $oOrg->aAllowed = array('sIdUnic', 'sId');
                $oOrg->oFiltru->adaugaCriteriu('sIdUnic', $sIdUnic);
                $oRezInternational = $this->obtineLista($oOrg);
            }

            $this->initConfig((int)$sCateg + 1, ['src' => RMFW_DATASOURCE_ONLINE]);
            if (count($oRez->aItems) == 0 &&
                (!isset($oRezInternational) || count($oRezInternational->aItems) == 0)
            ) {
                $bAllOkay = true;
            } else {
                $iExtraMarker++;
            }
        } while (!$bAllOkay);
        return $sIdUnic;
    }

}