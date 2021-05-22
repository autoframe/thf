<?php
namespace autoframe\thf\helpers;

use function str_replace;
use function is_array;
use function htmlentities;
use function print_r;
use function html_entity_decode;
use function explode;
use function count;
//use function unset;
/**
 * autoframe project
 * https://github.com/autoframe
 * Nistor Alexandru Marius 
 * v0.0.1
 * test version first commit
 */

class afrHlp
{
	/**
	 * mysql escape quot
	 * 
	 */

	public static function q($str)
	{
		$str = str_replace(chr(92), chr(92) . chr(92), $str); //fix from \ to \\
		return str_replace("'", "''", $str);                  //replace ' with double ''
	}
	/** 
	 * print as safe html with htmlentities
	 * 
	 */
	public static function h($str, $enc = 'UTF-8')
	{
		if (is_array($str)) {
			foreach ($str as $key => &$val) {
				$val = self::h($val);
			}
		} else {
			$str = @htmlentities($str, ENT_QUOTES | ENT_IGNORE, $enc);
		}
		return $str;
	}

	/**
	 * sage xml string
	 * 
	 */

	public static function h_xml($str)
	{
		return str_replace(
			array('&',		'<',	'>',	'"',	"'",),
			array('&amp;',	'&lt;', '&gt;', '&quot;',	'&apos;',),
			$str
		);
	}
	/**
	 * decode html utf-8 string 
	 */
	public static function uh($html_str, $enc = 'UTF-8')
	{
		return html_entity_decode($html_str, ENT_QUOTES, $enc);
	}

	/**
	 * print_r wrapped to a <pre></pre> wrapper
	 * 
	 */

	public static function prea($array, $return_output = false)
	{
		$out = '<pre>' . print_r(h($array), true) . '</pre>';
		if ($return_output) {
			return $out;
		}
		echo $out;
	}
	/**
	 * this is like a explode with two string parameters
	 * extract_between('html_string','class="','"'); //get all classes
	 */
	public static function extract_between($str = NULL, $start_char = NULL, $end_char = NULL)
	{
		if ($str != NULL && $start_char !== NULL && $start_char != '') {
			if ($end_char === NULL || $end_char == '') {
				$end_char = $start_char;
			}
		} else {
			return array(NULL);
		}
		$out = array();
		$str = explode($start_char, $str);
		$parts = count($str);
		if ($parts < 2) {
			return array(NULL);
		}
		if ($start_char == $end_char) {
			$i = 0;
			while ($i < $parts) {
				$out[] = $str[$i + 1];
				$i += 2;
			}
		} else {
			//unset($str[0]);
			foreach ($str as $i => &$val) {
				if ($i < 1) {
					continue;
				}
				$val = explode($end_char, $val);
				$val = $val[0];
				$out[] = $val;
			}
		}
		return $out;
	}
}
