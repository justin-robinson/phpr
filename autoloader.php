<?php

require_once $_SERVER['R_DOCUMENT_ROOT'] . '/phpr/config.php';
require_once $_SERVER['R_DOCUMENT_ROOT'] . '/phpr/path.php';

/**
 * @param $className
 */
function r_autoloader ( $className ) {

    // class names can be uppercase but files are lower case
    // namespace can have \, but we need / for the file path
    $relativeFilePath = '/'
        . str_replace ( '\\', '/', strtolower ( $className ) )
        . '.php';

    // looks in
    // 1. site classpath
    // 2. shared site classpath
    // 3. phpr classpath
    foreach ( \phpr\Config::get_class_paths () as $classPath ) {
        $filepath = $classPath . $relativeFilePath;

        if ( file_exists ( $filepath ) ) {
            include_once ( $filepath );
            break;
        }
    }
}

$composerAutoloaderPath = $_SERVER['R_DOCUMENT_ROOT'] . '/vendor/autoload.php';
if ( file_exists ( $composerAutoloaderPath ) ) {
    require_once $composerAutoloaderPath;
}

spl_autoload_register ( 'r_autoloader', true, true );

