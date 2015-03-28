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

//https://api.vdian.com/token?grant_type=client_credential&appkey=620889&secret=106e6c955826149d13ae025e8e44424b

add_action('init', 'jintoweidian_init',11);
function jintoweidian_init($wp){



    if(isset($_GET['jin']) ){

        //receive data from jinshuju push
        $jin_data = file_get_contents('php://input');
//        header('HTTP/1.1 200 OK');
        http_response_code(200);
        writelog($jin_data);

        //get weidian token
        $result = api_request(url_get_weidian_token);

        $json = json_decode($result,true);
        $token_result = isset($json['result']) ? $json['result'] : null;
        if($token_result == null){
        } else{
            $token = $token_result['access_token'];

            $url = url_weidian_add_product.$token.url_weidian_add_product_part2;


//            $jin_data = '{"form":"9rk3Dk","entry":{"serial_number":3,"field_1":"https://dn-jsjpri.qbox.me/en/551548c84150507c7f750300/3_1_21_3_1175555795.jpg?token=kTs1p9Tn1gGWiIC_O83TcJeBc2E7oVxVCgDuTGFj:9SlHHBkuTnkAiZTU2Ls0zcAn2kE=:eyJTIjoiZG4tanNqcHJpLnFib3gubWUvZW4vNTUxNTQ4Yzg0MTUwNTA3YzdmNzUwMzAwLzNfMV8yMV8zXzExNzU1NTU3OTUuanBnKiIsIkUiOjE0Mjc0NjE4NTN9\u0026download","field_2":"ceshe","field_8":"onefangjun","field_3":"全新","field_4":"北京","field_5":101,"field_6":"L","field_7":"无","creator_name":"mamifair","created_at":"2015-03-27T12:10:53Z","updated_at":"2015-03-27T12:10:53Z","info_remote_ip":"140.206.88.176"}}';

            $data = json_decode($jin_data,true);
            $product = $data["entry"];

            $img =  $product['field_1'];
            $imgurl= substr($img,0,-9);
            $title = $product['field_2'];
            $owner = $product['field_8'];
            $new =  $product['field_3'];
            $location = $product['field_4'];
            $prize = $product['field_5'];
            $size = $product['field_6'];
            $desc = $product['field_7'];


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


//下载图片
//            writelog($imgurl);
            $upfilename =  savefile($imgurl);

//            writelog('after save file');

//上传图片

            $upload_url = url_weidian_upload.$token;

            $file_name = JIN_PLUGIN_DIR.'/temp/'.$upfilename;


            $upresult = api_upload($upload_url,$file_name);
//            writelog( 'result:'.$upresult.'<br/><br/>');


//            $upresult = '{"result":"http://wd.geilicdn.com/vshop1427446241686-45435082.jpg?w=640&h=791","status":{"status_code":0,"status_reason":"success"}}';
            $image_result = json_decode($upresult,true);
//            var_dump($image_result);
//            echo "\n\n<br>";
            $img = isset($image_result['result']) ? $image_result['result'] : null;

//            echo "<br>imge:<br>";
//            echo $img;

            $img = substr($img,0,-strlen($img)+strpos($img,'?'));

//            writelog("uploadimage:\n".$img);

            $weidian_product = array(
                "imgs" => [$img],
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

            $url = $url.$weidian_product_json;

            $result= api_request($url);
            writelog($result);
//            $f  = file_put_contents($file, $result,FILE_APPEND);
            exit(1);

        }

    }

}


function api_request($url){

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $result = curl_exec($ch);

//    echo curl_getinfo($ch) . '<br/>';
//    echo curl_errno($ch) . '<br/>';
//    echo curl_error($ch) . '<br/>';

    curl_close($ch);

    return $result;

}


function api_upload($upload_url,$file_name){

//    $file_name = JIN_PLUGIN_DIR.'/temp/1175555795.jpg';
    $post = array('media'=>'@'.$file_name);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL,$upload_url);
    curl_setopt($ch, CURLOPT_POST,1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $result=curl_exec ($ch);


//    writelog(curl_getinfo($ch));
//    writelog(curl_errno($ch));
//    writelog(curl_error($ch));
//    echo curl_getinfo($ch) . '<br/>';
//    echo curl_errno($ch) . '<br/>';
//    echo curl_error($ch) . '<br/>';

    curl_close ($ch);

//    echo $result."\n<br><br>";

    return $result;

}


function savefile($url){

//    $url='https://dn-jsjpri.qbox.me/en/551548c84150507c7f750300/3_1_21_3_1175555795.jpg?token=kTs1p9Tn1gGWiIC_O83TcJeBc2E7oVxVCgDuTGFj:9SlHHBkuTnkAiZTU2Ls0zcAn2kE=:eyJTIjoiZG4tanNqcHJpLnFib3gubWUvZW4vNTUxNTQ4Yzg0MTUwNTA3YzdmNzUwMzAwLzNfMV8yMV8zXzExNzU1NTU3OTUuanBnKiIsIkUiOjE0Mjc0NjE4NTN9';
    $token_pos = strpos($url,'?token=');

    $filename = substr($url,0,$token_pos);
//    writelog( "<br/>filename".$filename."<br/>");
    $filename = substr($filename,strrpos($filename,'/')-strlen($filename)+1);
//    writelog( "<br/>filename".$filename."<br/>");


    set_time_limit(0);

    //File to save the contents to
    $file = JIN_PLUGIN_DIR."/temp/".$filename;

    $fp = fopen ($file, 'w+');

//    $url = "http://localhost/files.tar";

    //Here is the file we are downloading, replace spaces with %20
    $ch = curl_init(str_replace(" ","%20",$url));

    curl_setopt($ch, CURLOPT_TIMEOUT, 50);

    //give curl the file pointer so that it can write to it
    curl_setopt($ch, CURLOPT_FILE, $fp);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

    $data = curl_exec($ch);//get curl response

    //done
    curl_close($ch);

    return $filename;

}

function writelog($text){

    $file  = JIN_PLUGIN_DIR.'/log.txt';
    $text = $text."\n\n";
    $f  = file_put_contents($file, $text,FILE_APPEND);
}