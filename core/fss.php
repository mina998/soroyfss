<?php
/**
 * USER : Soroy
 * DATE : 2022-06-08 008
 */
class Fss extends Soroy {
	/**
	 * 上传附件
	 * @param $upload
	 * @return mixed
	 */
    public function fss_upload_attachments($upload){
		/**
		 * $upload -> 字段
		 *  [file] => /usr/local/lsws/wp.iosss.cc/wordpress/wp-content/uploads/2022/06/5.jpg
		 *  [url] => http://img.iosss.cc:5002/web/2022/06/5.jpg
		 *  [type] => image/jpeg
		 */
	    if( !wp_get_image_mime($upload['file']) ){
	        $ss_file_path = $this->build_path($upload['file'])['ss_full'] . basename($upload['file']);
            $this->file_upload($ss_file_path, $upload['file']);
        }
        return $upload;
    }
	/**
	 * 上传图片
	 * @param $meta
	 * @return mixed
	 */
    public function fss_upload_images($meta){
	    if ( !isset($meta['file']) ) return $meta;
	    //上传主图
		extract($this->build_path($meta['file']) );
        $ss_path = $ss_full . basename($meta['file']);
        $wp_path = $wp_full . basename($meta['file']);
        $this->file_upload($ss_path, $wp_path);
        //上传缩略图
        if (isset($meta['sizes']) && count($meta['sizes']) > 0) {
            foreach ($meta['sizes'] as $item) {
                $ss_path = $ss_full . $item['file'];
				$wp_path = $wp_full . $item['file'];
                $this->file_upload($ss_path, $wp_path);
            }
        }
        return $meta;
    }
	/**
	 * 上传入口 判断是否保存本地文件
	 * @param $ss_file_path : 远程文件路径
	 * @param $wp_file_path : 本地文件路径
	 *
	 * @return void
	 */
    public function file_upload($ss_file_path, $wp_file_path){
        $this->ftp()->Upload($ss_file_path,  $wp_file_path);
        // 如果上传成功，且不再本地保存，在此删除本地文件
        if ( file_exists($wp_file_path) && $this->disable_local ) {
            unlink($wp_file_path);
        }
    }
	/**
	 * 重命名中文文件名
	 * @param $filename
	 * @return array|mixed|string|string[]
	 */
    public function unique_file_name($filename){
	    if(preg_match('/[\x{4e00}-\x{9fa5}]+/u', $filename)){ //判断文件名为中文
		    $filename = substr(md5($filename), 0, 8). '.' . pathinfo($filename, PATHINFO_EXTENSION); //把文件的md5值的前8位作为文件名
	    }
		//如果本地留有副本 不需要检测远程文件
		if($this->disable_local == 0) return $filename;
		//以下检测远程文件是否存在
	    $ext    = '.' . pathinfo($filename, PATHINFO_EXTENSION);
	    $number = '';
		$ss_full= $this->build_path()['ss_full'];
		//此处会循环检查缩略图 所以 $ss_full 路径必须要写在 hasFileExist() 里面, 否则变成死循环， 每次检查图片都存在
        while ($this->ftp()->hasFileExist($ss_full.$filename)) {
            $new_number = (int)$number + 1;
            if ('' == $number.$ext) {
                $filename = $filename.'-'.$new_number;
            } else {
                $filename = str_replace(['-'.$number.$ext, $number.$ext], '-' . $new_number.$ext, $filename);
            }
            $number = $new_number;
        }
        return $filename;
    }
	/**
	 * 删除附件 无返回值
	 * @param $post_id : 附件id
	 * @return void
	 */
    public function delete_attachment($post_id){
        $deleteObjects = [];
        $meta = wp_get_attachment_metadata($post_id);
        if (isset($meta['file'])) {
	        extract($this->build_path($meta['file']));
            $deleteObjects[] = $this->fss_path . '/' . $meta['file'];
        } else {
            $file = get_attached_file($post_id);
	        extract($this->build_path($file));
            $deleteObjects[] = $ss_full . basename($file);
        }
        if (isset($meta['sizes']) && count($meta['sizes']) > 0) {
            foreach ($meta['sizes'] as $val) {
                $deleteObjects[] = $ss_full . $val['file'];
            }
        }
	    $backup = get_post_meta($post_id, '_wp_attachment_backup_sizes', true );
	    if($backup){
		    foreach($backup as $item){
			    $deleteObjects[] = $ss_full . $item['file'];
		    }
	    }
		if (!empty($deleteObjects)){
			$this->wp_ss_delete($deleteObjects, $wp_full);
		}
    }
	/**
	 * 删除附件入口 无返回值
	 * @param $data : 要删除的文件数组
	 * @param $path : 本地文件上传路径
	 * @return void
	 */
	public function wp_ss_delete($data, $path){
		if ($this->disable_local == 0){
			foreach ($data as $item){
				$file = $path . basename($item);
				if(file_exists($file)) unlink($file);
			}
		}
		$this->ftp()->delete($data);
	}

    public function save_image_editor_file($override){
        add_filter('wp_update_attachment_metadata', [$this, 'image_editor_file_save']);
        return $override;
    }

    public function image_editor_file_save($meta){
        $meta = $this->fss_upload_images($meta);
        remove_filter('wp_update_attachment_metadata', [$this, 'image_editor_file_save']);
        return $meta;
    }

	public function admin_menu(){
		add_options_page('SoroyFSS', 'SoroyFSS', 'manage_options', $this->plugin_file, [$this, 'view']);
	}

	public function plugin_action_links($links, $file){
		if ($this->plugin_file == $file) {
			$links[] = '<a href="admin.php?page=' . $file . '">设置</a>';
		}
		return $links;
	}

	public function restore_options(){
		update_option('upload_url_path', '');
	}

	public function load_options(){
		update_option('upload_url_path', $this->ftp_domain . $this->fss_path);
	}
	public function wp_upload_hooks(){
		add_filter('wp_handle_upload', [$this, 'fss_upload_attachments']);
		if (version_compare(get_bloginfo('version'), 5.3, '<')) {
			add_filter('wp_update_attachment_metadata', [$this, 'fss_upload_images']);
		} else {
			add_filter('wp_generate_attachment_metadata', [$this, 'fss_upload_images']);
			add_filter('wp_save_image_editor_file', [$this, 'save_image_editor_file']);
		}
	}

	public static function init($plugin_file){
		$soroy = new self($plugin_file);
		// 后台菜单添加设置页面
		add_action('admin_menu', [$soroy, 'admin_menu']);
		// 在插件列表页添加设置按钮
		add_filter('plugin_action_links', [$soroy, 'plugin_action_links'], 10, 2);
		// 避免上传插件/主题被同步到对象存储
		if (substr_count($_SERVER['REQUEST_URI'], '/update.php') <= 0) {
			$soroy->wp_upload_hooks();
		}
		//	删除附件
		add_action('delete_attachment', [$soroy, 'delete_attachment']);
		// 检测不重复的文件名
		if($soroy->renames_allow){
			add_filter('sanitize_file_name', [$soroy, 'unique_file_name'], 5,1);
		}
		// 插件禁用时触发
		register_deactivation_hook($soroy->plugin_file, [$soroy, 'restore_options']);
		# 插件 activation 函数当一个插件在 WordPress 中”activated(启用)”时被触发。
		register_activation_hook($soroy->plugin_file, [$soroy, 'load_options']);
	}
}