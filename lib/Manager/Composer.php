<?php


class Manager_Composer
{
    public static function getComposerFile()
    {
        // first check composer one level above - as it's recommended in pimcore documentation:
        // https://www.pimcore.org/wiki/display/PIMCORE3/Extension+management+using+Composer
        $composerFile = PIMCORE_DOCUMENT_ROOT . '/../composer.json';

        if (!is_file($composerFile))
            $composerFile = PIMCORE_DOCUMENT_ROOT . '/composer.json';

        if (is_file($composerFile))
            return $composerFile;

        return false;
    }

    public static function getComposerConfiguration()
    {
        $file = self::getComposerFile();

        if ($file)
            return json_decode(file_get_contents($file), true);

        return false;
    }

    public static function writeComposerConfiguration($config)
    {
        $file = self::getComposerFile();

        if ($file && is_writable($file))
            return file_put_contents($file, json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        return false;
    }

    public static function update()
    {
        $jobId = uniqid();
        $logFile = PIMCORE_SYSTEM_TEMP_DIRECTORY . "/composer_update_" . $jobId . ".txt";
        $pidFile = PIMCORE_SYSTEM_TEMP_DIRECTORY . "/composer_update_" . $jobId . ".pid";
        
        if(is_file($logFile))
            unlink($logFile);
        
        $pid = Pimcore_Tool_Console::execInBackground(Pimcore_Tool_Console::getPhpCli() . " " . PIMCORE_PLUGINS_PATH . "/Manager/cli/composer-update.php " . $jobId, $logFile);

        file_put_contents($pidFile, $pid);
        
        return $jobId;
    }
    
    public static function getStatus($jobId)
    {
        $pidFile = PIMCORE_SYSTEM_TEMP_DIRECTORY . "/composer_update_" . $jobId . ".pid";

        if(is_file($pidFile))
        {
            return "running";
        }
        
        return "finished";
    }
    
    public static function getLogFile($jobId)
    {
        $file = PIMCORE_SYSTEM_TEMP_DIRECTORY . "/composer_update_" . $jobId . ".txt";

        if(is_file($file))
        {
            return nl2br(file_get_contents($file));
        }
        
        return null;
    }
}
