<?php
error_reporting(E_ALL);

set_time_limit(0);

// Turn on implicit output dump, so we'll see what we're getting as it arrives.
ob_implicit_flush();

$address = '127.0.0.1';
$port = 10000;

if (($sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP)) === false) {
    echo "socket_create() failed: reason: " . socket_strerror(socket_last_error()) . "\n";
}

if (socket_bind($sock, $address, $port) === false) {
    echo "socket_bind() failed: reason: " . socket_strerror(socket_last_error($sock)) . "\n";
}

if (socket_listen($sock, 5) === false) {
    echo "socket_listen() failed: reason: " . socket_strerror(socket_last_error($sock)) . "\n";
}

//clients array
$clients = array();

do {
    $read = array();
    $read[] = $sock;

    $read = array_merge($read, $clients);
    $write = $except = null;
    // Set up a blocking call to socket_select
    if (socket_select($read, $write, $except, $tv_sec = 5) < 1) {
        //    SocketServer::debug("Problem blocking socket_select?");
        continue;
    }

    // Handle new Connections
    if (in_array($sock, $read)) {

        if (($msgsock = socket_accept($sock)) === false) {
            echo "socket_accept() failed: reason: " . socket_strerror(socket_last_error($sock)) . "\n";
            break;
        }
        $clients[] = $msgsock;
        $key = array_keys($clients, $msgsock);
        // send instructions.
        /*$msg = "\nWelcome to the PHP Test Server.  \n" .
            "You are customer number: {$key[0]}\n" .
            "To quit, type 'quit'. To shut down the server type 'shutdown'.\n\n";
        socket_write($msgsock, $msg, strlen($msg));*/

    }

    // Handle Input
    foreach ($clients as $key => &$client) { // for each client
        if (in_array($client, $read)) {
            if (false === ($buf = socket_read($client, 2048))) {
                echo "socket_read() failed: reason: " . socket_strerror(socket_last_error($client)) . "\n";
                break 2;
            }
            if (!$buf = trim($buf)) {
                continue;
            }
            if ($buf == 'quit') {
                $talkback = "Client {$key}: you said '$buf' ".print_r($clients,true);
                $talkback.="\n";
                for($i=0;$i<10;$i++){
                    for($j=0;$j<10;$j++){
                        $talkback.=str_repeat((string)$i,102)."\n";
                    }

                }
                $talkback = str_repeat($talkback,100);
                socket_write($client, $talkback, strlen($talkback));
                socket_close($client);
                unset($clients[$key]);
                $waste[$key] = $talkback;
                break;
            }
            if ($buf == 'shutdown') {
                socket_write($client, $buf, strlen($buf));
                socket_close($client);
                unset($clients[$key]);
                $peak_memory = number_format(memory_get_peak_usage()/1024, 0, '.', ',') . " kb";
                $end_memory = number_format(memory_get_usage()/1024, 0, '.', ',') . " kb";
                echo "peak_memory : $peak_memory\nend_memory : $end_memory\n";

                break 2;
            }
            $talkback = "Client {$key}: you said '$buf'".print_r($clients,true);
            socket_write($client, $talkback, strlen($talkback));
            echo "$buf\n";
        }

    }
} while (true);

socket_close($sock);