<?php

print_r($_SERVER);die;
// create a TCP/IP socket
$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
echo time();
// connect to the caching server
socket_connect($socket, '127.0.0.1', 8888);



// store a value in the cache
$key = 'bar';
$value = 'baz';
$message = "set $key $value";
socket_write($socket, $message, strlen($message));
$response = socket_read($socket, 1024);
if (trim($response) === 'STORED') {
    echo "Value '$value' stored with key '$key'\n";
} else {
    echo "Error storing value '$value' with key '$key'\n";

}
var_dump($response); echo PHP_EOL;

// get a value from the cache
//$key = 'bar';
$message = "get $key";
socket_write($socket, $message, strlen($message));
$response = socket_read($socket, 1024);
if (strpos($response, "VALUE $key") === 0) {
    $value = substr($response, strlen("VALUE $key "));
    echo "Value for key '$key': $value\n";
} else {
    echo "Key '$key' not found in cache.\n";
}
var_dump($response);echo PHP_EOL;

socket_close($socket);