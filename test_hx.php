<?php


require_once(__DIR__ . '/vendor/autoload.php');
require_once(__DIR__ . '/src/_demo_config.php');

use Autoframe\Core\Html\AfrBlackBody;
use Autoframe\Core\Http\Download\AfrHttpDownload;
use Autoframe\Core\Http\Log\AfrHttpLog;
use Autoframe\Core\Http\Request\AfrHttpRequest;

new AfrBlackBody();



class runn{
    use AfrHttpLog;
    use AfrHttpDownload;
    use AfrHttpRequest;
    public function __construct(){
        echo $this->isCli()?'CLI':'URL'; die;
        //$this->headerDownloadFile();
        print_r($this->logHttpRequested());
    }
}
new runn();
//new GitHubError500;
