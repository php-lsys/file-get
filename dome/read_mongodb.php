<?php
use LSYS\DI\SingletonCallback;
use LSYS\FileGet\MongoDB;

include_once __DIR__."/../vendor/autoload.php";
LSYS\Config\File::dirs(array(
    __DIR__."/config",
));
LSYS\FileGet\DI::set(function (){
    return (new LSYS\FileGet\DI)->fileget(new SingletonCallback(function () {
        return new MongoDB(\LSYS\Config\DI::get()->config("fileget.gridfs"));
    }));
});
$id=isset($_GET['id'])?$_GET['id']:'default/5b8ca1068198ed33a4000d90';
LSYS\FileGet\DI::get()->fileget()->output($id);