<?php

namespace plugins\Start;

use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveTreeIterator;
use Swoole\Table;

class server
{
    public static function tickOnChangeMonitor($server, int $milliseconds, Table $tableServer)
    {
        $server->tick($milliseconds, function () use ($server, $tableServer) {
            $algorithm = 'crc32';
            $Iterator = new RecursiveTreeIterator(new RecursiveDirectoryIterator(".", FilesystemIterator::SKIP_DOTS));
            foreach ($Iterator as $path) {
                $addressFile = explode('-./', $path)[1];
                $eTypeOf = explode('.', $addressFile);
                $typeOf = $eTypeOf[count($eTypeOf) - 1];
                if (in_array($typeOf, cache::global()['interface']['reloadCaseFileModify'])) {
                    if (is_file($addressFile)) {
                        $id = md5($addressFile);
                        if (empty($tableServer->get($id, 'identifier'))):
                            $tableServer->set($id, [
                                'identifier' => $id,
                                'data' => hash_file($algorithm, $addressFile)
                            ]);
                        endif;
                        $nowHash = hash_file($algorithm, $addressFile);
                        if ($nowHash !== $tableServer->get($id, 'data')) {
                            $port = $server->port;
                            exec("sudo kill -9 \$(sudo lsof -t -i:{$port})");
                        }
                    }
                }
            }
        });
    }
    public static function startServer(string $idProccess, string $code): ?array
    {
        if (strpos($code, '"') !== false) {
            return [
                'success' => false,
                'message' => 'Not allowed double quotes'
            ];
        }
        if (empty($idProccess) || empty($code)) {
            return [
                'success' => false,
                'message' => 'Missing parameters',
            ];
        }
        exec('screen -ls', $outputCommand);
        $outputCommand = implode(' ', $outputCommand);
        if (strpos($outputCommand, $idProccess) !== false) {
            exec('screen -ls', $outputCommand);
            $outputCommand = implode(' ', $outputCommand);
            $splitLines = explode(' ', $outputCommand);
            foreach ($splitLines as $s) {
                $s = trim($s);
                if (strpos($s, $idProccess) !== false) {
                    $realWorker = explode('__', $s)[0];
                    return [
                        'success' => false,
                        'color' => 'yellow',
                        'message' => "Já existe um processo com o identificador: {$idProccess}",
                    ];
                }
            }
        }
        exec(sprintf("screen -dmS \"%s\" bash -c \"%s\"", $idProccess, $code));
        return [
            'success' => true,
            'message' => 'Process started',
        ];
    }
}