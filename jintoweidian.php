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
define('url_get_weidian_token','https://api.vdian.com/token?grant_type=client_credential&appkey='.weidian_key.'&secret='.weidian_secret);
//https://api.vdian.com/token?grant_type=client_credential&appkey=620889&secret=106e6c955826149d13ae025e8e44424b

add_action('init', 'jintoweidian_init',11);
function jintoweidian_init($wp){

    $file  = JIN_PLUGIN_DIR.'/log.txt';

    if(isset($_GET['jin']) ){

        //receive data from jinshuju push
        $data = file_get_contents('php://input');
//        $data = var_export($data,1);

        $content = $data.'\n\n';

        if($f  = file_put_contents($file, $content,FILE_APPEND)){
            header('HTTP/1.1 200 OK');
        }

        //get weidian token
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, url_get_weidian_token);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $result = curl_exec($ch);
        curl_close($ch);

        file_put_contents($file, $result.'\n\n',FILE_APPEND);
        $token   = json_decode($result);



        $data = json_decode($data);
        $product = $data['entry'];
        $content = var_export($product,1);
        $f  = file_put_contents($file, $content,FILE_APPEND);


    }

}
