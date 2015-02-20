<?php

use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\StreamOutput;

chdir(dirname(__FILE__));

include_once('../../../pimcore/cli/startup.php');

$pidFile = Manager_Composer::getPidFile($argv[1]);

file_put_contents($pidFile, getmypid());

// change working dir to composer.json location
chdir(dirname(Manager_Composer::getComposerFile()));

putenv('COMPOSER_HOME=' . PIMCORE_DOCUMENT_ROOT . '/vendor/composer/composer/bin/composer');

// create the commands
$input = new ArrayInput(array('command' => 'update'));
$output = new StreamOutput(fopen('php://output', 'w'));

// create the composer application and run it with the commands
$application = new Composer\Console\Application();
$application->setAutoExit(false);
$application->run($input, $output);

@unlink($pidFile);
