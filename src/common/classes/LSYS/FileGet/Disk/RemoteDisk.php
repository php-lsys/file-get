<?php
namespace LSYS\FileGet\Disk;
use LSYS\Config;
use LSYS\FileGet\Disk;
use LSYS\Exception;
use function LSYS\FileGet\__;
use LSYS\FileGet\Utils;
class RemoteDisk extends Disk{
    use Utils;
    protected $_cache_file=[];
    public function __construct(Config $config){
        parent::__construct($config);
    }
    public function __destruct(){
        foreach ($this->_cache_file as $file){
            @unlink($file);
        }
    }
    public function download(?string $file){
        $file=$this->url($file);
        if (!$file)return $file;
        
        $timeout=$this->_config->get("timeout");
        $connect_timeout=$this->_config->get("connect_timeout");
        $dir=$this->_config->get("cache_dir",sys_get_temp_dir());
        
        $filename=$dir."/".uniqid();
        $ext=pathinfo($file, PATHINFO_EXTENSION);
        if ($ext)$filename.=".".$ext;
        
        $ch=curl_init($file);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, false);
        if ($timeout)curl_setopt($ch, CURLOPT_TIMEOUT,$timeout);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $connect_timeout);
        curl_setopt($ch, CURLOPT_USERAGENT, isset($_SERVER['HTTP_USER_AGENT'])?$_SERVER['HTTP_USER_AGENT']:'');
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_BUFFERSIZE, 8192);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        
        $fopen=@fopen($filename, "w+b");
        curl_setopt($ch, CURLOPT_WRITEFUNCTION,function($ch, $str)use($fopen){
            if(!@fwrite($fopen, $str))return false;
            return strlen($str);
        });
        if (!curl_exec($ch)){
            throw new Exception(__("curl error:[:msg]",array(":msg"=>curl_error($ch))),curl_errno($ch));
        }
        @fclose($fopen);
        $this->_cache_file[]=$filename;
        return $filename;
    }
    public function output(?string $file,?string $name=null){
        if (empty($file))return null;
        $file=$this->url($file);
        if (!$file)return $file;
        $timeout=$this->_config->get("timeout");
        $connect_timeout=$this->_config->get("connect_timeout");
        $this->_sendNameHeader($name);
        $ch=curl_init($file);
        if ($timeout)curl_setopt($ch, CURLOPT_TIMEOUT,$timeout);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, false);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $connect_timeout);
        curl_setopt($ch, CURLOPT_USERAGENT, isset($_SERVER['HTTP_USER_AGENT'])?$_SERVER['HTTP_USER_AGENT']:'');
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_BUFFERSIZE, 8192);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        $is_header=true;
        curl_setopt($ch, CURLOPT_WRITEFUNCTION,function($ch, $str)use(&$is_header){
            if ($is_header){
                if ($str=="\r\n"){$is_header=false;}
                else if(!headers_sent()) header($str);
            }else print $str;
            return strlen($str);
        });
        if(!curl_exec($ch)){
            throw new Exception(__("curl error:[:msg]",array(":msg"=>curl_error($ch))),curl_errno($ch));
        }
        return true;
    }
}