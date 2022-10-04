<?php

require_once(__DIR__ . '/vendor/autoload.php');
require_once(__DIR__ . '/src/_demo_config.php');


use Autoframe\Core\FileSystem\AfrDirToolsCollection;
use Autoframe\Core\Html\AfrBlackBody;

new AfrBlackBody;

$oDT = new AfrDirToolsCollection();


echo $oDT->dirVersioningDirMtimeHash('x:/xampp/htdocs',false)."<br>\n";
//echo $oDT->dirVersioningDirMtimeHash('D:/xampp/htdocs');
echo $oDT->getDirMaxFileMtime('D:/xampp',0,1);

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


$aDirs = $oDT->dirPathCountChildrenDirs('D:/');
//$aDirs = $oDT->getAllSubDirs('D:/',1);

echo microtime(true)-$start."<br>\n";
echo '<pre>';
print_r($aDirs);
//die('k');


//new GitHubError500;
