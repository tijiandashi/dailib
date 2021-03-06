<?php
/**
 * Created by PhpStorm.
 * User: liujun
 * Date: 2017/11/21
 * Time: 下午7:05
 */
namespace Dai\Lib\Framework\Library;

use Phalcon\Logger;


/**
 * Class Log
 * @package Dai\Lib\Framework\Library
 * SPECIAL 9
 * CUSTOM 8
 * DEBUG 7
 * INFO 6
 * NOTICE 5
 * WARNING 4
 * ERROR 3
 * ALERT 2
 * CRITICAL 1
 * EMERGENCE 0
 * EMERGENCY 0
 */
class Log
{
    private static $_instances = [];

    /**
     * @return mixed
     */
    public static function getInstance()
    {
        $di = \Phalcon\DI::getDefault();

        /** @var \DaiBk\Models\Base\BasePageInfo $basePageInfo */
        $basePageInfo = $di->get('basePageInfo');
        $module = lcfirst($basePageInfo->module);

        if( ! isset( self::$_instances[$module] ) ) {
            $logConfig = $di->get('config')->log;
            $filePath = self::getConfig($logConfig,"filePath", "./log");
            $level = self::getConfig($logConfig,"level", 4);
            $format = self::getConfig($logConfig,"format", "[%date%][%type%] %message%");

            self::$_instances[$module] = new \Phalcon\Logger\Adapter\File(BASE_PATH.$filePath."/$module.log");
            self::$_instances[$module]->setLogLevel($level);
            $formatter = new \Phalcon\Logger\Formatter\Line($format, 'Y-m-d H:i:s');
            self::$_instances[$module]->setFormatter( $formatter );
        }
        return self::$_instances[$module];
    }

    public static function getConfig($config, $key, $default)
    {
        $value = $config->$key;
        if($value == null){
            $value = $default;
        }
        return $value;
    }

    public static function writeLog($logType, $str)
    {
        $instance = self::getInstance();
        return $instance->$logType($str);
    }

    /**
     * @param $str
     * @return bool
     */
    public static function error($str)
    {
        return self::writeLog(__FUNCTION__, $str);
    }

    /**
     * @param $str
     * @return bool
     */
    public static function warning($str)
    {
        return self::writeLog(__FUNCTION__, $str);
    }

    /**
     * @param $str
     * @return bool
     */
    public static function info($str)
    {
        return self::writeLog(__FUNCTION__, $str);
    }

    /**
     * @param $str
     * @return bool
     */
    public static function debug($str)
    {
        return self::writeLog(__FUNCTION__, $str);
    }
}