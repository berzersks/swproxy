<?php

namespace plugins\Start;

class cache
{
    public static function global(): ?array
    {
        return $GLOBALS;
    }

}