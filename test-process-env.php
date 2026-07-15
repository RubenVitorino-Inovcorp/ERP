<?php

require 'vendor/autoload.php';
use Symfony\Component\Process\Process;

$process = Process::fromShellCommandline("/usr/bin/node -e 'console.log(process.env)'");
$process->run();
echo $process->getOutput();
