<?php
/**
 * lsys storage
 * @author     Lonely <shan.liu@msn.com>
 * @copyright  (c) 2017 Lonely <shan.liu@msn.com>
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 */
namespace LSYS\FileGet;
use LSYS\FileGet;
use LSYS\Config;
use LSYS\Exception;
class FastDFS implements FileGet{
    use Utils;
    protected $_config;
    protected $_server;
    private $_clear_file=array();
    public function __construct(Config $config){
        $this->_config=$config;
    }
    public function url(?string $file){
        if (empty($file))return null;
        $path=$this->_config->get("url");
        return $path.$file;
    }
    public function download(?string $file){
        if (empty($file))return null;
        
        list($group,$file)=$this->_split($file);
        
        $dir=$this->_config->get("cache_dir",sys_get_temp_dir());
        $filename=$dir."/".uniqid();
        $ext=pathinfo($file, PATHINFO_EXTENSION);
        if ($ext)$filename.=".".$ext;
        
        $fastdfs=\LSYS\FastDFS\DI::get()->fastdfs();
        $this->_server=$fastdfs->connect_server_from_tracker();
        $fileinfo=$fastdfs->storage_download_file_to_file($group,$file, $filename);
        if (!$fileinfo){
            throw new Exception($fastdfs->get_last_error_info(),$fastdfs->get_last_error_no());
        }
        
        $this->_clear_file[]=$filename;
        return $filename;
    }
    public function output(?string $file,?string $name=null){
        if (empty($file))return null;
        
        list($group,$file)=$this->_split($file);
        $fastdfs=\LSYS\FastDFS\DI::get()->fastdfs();
        $this->_server=$fastdfs->connect_server_from_tracker();
        $meta_list = $fastdfs->storage_get_metadata($group,$file);
        $data=$fastdfs->storage_download_file_to_buff($group,$file);
        if($data===false){
            throw new Exception($fastdfs->get_last_error_info(),$fastdfs->get_last_error_no());
        }
        if (isset($meta_list['Content-type'])){
            $this->_sendMimeHeader($meta_list['Content-type']);
        }
        $name=$name?$name:isset($meta_list['filename'])?$meta_list['filename']:null;
        $this->_sendNameHeader($name);
        print $data;
        return true;
    }
    public function __destruct(){
        $this->_server&&\LSYS\FastDFS\DI::get()->fastdfs()->disconnect_server($this->_server);
        foreach ($this->_clear_file as $v) @unlink($v);
    }
	protected function _split($file){
	    $p=strpos($file,"/");
	    if ($p===false)return array('',$file);
	    else return array(substr($file, 0,$p),substr($file, $p+1));
	}
}