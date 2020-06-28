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
class GridFS implements FileGet{
    use Utils;
	protected $_gridfs;
	protected $_config;
	private $_clear_file=array();
	protected $_space;
	public function __construct(Config $config,\LSYS\MongoDB $monggodb=null){
	    $this->_config=$config;
	    $monggodb=$monggodb?$monggodb:\LSYS\MongoDB\DI::get()->mongodb();
	    $this->_space=$config->get("db");
	    if (!$this->_space){
	        $db=$monggodb->getDatabase();
	        $this->_space=strval($db);
	    }else $db=$monggodb->selectDatabase($this->_space);
	    $this->_gridfs = $db->selectGridFSBucket();
	}
	public function url(?string $file){
	    if (empty($file))return null;
	    $path=$this->_config->get("url");
	    return $path.$file;
	}
	public function download(?string $file){
	    if (empty($file))return null;
	    list($space,$file)=$this->_split($file);
	    if ($space!=$this->_space)return false;
        $id=new \MongoDB\BSON\ObjectID($file);
        $dir=$this->_config->get("cache_dir",sys_get_temp_dir());
        $filename=$dir."/".uniqid();
        
        $stream=$this->_gridfs->openDownloadStream($id);
        $meta=$this->_gridfs->getFileDocumentForStream($stream);
        if (isset($meta['filename'])){
            $ext=pathinfo($meta['filename'], PATHINFO_EXTENSION);
            if ($ext)$filename.=".".$ext;
        }
        
        $res=fopen($filename, "w+b");
	    $this->_gridfs->downloadToStream($id, $res);
	    fclose($res);
	    $this->_clear_file[]=$filename;
	    return $filename;
	}
	public function output(?string $file,?string $name=null){
	    if (empty($file))return null;
	    list($space,$file)=$this->_split($file);
	    if ($space!=$this->_space)return false;
	    $id=new \MongoDB\BSON\ObjectID($file);
	    $stream=$this->_gridfs->openDownloadStream($id);
	    
	    $meta=$this->_gridfs->getFileDocumentForStream($stream);
	    $name=$name?$name:isset($meta['filename'])?$meta['filename']:null;
	    $this->_sendNameHeader($name);
	    
	    $finfo = finfo_open(FILEINFO_MIME);
	    $str=stream_get_contents($stream,30);
	    $mimetype = finfo_buffer($finfo, $str);
	    finfo_close($finfo);
	    $mimetype=strstr($mimetype, ";",true);
	    if ($mimetype)$this->_sendMimeHeader($mimetype);
	    
	    $res=fopen('php://output','w');
	    fwrite($res, $str);
	    stream_copy_to_stream($stream, $res);
	    fclose($res);
	    return true;
	}
	public function __destruct(){
	    foreach ($this->_clear_file as $v) @unlink($v);
	}
	protected function _split($file){
	    $p=strpos($file,"/");
	    if ($p===false)return array('',$file);
	    else return array(substr($file, 0,$p),substr($file, $p+1));
	}
}