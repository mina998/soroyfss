<div class="wrap">
    <h1>常规选项</h1>
    <?php if ( $this->message ) { ?>
    <div id="setting-error-settings_updated" class="notice notice-success settings-error is-dismissible"> 
        <p><strong><?php echo $this->message ?></strong></p>
    </div>
    <?php } ?>
    <hr>
    <form method="post" action="<?php echo wp_nonce_url('admin.php?page='.$this->plugin_file); ?>">
        <table class="form-table" role="presentation">
            <tbody>
                <tr>
                    <th scope="row"><label for="ftp_host">FTP服务器地址</label></th>
                    <td>
                        <input id="ftp_host" name="ftp_host" type="text" value="<?php echo esc_attr($this->ftp_host); ?>" class="regular-text">
                        <p class="description">填写虚拟主机地址. 示例：<code>123.123.123.123</code></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="ftp_port">FTP服务器端口</label></th>
                    <td>
                        <input id="ftp_port" name="ftp_port" type="text" value="<?php echo esc_attr($this->ftp_port); ?>" class="regular-text">
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="ftp_mode">FTP模式</label></th>
                    <td>
                        <input name="ftp_mode" type="hidden"  value="<?php echo $this->ftp_mode;?>">
                        <input id="ftp_mode" type="checkbox" <?php if ($this->ftp_mode){ ?> checked="checked" <?php }?>>
                        "勾选" 使用FTP被动模式
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="ftp_domain">FTP空间绑定域名</label></th>
                    <td>
                        <input id="ftp_domain" name="ftp_domain" type="text" value="<?php echo esc_attr($this->ftp_domain); ?>" class="regular-text">
                        <div class="description">
                            <p><strong>设置注意事项: </strong></p>
                            <p>1. 一般我们是以: <code>http://{FTP空间绑定域名}</code>, 同样不要用"/"结尾.</p>
                            <p>2. 示范： <code>http(s)://images.xxxx.com</code></p>
                        </div>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="ftp_username">FTP用户名</label></th>
                    <td><input id="ftp_username" name="ftp_username" type="text" value="<?php echo esc_attr($this->ftp_username); ?>" class="regular-text code"></td>
                </tr>
                <tr>
                    <th scope="row"><label for="ftp_password">FTP密码</label></th>
                    <td>
                        <input id="ftp_password" name="ftp_password" type="password" value="<?php echo esc_attr($this->ftp_password); ?>" class="regular-text code">
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="fss_path">存储子目录</label></th>
                    <td>
                        <input id="fss_path" name="fss_path" type="text" value="<?php echo esc_attr($this->fss_path); ?>" class="regular-text ltr">
                        <p class="description">默认留空, 结尾不要加 "/", 比如/wwwroot</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="renames_allow">自动重命名</label></th>
                    <td>
                        <input name="renames_allow" type="hidden"  value="<?php echo $this->renames_allow;?>">
                        <input id="renames_allow" type="checkbox" <?php if ($this->renames_allow){ ?> checked="checked" <?php }?>>
                        "勾选" 如果文件名中包含中文, 则自动动重命名
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="disable_local">删除本地文件</label></th>
                    <td>
                        <input name="disable_local" type="hidden"  value="<?php echo $this->disable_local;?>">
                        <input id="disable_local" type="checkbox" <?php if ($this->disable_local){ ?> checked="checked" <?php }?>>
                        "勾选" 不在本地保留备份
                    </td>
                </tr>
            </tbody>
        </table>
        <?php 
            wp_nonce_field();
            submit_button(); 
        ?>
    </form>

    <hr>
    <blockquote>
        <p>1. 网站本地已有静态文件,需要在测试插件兼容之后,将本地文件对应目录上传到自建FTP存储目录中(可用 FTP工具)</p>
        <p>2. 初次使用SoroyFSS存储插件,可以通过下面按钮一键快速替换网站内容中的原有图片地址更换为新的FTP图床存储自定义地址</p>
        <p>3. 建议不熟悉的朋友先备份网站和数据.</p>
    </blockquote>
    <form method="post" action="<?php echo wp_nonce_url('admin.php?page='.$this->plugin_file); ?>">
        <input type="hidden" id="content_replace" name="content_replace" value="" />
        <?php
        submit_button('一键替换为FSS空间文件地址', 'primary2', 'replace_url_fss', false);
        echo '&nbsp;&nbsp;&nbsp;&nbsp;';
        submit_button('一键替换为WordPress文件地址', 'primary', 'replace_url_wp', false);
        ?>
    </form>
</div>
<script>
    jQuery(function($) {
        $('#replace_url_fss').click(function(){
            $('#content_replace').val('fss')
        })
        $('#replace_url_wp').click(function(){
            $('#content_replace').val('wp')
        })
        $(":checkbox").change(function(){
            if($(this).is(':checked')){
                $(this).prev('input').val(1)
            }else{ $(this).prev('input').val(0) }
        })
    })
</script>