<?php
namespace LSYS\FileGet;
use LSYS\FileGet;
use LSYS\Config;
abstract class Disk implements FileGet{
    protected $_config;
    public function __construct(Config $config){
        $this->_config=$config;
	}
	public function url($file){
	    if (empty($file))return null;
	    if (!$this->_config->exist("url"))return false;
	    $base_url=rtrim($this->_config->get("url"),"\//")."/";
	    return $base_url.$file;
	}
}