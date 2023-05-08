<?php
/*
This server listens on port 8888 for incoming connections and implements a simple caching system.
Clients can send get, set, and delete commands to retrieve, store, and delete values from 
the cache, respectively. When a client sends a get command with a key, the server checks 
if the key is present in the cache and sends back the corresponding value. If the key is 
not present, an empty value is returned. When a client sends a set command with a key and a value,
 the server stores the value in the cache. When a client sends a delete command with a key, 
 the server removes the key and its corresponding value from the cache.
*/


// create a TCP/IP socket
$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);

// bind the socket to an address and port
socket_bind($socket, '127.0.0.1', 8888) or die('Binding failed! Check the server if it is up and running!');

// start listening for incoming connections
socket_listen($socket);
$tstart = time();
echo "Memory caching server started.\n".$tstart."\r\n";
ob_flush();
// initialize the cache
$cache = [];

// loop continuously to handle incoming connections
set_time_limit(30);
while (true) {
    // accept a new connection
    $clientSocket = socket_accept($socket);

    // read data from the client
    $data = socket_read($clientSocket, 1024);

    // process the client's request
    $tokens = explode(' ', $data);
    $command = $tokens[0];
    $key = $tokens[1];

    if ($command === 'get') {
        $value = $cache[$key] ?? '';
        $response = "VALUE $key $value\r\n";
    } elseif ($command === 'set') {
        $value = $tokens[2];
        $cache[$key] = $value;
        $response = "STORED\r\n$data\r\n";
    } elseif ($command === 'delete') {
        unset($cache[$key]);
        $response = "DELETED\r\n";
    } else {
        $response = "ERROR\r\n";
    }

    // send the response back to the client
    socket_write($clientSocket, $response, strlen($response));
    // close the client connection
    socket_close($clientSocket);
    echo time()-$tstart."\r\n";
    ob_flush();
    usleep(1);
}
socket_close($socket);
