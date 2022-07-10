<?php


namespace Autoframe\Core\Session;


interface AfrSessionInterface
{

    function session_abort();

    function session_cache_expire();

    function session_cache_limiter();

    function session_commit();

    function session_create_id(string $prefix);

    function session_decode(string $data);

    function session_destroy();

    function session_encode();

    function session_gc();

    function session_get_cookie_params();

    function session_id();

    function session_module_name();

    function session_name();

    function session_regenerate_id();

    function session_register_shutdown();

    function session_reset();

    function session_save_path();

    function session_set_cookie_params($lifetime_or_options);

    function session_set_save_handler(
        $sessionhandler_or_open,
        $register_shutdown_or_close,
        $read,
        $write,
        $destroy,
        $gc,
        $create_sid,
        $validate_sid,
        $update_timestamp
    );

    function session_start(array $options = []);

    function sessionConfigAfr();

    function session_status();

    function session_unset();

    function session_write_close();
}
