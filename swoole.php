<?php
$url = urldecode($_GET['img']);
function img($url){
    $img = $url;
    $info = getimagesize($img);
    $imgExt = image_type_to_extension($info[2], false);  //获取文件后缀
    $fun = "imagecreatefrom{$imgExt}";
    $imgInfo = $fun($img);                     //1.由文件或 URL 创建一个新图象。如:imagecreatefrompng ( string $filename )
    //$mime = $info['mime'];
    $mime = image_type_to_mime_type(exif_imagetype($img)); //获取图片的 MIME 类型
    header('Content-Type:'.$mime);
    $quality = 100;
    if($imgExt == 'png') $quality = 9;        //输出质量,JPEG格式(0-100),PNG格式(0-9)
    $getImgInfo = "image{$imgExt}";
    $getImgInfo($imgInfo, null, $quality);    //2.将图像输出到浏览器或文件。如: imagepng ( resource $image )
    imagedestroy($imgInfo);

}

$http = new Swoole\Http\Server("0.0.0.0", 8080);

$http->on("start", function ($server) {
    echo "Swoole http server is started at http://127.0.0.1:8080\n";
});

$http->on("request", function ($request, $response) {

    $res = ob_get_contents();//获取缓存区的内容
    ob_end_clean();//清除缓存区

    //输出缓存区域的内容
    img();

    $response->end($res);

});

$http->start();
