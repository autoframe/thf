<?php
// create a TCP/IP socket
$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);

// bind the socket to an address and port
socket_bind($socket, '127.0.0.1', 8888);

// start listening for incoming connections
socket_listen($socket);

echo "Socket server started.\n";

// loop continuously to handle incoming connections
while (true) {
    // accept a new connection
    $clientSocket = socket_accept($socket);

    // read data from the client
    $data = socket_read($clientSocket, 1024);

    // process the client's request
    $response = "Hello, client! You said: " . $data;

    // send the response back to the client
    socket_write($clientSocket, $response, strlen($response));

    // close the client connection
    socket_close($clientSocket);
}