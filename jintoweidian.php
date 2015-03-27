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
define('weidian_key', '6208891');
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


        $result   = json_decode($result,true)['result'];
        $nresult = var_export($result,1);
        file_put_contents($file, $nresult.'\n\n',FILE_APPEND);
        file_put_contents($file, $result['access_token'].'\n\n',FILE_APPEND);

        $data = '{"form":"9rk3Dk","entry":{"serial_number":2,"field_1":"https://dn-jsjpri.qbox.me/en/5514f1bd41505068a58f0200/2_1_mamifair_%E5%9B%BE.png?token=kTs1p9Tn1gGWiIC_O83TcJeBc2E7oVxVCgDuTGFj:XjeM9m3yDreyfOr281Isemjje-Q=:eyJTIjoiZG4tanNqcHJpLnFib3gubWUvZW4vNTUxNGYxYmQ0MTUwNTA2OGE1OGYwMjAwLzJfMV9tYW1pZmFpcl_lm74ucG5nKiIsIkUiOjE0Mjc0Mzk2MDh9\u0026download","field_2":"测试商品","field_8":"onefangjun","field_3":"全新","field_4":"上海","field_5":100,"field_6":"","field_7":"","creator_name":"mamifair","created_at":"2015-03-27T06:00:18Z","updated_at":"2015-03-27T06:00:18Z","info_remote_ip":"106.120.85.234"}}';

        $data = json_decode($data,true);
//        var_dump($data);
        $product = $data["entry"];
//        $product = $data->{"entry"};
        $content = var_export($product,1);
        var_dump($product);
        $f  = file_put_contents($file, $content,FILE_APPEND);




    }

}
