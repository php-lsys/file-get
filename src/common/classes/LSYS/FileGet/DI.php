<?php
namespace LSYS\FileGet;
/**
 * @method \LSYS\FileGet fileGet($config=null)
 */
class DI extends \LSYS\DI{
    public static $config = 'fileget.local_disk';
    /**
     * @return static
     */
    public static function get(){
        $di=parent::get();
        !isset($di->fileGet)&&$di->fileGet(new \LSYS\DI\ShareCallback(function($config=null){
            return $config?$config:self::$config;
        },function($config=null){
            $config=\LSYS\Config\DI::get()->config($config?$config:self::$config);
            return new \LSYS\FileGet\Disk\LocalDisk($config);
        }));
        return $di;
    }
}