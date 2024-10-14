<?php
error_reporting(E_ALL);




set_time_limit(0);

ob_end_flush();

// Turn on implicit output dump, so we'll see what we're getting as it arrives.
ob_implicit_flush();

echo "Starting:<br>\n";
$address = '127.0.0.1';
$port = 11317;

if (($sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP)) === false) {
    echo "socket_create() failed: reason: " . socket_strerror(socket_last_error()) . "\n";
}

if (socket_bind($sock, $address, $port) === false) {
    echo "socket_bind() failed: reason: " . socket_strerror(socket_last_error($sock)) . "\n";
}

if (socket_listen($sock, 1000) === false) {
    echo "socket_listen() failed: reason: " . socket_strerror(socket_last_error($sock)) . "\n";
}

//clients array
$clients = array();

do {
    file_put_contents(
        __DIR__.DIRECTORY_SEPARATOR.'hearthBeat',
        microtime(true).PHP_EOL,
        FILE_APPEND
    );
    $read = array_merge([$sock], $clients);
    $write = $except = null;
    // Set up a blocking call to socket_select
    if (socket_select($read, $write, $except, $tv_sec = 500) < 1) {
        //    SocketServer::debug("Problem blocking socket_select?");
        continue;
    }

    // Handle new Connections
    if (in_array($sock, $read)) {

        if (($msgsock = socket_accept($sock)) === false) {
            echo "socket_accept() failed: reason: " . socket_strerror(socket_last_error($sock)) . "\n";
            break;
        }
        //    socket_shutdown($msgsock, 2); //off reading(0);writing(1);both(2)
        $clients[] = $msgsock; //$all_sock[(int)$sock] = $sock;

    }

    // Handle Input
    foreach ($clients as $key => &$client) { // for each client
        if (in_array($client, $read)) {
            /////    socket_shutdown($client, 1); //off write
            //las citire cu buffer REQUIRED!!!
            $buf = '';
            while ($sRead = socket_read($client, 2048)) {
                $buf .= $sRead;
            }

        //    socket_shutdown($client, 0); //off reading
            if (strlen($buf) < 1) {
                continue; //just connected without any send
            }
            socket_shutdown($client, 0); //off reading
            if ($buf != 'shutdown') {
                $talkback = '';
                for ($i = 0; $i < 10; $i++) {
                    for ($j = 0; $j < 10; $j++) {
                        $talkback .= str_repeat((string)$i, 102) . "\n";
                    }

                }

                $md5 = substr($buf, 0, 32);
                $talkback = "#{$key}~~$md5~~" . strlen($buf) . "~~\n" . str_repeat($talkback, rand(1,25)) . '~~' . $buf;
                addSvResponseLengthCheckup($talkback);
                //$talkback = 'KKKKKKKKKKKKKKKKKKKKK';
                ///////// WRITE

                socket_write($client, $talkback, strlen($talkback));

                socket_shutdown($client, 1); //off reading(0);writing(1);both(2)
                socket_close($client);
                if ($key % 100 == 0) {
                    echo "\n";
                    listMemoryStats($key);
                    echo "\n<hr>\n";
                }
                echo "#$key CL: " . count($clients) . "; RECV_SV: " . round(strlen($buf) / 1024, 2) . ' KB; ';
                echo 'SENT_SV: ' . round(strlen($talkback) / 1024, 2) . ' KB; ';
                echo substr($talkback, 0, 51) . "...<br>\n";
                $talkback = '';
                unset($clients[$key]);

                //if(memory_get_usage()>496870912){          break 2;              }
                break;
            }
            if ($buf == 'shutdown') {
                socket_write($client, $buf, strlen($buf));
                socket_close($client);
                unset($clients[$key]);
                listMemoryStats('end');
                print_r($GLOBALS);
                break 2;
            }
            //$talkback = "Client {$key}: you said '$buf'".print_r($clients,true);
            //socket_write($client, $talkback, strlen($talkback));
            //echo "$buf\n";
        }

    }
} while (true);


function listMemoryStats($key)
{
    $peak_memory = number_format(memory_get_peak_usage() / 1024, 0, '.', ',') . " kb";
    $end_memory = number_format(memory_get_usage() / 1024, 0, '.', ',') . " kb";
    echo "#$key; peak: $peak_memory; end: $end_memory <br>\n";
}

function addSvResponseLengthCheckup(string &$sReply, int $iContainerLen = 32)
{

    $sTotalLen = (string)(strlen($sReply) + $iContainerLen);
    $sContainer = str_repeat('@', $iContainerLen - strlen($sTotalLen)) . $sTotalLen;
    $sReply .= $sContainer;
}
socket_close($sock);