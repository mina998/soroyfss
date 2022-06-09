<?php
/**
 * USER : Soroy
 * DATE : 2022-06-08 008
 */
class SsFtp{

    private $conn;
    private static $instance;
	/**
	 * 构造方法
	 * @param $host     : FTP服务器地址
	 * @param $port     : FTP服务器端口
	 * @param $username : FTP服务器登陆用户名
	 * @param $password : FTP服务器用户密码
	 */
    private function __construct($host, $port, $username, $password, $mode){
        // 链接FTP服务器
        $this->conn = ftp_connect($host, $port) or die("Could not connect");
        // 登陆FTP
        ftp_login($this->conn, $username, $password);
        // FTP主动模式
        ftp_pasv($this->conn, $mode);
    }
	/**
	 * 创建文件夹 无返回值
	 * @param $path : 文件夹路径
	 *
	 * @return void
	 */
    private function create_folder_path($path){
        // 判断路径是否存在 不存在循环创建
        if(! @ftp_chdir($this->conn, $path) ){
            $folders = array_filter(explode('/', $path));
            foreach($folders as $item){
                if(! @ftp_chdir($this->conn, $item)){
                    ftp_mkdir($this->conn, $item);
                    ftp_chdir($this->conn, $item);
                }
            }
        }
        @ftp_chdir($this->conn, '/');
    }
	/**
	 * FTP文件上传 成功返回 true 失败 返回 false
	 * @param $ss_file
	 * @param $wp_file
	 *
	 * @return bool
	 */
    public function upload($ss_file, $wp_file): bool {
        $path = dirname($ss_file);
        $this->create_folder_path($path);
        return ftp_put($this->conn, $ss_file, $wp_file);
    }
	/**
	 * FTP删除文件 无返回值
	 * @param $data : 文件数组 或 文件路径
	 *
	 * @return void
	 */
    public function delete($data) {
        if(is_array($data)){
			$data = array_unique($data);
	        foreach ($data as $item) {
		        @ftp_delete($this->conn, $item);
	        }
        }else{
	        @ftp_delete($this->conn, $data);
        }
    }
	/**
	 * FTP检测文件是否存在 成功返加文件大小 失败返回-1 文件夹会返回-1
	 * 有些 FTP 服务器可能不支持此特性。
	 * @param $ss_file_path : 文件路径
	 *
	 * @return bool
	 */
    public function hasFileExist($ss_file_path): bool {
        $int = ftp_size($this->conn,  $ss_file_path);
		return ! ( $int < 0 );
    }
	/**
	 * 单例模式
	 *
	 * @param $host
	 * @param $port
	 * @param $username
	 * @param $password
	 * @param $mode
	 *
	 * @return SsFtp
	 */
    public static function ftp_instance($host, $port, $username, $password, $mode): SsFtp {
        if(!self::$instance instanceof self){
            self::$instance = new self($host, $port, $username, $password, $mode);
        }
        return self::$instance;
    }
	/* 析构方法 */
    public function __destruct(){
        ftp_close($this->conn);  // 关闭 FTP 流
    }
	/* 私有的克隆方法，防止在类外 clone 对象 */
	public function __clone(){}
}