<?
    chdir(dirname(__FILE__));
    
    include_once("../../../pimcore/cli/startup.php");
    
    $pidFile = PIMCORE_SYSTEM_TEMP_DIRECTORY . "/composer_update_" . $argv[1] . ".pid";

    file_put_contents($pidFile, getmypid());
    
    // change out of the webroot so that the vendors file is not created in
    // a place that will be visible to the intahwebz
    chdir(PIMCORE_DOCUMENT_ROOT);

    putenv('COMPOSER_HOME=' . PIMCORE_DOCUMENT_ROOT . '/vendor/composer/composer/bin/composer');

    //Create the commands
    $input = new Symfony\Component\Console\Input\ArrayInput(array('command' => 'update'));
    $output = new Symfony\Component\Console\Output\StreamOutput(fopen('php://output','w'));

    //Create the application and run it with the commands
    $application = new Composer\Console\Application();
    $application->setAutoExit(false);
    $application->run($input, $output);
    
    
    if(is_file($pidFile))
        unlink($pidFile);