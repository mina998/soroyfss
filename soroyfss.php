<?php
/*
Plugin Name: SoroyFSS
Plugin URI: https://www.skiss.cc
Description: 这是一个FTP存储服务.
Version: 1.0
Author: Soroy
Author URI: https://www.skiss.cc
License: A "Slug" license name e.g. GPL2
*/

defined('ABSPATH') || exit();
// 定义插件目录绝对路径
define( 'SOROY_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
// 加载工具类
require_once 'core/soroy.php';
// 注册自动加载类函数
spl_autoload_register('Soroy::loader');
// 插件执行文件
$plugin_file = plugin_basename(__FILE__);

Fss::init($plugin_file);
