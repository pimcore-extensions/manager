<?php


class ExtensionManager_Composer
{
    public static function getComposerFile()
    {
        $composerFile = PIMCORE_DOCUMENT_ROOT . "/composer.json";
        
        if(!is_file($composerFile))
            $composerFile = PIMCORE_DOCUMENT_ROOT . "/../composer.json";
        
        if(is_file($composerFile))
            return $composerFile;
        return false;
    }
    
    public static function getComposerConfiguration()
    {
        $file = self::getComposerFile();
        
        if($file)
            return json_decode(file_get_contents($file), true);
        
        return false;
    }
    
    public static function writeComposerConfiguration($config)
    {
        $file = self::getComposerFile();
        
        if($file)
            return file_put_contents($file, json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        
        return false;
    }
    
    public static function update()
    {
        // change out of the webroot so that the vendors file is not created in
        // a place that will be visible to the intahwebz
        chdir(PIMCORE_DOCUMENT_ROOT);
        
        //Create the commands
        $input = new Symfony\Component\Console\Input\ArrayInput(array('command' => 'update'));
        
        //Create the application and run it with the commands
        $application = new Composer\Console\Application();
        $application->setAutoExit(false);
        $application->run($input);
    }
}
