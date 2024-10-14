<?php
declare(strict_types=1);

namespace Autoframe\Core\Http\Header;

use Autoframe\Core\Http\Header\Utils\AfrHttpHeaderUtils;
use Autoframe\Core\Http\Header\Status\AfrHttpHeaderStatus;


trait AfrHttpHeader
{
    use AfrHttpHeaderUtils;
    use AfrHttpHeaderStatus;

    /**
     * @param int $iCode
     * @param string $sLocation
     * @param bool $bStripGetParams
     * @param array $aBuildQuery
     * @param bool $bExit
     * @return void
     */
    public function redirect3xx(
        int    $iCode = 302,
        string $sLocation = '',
        bool   $bStripGetParams = false,
        array  $aBuildQuery = [],
        bool   $bExit = true
    ):void
    {
        $this->hRedirect3xx($iCode,$sLocation,$bStripGetParams,$aBuildQuery,$bExit);
    }

    /**
     * @param $mData
     * @param bool $bExit
     * @return void
     */
    public function json_encode_header($mData, bool $bExit = true)
    {
        header('Content-Type: application/json');
        echo json_encode($mData);
        if ($bExit) {
            exit;
        }
    }



}