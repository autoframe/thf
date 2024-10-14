<?php


require_once(__DIR__ . '/vendor/autoload.php');
require_once(__DIR__ . '/src/_demo_config.php');

use Autoframe\Core\Html\AfrBlackBody;
use Autoframe\Core\Http\Download\AfrHttpDownload;
use Autoframe\Core\Http\Header\Formatters\AfrHttpHeaderFormatters;
use Autoframe\Core\Http\Log\AfrHttpLog;
use Autoframe\Core\Http\Request\AfrHttpRequest;
use Autoframe\Core\Http\CurlSimple\AfrHttpCurlSimple;

new AfrBlackBody();

class runn{
    use AfrHttpLog;
    use AfrHttpDownload;
    use AfrHttpRequest;
    use AfrHttpHeaderFormatters;
    use AfrHttpCurlSimple;
    use AfrHttpLog;

    public function __construct(){
        $this->hXxxLog();
        if(empty($_GET['log'])){
            //$this->makeTestRequestTo('http://localhost:8074/afr/test_hx.php?log=1',['form'=>'go!','log'=>'log']);
            $this->makeTestRequestTo('http://localhost:808/afr/test_hx.php?log=1',['form'=>'go!','log'=>'log']);
        }
        else{
            setcookie('Xset21','Yes',time()+300,'/');
            setcookie('Yset'.rand(20,22),'Yes',time()+300,'/');
            setcookie('sterse','nnnoo',0,'/');
            setcookie('sterse_fara_path','');
            setcookie('defaultP','',0);
            print_r($this->logHttpRequestedToFile());
        }
        die;
        echo PHP_EOL;
        var_dump($this->formatCookieLineIntoAssociativeArray('AFRSSIDY=-j1JO%2CIXnCfc8hzFoiENvoZJvI; AjAxSeRvicEsC=%7B%22time%22%3A%7B%22client%22%3A1657365227%2C%22server%22%3A1657365227%2C%22dif%22%3A0%2C%22boundry_seconds%22%3A28%2C%22use_cookie%22%3Atrue%7D%2C%22login%22%3A%5B%7B%22callback%22%3A%22showMask%22%2C%22data%22%3A%22%22%7D%5D%2C%22reminder%22%3A0%2C%22check_alerts%22%3A28%2C%22pinch%22%3A%7B%22newMsg%22%3A%22x%22%7D%2C%22conversation%22%3A12955%2C%22ie_requests%22%3A0%2C%22te_requests%22%3A0%2C%22it_equipements%22%3A%22%20%28%21%21%21%29%22%2C%22financial_forcast_update%22%3A0%7D; cX2=yes! '));die;
        var_dump($this->formatSplitHeaderLineInKeyVal('Location: https://fff.com')); die;


        $mData = 'ddd=xx; sss=a+a;';
        $mData = ['ddd=xx;','sss=aa'];
        $mData = ['ddd'=>'xx','sss'=>'a a'];
        $mData = 'ddd=';
        die;

    }
}
new runn();
//new GitHubError500;
