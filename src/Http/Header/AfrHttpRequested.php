<?php

namespace Autoframe\Core\Http\Header;

trait AfrHttpRequested
{
    use AfrHttpHeader;

    protected function getHttpRequested(bool $bEnv = true): array
    {
        return array(
            'headers' => $this->getServerRequestHeaders(),
            'get' => $_GET,
            'post' => $_POST,
            'cookie' => $_COOKIE,
            'files' => $_FILES,
            'request' => $_REQUEST,
            'server' => $_SERVER,
            'env' => $bEnv ? $_ENV : null,
            'php_sapi_name' => php_sapi_name(),
            'body' => count($_FILES) ? '$_FILES' : ($_SERVER['REQUEST_METHOD'] == 'GET' ? 'REQUEST_METHOD is GET, so no body should be here...' : file_get_contents("php://input")),
        );
    }


}