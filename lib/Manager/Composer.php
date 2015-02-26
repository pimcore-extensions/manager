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

    public static function getDownloaded()
    {
        $downloaded = [];

        $file = Manager_Composer::getComposerFile();
        if (!$file)
            return $downloaded;

        $downloaded = json_decode(file_get_contents($file), true);

        return $downloaded['require'];
    }

    public static function requirePackage($package)
    {
        $jobId = uniqid();
        $logFile = self::getLogFile();

        if (is_file($logFile))
            unlink($logFile);
            
        file_put_contents(self::getPidFile($jobId), $jobId);

        $cmd = Pimcore_Tool_Console::getPhpCli() . ' ';
        $cmd .= PIMCORE_PLUGINS_PATH . '/Manager/cli/composer-require.php ';
        $cmd .= $package . ' ' . $jobId;
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
            $content = file_get_contents($file);
            $content = preg_replace('~[[:cntrl:]]+~', "\n", $content);
            return nl2br($content, false);
        }

        return null;
    }

    public static function postInstall(Event $event)
    {
        echo "Setting permissions for pimcore-extensions/manager... ";

        $config = $event->getComposer()->getConfig();
        $vendorPath = $config->get('vendor-dir');
        $basePath = realpath(getcwd());
        $documentRoot = realpath($basePath . '/' . $config->get('document-root-path'));

        include_once($documentRoot . '/pimcore/cli/startup.php');

        chmod(self::getComposerFile(), 0666);

        if (!is_file($basePath . '/composer.lock'))
            touch($basePath . '/composer.lock');
        chmod($basePath . '/composer.lock', 0666);

        chmod($vendorPath, 0777);
        $iterator = new IteratorIterator(new DirectoryIterator($vendorPath . '/composer'));
        foreach($iterator as $item) {
            if ($item->isFile()) {
                chmod($item->getPathName(), 0666);
            }
        }
        chmod($vendorPath . '/autoload.php', 0666);
        chmod($documentRoot . '/plugins', 0777);
        echo "done\n";
    }
}
