<?php


class ExtensionManager_Plugin  extends Pimcore_API_Plugin_Abstract implements Pimcore_API_Plugin_Interface {

	protected static $installedFileName = "/var/config/.extensionManager";

    public static function isInstalled()
    {
        return file_exists(PIMCORE_WEBSITE_PATH . self::$installedFileName);
    }
    
    public function preDispatch($e)
    {
        include_once(PIMCORE_PLUGINS_PATH . '/ExtensionManager/vendor/autoload.php');
    }

    public static function install()
    {
        touch(PIMCORE_WEBSITE_PATH . self::$installedFileName);
    }
    
    public static function uninstall()
    {
        unlink(PIMCORE_WEBSITE_PATH . self::$installedFileName);
    }

}
