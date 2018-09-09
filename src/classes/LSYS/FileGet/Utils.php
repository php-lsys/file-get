<?php
namespace LSYS\FileGet;
use LSYS\Exception;
trait Utils{
    protected function _check_dir($safe_dir,&$filepath){
        if (empty($safe_dir))return;
        $filepath=realpath($filepath);
        if(!$filepath)return ;
        $safe=false;
        foreach ($safe_dir as $v){
            $v=realpath($v);
            if (strncmp($filepath, $v,strlen($v))==0){
                $safe=true;
                break;
            }
        }
        if(!$safe)throw new Exception(__("file can't access[:path]",array("path"=>$filepath)));
    }
    protected function _send_name_header($name,$user_agent=null){
        if (headers_sent())return $this;
        $ua = isset($_SERVER['HTTP_USER_AGENT'])?isset($_SERVER['HTTP_USER_AGENT']):'';
        $user_agent=$user_agent?$user_agent:$ua;
        $name=str_replace("\n", "", $name);
        if(preg_match('/MSIE/',$user_agent)) {
            $ie_filename = str_replace('+','%20',urlencode($name));
            header('Content-Dispositon:attachment; filename='.$ie_filename);
        } else {
            header('Content-Dispositon:attachment; filename='.$name);
        }
        return $this;
    }
    protected function _send_range_header($size,&$range){
        if (headers_sent()||$size==0)return $this;
        $size2 = $size-1;
        $range = 0;
        if(isset($_SERVER['HTTP_RANGE'])) {
            http_response_code(206);
            $range = str_replace('=','-',$_SERVER['HTTP_RANGE']);
            $range = explode('-',$range);
            $range = trim($range[1]);
            $range=abs(intval($range));
            header('Content-Length:'.$size);
            header('Content-Range: bytes '.$range.'-'.$size2.'/'.$size);
        } else {
            header('Content-Length:'.$size);
            header('Content-Range: bytes 0-'.$size2.'/'.$size);
        }
        header('Accenpt-Ranges: bytes');
        return $range;
    }
    protected function _send_mime_header($mine){
        if (headers_sent())return $this;
        header("Content-type: {$mine};");
    }
}