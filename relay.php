<?php

use plugins\Start\cache;

include 'plugins/autoloader.php';

if (!empty($argv[1]))
    $GLOBALS['interface']['server']['localPortListener'] = $argv[1];
if (!empty($argv[2]))
    $GLOBALS['interface']['server']['remoteAddress'] = $argv[2];
if (!empty($argv[3]))
    if ($argv[3] == 'auto-secure') $GLOBALS['interface']['server']['autoGenerateSslCerificate'] = true;
    else $GLOBALS['interface']['server']['autoGenerateSslCerificate'] = false;
else $GLOBALS['interface']['server']['autoGenerateSslCerificate'] = false;

if ($GLOBALS['interface']['server']['autoGenerateSslCerificate']) {
    $useSsl = true;
}
$newSettings = [];
if (!empty($useSsl)) {
    $newSettings['ssl_cert_file'] = $GLOBALS['interface']['server']['ssl_cert_file'];
    $newSettings['ssl_key_file'] = $GLOBALS['interface']['server']['ssl_key_file'];
    $newSettings['ssl_verify_peer'] = false;
    $newSettings['ssl_allow_self_signed'] = true;
}
foreach (cache::global()['interface']['serverSettings'] as $param => $value) $newSettings[$param] = $value;

Co::set(['hook_flags' => SWOOLE_HOOK_ALL]);
Swoole\Coroutine::set(['max_coroutine' => 20000000]);


if (!empty($useSsl)) {
    $server = new Swoole\Http\Server(
        'localhost',
        cache::global()['interface']['server']['localPortListener'],
        SWOOLE_PROCESS, SWOOLE_SOCK_TCP | SWOOLE_SSL
    );
} else {
    $server = new Swoole\Http\Server(
        'localhost',
        cache::global()['interface']['server']['localPortListener'],
        SWOOLE_BASE
    );
}

$server->set($newSettings);
$server->on('Start', '\plugins\Start\handler::init');
$server->on('Request', '\plugins\Request\router::callBack');
$server->start();