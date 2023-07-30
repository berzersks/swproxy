<?php
include 'plugins/autoloader.php';
$cli = new \plugins\Start\console();

$script = 'php relay.php ' . $argv[1] . ' ' . $argv[2] . ' ' . $argv[3];
\plugins\Extension\terminal::scriptRunner('chmod -R 777 .', $cli);
\plugins\Extension\terminal::scriptRunner($script, $cli);
