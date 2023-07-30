<?php

namespace Utils;
class swoola
{
    public static function openPort($host, $port, $timeout = 1): bool
    {
        $fp = @fsockopen($host, $port, $errno, $errstr, $timeout);
        if ($fp) {
            fclose($fp);
            return true;
        } else {
            return false;
        }
    }
}