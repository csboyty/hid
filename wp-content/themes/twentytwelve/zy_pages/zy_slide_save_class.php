<?php
/**
 * Created by JetBrains PhpStorm.
 * User: ty
 * Date: 13-6-20
 * Time: 上午9:53
 * To change this template use File | Settings | File Templates.
 */
include("zy_articles_save_class.php");
class zy_slide_save_class extends zy_articles_save_class
{
    /**
     * 新建幻灯片
     * @return int|WP_Error 新疆后的幻灯片id或者错误
     */
    public function zy_slide_new(){

        global $user_ID,$wpdb;

        //获取主体内容
        $post_data=array();
        $post_data["post_category"]=$_POST["post_category"];
        $post_data["tax_input"]=array(
            "zy_city"=>$_POST["tax_input"]["zy_city"],
            "zy_company"=>$_POST["tax_input"]["zy_company"],
            "zy_genre"=>$_POST["tax_input"]["zy_genre"],
            "zy_people"=>$_POST["tax_input"]["zy_people"]
        );
        $post_data["post_content"]=$_POST["zy_content"];
        $post_data["post_title"]=$_POST["zy_title"];
        $post_data["post_excerpt"]=$_POST["zy_memo"];
        $post_data["post_name"]=$_POST["zy_title"];
        $post_data["post_date"]=current_time('mysql');
        $post_data["post_date_gmt"]=date("Y-m-d H:i:s");
        $post_data["post_status"]="publish";
        $post_data["post_type"]="post";
        $post_data["post_mime_type"]="zyslide";


        //保存主题内容
        if(!$post_id=wp_insert_post($post_data)){
            die("保存幻灯片数据出错，请联系开发人员");
        }


        //存储媒体文件数据
        $new_medias=$_POST["zy_medias"];
        if(!$this->zy_new_save_medias($post_id,$new_medias)){
            die("保存媒体数据出错，请联系开发人员");
        }

        //存储缩略图数据
        $filename=$_POST["zy_thumb"];
        if(!$this->zy_new_save_thumb($post_id,$filename)){
            die("保存缩略图数据出错，请联系开发人员");
        }


        //存储年份数据
        $start_year=$_POST["zy_start_year"];
        $end_year=$_POST["zy_end_year"];
        if(!$this->zy_new_save_year($post_id,$start_year,$end_year)){
            die("保存年份数据出错，请联系开发人员");
        }

        //存储背景数据
        $filename=$_POST["zy_background"];
        if(!$this->zy_new_save_background($post_id,$filename)){
            die("保存背景数据出错，请联系开发人员");
        }

        //替换文件内容
        $post_content=get_post($post_id)->post_content;
        $post_content=str_replace("tmp/$user_ID",$post_id,$post_content);

        if(!$wpdb->update($wpdb->posts,array("post_content"=>$post_content),array("ID"=>$post_id))){
            die("保存文章内容出错，请联系开发人员");
        }

        //保存打包数据
        if(!$wpdb->insert($wpdb->prefix."pack_ids",array("post_id"=>$post_id),array("%d"))){
            die("保存打包数据出错，请联系开发人员");
        }

        //设置锁定标志
        wp_set_post_lock( $post_id );

        //删除临时存储文件夹
       /* $target_dir=wp_upload_dir();
        $target_dir=$target_dir["basedir"]."/tmp/".$user_ID;
        if(is_dir($target_dir)){
            zy_common_class::zy_deldir($target_dir);
        }*/


        return $post_id;
    }

    /**
     * 修改幻灯片保存
     * @return mixed 返回幻灯片id或者错误
     */
    public function zy_slide_edit(){

        global $wpdb,$user_ID;

        //获取主体内容
        $post_data=array();
        $post_id=$_POST["zy_slide_id"];
        $post_data["ID"]=$post_id;
        $post_data["post_category"]=$_POST["post_category"];
        $post_data["tax_input"]=array(
            "zy_city"=>$_POST["tax_input"]["zy_city"],
            "zy_company"=>$_POST["tax_input"]["zy_company"],
            "zy_genre"=>$_POST["tax_input"]["zy_genre"],
            "zy_people"=>$_POST["tax_input"]["zy_people"]
        );
        $post_data["post_content"]=str_replace("tmp/$user_ID",$post_id,$_POST["zy_content"]);
        $post_data["post_title"]=$_POST["zy_title"];
        $post_data["post_excerpt"]=$_POST["zy_memo"];
        $post_data["post_name"]=$_POST["zy_title"];
        /*$post_data["post_date"]=current_time('mysql');
        $post_data["post_date_gmt"]=date("Y-m-d H:i:s");*/
        $post_data["post_status"]="publish";
        $post_data["post_type"]="post";
        $post_data["post_mime_type"]="zyslide";

        //保存主题内容
        if(!wp_update_post($post_data)){
            die("保存幻灯片数据出错，请联系开发人员");
        }


        //存储媒体文件数据
        $new_medias=$_POST["zy_medias"];
        if(!$this->zy_edit_save_medias($post_id,$new_medias)){
            die("保存媒体数据出错，请联系开发人员");
        }

        //存储缩略图数据
        $filename=$_POST["zy_thumb"];
        $old_filename=$_POST["zy_old_thumb"];
        if(!$this->zy_edit_save_thumb($post_id,$filename,$old_filename)){
            die("保存缩略图数据出错，请联系开发人员");
        }


        //存储年份数据
        $start_year=$_POST["zy_start_year"];
        $old_start_year=$_POST["zy_old_start_year"];
        if(!$this->zy_edit_save_year($post_id,$start_year,$old_start_year)){
            die("保存年份数据出错，请联系开发人员");
        }

        //存储背景数据
        $filename=$_POST["zy_background"];
        $old_filename=$_POST["zy_old_background"];
        if($old_filename){
            if(!$this->zy_edit_save_background($post_id,$filename,$old_filename)){
                die("保存背景数据出错，请联系开发人员");
            }
        }else{
            if(!$this->zy_new_save_background($post_id,$filename)){
                die("保存背景数据出错，请联系开发人员");
            }
        }

        //存在的情况下，修改
        if($wpdb->update($wpdb->prefix."pack_ids",array("pack_lock"=>0,"pack_time"=>NULL),array("post_id"=>$post_id),array("%d","%s"))===false){
            die("保存打包数据出错，请联系开发人员");
        }

        //设置锁定标志
        wp_set_post_lock( $post_id );

        //删除临时存储文件夹
       /* $target_dir=wp_upload_dir();
        $target_dir=$target_dir["basedir"]."/tmp/".$user_ID;
        if(is_dir($target_dir)){
            zy_common_class::zy_deldir($target_dir);
        }*/

        //移动分类删除打包数据,不在timeline分类里的需要删除打包数据
        $timeline_term_id=19;
        if($_POST["post_category"]){
            if(count($_POST["post_category"])>=2||(count($_POST["post_category"])==1&&$_POST["post_category"][0]!=$timeline_term_id)){

                //发送数据给打包程序，删除zip包
                $url=get_site_url()."/bundle-app/removeBundle";
                $zy_http_result=false;

                for($i=0;$i<3;$i++){
                    if(zy_common_class::zy_http_send($post_id,$url)){
                        $zy_http_result=true;
                        break;//跳出循环
                    }
                }

                //判断是否成功
                if(!$zy_http_result){
                    die("删除打包文件失败，请将文章id".$post_id."告诉开发人员！");
                }
            }
        }


        return $post_id;
    }
}