<?php
/**
 * Created by PhpStorm.
 * User: krustnic
 * Date: 04.02.14
 * Time: 16:24
 */

function DocxMergeAutoload($classname)
{
    $filename = dirname(__FILE__).DIRECTORY_SEPARATOR.$classname.'.php';
    if (is_readable($filename)) {
        require $filename;
    }
}

if (version_compare(PHP_VERSION, '5.1.2', '>=')) {
    //SPL autoloading was introduced in PHP 5.1.2
    if (version_compare(PHP_VERSION, '5.3.0', '>=')) {
        spl_autoload_register('DocxMergeAutoload', true, true);
    } else {
        spl_autoload_register('DocxMergeAutoload');
    }
} else {
    /**
     * Fall back to traditional autoload for old PHP versions
     * @param string $classname The name of the class to load
     */
    function __autoload($classname)
    {
        PHPMailerAutoload($classname);
    }
}