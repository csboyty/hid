<?php
/**
 * Created by JetBrains PhpStorm.
 * User: ty
 * Date: 13-6-26
 * Time: 下午12:03
 * 一些ajax接口
 */
include("zy_image_class.php");
class zy_ajax_class
{
    const ZY_COMPRESS_SUFFIX="_480x480"; //常量，代表压缩文件的后缀

    /**
     *ajax 上传文件函数
     */
    public function zy_action_uploadfile(){
        $image=new zy_image_class();
        $dir=wp_upload_dir();
        $user_id=$_POST["user_id"];
        $post_id=$_POST["post_id"];

        //文件代表的类型，如果是content_img表示是要显示到内容中的图片，zy_thumb缩略图,zy_background背景
        $file_use_type=isset($_POST["file_type"])?$_POST["file_type"]:"";
        $filename=$_FILES["file"]["name"];
        $pathinfo=pathinfo($filename);
        $filetype =$pathinfo["extension"];//获取后缀

        //中文为自首的文件会是空
        $pathinfo["filename"]=substr($filename, 0,strrpos($filename, '.'));

        //判断缩略图是否为1：1
        if($file_use_type=="zy_thumb"){
            $attr=getimagesize($_FILES["file"]["tmp_name"]);
            if($attr[0]!=$attr[1]){
                //如果不是1：1的图片报错
                $obj=array("message"=>"图片不是1：1比例！");
                wp_send_json_error($obj);
            }
        }

        //判断背景图是否为1280宽
        if($filetype!="mp4"&&$file_use_type=="zy_background"){
            $attr=getimagesize($_FILES["file"]["tmp_name"]);
            if($attr[0]!=1024||$attr[1]!=768){
                //如果不是1：1的图片报错
                $obj=array("message"=>"图片宽度不是1024或者高度不是768！");
                wp_send_json_error($obj);
            }
        }

        $tmp_dir=$dir["basedir"]."/tmp";
        $target_dir=$tmp_dir."/".$user_id;

        //判断图片是否存在，如果存在，则重命名，加上当前时间搓
        if(is_file($target_dir . "/".$filename)||(!empty($post_id)&&is_file($dir["basedir"]."/".$post_id."/".$filename))){
            $filename=$pathinfo["filename"]."_".time().".".$filetype;
        }

        //创建临时文件夹
        if(!is_dir($tmp_dir)){
            if(!mkdir($tmp_dir)){
                $obj=array("message"=>"创建临时文件夹失败！");
                wp_send_json_error($obj);
            }
        }

        //创建目标文件夹
        if(!is_dir($target_dir)){
            if(!mkdir($target_dir)){
                $obj=array("message"=>"创建文件夹失败！");
                wp_send_json_error($obj);
            }
        }

        //此处需要文件转码才能支持中文
        if(move_uploaded_file($_FILES["file"]["tmp_name"],$target_dir . "/".$filename)){

            //如果是zip文件，需要解压，如果解压不成功，返回false，这里看是在什么时候解压（上传、放到最终目录时）
            if($filetype=="zip"){

                //$zipdir=$pathinfo["filename"];//获取除后缀名外的文件名部分，中文名字有bug采用下面的方法
                $zipdir=substr($filename, 0, strrpos($filename, "."));

                //创建文件夹
                if(!is_dir($target_dir."/".$zipdir)){
                    if(!mkdir($target_dir."/".$zipdir)){
                        $obj=array("message"=>"创建压缩文件夹失败！");
                        wp_send_json_error($obj);
                    }
                }

                //开始解压zip
                $zip = new ZipArchive();
                $rs = $zip->open($target_dir."/".$filename);
                if($rs !== TRUE)
                {

                    //如果为成功，直接返回
                    $obj=array("message"=>"解压zip文件出错");
                    wp_send_json_error($obj);
                }

                //解压到哪个文件夹下
                $zip->extractTo($target_dir."/".$zipdir);
                $zip->close();

                $obj=array("url"=>$dir["baseurl"]."/tmp/".$user_id."/".$zipdir,"filename"=>$filename);
                wp_send_json_success($obj);
            }

            //压缩整篇文章的封面图
            if($file_use_type=="zy_thumb"){
                $image->resize($target_dir . "/".$filename,self::ZY_COMPRESS_SUFFIX,480,480);
            }

            $obj=array("url"=>$dir["baseurl"]."/tmp/".$user_id."/".$filename,"filename"=>$filename);
            wp_send_json_success($obj);

        }else{

            //上传出错反馈
            $obj=array("message"=>"文件上传失败，请稍后重试");
            wp_send_json_error($obj);
        }
    }

    /**
     *打包程序返回接口，打包程序对打包结果的反馈
     */
    public function zy_pack_unlock_callback(){
        $post_id=$_POST['docId'];

        $success_flag=$_POST['packed_status'];

        global $wpdb;


        //重置数据库记录,需要记录的id在数据库中
        if($success_flag=="true"){

            //设置数据库标志,表示已经打包
            if($wpdb->update($wpdb->prefix."pack_ids",array("pack_lock"=>1),array("post_id"=>$post_id),array("%d"))!==false){
                echo "success";
                die();
            }else{

                //返回failture打包程序将帮助将标志置位1
                echo "failure";
                die();
            }
        }else{

            //去掉数据库的打包时间，不锁定文章，此处不判断，不需要通知打包程序来设置时间
            $wpdb->update($wpdb->prefix."pack_ids",array("pack_time"=>NULL),array("post_id"=>$post_id),array("%s"));
            echo "success";
            die();
        }

    }
}
