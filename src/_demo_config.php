<?php

/*
set_exception_handler(function(){});
set_exception_handler(?callable $callback): string|array|object|null
set_error_handler(?callable $callback, int $error_levels = E_ALL): string|array|object|null
*/


define('AFR_SESSION_CLASS_DEFAULT_PROFILE','afr&nocache&cookie&subdomainsSession&iMinutes=302400&samesite=strict');
define('AFR_SESSION_CLASS_DEFAULT_PROFILES_ARR',[AFR_SESSION_CLASS_DEFAULT_PROFILE=>['name' => 'AFRSSIDZ',]]);
define('AFR_SESSION_CLASS_DEFAULT_CAPTCHA_PROFILE','captcha');
define('AFR_SESSION_CLASS_DEFAULT_CAPTCHA_PROFILES_ARR', [AFR_SESSION_CLASS_DEFAULT_CAPTCHA_PROFILE => ['name' => 'AFRSSIDY',]]);