<?php


require_once(__DIR__ . '/vendor/autoload.php');
require_once(__DIR__ . '/src/_demo_config.php');

use Autoframe\Core\Date\AfrDate;
use Autoframe\Core\Config\AfrConfigRegister;
use Autoframe\Core\Config\AfrConfig;
use Autoframe\Core\Config\AfrConfigFactory;
use Autoframe\Core\Date\Month\Language\AfrDateMonthLanguageFactory;
use Autoframe\Core\Date\Day\Language\AfrDateDayLanguageFactory;
use Autoframe\Core\Html\AfrBlackBody;
use Autoframe\Core\Http\Cookie\AfrHttpCookieClass as AfrHttpCookieClassAlias;
use Autoframe\Core\Http\Download\AfrHttpDownload;
use Autoframe\Core\Http\Header\Formatters\AfrHttpHeaderFormatters;
use Autoframe\Core\Http\Log\AfrHttpLog;
use Autoframe\Core\Http\Request\AfrHttpRequest;
use Autoframe\Core\Http\CurlSimple\AfrHttpCurlSimple;
use Autoframe\Core\Entity\AfrEntityTest;
use Autoframe\Core\Entity\AfrEntityTestx;
use Autoframe\Core\Http\Cookie\Manager\AfrHttpCookieManagerClass;

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


$oDay = new AfrDateDayLanguageFactory();
//print_r($oDay->getDayNames());
$oMonth = new AfrDateMonthLanguageFactory();
//print_r($oMonth->getMonthNames());
AfrDate::class;
$oCfg = new AfrConfig('Autoframe\Core\Date\AfrDate');
$oCfg->
    assignConstructorArgs(['constructor arg1'])->
    assignPreventExistenceErrors(true)->
    assignData(['xx','data'])->
    assignProperties(['prop1'=>3])->
    assignMethod('test',['arg1 inline echo'])->
    assignStaticProperties(['staticPropx'=>'valY'])->
    assignConstants(['GCCCCC'=>3])->
    assignStaticMethod('testStatic',['st testStatic'])->defineConstants();
//print_r($oCfg);
AfrConfigRegister::getInstance()->registerConfig($oCfg);
$oNew = AfrConfigFactory::makeInstanceFromNsClass('Autoframe\Core\Date\AfrDate',false);
$oCfg2 = new AfrConfig('Autoframe\Core\Date\AfrDate');
$oCfg2->assignConstants(['GCCCCCX'=>998])->defineConstants();
print_r(AfrConfigRegister::getInstance()->getDataConfig('Autoframe\Core\Date\AfrDate'));
//print_r($oCfg->getConstants());
echo GCCCCCX;
print_r(AfrDate::$staticPropx);
AfrConfigRegister::getInstance()->getConfigByKey();
die();

$oCookieManager = AfrHttpCookieManagerClass::getInstance();
$oCookieManager->assumeAllHttpCookies();
$oCookieManager->bAutoDomainDotNotationForAllSubdomains = true;
$aCookies = $oCookieManager->getAllIndexes();
print_r($aCookies);
if(!empty($aCookies['sterse'])){
    $aCookies['sterse']->unset();
}
else{
    $oCookie = new \Autoframe\Core\Http\Cookie\AfrHttpCookie('sterse','gvchgvjvg',time()+4444);
    $oCookie->set();
    echo 'sss';
}


die;


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
