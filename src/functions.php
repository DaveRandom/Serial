<?php declare(strict_types=1);

namespace DaveRandom\Serial;

function capture_errors(callable $callback, int &$errNo = null, string &$errMessage = null, int $types = \E_ALL | \E_STRICT)
{
    \set_error_handler(function(int $no, string $message) use(&$errNo, &$errMessage) {
        $errNo = $no;
        $errMessage = $message;
    }, $types);

    $result = $callback();

    \restore_error_handler();

    return $result;
}
