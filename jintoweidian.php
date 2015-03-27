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


        echo '1/';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, url_get_weidian_token);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        echo '2/';

        $result = curl_exec($ch);
        echo '3/';

        curl_close($ch);
        echo $result.'result/';

        file_put_contents($file, $result,FILE_APPEND);
        $json   = json_decode($result);
        echo '4/';




/*
        $data = file_get_contents('php://input');
        $data = json_decode($data);
        $data = var_export($data,1);

        $content = $data.'\n\n';

        if($f  = file_put_contents($file, $content,FILE_APPEND)){
            header('HTTP/1.1 200 OK');
        }
*/

/*
//        echo url_get_weidian_token;
        $response = http_get(url_get_weidian_token, array(
          'headers' => array(
            'Accept' => 'application/json'
          )
        ), $info);

//        $response = http_get(url_get_weidian_token, array("timeout"=>1), $info);
        echo url_get_weidian_token;
        echo $info;
        file_put_contents($file, $response,FILE_APPEND);

*/

    }

}
