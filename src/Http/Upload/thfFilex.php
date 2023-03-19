<?php
//////FUNCTII PT VERIFICAT FILELOR DE PE SERVER//////////////////////////////////////////////////
/*
http://php.net/manual/ro/language.constants.predefined.php
A few "magical" PHP constants
__LINE__	The current line number of the file.
__FILE__	The full path and filename of the file with symlinks resolved. If used inside an include, the name of the included file is returned.
__DIR__	The directory of the file. If used inside an include, the directory of the included file is returned. This is equivalent to dirname(__FILE__). This directory name does not have a trailing slash unless it is the root directory.
__FUNCTION__	The function name.
__CLASS__	The class name. The class name includes the namespace it was declared in (e.g. Foo\Bar). Note that as of PHP 5.4 __CLASS__ works also in traits. When used in a trait method, __CLASS__ is the name of the class the trait is used in.
__TRAIT__	The trait name. The trait name includes the namespace it was declared in (e.g. Foo\Bar).
__METHOD__	The class method name.
__NAMESPACE__	The name of the current namespace.
echo  dirname ( realpath ( __FILE__ ) ).DIRECTORY_SEPARATOR ; //directorul curent de unde este inclusa fipa php care ruleaza acum
*/


/*
ThfUpload::$alowed='jpg|jpeg|doc|docx|pdf|xls|xlsx|odt|png|gif';
ThfUpload::$overwritePolicy='r'; //o=overwrite
//ThfUpload::$newFilenameFormatPolicy = '*f*';
//

//ThfUpload::$FILES=array(); ThfUpload::liniarize_class_files(array('only_one'=>$_FILES['only_one']));
ThfUpload::upload_files('new_name_*i*',THF_UPLOADS);
//prea(ThfUpload::$FILES);
//prea(ThfUpload::$messages);
//ThfUpload::make_unique_filename(THF_UPLOAD.'test.pdf');
*/

class ThfUpload{
	public static $FILES=array();
	public static $messages=array('error'=>array(),'info'=>array(),'success'=>array(),);
	public static $permDir=DIR_PERMS;
	//public static $permDir=0775;
	public static $permFile=FILE_PERMS;
	//public static $permFile=0664;
	public static $renamePolicy='_i'; //	(i): increment(i); 		_i: name_increment_i;	md5name: md5(filename).ext;
	public static $alowed='jpg|jpeg|doc|docx|pdf|xls|xlsx|odt|png|gif';
	public static $overwritePolicy='r'; //		r = rename new file with $renamePolicy; 		R = rename old file with $renamePolicy; 		
										//		o = overwrite without error; 
										//		n = skip with error;		N = skip without error;	
	public static $newFilenameFormatPolicy = '*f*';
		//	eg:			pic_*i*			document_name_*isodt**up*		*f*
		//	*f*			sanitized posted filename
		//	*ff*		original not sanitized posted filename
		//	*i*			incremental started from 0; It will fill search for blank number slots and ignore the $overwritePolicy
		//	*i=0*		incremental started from 0;	It will follow the $overwritePolicy
		//	*i=22*		incremental started from 22
		//	*up*		strtoupper all name
		//	*low*		strtolowwer all name
		//	*dt*		date('Y-m-d_H-i-s')
		//	*t*			date('H-i-s')
		//	*d*			date('Y-m-d')
	
	public static function upload_files($new_filename_format='',$upload_dir='',$alowed='',$overwritePolicy='',$partial_files_arr=array()){
		self::check_files_integrity();
		if(count(self::$FILES)<1){ self::liniarize_class_files($partial_files_arr); }		 //init if necesarry with some files only; use when multiple upload paths
		if(count(self::$FILES)<1){ self::$messages['info'][]='No file was posted to the server.'; return false;}
		
		if(!$overwritePolicy){$overwritePolicy=self::$overwritePolicy;}
		if(!$new_filename_format){$new_filename_format=self::$newFilenameFormatPolicy;}
		if(!$alowed){$alowed=self::$alowed;}
		if(!is_array($alowed)){$alowed=explode('|',$alowed);}
		
		if(!$upload_dir){$upload_dir=THF_UPLOAD.'ThfUpload/';}
		if(!is_dir($upload_dir) ){mkdir($upload_dir, self::$permDir,true); chmod($upload_dir,self::$permDir);}
		if(substr($upload_dir,-1,1)!='/'){$upload_dir.='/';}	
		
		$i=0;
		if(substr_count($new_filename_format,'*i=')){
			$etmp=extract_between($new_filename_format,'*i=','*');//[0];
			$i = $i_start = floor($etmp[0]);
		}
	
		foreach(self::$FILES as $input_name=>$report){
			$fileNameExt = $report['name']; //Booking Pensiunea Rubin.pdf
			$fileName = $report['filename']; //Booking Pensiunea Rubin
			$fileExt = $report['ext']; //pdf
			$fileType = $report['type']; //application/pdf
			$fileError = $report['error']; //int 0
			$fileSize = $report['size']; //int 60454
			//$fileContent = file_get_contents($report['tmp_name']);
			$msg=''; $success=false; $current_file_path_alt='';

			$policy=$new_filename_format;
			if(substr_count($policy,'*f*')){ $policy=str_replace('*f*',s($fileName),$policy);}//insert sanitized filename
			if(substr_count($policy,'*ff*')){ $policy=str_replace('*ff*',$fileName,$policy);	}//insert NOT sanitized filename
			if(substr_count($policy,'*dt*')){ $policy=str_replace('*dt*',date('Y-m-d_H-i-s'),$policy);	}
			if(substr_count($policy,'*t*')){ $policy=str_replace('*t*',date('H-i-s'),$policy);	}
			if(substr_count($policy,'*d*')){ $policy=str_replace('*d*',date('Y-m-d'),$policy);	}
			if(substr_count($policy,'*low*')){ $policy=strtolower(str_replace('*low*','',$policy));	}
			if(substr_count($policy,'*up*')){
				$policy=strtoupper(str_replace('*up*','',$policy));
				$policy=(str_replace('*I','*i',$policy));//do not screw up the number policy
			}
			
			if(substr_count($policy,'*i*')){
				$test_filename=str_replace('*i*',$i,$policy). ".".$report['ext'];
				while (file_exists($upload_dir.$test_filename)) {
					$i++;
					$test_filename=str_replace('*i*',$i,$policy). ".".$report['ext'];
				}
			$policy=str_replace('*i*',$i,$policy);
			}
			elseif(substr_count($policy,'*i=')){ //incremental started from $i_start;	It will follow the $overwritePolicy
				$policy=str_replace('*i='.$i_start.'*',$i,$policy);
			}
			$i++;//inc counter for autorename
			
			$current_file_path = $upload_dir. $policy. ".".$report['ext'];
			
			if($report['error'] != UPLOAD_ERR_OK){//upload error
				$msg=self::fileUploadMessage($report['error']);
			}
			elseif(!in_array($report['ext'],$alowed)){ //extenfion not in this list
				$msg=self::fileUploadMessage(UPLOAD_ERR_EXTENSION);
			}
			elseif($report['size']==0){ //check size
				$msg = "Filesize is zero";     	} 
			elseif($report['size'] > self::upload_return_bytes(ini_get('upload_max_filesize'))){ //check size
				$msg = "File size exceeds upload_max_filesize limit";     	}
			elseif($report['size'] > self::upload_return_bytes(ini_get('post_max_size'))){ //check size
				$msg = "File size exceeds post_max_size limit";     	} 
			elseif(!is_uploaded_file($report['tmp_name'])){ // check if there is a file in the array
				$msg = 'No file uploaded into tmp directory';}
			else{ 
				//		!!!!!!!!!!!!!!!!!!!                     $overwritePolicy
				$overwritePolicy_satisfied=true;
				if(is_file($current_file_path)){
					$overwritePolicy_satisfied=false;
					//		r = rename new file & keep both; 		R = rename old file & keep both; 		
					//		o = overwrite without error; 
					//		n = skip with error;		N = skip without error;	
						if($overwritePolicy=='n'){	$overwritePolicy_satisfied=false;	$msg = 'Error: file exists on server';	}
					elseif($overwritePolicy=='N'){	$overwritePolicy_satisfied=true;	$msg = 'Skipped because file exists on server';	}
					elseif($overwritePolicy=='r'){
						$current_file_path=self::make_unique_filename($current_file_path); //rename the new file
						$tmp=pathinfo($current_file_path);
						$overwritePolicy_satisfied=true;	$msg = 'Uploaded and renamed to '.$tmp['basename']; $policy=$tmp['filename'];
					}
					elseif($overwritePolicy=='R'){
						$current_file_path_alt=self::make_unique_filename($current_file_path); //rename the new file
						$tmp=pathinfo($current_file_path_alt);
						$overwritePolicy_satisfied=rename($current_file_path,$current_file_path_alt);//$overwritePolicy_satisfied=true;
						$msg = 'Uploaded. Previews file version renamed to '.$tmp['basename'];
						if(!$overwritePolicy_satisfied){$msg = 'Upload failed because of rename error for '.$current_file_path;}
					}
					elseif($overwritePolicy=='o'){
						    @chmod($current_file_path,0755); //Change the file permissions if allowed
    						if(unlink($current_file_path)){	$overwritePolicy_satisfied=true; $msg = 'Uploaded and overwritten'; }
							else{$overwritePolicy_satisfied=false; $msg = 'File exists on server and is write prottected!';}
							}
					else{$overwritePolicy_satisfied=false;	$msg = 'Error: file exists on server generic error';	}
				}

				if($overwritePolicy_satisfied && move_uploaded_file($report['tmp_name'], $current_file_path )){
					$msg = $msg?$msg:'File is uploaded';
					$success=true;
					//self::$nume_fila_upl[]=$current_file_path;
					//self::$nume_orig_fila[]=$orig_file_name;
					}
				elseif(!$overwritePolicy_satisfied){}//handeled above
				else{ $msg = 'Uploading Failed'; }
				}
			self::$FILES[$input_name]['success']=$success;
			self::$FILES[$input_name]['message']=$report['name'].' » '.$msg;
			self::$FILES[$input_name]['current_file_path']=$current_file_path;
			self::$FILES[$input_name]['current_file_path_alt']=$current_file_path_alt;
			self::$FILES[$input_name]['upload_dir']=$upload_dir;
			self::$FILES[$input_name]['new_filename']=$policy;
			self::$FILES[$input_name]['new_filename_ext']=$policy.'.'.$report['ext'];
			self::$FILES[$input_name]['web_path']=substr($upload_dir,strlen(THF_ROOT)).$policy.'.'.$report['ext'];
			self::$messages[ ($success?'success':'error') ][]=self::$FILES[$input_name]['message'];
			ThfAjax::status($success,self::$FILES[$input_name]['message']); //populate global class with update report
			ThfAjax::$out['data']['upload'][]=array(
				'web_path'=>self::$FILES[$input_name]['web_path'],
				'name'=>self::$FILES[$input_name]['name'],
				'error'=>self::$FILES[$input_name]['error'],
				'new_name'=>self::$FILES[$input_name]['new_filename_ext'],
			);

		}//end of foreach
		return true;
	}
	
	public static function is_post(){ return (count($_POST)>0 || $_SERVER['REQUEST_METHOD']=='POST' ? true:false);	}
	public static function is_post_files_oversize_error(){
		return (count($_POST)<1 && count($_FILES)<1 && $_SERVER['REQUEST_METHOD']=='POST' && @floor($_SERVER['CONTENT_LENGTH'])>0 ? true:false);
	}
	public static function check_files_integrity($report=0){
		set_time_limit(600);
		if(self::is_post_files_oversize_error() || $report){
			ThfAjax::status($report,'POST request '.($report?'TEST:':'FAILED because of the max upload limits or one of the next settings:'));
			ThfAjax::status($report,'file_uploads='.(ini_get('file_uploads')?'Enabled':'Dissabled').";");
			ThfAjax::status($report,'max_file_uploads='.ini_get('max_file_uploads')."; (The maximum number of files allowed to be uploaded simultaneously)");
			ThfAjax::status($report,'upload_max_filesize='.ini_get('upload_max_filesize')."; ( The maximum size of an uploaded file)");
			ThfAjax::status($report,'post_max_size='.ini_get('post_max_size')."; (Sets max size of post data allowed. This setting also affects file upload. To upload large files, this value must be larger than upload_max_filesize. Generally speaking, memory_limit should be larger than post_max_size)" );
			ThfAjax::json();
		}
	}
/*$_SERVER['REQUEST_METHOD']=='POST'
[CONTENT_LENGTH] => 36759622
[CONTENT_TYPE] => multipart/form-data; boundary=---------------------------11956435811204*/
		
	public static function upload_one($input_name, $new_filename_format='contract_*d*',$upload_dir='',$alowed='',$overwritePolicy=''){
		self::check_files_integrity();
		self::$FILES=array();//reset
		if(isset($_FILES[$input_name])){
			//self::$newFilenameFormatPolicy = 'contract_'.s($_POST['client']).'_*d*';//'contract_'.s($_POST['client']).'_*f*';
			self::liniarize_class_files(array($input_name=>$_FILES[$input_name]));//load files array to process
			self::upload_files(  $new_filename_format,  $upload_dir,$alowed,$overwritePolicy);
			if(self::$FILES[$input_name]['success'] && self::$FILES[$input_name]['new_filename_ext']){
				$_POST[$input_name]=self::$FILES[$input_name]['new_filename_ext'];//get the filename for the query
			}
			else{ThfAjax::status(false); } //	ThfAjax::status($success,self::$FILES[$input_name]['message']); //populate global class with update report
		}
		return self::$FILES;
		//prea(self::$messages);
	}
	
	
	public static function make_unique_filename($full_path) {
		
		$tmp=pathinfo($full_path);
		$file_name = $tmp['basename'];
		$directory = $tmp['dirname'].'/';
		
		if(self::$renamePolicy=='md5name'){
			$file_name=md5($tmp['filename']).'.'.$tmp['extension'];
			while (file_exists($directory.$file_name)) {
				$file_name = md5($file_name).'.'.$tmp['extension'];
			}
		}
		if(self::$renamePolicy=='(i)'){
			$i = 2;
			while (file_exists($directory.$file_name)) {
				// Remove any numbers in brackets in the file name
				$file_name = preg_replace('/\(([0-9]*)\)$/', '', $tmp['filename']).'('.$i.').'.$tmp['extension'];
				$i++;		if($i>20000){$file_name.=time().rand(23,423542); break;}//failsafe
			}
		}
		if(self::$renamePolicy=='_i'){
			$i = 2;
			while (file_exists($directory.$file_name)) {
				// Remove any numbers in underline in the file name
				$file_name = preg_replace('/\_([0-9]*)\$/', '', $tmp['filename']).'_'.$i.'.'.$tmp['extension'];
				$i++;		if($i>20000){$file_name.=time().rand(23,423542); break;}//failsafe
			}
		}
		return $directory.$file_name;
	}
	public static function get_name($fname){
		$tmp= pathinfo($fname);
		if(isset($tmp['extension'])){$tmp['extension']=strtolower(trim($tmp['extension']));}
		if(isset($tmp['extension']) && $tmp['extension']==='jpeg'){$tmp['extension']='jpg';}
		return array('filename'=>@$tmp['filename'],'ext'=>@$tmp['extension']);
	}
	public static function liniarize_class_files($server_files=array()){
		if(count($server_files)<1){$server_files=$_FILES;}//inherit all global post
		if(count(self::$FILES)<1){}//first run or run after reset the self::$FILES
		else{return;}
		foreach($server_files as $input_name=>$report){
			if(is_array($report['name'])){//input was posted with "file[]"
				foreach($report as $prop=>$arr){
					foreach($arr as $i=>$val){
						self::$FILES[$input_name.'['.$i.']'][$prop]=$val;
						if($prop=='error'){
							self::$FILES[$input_name.'['.$i.']']['message']=self::fileUploadMessage($val);
						}
						elseif($prop=='name'){
							self::$FILES[$input_name.'['.$i.']']=(self::$FILES[$input_name.'['.$i.']'] + self::get_name($val));
						}
					}
				}
			}
			else{
				self::$FILES[$input_name]=$report;
				self::$FILES[$input_name]['message']=self::fileUploadMessage($report['error']);
				self::$FILES[$input_name]=(self::$FILES[$input_name] + self::get_name($report['name']));
			}
		}
	}
	
	public static function fileUploadMessage($status){
		$fileUploadErrors=array(
			UPLOAD_ERR_OK => 'Upload is successfull', //0
			UPLOAD_ERR_INI_SIZE => 'Error trying to upload a file that exceeds the allowed INI size.', //1
			UPLOAD_ERR_FORM_SIZE => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.', //2
			UPLOAD_ERR_PARTIAL => 'The uploaded file was only partially uploaded.', //3
			UPLOAD_ERR_NO_FILE=> 'No file was uploaded.',//4  this appears when no file is chosed, 
			UPLOAD_ERR_NO_TMP_DIR=> 'Error: server not configured to upload files.', //6
			UPLOAD_ERR_CANT_WRITE=> 'Error: possible failure when saving the file.', //7
			UPLOAD_ERR_EXTENSION=> 'Error: file upload not completed because of extension.', //8
			);
		return (isset($fileUploadErrors[$status])?$fileUploadErrors[$status]:'Error: file upload not completed.');
	}

	public static function upload_return_bytes($val){
		$val = trim($val); $last = strtolower($val[strlen($val)-1]); 
		switch($last) {  case 'g': $val *= 1024;   case 'm': $val *= 1024;   case 'k': $val *= 1024;	}
		return $val;}

	public static function get_upload_max_filesize(){
		return min(self::upload_return_bytes(ini_get('upload_max_filesize')),self::upload_return_bytes(ini_get('post_max_size')));}	
	
	public static function get_max_paralel_upload_files(){return floor(ini_get('max_file_uploads'));}

	public static function set_upload_mb_limit($mb=4096){
		$limit=$mb.'M';	if($mb>1024){$limit=floor($mb/1024).'G';} ini_set('post_max_size',$limit); ini_set('upload_max_filesize', $limit);
	}
	
}//end of class



/*
ThfAjax::status(bool,'message to show');
//ThfAjax::msg('info message'); //ThfAjax::$nl="\r\n";
//ThfAjax::redirect('/'); //ThfAjax::$callback='alert'; ThfAjax::$callback_params='MSG!'; 
ThfAjax::json();
//ThfAjax::prea(); 
*/
class ThfAjax{
	public static $out=array();
	public static $nl='<hr style="margin:5px;">'; //br |   \r\n
	public static function init(){
		self::$out=array(
			'redirect'=>NULL, //link for redirect after mesessage
			'status'=>-1, // true; false; info =-1; 
			'msg'=>NULL, //str | array
			'callback'=>NULL, //js function name
			'callback_params'=>NULL, 
			'click'=>false,//call 
			'show_time'=>3000,//call 
			'class'=>NULL,
			'data'=>array(),
		);
	}

	public static function redirect($link){self::$out['redirect']=$link;}
	public static function msg($msg){
		if(!$msg){return;}
		if(is_array(self::$out['msg'])){self::$out['msg'][]=$msg;}
		elseif(self::$out['msg']){self::$out['msg']=array(self::$out['msg'],$msg);}
		else{self::$out['msg']=array($msg);}
	}
	public static function status($status,$msg=''){
		if(!$status && !$msg){$msg='Generic state process ERROR!';}
		self::msg($msg);
		if(floor(self::$out['status'])==-1 || self::$out['status']){ self::$out['status']=$status;	}
	}
	
	public static function json(){self::process();	header('Content-Type:application/json');	die(json_encode(self::$out)); }
	public static function prea(){self::process();	prea(self::$out); die;	}
	public static function process(){
		if(is_array(self::$out['msg'])){
			self::$out['msg']=(count(self::$out['msg'])>1?implode(self::$nl,self::$out['msg']):self::$out['msg'][0]);
		}
		if(!self::$out['msg'] && !self::$out['status']){self::$out['msg']='Error!';}
		elseif(!self::$out['msg'] && floor(self::$out['status'])==-1){self::$out['msg']='Info!';}
		elseif(!self::$out['msg'] && self::$out['status'] && floor(self::$out['status'])==1){self::$out['msg']='Success!';}
	}
}//end of class
ThfAjax::init();

function num2alphaThf($n){    for($r = ""; $n >= 0; $n = intval($n / 26) - 1){ $r = chr($n%26 + 0x41) . $r;} return $r;}//helper

function export_to_excel($sheets_rows_cels=array(),$title='Data feed export',$firm='THF',$format='xls',$save_file_to_path=false,$setCellValueExplicit=true,$password=false, $die=true){
	//prea($sheets_rows_cels);
	if(!class_exists('PHPExcel')){
		$class_path = THF_FUNC.'helper/PHPExcel/Classes/PHPExcel.php';

		if(is_file($class_path) && is_readable($class_path)){		require_once ($class_path);		}
		else{echo '<h1>PHPExcel Class not found! Export failed!</h1>'; if($die){die();} return false;}
	}


	$objPHPExcel = new PHPExcel();
	$objPHPExcel->getProperties()->setCreator($firm)
								 ->setLastModifiedBy($firm)
								 ->setTitle($title)
								 ->setSubject($title)
								 ->setDescription($firm)
								 ->setKeywords($firm)
								 ->setCategory($firm);
	
	
	
	
	
	$s=0; 
	foreach($sheets_rows_cels as $sheet_name=>$rows){
		if($s>0){$objPHPExcel->createSheet();}
		$i=0;
		foreach($rows as $row){//$i=>
			$j=0;
			foreach($row as $cell){ // $j=>
				if($setCellValueExplicit){
					$objPHPExcel->setActiveSheetIndex($s)->setCellValueExplicit(num2alphaThf($j).($i+1), $cell,PHPExcel_Cell_DataType::TYPE_STRING);
				}
				else{
					$objPHPExcel->setActiveSheetIndex($s)->setCellValue(num2alphaThf($j).($i+1), $cell);	
				}
				$j++;
			}
			$i++;
		}
		if(strlen($sheet_name)>1){
			$objPHPExcel->getActiveSheet()->setTitle($sheet_name);
		}
		$s++;
	}
	$objPHPExcel->setActiveSheetIndex(0);
	
	
	if($password){
		//$format='xlsx'; //https://stackoverflow.com/questions/21639731/protect-the-excel-file-using-phpexcel
		if(is_string($password)){
			$objPHPExcel->getSecurity()->setLockWindows(true);
			$objPHPExcel->getSecurity()->setLockStructure(true);
			$objPHPExcel->getSecurity()->setWorkbookPassword($password);
		}
		elseif(is_array($password)){
			/*$objPHPExcel->getActiveSheet()->getProtection()->setSheet(true);
			$objPHPExcel->getActiveSheet()->getProtection()->setSort(true);
			$objPHPExcel->getActiveSheet()->getProtection()->setInsertRows(true);
			$objPHPExcel->getActiveSheet()->getProtection()->setFormatCells(true);
			$objPHPExcel->getActiveSheet()->getProtection()->setPassword('password');*/
			die('not implemented password for each sheet');
		}
	}

	
	
	
	
	if($save_file_to_path){
		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, ($format=='xls'?'Excel5':'Excel2007'));
		$objWriter->save( rtrim($save_file_to_path,'\/')  .'/'.  $title.  '.xls'. ($format=='xls'?'':'x') );
	}
	else{ //download

		// Redirect output to a client's web browser (Excel5)
		header(($format=='xls'?'Content-Type: application/vnd.ms-excel':"Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"));
		header('Content-Disposition: attachment;filename="'.$title.'.xls'.($format=='xls'?'':'x').'"');
		header('Cache-Control: max-age=0');
		// If you're serving to IE 9, then the following may be needed
		header('Cache-Control: max-age=1');

		// If you're serving to IE over SSL, then the following may be needed
		header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
		header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
		header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
		header ('Pragma: public'); // HTTP/1.0

		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, ($format=='xls'?'Excel5':'Excel2007'));
		$objWriter->save('php://output');
		
	}
	if($die){die($die);}
	return true;
}

function thf_ziper($source, $destination, $include_dir_name = false){ //Example:	thf_ziper('/path/to/maindirectory','/path/to/compressed.zip',true);
    $source=rtrim($source);// lose the last /
	if (!extension_loaded('zip') || !file_exists($source)) { return false; }
    if (file_exists($destination)) { unlink ($destination);}

    $zip = new ZipArchive();
    if (!$zip->open($destination, ZIPARCHIVE::CREATE)) { return false;   }
    $source = str_replace('\\', '/', realpath($source));
    if (is_dir($source) === true){
        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($source), RecursiveIteratorIterator::SELF_FIRST);
        if ($include_dir_name) {
            $arr = explode("/",$source);
            $maindir = $arr[count($arr)- 1];
            $source = "";
            for ($i=0; $i < count($arr) - 1; $i++) { $source .= '/' . $arr[$i];  }
            $source = substr($source, 1);
            $zip->addEmptyDir($maindir);
        	}
        foreach ($files as $file){
            $file = str_replace('\\', '/', $file);
            // Ignore "." and ".." folders
            if( in_array(substr($file, strrpos($file, '/')+1), array('.', '..')) ) continue;
            $file = realpath($file);
            if (is_dir($file) === true){ $zip->addEmptyDir(str_replace($source . '/', '', $file . '/')); }
            else if (is_file($file) === true){$zip->addFromString(str_replace($source . '/', '', $file), file_get_contents($file)); }
        	}
    	}
    else if (is_file($source) === true){ $zip->addFromString(basename($source), file_get_contents($source));}
    return $zip->close();
	}

function thf_unzipper($zip_file, $extractPath, $directory_collaps=false) {
	$extractPath=rtrim($extractPath,'/ ').'/';
	if(is_dir($extractPath) && is_readable($extractPath)){
		$zip = new ZipArchive;	$res = $zip->open($zip_file);
		if ($res === TRUE) {
			if($directory_collaps){
				for($i = 0; $i < $zip->numFiles; $i++) {
					$filename = $zip->getNameIndex($i);
					$fileinfo = pathinfo($filename);
					copy("zip://".$zip_file."#".$filename, $extractPath.$fileinfo['basename']);
					}
				}
			else{	$zip->extractTo($extractPath);	$zip->close();	}
			return TRUE;
			}
		}
	return FALSE;
	}
function unzip_old($zip_archive,$extract_to_dir='',$perms_folder=0755){
	if(is_file($zip_archive) && is_readable($zip_archive)){
		if(!function_exists(zip_open) || !function_exists(zip_read)){die('Zip plugin not installed on this server!');}
		$zip = zip_open($zip_archive) or die("Can't open Zip archive");
		if($extract_to_dir==''){$dir_name = explode('.',basename($zip_archive)); $extract_to_dir=str_replace(basename($zip_archive),'',$zip_archive).$dir_name.'/';  }
		if(!is_dir($extract_to_dir)){mkdir($extract_to_dir,$perms_folder,true);}
		if(substr($extract_to_dir,-1)!='/'){$extract_to_dir.='/';}
		if ($zip) {
			while ($zip_entry = zip_read($zip)) {
				$zip_entry_name = zip_entry_name($zip_entry);
				if(substr($zip_entry_name,-1)=='/' && !is_dir($extract_to_dir.$zip_entry_name)){
					mkdir($extract_to_dir.$zip_entry_name,$perms_folder,true);
					}
				else{				
					$fp = fopen($extract_to_dir . $zip_entry_name , "w");
					if (zip_entry_open($zip, $zip_entry, "r")) {
						$buf = zip_entry_read($zip_entry, zip_entry_filesize($zip_entry));
						fwrite($fp,$buf);
						zip_entry_close($zip_entry);
						fclose($fp);
						}
					}
				}
			zip_close($zip);
			return 1;
			}
		}
	return 0;
	}



function file_get_contents_utf8($fn) { $content = file_get_contents($fn);
	return mb_convert_encoding($content, 'UTF-8',  mb_detect_encoding($content, 'UTF-8, ISO-8859-1', true));	 }


function relative_path($x='s'){//s=server path(real) ... else orce alt path
    $root_distance = substr_count($_SERVER['REQUEST_URI'], "/") - 1;
    if($root_distance>0){ $path = str_repeat('../', $root_distance);}
	else {$path='./';}
	
	$root_distance2 = substr_count($_SERVER['PHP_SELF'], "/") - 1;
    if($root_distance2>0){ $path2 = str_repeat('../', $root_distance2);}
	else {$path2='./';}

	if($path!=$path2 && $x=='s'){return $path2;}
	else{return $path;}
		
	return $path;
}function rpath($x='s'){return relative_path($x);}



function f($filepath,$mod=-1){//mod=0 => webpath input, mod=1 =>server path input
	if($mod==-1){//autodetect
		$tmp=substr(strtolower($filepath),0,7);//https://
		if($tmp=='https:/' || $tmp=='http://'){	return $filepath.'?v='.date('Y-m-d_H-i-s');	}
		
		$mod=( substr($filepath,0,strlen(THF_PATH))==THF_PATH?1:0 );
		if(!$mod && substr($filepath,0,1)!='/' && is_file(realpath($filepath))){$filepath=realpath($filepath); $mod=1;}//cai relative serverside
		}
	if(!$mod){	$web_p=$filepath;
				$server_p=THF_PATH.substr($web_p,strlen(ROOT));	}
	else{		$server_p=$filepath;
				$web_p=ROOT.substr($server_p,strlen(THF_PATH));	}
	return $web_p.'?v='.date('Y-m-d_H-i-s',filemtime($server_p));	}


function force_file_get_contents($file_or_url,$force_verify=1,$max_retries=2){
	$max_retries--;	if($max_retries<0){return NULL;}	//echo $max_retries.' maxr<br />';
	$contents=@file_get_contents($file_or_url);
	if($force_verify){
		if(substr($file_or_url,0,4)=='http'){//validare header http
			$headers=@get_headers($file_or_url); $size=0;
			if(is_array($headers)){
				foreach($headers as $he){	$he=strtolower($he);
					if(substr_count($he,'content-length: ')==1){ $size=str_replace('content-length: ','',$he); }
					}
				}
			else return force_file_get_contents($file_or_url,$force_verify,$max_retries);
			}
		elseif(is_file($file_or_url)){	$size = filesize($file_or_url); }//filesize
		else return force_file_get_contents($file_or_url,$force_verify,$max_retries);
		if($size>0 && $size==strlen($contents)){return $contents;} //compare...
		}
	elseif($contents!=''){return $contents;}//no sizse check...
	else return force_file_get_contents($file_or_url,$force_verify,$max_retries);
	}

function file_size($filename='',$bytes=0) {
	if($filename){$bytes=filesize($filename);}
	$precision = 2; $units = array('B', 'KB', 'MB', 'GB', 'TB');
	$bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= pow(1024, $pow);  
    return round($bytes, $precision) . ' ' . $units[$pow]; } 

function file_save($file_path_and_name,$contents,$overwrite=0,$perms=0664,$perms_d=0775){	global $debug_thorr; 
	if($overwrite==1 && is_file($file_path_and_name)){unlink($file_path_and_name);}
	
	if(!is_dir(dirname($file_path_and_name))){mkdir(dirname($file_path_and_name), $perms_d, true);}
	if(is_file($file_path_and_name)){chmod($file_path_and_name, $perms);}
	$fh = fopen($file_path_and_name, ($overwrite==0? 'a':'w') );
	if(!$fh){	if($debug_thorr==1){ echo "<strong>File Save Error: $file_path_and_name </strong> ";} return 0; }
	fwrite($fh, $contents);	fclose($fh); chmod($file_path_and_name, $perms); return 1; }



function array_to_file($file_path_and_name,$array,$overwrite=1){ return file_save($file_path_and_name,serialize($array),$overwrite);}
function array_from_file($file_path_and_name){if(is_file($file_path_and_name)){return unserialize(file_get_contents($file_path_and_name));}else{return 0;}}

function get_perms($file){
	$perms=fileperms($file);
	if (($perms & 0xC000) == 0xC000) {$info = 's';}
	elseif (($perms & 0xA000) == 0xA000) {$info = 'l';}
	elseif (($perms & 0x8000) == 0x8000) {$info = '-';}
	elseif (($perms & 0x6000) == 0x6000) {$info = 'b';}
	elseif (($perms & 0x4000) == 0x4000) {$info = 'd';}
	elseif (($perms & 0x2000) == 0x2000) {$info = 'c';}
	elseif (($perms & 0x1000) == 0x1000) {$info = 'p';}
	else {$info = 'u';}
	// Owner
	$info .= (($perms & 0x0100) ? 'r' : '-');
	$info .= (($perms & 0x0080) ? 'w' : '-');
	$info .= (($perms & 0x0040) ? (($perms & 0x0800) ? 's' : 'x' ) :  (($perms & 0x0800) ? 'S' : '-'));
	// Group
	$info .= (($perms & 0x0020) ? 'r' : '-');
	$info .= (($perms & 0x0010) ? 'w' : '-');
	$info .= (($perms & 0x0008) ? (($perms & 0x0400) ? 's' : 'x' ) :  (($perms & 0x0400) ? 'S' : '-'));
	// World
	$info .= (($perms & 0x0004) ? 'r' : '-');
	$info .= (($perms & 0x0002) ? 'w' : '-');
	$info .= (($perms & 0x0001) ? (($perms & 0x0200) ? 't' : 'x' ) :  (($perms & 0x0200) ? 'T' : '-'));
	return $info;
	}

function downloadFile( $fullPath ){
	if( headers_sent() ) die('Headers Sent'); // Must be fresh start
	if(ini_get('zlib.output_compression'))  ini_set('zlib.output_compression', 'Off');  // Required for some browsers
	if( file_exists($fullPath) ){  // File Exists?
		$fsize = filesize($fullPath); // Parse Info / Get Extension
		$path_parts = pathinfo($fullPath);
		$ext = strtolower($path_parts["extension"]);
		switch ($ext) { // Determine Content Type
			case "pdf": $ctype="application/pdf"; break;
			case "exe": $ctype="application/octet-stream"; break;
			case "zip": $ctype="application/zip"; break;
			case "doc": $ctype="application/msword"; break;
			case "xls": $ctype="application/vnd.ms-excel"; break;
			case "ppt": $ctype="application/vnd.ms-powerpoint"; break;
			case "gif": $ctype="image/gif"; break;
			case "png": $ctype="image/png"; break;
			case "jpeg":
			case "jpg": $ctype="image/jpg"; break;
			default: $ctype="application/force-download";
			}
		header("Pragma: public"); // required
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header("Cache-Control: private",false); // required for certain browsers
		header("Content-Type: $ctype");
		header("Content-Disposition: attachment; filename=\"".basename($fullPath)."\";" );
		header("Content-Transfer-Encoding: binary");
		header("Content-Length: ".$fsize);
		ob_clean();
		flush();
		readfile( $fullPath );
		}
	else die('File Not Found');
	}//end of function



function s($filename,$file=true,$language=""){// function safe_file_name() filtreaza diacritice, chinezarii UDF8
	return URLify::filter ($filename, 100, $language, $file);
	$filename=strtolower($filename);
	$allowd="-.,"; //$allowd.='_@+';//??
	for($i=48;$i<58;$i++){$allowd.=chr($i);}//0-9
	//for($i=65;$i<91;$i++){$allowd.=chr($i);}//A-Z
	for($i=97;$i<123;$i++){$allowd.=chr($i);}//a-z
	$out='';	$fl=strlen($filename);	$al=strlen($allowd);
	for($i=0;$i<$fl;$i++){	for($j=0;$j<$al;$j++){	if($filename[$i]==$allowd[$j]){$out.=$allowd[$j];$j=$al+1;}	}
		if($j==$al){$out.='-';$j=0;} }	return str_replace(array('-----','----','---','--'),array('-','-','-','-'), $out);
}

function s2($filename){//function safe_file_name_orig()  UDF8 allowd
	$str='<>\/|:*?"';
	for($i=0;$i < strlen($str);$i++){$filename=str_replace($str[$i],'-',$filename);}
	return strtolower($filename);
}


// rename($file, $newfile);
// copy($file, $newfile);
// unlink('test.html'); //sterg file
// rmdir('examples'); //sterg director
// if (!is_dir('examples')) { mkdir('examples'); }
// is_file 




function deleteAll($directory, $empty = false) { //sterg fisierele care nu sunt goale :D
    if(trim($directory)=='.' || trim($directory)=='..' || trim($directory)==''){return false;}
	elseif(substr($directory,-1) == "/") {$directory = substr($directory,0,-1);	}
    if(!file_exists($directory) || !is_dir($directory)) {return false;}
	elseif(!is_readable($directory)){return false;} 
	else{
        $directoryHandle = opendir($directory);
        while ($contents = readdir($directoryHandle)){
            if($contents != '.' && $contents != '..'){
				$path = $directory . "/" . $contents;
                if(is_dir($path)) {deleteAll($path);}
				else {unlink($path);}
            	}
        	}
       closedir($directoryHandle);
       if($empty == false) {  if(!rmdir($directory)) {return false;}   }
       return true;    } }


function thorr_read_file($filename){ //echivalent file_get_contents(./zz.txt);
	$handle = fopen($filename, "r") or die('Invalid handle!');
	$contents = fread($handle, filesize($filename))or die('Invalid read!');
	fclose($handle); return $contents; }

	
function list_dir_th($dir='.'){
	$myDirectory = opendir($dir);	// open this directory 
	while($entryName = readdir($myDirectory)) {	$dirArray[] = $entryName;}	// get each entry
	closedir($myDirectory);	// close directory

	$indexCount	= count($dirArray);
	echo "$indexCount files<br>\n";	//	count elements in array
	sort($dirArray);// sort 'em
	
	echo '<TABLE border="1" cellpadding="5" cellspacing="0">'."\n";// print 'em
	echo "<TR><TH>Filename</TH><th>Filetype</th><th>Filesize</th></TR>\n";
	for($index=0; $index < $indexCount; $index++) { // loop through the array of files and print them all
		if (substr("$dirArray[$index]", 0, 1) != "."){ // don't list hidden files
		echo "<TR><TD><a href=\"$dirArray[$index]\">$dirArray[$index]</a></td>";
		echo '<td>'.filetype($dir.'/'.$dirArray[$index]).'</td>';
		echo '<td>'.filesize($dir.'/'.$dirArray[$index]).'</td>';
		echo "</TR>\n";
		}
	}
	print("</TABLE>\n");
	}



function upload_return_bytes($val){		$val = trim($val);   $last = strtolower($val[strlen($val)-1]);
	switch($last) {  case 'g': $val *= 1024;   case 'm': $val *= 1024;   case 'k': $val *= 1024;	}	return $val;}

function get_upload_max_filesize(){ return min(upload_return_bytes(ini_get('upload_max_filesize')),upload_return_bytes(ini_get('post_max_size')));}




/*
http://stackoverflow.com/questions/166221/how-can-i-upload-files-asynchronously
http://stackoverflow.com/questions/6974684/how-to-send-formdata-objects-with-ajax-requests-in-jquery
upload 1 file
upload x files
upload x + resize


$name= same; md5; add_counter; id+?; timestamp;
$allowed= *, pics, docs, ?
file size checks;
file_limits_by_user;

ajax / iframe post
*/




//$messages = array(); //status
//upload 1 file
$nume_fila_upl = array(); //raport
$nume_orig_fila = array();
if(!isset($messages)){$messages=array();}
//prea($messages); prea($nume_fila_upl); 	prea($nume_orig_fila); 	die;
function upload_files_new($file_name='',$upload_dir='upload/pics',$alowed='jpg',$camp='userfile',$max_size=5242880,$perm=0775){ //5 mb limit
	global $messages,$nume_fila_upl,$nume_orig_fila; //status
	
	if(!is_dir($upload_dir) ){mkdir($upload_dir, $perm,true); chmod($upload_dir,$perm);}
	if(substr($upload_dir,-1,1)!='/'){$upload_dir.='/';}	
	
	$limit=ceil($max_size/(1024*1024));
	ini_set('post_max_size', ($limit * count($_FILES[$camp]['tmp_name']) ).'M');
	ini_set('upload_max_filesize', $limit.'M');
    
	$max_size=get_upload_max_filesize();
	
	if($alowed==''){$alowed='jpg|jpeg|doc|docx|pdf';} 	$alowed=explode('|',$alowed);
	
	if(isset($_FILES[$camp]['tmp_name'])){ /*** check if a file has been submitted ***/
		$orig_file_name='';	
		if(substr_count($_FILES[$camp]['name'],'.')<1 && $file_name==''){//nu exista un . pt extensie
			$messages[] = 'Invalid file name and type'; return 0;}
		
		$extensie=explode('.',$_FILES[$camp]['name']);
		for($i=0;$i<count($extensie)-1;$i++){$orig_file_name.=$extensie[$i];}//recompun numele original, daca este cazul
		
		$extensie=strtolower($extensie[count($extensie)-1]);	if($extensie=='jpeg'){$extensie='jpg';}
		
		if($file_name==''){
			if(strlen($orig_file_name)==0){$orig_file_name=$_SESSION['id'].'_sid_'.date("Y-m-d_H-i-s");}
			$file_name=$orig_file_name;
			}
		$current_file=$upload_dir. s($file_name) .".".$extensie;		
		$ok='nu';	foreach($alowed as $al){ if($al==$extensie){$ok='ok';} } //validare tip fila
		
		if($_FILES[$camp]['size'] > $max_size){ //check size
			$messages[] = "File size exceeds limit"; return 0;    	} 
		elseif($_FILES[$camp]['size']==0){ //check size
			$messages[] = "No file was selected."; return 0;    	} 
		elseif(!is_uploaded_file($_FILES[$camp]['tmp_name'])){ // check if there is a file in the array
			if(is_file($current_file)){ $messages[] = 'File exists on server'; return 0; }
			else{$messages[] = 'No file uploaded'; return 0;}
			}
		elseif($ok!='ok'){	$messages[] = 'Invalid file type'; return 0;	}
		else{ // copy the file to the specified dir move_uploaded_file
			if(@copy($_FILES[$camp]['tmp_name'], $current_file )){ /*** give praise and thanks to the php gods ***/
				$messages[] = 'File is uploaded';
				$nume_fila_upl[]=$current_file;
				$nume_orig_fila[]=$orig_file_name;
				return 1; 
				}
			else{ $messages[] = 'Uploading Failed'; return 0;}
			}
		}
	else {return 0;}
	}



function upload_files($up_path='upload/wtf',$file_name='jpg',$alowed='',$camp='userfile',$max_size=524288,$perm=0755){
	$file_name=s($file_name);
	if($alowed==''){$alowed='jpg|jpeg|doc|docx|pdf';} 	$alowed=explode('|',$alowed);
	$messages = array(); /*** an array to hold messages ***/
	$filenames= array();
	$upload_dir= relative_path().$up_path; /*** the upload directory ***/
	if(!is_dir($upload_dir) ){mkdir($upload_dir, $perm);}
	
	if( count($_FILES[$camp]['tmp_name'])>0 ){	$limit=ceil($max_size/(1024*1024));	}
	else{ return 0;}
	ini_set('post_max_size', ($limit * count($_FILES[$camp]['tmp_name']) ).'M');
	ini_set('upload_max_filesize', $limit.'M');
    
    if(isset($_FILES[$camp]['tmp_name'])){ /*** check if a file has been submitted ***/
		
        for($i=0; $i < count($_FILES[$camp]['tmp_name']);$i++){  /** loop through the array of files ***/
			/* extensie si filemane */
			if($file_name==''){$file_name='file';}
			$extensie=explode('.',$_FILES[$camp]['name'][$i]);
			$extensie = strtolower( $extensie[ (count($extensie)-1) ] );
			if($extensie=='jpeg'){$extensie='jpg';}
			$current_file=$upload_dir.'/'.$file_name."_$i.$extensie";
			
			unset($ok,$al);
			foreach($alowed as $al){ if($al==$extensie){$ok='ok';} } 
			
            if(!is_uploaded_file($_FILES[$camp]['tmp_name'][$i])){ // check if there is a file in the array
				if(is_file($current_file)) $messages[$i] = 'File exists on server'; 
				else $messages[$i] = 'No file uploaded';
				}
            elseif($_FILES[$camp]['size'][$i] > $max_size){ /*** check if the file is less then the max php.ini size ***/
                $messages[$i] = "File size exceeds limit";
            	}
			elseif($ok!='ok'){	$messages[$i] = 'Invalid file type';	}
            else{ // copy the file to the specified dir 
				if(@copy($_FILES[$camp]['tmp_name'][$i], $current_file )){ /*** give praise and thanks to the php gods ***/
					$messages[$i] = 'File is uploaded';
					$status_fila[$i] = 1;
					$filenames[$i]=$file_name."_$i.$extensie";
					$file_path[$i]=$up_path.'/'.$file_name."_$i.$extensie";
					$file_full_path[$i]=relative_path().$up_path.'/'.$file_name."_$i.$extensie";
					}
				else{ $messages[$i] = 'Uploading Failed'; $filenames[$i]='';}
				}
			}
		}
	$ret['messages']=$messages;  $ret['status']=1;	$ret['filenames']=$filenames;
	$ret['file_path']=$file_path;	$ret['file_full_path']=$file_full_path; $ret['status_fila']=$status_fila;
	return $ret;
	}
	/* 
	
$status_upload = upload_files('upload/wtf2','numeee','gif|jpg');

if(sizeof($status_upload['file_path'])!= 0){	
	foreach($status_upload['file_path'] as $img_path){
		thumb($img_path,$x=128,$y=96);
		}
	}

	
<?php if(sizeof($messages) != 0){ foreach($status_upload['messages'] as $err){ echo $err.'<br />';} } ?>

*/



//////////////////////////////////////////////////////////////////////////////////////////////////////
function sitemap(){	$site='http://www.autobike.ro';
	$p1='1.0'; $p2='0.8'; $p3='0.6'; $p4='0.5';			$c1='always'; $c2='4 days'; $c3='10 days'; $c4='24 days';
	$structura_statica=array('/produse/','/contact/','/oferta.php','/sitemap/');// cu / la sfarsit!
	/***********************************************************/
	$s='<?xml version="1.0" encoding="UTF-8" ?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">
';	$r="User-agent: *\r\n\r\nSitemap: $site/sitemap.xml\r\n\r\nAllow: /"."\r\n\r\n";
	$s.="	<url>\r\n		<loc>".$site."</loc>\r\n";
	$s.="		<changefreq>$c1</changefreq>\r\n		<priority>$p1</priority>\r\n	</url>\r\n";
	foreach($structura_statica as $val){$s.="	<url>\r\n		<loc>$site";
		if(substr($val,-1)=='/'){$s.=substr($val,0,-1);}else{$s.=$val;}
		$s.="</loc>\r\n		<changefreq>$c2</changefreq>\r\n		<priority>$p2</priority>\r\n	</url>\r\n";
		$r.='Allow: '.$val."\r\n\r\n"; 
		}
		
	$rez1=mysql_query("SELECT * FROM `categorii` WHERE 1");
	while($cat=mysql_fetch_array($rez1,1)){//cat
		$s.="	<url>\r\n		<loc>".$site.'/'.s($cat['categorie'])."</loc>\r\n";
		$s.="		<changefreq>$c2</changefreq>\r\n		<priority>$p2</priority>\r\n	</url>\r\n";
		$r.='Allow: /'.s($cat['categorie'])."/\r\n\r\n";
		$rez2=mysql_query("SELECT * FROM `subcategorii` WHERE `cat_id`='$cat[index]'");
		while($subcat=mysql_fetch_array($rez2,1)){//scat
			$s.="	<url>\r\n		<loc>".$site.'/'.s($cat['categorie']).'/'.s($subcat['subcategorie'])."</loc>\r\n";
			$s.="		<changefreq>$c3</changefreq>\r\n		<priority>$p3</priority>\r\n	</url>\r\n";
			$r.='Allow: /'.s($cat['categorie']).'/'.s($subcat['subcategorie'])."/\r\n\r\n";
			$rez3=mysql_query("SELECT * FROM `produse` WHERE `subcat_id`='".$subcat['subcat_id']."' ORDER BY `nume` ASC");
			while($prod=mysql_fetch_array($rez3,1)){//prod
				$s.="	<url>\r\n		<loc>$site/".s($cat['categorie']).'/'.s($subcat['subcategorie']).'/'.s($prod['nume']);
				$s.=".php</loc>\r\n		<changefreq>$c4</changefreq>\r\n		<priority>$p4</priority>\r\n	</url>\r\n";
				$r.='Allow: /'.s($cat['categorie']).'/'.s($subcat['subcategorie']).'/'.s($prod['nume']).".php\r\n\r\n";
				}
			}
		}	$s.='</urlset>';
	
	$fisier=relative_path().'robots.txt';	if(is_file($fisier)){unlink($fisier);}
	$fp = fopen($fisier,"w"); if(!$fp){return 0;}
	fprintf($fp,$r);fclose($fp);chmod($fisier, 0644);
	
	$fisier=relative_path().'sitemap.xml';	if(is_file($fisier)){unlink($fisier);}
	$fp = fopen($fisier,"w"); if(!$fp){return 0;}
	fprintf($fp,$s);fclose($fp);chmod($fisier, 0644);
	return 1;}





//////////////////////////////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////////////////

function getRealIpAddr() {
		if(isset($_SERVER['HTTP_CLIENT_IP']) && is_ip($_SERVER['HTTP_CLIENT_IP'])){        		return $_SERVER['HTTP_CLIENT_IP'];}
	elseif(isset($_SERVER['HTTP_X_FORWARDED_FOR']) && is_ip($_SERVER['HTTP_X_FORWARDED_FOR'])) {return $_SERVER['HTTP_X_FORWARDED_FOR'];}
	elseif(isset($_SERVER['HTTP_X_FORWARDED']) && is_ip($_SERVER['HTTP_X_FORWARDED'])) {		return $_SERVER['HTTP_X_FORWARDED'];}
	elseif(isset($_SERVER['HTTP_FORWARDED_FOR']) && is_ip($_SERVER['HTTP_FORWARDED_FOR'])){     return $_SERVER['HTTP_FORWARDED_FOR'];}
	elseif(isset($_SERVER['HTTP_FORWARDED']) && is_ip($_SERVER['HTTP_FORWARDED'])){				return $_SERVER['HTTP_FORWARDED'];}
	elseif(isset($_SERVER['REMOTE_ADDR']) && is_ip($_SERVER['REMOTE_ADDR'])){					return $_SERVER['REMOTE_ADDR'];}
	return 'UNKNOWN_IP'; }
function is_ip($ip){return filter_var($ip, FILTER_VALIDATE_IP);}
function is_ipv4($ip){return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4);}
function is_ipv6($ip){return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6);}

//0000:0000:0000:0000:0000:0000:192.168.111.111 ipv6+tunelare 46 chars
//2001:0db8:85a3:0000:0000:8a2e:0370:7334 ipv6 40 chars

// Uncompress an IPv6 address --- @param ip adresse IP IPv6 x d'compresser ---  @return ip adresse IP IPv6 d'compress'
//XXXX:XXXX:XXXX:XXXX:XXXX:XXXX:AAA.BBB.CCC.DDD //45 chrs
//0123:4567:89ab:cdef:0123:4567:89ab:cdef //39 chrs
function uncompress_ipv6($ip =""){ 
	if ($ip == ""){$ip = getRealIpAddr();}
	if(is_ipv6($ip) && strstr($ip,"::" )){
		$e = explode(":", $ip);
		$s = 8-sizeof($e)+1;
		foreach($e as $key=>$val){
			if ($val == ""){ for($i==0;$i<=$s;$i++){$newip[] = 0;} }
			else {$newip[] = $val;}
			}
		$ip = implode(":", $newip);
		}
	return $ip;
	}

// Compress an IPv6 address  --- @param ip adresse IP IPv6 x compresser --- @return ip adresse IP IPv6 compress'
function compress_ipv6($ip =""){
	if ($ip == ""){getRealIpAddr();}
	if(!strstr($ip,"::" )){
		$e = explode(":", $ip);
		$zeros = array(0);
		$result = array_intersect ($e, $zeros );
		if (sizeof($result) >= 6){
			if ($e[0]==0) {$newip[] = "";}
			foreach($e as $key=>$val){  if($val !=="0"){$newip[] = $val;}  }
			$ip = implode("::", $newip);
			}
		}
	return $ip;
	}


/**
 * A PHP port of URLify.js from the Django project
 * (https://github.com/django/django/blob/master/django/contrib/admin/static/admin/js/urlify.js).
 * Handles symbols from Latin languages, Greek, Turkish, Bulgarian, Russian,
 * Ukrainian, Czech, Polish, Romanian, Latvian, Lithuanian, Vietnamese, Arabic,
 * Serbian, Azerbaijani and Kazakh. Symbols it cannot transliterate
 * it will simply omit.
 *
 * Usage:
 *	To generate slugs for URLs:
 *     echo URLify::filter (' J\'étudie le français ');
 *     // "jetudie-le-francais"
 *
 *     echo URLify::filter ('Lo siento, no hablo español.');
 *     // "lo-siento-no-hablo-espanol"

To generate slugs for file names:

	echo URLify::filter ('фото.jpg', 60, "", true);
	// "foto.jpg"

To simply transliterate characters:

	echo URLify::downcode ('J\'étudie le français');
	// "J'etudie le francais"
	echo URLify::downcode ('Lo siento, no hablo español.');
	// "Lo siento, no hablo espanol."

Or use transliterate() alias: 

	echo URLify::transliterate ('Lo siento, no hablo español.');
	// "Lo siento, no hablo espanol."



To extend the character list:

	URLify::add_chars (array (
		'¿' => '?', '®' => '(r)', '¼' => '1/4',
		'½' => '1/2', '¾' => '3/4', '¶' => 'P'
	));

	echo URLify::downcode ('¿ ® ¼ ¼ ¾ ¶');
	// "? (r) 1/2 1/2 3/4 P"

To extend the list of words to remove:

	URLify::remove_words (array ('remove', 'these', 'too'));

To prioritize a certain language map:

	echo URLify::filter (' Ägypten und Österreich besitzen wie üblich ein Übermaß an ähnlich öligen Attachés ',60,"de");
	// "aegypten-und-oesterreich-besitzen-wie-ueblich-ein-uebermass-aehnlich-oeligen-attaches"

	echo URLify::filter ('Cağaloğlu, çalıştığı, müjde, lazım, mahkûm',60,"tr");
	// "cagaloglu-calistigi-mujde-lazim-mahkum"

*/


class URLify
{
	public static $maps = array (
		'de' => array ( /* German */
			'Ä' => 'Ae', 'Ö' => 'Oe', 'Ü' => 'Ue', 'ä' => 'ae', 'ö' => 'oe', 'ü' => 'ue', 'ß' => 'ss',
			'ẞ' => 'SS'
		),
		'latin' => array (
			'À' => 'A', 'Á' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Ä' => 'A', 'Å' => 'A','Ă' => 'A', 'Æ' => 'AE', 'Ç' =>
			'C', 'È' => 'E', 'É' => 'E', 'Ê' => 'E', 'Ë' => 'E', 'Ì' => 'I', 'Í' => 'I', 'Î' => 'I',
			'Ï' => 'I', 'Ð' => 'D', 'Ñ' => 'N', 'Ò' => 'O', 'Ó' => 'O', 'Ô' => 'O', 'Õ' => 'O', 'Ö' =>
			'O', 'Ő' => 'O', 'Ø' => 'O', 'Œ' => 'OE' ,'Ș' => 'S','Ț' => 'T', 'Ù' => 'U', 'Ú' => 'U', 'Û' => 'U', 'Ü' => 'U', 'Ű' => 'U',
			'Ý' => 'Y', 'Þ' => 'TH', 'ß' => 'ss', 'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' =>
			'a', 'å' => 'a', 'ă' => 'a', 'æ' => 'ae', 'ç' => 'c', 'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e',
			'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i', 'ð' => 'd', 'ñ' => 'n', 'ò' => 'o', 'ó' =>
			'o', 'ô' => 'o', 'õ' => 'o', 'ö' => 'o', 'ő' => 'o', 'ø' => 'o', 'œ' => 'oe', 'ș' => 's', 'ț' => 't', 'ù' => 'u', 'ú' => 'u',
			'û' => 'u', 'ü' => 'u', 'ű' => 'u', 'ý' => 'y', 'þ' => 'th', 'ÿ' => 'y'
		),
		'latin_symbols' => array (
			'©' => '(c)'
		),
		'el' => array ( /* Greek */
			'α' => 'a', 'β' => 'b', 'γ' => 'g', 'δ' => 'd', 'ε' => 'e', 'ζ' => 'z', 'η' => 'h', 'θ' => '8',
			'ι' => 'i', 'κ' => 'k', 'λ' => 'l', 'μ' => 'm', 'ν' => 'n', 'ξ' => '3', 'ο' => 'o', 'π' => 'p',
			'ρ' => 'r', 'σ' => 's', 'τ' => 't', 'υ' => 'y', 'φ' => 'f', 'χ' => 'x', 'ψ' => 'ps', 'ω' => 'w',
			'ά' => 'a', 'έ' => 'e', 'ί' => 'i', 'ό' => 'o', 'ύ' => 'y', 'ή' => 'h', 'ώ' => 'w', 'ς' => 's',
			'ϊ' => 'i', 'ΰ' => 'y', 'ϋ' => 'y', 'ΐ' => 'i',
			'Α' => 'A', 'Β' => 'B', 'Γ' => 'G', 'Δ' => 'D', 'Ε' => 'E', 'Ζ' => 'Z', 'Η' => 'H', 'Θ' => '8',
			'Ι' => 'I', 'Κ' => 'K', 'Λ' => 'L', 'Μ' => 'M', 'Ν' => 'N', 'Ξ' => '3', 'Ο' => 'O', 'Π' => 'P',
			'Ρ' => 'R', 'Σ' => 'S', 'Τ' => 'T', 'Υ' => 'Y', 'Φ' => 'F', 'Χ' => 'X', 'Ψ' => 'PS', 'Ω' => 'W',
			'Ά' => 'A', 'Έ' => 'E', 'Ί' => 'I', 'Ό' => 'O', 'Ύ' => 'Y', 'Ή' => 'H', 'Ώ' => 'W', 'Ϊ' => 'I',
			'Ϋ' => 'Y'
		),
		'tr' => array ( /* Turkish */
			'ş' => 's', 'Ş' => 'S', 'ı' => 'i', 'İ' => 'I', 'ç' => 'c', 'Ç' => 'C', 'ü' => 'u', 'Ü' => 'U',
			'ö' => 'o', 'Ö' => 'O', 'ğ' => 'g', 'Ğ' => 'G'
		),
		'bg' => array( /* Bulgarian */
			'Щ' => 'Sht', 'Ш' => 'Sh', 'Ч' => 'Ch', 'Ц' => 'C', 'Ю' => 'Yu', 'Я' => 'Ya',
			'Ж' => 'J',   'А' => 'A',  'Б' => 'B',  'В' => 'V', 'Г' => 'G',  'Д' => 'D',
			'Е' => 'E',   'З' => 'Z',  'И' => 'I',  'Й' => 'Y', 'К' => 'K',  'Л' => 'L',
			'М' => 'M',   'Н' => 'N',  'О' => 'O',  'П' => 'P', 'Р' => 'R',  'С' => 'S',
			'Т' => 'T',   'У' => 'U',  'Ф' => 'F',  'Х' => 'H', 'Ь' => '',   'Ъ' => 'A',
			'щ' => 'sht', 'ш' => 'sh', 'ч' => 'ch', 'ц' => 'c', 'ю' => 'yu', 'я' => 'ya',
			'ж' => 'j',   'а' => 'a',  'б' => 'b',  'в' => 'v', 'г' => 'g',  'д' => 'd',
			'е' => 'e',   'з' => 'z',  'и' => 'i',  'й' => 'y', 'к' => 'k',  'л' => 'l',
			'м' => 'm',   'н' => 'n',  'о' => 'o',  'п' => 'p', 'р' => 'r',  'с' => 's',
			'т' => 't',   'у' => 'u',  'ф' => 'f',  'х' => 'h', 'ь' => '',   'ъ' => 'a'
		),
		'ru' => array ( /* Russian */
			'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd', 'е' => 'e', 'ё' => 'yo', 'ж' => 'zh',
			'з' => 'z', 'и' => 'i', 'й' => 'i', 'к' => 'k', 'л' => 'l', 'м' => 'm', 'н' => 'n', 'о' => 'o',
			'п' => 'p', 'р' => 'r', 'с' => 's', 'т' => 't', 'у' => 'u', 'ф' => 'f', 'х' => 'h', 'ц' => 'c',
			'ч' => 'ch', 'ш' => 'sh', 'щ' => 'sh', 'ъ' => '', 'ы' => 'y', 'ь' => '', 'э' => 'e', 'ю' => 'yu',
			'я' => 'ya',
			'А' => 'A', 'Б' => 'B', 'В' => 'V', 'Г' => 'G', 'Д' => 'D', 'Е' => 'E', 'Ё' => 'Yo', 'Ж' => 'Zh',
			'З' => 'Z', 'И' => 'I', 'Й' => 'I', 'К' => 'K', 'Л' => 'L', 'М' => 'M', 'Н' => 'N', 'О' => 'O',
			'П' => 'P', 'Р' => 'R', 'С' => 'S', 'Т' => 'T', 'У' => 'U', 'Ф' => 'F', 'Х' => 'H', 'Ц' => 'C',
			'Ч' => 'Ch', 'Ш' => 'Sh', 'Щ' => 'Sh', 'Ъ' => '', 'Ы' => 'Y', 'Ь' => '', 'Э' => 'E', 'Ю' => 'Yu',
			'Я' => 'Ya',
			'№' => ''
		),
		'uk' => array ( /* Ukrainian */
			'Є' => 'Ye', 'І' => 'I', 'Ї' => 'Yi', 'Ґ' => 'G', 'є' => 'ye', 'і' => 'i', 'ї' => 'yi', 'ґ' => 'g'
		),
        'kk' => array ( /* Kazakh */
            'Ә' => 'A', 'Ғ' => 'G', 'Қ' => 'Q', 'Ң' => 'N', 'Ө' => 'O', 'Ұ' => 'U', 'Ү' => 'U', 'Һ' => 'H',
            'ә' => 'a', 'ғ' => 'g', 'қ' => 'q', 'ң' => 'n', 'ө' => 'o', 'ұ' => 'u', 'ү' => 'u', 'һ' => 'h',
        ),
		'cs' => array ( /* Czech */
			'č' => 'c', 'ď' => 'd', 'ě' => 'e', 'ň' => 'n', 'ř' => 'r', 'š' => 's', 'ť' => 't', 'ů' => 'u',
			'ž' => 'z', 'Č' => 'C', 'Ď' => 'D', 'Ě' => 'E', 'Ň' => 'N', 'Ř' => 'R', 'Š' => 'S', 'Ť' => 'T',
			'Ů' => 'U', 'Ž' => 'Z'
		),
		'pl' => array ( /* Polish */
			'ą' => 'a', 'ć' => 'c', 'ę' => 'e', 'ł' => 'l', 'ń' => 'n', 'ó' => 'o', 'ś' => 's', 'ź' => 'z',
			'ż' => 'z', 'Ą' => 'A', 'Ć' => 'C', 'Ę' => 'e', 'Ł' => 'L', 'Ń' => 'N', 'Ó' => 'O', 'Ś' => 'S',
			'Ź' => 'Z', 'Ż' => 'Z'
		),
		'ro' => array ( /* Romanian */
			'ă' => 'a', 'â' => 'a', 'î' => 'i', 'ș' => 's', 'ț' => 't', 'Ţ' => 'T', 'ţ' => 't'
		),
		'lv' => array ( /* Latvian */
			'ā' => 'a', 'č' => 'c', 'ē' => 'e', 'ģ' => 'g', 'ī' => 'i', 'ķ' => 'k', 'ļ' => 'l', 'ņ' => 'n',
			'š' => 's', 'ū' => 'u', 'ž' => 'z', 'Ā' => 'A', 'Č' => 'C', 'Ē' => 'E', 'Ģ' => 'G', 'Ī' => 'i',
			'Ķ' => 'k', 'Ļ' => 'L', 'Ņ' => 'N', 'Š' => 'S', 'Ū' => 'u', 'Ž' => 'Z'
		),
		'lt' => array ( /* Lithuanian */
			'ą' => 'a', 'č' => 'c', 'ę' => 'e', 'ė' => 'e', 'į' => 'i', 'š' => 's', 'ų' => 'u', 'ū' => 'u', 'ž' => 'z',
			'Ą' => 'A', 'Č' => 'C', 'Ę' => 'E', 'Ė' => 'E', 'Į' => 'I', 'Š' => 'S', 'Ų' => 'U', 'Ū' => 'U', 'Ž' => 'Z'
		),
		'vn' => array ( /* Vietnamese */
			'Á' => 'A', 'À' => 'A', 'Ả' => 'A', 'Ã' => 'A', 'Ạ' => 'A', 'Ă' => 'A', 'Ắ' => 'A', 'Ằ' => 'A', 'Ẳ' => 'A', 'Ẵ' => 'A', 'Ặ' => 'A', 'Â' => 'A', 'Ấ' => 'A', 'Ầ' => 'A', 'Ẩ' => 'A', 'Ẫ' => 'A', 'Ậ' => 'A',
			'á' => 'a', 'à' => 'a', 'ả' => 'a', 'ã' => 'a', 'ạ' => 'a', 'ă' => 'a', 'ắ' => 'a', 'ằ' => 'a', 'ẳ' => 'a', 'ẵ' => 'a', 'ặ' => 'a', 'â' => 'a', 'ấ' => 'a', 'ầ' => 'a', 'ẩ' => 'a', 'ẫ' => 'a', 'ậ' => 'a',
			'É' => 'E', 'È' => 'E', 'Ẻ' => 'E', 'Ẽ' => 'E', 'Ẹ' => 'E', 'Ê' => 'E', 'Ế' => 'E', 'Ề' => 'E', 'Ể' => 'E', 'Ễ' => 'E', 'Ệ' => 'E',
			'é' => 'e', 'è' => 'e', 'ẻ' => 'e', 'ẽ' => 'e', 'ẹ' => 'e', 'ê' => 'e', 'ế' => 'e', 'ề' => 'e', 'ể' => 'e', 'ễ' => 'e', 'ệ' => 'e',
			'Í' => 'I', 'Ì' => 'I', 'Ỉ' => 'I', 'Ĩ' => 'I', 'Ị' => 'I', 'í' => 'i', 'ì' => 'i', 'ỉ' => 'i', 'ĩ' => 'i', 'ị' => 'i',
			'Ó' => 'O', 'Ò' => 'O', 'Ỏ' => 'O', 'Õ' => 'O', 'Ọ' => 'O', 'Ô' => 'O', 'Ố' => 'O', 'Ồ' => 'O', 'Ổ' => 'O', 'Ỗ' => 'O', 'Ộ' => 'O', 'Ơ' => 'O', 'Ớ' => 'O', 'Ờ' => 'O', 'Ở' => 'O', 'Ỡ' => 'O', 'Ợ' => 'O',
			'ó' => 'o', 'ò' => 'o', 'ỏ' => 'o', 'õ' => 'o', 'ọ' => 'o', 'ô' => 'o', 'ố' => 'o', 'ồ' => 'o', 'ổ' => 'o', 'ỗ' => 'o', 'ộ' => 'o', 'ơ' => 'o', 'ớ' => 'o', 'ờ' => 'o', 'ở' => 'o', 'ỡ' => 'o', 'ợ' => 'o',
			'Ú' => 'U', 'Ù' => 'U', 'Ủ' => 'U', 'Ũ' => 'U', 'Ụ' => 'U', 'Ư' => 'U', 'Ứ' => 'U', 'Ừ' => 'U', 'Ử' => 'U', 'Ữ' => 'U', 'Ự' => 'U',
			'ú' => 'u', 'ù' => 'u', 'ủ' => 'u', 'ũ' => 'u', 'ụ' => 'u', 'ư' => 'u', 'ứ' => 'u', 'ừ' => 'u', 'ử' => 'u', 'ữ' => 'u', 'ự' => 'u',
			'Ý' => 'Y', 'Ỳ' => 'Y', 'Ỷ' => 'Y', 'Ỹ' => 'Y', 'Ỵ' => 'Y', 'ý' => 'y', 'ỳ' => 'y', 'ỷ' => 'y', 'ỹ' => 'y', 'ỵ' => 'y',
			'Đ' => 'D', 'đ' => 'd'
		),
		'ar' => array ( /* Arabic */
			'أ' => 'a', 'ب' => 'b', 'ت' => 't', 'ث' => 'th', 'ج' => 'g', 'ح' => 'h', 'خ' => 'kh', 'د' => 'd',
			'ذ' => 'th', 'ر' => 'r', 'ز' => 'z', 'س' => 's', 'ش' => 'sh', 'ص' => 's', 'ض' => 'd', 'ط' => 't',
			'ظ' => 'th', 'ع' => 'aa', 'غ' => 'gh', 'ف' => 'f', 'ق' => 'k', 'ك' => 'k', 'ل' => 'l', 'م' => 'm',
			'ن' => 'n', 'ه' => 'h', 'و' => 'o', 'ي' => 'y',
			'ا' => 'a', 'إ' => 'a', 'آ' => 'a', 'ؤ' => 'o', 'ئ' => 'y', 'ء' => 'aa',
			'٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4', '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9',
		),
		'fa' => array ( /* Persian */
			'گ' => 'g', 'ژ' => 'j', 'پ' => 'p', 'چ' => 'ch', 'ی' => 'y', 'ک' => 'k',
			'۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4', '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9',
		),
		'sr' => array ( /* Serbian */
			'ђ' => 'dj', 'ј' => 'j', 'љ' => 'lj', 'њ' => 'nj', 'ћ' => 'c', 'џ' => 'dz', 'đ' => 'dj',
			'Ђ' => 'Dj', 'Ј' => 'j', 'Љ' => 'Lj', 'Њ' => 'Nj', 'Ћ' => 'C', 'Џ' => 'Dz', 'Đ' => 'Dj'
		),
		'az' => array ( /* Azerbaijani */
			'ç' => 'c', 'ə' => 'e', 'ğ' => 'g', 'ı' => 'i', 'ö' => 'o', 'ş' => 's', 'ü' => 'u',
			'Ç' => 'C', 'Ə' => 'E', 'Ğ' => 'G', 'İ' => 'I', 'Ö' => 'O', 'Ş' => 'S', 'Ü' => 'U'
		)
	);

	/**
	 * List of words to remove from URLs.
	 */
	public static $remove_list = array (
/*		'a', 'an', 'as', 'at', 'before', 'but', 'by', 'for', 'from',
		'is', 'in', 'into', 'like', 'of', 'off', 'on', 'onto', 'per',
		'since', 'than', 'the', 'this', 'that', 'to', 'up', 'via',
		'with'*/
	);

	/**
	 * The character map.
	 */
	private static $map = array ();

	/**
	 * The character list as a string.
	 */
	private static $chars = '';

	/**
	 * The character list as a regular expression.
	 */
	private static $regex = '';

	/**
	 * The current language
	 */
	private static $language = '';

	/**
	 * Initializes the character map.
     * @param string $language
	 */
	private static function init ($language = "")
    {
		if (count (self::$map) > 0 && (($language == "") || ($language == self::$language))) {
			return;
		}

		/* Is a specific map associated with $language ? */
		if (isset(self::$maps[$language]) && is_array(self::$maps[$language])) {
			/* Move this map to end. This means it will have priority over others */
			$m = self::$maps[$language];
			unset(self::$maps[$language]);
			self::$maps[$language] = $m;
		}
		/* Reset static vars */
		self::$language = $language;
		self::$map = array();
		self::$chars = '';

		foreach (self::$maps as $map) {
			foreach ($map as $orig => $conv) {
				self::$map[$orig] = $conv;
				self::$chars .= $orig;
			}
		}

		self::$regex = '/[' . self::$chars . ']/u';
	}

	/**
	 * Add new characters to the list. `$map` should be a hash.
     * @param array $map
	 */
	public static function add_chars ($map)
    {
		if (! is_array ($map)) {
			throw new LogicException ('$map must be an associative array.');
		}
		self::$maps[] = $map;
		self::$map = array ();
		self::$chars = '';
	}

	/**
	 * Append words to the remove list. Accepts either single words
	 * or an array of words.
     * @param mixed $words
	 */
	public static function remove_words ($words)
    {
		$words = is_array ($words) ? $words : array ($words);
		self::$remove_list = array_merge (self::$remove_list, $words);
	}

	/**
	 * Transliterates characters to their ASCII equivalents.
     * $language specifies a priority for a specific language.
     * The latter is useful if languages have different rules for the same character.
     * @param string $text
     * @param string $language
     * @return string
	 */
	public static function downcode ($text, $language = "")
    {
		self::init ($language);

		if (preg_match_all (self::$regex, $text, $matches)) {
			for ($i = 0; $i < count ($matches[0]); $i++) {
				$char = $matches[0][$i];
				if (isset (self::$map[$char])) {
					$text = str_replace ($char, self::$map[$char], $text);
				}
			}
		}
		return $text;
	}

	/**
	 * Filters a string, e.g., "Petty theft" to "petty-theft"
	 * @param string $text The text to return filtered
	 * @param int $length The length (after filtering) of the string to be returned
	 * @param string $language The transliteration language, passed down to downcode()
	 * @param bool $file_name Whether there should be and additional filter considering this is a filename
	 * @param bool $use_remove_list Whether you want to remove specific elements previously set in self::$remove_list
	 * @param bool $lower_case Whether you want the filter to maintain casing or lowercase everything (default)
	 * @param bool $treat_underscore_as_space Treat underscore as space, so it will replaced with "-"
     * @return string
	 */
	public static function filter ($text, $length = 60, $language = "", $file_name = false, $use_remove_list = true, $lower_case = true, $treat_underscore_as_space = true)
    {
		$text = self::downcode ($text,$language);

		if ($use_remove_list) {
			// remove all these words from the string before urlifying
			$text = preg_replace ('/\b(' . join ('|', self::$remove_list) . ')\b/i', '', $text);
		}

		// if downcode doesn't hit, the char will be stripped here
		$remove_pattern = ($file_name) ? '/[^_\-.\-a-zA-Z0-9\s]/u' : '/[^\s_\-a-zA-Z0-9]/u';
		$text = preg_replace ($remove_pattern, '', $text); // remove unneeded chars
		if ($treat_underscore_as_space) {
		    	$text = str_replace ('_', ' ', $text);             // treat underscores as spaces
		}
		$text = preg_replace ('/^\s+|\s+$/u', '', $text);  // trim leading/trailing spaces
		$text = preg_replace ('/[-\s]+/u', '-', $text);    // convert spaces to hyphens
		if ($lower_case) {
			$text = strtolower ($text);                        // convert to lowercase
		}

		return trim (substr ($text, 0, $length), '-');     // trim to first $length chars
	}

	/**
	 * Alias of `URLify::downcode()`.
	 */
	public static function transliterate ($text)
    {
		return self::downcode ($text);
	}
}



function base64data_to_file($str,$folder_to_save_images){ // $folder_to_save_images sa fi calea de la /uploads/messages/img/
	$imagini = extract_between($str,'"data:','"');
	foreach($imagini as $d){
		$ext=explode(',',$d);//image/png;base64,
		$link_to_replace=$ext[0].',';
		$ext=explode('/',$ext[0]);//png;base64
		$ext=explode(';',$ext[1]);//png
		$ext=$ext[0];
		if(!in_array($ext,array('png','jpg','jpeg','tiff'))){ continue; }
		$d=substr($d,strlen($link_to_replace));
		$link_to_replace='data:'.$link_to_replace.$d;
		$filename=md5($d.time().rand(22,35656346)).'.'.$ext;
		file_put_contents(THF_ROOT.$folder_to_save_images.$filename,base64_decode($d));
		$str=str_replace($link_to_replace,$folder_to_save_images.$filename,$str);
	}
	return $str;
}

function file_to_base64data($html_str){ // $folder_to_save_images sa fi calea de la /uploads/messages/img/
	$imagini = extract_between($html_str,'<img','>');
	foreach($imagini as $d){
		$link=extract_between($d,'src="','"');
		$link=$link[0];
		if(substr($link,0,1)=='/'){
			$info=pathinfo($link);
			if(isset($info['extension']) && $info['extension'] && in_array($info['extension'],array('png','jpg','jpeg','tiff'))){
				$link_64='data:image/'.$info['extension'].';base64,'.base64_encode(file_get_contents(THF_ROOT.$link));
				$html_str=str_replace($link,$link_64,$html_str);
			}
		}
	}
	return $html_str;
}

?>