<?php




require_once(__DIR__ . '/vendor/autoload.php');
require_once(__DIR__ . '/src/_demo_config.php');


echo dechex(0); die;

use Autoframe\Core\FileSystem\AfrCollectionInterfaceSystemCollectionClass;
use Autoframe\Core\Html\AfrBlackBody;
use Autoframe\Components\Arr\Export\AfrArrExportArrayAsStringClass;

//echo (new AfrFileSystemClass())->base64EncodeFile(__DIR__.'/composer.json'); die;

$oClass = serialize(new stdClass());
$aSet = [
    'aa' => 'Â',
    'a' => 'r\\\'',
    '\ca' => 'ă)',
    'A' => 0.,
    'n30' => '0.0',
    null => 'Țg',
    'subA' =>[
        'n31' => $oClass,
        'cA' => -2.,
        85 => null,
        'ș' => 'ț',
    ],
    'Ș' => '&',
    8 => '\\',
    71 => '%',
    ';' => -22.3,
    '#~' => 'R',
    "\tTAB" => 'a\\"a',
    '#  ~' => 'Ă',
    7 => ".0",
    '71.2'.PHP_EOL => '-2',
    'â' => PHP_EOL.'r',

];

$x = '';
var_dump($aSet); echo "\n\n";

$oExport = new AfrArrExportArrayAsStringClass();
$sOut = $oExport->exportPhpArrayAsString($aSet);

echo $sOut;

echo "\n\n";
echo serialize($aSet); echo "\n\n";
$aData = [];
eval($sOut);
echo serialize($aData); echo "\n\n";
die;

new AfrBlackBody;

$oDT = new AfrCollectionInterfaceSystemCollectionClass();


//echo $oDT->dirVersioningDirMtimeHash('x:/xampp/htdocs',false)."<br>\n";
//echo $oDT->dirVersioningDirMtimeHash('D:/xampp/htdocs');
//echo $oDT->getDirMaxFileMtime('D:/xampp',0,1);

$start = microtime(true);

/*
for($i=0; $i<100;$i++){
    $iTmax = $oDT->getDirPathMaxFilemtime('./',7);
}
echo date('Y-m-d H:i:s',$iTmax)."<br>\n";
echo $iTmax*$i;*/

$aSortingOptions = [
    'asort',
    'arsort',
    'krsort',
    'ksort',
    'natcasesort',
    'natsort',
    'rsort',
    'shuffle',
    'sort',
    'array_multisort',
    'uasort',
    'uksort',
    'usort'
];
$iSk = 0;
//$oDT->setAfrDirTraversingSortMethod('ksort',[  SORT_NATURAL  ],true);
//$oDT->setAfrDirTraversingSortMethod('natsort',[],true);
$oDT->setAfrDirTraversingSortMethod(false,SORT_DESC);
print_r($oDT->getDirFileList('D:/xampp')); die;

$aDirs = $oDT->dirPathCountChildrenDirs('D:/');
//$aDirs = $oDT->getAllSubDirs('D:/',1);

echo microtime(true)-$start."<br>\n";
echo '<pre>';
print_r($aDirs);
//die('k');


//new GitHubError500;
