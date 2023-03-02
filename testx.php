<?php


require_once(__DIR__ . '/vendor/autoload.php');
require_once(__DIR__ . '/src/_demo_config.php');

use Autoframe\Core\Html\AfrBlackBody;
use Autoframe\Core\Http\Cookie\AfrHttpCookieClass as AfrHttpCookieClassAlias;
use Autoframe\Core\Http\Download\AfrHttpDownload;
use Autoframe\Core\Http\Header\Formatters\AfrHttpHeaderFormatters;
use Autoframe\Core\Http\Log\AfrHttpLog;
use Autoframe\Core\Http\Request\AfrHttpRequest;
use Autoframe\Core\Http\CurlSimple\AfrHttpCurlSimple;
use Autoframe\Core\Entity\AfrEntityTest;
use Autoframe\Core\Entity\AfrEntityTestx;
use Autoframe\Core\Http\Cookie\Manager\AfrHttpCookieManagerManagerClass;

new AfrBlackBody();

/*
include ('src/FileSystem/BackupBPG/PhpBackupClass.php');
$oBkp = new PhpBackupClass(
    __DIR__.'/src/Arr',
    __DIR__.'/Bkp_test',
);
print_r($oBkp->makeBackup());
print_r($oBkp->aActionLog);
print_r($oBkp->aFiletype);
echo $oBkp->singleDayReport($oBkp->today);
*/


die;
$oCookieManager = AfrHttpCookieManagerManagerClass::getInstance();


$oEnt = new AfrEntityTest(['1'=>333333333,'aData'=>['pp'=>'fucking array data!'],'mMix'=>'American']);
$oEnt->iExtSet = '57.9';
$oEnt->iNbrNew = 99;
$oEnt->oGit=['Kx'=>'vy'];
$oEnt->tDate='';
$oEnt->mDateee=new stdClass();

echo (string)$oEnt;

//$oRefl = AfrEntityMap::get(get_class($oEnt));
echo "~~~\n";
//print_r($oRefl->getProperty('sTexxxt')->getDefaultValue() );

$oEntx = new AfrEntityTestx(['iId'=>7,'sPoveste'=>'Mexican']);
echo "\n~~~\n";


print_r($oEnt->getEntityPublicVars());
print_r($oEntx->getEntityPublicVars());
echo "\n---------------------\n\n\n";
$oEntx->copyPublicProperties($oEnt);
$oEntx->mFullMe = $oEnt;
echo "~~~~~~~~~~~\n\n";
print_r($oEntx->castForDatabase());

//new GitHubError500;
