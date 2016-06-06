<?php

namespace Manager;

use Composer\Script\Event;
use Pimcore\Tool\Console;

class Composer
{
    /**
     * @return bool|string
     */
    public static function getComposerFile()
    {
        $composerFile = PIMCORE_DOCUMENT_ROOT . '/composer.json';

        if (is_file($composerFile))
            return $composerFile;

        return false;
    }

    /**
     * @return array|mixed
     */
    public static function getDownloaded()
    {
        $downloaded = [];

        $file = self::getComposerFile();
        if (!$file)
            return $downloaded;

        $downloaded = json_decode(file_get_contents($file), true);

        return $downloaded['require'];
    }

    /**
     * @param $package
     * @return string
     *
     * @throws \Exception
     */
    public static function installPackage($package)
    {
        $jobId = uniqid();
        $logFile = self::getLogFile();

        if (is_file($logFile))
            unlink($logFile);
            
        file_put_contents(self::getPidFile($jobId), $jobId);

        $cmd = Console::getPhpCli() . " " . realpath(PIMCORE_PATH . DIRECTORY_SEPARATOR . "cli" . DIRECTORY_SEPARATOR . "console.php"). " manager:require -p " . self::getPidFile($jobId) . " -r " . $package;
        Console::execInBackground($cmd, $logFile);
        
        return $jobId;
    }

    /**
     * @param $jobId
     * @return string
     */
    public static function getStatus($jobId)
    {
        $pidFile = self::getPidFile($jobId);

        if (is_file($pidFile)) {
            return 'running';
        }

        return 'finished';
    }

    /**
     * @param $jobId
     * @return string
     */
    public static function getPidFile($jobId)
    {
        return PIMCORE_SYSTEM_TEMP_DIRECTORY . '/composer_update_' . $jobId . '.pid';
    }

    /**
     * @return string
     */
    public static function getLogFile()
    {
        return PIMCORE_LOG_DIRECTORY . '/composer_update.log';
    }

    /**
     * @return null|string
     */
    public static function getLog()
    {
        $file = self::getLogFile();

        if (is_file($file)) {
            $content = file_get_contents($file);
            $content = preg_replace('~[[:cntrl:]]+~', "\n", $content);
            return nl2br($content, false);
        }

        return null;
    }
}
