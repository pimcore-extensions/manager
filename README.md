# Extensions Manager for [pimcore](http://www.pimcore.org/) #

Helps you manage your Pimcore Extensions with [Composer](https://github.com/composer/composer).
Browse, download and install Pimcore extensions directly from admin panel.

## Install ##
* Add to your project's `composer.json` (it's required until [Composer](https://github.com/composer/composer) reaches stable release):
```
"minimum-stability": "dev"
```

* Run in console:
```
composer.phar require pimcore-extensions/manager
```

* Set `composer.json` and `/plugins` directory writable by web server
* Enable plugin in `Extras` -> `Extensions`
