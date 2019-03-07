<?php
namespace LSYS\FileGet\Disk;
use LSYS\FileGet\Disk;
use function LSYS\FileGet\__;
use LSYS\FileGet\Utils;
class LocalDisk extends Disk{
    use Utils;
    public function download($file){
        if (empty($file))return null;
        if (!$this->_config->exist("dir"))return FALSE;
        $dir=rtrim($this->_config->get("dir"),"\\/")."/";
        $filepath=$dir.$file;
        $this->_checkDir($this->_config->exist("safe_dir",[]),$filepath);
        if (!is_file($filepath))return null;
        return $filepath;
    }
    public function output($file,$name=null){
        if (empty($file))return null;
        if (!$this->_config->exist("dir"))return FALSE;
        $dir=rtrim($this->_config->get("dir"),"\\/")."/";
        $filepath=$dir.$file;
        $this->_checkDir($this->_config->exist("safe_dir",[]),$filepath);
        if (!is_file($filepath))return FALSE;
        $size = filesize($filepath);
        if ($size==0)return null;
        if (headers_sent()){
            readfile($filepath);
            return true;
        }
        $this->_sendNameHeader($name);
        $this->_sendMimeHeader(mime_content_type($filepath));
        $this->_sendRangeHeader($size,$range);
        if ($range==0){
            readfile($filepath);
        }else{//断点续传
            $fp = fopen($filepath,'rb+');
            fseek($fp,$range);
            while(!feof($fp)) {
                print(fread($fp,1024));
                flush();
                ob_flush();
            }
            fclose($fp);
        }
        return true;
    }
}