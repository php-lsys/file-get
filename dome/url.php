<?php
use LSYS\FileGet\GridFS;
use LSYS\DI\SingletonCallback;
use LSYS\FileGet\MongoDB;

include_once __DIR__."/../vendor/autoload.php";
LSYS\Config\File::dirs(array(
    __DIR__."/config",
));
LSYS\FileGet\DI::set(function (){
    return (new LSYS\FileGet\DI)->fileget(new SingletonCallback(function () {
        //return new GridFS(\LSYS\Config\DI::get()->config("fileget.gridfs"));
        return new MongoDB(\LSYS\Config\DI::get()->config("fileget.gridfs"));
    }));
});

var_dump(LSYS\FileGet\DI::get()->fileget()->download("default/5b8cacc48198ed33a4000da0"));
//var_dump(LSYS\FileGet\DI::get()->fileget()->download("test/5b8caf2e8198ed33a4000da7"));
    
//var_dump(LSYS\FileGet\DI::get()->fileget()->url("test/5b8ca0b38198ed33a4000d8d"));
    