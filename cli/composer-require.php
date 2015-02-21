<?php

use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\StreamOutput;

chdir(dirname(__FILE__));

if ($argc < 3) {
    die('Wrong arguments count, should be 2, ' . ($argc - 1) . ' passed' . PHP_EOL);
}

include_once('../../../pimcore/cli/startup.php');

$pidFile = Manager_Composer::getPidFile($argv[2]);

file_put_contents($pidFile, getmypid());

// change working dir to composer.json location
chdir(dirname(Manager_Composer::getComposerFile()));

putenv('COMPOSER_HOME=' . PIMCORE_DOCUMENT_ROOT . '/vendor/composer/composer/bin/composer');
putenv('COMPOSER_NO_INTERACTION=1');

// create the command
$input = new ArrayInput([
    'command' => 'require',
    'packages' => [$argv[1]],
]);
$output = new StreamOutput(fopen('php://output', 'w'));

// create the composer application and run it with the command
$application = new Composer\Console\Application();
$application->setAutoExit(false);
$application->run($input, $output);

echo "\n \n<b>Extension " . $argv[1] . ' successfully installed.</b>';

@unlink($pidFile);
