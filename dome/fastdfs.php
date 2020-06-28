<?php
use LSYS\Config\File;
use LSYS\DI\SingletonCallback;
include_once __DIR__."/../vendor/autoload.php";
File::dirs(array(
    __DIR__."/config",
));
LSYS\FileGet\DI::set(function (){
    return (new LSYS\FileGet\DI)->fileget(new SingletonCallback(function () {
        return new LSYS\FileGet\FastDFS(\LSYS\Config\DI::get()->config("fileget.fastdfs"));
    }));
});
$fileget=\LSYS\FileGet\DI::get()->fileget();
$string="group1/M00/01/11/CqArGluM-jWAV_aMAAACGmrkxWY552.png";//文件标识,存放到你的数据库
//得到URL:
// echo $fileget->url($string);


//输出文件内容
$fileget->output($string);


//得到临时本地文件
// var_dump($fileget->download($string));



