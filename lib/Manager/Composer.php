<?php

use Composer\Script\Event;

class Manager_Composer
{
    public static function getComposerFile()
    {
        // first check composer one level above - as it's recommended in pimcore documentation:
        // https://www.pimcore.org/wiki/display/PIMCORE3/Extension+management+using+Composer
        $composerFile = realpath(PIMCORE_DOCUMENT_ROOT . '/../composer.json');

        if (!$composerFile || !is_file($composerFile))
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
            return file_put_contents($file,
                json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        return false;
    }

    public static function update()
    {
        $jobId = uniqid();
        $logFile = self::getLogFile();

        if (is_file($logFile))
            unlink($logFile);

        $cmd = Pimcore_Tool_Console::getPhpCli() . ' ';
        $cmd .= PIMCORE_PLUGINS_PATH . '/Manager/cli/composer-update.php ' . $jobId;
        Pimcore_Tool_Console::execInBackground($cmd, $logFile);

        return $jobId;
    }

    public static function getStatus($jobId)
    {
        $pidFile = self::getPidFile($jobId);

        if (is_file($pidFile)) {
            return 'running';
        }

        return 'finished';
    }

    public static function getPidFile($jobId)
    {
        return PIMCORE_SYSTEM_TEMP_DIRECTORY . '/composer_update_' . $jobId . '.pid';
    }

    public static function getLogFile()
    {
        return PIMCORE_LOG_DIRECTORY . '/composer_update.log';
    }

    public static function getLog()
    {
        $file = self::getLogFile();

        if (is_file($file)) {
            return nl2br(file_get_contents($file));
        }

        return null;
    }

    public static function postInstall(Event $event)
    {
        echo "Setting permissions for pimcore-extensions/manager... ";
        $vendorPath = $event->getComposer()->getConfig()->get('vendor-dir');
        $basePath = dirname($vendorPath);

        include_once($basePath . '/pimcore/cli/startup.php');

        chmod(self::getComposerFile(), 0666);
        if (!is_file($basePath . '/composer.lock'))
            @touch($basePath . '/composer.lock');
        chmod($basePath . '/composer.lock', 0666);

        $iterator = new IteratorIterator(new DirectoryIterator($vendorPath . '/composer'));
        foreach($iterator as $item) {
            if ($item->isFile()) {
                chmod($item->getPathName(), 0666);
            }
        }
        chmod($vendorPath . '/autoload.php', 0666);
        chmod($basePath . '/plugins', 0777);
        echo "done\n";
    }
}
