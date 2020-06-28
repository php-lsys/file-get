<?php
namespace LSYS;
interface FileGet{
    /**
     * 得到指定文件的访问URL
     * 返回:
     *  不支持返回false
     *  为空返回null
     * @param string $file
     * @return false|null|string
     */
    public function url(?string $file);
    /**
     * 下载文件到本地
     * 返回本地文件路径,默认为临时文件
     * 要永久保存请copy,请勿剪切rename
     * @param string $file
     * @return string
     */
    public function download(?string $file);
	/**
	 * 输出文件
	 * 成功输出返回true
	 * 空文件输出返回null
	 * 失败返回false
	 * @param string $file
	 * @return bool|false|null
	 */
    public function output(?string $file,?string $name=null);
}