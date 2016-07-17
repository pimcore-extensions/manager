# Extensions Manager for [pimcore](http://www.pimcore.org/) #

Manage your Pimcore Extensions with [Composer][1] directly from admin panel.

## Important notes
Using composer CLI from pimcore's admin panel is a bit tricky.
The most important thing is that your web server needs write permissions
to many paths (see [required write permissions](#required-write-permissions)
section for details).  
**It's a potential security issue, so be warned and use with caution.**

Manager assumes that you are using pimcore's ```composer.json``` for
dependency management (not one level above document-root as suggested in
[pimcore extensions documentation][2])

```composer``` binary needs to be in ```$PATH``` and be executable.

## Install

    composer require pimcore-extensions/manager

* Enable plugin in **Extensions** tab in admin panel
* After reload, open **Extensions** tab and you should find
**Download Extensions** button in top bar.
* Make sure that [all required paths are **writable**](#required-write-permissions)
 by your web server.  
 There is a CLI command to help you with that. Run it before installing any package by manager:  
 ```php pimcore/cli/console.php manager:permissions```

## Required write permissions
* /plugins/*
* /composer.json
* /composer.lock
* /vendor/*

# CHANGELOG

* **0.3.1** (2016-07-17)
    * fix reload after package install
* **0.3.0** (2016-07-17)
    * sort available packages (by name, description, downloads, stars)
    * added search/filter field
    * added pagination
* **0.2.0** (2016-07-17)
    * Compatibility with pimcore 4.1.3
    * CLI command to set required permissions
    * Improved error handling

[1]: https://github.com/composer/composer
[2]: https://www.pimcore.org/wiki/display/PIMCORE4/Extension+management+using+Composer
