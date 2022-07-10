<?php

//namespace Autoframe\Core\String;

use Autoframe\Core\String\AfrStr;

/**
 * @param string|array $saData
 * @param string $sEncoding
 * @return array|string
 */
function h($saData, string $sEncoding = '')
{
    return AfrStr::h($saData, $sEncoding);
}

/**
 * @param $mixed
 */
function prea($mixed, $print = true)
{
    AfrStr::prea($mixed, $print);
}

function extract_between(string $str, string $start_char, string $end_char = NULL): array
{
    return AfrStr::extractBetween($str, $start_char, $end_char);
}
