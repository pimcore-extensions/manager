<?php

namespace Manager;

use Pimcore\API\Plugin as PimPlugin;

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
     * Init Plugin.
     */
    public function init()
    {
        parent::init();

        \Pimcore::getEventManager()->attach('system.console.init', function (\Zend_EventManager_Event $e) {
            /** @var \Pimcore\Console\Application $application */
            $application = $e->getTarget();

            // add a namespace to autoload commands from
            $application->addAutoloadNamespace('Manager\\Console', PIMCORE_PLUGINS_PATH . '/Manager/lib/Manager/Console');
        });
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
