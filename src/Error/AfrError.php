<?php
declare(strict_types=1);

namespace Autoframe\Core\Error;

class AfrError
{
    /**
     * @param int $iRemoveLastNLevels
     * @param int $options 0 exlude ["object"] | 1  show all ["object"] AND ["args"] | 2 - exlude ["object"] AND ["args"]
     * @param int $limit
     * @return array
     */
    public static function getMinifiedBacktrace(
        int $iRemoveLastNLevels = 1,
        int $options = DEBUG_BACKTRACE_PROVIDE_OBJECT,
        int $limit = 0
    ):array
    {

        $aHuge = debug_backtrace($options,$limit);
        $aHuge = array_slice($aHuge, $iRemoveLastNLevels);
        $aStack = [];
        foreach ( $aHuge as $iKey => & $aItem ) {
            if( isset( $aItem['object'] ) ) {
                unset( $aHuge[ $iKey ][ 'object' ] );
            }
            if( isset( $aItem['args'] ) ) {
                unset( $aHuge[ $iKey ][ 'args' ] );
            }

            $aStack[] =
                ($aItem['file'] ?? '---'). ':' .
                ($aItem['line'] ?? '---'). ' > ' .
                ($aItem['class'] ?? '---'). '::' .
                ($aItem['function'] ?? '---'). ' - ' .
                ($aItem['line'] ?? '---');

        }
        unset($aItem);
        return $aStack;
    }
}

//trigger_error('nive err', E_USER_NOTICE);

function process_error_backtrace_general($errno, $errstr, $errfile, $errline, $errcontext,$args=false) {
    if(!(error_reporting() & $errno)) return;//$debug thorr
    switch($errno) {
        case E_WARNING      :
        case E_USER_WARNING :
        case E_STRICT       :
        case E_NOTICE       :
        case E_USER_NOTICE  :
            $type = 'warning';
            $fatal = false;
            break;
        default             :
            $type = 'fatal error';
            $fatal = true;
            break;
    }
    $trace = array_reverse(debug_backtrace());
    array_pop($trace);
    if(php_sapi_name() == 'cli') {
        echo 'Backtrace from ' . $type . ' \'' . $errstr . '\' at ' . $errfile . ' ' . $errline . ':' . "\n";
        foreach($trace as $item){
            echo '  ' . (isset($item['file']) ? $item['file'] : '<unknown file>') . ' ' . (isset($item['line']) ? $item['line'] : '<unknown line>') . ' calling ' . $item['function'] . '()' . "\n";
        }
    }
    else {
        if($args){$a='Args listfrom ' . $type . ' \'' . $errstr . '\' at ' . $errfile . ' ' . $errline . ':' . "\n";}
        echo '<p class="error_backtrace">' . "\n";
        echo '  Backtrace from ' . $type . ' \'' . $errstr . '\' at ' . $errfile . ' ' . $errline . ':' . "\n";
        echo '  <ol>' . "\n";
        foreach($trace as $item){
            echo '    <li>' . (isset($item['file']) ? $item['file'] : '<unknown file>') . ' ' . (isset($item['line']) ? $item['line'] : '<unknown line>') . ' calling ' . $item['function'] . '()</li>' . "\n";
            if($args){ob_start(); echo $item['function'] . '  <ol>('; prea($item['args']);  echo ')</ol>'."\r\n"; $a.=ob_get_contents(); ob_end_clean();}
        }
        echo '  </ol>' . "\n";
        if($args){echo $a;}
        echo '</p>' . "\n";
    }
    if(ini_get('log_errors')) {
        $items = array();
        foreach($trace as $item)
            $items[] = (isset($item['file']) ? $item['file'] : '<unknown file>') . ' ' . (isset($item['line']) ? $item['line'] : '<unknown line>') . ' calling ' . $item['function'] . '()';
        $message = 'Backtrace from ' . $type . ' \'' . $errstr . '\' at ' . $errfile . ' ' . $errline . ': ' . join(' | ', $items);
        error_log($message);
    }
    if($fatal) exit(1);
}
function process_error_backtrace_ext($errno, $errstr, $errfile, $errline, $errcontext){
    process_error_backtrace_general($errno, $errstr, $errfile, $errline, $errcontext,true);
}


if(isset($debug_thorr) && $debug_thorr && isset($debug_thorr_ext) && $debug_thorr_ext){set_error_handler('process_error_backtrace_ext');}
elseif(isset($debug_thorr) && $debug_thorr){set_error_handler('process_error_backtrace_general');}