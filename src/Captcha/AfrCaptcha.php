<?php


namespace Autoframe\Core\Captcha;

//use Autoframe\Core\Object\AfrObjectSingleton;
use Autoframe\Core\Object\AfrObjectSingletonTrait;
use Autoframe\Core\String\AfrStr;

abstract class AfrCaptcha //extends AfrObjectSingleton
{
    use AfrObjectSingletonTrait;

    protected array $aParams = [];


    function __construct(array $aParams = [])
    {
        //parent::__construct();
        $this->mergeParams($aParams);
    }

    public function getParams(): array
    {
        return $this->aParams;
    }

    public function setParams(array $aParams): object
    {
        $this->aParams = $aParams;
        return $this;
    }

    public function mergeParams(array $aParams): object
    {
        $this->aParams = AfrStr::array_merge_recursive_settings($this->aParams, $aParams);
        return $this;
    }

    abstract public function getHtmlCaptcha(): string;



    /**
     *
     * TODO JSLIB DE GEN OBIECTE!!!!!!!!!
     *
     */
    protected array $aHeadResources = [
        'js' =>[
            'dependencies' => [
                'jQuery' =>'*',
                //'libs' =>'^2.2',
            ],

            0 =>'/path/to/script.js'

        ],
        'css' =>[

            0 =>'/path/to/style.css'
        ],
    ];
    public function getHeadResources(): array
    {
        return $this->aHeadResources;

    }


}