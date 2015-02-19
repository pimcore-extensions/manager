<?php

class Manager_Plugin extends Pimcore_API_Plugin_Abstract implements Pimcore_API_Plugin_Interface
{
    public static function isInstalled()
    {
        return true;
    }

    public static function install()
    {
    }

    public static function uninstall()
    {
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
     * @return string path to the translation file relative to plugin directory
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
