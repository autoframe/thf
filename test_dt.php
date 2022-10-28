<?php




require_once(__DIR__ . '/vendor/autoload.php');
require_once(__DIR__ . '/src/_demo_config.php');


use Autoframe\Core\FileSystem\AfrFileSystemClass;
use Autoframe\Core\Html\AfrBlackBody;
use Autoframe\Core\Export\Php\AfrExportPhpArrClass;

echo (new AfrFileSystemClass())->base64EncodeFile(__DIR__.'/composer.json'); die;


$aSet = [
    'aa' => 'Â',
    'a' => 'r\\\'',
    'ca' => 'ă)',
    'A' => 0.,
    'n30' => '0.0',
    'n3' => 'Țg',
    'n31' => '!d',
    'cA' => -2.,
    85 => '&',
    'ș' => 'ț',
    'Ș' => '&',
    8 => '\\',
    71 => '%',
    71.2 => '-2',
    '70.2' => '-2.',
    ';' => -22,
    'â' => 'r',
    '#~' => 'R',
    "\tTAB" => 'a\"a',
    '#  ~' => 'Ă',
    7 => ".0",
];

$x = '';
var_dump($aSet); echo "\n\n";
echo serialize($aSet); echo "\n\n";

$oExport = new AfrExportPhpArrClass();
echo $oExport->exportPhpArrayAsString($aSet);
die;

new AfrBlackBody;

$oDT = new AfrFileSystemClass();


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
