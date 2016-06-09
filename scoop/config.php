<?php

namespace Scoop;

/**
 * Class Config
 * @package Scoop
 */
class Config {

    private static $options = [ ];

    /**
     * @return array
     * Gets all possible class paths
     */
    public static function get_class_paths () {

        return [
            self::get_site_class_path (),
            self::get_shared_class_path (),
            self::get_option ( 'install_dir' ),
        ];
    }

    /**
     * @return string
     * Gets classpath for the current site
     */
    public static function get_site_class_path () {

        if ( self::option_exists ( 'server_document_root' ) ) {
            $siteClassPath = self::get_option ( 'server_document_root' );
        } else if ( self::option_exists ( 'site_name' ) ) {
            $siteClassPath = self::get_sites_folder () . DIRECTORY_SEPARATOR . self::get_option ( 'site_name' );
        } else {
            $siteClassPath = self::get_shared_class_path() . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR;
        }

        return $siteClassPath . DIRECTORY_SEPARATOR . self::get_option ( 'classpath_folder_name' ) . DIRECTORY_SEPARATOR;

    }

    /**
     * @param $name
     *
     * @return bool
     */
    public static function option_exists ( $name ) {

        return array_key_exists ( $name, self::$options );
    }

    /**
     * @param $name
     *
     * @return null
     */
    public static function get_option ( $name ) {

        return array_key_exists ( $name, self::$options ) ? self::$options[$name] : null;
    }

    /**
     * @return string
     */
    public static function get_sites_folder () {

        return Path::make_absolute ( self::get_option ( 'sites_folder' ) );

    }

    /**
     * @return string
     * Gets classpath shared by all sites
     */
    public static function get_shared_class_path () {

        if ( self::option_exists ( 'shared_classpath_parent_directory' ) ) {

            return Path::make_absolute (
                self::get_option ( 'shared_classpath_parent_directory' ) .
                self::get_option ( 'classpath_folder_name' )
            ) . DIRECTORY_SEPARATOR;
        }

        return '';

    }

    /**
     * @return array
     */
    public static function get_db_config () {

        $ScoopDB = require self::get_option ( 'install_dir' ) . DIRECTORY_SEPARATOR . 'configs' . DIRECTORY_SEPARATOR . 'db.php';

        $siteDBConfigPath = self::get_site_db_config_path();

        if ( file_exists ( $siteDBConfigPath ) ) {
            $siteDB = require $siteDBConfigPath;
            $ScoopDB = array_replace_recursive ( $ScoopDB, $siteDB );
        }

        return $ScoopDB;
    }

    /**
     * @param $siteName
     *
     * @return string
     */
    public static function get_site_class_path_by_name ( $siteName ) {

        return self::get_sites_folder () . DIRECTORY_SEPARATOR . $siteName . DIRECTORY_SEPARATOR . self::get_option ( 'classpath_folder_name' ) . DIRECTORY_SEPARATOR;
    }

    /**
     * @return string
     */
    public static function get_site_db_config_path () {

        return self::get_site_class_path () . '..' . DIRECTORY_SEPARATOR . self::get_option ( 'configpath_folder_name' ) . DIRECTORY_SEPARATOR . 'db.php';
    }

    /**
     * @param array $options
     */
    public static function set_options ( array $options ) {

        self::$options = array_merge_recursive ( self::$options, $options );
    }

    /**
     * @param $name
     * @param $option
     */
    public static function set_option ( $name, $option ) {

        self::$options[$name] = $option;
    }

    /**
     * @param $name
     */
    public static function unset_option ( $name ) {

        if ( self::option_exists($name) ) {
            unset(self::$options[$name]);
        }
    }
}
