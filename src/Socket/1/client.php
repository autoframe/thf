<?php
// create a TCP/IP socket
$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);

// connect to the socket server
socket_connect($socket, '127.0.0.1', 8888);

// send a message to the server
$message = "Hello, server!";
socket_write($socket, $message, strlen($message));

// read the response from the server
$response = socket_read($socket, 1024);

// print the response
echo $response;

// close the socket connection
socket_close($socket);