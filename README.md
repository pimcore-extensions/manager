# Extensions Manager for [pimcore](http://www.pimcore.org/) #

Helps you manage your Pimcore Extensions with [Composer](https://github.com/composer/composer).
Browse, download and install Pimcore extensions directly from admin panel.

## Install ##
* Add to your project's `composer.json` (it's required until
[Composer](https://github.com/composer/composer) reaches stable release):
```
"minimum-stability": "dev",
"prefer-stable": true
```
* Some paths needs to be writable by web server when running composer from admin panel.
  You have two options:
 * Add this post install script to project's `composer.json`:

    ```
    "scripts": {
        "post-install-cmd": "Manager_Composer::postInstall"
    }
    ```
  * Manually set suitable permissions to `composer.json`, `composer.lock`, `vendor/composer/*`,
    `vendor/autoload.php` and `/plugins` directory
* Run in console:
```
composer.phar require pimcore-extensions/manager
```

* Enable plugin in `Extras` -> `Extensions`
* After reload, open `Extras` -> `Extensions` and you should find `Download Extensions`
  button in top bar
