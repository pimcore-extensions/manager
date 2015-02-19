<?php


class Manager_Plugin  extends Pimcore_API_Plugin_Abstract implements Pimcore_API_Plugin_Interface {

	protected static $installedFileName = "/var/config/.manager";

    public static function isInstalled()
    {
        return file_exists(PIMCORE_WEBSITE_PATH . self::$installedFileName);
    }
    
    public function preDispatch($e)
    {
        include_once(PIMCORE_PLUGINS_PATH . '/Manager/vendor/autoload.php');
    }

    public static function install()
    {
        touch(PIMCORE_WEBSITE_PATH . self::$installedFileName);
    }
    
    public static function uninstall()
    {
        unlink(PIMCORE_WEBSITE_PATH . self::$installedFileName);
    }

    /**
     * @return string
     */
    public static function getTranslationFileDirectory()
    {
        return PIMCORE_PLUGINS_PATH . '/Manager/static/texts';
    }

    /**
     * @param string $language
     * @return string path to the translation file relative to plugin direcory
     */
    public static function getTranslationFile($language)
    {
        if (is_file(self::getTranslationFileDirectory() . "/$language.csv")) {
            return "/Manager/static/texts/$language.csv";
        } else {
            return '/Manager/static/texts/en.csv';
        }
    }
}
