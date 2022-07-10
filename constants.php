<?php

define('DOCUMENT_ROOT', str_replace('\\', '/', !empty($_SERVER['DOCUMENT_ROOT']) ? $_SERVER['DOCUMENT_ROOT'] : __DIR__));
define('DIR', __DIR__ );
define('DS', DIRECTORY_SEPARATOR );

define('THF_CLASSES', DIR.DS.'thfClasses' ); 
define('THF_CLASSES_BLADE', DIR.DS.'thfClasses'.DS.'bladeone'.DS.'lib' ); 
define('APP_CLASSES', DIR.DS.'appClasses' ); //global classes

define('AUTOLOAD_CLASSES', serialize( array(THF_CLASSES,THF_CLASSES_BLADE,APP_CLASSES) ) );
define('AUTOLOAD_VENDOR_NS_PSR4', serialize( array(DIR.DS.'vendor') ) );


function prea($in){echo '<pre>'.@htmlentities(print_r($in,true), ENT_QUOTES | ENT_IGNORE,'UTF-8').'</pre>';}


$__modulesList=function(){
	if(!defined('MODULES')){
		$moduleDirs=array();
		define('MODULES_DIR', DIR.DS.'Modules');
		if(is_dir(MODULES_DIR)){
			$myDirectory = opendir(MODULES_DIR);	// open this directory 
			while($entryName = readdir($myDirectory)) {
				if(filetype(MODULES_DIR.DS.$entryName)=='dir' && $entryName!='.' && $entryName!='..'){
					$moduleDirs[]=$entryName;
					}
				}
			closedir($myDirectory);	// close directory
			ksort($moduleDirs);
			}
		
		$moduleDirsChecked=array();
		/////////////////////////////////////
		
		$autoload_modules=array();
		foreach( $moduleDirs as $im=>$moduleName){
			if(in_array(substr($moduleName,0,1),array('_','!','.'))){ continue; } //a module name must not start with any of _ ! .
			foreach(array('Controllers','Models','Views','Classes','Routes') as $partType){
				//echo MODULES_DIR.DS.$moduleName.DS.$partType."<br>\r\n";
				if(is_dir(MODULES_DIR.DS.$moduleName.DS.$partType)){
					$autoload_modules[]=MODULES_DIR.DS.$moduleName.DS.$partType;
					$moduleDirsChecked[$im]=$moduleName;//add only the modules with at least one of this subfolders
				}
			}
		}
		ksort($moduleDirsChecked);
		define('MODULES', serialize($moduleDirsChecked));
		define('AUTOLOAD_MODULES', serialize($autoload_modules) );
		//prea($autoload_modules);

		
	}
}; $__modulesList(); unset($__modulesList);







