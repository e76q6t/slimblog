<?php

define('ROOT_PATH', realpath(__DIR__ ));
define('APP_PATH', ROOT_PATH . '/app');
define('PUBLIC_PATH', ROOT_PATH . '/public');
define('VENDOR_PATH', ROOT_PATH . '/vendor');

require(VENDOR_PATH . '/autoload.php');

if (debug_backtrace()) {
    throw new \Exception('コマンドラインから実行してね');
}

if (count($argv) < 2) {
    echo "usage: php command.php <command>";
    exit;
}

unset($argv[0]);

$command = array_shift($argv);
if ($pos = strpos($command, ':') !== false) {
    list($command, $func) = explode(':', $command);
} else {
    $func = 'run';
}

$class = 'app\commands\\' . ucfirst($command);
$class::$func($argv);
