<?php
/**
 * Template Name: 显示媒体文件
 *
 * Description:用于客户端调用，显示图片绑定的媒体文件
 *
 * Tip: post_id作为第一个参数,media_id作为第二个参数.url重写在functions.php中实现
 *
 * @package WordPress
 * @subpackage Twenty_Twelve
 * @since Twenty Twelve 1.0
 */
//需要判断用户是否有权限查看该媒体文件



//需要传img的标识和文章的id过来，才能搜索出该媒体文件
$querys=$wp_query->query;
$post_id=$querys->zy_post_id;
$media_id=$querys->zy_media_id;

//获取媒体信息字符串
$media_string=get_post_meta($post_id,$media_id,true);
//将json字符串转化为数组
$media=json_decode($media_string,true);
$media_type=$media->zy_media_type;
$media_filepath=$media->zy_media_filepath;

?>
<!DOCTYPE HTML>
<html>
<head>
    <title>上传本地视频</title>
    <meta http-equiv="content-type" content="text/html;charset=utf-8">
</head>
<body>
<?php
    if($media_type=="zy_location_video"){
        echo "<video class='zy_preview_video' autoplay='autoplay' style='width: 100%;height: 100%' controls><source src='$media_filepath' type='video/mp4' /></video>";
    }else if($media_type=="zy_3d"){

    }else if($media_type=="zy_ppt"){
        echo "<iframe src='$media_filepath/index.html' width='100%' height='100%'></iframe>";
    }else if($media_type=="zy_network_video"){
        echo $media_filepath;
    }
?>
</body>
</html>


