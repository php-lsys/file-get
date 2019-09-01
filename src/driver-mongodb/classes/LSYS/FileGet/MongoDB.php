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
class MongoDB implements FileGet {
    use Utils;
    protected $_mcon;
    protected $_config;
    private $_clear_file=array();
    protected $_db;
    protected $_space;
    public function __construct(Config $config,\LSYS\MongoDB $monggodb=null){
        $this->_config=$config;
        $monggodb=$monggodb?$monggodb:\LSYS\MongoDB\DI::get()->mongodb();
        $this->_db=$config->get("db");
        if (!$this->_db)$db=$monggodb->getDatabase();
        else $db=$monggodb->selectDatabase($this->_db);
        $this->_space=$config->get("space","default");
        $this->_mcon = $db->selectCollection($this->_space);
    }
    public function url($file){
        if (empty($file))return null;
        $path=$this->_config->get("url");
        return $path.$file;
    }
    public function download($file){
        if (empty($file))return null;
        list($space,$file)=$this->_split($file);
        if ($space!=$this->_space)return false;
        $id=new \MongoDB\BSON\ObjectID($file);
        $dir=$this->_config->get("cache_dir",sys_get_temp_dir());
        $filename=$dir."/".uniqid();
        $data=$this->_mcon->find([
            '_id' =>$id,
        ]);
        $data=current($data->toarray());
        if (!$data)return null;
        if (!isset($data['filedata']))return false;
        
        if (isset($data['filename'])){
            $ext=pathinfo($data['filename'], PATHINFO_EXTENSION);
            if ($ext)$filename.=".".$ext;
        }
        
        if (!@file_put_contents($filename, $data['filedata']->getdata())) return false;
        $this->_clear_file[]=$filename;
        return $filename;
    }
    public function output($file,$name=null){
        if (empty($file))return null;
        list($space,$file)=$this->_split($file);
        if ($space!=$this->_space)return false;
        $id=new \MongoDB\BSON\ObjectID($file);
        $data=$this->_mcon->find([
            '_id' =>$id,
        ]);
        $data=current($data->toarray());
        if (!$data)return null;
        if (isset($data['Content-type'])){
            $this->_sendMimeHeader($data['Content-type']);
        }
        print $data['filedata']->getdata();
        flush();
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