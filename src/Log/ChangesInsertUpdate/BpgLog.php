<?php

namespace Autoframe\Core\Log\ChangesInsertUpdate;

class BpgLog
{
    function log_operations_x($table,$id_col_name,$id,$mode='i/u',$array,$exclude_from_logging=array('id')){
        $id=floor($id);
        if($mode=='i'){
            foreach($array as $col=>$new_val){
                if(in_array($col,$exclude_from_logging)){continue;}
                log_operations_thf($table,$id,$col,$new_val,'i');
            }
        }
        elseif($mode=='u'){
            $old=many_query("SELECT * FROM `$table` WHERE `$id_col_name` = '".floor($id)."' LIMIT 1");		//prea($old);
            foreach($array as $col=>$new_val){
                if(in_array($col,$exclude_from_logging)){continue;}
                if(isset($old[$col]) && $old[$col]!=$new_val){
                    //echo $col.': '.$new_val.'<br />';
                    //log_operations($tab,$tab_id,$col,$new_val,$op='u');
                    //echo '$old[$col]='.$old[$col].'; $_POST[$col]='.$new_val.'; $col='.$col.'<br>';
                    log_operations_thf($table,$id,$col,$new_val,'u');
                }
            }
        }
        else{die('Use $mode = i / u for log_operations_x');}
    }


    function log_operations_thf($tab,$tab_id,$col,$new_val,$op='u',$uid=NULL){
        if(!is_db_table('logs_operations')){
            $sql="	CREATE TABLE IF NOT EXISTS `logs_operations` (
		  `id` bigint(20) NOT NULL AUTO_INCREMENT,
		  `tab` varchar(40) NOT NULL,
		  `table_id` int(11) NOT NULL,
		  `col` varchar(40) NOT NULL,
		  `new_val` varchar(255) NOT NULL,
		  `uid` smallint(6) NOT NULL DEFAULT '0',
		  `ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
		  `op` enum('i','u','d','ud') NOT NULL DEFAULT 'u' COMMENT 'insert/ update/ delete/ update_col_delete=1',
		  PRIMARY KEY (`id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
            sql_block_execute($sql);
        }
        if($uid===NULL){
            if(isset($GLOBALS["login"])){
                $uid=$GLOBALS["login"]->get_uid();
            }
        }
        $uid=@floor($uid);

        $a=array(
            'tab'=>($tab),
            'table_id'=>($tab_id),
            'col'=>($col),
            'new_val'=>($new_val),
            'uid'=>($uid),
            'ts'=>(date('Y-m-d H:i:s')),
            'op'=>($op),
        );

        return insert_qa(logs_operations,$a);
    }

//log_operations_x(
//	$table='table to log',
//	$id_col_name='id',
//	$id='667',
//	$mode='i/u',
//	$array, //update data
//	$exclude_from_logging=array('id'))
}