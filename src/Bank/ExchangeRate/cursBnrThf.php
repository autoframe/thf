<?php
declare(strict_types=1);

namespace Autoframe\Core\Bank\ExchangeRate;


//     $curs=new cursBnrThf(array('EUR','USD'));	//$curs->test();


class cursBnrXML
{
    var $xmlDocument = "";
    var $date = "";
    var $currency = array();

    function __construct()
    {
        //$this->xmlDocument = file_get_contents('http://www.bnro.ro/nbrfxrates.xml');
        $this->xmlDocument = file_get_contents('http://www.bnr.ro/nbrfxrates.xml');
        $this->parseXMLDocument();
    }

    function parseXMLDocument()
    {
        $xml = new SimpleXMLElement($this->xmlDocument);

        $this->date = $xml->Header->PublishingDate;

        foreach ($xml->Body->Cube->Rate as $line) {
            $this->currency[] = array("name" => $line["currency"], "value" => $line, "multiplier" => $line["multiplier"]);
        }
    }

    function getExchangeRate($currency)
    {
        foreach ($this->currency as $line) {
            if ($line["name"] == $currency) {
                return $line["value"];
            }
        }
        return "Incorrect currency!";
    }
}

class cursBnrThf extends cursBnrXML
{
    var $monede = array('EUR', 'USD');

    function __construct($monede = array())
    {
        if (is_array($monede) && count($monede) > 0) {
            $this->monede = $monede;
        }
        $this->checkLatest();
    }

    function checkLatest()
    {
        $storedDate = get_sv_val('curs_bnr_date');
        if (!$storedDate || date('Y-m-d', strtotime($storedDate)) < date('Y-m-d', time() - 3600 * 14 - 60 * 10)) {
            //no date or date older than 1 day and 14:10 minutes
            $this->updateCurs();
        }
    }

    function updateCurs()
    {
        parent::__construct();
        set_sv_val('curs_bnr_date', date('Y-m-d'));
        set_sv_val('curs_bnr_last_date', $this->date);
        foreach ($this->monede as $moneda) {
            set_sv_val('curs_bnr_' . $moneda, $this->getExchangeRate($moneda));
        }
    }

    function test()
    {
        $this->updateCurs();
        print get_sv_val('curs_bnr_date');
        print '~';
        print get_sv_val('curs_bnr_last_date');
        print "<hr>";
        print "USD: " . get_sv_val('curs_bnr_USD');
        print "<hr>";
        print "EUR: " . get_sv_val('curs_bnr_EUR');
        print "<hr>";
    }
}
