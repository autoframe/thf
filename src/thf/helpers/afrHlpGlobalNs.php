<?php
namespace autoframe\thf\helpers;

use autoframe\thf\helpers\afrHlp;

/**
 * autoframe project
 * https://github.com/autoframe
 * Nistor Alexandru Marius 
 * v0.0.1
 * test version first commit
 */

class afrHlpGlobalNs extends afrHlp
{
	
}

function q($str){return afrHlp::q($str);}
function prea($str){return afrHlp::prea($str);}
function h($str){return afrHlp::h($str);}
function h_xml($str){return afrHlp::h_xml($str);}
function extract_between($str,$start,$end){return afrHlp::extract_between($str,$start,$end=null);}
