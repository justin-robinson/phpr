<?php

namespace Scoop;

/**
 * Class Environment
 * @package Scoop
 */
class Environment {

    /**
     * Environment constants
     */
    const ENV_PROD = 1;

    const ENV_STAGING = 2;

    const ENV_TEST = 3;

    const ENV_LOCAL = 4;

    /**
     * @var boolean
     * cache for is_iternal_ip()
     */
    private static $isInternalIP;

    /**
     * @var string
     * Cache for get_environment()
     */
    private static $environment;

    /**
     * @return string
     * gets integer representation of environment
     */
    public static function get_environment () {

        if ( !isset( self::$environment ) ) {
            self::detect_environment ();
        }

        return self::$environment;

    }

    /**
     * detects environment based on server subdomain
     */
    private static function detect_environment () {

        $hostnameParts = explode ( '.', self::get_server_name () );

        switch ( $hostnameParts[0] ) {
            case 'localhost':
                self::$environment = self::ENV_LOCAL;
                break;
            case 'test':
                self::$environment = self::ENV_TEST;
                break;
            case 'staging':
                self::$environment = self::ENV_STAGING;
                break;
            default:
                self::$environment = self::ENV_PROD;
                break;
        }
    }

    /**
     * @return string
     * Searches known variables for a hostname
     */
    public static function get_server_name () {

        if ( array_key_exists ( 'SERVER_NAME', $_SERVER ) && !empty( $_SERVER['SERVER_NAME'] ) ) {
            $hostname = $_SERVER['SERVER_NAME'];
        } else if ( array_key_exists ( 'HOSTNAME', $_SERVER ) && !empty( $_SERVER['HOSTNAME'] ) ) {
            $hostname = $_SERVER['HOSTNAME'];
        } else {
            $hostname = gethostname ();
        }

        return $hostname;

    }

    /**
     * @return bool
     * Determine if client is on an internal network
     */
    public static function is_internal_ip () {

        if ( is_null ( self::$isInternalIP ) ) {

            $clientIP = array_key_exists ( 'REMOTE_ADDR', $_SERVER ) ? $_SERVER['REMOTE_ADDR'] : '0.0.0.0';
            $serverIP = array_key_exists ( 'SERVER_ADDR', $_SERVER ) ? $_SERVER['SERVER_ADDR'] : '0.0.0.0';

            $octets = explode ( '.', $clientIP );

            /*
             * The Internet Assigned Numbers Authority (IANA) has reserved the following three blocks
             * of the IP address space for private internets:
             *
             * 10.0.0.0 - 10.255.255.255 (10/8 prefix)
             * 172.16.0.0 - 172.31.255.255 (172.16/12 prefix)
             * 192.168.0.0 - 192.168.255.255 (192.168/16 prefix)
             */
            $isLocalIP =
                $octets[0] === '10'
                || $octets[0] === '172' && ( $octets[1] >= '16' && $octets[1] <= '31' )
                || ( $octets[0] === '192' && $octets[1] === '168' )
                || $octets[0] === '127';
            $isOurPublicIP = $clientIP === $serverIP;

            self::$isInternalIP = ( $isLocalIP || $isOurPublicIP );
        }

        return self::$isInternalIP;

    }

    /**
     * @param $value
     * @param $equals
     *
     * @return bool
     */
    public static function constant_is_defined_and_equals ( $value, $equals = true ) {

        return defined ( $value ) && constant ( $value ) === $equals;
    }
}
