<?php

/**
 * Class Autoloader for SanderT2001/PDOHelper.
 *
 * @author Sander Tuinstra <sandert2001@hotmail.com>
 * @link https://github.com/SanderT2001/PDOHelper.git
 *
 * This function is automatically called by PHP when a Class is called, but not included/required.
 * This function will then auto include/require this Class (autoload).
 *
 * @param string $className Containing the name of the Class to load.
 *
 * @return void
 */
spl_autoload_register(function(string $className): void
{
    if (!defined('DS')) {
        define('DS', '/');
    }

    // Replace the Class Seperator with the directory seperator, ex. `FryskeOranjekoeke\View\View` => `FryskeOranjekoeke/View/View`.
    $className = str_replace('\\', DS, $className);
    $classNameSeperated = explode(DS, $className);

    $namespace = $classNameSeperated[0];
    $directories = $classNameSeperated;
    unset($directories[0]);

    // Load class
    $classPath = dirname(__DIR__) . DS;
    $classPath = $classPath . $namespace . DS . $directories[1];
    $classPath = $classPath . '.php';

    if (is_file($classPath) === false) {
        // @NOTE Don't throw an exception, because this will prevent PHP from trying other autoloaders to load the file when this autoloader cannot.
        // throw new \InvalidArgumentException('File not found, given path is ' . $classPath);
        return;
    }
    @require_once $classPath;
});
