<?php

namespace plugins\Extension;

class terminal
{
    public static function scriptRunner($command, $cli): void
    {
        $descriptorspec = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];
        $process = proc_open($command, $descriptorspec, $pipes);
        if (is_resource($process)) {
            fclose($pipes[0]);
            $outputPipes = [$pipes[1], $pipes[2]];
            while (count($outputPipes) > 0) {
                $readyPipes = $outputPipes;
                $null = null;
                if (stream_select($readyPipes, $null, $null, null) === false) {
                    break;
                }
                foreach ($readyPipes as $pipe) {
                    $data = fgets($pipe);
                    if ($data === false) {
                        fclose($pipe);
                        $outputPipes = array_diff($outputPipes, [$pipe]);
                    } else {
                        if (stripos($data, 'killed') !== false) {
                            self::scriptRunner('clear', $cli);
                            $message = sprintf("server restarted at %s%s", date('d/m/Y H:i:s'), PHP_EOL);
                            $messageGreen = sprintf("O servidor foi reiniciado devido a uma modifição de um arquivo!%s%s", PHP_EOL, PHP_EOL);
                            print $cli->color($message, 'light_blue');
                            print $cli->color($messageGreen, 'light_green');
                            self::scriptRunner($command, $cli);
                        } else {
                            echo $data;
                        }
                    }
                }
            }
            proc_close($process);
        }
    }
}