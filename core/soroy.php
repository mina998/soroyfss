<?php
/**
 * USER : Soroy
 * DATE : 2022-06-08 008
 */
class Soroy{
	// 操作消息
	protected $message = false;
	// FTP 主机地址
	protected $ftp_host;
	// FTP 主机绑定域名 文件访问域名
	protected $ftp_domain;
	// FTP 用户名
	protected $ftp_username;
	// FTP 用户密码
	protected $ftp_password;
	// FTP 上传子目录
	protected $fss_path      = '';
	// FTP 端口
	protected $ftp_port      = 21;
	// FTP 模式
	protected $ftp_mode      = 0;
	// 上传文件是否自动重命名 1=重命名
	protected $renames_allow = 0;
	// 是否删除本地文件 1 为删除
	protected $disable_local = 1;
	// 插件参数保存在数据库中的字段名
	protected $option_name = 'soroy_fss_plugin';
	// 插件文件
	protected $plugin_file;
	/**
	 * 构造方法
	 * @param $file : 插件文件 soroyfss/soroyfss.php
	 */
	public function __construct($file){
		$this->plugin_file =  $file;
		$options = get_option($this->option_name);
		if($options){
			$this->_add_property($options);
		}
	}
	/**
	 * 根据文件生成路径 返回数组
	 * [ss_full:远程完整路径 sub_dir:子路径 wp_full:本地完整路径 wp_base:本地基础路径]
	 *
	 * @param string $file_path : 文件路径
	 * @return array
	 */
    protected function build_path(string $file_path=''): array {
	    /**
	     * wp_get_upload_dir() -> fields
	     *   [path] => /usr/local/lsws/wp.iosss.cc/wordpress/wp-content/uploads/2022/06
	     *   [url] => http://img.iosss.cc/web/2022/06
	     *   [subdir] => /2022/06
	     *   [basedir] => /usr/local/lsws/wp.iosss.cc/wordpress/wp-content/uploads
	     *   [baseurl] => http://img.iosss.cc/web
	     *   [error] =>
	     */
	    $dirs = wp_get_upload_dir();
		$path=array('wp_base' => $dirs['basedir'] . '/');
		if ($file_path == '' && strpos($file_path, '/') === false){
			$path['ss_full'] = str_replace($this->ftp_domain, '', $dirs['url']) . '/';
			$path['wp_full'] = $dirs['path'] . '/';
			$path['sub_dir'] = $dirs['subdir'] . '/';
			return $path;
		}
		if (strpos($file_path, $dirs['basedir']) === 0){
			$file_path = str_replace($dirs['basedir'].'/', '', $file_path);
		}
		$path['sub_dir'] = '/' . dirname($file_path) . '/';
		$path['wp_full'] = $dirs['basedir'] . $path['sub_dir'];
		$path['ss_full'] = $this->fss_path . $path['sub_dir'];
        return $path;
    }
	/**
	 * 如果数组中的key 是类的属性 取出并为类属性赋值 并返回所有赋值属性
	 * @param array $data : 数组
	 *
	 * @return array
	 */
    protected function _add_property(array $data): array {
        $options = [];
        foreach($data as $key =>$item){
            if ( !property_exists(__CLASS__, $key) ) continue;
            $options[$key] = sanitize_text_field(trim(stripslashes($item)));
            $this->$key = $item;
        }
        return $options;
    }
	/**
	 * 加载设置页面HTML
	 * @return void
	 */
	public function view(){
		if ( isset($_POST['_wpnonce']) ){
			$options = $this->_add_property($_POST);
			if(update_option($this->option_name, $options) ) {
				$this->message = '已保存设置';
			}else{
				$this->message = '请不要重复提交';
			}
			update_option('upload_url_path', $options['ftp_domain'] . $options['fss_path']);
		}elseif(isset($_POST['content_replace'])){
			$temp = str_replace(ABSPATH, '', wp_get_upload_dir()['basedir']);
			$original_content = home_url($temp); //wpordpress url
			$new_content  = $this->fss_path ? $this->ftp_domain . $this->fss_path : $this->ftp_domain;
			if($_POST['content_replace'] == 'fss' ){
				$this->content_replace($original_content, $new_content);
				update_option('upload_url_path', $this->ftp_domain . $this->fss_path);
			}
			if ($_POST['content_replace'] == 'wp'){
				$this->content_replace($new_content, $original_content);
				update_option('upload_url_path', '');
			}
			$this->message = '替换完成';
		}
		require_once plugin_dir_path(__FILE__) . 'view.php';
	}
	/**
	 * 内容替换
	 * @param $new_content : fss url
	 * @param string $original_content : wp url
	 *
	 * @return void
	 */
	protected function content_replace( $new_content, string $original_content ): void {
		global $wpdb;
		$sql = 'UPDATE ' . $wpdb->prefix . "posts SET `post_content` = REPLACE( `post_content`, '" . $new_content . "', '" . $original_content . "');";
		$wpdb->query( $sql );
		$sql = 'UPDATE ' . $wpdb->prefix . "posts SET `guid` = REPLACE( `guid`, '" . $new_content . "', '" . $original_content . "') WHERE `post_type` = 'attachment';";
		$wpdb->query( $sql );
	}
	/**
	 * @return SsFtp
	 */
    protected function ftp(): SsFtp {
        return SsFtp::ftp_instance($this->ftp_host, $this->ftp_port, $this->ftp_username, $this->ftp_password, $this->ftp_mode);
    }
	/**
	 * 自动加载类
	 * @param $class
	 *
	 * @return void
	 */
    public static function loader($class){
        $class = SOROY_PLUGIN_DIR . 'core/' . strtolower($class) . '.php';
        if(file_exists($class)){
            require_once $class;
        }
    }
}
