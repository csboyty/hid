<?php
/**
 * Created by JetBrains PhpStorm.
 * User: ty
 * Date: 13-6-20
 * Time: 上午9:53
 * 保存文章和幻灯片数据的基类
 */

//include("zy_common_class.php");//在admin_init中引入了
class zy_articles_save_class
{
    private $dir,$target_dir,$from_dir;

    const ZY_COMPRESS_SUFFIX="_480x480"; //常量，代表压缩文件的后缀


    /**
     * 移动媒体文件
     * @param string $from_dir 存放文件的tmp路径
     * @param string $target_dir 最终保存文件的路径
     * @param array $value 媒体文件数据的数组
     * {
            zy_media_type:
            zy_media_filename:
            zy_media_filepath:
            zy_media_thumb_filename:
            zy_media_thumb_filepath:
            zy_media_title:
            zy_media_memo
        }
     * @return bool true|false 保存是否成功
     */
    public function zy_move_media_files($from_dir,$target_dir,$value){

        //获取文件相关信息
        $filename=$value["zy_media_filename"];
        $thumb_filename=$value["zy_media_thumb_filename"];
        $media_type=$value["zy_media_type"];

        //非网络视频，需要移动文件
        if($media_type!="zy_network_video"){

            //移动文件,需要判断文件是否完整
            if(is_file($from_dir."/".$filename)){
                if(!rename($from_dir."/".$filename,$target_dir."/".$filename)){
                    return false;
                }
            }

            //移动zip解压后的文件夹
            if($media_type=="zy_3d"||$media_type=="zy_ppt"){

                //3d文件和ppt文件才移动文件夹
                $zipdir=substr($filename, 0, strrpos($filename, "."));
                if(is_dir($from_dir."/".$zipdir)){
                    if(!rename($from_dir."/".$zipdir,$target_dir."/".$zipdir)){
                        return false;
                    }
                }
            }
        }

        //移动媒体文件的封面图
        if(is_file($from_dir."/".$thumb_filename)){
            if(!rename($from_dir."/".$thumb_filename,$target_dir."/".$thumb_filename)){
                return false;
            }
        }

        //返回值
        return true;
    }

    /**
     * 创建目标文件夹,并且给私有变量路径赋值
     * @param int $post_id 文章或者幻灯片id
     * @return bool true|false 创建是否成功
     */
    public function zy_get_targetdir($post_id){
        global $user_ID;
        $this->dir=wp_upload_dir();
        $this->from_dir=$this->dir["basedir"]."/tmp/".$user_ID;
        $this->target_dir=$this->dir["basedir"]."/".$post_id;

        //创建目标文件夹
        if(!is_dir($this->target_dir)){
            if(!mkdir($this->target_dir)){
                return false;
            }

            /*修改文件夹权限，在linux上会与umask权限做&操作，默认的0777会变成0755，
            所以要手动更改，更改是因为打包系统会在文件夹里面生成压缩图片，如果755会没有写权限，无法放压缩文件
            */
            chmod($this->target_dir,0777);
        }

        return true;
    }

    /**
     * 新建的时候保存媒体数据
     * @param int $post_id 文章或者幻灯片id
     * @param string $new_medias 媒体文件数据json字符串
     * @return bool true|false 保存是否成功
     */
    public function zy_new_save_medias($post_id,$new_medias){
        global $user_ID;

        //获取和创建目录
        if(!$this->zy_get_targetdir($post_id)){
            return false;
        }

        if($new_medias!=""){
            $new_medias=stripslashes($new_medias);//使用这个删除字符串中前台添加的反斜杠，需要删除反斜杠json_decode才能解析
            $new_medias_array=json_decode($new_medias,true);

            //循环处理没一张图片的媒体文件
            foreach($new_medias_array as $key=>$value){

                //移动文件
                if(!$this->zy_move_media_files($this->from_dir,$this->target_dir,$value)){
                    return false;
                }

                //替换路径中tmp,json_encode函数遇到中文的时候会转成unicode编码，在写入数据库之前要设置成utf8
                $json_string=zy_common_class::zy_array_to_string($value);
                $json_string=str_replace("tmp/$user_ID",$post_id,$json_string);

                if(!update_post_meta($post_id,$key,$json_string)){
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * 修改的时候保存媒体数据
     * @param int $post_id 文章或者幻灯片id
     * @param string $new_medias 所有上传的媒体文件的json字符串
     * @return bool true|false 保存是否成功
     */
    public function zy_edit_save_medias($post_id,$new_medias){
        global $user_ID;

        //获取和创建目录
        if(!$this->zy_get_targetdir($post_id)){
            return false;
        }

        //获取出所有的meta
        $old_meta=get_post_meta($post_id);
        $old_medias=array();

        //取出是代表媒体文件的数据
        foreach($old_meta as $key=>$value){
            if(strpos($key,"zy_image_")!==false||strpos($key,"zy_location_")!==false||strpos($key,"zy_3d_")!==false||strpos($key,"zy_ppt_")!==false||strpos($key,"zy_network_")!==false){
                $old_medias[$key]=json_decode($value[0],true);
            }
        }

        if($new_medias!=""){

            //本次提交的媒体文件不为空的情况下，需要判断是否更改了
            $new_medias=stripslashes($new_medias);
            $new_medias_array=json_decode($new_medias,true);

            $new_medias_diffrent=array_diff_key($new_medias_array,$old_medias);//新的和旧的不同，以新的为基准
            $old_medias_diffrent=array_diff_key($old_medias,$new_medias_array);//旧的和旧的不同，以旧的为基准


            /*
           * 如果旧的文件和新的文件键不一样，那就是增加或者删除了与此同时可能修改了，需要进一步判断删除了的，新增了的
           * 这里就有多种情况
           * 1、新增了并且改了原来的
           * 2、删除了并且改了原来的
           * 3、纯粹新增
           * 4、纯粹删除
           * */
            if(count($new_medias_diffrent)||count($old_medias_diffrent)){

                //处理1、3情况,处理2的改了原来部分，以新的为基准
                foreach($new_medias_array as $key=>$value){

                    //old和new不同的时候，这里需要先判断键在旧的中是否存在，来判断该媒体文件是否是新增的
                    if(array_key_exists($key,$old_medias)){

                        //如果键存在，这里也分两种情况，一种是value值改动过，一种是没有改动过
                        $diffrent=array_diff_assoc($value,$old_medias[$key]);//取数组差集,看这个值是否改动过,函数不能比较嵌套数组

                        //改动过值，需要取文件，并且更新数据库
                        if(count($diffrent)){
                            if(!$this->zy_move_media_files($this->from_dir,$this->target_dir,$value)){
                                return false;
                            }

                            $json_string=zy_common_class::zy_array_to_string($value);
                            $json_string=str_replace("tmp/$user_ID",$post_id,$json_string);

                            if(!update_post_meta($post_id,$key,$json_string)){
                                return false;
                            }
                        }else{

                            //没有改动过值，不需要更新数据库，只需要取同名文件
                            if(!$this->zy_move_media_files($this->from_dir,$this->target_dir,$value)){
                                return false;
                            }
                        }
                    }else{

                        //不存在的话，代表是新增的，需要插入数据库记录，并且移动文件
                        if(!$this->zy_move_media_files($this->from_dir,$this->target_dir,$value)){
                            return false;
                        }

                        //更新数据库记录
                        $json_string=zy_common_class::zy_array_to_string($value);
                        $json_string=str_replace("tmp/$user_ID",$post_id,$json_string);

                        if(!update_post_meta($post_id,$key,$json_string)){
                            return false;
                        }
                    }
                }

                //处理4情况，处理2的删除部分，旧的中存在，但是新的中不存在的，需要删除
                foreach($old_medias as $key=>$value){
                    if(!array_key_exists($key,$new_medias_array)){

                        //在新的数组中不存在的，要删除数据库记录，并且删除文件
                        if(!delete_post_meta($post_id,$key)){
                            return false;
                        }

                        //删除文件和文件夹，这里网络文件不需要删除文件
                        $type=$value["zy_media_type"];
                        $filename=$value["zy_media_filename"];
                        if($type!="zy_network_video"){
                            if($type=="zy_3d"||$type=="zy_ppt"){

                                //删除解压的文件夹
                                $zipdir=substr($filename, 0, strrpos($filename, "."));
                                if(!zy_common_class::zy_deldir($this->target_dir."/".$zipdir)){
                                    return false;
                                }

                            }

                            //删除文件本身
                            if(is_file($this->target_dir."/".$filename)){
                                if(!unlink($this->target_dir."/".$filename)){
                                    return false;
                                }
                            }

                        }

                        //删除文件的缩略图,需要判断，有可能多个共用一个缩略图,或者就是图片媒体资源,要求不能传同名
                        $thumb_name=$value["zy_media_thumb_filename"];

                        //图文混排的媒体文件缩略图为空
                        if($thumb_name!=""){
                            if(is_file($this->target_dir."/".$thumb_name)){
                                if(!unlink($this->target_dir."/".$thumb_name)){
                                    return false;
                                }
                            }
                        }
                    }
                }

            }else{

                //如果旧的和新的键值一样，也可能修改了键值的值，要进行比较，即使没有修改也要取同名文件
                foreach($new_medias_array as $key=>$value){

                    $diffrent=array_diff_assoc($value,$old_medias[$key]);//取数组差集,看这个值是否改动过,函数不能比较嵌套数组

                    if(count($diffrent)){

                        //改动过值，需要取同名文件，并且更新数据库
                        if(!$this->zy_move_media_files($this->from_dir,$this->target_dir,$value)){
                            return false;
                        }

                        //更新数据库记录
                        $json_string=zy_common_class::zy_array_to_string($value);
                        $json_string=str_replace("tmp/$user_ID",$post_id,$json_string);

                        if(!update_post_meta($post_id,$key,$json_string)){
                            return false;
                        }
                    }else{

                        //没有改动过值，不需要更新数据库，只需要取同名文件
                        if(!$this->zy_move_media_files($this->from_dir,$this->target_dir,$value)){
                            return false;
                        }
                    }
                }
            }
        }else{

            //本次提交的媒体文件为空，那说明所有的媒体文件都被删除，则要删除原来的文件和数据库数据
            foreach($old_medias as $key=>$value){

                //删除数据库记录，并且删除文件
                if(!delete_post_meta($post_id,$key)){
                    return false;
                }

                //删除文件和文件夹，这里网络文件不需要删除文件
                $type=$value["zy_media_type"];
                $filename=$value["zy_media_filename"];
                if($type!="zy_network_video"){
                    if($type=="zy_3d"||$type=="zy_ppt"){

                        //删除解压的文件夹
                        $zipdir=substr($filename, 0, strrpos($filename, "."));
                        if(!zy_common_class::zy_deldir($this->target_dir."/".$zipdir)){
                            return false;
                        }
                    }

                    //删除文件本身
                    if(is_file($this->target_dir."/".$filename)){
                        if(!unlink($this->target_dir."/".$filename)){
                            return false;
                        }
                    }

                }

                //删除文件的缩略图,需要判断，有可能多个共用一个缩略图,或者就是图片媒体资源,要求不能传同名
                $thumb_name=$value["zy_media_thumb_filename"];

                //图文混排的媒体文件缩略图为空
                if($thumb_name!=""){
                    if(is_file($this->target_dir."/".$thumb_name)){
                        if(!unlink($this->target_dir."/".$thumb_name)){
                            return false;
                        }
                    }
                }
            }
        }

        //返回值
        return true;
    }

    /**
     * 新建时保存缩略图文件
     * @param int $post_id 文章或者幻灯片id
     * @param string $filename 缩略图文件名
     * @return bool true|false 保存是否成功
     */
    public function zy_new_save_thumb($post_id,$filename){

        //获取和创建目录
        if(!$this->zy_get_targetdir($post_id)){
            return false;
        }

        //移动文件
        if(is_file($this->from_dir."/".$filename)){
            //移动文件
            if(!rename($this->from_dir."/".$filename,$this->target_dir."/".$filename)){
                return false;
            }
        }

        //获取系统压缩的缩略图的压缩图路径
        $pathinfo=pathinfo($filename);

        //中文为自首的文件会是空
        $pathinfo["filename"]=substr($filename, 0,strrpos($filename, '.'));

        $zy_thumb=$pathinfo["filename"].self::ZY_COMPRESS_SUFFIX.".".$pathinfo["extension"];

        //移动系统自动压缩文件
        if(is_file($this->from_dir."/".$zy_thumb)){
            if(!rename($this->from_dir."/".$zy_thumb,$this->target_dir."/".$zy_thumb)){
                return false;
            }
        }

        $filepath=$this->dir["baseurl"]."/".$post_id."/".$filename;

        //组装数据库数据
        $json='{"filename":"'.$filename.'","filepath":"'.$filepath.'"}';
        if(!update_post_meta($post_id,"zy_thumb",$json)){
            return false;
        }

        return true;
    }

    /**
     * 修改时保存缩略图文件
     * @param int $post_id 文章或者幻灯片id
     * @param string $filename 新缩略图文件名
     * @param string $old_filename 旧缩略图文件名
     * @return bool true|false 保存是否成功
     */
    public function zy_edit_save_thumb($post_id,$filename,$old_filename){

        //获取和创建目录
        if(!$this->zy_get_targetdir($post_id)){
            return false;
        }


        if($old_filename!=$filename){

            //删除原有文件
            if(is_file($this->target_dir."/".$old_filename)){
                if(!unlink($this->target_dir."/".$old_filename)){
                    return false;
                }
            }

            //获取系统压缩的缩略图的压缩图路径
            $pathinfo_old=pathinfo($old_filename);

            //中文为自首的文件会是空
            $pathinfo_old["filename"]=substr($old_filename, 0,strrpos($old_filename, '.'));

            $zy_compress_old=$pathinfo_old["filename"].self::ZY_COMPRESS_SUFFIX.".".$pathinfo_old["extension"];
            //删除系统自动压缩文件
            if(is_file($this->target_dir."/".$zy_compress_old)){
                if(!unlink($this->target_dir."/".$zy_compress_old)){
                    return false;
                }
            }

            //移动文件
            if(is_file($this->from_dir."/".$filename)){
                if(!rename($this->from_dir."/".$filename,$this->target_dir."/".$filename)){
                    return false;
                }
            }

            //获取系统压缩的缩略图的压缩图路径
            $pathinfo=pathinfo($filename);

            //中文为自首的文件会是空
            $pathinfo["filename"]=substr($filename, 0,strrpos($filename, '.'));
            $zy_compress=$pathinfo["filename"].self::ZY_COMPRESS_SUFFIX.".".$pathinfo["extension"];

            //移动系统自动压缩文件
            if(is_file($this->from_dir."/".$zy_compress)){
                if(!rename($this->from_dir."/".$zy_compress,$this->target_dir."/".$zy_compress)){
                    return false;
                }
            }


            $filepath=$this->dir["baseurl"]."/".$post_id."/".$filename;
            //组装数据库数据
            $json='{"filename":"'.$filename.'","filepath":"'.$filepath.'"}';
            if(!update_post_meta($post_id,"zy_thumb",$json)){
                return false;
            }
        }else{

            //如果旧的和新的相同，也要去取同名文件
            if(is_file($this->from_dir."/".$filename)){
                if(!rename($this->from_dir."/".$filename,$this->target_dir."/".$filename)){
                    return false;
                }
            }

            //获取系统压缩的缩略图的压缩图路径
            $pathinfo=pathinfo($filename);

            //中文为自首的文件会是空
            $pathinfo["filename"]=substr($filename, 0,strrpos($filename, '.'));

            $zy_compress=$pathinfo["filename"].self::ZY_COMPRESS_SUFFIX.".".$pathinfo["extension"];
            //移动系统自动压缩文件
            if(is_file($this->from_dir."/".$zy_compress)){
                if(!rename($this->from_dir."/".$zy_compress,$this->target_dir."/".$zy_compress)){
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * 新建时保存年份
     * @param int $post_id 文章或者幻灯片id
     * @param string $start_year 起始年份
     * @return bool true|false 保存是否成功
     */
    public function zy_new_save_year($post_id,$start_year){

        //新增
        if(!update_post_meta($post_id,"zy_start_year",$start_year)){
            return false;
        }

        return true;
    }

    /**
     * 修改时时保存时间
     * @param int $post_id 文章或者幻灯片id
     * @param string $start_year 起始年份
     * @param string $old_start_year 原来的其实年份
     * @return bool true|false 保存是否成功
     * @return bool
     */
    public function zy_edit_save_year($post_id,$start_year,$old_start_year){
        if($start_year!=$old_start_year){
            if(!update_post_meta($post_id,"zy_start_year",$start_year)){
                return false;
            }
        }
        return true;
    }

    /**
     * 新建时保存背景文件
     * @param int $post_id 文章或幻灯片id
     * @param string $filename 背景文件名
     * @return bool true|false 保存是否成功
     */
    public function zy_new_save_background($post_id,$filename){
        if(!empty($filename)){
            $pathinfo=pathinfo($filename);
            $filetype =$pathinfo["extension"];//获取后缀

            //获取和创建目录
            if(!$this->zy_get_targetdir($post_id)){
                return false;
            }

            //移动文件
            if(is_file($this->from_dir."/".$filename)){
                //移动文件
                if(!rename($this->from_dir."/".$filename,$this->target_dir."/".$filename)){
                    return false;
                }
            }

            $filepath=$this->dir["baseurl"]."/".$post_id."/".$filename;
            $json='{"filename":"'.$filename.'","filepath":"'.$filepath.'","type":"'.$filetype.'"}';
            if(!update_post_meta($post_id,"zy_background",$json)){
                return false;
            }
        }

        return true;
    }

    /**
     * 修改时保存缩略图文件
     * @param int $post_id 文章或者幻灯片id
     * @param string $filename 新背景文件名
     * @param string $old_filename 旧背景文件名
     * @return bool true|false 保存是否成功
     */
    public function zy_edit_save_background($post_id,$filename,$old_filename){
        if(!empty($filename)){
            $pathinfo=pathinfo($filename);
            $filetype =$pathinfo["extension"];//获取后缀

            //获取和创建目录
            if(!$this->zy_get_targetdir($post_id)){
                return false;
            }


            if($old_filename!=$filename){

                //删除原有文件
                if(is_file($this->target_dir."/".$old_filename)){
                    if(!unlink($this->target_dir."/".$old_filename)){
                        return false;
                    }
                }

                //移动文件
                if(is_file($this->from_dir."/".$filename)){
                    if(!rename($this->from_dir."/".$filename,$this->target_dir."/".$filename)){
                        return false;
                    }
                }

                $filepath=$this->dir["baseurl"]."/".$post_id."/".$filename;
                $json='{"filename":"'.$filename.'","filepath":"'.$filepath.'","type":"'.$filetype.'"}';
                if(!update_post_meta($post_id,"zy_background",$json)){
                    return false;
                }
            }else{

                //如果相同，也要去同名文件
                if(is_file($this->from_dir."/".$filename)){
                    //移动文件
                    if(!rename($this->from_dir."/".$filename,$this->target_dir."/".$filename)){
                        return false;
                    }
                }
            }
        }else{

            /*
           * 如果新传过来的为空，则需要删除原来的背景
           * 这里需要判断原来的背景是否存在，因为在幻灯片那里可能传一个不存在的值过来$_POST["zy_backgroun"]为null
           * */
            if(isset($old_filename)){

                //删除原有文件
                if(is_file($this->target_dir."/".$old_filename)){
                    if(!unlink($this->target_dir."/".$old_filename)){
                        return false;
                    }
                }

                //如果值为空，则直接删除meta
                if(!delete_post_meta($post_id,"zy_background")){
                    return false;
                }
            }
        }

        return true;
    }
}
