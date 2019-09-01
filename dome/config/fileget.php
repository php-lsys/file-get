<?php
/**
 * lsys storage
 * @author     Lonely <shan.liu@msn.com>
 * @copyright  (c) 2017 Lonely <shan.liu@msn.com>
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 */

return array(
	"local_disk"=>array(//
		"url"=>"http://localhost/filemgr/lfile-get/dome/file/",
		"dir"=>dirname(__DIR__).'/file',
	),
    "remote_disk"=>array(//
        //"cache_dir"=>"./",
       // "timeout"=>'8',
       // "connect_timeout"=>'50',
        "url"=>'http://localhost/filemgr/lfile-get/dome/file/',
    ),
	 "gridfs"=>array(//
        //"db"=>'test',
        "url"=>'http://localhost/filemgr/lfile-get-mongodb/dome/read_gridfs.php?id=',
        "dir"=>"./assets/",
    ),
    "mongodb"=>array(//
      //  "db"=>'test',
      //  "space"=>'test',
        "url"=>'http://localhost/filemgr/lfile-get-mongodb/dome/read_mongodb.php?id=',
        "dir"=>"./assets/",
    ),
);