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
define('url_weidian_add_product','http://api.vdian.com/api?public={"method":"vdian.item.add","access_token":"');
define('url_weidian_add_product_part2','","version":"1.0","format":"json"}&param=');

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
        $result = api_request(url_get_weidian_token);
/*
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, url_get_weidian_token);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $result = curl_exec($ch);
        curl_close($ch);
*/
        $json = json_decode($result,true);
        $result = isset($json['result']) ? $json['result'] : null;
        if($result == null){
//            echo 'no token';
        }
        else{

            $url = url_weidian_add_product.$result['access_token'].url_weidian_add_product_part2;
/*
            $weidian_product= {"imgs":["http://wd.geilicdn.com/vshop395640-1390204649-1.jpg"],
                                "stock":"110",
                                "price":"350.00",
                                "item_name":"海北",
                                "fx_fee_rate":"1",
                                "skus":[],
                                "merchant_code":"90"};
*/
            $data = '{"form":"9rk3Dk","entry":{"serial_number":2,"field_1":"https://dn-jsjpri.qbox.me/en/5514f1bd41505068a58f0200/2_1_mamifair_%E5%9B%BE.png?token=kTs1p9Tn1gGWiIC_O83TcJeBc2E7oVxVCgDuTGFj:XjeM9m3yDreyfOr281Isemjje-Q=:eyJTIjoiZG4tanNqcHJpLnFib3gubWUvZW4vNTUxNGYxYmQ0MTUwNTA2OGE1OGYwMjAwLzJfMV9tYW1pZmFpcl_lm74ucG5nKiIsIkUiOjE0Mjc0Mzk2MDh9\u0026download","field_2":"测试商品","field_8":"onefangjun","field_3":"全新","field_4":"上海","field_5":100,"field_6":"","field_7":"","creator_name":"mamifair","created_at":"2015-03-27T06:00:18Z","updated_at":"2015-03-27T06:00:18Z","info_remote_ip":"106.120.85.234"}}';

            $data = json_decode($data,true);
            $product = $data["entry"];

            $img =  $product['field_1'];
            echo $img;
            $title = $product['field_2'];
            $owner = $product['field_8'];
            $new =  $product['field_3'];
            $location = $product['field_4'];
            $prize = $product['field_5'];
            $size = $product['field_6'];
            $desc = $product['field_7'];
/*
            array(13)
            { ["serial_number"]=> int(2)
            ["field_1"]=> string(286) "https://dn-jsjpri.qbox.me/en/5514f1bd41505068a58f0200/2_1_mamifair_%E5%9B%BE.png?token=kTs1p9Tn1gGWiIC_O83TcJeBc2E7oVxVCgDuTGFj:XjeM9m3yDreyfOr281Isemjje-Q=:eyJTIjoiZG4tanNqcHJpLnFib3gubWUvZW4vNTUxNGYxYmQ0MTUwNTA2OGE1OGYwMjAwLzJfMV9tYW1pZmFpcl_lm74ucG5nKiIsIkUiOjE0Mjc0Mzk2MDh9&download"
            ["field_2"]=> string(12) "测试商品"
            ["field_8"]=> string(10) "onefangjun"
                ["field_3"]=> string(6) "全新"
                ["field_4"]=> string(6) "上海"
                ["field_5"]=> int(100)
            ["field_6"]=> string(0) ""
                ["field_7"]=> string(0) ""
                ["creator_name"]=> string(8) "mamifair" ["created_at"]=> string(20) "2015-03-27T06:00:18Z" ["updated_at"]=> string(20) "2015-03-27T06:00:18Z" ["info_remote_ip"]=> string(14) "106.120.85.234" }
*/

/*
【@Jennifer 团子】burberry 双排扣棉线针织长款短款两穿大衣
棉线，自重较重，适合略胖妹纸。
成色：九成新仅用过几次
所在地：上海
尺码：L
主人：@Jennifer 团子
             */

            $product_title = '【'.$owner.'】'.$title."\n";
            if($desc!=''){
                $product_title = $product_title.$desc."\n";
            }
            $product_title = $product_title.'成色：'.$new."\n";
            $product_title = $product_title.'所在地：'.$location."\n";
            if($size!=''){
                $product_title = $product_title.'尺码：'.$size."\n";
            }
            $product_title = $product_title.'主人：'.$owner."\n";

//            $imgs = array($img);
//            var_dump($imgs);
//            echo "\n";

            $weidian_product = array(
                "imgs" => ["http: //wd.geilicdn.com/vshop395640-1390204649-1.jpg"],
                "stock" => 1,
                "price" => $prize,
                "item_name"=>$product_title,
                "fx_fee_rate"=>"1",
                "cate_ids"=>[36660506],
                "skus"=>[],
                "merchant_code"=>"",
            );


//            var_dump($product);

            $weidian_product_json = json_encode($weidian_product,true);
//            echo $weidian_product_json;


            $url = $url.$weidian_product_json;
//            echo $url;

            $f  = file_put_contents($file, $url,FILE_APPEND);
/*
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            $result = curl_exec($ch);
            curl_close($ch);
*/

            echo 'before';
            $result= api_request($url);
            echo 'after';
            echo $result;
            $f  = file_put_contents($file, $result,FILE_APPEND);

        }

    }

}


function api_request($url){

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $result = curl_exec($ch);
    curl_close($ch);

    return $result;

}
