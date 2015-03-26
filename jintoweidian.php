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
define('weidian_key', '620889');
define('weidian_secret', '106e6c955826149d13ae025e8e44424b');
define('url_get_weidian_topen','https://api.vdian.com/token?grant_type=client_credential&appkey='.
    weidian_key.'&secret='.weidian_secret);

add_action('init', 'jintoweidian_init',11);
function jintoweidian_init($wp){

    $file  = JIN_PLUGIN_DIR.'/log.txt';

    if(isset($_GET['jin']) ){


        $data = file_get_contents('php://input');
        $data = json_decode($data);
        $data = var_export($data,1);

        $content = $data.'\n\n';



//        $content ='test\n';
        if($f  = file_put_contents($file, $content,FILE_APPEND)){
            header('HTTP/1.1 200 OK');
        }

        $response = http_get(url_get_weidian_topen, array("timeout"=>1), $info);
        file_put_contents($file, $info,FILE_APPEND);

    }

}
