<?php

namespace Manager;

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
     * @throws \Exception
     */
    public static function installPackage($package)
    {
        $logFile = self::getLogFile();
        $jobId = uniqid();
        $pidFile = self::getPidFile($jobId);

        $console = implode(DIRECTORY_SEPARATOR, [
            PIMCORE_PATH,
            'cli',
            'console.php'
        ]);
        $cmd = implode(' ', [
            Console::getPhpCli(),
            $console,
            'manager:require',
            '-p ' . $pidFile,
            '-r ' . $package
        ]);

        if (is_file($logFile))
            unlink($logFile);
        file_put_contents($pidFile, $jobId);

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
            return nl2br($content);
        }

        return null;
    }
}
