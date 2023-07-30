<?php

namespace plugins\Start;

use plugins\Start\tableServer as TableServer;
class handler
{
    public static function init($server)
    {
        \plugins\Start\server::tickOnChangeMonitor($server, 1000, new tableServer());
        if (cache::global()['interface']['server']['autoGenerateSslCerificate']) $prefix = 'https://';
        else $prefix = 'http://';
        print (new \plugins\Start\console())->color(sprintf("O servidor está sendo executado no endereço => {$prefix}%s:%s%s", $server->host, $server->port, PHP_EOL), 'yellow');
    }
}