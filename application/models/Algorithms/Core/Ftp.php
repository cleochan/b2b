<?php
/**
 * Description of Ftp
 *
 * @author Administrator
 */
class Algorithms_Core_Ftp {
    
    public $off;                          // 返回操作状态(成功/失败)  
    public $conn_id;                      // FTP连接  
    var $FTP_HOST;
    var $FTP_PORT;
    var $FTP_USER;
    var $FTP_PASS;
    function __construct($FTP_HOST,$FTP_PORT,$FTP_USER,$FTP_PASS) {
        $this->conn_id = @ftp_connect($FTP_HOST,$FTP_PORT) or die("FTP Server connect failed.");  
        @ftp_login($this->conn_id,$FTP_USER,$FTP_PASS) or die("Unable to login to the FTP server.");  
        @ftp_pasv($this->conn_id,1); // 打开被动模拟  
    }
    
    function close()  
    {  
        @ftp_close($this->conn_id);  
    }
   /** 
    * 方法：上传文件 
    * @path    -- 本地路径 
    * @newpath -- 上传路径 
    * @type    -- 若目标目录不存在则新建 
    */  
    function up_file($newpath,$path)  
    {  
        $this->off  =    @ftp_put($this->conn_id,$newpath,$path,FTP_BINARY);
        if(!$this->off) {
            return "File upload failed, please check the permissions and the path is correct.";  
        }
    }
    /** 
    * 方法：复制文件 
    * 说明：由于FTP无复制命令,本方法变通操作为：下载后再上传到新的路径 
    * @downpath -- 目标路径
    * @path    -- 原路径 
    */  
    function copy_file($downpath, $path)
    {
        $this->off  =   ftp_get($this->conn_id, $path, $downpath, FTP_BINARY);   // Download
        if(!$this->off){
            return FALSE;
        }else{
            return TRUE;
        }
    }
    
    function getNewestFile($path){
        $filelist   =   @ftp_rawlist($this->conn_id, $path);
        if($filelist){
            preg_match("/crazysales_picking([\s\S]*)/",end($filelist),$matches);
            if($matches){
                return $matches[0];
            }else{
                return FALSE;
            }
        }else{
            return  FALSE;
        }
    }
}

?>
