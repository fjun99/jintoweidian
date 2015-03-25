<?php

/*
Plugin Name: Jintoweidian
Plugin URI: http://URI_Of_Page_Describing_Plugin_and_Updates
Description: receive data from jinshuju and update it to weidian
Version: 1.0
Author: fangjun
Author URI: http://URI_Of_The_Plugin_Author
License: A "Slug" license name e.g. GPL2
*/

define('JIN_PLUGIN_DIR', WP_PLUGIN_DIR.'/'. dirname(plugin_basename(__FILE__)));

add_action('init', 'jintoweidian_init',11);
function jintoweidian_init($wp){

    $file  = JIN_PLUGIN_DIR.'/log.txt';//要写入文件的文件名（可以是任意文件名），如果文件不存在，将会创建一个

    if(isset($_GET['jin']) ){
        $data = $_POST;
        $content = json_decode($data)."\n\n";
        if($f  = file_put_contents($file, $content,FILE_APPEND)){
            header('HTTP/1.1 200 OK');
        }

    }

}
