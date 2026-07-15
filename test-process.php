<?php

require 'vendor/autoload.php';
use Symfony\Component\Process\Process;

$process = Process::fromShellCommandline("PATH=\$PATH:/usr/local/bin:/opt/homebrew/bin NODE_PATH=\"/home/owy/Coding/Estagio/erp-inovcorp/node_modules\" \"/usr/bin/node\" '/home/owy/Coding/Estagio/erp-inovcorp/vendor/spatie/browsershot/src/../bin/browser.cjs' '{\"url\":\"file:///tmp/dummy.html\",\"action\":\"pdf\",\"options\":{\"args\":[],\"viewport\":{\"width\":800,\"height\":600},\"displayHeaderFooter\":false,\"executablePath\":\"/home/owy/Coding/Estagio/erp-inovcorp/.cache/puppeteer/chrome/linux-150.0.7871.24/chrome-linux64/chrome\"}}'");
$process->run();
echo $process->getErrorOutput();
echo $process->getOutput();
