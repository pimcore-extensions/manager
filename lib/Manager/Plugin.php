<?php

namespace Manager;

use Pimcore\Api\Plugin as PimPlugin;

class Plugin extends PimPlugin\AbstractPlugin implements PimPlugin\PluginInterface
{
    /**
     * @return bool
     */
    public static function isInstalled()
    {
        return true;
    }

    /**
     * Install routine
     */
    public static function install()
    {
    }

    /**
     * Uninstall routine
     */
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
