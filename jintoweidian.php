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
define('url_weidian_upload','http://api.vdian.com/media/upload?access_token=');
define('option_name','jindata');


add_action('init', 'jintoweidian_init',11);

function jintoweidian_init($wp){

    if(isset($_GET['jin']) ) {

        //receive data from jinshuju push
        $jin_data = file_get_contents('php://input');
        if ($jin_data) {

            ignore_user_abort(true);

            header('HTTP/1.1 200 OK');
            header('Content-Length:0');
            header('Connection:Close');

            flush();

            writelog($jin_data);
            update_option(option_name,$jin_data);

            wp_schedule_single_event(time() + 10, 'pushweidian');

            $timestamp = wp_next_scheduled( 'pushweidian');

            writelog("====end===");

            wp_cron();

            $timestamp = wp_next_scheduled( 'pushweidian');

        } else {
            //
        }

    }
}

//wp_cron();

function api_request($url){

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $result = curl_exec($ch);

    curl_close($ch);

    return $result;
}



function api_upload($upload_url,$file_name){

//    $file_name = JIN_PLUGIN_DIR.'/temp/1175555795.jpg';
    $post = array('media'=>'@'.$file_name);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_TIMEOUT, 60);
    curl_setopt($ch, CURLOPT_URL,$upload_url);
    curl_setopt($ch, CURLOPT_POST,true);
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $result=curl_exec ($ch);

    $info  = curl_getinfo( $ch );

    curl_close ($ch);

    return $result;
}


function savefile($url){

//    $url='https://dn-jsjpri.qbox.me/en/551548c84150507c7f750300/3_1_21_3_1175555795.jpg?token=kTs1p9Tn1gGWiIC_O83TcJeBc2E7oVxVCgDuTGFj:9SlHHBkuTnkAiZTU2Ls0zcAn2kE=:eyJTIjoiZG4tanNqcHJpLnFib3gubWUvZW4vNTUxNTQ4Yzg0MTUwNTA3YzdmNzUwMzAwLzNfMV8yMV8zXzExNzU1NTU3OTUuanBnKiIsIkUiOjE0Mjc0NjE4NTN9';
    $token_pos = strpos($url,'?token=');
    $filename = substr($url,0,$token_pos);
    $filename = substr($filename,strrpos($filename,'/')-strlen($filename)+1);
    $tempfile = JIN_PLUGIN_DIR."/temp/".$filename;

    $ch = curl_init ($url);
    curl_setopt($ch, CURLOPT_TIMEOUT, 60);
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_BINARYTRANSFER,true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $raw=curl_exec($ch);

    $info  = curl_getinfo( $ch );

    curl_close ($ch);

    if(file_exists($tempfile)){
        unlink($tempfile);
    }
    $fp = fopen($tempfile,'x');
    fwrite($fp, $raw);
    fclose($fp);

    writelog('save file end'.$filename."size".filesize($tempfile));
    return $filename;
}

function writelog($text){

    $file  = JIN_PLUGIN_DIR.'/log.txt';
    $text = $text."\n\n";
    $f  = file_put_contents($file, $text,FILE_APPEND);
}


add_action( 'pushweidian', 'push_to_weidian', 10, 0 );

function push_to_weidian() {

    writelog('push_to_weidian begin==');

    $jin_data = get_option(option_name);
    writelog('option:'.$jin_data);
    update_option(option_name, '');
    //no check TODO

    if( $jin_data !='' ) {


        //get weidian token
        $result = api_request(url_get_weidian_token);


        $json = json_decode($result, true);
        $token_result = isset($json['result']) ? $json['result'] : null;

        writelog('before token');
        if ($token_result != null) {
            writelog('after token:'.$token_result['access_token']);

            $token = $token_result['access_token'];


            $data = json_decode($jin_data, true);
            $product = $data["entry"];

            $img = $product['field_1'];
            $imgurl = substr($img, 0, -9);
            $title = $product['field_2'];
            $owner = $product['field_8'];
            $new = $product['field_3'];
            $location = $product['field_4'];
            $prize = $product['field_5'];
            $size = $product['field_6'];
            $desc = $product['field_7'];


            $product_title = '【' . $owner . '】' . $title . "\n";
            if ($desc != '') {
                $product_title = $product_title . $desc . "\n";
            }
            $product_title = $product_title . '成色：' . $new . "\n";
            $product_title = $product_title . '所在地：' . $location . "\n";
            if ($size != '') {
                $product_title = $product_title . '尺码：' . $size . "\n";
            }
            $product_title = $product_title . '主人：' . $owner . "\n";


//下载图片
            $upfilename = savefile($imgurl);


//    $upfilename = '103_1_images.jpeg';
//上传图片

            $upload_url = url_weidian_upload . $token;
//            $file_name = JIN_PLUGIN_DIR . '/temp/' . $upfilename;

            $file_name = resizeImageIfNeed(JIN_PLUGIN_DIR . '/temp/',$upfilename);

            $upresult = api_upload($upload_url, $file_name);


            $image_result = json_decode($upresult, true);
            $wimg = isset($image_result['result']) ? $image_result['result'] : null;

            writelog('before img');
            if ($wimg != null) {
                writelog('after img' . $wimg);
                $wimg = substr($wimg, 0, -strlen($wimg) + strpos($wimg, '?'));
            }else{
                $wimg = 'http://wd.geilicdn.com/vshop252249156-1428061288665-2372617.jpg';

            }
//    $wimg = 'http://wd.geilicdn.com/vshop395640-1390204649-1.jpg';

            $weidian_product = array(
                "imgs" => [$wimg],
                "stock" => 1,
                "price" => $prize,
                "item_name" => $product_title,
                "fx_fee_rate" => "1",
                "cate_ids" => [38579397],
                "skus" => [],
                "merchant_code" => "",
            );

            $weidian_product_json = json_encode($weidian_product, true);

            $add_product_url = url_weidian_add_product . $token . url_weidian_add_product_part2;

            $weidian_product_json = urlencode($weidian_product_json);
            $add_product_url = $add_product_url . $weidian_product_json;

            writelog('add product url:'.$add_product_url);
            $result = api_request($add_product_url);
            writelog('result:'.$result);

        }
    }
        writelog('push_to_weidian end==');

}


function resizeImageIfNeed($filedir,$filename){

    $images = $filedir.$filename;
    $new_images =$filedir.'re'.$filename;

    writelog("image size:".filesize($images));
    if(filesize($images) < 500*1024){
        return $images;
    }

    writelog("======resize image====");

    if( extension_loaded('imagick') || class_exists("Imagick") ) {

        $im = new Imagick($images);
        $imageprops = $im->getImageGeometry();
        $width = $imageprops['width'];
        $height = $imageprops['height'];
        $newWidth = 1000;
        $newHeight = ($newWidth / $width) * $height;

        $im->resizeImage($newWidth, $newHeight, imagick::FILTER_LANCZOS, 0.9, true);
        $im->writeImage($new_images);

        return $new_images;
    }else{
        return $images;

    }
}