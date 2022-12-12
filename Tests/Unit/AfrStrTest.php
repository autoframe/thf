<?php

//https://www.youtube.com/watch?v=9-X_b_fxmRM&ab_channel=ProgramWithGio
namespace Unit;

use Autoframe\Core\Http\Log\AfrHttpLog;
use Autoframe\Core\String\AfrStr;
use PHPUnit\Framework\TestCase;

class AfrStrTest extends TestCase
{
    use AfrHttpLog;
    public array $aTestStrings = [
        'lore' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        'htmlspecialchars' => '<>&\'"',
    ];


    function escapeDataProvider():array
    {
        $this->logHttpRequestedToFile('');
        echo __CLASS__ . '->' . __FUNCTION__ . PHP_EOL;
        $aStrings = $this->aTestStrings;
        return [
            [$aStrings,'html',''],
            [$aStrings,'html',AfrStr::getHtmlEncoding()],
            [$aStrings,'html','ISO-8859-1'],
            [$aStrings,'html','ISO-8859-5'],
        ];
    }
    /**
     * @test
     * @dataProvider escapeDataProvider
     */
    public function escape_simple(array $aStrings, string $esc_type, string $charset): void
    {
        foreach ($aStrings as $s) {
            AfrStr::setHtmlEncoding($charset);
            $desired = htmlspecialchars($s, AfrStr::$iFlagsHtmlentities, $charset?:AfrStr::getHtmlEncoding(), true);
            $this->assertSame($desired, AfrStr::escape($s, $esc_type, $charset));
        }
    }
}