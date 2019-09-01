<?php
use LSYS\FileGet\GridFS;
use LSYS\DI\SingletonCallback;

include_once __DIR__."/../vendor/autoload.php";
LSYS\Config\File::dirs(array(
    __DIR__."/config",
));
LSYS\FileGet\DI::set(function (){
    return (new LSYS\FileGet\DI)->fileget(new SingletonCallback(function () {
        return new GridFS(\LSYS\Config\DI::get()->config("fileget.gridfs"));
    }));
});
$id=isset($_GET['id'])?$_GET['id']:'test/5b8ca5f98198ed33a4000d99';
LSYS\FileGet\DI::get()->fileget()->output($id);
