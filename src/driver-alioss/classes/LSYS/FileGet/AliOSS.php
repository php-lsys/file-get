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
class AliOSS implements FileGet{
    use Utils;
    protected $_config;
    protected $_bucket;
    protected $_clear_file=[];
    protected $_oss;
    public function __construct(Config $config){
        $this->_config=$config;
        $oss_access_id=$this->_config->get("oss_access_id");
        $oss_access_key=$this->_config->get("oss_access_key");
        $endpoint=$this->_config->get("oss_endpoint");
        $this->_bucket=$this->_config->get("bucket");
        $ossClient = new \OssClient($oss_access_id, $oss_access_key, $endpoint, false);
        if (!$this->_oss->doesBucketExist($this->_bucket)){
            $this->_oss->createBucket($this->_bucket, \OssClient::OSS_ACL_TYPE_PUBLIC_READ_WRITE);
        }
        $this->_oss=$ossClient;
    }
    public function url($file){
        if (empty($file))return null;
        return "http://{$this->_bucket}.oss.aliyuncs.com/{$file}";
    }
    public function download($file){
        if (empty($file))return null;
        $dir=$this->_config->get("cache_dir",sys_get_temp_dir());
        $filename=$dir."/".uniqid();
        $ext=pathinfo($file, PATHINFO_EXTENSION);
        if ($ext)$filename.=".".$ext;
        $options = array(
            \OssClient::OSS_FILE_DOWNLOAD => $filename,
        );
        $this->_oss->getObject($this->_bucket,$filename, $options);
        $this->_clear_file[]=$filename;
        return $fullname;
    }
    public function output($file,$name=null){
        if (empty($file))return null;
        $objectMeta = $this->_oss->getObjectMeta($this->_bucket,$file);
        if (isset($objectMeta['Content-type'])){
            $this->_sendMimeHeader($objectMeta['Content-type']);
        }
        $name=$name?$name:isset($objectMeta['filename'])?$objectMeta['filename']:null;
        $this->_sendHeaderName($name);
        $res=fopen('php://output','w');
        $this->_oss->getObject($this->_bucket,$file, array(
            \OssClient::OSS_FILE_DOWNLOAD => $res,
        ));
        @fclose($res);
        return true;
    }
	public function __destruct(){
		foreach ($this->_clear_file as $v) @unlink($v);
	}
}