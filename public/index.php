<?php

require __DIR__ . '/../vendor/autoload.php';

$timer = new \ProfIT\Bbb\Timer();
$timer->run();

$process = new \ProfIT\Bbb\Process(new \Runn\Core\Config(require __DIR__ . '/../config/config.php'));
$process->run();

$timer->lock();
echo 'Total working time: ' . $timer->getTotalTime('%Hh %Im %Ss');
